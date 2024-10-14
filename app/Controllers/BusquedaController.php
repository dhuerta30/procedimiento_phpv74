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
    
    public function generarToken()
    {
        // Obteniendo rut y password desde las variables de entorno
        $rut = $_ENV["rut_api"];
        $password = $_ENV["clave_api"];

        // Configurar la solicitud CURL para obtener el token
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://10.5.131.14/Imagenologia/api/usuarios/?op=jwtauth',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array(
                'data' => array(
                    'rut' => $rut,  // Usar el rut del entorno
                    'contrasena' => $password  // Usar el password del entorno
                )
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        // Ejecutar la solicitud
        $response = curl_exec($curl);

        // Comprobar si ocurrió un error en la solicitud CURL
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            echo "Error en CURL: " . $error_msg;
            return null;
        }

        curl_close($curl);
        
        // Mostrar la respuesta completa para depurar (puedes eliminar este echo si ya no lo necesitas)
        echo "Respuesta de la API: " . $response . "\n";

        // Decodificar la respuesta JSON
        $responseData = json_decode($response, true);

        // Revisar si el campo 'data' está presente
        if (isset($responseData['data'])) {
            return $responseData['data'];  // Retornar el token almacenado en 'data'
        } else {
            echo "Error: El campo 'data' no está presente en la respuesta.";
            return null;
        }
    }
    
    public function obtener_estadisticas($token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://10.5.131.14/Imagenologia/api/estadistica',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        // Decodificar el JSON de respuesta
        $responseData = json_decode($response, true);
        
        // Verificar si el campo 'data' existe
        if (isset($responseData['data'])) {
            return $responseData['data'];
        } else {
            echo "Error: No se encontraron Datos.";
            return null;
        }
    }

    public function rango_fechas()
    {
        $token = $this->generarToken();
        $estadisticas = $this->obtener_estadisticas($token);
        
        print_r($estadisticas);
        die();
        
        View::render('busqueda_rango_fechas');
       
    }

    public function por_rut()
    {
        View::render('busqueda_por_rut');
    }

    public function listar_rango_tabla_nulla(){
        $data = array();
        echo json_encode(array('data' => $data));
    }
}