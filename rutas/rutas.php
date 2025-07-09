<?php

$ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$arrayRutas = explode("/", trim($ruta, "/"));

// Verificar si llega 'pagina' como query string y es numérico
if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
    $pagina = (int) $_GET['pagina'];

    $cursos = new ControladorCursos();
    $cursos->index($pagina);
    return; // Termina para no seguir procesando rutas
}

// Si la ruta es vacía o sólo tiene un segmento 'api-rest'
if (count($arrayRutas) == 0 || (count($arrayRutas) == 1 && $arrayRutas[0] === 'api-rest')) {
    $json = array(
        "detalle" => "No encontrado"
    );
    echo json_encode($json, true);
    return;
}

// Cuando la URI es del tipo /api-rest/cursos
if (count($arrayRutas) == 2 && $arrayRutas[0] == "api-rest" && $arrayRutas[1] == "cursos") {
    $cursos = new ControladorCursos();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $cursos->index(null);
        return;

    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Leer el contenido JSON
        $input = file_get_contents("php://input");
        $datos = json_decode($input, true);

        if (!$datos) {
            echo json_encode([
                "status" => 400,
                "detalle" => "JSON inválido"
            ]);
            return;
        }

        // Validar campos requeridos
        $camposRequeridos = ['titulo', 'descripcion', 'instructor', 'imagen', 'precio'];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                echo json_encode([
                    "status" => 400,
                    "detalle" => "El campo '$campo' es obligatorio"
                ]);
                return;
            }
        }

        $cursos->create($datos);
        return;
    }
}

// Cuando la URI es del tipo /api-rest/cursos/{id}
if (count($arrayRutas) == 3 && $arrayRutas[0] == "api-rest" && $arrayRutas[1] == "cursos") {
    $id = $arrayRutas[2];
    $cursos = new ControladorCursos();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $cursos->show($id);
        return;

    } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        // Leer contenido JSON para PUT
        $input = file_get_contents("php://input");
        $putData = json_decode($input, true);

        if (!$putData) {
            echo json_encode([
                "status" => 400,
                "detalle" => "JSON inválido"
            ]);
            return;
        }

        // Opcional: validar campos si quieres asegurarte que no estén vacíos
        // por ejemplo, si el PUT debe enviar todos los campos:
        /*
        $camposRequeridos = ['titulo', 'descripcion', 'instructor', 'imagen', 'precio'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($putData[$campo]) || empty($putData[$campo])) {
                echo json_encode([
                    "status" => 400,
                    "detalle" => "El campo '$campo' es obligatorio"
                ]);
                return;
            }
        }
        */

        $cursos->update($id, $putData);
        return;

    } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        $cursos->delete($id);
        return;
    }
}

// Cuando la URI es del tipo /api-rest/clientes
if (count($arrayRutas) == 2 && $arrayRutas[0] == "api-rest" && $arrayRutas[1] == "clientes") {

    $clientes = new ControladorClientes();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Mostrar todos los clientes sin autenticación
        $clientes->index();
        return;
    }

    elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Leer contenido JSON
        $input = file_get_contents("php://input");
        $datos = json_decode($input, true);

        if (!$datos) {
            echo json_encode([
                "status" => 400,
                "detalle" => "JSON inválido"
            ]);
            return;
        }

        // Validar campos requeridos
        $camposRequeridos = ['nombre', 'apellido', 'email'];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                echo json_encode([
                    "status" => 400,
                    "detalle" => "El campo '$campo' es obligatorio"
                ]);
                return;
            }
        }

        $clientes->create($datos);
        return;
    }
}


// Cuando la URI es del tipo /api-rest/clientes/{id}
if (count($arrayRutas) == 3 && $arrayRutas[0] == "api-rest" && $arrayRutas[1] == "clientes") {

    $id = $arrayRutas[2];
    $clientes = new ControladorClientes();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $clientes->show($id);
        return;

    } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        // Leer contenido JSON para PUT
        $input = file_get_contents("php://input");
        $putData = json_decode($input, true);

        if (!$putData) {
            echo json_encode([
                "status" => 400,
                "detalle" => "JSON inválido"
            ]);
            return;
        }

        // Validación opcional (ejemplo: verificar que nombre, apellido, email estén presentes)
        $camposRequeridos = ['nombre', 'apellido', 'email'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($putData[$campo]) || empty($putData[$campo])) {
                echo json_encode([
                    "status" => 400,
                    "detalle" => "El campo '$campo' es obligatorio"
                ]);
                return;
            }
        }

        $clientes->update($id, $putData);
        return;

    } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        $clientes->delete($id);
        return;
    }
}


// Cuando la URI es del tipo /api-rest/compras
if (count($arrayRutas) == 2 && $arrayRutas[0] == "api-rest" && $arrayRutas[1] == "compras") {

    $compras = new ControladorCompras();

    // Registrar una compra (a partir del carrito)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $compras->create();
        return;
    }

    // Obtener mis compras (cliente autenticado)
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $compras->getMisCompras();
        return;
    }
}

// Cuando la URI es del tipo /api-rest/compras/{id}
if (count($arrayRutas) == 3 && $arrayRutas[0] == "api-rest" && $arrayRutas[1] == "compras") {

    $id = $arrayRutas[2];
    $compras = new ControladorCompras();

    // Eliminar compra por ID (opcional)
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        $compras->delete($id);
        return;
    }
}

// Cuando la URI es del tipo /api-rest/carrito
if (count($arrayRutas) == 2 && $arrayRutas[0] == "api-rest" && $arrayRutas[1] == "carrito") {

    $carrito = new ControladorCarrito();

    // Agregar curso al carrito (cliente autenticado)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $input = file_get_contents("php://input");
        $datos = json_decode($input, true);

        if (!$datos) {
            echo json_encode([
                "status" => 400,
                "detalle" => "JSON inválido"
            ]);
            return;
        }

        $carrito->create($datos);
        return;
    }

    // Obtener mi carrito (cliente autenticado)
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $carrito->getMiCarrito();
        return;
    }
}

// Cuando la URI es del tipo /api-rest/carrito/{id}
if (count($arrayRutas) == 3 && $arrayRutas[0] == "api-rest" && $arrayRutas[1] == "carrito") {

    $id = $arrayRutas[2];
    $carrito = new ControladorCarrito();

    // Eliminar curso del carrito por ID
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        $carrito->delete($id);
        return;
    }
}

// /api-rest/metodos-pago  → GET, POST
if ($arrayRutas[0]==='api-rest' && $arrayRutas[1]==='metodos-pago') {
    $ctrl = new ControladorMetodosPago();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $ctrl->index();
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ctrl->create();
        return;
    }
}

// /api-rest/metodos-pago/{id} → DELETE
if (count($arrayRutas) === 3 
    && $arrayRutas[0] === 'api-rest' 
    && $arrayRutas[1] === 'metodos-pago') {
    
    $id = intval($arrayRutas[2]);
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $ctrl = new ControladorMetodosPago();
        $ctrl->delete($id);
        return;
    }
}




?>