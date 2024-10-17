<?php

namespace App\Controllers;

use App\core\SessionManager;
use App\core\Token;
use App\core\Request;
use App\core\View;
use App\core\Redirect;
use App\core\DB;
use Xinvoice;
        
class BusquedaController
{
    public $token;

	public function __construct()
	{
		SessionManager::startSession();
		$Sesusuario = SessionManager::get('usuario');
		if (!isset($Sesusuario)) {
			Redirect::to("Login/index");
		}
        $this->token = Token::generateFormToken('send_message');
	}

    public function generarToken(){
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

        echo json_encode(array('data' => $resultArray["data"]));
    }
    
    public function obtener_rango_fechas_pacientes()
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
            $data = array("op" => "query", "sql" => "SELECT * FROM pacientes WHERE fechavalidacion BETWEEN '$f1' AND '$f2' ORDER BY fecha_registro DESC LIMIT 10000");
            
            // Llamada a la API
            $data = http_build_query($data);
            // Inicializa curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.14/Imagenologia/api/pacientes?" . $data);
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


    public function obtener_pacientes_por_rut()
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
            $data = array("op" => "query", "sql" => "SELECT * FROM pacientes WHERE rut LIKE '%$rut%' ");
            
            // Llamada a la API
            $data = http_build_query($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.14/Imagenologia/api/pacientes?" . $data);
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
            $data = array("op" => "query", "sql" => "SELECT * FROM pacientes WHERE id = '".$id."' ");
            
            // Llamada a la API
            $data = http_build_query($data);
            // Inicializa curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.14/Imagenologia/api/pacientes?" . $data);
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
            echo json_encode(["data" => ["rutapdf" => $pdfUrl]]);
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

            // Consulta a la base de datos
            $data = array("op" => "query", "sql" => "SELECT * FROM pacientes WHERE id = '".$id."' ");
            
            // Llamada a la API
            $data = http_build_query($data);
            // Inicializa curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer '. $token
            ));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.14/Imagenologia/api/pacientes?" . $data);
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
            echo json_encode(["data" => ["rutapdf" => $pdfUrl]]);
        }
    }

    public function rango_fechas()
    {
        View::render('busqueda_rango_fechas');
    }

    public function por_rut()
    {
        View::render('busqueda_por_rut');
    }
}