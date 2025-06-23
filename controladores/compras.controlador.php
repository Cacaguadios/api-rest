<?php

class ControladorCompras {

    // Crear una nueva compra a partir del carrito (cliente autenticado)
    public function create() {

        $clientes = ModeloClientes::index("clientes", true);

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

            foreach ($clientes as $cliente) {

                if (
                    base64_encode($_SERVER['PHP_AUTH_USER'] . ":" . $_SERVER['PHP_AUTH_PW']) ==
                    base64_encode($cliente["id_cliente"] . ":" . $cliente["llave_secreta"])
                ) {
                    $id_cliente = $cliente["id"];

                    // Obtener cursos del carrito
                    $carrito = ModeloCarrito::getByCliente("carrito", $id_cliente);

                    if (count($carrito) == 0) {
                        echo json_encode([
                            "status" => 400,
                            "detalle" => "El carrito está vacío"
                        ]);
                        return;
                    }

                    // Crear el ticket de compra
                    $id_compra = ModeloCompras::createCompra("compras", $id_cliente, null);

                    if (!is_numeric($id_compra)) {
                        echo json_encode([
                            "status" => 500,
                            "detalle" => "Error al generar la compra"
                        ]);
                        return;
                    }


                    // Insertar detalle de cada curso
                    foreach ($carrito as $item) {
                        // Obtener precio actual del curso
                        $curso_id = $item["id_curso"];
                        $cantidad = $item["cantidad"];

                        $conexion = Conexion::conectar();
                        $stmt = $conexion->prepare("SELECT precio FROM cursos WHERE id = :id");
                        $stmt->bindParam(":id", $curso_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $curso = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$curso) continue;

                        $detalle = array(
                            "id_compra" => $id_compra,
                            "id_curso" => $curso_id,
                            "cantidad" => $cantidad,
                            "precio_unitario" => $curso["precio"]
                        );

                        ModeloCompras::createDetalle("detalle_compra", $detalle);
                    }

                    // Vaciar carrito del cliente
                    ModeloCarrito::emptyByCliente("carrito", $id_cliente);

                    echo json_encode([
                        "status" => 201,
                        "detalle" => "Compra realizada con éxito",
                        "id_compra" => $id_compra
                    ]);
                    return;
                }
            }

            echo json_encode([
                "status" => 401,
                "detalle" => "Credenciales inválidas"
            ]);
            return;
        }

        echo json_encode([
            "status" => 401,
            "detalle" => "Se requieren credenciales de autenticación"
        ]);
    }

    // Obtener mis compras (tickets)
    public function getMisCompras() {

        $clientes = ModeloClientes::index("clientes", true);

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

            foreach ($clientes as $cliente) {

                if (
                    base64_encode($_SERVER['PHP_AUTH_USER'] . ":" . $_SERVER['PHP_AUTH_PW']) ==
                    base64_encode($cliente["id_cliente"] . ":" . $cliente["llave_secreta"])
                ) {
                    $compras = ModeloCompras::getByCliente("compras", $cliente["id"]);

                    echo json_encode([
                        "status" => 200,
                        "total_registros" => count($compras),
                        "detalle" => $compras
                    ]);
                    return;
                }
            }

            echo json_encode([
                "status" => 401,
                "detalle" => "Credenciales inválidas"
            ]);
            return;
        }

        echo json_encode([
            "status" => 401,
            "detalle" => "Se requieren credenciales de autenticación"
        ]);
    }

    // Eliminar ticket de compra (opcional)
    public function delete($id) {
        $respuesta = ModeloCompras::delete("compras", $id);

        if ($respuesta == "ok") {
            echo json_encode([
                "status" => 200,
                "detalle" => "Compra eliminada correctamente"
            ]);
        } else {
            echo json_encode([
                "status" => 500,
                "detalle" => "No se pudo eliminar la compra"
            ]);
        }
    }
}
