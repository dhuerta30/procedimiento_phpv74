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

    public function busqueda()
    {
        View::render('buscar_polos');
    }

    public function rut()
    {
        View::render('buscar_polos_rut');
    }
}