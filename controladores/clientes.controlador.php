<?php 



class ControladorClientes{


    public function index($pagina = null) {

        if ($pagina != null && is_numeric($pagina)) {
            $cantidad = 10;
            $desde = ($pagina - 1) * $cantidad;
            $clientes = ModeloClientes::index("clientes", $cantidad, $desde);
        } else {
            $clientes = ModeloClientes::index("clientes");
        }

        $json = array(
            "status" => 200,
            "total_registros" => count($clientes),
            "detalle" => $clientes
        );

        echo json_encode($json, true);
        return;
    }



    public function create($datos) {

        // Validar nombre
        if (isset($datos["nombre"]) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $datos["nombre"])) {
            $json = array(
                "status" => 404,
                "detalle" => "error en el campo del nombre, permitido solo letras"
            );
            echo json_encode($json, true);
            return;
        }

        // Validar apellido
        if (isset($datos["apellido"]) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $datos["apellido"])) {
            $json = array(
                "status" => 404,
                "detalle" => "error en el campo del apellido, permitido solo letras"
            );
            echo json_encode($json, true);
            return;
        }

        // Validar email
        if (isset($datos["email"]) && !preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $datos["email"])) {
            $json = array(
                "status" => 404,
                "detalle" => "error en el campo email"
            );
            echo json_encode($json, true);
            return;
        }

        // Validar email repetido
        $clientes = ModeloClientes::index("clientes");
        foreach ($clientes as $key => $value) {
            if ($value["email"] == $datos["email"]) {
                $json = array(
                    "status" => 404,
                    "detalle" => "el email está repetido"
                );
                echo json_encode($json, true);
                return;
            }
        }

        // Generar credenciales del cliente
        $id_cliente = str_replace("$", "c", crypt(
            $datos["nombre"] . $datos["apellido"] . $datos["email"],
            '$2a$07$afartwetsdAD52356FEDGsfhsd$'
        ));

        $llave_secreta = str_replace("$", "a", crypt(
            $datos["email"] . $datos["apellido"] . $datos["nombre"],
            '$2a$07$afartwetsdAD52356FEDGsfhsd$'
        ));

        $datos = array(
            "nombre" => $datos["nombre"],
            "apellido" => $datos["apellido"],
            "email" => $datos["email"],
            "id_cliente" => $id_cliente,
            "llave_secreta" => $llave_secreta,
            "created_at" => date('Y-m-d h:i:s'),
            "updated_at" => date('Y-m-d h:i:s')
        );

        $create = ModeloClientes::create("clientes", $datos);

        if ($create == "ok") {
            $json = array(
                "status" => 200,
                "detalle" => "se generaron sus credenciales",
                "id_cliente" => $id_cliente,
                "llave_secreta" => $llave_secreta
            );
            echo json_encode($json, true);
            return;
        }
    }


    public function update($id, $datos)
    {
        /*=============================================
        Validar credenciales del cliente
        =============================================*/
        $clientes = ModeloClientes::index("clientes");

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

            foreach ($clientes as $cliente) {

                $authUser = $_SERVER['PHP_AUTH_USER'];
                $authPw = $_SERVER['PHP_AUTH_PW'];

                $encodedRequest = base64_encode($authUser . ":" . $authPw);
                $encodedStored = base64_encode($cliente["id_cliente"] . ":" . $cliente["llave_secreta"]);

                if ($encodedRequest === $encodedStored) {

                    // Verificamos que el cliente autenticado sea el que quiere actualizar
                    if ($cliente["id"] != $id) {
                        echo json_encode([
                            "status" => 403,
                            "detalle" => "No tiene permiso para editar este perfil"
                        ]);
                        return;
                    }

                    /*=============================================
                    Validar los campos enviados
                    =============================================*/
                    foreach ($datos as $key => $valor) {
                        if (isset($valor) && !preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ@_.\- ]+$/', $valor)) {
                            echo json_encode([
                                "status" => 422,
                                "detalle" => "Error en el campo " . $key
                            ]);
                            return;
                        }
                    }

                    /*=============================================
                    Actualizar datos del cliente
                    =============================================*/
                    $datosActualizados = array(
                        "nombre" => $datos["nombre"],
                        "apellido" => $datos["apellido"],
                        "email" => $datos["email"],
                        "updated_at" => date('Y-m-d H:i:s')
                    );

                    $respuesta = ModeloClientes::update("clientes", $id, $datosActualizados);

                    if ($respuesta == "ok") {
                        echo json_encode([
                            "status" => 200,
                            "detalle" => "Perfil actualizado correctamente"
                        ]);
                    } else {
                        echo json_encode([
                            "status" => 500,
                            "detalle" => "No se pudo actualizar el perfil"
                        ]);
                    }

                    return;
                }
            }

            // Credenciales no coinciden
            echo json_encode([
                "status" => 401,
                "detalle" => "Credenciales inválidas"
            ]);
            return;
        }

        // No se proporcionaron credenciales
        echo json_encode([
            "status" => 401,
            "detalle" => "Se requieren credenciales de autenticación"
        ]);
        return;
    }




    public function delete($id)
    {

        //Validar credenciales del cliente

        $clientes = ModeloClientes::index("clientes");

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

            foreach ($clientes as $cliente) {

                $authUser = $_SERVER['PHP_AUTH_USER'];
                $authPw = $_SERVER['PHP_AUTH_PW'];

                $encodedRequest = base64_encode($authUser . ":" . $authPw);
                $encodedStored = base64_encode($cliente["id_cliente"] . ":" . $cliente["llave_secreta"]);

                if ($encodedRequest === $encodedStored) {

                    if ($cliente["id"] != $id) {
                        echo json_encode([
                            "status" => 403,
                            "detalle" => "No tiene permiso para eliminar este perfil"
                        ]);
                        return;
                    }

                    $respuesta = ModeloClientes::delete("clientes", $id);

                    if ($respuesta == "ok") {
                        echo json_encode([
                            "status" => 200,
                            "detalle" => "Perfil eliminado correctamente"
                        ]);
                    } else {
                        echo json_encode([
                            "status" => 500,
                            "detalle" => "No se pudo eliminar el perfil"
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
        return;
    }




}






?>