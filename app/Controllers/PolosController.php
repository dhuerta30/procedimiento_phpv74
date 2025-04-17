<?php

namespace App\Controllers;

use App\core\SessionManager;
use App\core\Token;
use App\core\Request;
use App\core\View;
use App\core\Redirect;
use App\core\DB;

class PolosController
{
	public function __construct()
	{
		SessionManager::startSession();
		$Sesusuario = SessionManager::get('usuario');
		if (!isset($Sesusuario)) {
			Redirect::to("Login/index");
		}
	}

    public function generarToken(){
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

        $resultArray = json_decode($response, true);

        echo json_encode(array('data' => $resultArray["data"]));
    }


    public function obtener_rango_fechas_polos()
    {
        $request = new Request();
        if ($request->getMethod() === 'POST') {

            $f1 = $request->post("ingreso");
            $f2 = $request->post("termino");
            $token = $request->post("token");

            if(empty($f1) && empty($f2)){
                echo json_encode(["error" => "Debe ingresar al menos un campo para realizar la búsqueda"]);
                return;
            }

            // Consulta a la base de datos
            $data = array("op" => "query", "sql" => "SELECT * FROM polos WHERE fechadocumento BETWEEN '$f1' AND '$f2'");
            
            // Llamada a la API
            $data = http_build_query($data);
            // Inicializa curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.63/repositoriopolos/api/polos?" . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            // Convierte el resultado a un array asociativo
            $resultArray = json_decode($result, true);

            if($resultArray["mensaje"] == "No tienes permitido acceder a este recurso."){
                // Responde con los datos obtenidos o un error
                echo json_encode(array('mensaje' => $resultArray["mensaje"]));
            } else {
                echo json_encode(array('data' => $resultArray["data"]));
            }
        }
    }

    public function obtener_polos_por_rut()
    {
        $request = new Request();
        if ($request->getMethod() === 'POST') {
            $rut = $request->post("rut");
            $token = $request->post("token");

            if (empty($rut)) {
                echo json_encode(["error" => "Ingrese un Rut para realizar la búsqueda"]);
                return;
            }

            // Consulta a la base de datos
            $data = array("op" => "query", "sql" => "SELECT * FROM polos WHERE rut LIKE '%$rut%' ");
            
            // Llamada a la API
            $data = http_build_query($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.63/repositoriopolos/api/polos?" . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            
            // Verificar errores en la solicitud CURL
            if (curl_errno($ch)) {
                echo json_encode(["error" => "Error al conectarse con la API."]);
                curl_close($ch);
                return;
            }

            curl_close($ch);

            // Debug: Output the raw response for inspection
            if (!$result) {
                echo json_encode(["error" => "No se recibió una respuesta de la API."]);
                return;
            }

            // Mostrar la respuesta completa para depuración
            $resultArray = json_decode($result, true);
            if ($resultArray === null) {
                // Error de formato de JSON
                echo json_encode(["error" => "La respuesta no es un JSON válido. Respuesta recibida: " . $result]);
                return;
            }

            if (!isset($resultArray["data"])) {
                // Responder con el formato completo para verificar la estructura
                echo json_encode(["error" => "Formato de respuesta no válido. Respuesta completa: " . $result]);
                return;
            }

            if($resultArray["mensaje"] == "No tienes permitido acceder a este recurso."){
                // Responde con los datos obtenidos o un error
                echo json_encode(array('mensaje' => $resultArray["mensaje"]));
            } else {
                echo json_encode(array('data' => $resultArray["data"]));
            }
        }
    }

    public function obtener_pdf_por_fecha(){
        $request = new Request();
        if ($request->getMethod() === 'POST') {
            $id = $request->post("id");
            $token = $request->post("token");

            if(empty($id)){
                echo json_encode(["error" => "No Hay documento PDF para mostrar"]);
                return;
            }

            // Consulta a la base de datos
            $data = array("op" => "query", "sql" => "SELECT * FROM polos WHERE id = '".$id."' ");
            
            // Llamada a la API
            $data = http_build_query($data);
            // Inicializa curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.63/repositoriopolos/api/polos?" . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            // Convierte el resultado a un array asociativo
            $resultArray = json_decode($result, true);
            
            if ($resultArray === null || !isset($resultArray["data"]) || empty($resultArray["data"])) {
                echo json_encode(["error" => "Documento no encontrado o respuesta inválida."]);
                return;
            }
    
            // Obtener la URL del PDF desde la respuesta de la API
            $pdfUrl = $resultArray["data"][0]["rutapdf"] ?? null;
    
            if (!$pdfUrl) {
                echo json_encode(["error" => "No se encontró la ruta del PDF."]);
                return;
            }
    
            // Responder con la URL del PDF
            echo json_encode(["data" => ["rutapdf" => "http://10.5.131.63/repositoriopolos/app/libs/script/uploads/".$pdfUrl]]);
        }
    }

    public function obtener_pdf_rut(){
        $request = new Request();
        if ($request->getMethod() === 'POST') {
            $id = $request->post("id");
            $token = $request->post("token");

            if(empty($id)){
                echo json_encode(["error" => "No Hay documento PDF para mostrar"]);
                return;
            }

            $data = array("op" => "query", "sql" => "SELECT * FROM polos WHERE id = '".$id."' ");
            
            // Llamada a la API
            $data = http_build_query($data);
            // Inicializa curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.63/repositoriopolos/api/polos?" . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            // Convierte el resultado a un array asociativo
            $resultArray = json_decode($result, true);
            
            if ($resultArray === null || !isset($resultArray["data"]) || empty($resultArray["data"])) {
                echo json_encode(["error" => "Documento no encontrado o respuesta inválida."]);
                return;
            }
    
            // Obtener la URL del PDF desde la respuesta de la API
            $pdfUrl = $resultArray["data"][0]["rutapdf"] ?? null;
    
            if (!$pdfUrl) {
                echo json_encode(["error" => "No se encontró la ruta del PDF."]);
                return;
            }
    
            // Responder con la URL del PDF
            echo json_encode(["data" => ["rutapdf" => "http://10.5.131.63/repositoriopolos/app/libs/script/uploads/".$pdfUrl]]);
        }
    }

    public function obtener_datos_polos(){
        $request = new Request();
        if ($request->getMethod() === 'POST') {
            $token = $request->post("token");

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://10.5.131.63/repositoriopolos/api/polos',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl);
            curl_close($curl);
           
            $datos = json_decode($response, true);

            if (is_array($datos)) {
                $db = DB::PDOModel();
                foreach ($datos['data'] as $fila) {
                    $db->where("rut", $fila['rut']);
                    $existe = $db->select("polos_api");
                    if (empty($existe)) {
                        $db->insert("polos_api", array(
                            'rut'            => $fila['rut'] ?? null,
                            'poc'            => $fila['poc'] ?? null,
                            'dnombre'        => $fila['dnombre'] ?? null,
                            'apellidop'      => $fila['apellidop'] ?? null,
                            'apellidom'      => $fila['apellidom'] ?? null,
                            'fechadocumento' => $fila['fechadocumento'] ?? null,
                            'tipodocumento'  => $fila['tipodocumento'] ?? null,
                            'fecharegistro'  => $fila['fecharegistro'] ?? null,
                            'rutapdf'        => $fila['rutapdf'] ?? null,
                            'rutapdf2'       => $fila['rutapdf2'] ?? null,
                            'rutapdf3'       => $fila['rutapdf3'] ?? null
                        ));
                    }
                }
            }
    
            echo json_encode(['success' => true, 'inserted' => count($datos)]);
        }
    }

    public function busqueda()
    {
        $pdocrud = DB::PDOCrud();

        $pdocrud->addFilter("FechaInicio", "Fecha Inicio", "fechadocumento", "date");
        $pdocrud->setFilterSource("FechaInicio", "polos_api", "fechadocumento", "fechadocumento as pl", "db");
        $pdocrud->addFilter("FechaTermino", "Fecha Término", "fechadocumento", "date");
        $pdocrud->setFilterSource("FechaTermino", "polos_api", "fechadocumento", "fechadocumento as pl", "db");

        $pdocrud->tableColFormatting("rutapdf", "html",array("type" => "html", "str"=>"<a href='http://10.5.131.63/repositoriopolos/app/libs/script/uploads/{col-name}' target='_blank' class='btn btn-info btn-sm btn-block'>Ver PDF</a>"));
        $pdocrud->tableColFormatting("fechadocumento", "date", array("format" =>"d-m-Y"));
        $pdocrud->tableColFormatting("fecharegistro", "date", array("format" =>"d-m-Y H:i:s"));
        $pdocrud->tableHeading("Búsqueda Rango de Fechas");
        $pdocrud->where("id", "NULL");
        $pdocrud->addCallback("before_table_data", "funciones_de_filtro");
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("1" => "ANGIOGRAFIA"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("2" => "OCT"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("3" => "RECUENTO ENDOTELIAL"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("4" => "ECO OCULAR"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("5" => "FONDO DE OJO"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("6" => "AVASTIN"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("7" => "CAMPO VISUAL"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("8" => "CONSENTIMIENTO"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("9" => "BIOMETRIA"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("10" => "Tratamiento Ortóptico"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("11" => "Estudio de Estrabismo"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("12" => "Retinografía"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("13" => "Paquimetría"));
        $pdocrud->tableColFormatting("tipodocumento", "replace", array("" => ""));
        $pdocrud->setSettings("function_filter_and_search", false);
        $pdocrud->setSettings("searchbox", true);
        $pdocrud->setSettings("addbtn", false);
        $pdocrud->setSettings("viewbtn", false);
        $pdocrud->setSettings("printBtn", false);
        $pdocrud->setSettings("pdfBtn", false);
        $pdocrud->setSettings("csvBtn", false);
        $pdocrud->setSettings("excelBtn", false);
        $pdocrud->setSettings("deleteMultipleBtn", false);
        $pdocrud->setSettings("checkboxCol", false);
        $pdocrud->setSettings("actionbtn", false);
        $pdocrud->colRename("poc", "Código");
        $pdocrud->colRename("dnombre", "Nombre");
        $pdocrud->setLangData("no_data", "No se encontraron Resultados");
        $pdocrud->colRename("apellidop", "Apellido Paterno");
        $pdocrud->colRename("apellidom", "Apellido Materno");
        $pdocrud->colRename("fechadocumento", "Fecha Documento");
        $pdocrud->colRename("tipodocumento", "Tipo Documento");
        $pdocrud->colRename("fecharegistro", "Fecha Registro");
        $pdocrud->colRename("rutapdf", "Documento 1");
        $pdocrud->colRename("rutapdf2", "Documento 2");
        $pdocrud->colRename("rutapdf3", "Documento 3");
        $pdocrud->crudRemoveCol(array("id", "subido_por", "fechavalidacion"));
        $pdocrud->setSearchCols(array(
            "rut", 
            "poc", 
            "dbnombre", 
            "apellidop", 
            "apellidom", 
            "fechadocumento", 
            "tipodocumento", 
            "observaciones", 
            "fecharegistro", 
            "rutapdf", 
            "rutapdf2", 
            "rutapdf3"
        ));
        $render = $pdocrud->dbTable("polos_api")->render();
        View::render('buscar_polos', [
            'render' => $render
        ]);
    }

    public function rut()
    {
        View::render('buscar_polos_rut');
    }
}