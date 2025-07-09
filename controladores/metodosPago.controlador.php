<?php
require_once 'modelos/metodosPago.modelo.php';
require_once 'modelos/clientes.modelo.php';

class ControladorMetodosPago {

    // ─── Helper de Autenticación ─────────────────────────────────────────
    private function getClienteAutenticado() {
        if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode([
                "status"  => 401,
                "detalle" => "Faltan credenciales"
            ]);
            return false;
        }

        $id = ModeloClientes::autenticar(
            $_SERVER['PHP_AUTH_USER'],
            $_SERVER['PHP_AUTH_PW']
        );

        if (!$id) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode([
                "status"  => 401,
                "detalle" => "Credenciales inválidas"
            ]);
            return false;
        }

        return $id;
    }

    // ─── Listar mis métodos de pago ───────────────────────────────────────
    public function index() {
        $id_cliente = $this->getClienteAutenticado();
        if ($id_cliente === false) return;

        $metodos = ModeloMetodosPago::getByCliente("metodos_pago", $id_cliente);
        echo json_encode([
            "status" => 200,
            "total"  => count($metodos),
            "datos"  => $metodos
        ]);
    }

    // ─── Crear un método de pago ─────────────────────────────────────────
    public function create() {
        $id_cliente = $this->getClienteAutenticado();
        if ($id_cliente === false) return;

        $json = json_decode(file_get_contents("php://input"), true);

        // validamos tipo de pago
        $tipo_pago = $json['tipo_pago'] ?? null;
        if (!in_array($tipo_pago, ['efectivo','tarjeta'])) {
            echo json_encode([
                "status"  => 400,
                "detalle" => "tipo_pago inválido"
            ]);
            return;
        }

        // armamos el array de datos
        $datos = [
            "id_cliente"     => $id_cliente,
            "tipo_pago"      => $tipo_pago,
            "tipo"           => $json['tipo']          ?? $tipo_pago,
            "detalles"       => $json['detalles']      ?? null,
            "titular"        => null,
            "numero_tarjeta" => null,
            "exp_mes"        => null,
            "exp_ano"        => null
        ];

        // si es tarjeta, validamos campos extra
        if ($tipo_pago === 'tarjeta') {
            foreach (['titular','numero_tarjeta','exp_mes','exp_ano'] as $f) {
                if (empty($json[$f])) {
                    echo json_encode([
                        "status"  => 400,
                        "detalle" => "Falta campo $f"
                    ]);
                    return;
                }
                $datos[$f] = $json[$f];
            }
            // opcionalmente enmascara aquí:
            // $datos['numero_tarjeta'] = substr($datos['numero_tarjeta'], -4);
        }

        $newId = ModeloMetodosPago::create("metodos_pago", $datos);
        if ($newId) {
            http_response_code(201);
            echo json_encode([
                "status"  => 201,
                "detalle" => "Método de pago creado",
                "id"      => $newId
            ]);
        } else {
            echo json_encode([
                "status"  => 500,
                "detalle" => "Error al crear método"
            ]);
        }
    }

    // ─── Eliminar un método de pago ─────────────────────────────────────
    public function delete($id) {
        $id_cliente = $this->getClienteAutenticado();
        if ($id_cliente === false) return;

        $ok = ModeloMetodosPago::delete("metodos_pago", $id, $id_cliente);
        if ($ok) {
            echo json_encode([
                "status"  => 200,
                "detalle" => "Método eliminado"
            ]);
        } else {
            echo json_encode([
                "status"  => 500,
                "detalle" => "No se pudo eliminar"
            ]);
        }
    }
}
