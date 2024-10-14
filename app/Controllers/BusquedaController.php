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

        $request = new Request();
    	if ($request->getMethod() === 'POST') {
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
            curl_close($curl);
            
            // Mostrar la respuesta completa para depurar (puedes eliminar este echo si ya no lo necesitas)
            //echo "Respuesta de la API: " . $response . "\n";

            // Decodificar la respuesta JSON
            $responseData = json_decode($response, true);
            return $responseData["data"];
        }
    }
    
    public function obtener_estadisticas()
    {
        $request = new Request();
        $token = $request->get('token');
        
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
            'Authorization: Bearer '. $token
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

    }

    public function rango_fechas()
    {
        $token = $this->generarToken();
        echo "Token generado: " . $token . "\n";
        
        $estadisticas = $this->obtener_estadisticas($token);
        
        if ($estadisticas !== null) {
            // Muestra el contenido de $estadisticas
            print_r($estadisticas);
        } else {
            echo "No se pudieron obtener estadísticas.";
        }
        
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