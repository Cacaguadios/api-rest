<?php 


class ControladorCursos{



    public function index($pagina){

      /*=============================================
		Validar credenciales del cliente
		=============================================*/

    $clientes = ModeloClientes::index("clientes");


    if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){

      foreach ($clientes as $key => $value) {


        if(base64_encode($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']) == 
           base64_encode($value["id_cliente"] .":". $value["llave_secreta"])){


            if($pagina !=null){

              $cantidad=10;

              $desde=($pagina-1)*$cantidad;


              $cursos=ModeloCursos::index("cursos","clientes",$cantidad ,$desde);



            }else{

                $cursos=ModeloCursos::index("cursos","clientes",null, null);

            }



              

                  $json=array(

                        "status"=>200,
                        "total_registros"=>count($cursos),
                        "detalle"=>$cursos

                                    );

                        echo json_encode($json,true);

                    return;



        }



       
      }








    }


     



    }



    public function create($datos){

		/*=============================================
		Validar credenciales del cliente
		=============================================*/

    $clientes = ModeloClientes::index("clientes");

    if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){

      foreach ($clientes as $key => $valueCliente) {

        if(base64_encode($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']) == 
           base64_encode($valueCliente["id_cliente"] .":". $valueCliente["llave_secreta"])){


          /*=============================================
					Validar datos
					=============================================*/

          foreach ($datos as $key => $valueDatos) {


            if(isset($valueDatos) && !preg_match('/^[(\\)\\=\\&\\$\\;\\-\\_\\*\\"\\<\\>\\?\\¿\\!\\¡\\:\\,\\.\\0-9a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $valueDatos)){

							$json = array(

								"status"=>404,
								"detalle"=>"Error en el campo ".$key

							);

							echo json_encode($json, true);

							return;
						}

          }

          	/*=============================================
					Validar que el titulo o la descripcion no estén repetidos
					=============================================*/

          $cursos = ModeloCursos::index("cursos","clientes" , null , null);

          foreach ($cursos as $key => $value) {

            if($value->titulo == $datos["titulo"]){

							$json = array(

								"status"=>404,
								"detalle"=>"El título ya existe en la base de datos"

							);

							echo json_encode($json, true);	

							return;

						}


            if($value->descripcion == $datos["descripcion"]){

							$json = array(

								"status"=>404,
								"detalle"=>"La descripción ya existe en la base de datos"

							);

							echo json_encode($json, true);	

							return;

							
						}


        }

        	/*=============================================
					Llevar datos al modelo
					=============================================*/

          $datos = array( "titulo"=>$datos["titulo"],
                          "descripcion"=>$datos["descripcion"],
                          "instructor"=>$datos["instructor"],
                          "imagen"=>$datos["imagen"],
                          "precio"=>$datos["precio"],
                          "id_creador"=>$valueCliente["id"],
                          "created_at"=>date('Y-m-d h:i:s'),
                          "updated_at"=>date('Y-m-d h:i:s'));



                          $create = ModeloCursos::create("cursos", $datos);


        /*=============================================
					Respuesta del modelo
					=============================================*/

					if($create == "ok"){

				    	$json = array(
			        	"status"=>200,
				    		"detalle"=>"Registro exitoso, su curso ha sido guardado"

				    	); 
				    	
				    	echo json_encode($json, true); 

				    	return;    	

			   	 	}











      }






      }


    }











          $json=array(

            "detalle"=>"estas en la vista create"

                            );

            echo json_encode($json,true);

                return;




    }






    public function show($id){

      /*=============================================
		Validar credenciales del cliente
		=============================================*/

    $clientes = ModeloClientes::index("clientes");

    if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){

      foreach ($clientes as $key => $valueCliente) {

        if(base64_encode($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']) == 
           base64_encode($valueCliente["id_cliente"] .":". $valueCliente["llave_secreta"])){


            /*=============================================
					Mostrar todos los cursos
					=============================================*/

          $curso = ModeloCursos::show("cursos" ,"clientes", $id);

          if(!empty($curso)){

            $json=array(

              "status"=>200,
              "detalle"=>$curso

                              );

              echo json_encode($json,true);

                  return;


          }else{

            $json = array(

				    		"status"=>200,
				    		"total_registros"=>0,
				    		"detalles"=>"No hay ningún curso registrado"
				    		
				    	);

						echo json_encode($json, true);	

						return;





          }

          

         

          

       






           }

          }

        }




    }







public function update($id, $datos)
{
    /*=============================================
    Validar credenciales del cliente
    =============================================*/
    $clientes = ModeloClientes::index("clientes");

    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

        foreach ($clientes as $valueCliente) {

            $authUser = $_SERVER['PHP_AUTH_USER'];
            $authPw = $_SERVER['PHP_AUTH_PW'];

            $encodedRequest = base64_encode($authUser . ":" . $authPw);
            $encodedStored = base64_encode($valueCliente["id_cliente"] . ":" . $valueCliente["llave_secreta"]);

            if ($encodedRequest === $encodedStored) {

                /*=============================================
                Validar datos del curso
                =============================================*/
                foreach ($datos as $key => $valueDatos) {
                    if (isset($valueDatos) && !preg_match('/^[(\\)\\=\\&\\$\\;\\-\\_\\*\\"\\<\\>\\?\\¿\\!\\¡\\:\\,\\.\\0-9a-zA-ZñÑáéíóúÁÉÍÓÚ ]+$/', $valueDatos)) {
                        echo json_encode([
                            "status" => 404,
                            "detalle" => "Error en el campo " . $key
                        ]);
                        return;
                    }
                }

                /*=============================================
                Verificar si el curso pertenece al cliente
                =============================================*/
                $curso = ModeloCursos::show("cursos", "clientes", $id);
                foreach ($curso as $valueCurso) {
                    if ($valueCurso->id_creador == $valueCliente["id"]) {

                        /*=============================================
                        Enviar datos al modelo para actualización
                        =============================================*/
                        $datosActualizados = array(
                            "id" => $id,
                            "titulo" => $datos["titulo"],
                            "descripcion" => $datos["descripcion"],
                            "instructor" => $datos["instructor"],
                            "imagen" => $datos["imagen"],
                            "precio" => $datos["precio"],
                            "updated_at" => date('Y-m-d H:i:s')
                        );

                        $update = ModeloCursos::update("cursos", $datosActualizados);

                        if ($update == "ok") {
                            echo json_encode([
                                "status" => 200,
                                "detalle" => "Registro exitoso, su curso ha sido actualizado"
                            ]);
                            return;
                        } else {
                            echo json_encode([
                                "status" => 404,
                                "detalle" => "No se pudo actualizar el curso"
                            ]);
                            return;
                        }
                    }
                }

                // Si ningún curso coincide con el cliente autenticado
                echo json_encode([
                    "status" => 403,
                    "detalle" => "No tiene permisos para editar este curso"
                ]);
                return;
            }
        }

        // Si ningún cliente coincide con las credenciales
        echo json_encode([
            "status" => 401,
            "detalle" => "Credenciales inválidas"
        ]);
        return;
    }

    // Si no se proporcionaron credenciales
    echo json_encode([
        "status" => 401,
        "detalle" => "Se requieren credenciales de autenticación"
    ]);
    return;
}





      public function delete($id){
    $clientes = ModeloClientes::index("clientes");

    if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
        foreach ($clientes as $key => $valueCliente) {
            if(
                "Basic ".base64_encode($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']) == 
                "Basic ".base64_encode($valueCliente["id_cliente"].":".$valueCliente["llave_secreta"])
            ){
                // Cliente autenticado

                $curso = ModeloCursos::show("cursos", "clientes", $id);

                // Validar que el curso exista
                if(empty($curso)) {
                    echo json_encode([
                        "status" => 404,
                        "detalle" => "Curso no encontrado"
                    ]);
                    return;
                }

                foreach ($curso as $valueCurso) {
                    if($valueCurso->id_creador == $valueCliente["id"]){
                        $delete = ModeloCursos::delete("cursos", $id);

                        if($delete == "ok"){
                            echo json_encode([
                                "status" => 200,
                                "detalle" => "Se ha borrado el curso"
                            ]);
                            return;
                        } else {
                            echo json_encode([
                                "status" => 500,
                                "detalle" => "Error al intentar eliminar el curso"
                            ]);
                            return;
                        }
                    } else {
                        echo json_encode([
                            "status" => 403,
                            "detalle" => "No tienes permiso para eliminar este curso"
                        ]);
                        return;
                    }
                }
            }
        }

        // Si ningún cliente coincide
        echo json_encode([
            "status" => 401,
            "detalle" => "Credenciales inválidas"
        ]);
        
        return;

echo json_encode($json, true);
return;

    }

    // Si no se proporcionaron credenciales
    echo json_encode([
        "status" => 401,
        "detalle" => "Se requieren credenciales de autenticación"
    ]);
    return;
}






            }










?>