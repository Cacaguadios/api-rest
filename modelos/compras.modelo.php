<?php

class ModeloCompras {

    // Obtener todas las compras (tickets)
    static public function index($tabla) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todas las compras (tickets) de un cliente
    static public function getByCliente($tabla, $id_cliente) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE id_cliente = :id_cliente");
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear un ticket de compra
    static public function createCompra($tabla, $id_cliente, $id_metodo_pago = null) {
        try {
            // 1) Abre la conexión una sola vez
            $db = Conexion::conectar();

            // 2) Prepara la inserción
            $sql = "INSERT INTO $tabla (id_cliente, id_metodo_pago) VALUES (:id_cliente, :id_metodo_pago)";
            $stmt = $db->prepare($sql);

            // 3) Bindea el método de pago correctamente (null → PDO::PARAM_NULL)
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            if ($id_metodo_pago !== null) {
                $stmt->bindValue(":id_metodo_pago", $id_metodo_pago, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":id_metodo_pago", null, PDO::PARAM_NULL);
            }

            // 4) Ejecuta y, si va bien, recupera el ID de esa **misma** conexión
            if ($stmt->execute()) {
                $lastId = (int) $db->lastInsertId();
                error_log("✅ Compra creada con ID: $lastId");
                return $lastId;
            } else {
                $error = implode(" | ", $stmt->errorInfo());
                error_log("❌ ERROR SQL (createCompra): $error");
                return "error";
            }

        } catch (PDOException $e) {
            error_log("❌ EXCEPCIÓN: " . $e->getMessage());
            return "error";
        }
    }



    // Insertar detalle de compra (curso comprado)
    static public function createDetalle($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO $tabla (id_compra, id_curso, cantidad, precio_unitario) 
             VALUES (:id_compra, :id_curso, :cantidad, :precio_unitario)"
        );

        $stmt->bindParam(":id_compra", $datos["id_compra"], PDO::PARAM_INT);
        $stmt->bindParam(":id_curso", $datos["id_curso"], PDO::PARAM_INT);
        $stmt->bindParam(":cantidad", $datos["cantidad"], PDO::PARAM_INT);
        $stmt->bindParam(":precio_unitario", $datos["precio_unitario"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }

    // Eliminar ticket de compra (opcional)
    static public function delete($tabla, $id) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
            return "error";
        }

        $stmt->close();
        $stmt = null;
    }
}

?>
