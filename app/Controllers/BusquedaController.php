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
			Redirect::to("login/index");
		}
        $this->token = Token::generateFormToken('send_message');
	}
    
    public function obtener_rango_fechas_pacientes()
    {
        $request = new Request();
        if ($request->getMethod() === 'POST') {
            $f1 = $request->post("ingreso");
            $f2 = $request->post("termino");

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
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_URL, "http://10.5.131.14/Imagenologia/api/pacientes?" . $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            // Convierte el resultado a un array asociativo
            $resultArray = json_decode($result, true);
            
            // Responde con los datos obtenidos o un error
            echo json_encode(array('data' => $resultArray["data"]));
        }
    }


    public function obtener_pacientes_por_rut()
    {
        $request = new Request();
        if ($request->getMethod() === 'POST') {
            $rut = $request->post("rut");

            if (empty($rut)) {
                echo json_encode(["error" => "Ingrese un Rut para realizar la búsqueda"]);
                return;
            }

            // Consulta a la base de datos
            $data = array("op" => "query", "sql" => "SELECT * FROM pacientes WHERE rut LIKE '%$rut%' ");
            
            // Llamada a la API
            $data = http_build_query($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
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

            // Responde con los datos obtenidos
            echo json_encode(array('data' => $resultArray["data"]));
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