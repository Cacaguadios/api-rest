<?php
// modelos/metodosPago.modelo.php
require_once 'conexion.php';

class ModeloMetodosPago {

    // Listar todos los métodos de pago de un cliente
    static public function getByCliente($tabla, $id_cliente) {
        $stmt = Conexion::conectar()
            ->prepare("SELECT id, tipo_pago, tipo, detalles, titular, numero_tarjeta, exp_mes, exp_ano, creado_en 
                       FROM $tabla 
                       WHERE id_cliente = :id_cliente");
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo método de pago
    static public function create($tabla, $datos) {
        $db = Conexion::conectar();
        $sql = "INSERT INTO $tabla 
          (id_cliente, tipo_pago, tipo, detalles, titular, numero_tarjeta, exp_mes, exp_ano)
         VALUES 
          (:id_cliente, :tipo_pago, :tipo, :detalles, :titular, :numero_tarjeta, :exp_mes, :exp_ano)";
        $stmt = $db->prepare($sql);

        // Campos obligatorios y comunes
        $stmt->bindParam(":id_cliente",   $datos['id_cliente'],    PDO::PARAM_INT);
        $stmt->bindParam(":tipo_pago",    $datos['tipo_pago'],     PDO::PARAM_STR);
        $stmt->bindParam(":tipo",         $datos['tipo'],          PDO::PARAM_STR);
        $stmt->bindParam(":detalles",     $datos['detalles'],      PDO::PARAM_STR);

        // Campos tarjeta o NULL si es efectivo
        if ($datos['tipo_pago'] === 'tarjeta') {
            $stmt->bindParam(":titular",        $datos['titular'],        PDO::PARAM_STR);
            $stmt->bindParam(":numero_tarjeta", $datos['numero_tarjeta'], PDO::PARAM_STR);
            $stmt->bindParam(":exp_mes",        $datos['exp_mes'],        PDO::PARAM_INT);
            $stmt->bindParam(":exp_ano",        $datos['exp_ano'],        PDO::PARAM_INT);
        } else {
            $stmt->bindValue(":titular",        null, PDO::PARAM_NULL);
            $stmt->bindValue(":numero_tarjeta", null, PDO::PARAM_NULL);
            $stmt->bindValue(":exp_mes",        null, PDO::PARAM_NULL);
            $stmt->bindValue(":exp_ano",        null, PDO::PARAM_NULL);
        }

        if ($stmt->execute()) {
            return (int)$db->lastInsertId();
        }

        error_log("ERROR SQL ModeloMetodosPago::create: " . implode(" | ", $stmt->errorInfo()));
        return false;
    }

    // Eliminar un método de pago (solo si pertenece al cliente)
    static public function delete($tabla, $id, $id_cliente) {
        $stmt = Conexion::conectar()
            ->prepare("DELETE FROM $tabla 
                       WHERE id = :id AND id_cliente = :id_cliente");
        $stmt->bindParam(":id",         $id,          PDO::PARAM_INT);
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // (Opcional) Validar que un método de pago pertenezca al cliente
    static public function validar($tabla, $id_metodo_pago, $id_cliente) {
        $stmt = Conexion::conectar()
            ->prepare("SELECT id FROM $tabla 
                       WHERE id = :id AND id_cliente = :id_cliente");
        $stmt->bindParam(":id",          $id_metodo_pago, PDO::PARAM_INT);
        $stmt->bindParam(":id_cliente",  $id_cliente,     PDO::PARAM_INT);
        $stmt->execute();
        return ($stmt->fetch(PDO::FETCH_ASSOC) !== false);
    }

}
