<?php
require_once dirname(__DIR__, 3) . "/vendor/autoload.php";

// Cargar variables de entorno antes de iniciar la sesión
$dotenv = DotenvVault\DotenvVault::createImmutable(dirname(__DIR__, 3));
$dotenv->safeLoad();

@session_name($_ENV["APP_NAME"]);
@session_start();
/*enable this for development purpose */
//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);
date_default_timezone_set(@date_default_timezone_get());
define('PDOCrudABSPATH', dirname(__FILE__) . '/');
require_once PDOCrudABSPATH . "config/config.php";
spl_autoload_register('pdocrudAutoLoad');

function pdocrudAutoLoad($class) {
    if (file_exists(PDOCrudABSPATH . "classes/" . $class . ".php"))
        require_once PDOCrudABSPATH . "classes/" . $class . ".php";
}

if (isset($_REQUEST["pdocrud_instance"])) {
    $fomplusajax = new PDOCrudAjaxCtrl();
    $fomplusajax->handleRequest();
}

function formatTablePolos($data, $obj){
    if($data){
        foreach($data as &$item){
            if($item["rutapdf"] != ""){
                $item["rutapdf"] = "<a href='http://10.5.131.63/repositoriopolos/app/libs/script/uploads/".$item["rutapdf"]."' target='_blank' class='btn btn-info btn-sm btn-block'>Ver PDF</a>";
            }

            if($item["rutapdf2"] != ""){
                $item["rutapdf2"] = "<a href='http://10.5.131.63/repositoriopolos/app/libs/script/uploads/".$item["rutapdf2"]."' target='_blank' class='btn btn-info btn-sm btn-block'>Ver PDF</a>";
            }

            if($item["rutapdf3"] != ""){
                $item["rutapdf3"] = "<a href='http://10.5.131.63/repositoriopolos/app/libs/script/uploads/".$item["rutapdf3"]."' target='_blank' class='btn btn-info btn-sm btn-block'>Ver PDF</a>";
            }
        }
    }
    return $data;
}

function funciones_de_busqueda($data, $obj){
    if (isset($data["action"]) && $data["action"] == "search") {
        if (isset($data['search_col']) && isset($data['search_text'])) {
            $search_col = $data['search_col'];
            $search_text = $data['search_text'];
 
            if ($search_col !== 'all' && !empty($search_text)) {
                $obj->where($search_col, "%$search_text%", "LIKE");
            } else {
                $obj->where("id", "NULL");
            }
        }
    }
    return $data;
}

function funciones_de_filtro_rut($data, $obj){
    if (isset($data["action"]) && $data["action"] == "filter") {
        
        // Limpiar las condiciones WHERE anteriores
        $obj->clearWhereConditions();

        // Verificar si 'FechaDesdeFilter' y 'FechaHastaFilter' están presentes en $data
        $rut = isset($data['FilterRut']) ? $data['FilterRut'] : null;

        if (empty($rut)) {
            $obj->where('rut', "NULL");
        }

        // Filtrar por rango de fechas si ambos filtros están presentes
        elseif (!empty($rut)) {
            $obj->where('rut', $rut, "=");
        }
    }
    
    return $data;
}

function funciones_de_filtro($data, $obj){
    if (isset($data["action"]) && $data["action"] == "filter") {
        
        // Limpiar las condiciones WHERE anteriores
        $obj->clearWhereConditions();

        // Verificar si 'FechaDesdeFilter' y 'FechaHastaFilter' están presentes en $data
        $fechaDesde = isset($data['FechaInicio']) ? $data['FechaInicio'] : null;
        $fechaHasta = isset($data['FechaTermino']) ? $data['FechaTermino'] : null;

        if (empty($fechaDesde) && empty($fechaHasta)) {
            $obj->where('fechadocumento', "NULL");
        }

        // Filtrar por rango de fechas si ambos filtros están presentes
        if (!empty($fechaDesde) && !empty($fechaHasta)) {
            $obj->where('fechadocumento', $fechaDesde, ">=", "AND");
            $obj->where('fechadocumento', $fechaHasta, "<=", "AND");
        } 
        
        // Si solo se ha seleccionado una fecha desde
        elseif (!empty($fechaDesde) && empty($fechaHasta)) {
            $obj->where('fechadocumento', $fechaDesde, "=");
        }
        
        // Si solo se ha seleccionado una fecha hasta
        elseif (empty($fechaDesde) && !empty($fechaHasta)) {
            $obj->where('fechadocumento', $fechaHasta, "=");
        }
    }
    
    return $data;
}


function agregar_menu($data, $obj){
    $id_menu = $data;
    $id_usuario_session = $_SESSION["usuario"][0]["id"];

    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->insert("usuario_menu", array("id_menu" => $id_menu, "id_usuario" => $id_usuario_session, "visibilidad_menu" => "Mostrar"));

    return $data;
}

function despues_insertar_submenu($data, $obj){
    $id_submenu = $data;
    $id_usuario_session = $_SESSION["usuario"][0]["id"];

    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_submenu", $id_submenu);
    $id_menu = $pdomodel->select("submenu");
    $pdomodel->insert("usuario_submenu", array("id_menu" => $id_menu[0]["id_menu"], "id_submenu" => $id_submenu, "id_usuario" => $id_usuario_session, "visibilidad_submenu" => "Mostrar"));

    return $data;
}

function eliminar_menu($data, $obj){
    $id_menu = $data["id"];
    $id_usuario_session = $_SESSION["usuario"][0]["id"];
    
    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_menu", $id_menu);
    $pdomodel->where("id_usuario", $id_usuario_session);
    $pdomodel->delete("usuario_menu");

    $pdomodel->where("id_menu", $id_menu);
    $id_menu_db = $pdomodel->select("submenu");

    if($id_menu_db){
        $pdomodel->where("id_submenu", $id_menu_db[0]["id_submenu"]);
        $pdomodel->delete("submenu");

        $pdomodel->where("id_menu", $id_menu);
        $pdomodel->where("id_usuario", $id_usuario_session);
        $pdomodel->delete("usuario_submenu");
    }

    if(!$id_menu_db){
        $pdomodel->where("id_menu", $id_menu_db[0]["id_menu"]);
        $pdomodel->update("menu", array("submenu" => "No"));
    }

    return $data;
}

function eliminar_submenu($data, $obj){
    $id_submenu = $data["id"];
    $id_usuario_session = $_SESSION["usuario"][0]["id"];

    $pdomodel = $obj->getPDOModelObj();

    $pdomodel->where("id_submenu", $id_submenu);
    $id_menu = $pdomodel->select("submenu");

    $result = $pdomodel->DBQuery("SELECT COUNT(*) AS total FROM submenu WHERE id_menu = :id_menu", [":id_menu" => $id_menu[0]["id_menu"]]);

    $num_submenus = $result[0]["total"];

    if ($num_submenus == 0) {
        $pdomodel->where("id_menu", $id_menu[0]["id_menu"]);
        $pdomodel->update("menu", array("submenu" => "No"));
    }

    $pdomodel->where("id_submenu", $id_submenu);
    $pdomodel->where("id_usuario", $id_usuario_session);
    $pdomodel->delete("usuario_submenu");

    return $data;
}

function carga_masiva_usuarios_insertar($data, $obj){   
    $archivo = basename($data["carga_masiva_usuarios"]["archivo"]);
    $extension = pathinfo($archivo, PATHINFO_EXTENSION);

    $pdomodel = $obj->getPDOModelObj();

    if (empty($archivo)) {
        $error_msg = array("message" => "", "error" => "No se ha subido ningún Archivo", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else {
        if ($extension != "xlsx") {
            $error_msg = array("message" => "", "error" => "El Archivo Subido no es un Archivo Excel Válido", "redirectionurl" => "");
            die(json_encode($error_msg));
        } else {

            $records = $pdomodel->excelToArray("uploads/".$archivo);

            $sql = array();
            foreach ($records as $Excelval) {
                $rut_completo = $Excelval['Rut'] . '-' . $Excelval['Dv'];

                /*if (!App\Controllers\HomeController::validaRut($rut_completo)) {
                    $rutInvalidos[] = $rut_completo;
                } else {*/
                    $existingUsuario = $pdomodel->DBQuery("SELECT * FROM usuario WHERE rut = :rut", ['rut' => $rut_completo]);

                    if (!$existingUsuario) {

                        $rut_digits = substr($Excelval['Rut'], 0, 4);

                        $pass = $rut_digits;

                        $image = PDOCrudABSPATH . 'uploads/1710162578_user.png';
                        $avatar = basename($image);

                        $sql = array(
                            'nombre' => $Excelval['Nombre Funcionario'],
                            'rut' => $rut_completo,
                            'unidad' => $Excelval['Descripción Unidad'],
                            'planta' => $Excelval['Descripción Planta'],
                            'calidad_juridica' => $Excelval['Descripción Calidad Jurídica'],
                            'cargo' => $Excelval['Descripción Cargo'],
                            'password' => password_hash($pass, PASSWORD_DEFAULT),
                            'estatus' => 1,
                            'avatar' => $avatar
                        );

                        $pdomodel->insertBatch("usuario", array($sql));
                    } else {
                        $error_msg = array("message" => "", "error" => "Lo Siguientes Usuarios ingresados ya existen: ". implode(", ", $Excelval["Nombre"]), "redirectionurl" => "");
                        die(json_encode($error_msg));
                    }
                //}
            }

            /*if (!empty($rutInvalidos)) {
                $error_msg = array("message" => "", "error" => "Los siguientes Rut inválidos no han sido cargados: " . implode(", ", $rutInvalidos), "redirectionurl" => "");
                die(json_encode($error_msg));
            }*/
            $data["carga_masiva_usuarios"]["archivo"] = basename($data["carga_masiva_usuarios"]["archivo"]);
        }
    }
    return $data;
}

function carga_masiva_pacientes_insertar($data, $obj) {
    $archivo = basename($data["carga_masiva_pacientes"]["archivo"]);
    $extension = pathinfo($archivo, PATHINFO_EXTENSION);

    $pdomodel = $obj->getPDOModelObj();

    $rutInvalidos = [];
    $nombresInvalidos = [];
    $fechasInvalidas = [];

    if (empty($archivo)) {
        $error_msg = array("message" => "", "error" => "No se ha subido ningún Archivo", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else {
        if ($extension != "xlsx") {
            $error_msg = array("message" => "", "error" => "El Archivo Subido no es un Archivo Excel Válido", "redirectionurl" => "");
            die(json_encode($error_msg));
        } else {
            $records = $pdomodel->excelToArray("uploads/" . $archivo);

            foreach ($records as $Excelval) {
                $rut = $Excelval['Rut'];
                $nombre = $Excelval['Nombre'];

                // Verificación de RUT inválido
                if (!App\Controllers\HomeController::validaRut($rut)) {
                    $rutInvalidos[] = $rut;
                }

                // Verificación de nombre vacío o inválido
                if (empty($nombre) || !preg_match('/^[\p{L}\s\'-]+$/u', $nombre)) {
                    $nombresInvalidos[] = $rut; // Guardamos el RUT asociado al nombre inválido
                }

                // Verificación de fecha de egreso
                if (!empty($Excelval['Fecha Egreso'])) {
                    try {
                        $fecha_egreso = new DateTime($Excelval['Fecha Egreso']);
                    } catch (Exception $e) {
                        $fechasInvalidas[] = $rut; // Guardamos el RUT asociado a la fecha inválida
                    }
                }

                // Si hay errores, detener el proceso
                if (!empty($rutInvalidos) || !empty($nombresInvalidos) || !empty($fechasInvalidas)) {
                    continue; // Opción para continuar con el siguiente registro, puedes ajustar según necesidad
                }

                // Verificación de existencia del paciente
                $existingPacient = $pdomodel->DBQuery("SELECT * FROM datos_paciente WHERE rut = :rut", ['rut' => trim($Excelval['Rut'])]);

                if (!$existingPacient) {
                    try {
                        $fecha_nacimiento = new DateTime($Excelval['Fecha Nacimiento']);
                    } catch (Exception $e) {
                        $error_msg = array("message" => "", "error" => "Fecha de Nacimiento inválida para el Rut: " . $rut, "redirectionurl" => "");
                        die(json_encode($error_msg));
                    }

                    $fecha_actual = new DateTime();
                    $diferencia = $fecha_actual->diff($fecha_nacimiento);
                    $edad = $diferencia->y;

                    $sql = array(
                        'rut' => $Excelval['Rut'],
                        'nombres' => $Excelval['Nombre'],
                        'telefono' => $Excelval['Teléfono'],
                        'apellido_paterno' => $Excelval['Apellido Paterno'],
                        'apellido_materno' => $Excelval['Apellido Materno'],
                        'edad' => $edad,
                        'fecha_nacimiento' => $Excelval['Fecha Nacimiento'],
                        'direccion' => $Excelval['Dirección'],
                        'sexo' => $Excelval['Sexo'],
                    );

                    if (!empty($Excelval['Fecha y hora Ingreso'])) {
                        $sql['fecha_y_hora_ingreso'] = date("Y-m-d", strtotime($Excelval['Fecha y hora Ingreso']));
                    }

                    $pdomodel->insertBatch("datos_paciente", array($sql));
                    $id_datos_paciente = $pdomodel->lastInsertId;
                } else {
                    $id_datos_paciente = $existingPacient[0]["id_datos_paciente"];
                }

                date_default_timezone_set('America/Santiago');
                $fecha_actual = date('Y-m-d');
                $usuario = $_SESSION['usuario'][0]["usuario"];

                $sql_detalle = array(
                    'id_datos_paciente' => $id_datos_paciente,
                    'codigo_fonasa' => trim($Excelval["Codigo Fonasa"]),
                    'tipo_solicitud' => trim($Excelval["Tipo Solicitud"]),
                    'tipo_examen' => trim($Excelval["Tipo Exámen"]),
                    'examen' => trim($Excelval['Exámen']),
                    'plano' => trim($Excelval['Plano']),
                    'extremidad' => trim($Excelval['Extremidad']),
                    'observacion' => trim($Excelval['Observación']),
                    'contraste' => trim($Excelval['Contraste']),
                    'creatinina' => trim($Excelval['Cratinina']),
                    'estado' => trim($Excelval['Estado']),
                    'motivo_egreso' => trim($Excelval['Motivo Egreso']),
                    'usuario' => $usuario,
                    'fecha_ingreso' => $fecha_actual
                );

                if (!empty(trim($Excelval['Fecha Solicitud']))) {
                    $sql_detalle['fecha_solicitud'] = date("Y-m-d", strtotime(trim($Excelval['Fecha Solicitud'])));
                }
                if (!empty(trim($Excelval['Fecha Agendada'])) || !empty(trim($Excelval['Hora']))) {
                    $sql_detalle['fecha'] = date("Y-m-d H:i:s", strtotime(trim($Excelval['Fecha Agendada']) . " " . trim($Excelval['Hora'])));
                }
                if (!empty(trim($Excelval['Fecha Egreso']))) {
                    $sql_detalle['fecha_egreso'] = date("Y-m-d", strtotime(trim($Excelval['Fecha Egreso'])));
                }

                $pdomodel->insertBatch("detalle_de_solicitud", array($sql_detalle));

                $sql_diag = array(
                    'id_datos_paciente' => $id_datos_paciente,
                    'profesional' => trim($Excelval['Profesional']),
                    'especialidad' => trim($Excelval['Especialidad']),
                    'diagnostico_libre' => trim($Excelval['Diagnóstico Libre'])
                );

                if (!empty($Excelval['Fecha Solicitud'])) {
                    $sql_diag['fecha_solicitud_paciente'] = date("Y-m-d", strtotime($Excelval['Fecha Solicitud']));
                }

                $pdomodel->insertBatch("diagnostico_antecedentes_paciente", array($sql_diag));
            }

            if (!empty($rutInvalidos) || !empty($nombresInvalidos) || !empty($fechasInvalidas)) {
                $error_details = [];

                if (!empty($rutInvalidos)) {
                    $error_details[] = "RUTs inválidos: " . implode(", ", $rutInvalidos);
                }

                if (!empty($nombresInvalidos)) {
                    $error_details[] = "Nombres inválidos asociados a RUTs: " . implode(", ", $nombresInvalidos);
                }

                if (!empty($fechasInvalidas)) {
                    $error_details[] = "Fechas de egreso inválidas asociadas a RUTs: " . implode(", ", $fechasInvalidas);
                }

                $error_msg = array("message" => "", "error" => implode(" | ", $error_details), "redirectionurl" => "");
                die(json_encode($error_msg));
            }

            $data["carga_masiva_pacientes"]["archivo"] = basename($data["carga_masiva_pacientes"]["archivo"]);
        }
    }

    return $data;
}

function carga_masiva_codigo_insertar($data, $obj){
    $archivo = basename($data["carga_masiva_codigo"]["archivo"]);
    $extension = pathinfo($archivo, PATHINFO_EXTENSION);

    $pdomodel = $obj->getPDOModelObj();

    if (empty($archivo)) {
        $error_msg = array("message" => "", "error" => "No se ha subido ningún Archivo", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else {
        if ($extension != "xlsx") { /* comprobamos si la extensión del archivo es diferente de excel */
            //unlink(__DIR__ . "/uploads/".$archivo); /* eliminamos el archivo que se subió */
            $error_msg = array("message" => "", "error" => "El Archivo Subido no es un Archivo Excel Válido", "redirectionurl" => "");
            die(json_encode($error_msg));
        } else {

            $records = $pdomodel->excelToArray("uploads/".$archivo); /* Acá capturamos el nombre del archivo excel a importar */

            $sql = array();
            foreach ($records as $Excelval) {
                $sql['codigo_o'] = $Excelval['Código'];
                $sql['operacion'] = $Excelval['Descripción'];
                $pdomodel->insertBatch("codigo", array($sql));
            }
            $data["carga_masiva_codigo"]["archivo"] = basename($data["carga_masiva_codigo"]["archivo"]);
        }
    }
    return $data;
}

function carga_masiva_profesionales_insertar($data, $obj){
    $archivo = basename($data["carga_masiva_profesionales"]["archivo"]);
    $extension = pathinfo($archivo, PATHINFO_EXTENSION);

    $pdomodel = $obj->getPDOModelObj();
   
    if (empty($archivo)) { 
        $error_msg = array("message" => "", "error" => "No se ha subido ningún Archivo", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else {
        if ($extension != "xlsx") { /* comprobamos si la extensión del archivo es diferente de excel */
            //unlink(__DIR__ . "/uploads/".$archivo); /* eliminamos el archivo que se subió */
            $error_msg = array("message" => "", "error" => "El Archivo Subido no es un Archivo Excel Válido", "redirectionurl" => "");
            die(json_encode($error_msg));
        } else {

            $records = $pdomodel->excelToArray("uploads/".$archivo); /* Acá capturamos el nombre del archivo excel a importar */

            $sql = array();
            foreach ($records as $Excelval) {

                $existingProfesionales = $pdomodel->DBQuery(
                    "SELECT * FROM profesional 
                    WHERE nombre_profesional = :nombre_profesional 
                    AND apellido_profesional = :apellido_profesional",
                    [
                        'nombre_profesional' => $Excelval['Nombre Profesional'], 
                        'apellido_profesional' => $Excelval['Apellido Profesional']
                    ]
                );

                if(!$existingProfesionales){
                    $sql['nombre_profesional'] = $Excelval['Nombre Profesional'];
                    $sql['apellido_profesional'] = $Excelval['Apellido Profesional'];
                    $sql['rut_profesional'] = $Excelval['Rut Profesional'];

                    $pdomodel->insertBatch("profesional", array($sql));
                }
            }
            $data["carga_masiva_profesionales"]["archivo"] = basename($data["carga_masiva_profesionales"]["archivo"]);
        }
    }
    return $data;
}


function carga_masiva_prestaciones_insertar($data, $obj){
    $archivo = basename($data["carga_masiva_prestaciones"]["archivo"]);
    $extension = pathinfo($archivo, PATHINFO_EXTENSION);

    $pdomodel = $obj->getPDOModelObj();
   
    if (empty($archivo)) { 
        $error_msg = array("message" => "", "error" => "No se ha subido ningún Archivo", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else {
        if ($extension != "xlsx") { /* comprobamos si la extensión del archivo es diferente de excel */
            //unlink(__DIR__ . "/uploads/".$archivo); /* eliminamos el archivo que se subió */
            $error_msg = array("message" => "", "error" => "El Archivo Subido no es un Archivo Excel Válido", "redirectionurl" => "");
            die(json_encode($error_msg));
        } else {

            $records = $pdomodel->excelToArray("uploads/".$archivo); /* Acá capturamos el nombre del archivo excel a importar */

            $sql = array();
            foreach ($records as $Excelval) {
                $sql['tipo_solicitud'] = $Excelval['TIPO SOLICITUD'];
                $sql['especialidad'] = $Excelval['ESPECIALIDAD'];
                $sql['tipo_de_examen'] = $Excelval['TIPO DE EXAMEN'];
                $sql['examen'] = $Excelval['EXAMEN'];
                $sql['codigo_fonasa'] = $Excelval['CODIGO FONASA'];
                $sql['glosa'] = $Excelval['GLOSA'];

                $pdomodel->insertBatch("prestaciones", array($sql));
            }
            $data["carga_masiva_prestaciones"]["archivo"] = basename($data["carga_masiva_prestaciones"]["archivo"]);
        }
    }
    return $data;
}

function insertar_detalle_solicitud($data, $obj){
    return $data;
}

function insertar_procedimientos($data, $obj){
    $rut = $data["procedimiento"]["rut"];
    $fecha_solicitud = $data["procedimiento"]["fecha_solicitud"];
    $especialidad = $data["procedimiento"]["procedimiento"];
    $procedimiento_2 = $data["procedimiento"]["procedimiento_2"];
    $servicio = $data["procedimiento"]["servicio"];
    $fecha_registro = $data["procedimiento"]["fecha_registro"];
    $nombres = $data["procedimiento"]["nombres"];
    $apellido_paterno = $data["procedimiento"]["apellido_paterno"];
    $apellido_materno = $data["procedimiento"]["apellido_materno"];
    $operacion = $data["procedimiento"]["operacion"];
    $profesional_solicitante = $data["procedimiento"]["profesional_solicitante"];
    $numero_contacto = $data["procedimiento"]["numero_contacto"];
    $numero_contacto_2 = $data["procedimiento"]["numero_contacto_2"];
    $prioridad = $data["procedimiento"]["prioridad"];

    if(empty($rut) && empty($especialidad) && empty($procedimiento_2) && empty($servicio) && empty($nombres) && empty($apellido_paterno) && empty($apellido_materno) && empty($operacion) && empty($profesional_solicitante) && empty($numero_contacto) && empty($numero_contacto_2) && empty($prioridad)){
        $error_msg = array("message" => "", "error" => "Todos los campos son obligatorios", "redirectionurl" => "");
        die(json_encode($error_msg));
    }

    $newdata = array();
    $newdata["procedimiento"]["rut"] = $rut;
    $newdata["procedimiento"]["fecha_solicitud"] = $fecha_solicitud;
    $newdata["procedimiento"]["procedimiento"] = $procedimiento;
    $newdata["procedimiento"]["procedimiento_2"] = $procedimiento_2;
    $newdata["procedimiento"]["servicio"] = $servicio;
    $newdata["procedimiento"]["fecha_registro"] = $fecha_registro;
    $newdata["procedimiento"]["nombres"] = $nombres;
    $newdata["procedimiento"]["apellido_paterno"] = $apellido_paterno;
    $newdata["procedimiento"]["apellido_materno"] = $apellido_materno;
    $newdata["procedimiento"]["operacion"] = $operacion;
    $newdata["procedimiento"]["profesional_solicitante"] = $profesional_solicitante;
    $newdata["procedimiento"]["numero_contacto"] = $numero_contacto;
    $newdata["procedimiento"]["numero_contacto_2"] = $numero_contacto_2;
    $newdata["procedimiento"]["prioridad"] = $prioridad;

    return $newdata;
}

function delete_file_data($data, $obj)
{
    $id = $data['id'];
    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->fetchType = "OBJ";
    $pdomodel->where("id", $id);
    $result = $pdomodel->select("backup");

    $file_sql = $result[0]->archivo;

    $file_crop = "uploads/".$file_sql;

    if (file_exists($file_crop)) {
        unlink($file_crop);
        echo "<script>
        Swal.fire({
            title: 'Genial!',
            text: 'Respaldo Eliminado con éxito',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
        </script>";
    }
    return $data;
}

function eliminar_detalle_solicitud($data, $obj){
    $id = $data["id"];
    
    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_session_data_detalle_de_solicitud", $id);
    $session_data_detalle_de_solicitud = $pdomodel->select("session_data_detalle_de_solicitud");

    $file_sql = $session_data_detalle_de_solicitud[0]["adjuntar"];
    
    $file_crop = "uploads/". basename($file_sql);

    if (file_exists($file_crop)) {
        unlink($file_crop);
        echo "<script>
        Swal.fire({
            title: 'Genial!',
            text: 'Dato Eliminado con éxito',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
        </script>";
    }
    return $data;
}

function before_sql_data_estat($data, $obj){
    //print_r($data);
    return $data;
}

/*function editar_procedimientos($data, $obj){
    $id_datos_paciente = $data['datos_paciente']['id_datos_paciente'];
    $estado = $data["detalle_de_solicitud"]["estado"];
    $fecha = $data["detalle_de_solicitud"]["fecha"];
    $fecha_solicitud = $data["detalle_de_solicitud"]["fecha_solicitud"];
    $fundamento = $data['diagnostico_antecedentes_paciente']['fundamento'];
    $adjuntar = $data['diagnostico_antecedentes_paciente']['adjuntar'];
    $id_detalle_de_solicitud = $data["detalle_de_solicitud"]["id_detalle_de_solicitud"];
    $id_diagnostico_antecedentes_paciente = $data["diagnostico_antecedentes_paciente"]["id_diagnostico_antecedentes_paciente"];
 
    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_detalle_de_solicitud", $id_detalle_de_solicitud, "=");
    $data_detalle = $pdomodel->select("detalle_de_solicitud");
   
    $pdomodel->where("id_diagnostico_antecedentes_paciente", $id_diagnostico_antecedentes_paciente, "=");
    $data_diagnostico = $pdomodel->select("diagnostico_antecedentes_paciente");
    
    if($data_detalle && $data_diagnostico){
        $pdomodel->where("id_detalle_de_solicitud", $id_detalle_de_solicitud, "=", "AND");
        $pdomodel->update("detalle_de_solicitud", array("fecha" => $fecha, "estado" => $estado));

        $pdomodel->where("id_diagnostico_antecedentes_paciente", $id_diagnostico_antecedentes_paciente);
        $pdomodel->update("diagnostico_antecedentes_paciente", array("fundamento" => $fundamento, "adjuntar" => basename($adjuntar)));

        $success = array("message" => "Operación realizada con éxito", "error" => [], "redirectionurl" => "");
        die(json_encode($success));
    }

    $newdata = array();
    $newdata['datos_paciente']['id_datos_paciente'] = $id_datos_paciente;
    $newdata['diagnostico_antecedentes_paciente']['estado'] = $estado;
    $newdata['diagnostico_antecedentes_paciente']['diagnostico'] = $data['diagnostico_antecedentes_paciente']['diagnostico'];

    return $newdata;
}*/

function editar_procedimientos($data, $obj){
    $id_detalle_de_solicitud = $data["detalle_de_solicitud"]["id_detalle_de_solicitud"];
    $id_datos_paciente = $data["datos_paciente"]["id_datos_paciente"];
    $estado = $data["detalle_de_solicitud"]["estado"];
    $fecha = $data["detalle_de_solicitud"]["fecha"];
    $fecha_solicitud = $data["detalle_de_solicitud"]["fecha_solicitud"];
    $diagnostico = $data["diagnostico_antecedentes_paciente"]["diagnostico"];
    $fundamento = $data["detalle_de_solicitud"]["fundamento"];

    if(empty($estado)){
        $error_msg = array("message" => "", "error" => "Ingrese un estado", "redirectionurl" => "");
        die(json_encode($error_msg));
    }

    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_detalle_de_solicitud", $id_detalle_de_solicitud);
    $pdomodel->update("detalle_de_solicitud", array(
        "estado" => $estado, 
        "fecha" => $fecha,
        "fundamento" => $fundamento
    ));

    $pdomodel->where("id_datos_paciente", $id_datos_paciente);
    $pdomodel->where("fecha_solicitud_paciente", $fecha_solicitud);
    $pdomodel->update("diagnostico_antecedentes_paciente", $datos = array(
        "diagnostico" => $diagnostico
    ));
    
    return $data;
}

function editar_egresar_solicitud($data, $obj) {
    $id_detalle_de_solicitud = $data["detalle_de_solicitud"]["id_detalle_de_solicitud"];
    $id_datos_paciente = $data['datos_paciente']['id_datos_paciente'];
    $fecha_egreso = $data['detalle_de_solicitud']['fecha_egreso'];
    $motivo_egreso = $data['detalle_de_solicitud']['motivo_egreso'];
    $observacion = $data['detalle_de_solicitud']['observacion'];
    $compra_servicio = isset($data["detalle_de_solicitud"]["compra_servicio"]) ? $data["detalle_de_solicitud"]["compra_servicio"] : "2";
    $empresas_en_convenio = isset($data["detalle_de_solicitud"]["empresas_en_convenio"]) ? $data["detalle_de_solicitud"]["empresas_en_convenio"] : null;
    $adjuntar = $data["detalle_de_solicitud"]["adjuntar"];
    $adjuntar2 = $data["detalle_de_solicitud"]["adjuntar2"];
    $adjuntar3 = $data["detalle_de_solicitud"]["adjuntar3"];
    $adjuntar4 = $data["detalle_de_solicitud"]["adjuntar4"];

    if(empty($fecha_egreso)){
        $error_msg = array("message" => "", "error" => "Ingrese una Fecha de egreso", "redirectionurl" => "");
        die(json_encode($error_msg));
    }

    if(empty($motivo_egreso)){
        $error_msg = array("message" => "", "error" => "Ingrese un Motivo de egreso", "redirectionurl" => "");
        die(json_encode($error_msg));
    }

    if (!empty($adjuntar)) {
        $extension = pathinfo($adjuntar, PATHINFO_EXTENSION);
        if($extension == "zip" || $extension == "rar" || $extension == "exe"){
            $error_msg = array("message" => "", "error" => "El Archivo Adjunto no puede ser ni Zip ni RAR ni exe", "redirectionurl" => "");
            die(json_encode($error_msg));
        }
    }

    if (!empty($adjuntar2)) {
        $extension = pathinfo($adjuntar2, PATHINFO_EXTENSION);
        if($extension == "zip" || $extension == "rar" || $extension == "exe"){
            $error_msg = array("message" => "", "error" => "El Archivo Adjunto no puede ser ni Zip ni RAR ni exe", "redirectionurl" => "");
            die(json_encode($error_msg));
        }
    }

    if (!empty($adjuntar3)) {
        $extension = pathinfo($adjuntar3, PATHINFO_EXTENSION);
        if($extension == "zip" || $extension == "rar" || $extension == "exe"){
            $error_msg = array("message" => "", "error" => "El Archivo Adjunto no puede ser ni Zip ni RAR ni exe", "redirectionurl" => "");
            die(json_encode($error_msg));
        }
    }

    if (!empty($adjuntar4)) {
        $extension = pathinfo($adjuntar4, PATHINFO_EXTENSION);
        if($extension == "zip" || $extension == "rar" || $extension == "exe"){
            $error_msg = array("message" => "", "error" => "El Archivo Adjunto no puede ser ni Zip ni RAR ni exe", "redirectionurl" => "");
            die(json_encode($error_msg));
        }
    }

    $fecha_solicitud = $data['detalle_de_solicitud']['fecha_solicitud'];

    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_detalle_de_solicitud", $id_detalle_de_solicitud);
    $pdomodel->update("detalle_de_solicitud", array(
        "fecha_egreso" => $fecha_egreso,
        "motivo_egreso" => $motivo_egreso,
        "observacion" => $observacion,
        "compra_servicio" => $compra_servicio,
        "empresas_en_convenio" => $empresas_en_convenio,
        "adjuntar" => basename($adjuntar),
        "adjuntar2" => basename($adjuntar2),
        "adjuntar3" => basename($adjuntar3),
        "adjuntar4" => basename($adjuntar4),
        "estado" => "Egresado"
    ));
    return $data;
}


function formatTableDetalleSolicitud($data, $obj){
    if($data){
        for ($i = 0; $i < count($data); $i++) {
            if(!empty($data[$i]["adjuntar"])){
                $data[$i]["adjuntar"] = "<a href=".$data[$i]["adjuntar"]." target='_blank'><i class='fa fa-file fa-lg'></i></a>";
            } else {
                $data[$i]["adjuntar"] = "<div class='badge badge-danger'>Sin Adjunto</div>";
            }

            if(!empty($data[$i]["plano"])){
                $data[$i]["plano"] = $data[$i]["plano"];
            } else {
                $data[$i]["plano"] = "<div class='badge badge-danger'>Sin Plano</div>";
            }

            if(!empty($data[$i]["extremidad"])){
                $data[$i]["extremidad"] = $data[$i]["extremidad"];
            } else {
                $data[$i]["extremidad"] = "<div class='badge badge-danger'>Sin Extremidad</div>";
            }

            if(!empty($data[$i]["procedencia"])){
                $data[$i]["procedencia"] = $data[$i]["procedencia"];
            } else {
                $data[$i]["procedencia"] = "<div class='badge badge-danger'>Sin Procedencia</div>";
            }

            if(!empty($data[$i]["contraste"])){
                $data[$i]["contraste"] = $data[$i]["contraste"];
            } else {
                $data[$i]["contraste"] = "<div class='badge badge-danger'>Sin Contraste</div>";
            }
        }
    }
    return $data;
}

function formatTable_datos_paciente($data, $obj){
    if($data){
        for ($i = 0; $i < count($data); $i++) {
            if($data[$i]["fecha_y_hora_ingreso"] != "0000-00-00 00:00:00"){
                $data[$i]["fecha_y_hora_ingreso"] = "<div class='badge badge-success'>" . $data[$i]["fecha_y_hora_ingreso"] . "</div>";
            } else {
                $data[$i]["fecha_y_hora_ingreso"] = "<div class='badge badge-success'>Sin Fecha</div>";
            }

            if($data[$i]["edad"] == "0"){
                $data[$i]["edad"] = "<div class='badge badge-danger'>Sin Edad</div>";
            } else {
                $data[$i]["edad"] = $data[$i]["edad"];
            }
        }
    }
    return $data;
}


function editar_lista_examenes_notas($data, $obj){
    $id_datos_paciente = $data["datos_paciente"]["id_datos_paciente"];
    $fecha_solicitud = $data["detalle_de_solicitud"]["fecha_solicitud"];
    $observacion = $data["detalle_de_solicitud"]["observacion"];

    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_datos_paciente", $id_datos_paciente);
    $pdomodel->where("fecha_solicitud", $fecha_solicitud);
    $pdomodel->update("detalle_de_solicitud", array(
        "observacion" => $observacion
    ));
   
    return $data;
}

function editar_lista_examenes_modificar($data, $obj){
    $id_datos_paciente = $data["datos_paciente"]["id_datos_paciente"];
    $id_detalle_de_solicitud = $data["detalle_de_solicitud"]["id_detalle_de_solicitud"];
    $fecha_solicitud = $data["detalle_de_solicitud"]["fecha_solicitud"];
    $tipo_solicitud = $data["detalle_de_solicitud"]["tipo_solicitud"];
    $tipo_examen = $data["detalle_de_solicitud"]["tipo_examen"];
    $examen = $data["detalle_de_solicitud"]["examen"];

    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_datos_paciente", $id_datos_paciente, "=", "AND");
    $pdomodel->where("id_detalle_de_solicitud", $id_detalle_de_solicitud);
    $pdomodel->update("detalle_de_solicitud", array(
        "fecha_solicitud" => $fecha_solicitud,
        "tipo_solicitud" => $tipo_solicitud,
        "tipo_examen" => $tipo_examen,
        "examen" => $examen
    ));

    $pdomodel->where("id_datos_paciente", $id_datos_paciente);
    $pdomodel->update("diagnostico_antecedentes_paciente", array(
        "fecha_solicitud_paciente" => $fecha_solicitud
    ));
   return $data;
}

function formatTable_buscar_examenes($data, $obj){
    if($data){
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["nombres"] = ucwords($data[$i]["nombres"]) . " " .  ucwords($data[$i]["apellido_paterno"]) . " " . ucwords($data[$i]["apellido_materno"]);

            if($data[$i]["fecha_y_hora_ingreso"] == "0000-00-00 00:00:00"){
                $data[$i]["fecha_y_hora_ingreso"] = "<div class='badge badge-danger'>Sin Fecha</div>";
            } else {
                $data[$i]["fecha_y_hora_ingreso"] = date('d/m/Y H:i:s', strtotime($data[$i]["fecha_y_hora_ingreso"]));
            }

            if($data[$i]["fecha"] != null){
                $data[$i]["fecha"] = date('d/m/Y', strtotime($data[$i]["fecha"]));
            } else {
                $data[$i]["fecha"] = "<div class='badge badge-danger'>Sin Fecha</div>";
            }

            $data[$i]["profesional"] = ucwords($data[$i]["profesional"]);
        }
    }
    return $data;
}

function insertar_modulos($data, $obj)
{
    $newdata = array();
    $newdata["modulos"]["tabla"] = $data["modulos"]["tabla"];
    $newdata["modulos"]["activar_filtro_de_busqueda"] = $data["modulos"]["activar_filtro_de_busqueda"];
    $newdata["modulos"]["botones_de_accion"] = $data["modulos"]["botones_de_accion"];
    $newdata["modulos"]["activar_buscador"] = $data["modulos"]["activar_buscador"];
    if (isset($data["modulos"]["botones_de_exportacion"])) {
        $newdata["modulos"]["botones_de_exportacion"] = $data["modulos"]["botones_de_exportacion"];
    }
    $newdata["modulos"]["activar_eliminacion_multiple"] = $data["modulos"]["activar_eliminacion_multiple"];
    $newdata["modulos"]["activar_modo_popup"] = $data["modulos"]["activar_modo_popup"];
    $newdata["modulos"]["seleccionar_skin"] = $data["modulos"]["seleccionar_skin"];
    $newdata["modulos"]["seleccionar_template"] = $data["modulos"]["seleccionar_template"];

    $newdata["modulos"]["nombre_funcion_antes_de_insertar"] = $data["modulos"]["nombre_funcion_antes_de_insertar"];
    $newdata["modulos"]["nombre_funcion_despues_de_insertar"] = $data["modulos"]["nombre_funcion_despues_de_insertar"];
    $newdata["modulos"]["nombre_funcion_antes_de_actualizar"] = $data["modulos"]["nombre_funcion_antes_de_actualizar"];
    $newdata["modulos"]["nombre_funcion_despues_de_actualizar"] = $data["modulos"]["nombre_funcion_despues_de_actualizar"];
    $newdata["modulos"]["nombre_funcion_antes_de_eliminar"] = $data["modulos"]["nombre_funcion_antes_de_eliminar"];
    $newdata["modulos"]["nombre_funcion_despues_de_eliminar"] = $data["modulos"]["nombre_funcion_despues_de_eliminar"];
    $newdata["modulos"]["nombre_funcion_antes_de_actualizar_gatillo"] = $data["modulos"]["nombre_funcion_antes_de_actualizar_gatillo"];
    $newdata["modulos"]["nombre_funcion_despues_de_actualizar_gatillo"] = $data["modulos"]["nombre_funcion_despues_de_actualizar_gatillo"];
    $newdata["modulos"]["script_js"] = $data["modulos"]["script_js"];

    $newdata["campos"]["nombre"] = $data["campos"]["nombre"];
    $newdata["campos"]["nulo"] = $data["campos"]["nulo"];
    $newdata["campos"]["visibilidad_formulario"] = $data["campos"]["visibilidad_formulario"];
    $newdata["campos"]["visibilidad_busqueda"] = $data["campos"]["visibilidad_busqueda"];
    $newdata["campos"]["visibilidad_de_filtro_busqueda"] = $data["campos"]["visibilidad_de_filtro_busqueda"];
    $newdata["campos"]["visibilidad_grilla"] = $data["campos"]["visibilidad_grilla"];
    $newdata["campos"]["indice"] = $data["campos"]["indice"];
    $newdata["campos"]["autoincrementable"] = $data["campos"]["autoincrementable"];
    $newdata["campos"]["tipo"] = $data["campos"]["tipo"];
    $newdata["campos"]["longitud"] = $data["campos"]["longitud"];
    $newdata["campos"]["tipo_de_campo"] = $data["campos"]["tipo_de_campo"];

    $tabla = $newdata["modulos"]["tabla"];
    $nombre = $newdata["campos"]["nombre"];
    $nulo = $newdata["campos"]["nulo"];
    $indice = $newdata["campos"]["indice"];
    $autoincrementable = $newdata["campos"]["autoincrementable"];
    $tipo = $newdata["campos"]["tipo"];
    $longitud = $newdata["campos"]["longitud"];

    $result = [];
    for ($i = 0; $i < count($nombre); $i++) {
        if ($tipo[$i] == "TEXT" || $tipo[$i] == "DATE" && $nulo[$i] != "si") {
            $result[] = $nombre[$i] . ' ' . $tipo[$i] . ' ' . $longitud[$i] . ' ' . $nulo[$i];
        } else {
            if (isset($autoincrementable[$i]) || isset($indice[$i])) {
                $result[] = $nombre[$i] . ' ' . $tipo[$i] . '(' . $longitud[$i] . ')' . ' ' . $autoincrementable[$i] . ' ' . $indice[$i];
            } else {
                $result[] = $nombre[$i] . ' ' . $tipo[$i] . '(' . $longitud[$i] . ')';
            }
        }
    }
    $result_data = implode(",", $result);

    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->create_table($tabla, array($result_data));
    //echo $pdomodel->getLastQuery();
    //die();
    
    return $newdata;
}

function actualizar_modulo($data, $obj) {
    $newdata = array();
    $newdata["modulos"]["tabla"] = $data["modulos"]["tabla"];
    $newdata["modulos"]["activar_filtro_de_busqueda"] = $data["modulos"]["activar_filtro_de_busqueda"];
    $newdata["modulos"]["botones_de_accion"] = $data["modulos"]["botones_de_accion"];
    $newdata["modulos"]["activar_buscador"] = $data["modulos"]["activar_buscador"];
    if (isset($data["modulos"]["botones_de_exportacion"])) {
        $newdata["modulos"]["botones_de_exportacion"] = $data["modulos"]["botones_de_exportacion"];
    }
    $newdata["modulos"]["activar_eliminacion_multiple"] = $data["modulos"]["activar_eliminacion_multiple"];
    $newdata["modulos"]["activar_modo_popup"] = $data["modulos"]["activar_modo_popup"];
    $newdata["modulos"]["seleccionar_skin"] = $data["modulos"]["seleccionar_skin"];
    $newdata["modulos"]["seleccionar_template"] = $data["modulos"]["seleccionar_template"];

    $newdata["modulos"]["nombre_funcion_antes_de_insertar"] = $data["modulos"]["nombre_funcion_antes_de_insertar"];
    $newdata["modulos"]["nombre_funcion_despues_de_insertar"] = $data["modulos"]["nombre_funcion_despues_de_insertar"];
    $newdata["modulos"]["nombre_funcion_antes_de_actualizar"] = $data["modulos"]["nombre_funcion_antes_de_actualizar"];
    $newdata["modulos"]["nombre_funcion_despues_de_actualizar"] = $data["modulos"]["nombre_funcion_despues_de_actualizar"];
    $newdata["modulos"]["nombre_funcion_antes_de_eliminar"] = $data["modulos"]["nombre_funcion_antes_de_eliminar"];
    $newdata["modulos"]["nombre_funcion_despues_de_eliminar"] = $data["modulos"]["nombre_funcion_despues_de_eliminar"];
    $newdata["modulos"]["nombre_funcion_antes_de_actualizar_gatillo"] = $data["modulos"]["nombre_funcion_antes_de_actualizar_gatillo"];
    $newdata["modulos"]["nombre_funcion_despues_de_actualizar_gatillo"] = $data["modulos"]["nombre_funcion_despues_de_actualizar_gatillo"];
    $newdata["modulos"]["script_js"] = $data["modulos"]["script_js"];

    $newdata["campos"]["nombre"] = $data["campos"]["nombre"];
    $newdata["campos"]["nulo"] = $data["campos"]["nulo"];
    $newdata["campos"]["visibilidad_formulario"] = $data["campos"]["visibilidad_formulario"];
    $newdata["campos"]["visibilidad_busqueda"] = $data["campos"]["visibilidad_busqueda"];
    $newdata["campos"]["visibilidad_de_filtro_busqueda"] = $data["campos"]["visibilidad_de_filtro_busqueda"];
    $newdata["campos"]["visibilidad_grilla"] = $data["campos"]["visibilidad_grilla"];
    $newdata["campos"]["indice"] = $data["campos"]["indice"];
    $newdata["campos"]["autoincrementable"] = $data["campos"]["autoincrementable"];
    $newdata["campos"]["tipo"] = $data["campos"]["tipo"];
    $newdata["campos"]["longitud"] = $data["campos"]["longitud"];
    $newdata["campos"]["tipo_de_campo"] = $data["campos"]["tipo_de_campo"];

    $tabla = $newdata["modulos"]["tabla"];
    $nombre = $newdata["campos"]["nombre"];
    $nulo = $newdata["campos"]["nulo"];
    $indice = $newdata["campos"]["indice"];
    $autoincrementable = $newdata["campos"]["autoincrementable"];
    $tipo = $newdata["campos"]["tipo"];
    $longitud = $newdata["campos"]["longitud"];

    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("tabla", $tabla, "!=");
    $tabla_db = $pdomodel->select("modulos");

    if($tabla_db){
        $pdomodel->renameTable($tabla_db[0]["tabla"], $tabla);
    }

   foreach($nombre as $nombres){
       $pdomodel->columns = array("nombre");
       $pdomodel->where("nombre", $nombres, "!=");
       $campos_db = $pdomodel->select("campos");
       print_r($campos_db);
       die();
    }


    $columnNames = $pdomodel->tableFieldInfo($tabla);

    for ($i = 0; $i < count($columnNames); $i++) {
        $fieldName = $columnNames[$i]['Field'];

        // Verifica si el campo existe en la base de datos
        if (!in_array($fieldName, $nombre)) {
            $nombre_antiguo = $fieldName;
            $pdomodel->Query("ALTER TABLE $tabla CHANGE $nombre_antiguo $nombre[$i] $tipo[$i]");
        }
    }
    return $newdata;
}

function eliminar_modulo($data, $obj)
{
    $id = $data["id"];
    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_modulos", $id);
    $query = $pdomodel->select("modulos");
    $tabla = $query[0]["tabla"];
    $pdomodel->dropTable($tabla);
    return $data;
}

function editar_perfil($data, $obj){
    $token = $_POST['auth_token'];
    $valid = App\core\Token::verifyFormToken('send_message', $token);
    if (!$valid) {
        echo "El token recibido no es válido";
        die();
    }

    $id     = $data["usuario"]["id"];
    $nombre = $data["usuario"]["nombre"];
    $email  = $data["usuario"]["email"];
    $user   = $data["usuario"]["usuario"];
    $clave  = $data["usuario"]["password"];
    $rol    = $data["usuario"]["idrol"];

    if(empty($nombre)){
        $error_msg = array("message" => "", "error" => "El campo Nombre Completo es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else if(empty($email)){
        $error_msg = array("message" => "", "error" => "El campo Correo Electrónico es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else if(empty($user)){
        $error_msg = array("message" => "", "error" => "El campo Usuario es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else if(empty($clave)){
        $error_msg = array("message" => "", "error" => "El campo Clave de acceso es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else if(empty($rol)){
        $error_msg = array("message" => "", "error" => "El campo Rol es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    }

    $pdomodel = $obj->getPDOModelObj();
    $result = $pdomodel->DBQuery("SELECT * FROM usuario WHERE (usuario = :user OR email = :email) AND id != :id", [':user' => $user, ':email' => $email, ':id' => $id]);

    if($result){
        $error_msg = array("message" => "", "error" => "El correo o el usuario ya existe.", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else {

        if(empty($clave)){
            $error_msg = array("message" => "", "error" => "Ingresa una clave para guardar tus datos.", "redirectionurl" => "");
            die(json_encode($error_msg));
        }

        $newdata = array();
        $newdata["usuario"]["nombre"] = $nombre;
        $newdata["usuario"]["usuario"] = $user;
        $newdata["usuario"]["email"] = $email;
        $newdata["usuario"]["avatar"] = basename($data["usuario"]["avatar"]);
        $newdata["usuario"]["password"] = password_hash($clave, PASSWORD_DEFAULT);
        $newdata["usuario"]["token"] = $token;
        $newdata["usuario"]["expiration_token"] = 0;
        $newdata["usuario"]["idrol"] = $rol;
        $newdata["usuario"]["estatus"] = 1;

        return $newdata;
    }
}

function insetar_usuario($data, $obj){
    $token = $_POST['auth_token'];
    $valid = App\core\Token::verifyFormToken('send_message', $token);
    if (!$valid) {
        echo "El token recibido no es válido";
        die();
    }

    $nombre = $data["usuario"]["nombre"];
    $rut = $data["usuario"]["rut"];
    $email  = $data["usuario"]["email"];
    $user   = $data["usuario"]["usuario"];
    $clave  = $data["usuario"]["password"];
    $rol    = $data["usuario"]["idrol"];
    $cargo    = $data["usuario"]["cargo"];
    $avatar = $data["usuario"]["avatar"];

    if(empty($nombre)){
        $error_msg = array("message" => "", "error" => "El campo Nombre Completo es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    /*} else if(empty($email)){
        $error_msg = array("message" => "", "error" => "El campo Correo Electrónico es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));*/
    } else if(empty($user)){
        $error_msg = array("message" => "", "error" => "El campo Usuario es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else if(empty($clave)){
        $error_msg = array("message" => "", "error" => "El campo Clave de acceso es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else if(empty($rol)){
        $error_msg = array("message" => "", "error" => "El campo Rol es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    }

    $pdomodel = $obj->getPDOModelObj();
    $result = $pdomodel->DBQuery("SELECT * FROM usuario WHERE nombre = '$nombre'");

    if($result){
        $error_msg = array("message" => "", "error" => "El correo o el usuario ya existe.", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else {
        $newdata = array();
        $newdata["usuario"]["nombre"] = $nombre;
        $newdata["usuario"]["rut"] = $rut;
        $newdata["usuario"]["usuario"] = $user;
        $newdata["usuario"]["email"] = $email;
        if (empty($avatar)) {
            $image = PDOCrudABSPATH . 'uploads/1710162578_user.png';
            $newdata["usuario"]["avatar"] =  basename($image);
        } else {
            $newdata["usuario"]["avatar"] = basename($avatar);
        }
        $newdata["usuario"]["password"] = password_hash($clave, PASSWORD_DEFAULT);
        $newdata["usuario"]["token"] = $token;
        $newdata["usuario"]["expiration_token"] = 0;
        $newdata["usuario"]["idrol"] = $rol;
        $newdata["usuario"]["cargo"] = $cargo;
        $newdata["usuario"]["estatus"] = 1;

        return $newdata;
    }
}

function editar_usuario($data, $obj){
    $token = $_POST['auth_token'];
    $valid = App\core\Token::verifyFormToken('send_message', $token);
    if (!$valid) {
        echo "El token recibido no es válido";
        die();
    }

    $id     = $data["usuario"]["id"];
    $nombre = $data["usuario"]["nombre"];
    $rut = $data["usuario"]["rut"];
    $email  = $data["usuario"]["email"];
    $clave  = $data["usuario"]["password"];
    $user   = $data["usuario"]["usuario"];
    $rol    = $data["usuario"]["idrol"];
    $cargo = $data["usuario"]["cargo"];

    
    if(empty($nombre)){
        $error_msg = array("message" => "", "error" => "El campo Nombre Completo es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    /*} else if(empty($email)){
        $error_msg = array("message" => "", "error" => "El campo Correo Electrónico es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));*/
    } else if(empty($user)){
        $error_msg = array("message" => "", "error" => "El campo Usuario es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else if(empty($clave)){
        $error_msg = array("message" => "", "error" => "El campo Clave de acceso es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else if(empty($rol)){
        $error_msg = array("message" => "", "error" => "El campo Rol es obligatorio", "redirectionurl" => "");
        die(json_encode($error_msg));
    }

    $pdomodel = $obj->getPDOModelObj();
    $result = $pdomodel->DBQuery("SELECT * FROM usuario WHERE (usuario = :user OR email = :email) AND id != :id", [':user' => $user, ':email' => $email, ':id' => $id]);
    
    if ($result) {
        $error_msg = array("message" => "", "error" => "El correo o el usuario ya existe.", "redirectionurl" => "");
        die(json_encode($error_msg));
    } else {
        $newdata = array();
        $newdata["usuario"]["id"] = $id;
        $newdata["usuario"]["nombre"] = $nombre;
        $newdata["usuario"]["rut"] = $rut;
        $newdata["usuario"]["usuario"] = $user;
        $newdata["usuario"]["email"] = $email;
        $newdata["usuario"]["avatar"] = basename($data["usuario"]["avatar"]);
        $newdata["usuario"]["password"] = password_hash($clave, PASSWORD_DEFAULT);
        $newdata["usuario"]["token"] = $token;
        $newdata["usuario"]["expiration_token"] = 0;
        $newdata["usuario"]["idrol"] = $rol;
        $newdata["usuario"]["cargo"] = $cargo;
        $newdata["usuario"]["estatus"] = 1;

        return $newdata;
    }
}

function beforeloginCallback($data, $obj) {
    $pass = $data['usuario']['password'];
    $user_or_rut = $data['usuario']['usuario'] ?? $data['usuario']['rut'] ?? null;

    if ($user_or_rut) {
        $pdomodel = $obj->getPDOModelObj();
        $field = isset($data['usuario']['rut']) ? "rut" : "usuario";
        $pdomodel->where($field, $user_or_rut);
        $hash = $pdomodel->select("usuario");

       if ($hash) {
            if (password_verify($pass, $hash[0]['password'])) {
                @session_start();
                $_SESSION["data"] = $data;
                $obj->setLangData("no_data", "Bienvenido");
            } else {
                echo "El usuario o la contraseña ingresada no coinciden";
                die();
            }
        } else {
            if (isset($data['usuario']['rut'])) {
                echo "El RUT ingresado no coincide";
            } else {
                echo "El usuario ingresado no existe";
            }
            die();
        }
    } else {
        echo "Datos erróneos";
        die();
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://10.5.131.14/Imagenologia/api/usuarios?op=jwtauth',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "data": {
                "rut": '.$_ENV["rut_api"].',
                "contrasena": '.$_ENV["clave_api"].'
            }
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    $resultArray = json_decode($response, true);
    $obj->setLangData("tokenApi", $resultArray["data"]);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://10.5.131.63/repositoriopolos/api/usuario?op=jwtauth',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "data": {
                "rut": '.$_ENV["rut_polos_api"].',
                "password": '.$_ENV["clave_polos_api"].'
            }
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    $resultArrayPolos = json_decode($response, true);

    $obj->setLangData("tokenApiPolos", $resultArrayPolos["data"]);

    return $data;
}
 
function insertar_submenu($data, $obj){
    $id_menu = $data["submenu"]["id_menu"];
   
    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_menu", $id_menu);
    $result = $pdomodel->select("menu");
    
    if($result){
        $pdomodel->where("id_menu", $id_menu);
        $pdomodel->update("menu", array("submenu"=> "Si"));
    }
    return $data;
}

function modificar_submenu($data, $obj){
    $id_menu = $data["submenu"]["id_menu"];
   
    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("id_menu", $id_menu);
    $result = $pdomodel->select("menu");
    
    if($result){
        $pdomodel->where("id_menu", $id_menu);
        $pdomodel->update("menu", array("submenu"=> "Si"));
    }
    return $data;
}
 
function formatTableMenu($data, $obj){
    if($data){
        for ($i = 0; $i < count($data); $i++) {

            if($data[$i]["submenu"] == "No"){
                $data[$i]["submenu"] = "<div class='badge badge-danger'>".$data[$i]["submenu"]."</div>";
            } else {
                $data[$i]["submenu"] = "<div class='badge badge-success'>".$data[$i]["submenu"]."</div>";
            }

            $data[$i]["orden_menu"] = "<div class='badge badge-success'>".$data[$i]["orden_menu"]."</div>";

            $data[$i]["icono_menu"] = "<i style='font-size: 20px;' class='".$data[$i]["icono_menu"]."'></i>";
            
        }
    }
    return $data;
}

function formatTableSubMenu($data, $obj){
    if($data){
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["orden_submenu"] = "<div class='badge badge-success'>".$data[$i]["orden_submenu"]."</div>";

            $data[$i]["icono_submenu"] = "<i style='font-size: 20px;' class='".$data[$i]["icono_submenu"]."'></i>";
            
        }
    }
    return $data;
}


function agregar_profesional($data, $obj){
    $nombre_profesional = $data["profesional"]["nombre_profesional"];
    $apellido_profesional = $data["profesional"]["apellido_profesional"];
    $rut_profesional = $data["profesional"]["rut_profesional"];

    if (!App\Controllers\HomeController::validaRut($rut_profesional)) {
        echo "RUT inválido";
        die();
    }

    $obj->setLangData("success", "Profesional Agregado con éxito");

    return $data;
}

function modificar_profesional($data, $obj){
    $nombre_profesional = $data["profesional"]["nombre_profesional"];
    $apellido_profesional = $data["profesional"]["apellido_profesional"];
    $rut_profesional = $data["profesional"]["rut_profesional"];

    if (!App\Controllers\HomeController::validaRut($rut_profesional)) {
        echo "RUT inválido";
        die();
    }

    $obj->setLangData("success", "Profesional Actualizado con éxito");

    return $data;
}

function resetloginCallback($data, $obj)
{   
    $email = htmlspecialchars($data['usuario']['email']);
    $pdomodel = $obj->getPDOModelObj();
    $pdomodel->where("email", $email);
    $hash = $pdomodel->select("usuario");

    if ($hash) {
        $pass = $pdomodel->getRandomPassword(15, true);
        $encrypt = password_hash($pass, PASSWORD_DEFAULT);

        $pdomodel->where("id", $hash[0]["id"]);
        $pdomodel->update("usuario", array("password" => $encrypt));

        //$pdomodel->send_email_public($to, 'daniel.telematico@gmail.com', null, $subject, $emailBody);
        //App\core\DB::PHPMail($to, "daniel.telematico@gmail.com", $subject, $emailBody);

        $to = array($email => "Daniel Huerta");
        $subject = "Nueva Contraseña de acceso al sistema de Procedimentos";
        $emailBody = "Correo enviado  tu nueva contraseña es: $pass";
        $from = array("daniel.telematico@gmail.com" => "Daniel Huerta");
        $altMessage = "";
        $cc = array();
        $bcc = array();
        $attachments = array();
        $smtp = App\core\DB::settingEmail();
        $isHTML = true;

        $obj->sendEmail($to, $subject, $emailBody, $from, $altMessage, $cc, $bcc, $attachments, $smtp, $isHTML);

        $obj->setLangData("success", "Correo enviado con éxito");
    }

    return $data;
}
