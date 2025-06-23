<?php

class ControladorCarrito {

    // Agregar curso al carrito (autenticado)
    public function create($datos) {

        $clientes = ModeloClientes::index("clientes", true);

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

            foreach ($clientes as $cliente) {

                if (
                    base64_encode($_SERVER['PHP_AUTH_USER'] . ":" . $_SERVER['PHP_AUTH_PW']) ==
                    base64_encode($cliente["id_cliente"] . ":" . $cliente["llave_secreta"])
                ) {

                    if (!isset($datos["id_curso"])) {
                        echo json_encode([
                            "status" => 400,
                            "detalle" => "Falta el campo id_curso"
                        ]);
                        return;
                    }

                    // Verificar si ya existe en el carrito
                    $carrito = ModeloCarrito::getByCliente("carrito", $cliente["id"]);
                    foreach ($carrito as $item) {
                        if ($item["id_curso"] == $datos["id_curso"]) {
                            echo json_encode([
                                "status" => 409,
                                "detalle" => "El curso ya está en el carrito"
                            ]);
                            return;
                        }
                    }

                    $nuevoItem = array(
                        "id_cliente" => $cliente["id"],
                        "id_curso" => $datos["id_curso"],
                        "cantidad" => isset($datos["cantidad"]) ? $datos["cantidad"] : 1
                    );

                    $respuesta = ModeloCarrito::create("carrito", $nuevoItem);

                    if ($respuesta == "ok") {
                        echo json_encode([
                            "status" => 201,
                            "detalle" => "Curso agregado al carrito"
                        ]);
                    } else {
                        echo json_encode([
                            "status" => 500,
                            "detalle" => "Error al agregar al carrito"
                        ]);
                    }

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

    // Obtener carrito del cliente autenticado
    public function getMiCarrito() {

        $clientes = ModeloClientes::index("clientes", true);

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

            foreach ($clientes as $cliente) {

                if (
                    base64_encode($_SERVER['PHP_AUTH_USER'] . ":" . $_SERVER['PHP_AUTH_PW']) ==
                    base64_encode($cliente["id_cliente"] . ":" . $cliente["llave_secreta"])
                ) {
                    $carrito = ModeloCarrito::getByCliente("carrito", $cliente["id"]);

                    echo json_encode([
                        "status" => 200,
                        "total_registros" => count($carrito),
                        "detalle" => $carrito
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

    // Eliminar un ítem del carrito por ID
    public function delete($id) {
        $respuesta = ModeloCarrito::delete("carrito", $id);

        if ($respuesta == "ok") {
            echo json_encode([
                "status" => 200,
                "detalle" => "Elemento eliminado del carrito"
            ]);
        } else {
            echo json_encode([
                "status" => 500,
                "detalle" => "No se pudo eliminar del carrito"
            ]);
        }
    }
}

?>
