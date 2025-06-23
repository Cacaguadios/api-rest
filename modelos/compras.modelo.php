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
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO $tabla (id_cliente, id_metodo_pago) 
                VALUES (:id_cliente, :id_metodo_pago)"
            );

            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->bindParam(":id_metodo_pago", $id_metodo_pago, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $lastId = Conexion::conectar()->lastInsertId();
                error_log("✅ Compra creada con ID: $lastId");
                return (int)$lastId;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ ERROR SQL (createCompra): " . implode(" | ", $errorInfo));
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
