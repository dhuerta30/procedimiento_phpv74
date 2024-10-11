<?php

namespace App\core;
use PDOCrud;

class DB {
    public static function PDOCrud($multi = false, $template = "", $skin = "", $dbSettings = array(), $settings = array())
    {
        $settings["uploadURL"] = $_ENV['UPLOAD_URL'];
        $settings["downloadURL"] = $_ENV['DOWNLOAD_URL'];
        if (!empty($dbSettings)) {
            $settings["script_url"] = $dbSettings['script_url'];
            $settings["hostname"] = $dbSettings['hostname'];
            $settings["database"] = $dbSettings['database'];
            $settings["username"] = $dbSettings['username'];
            $settings["password"] = $dbSettings['password'];
            $settings["dbtype"] = $dbSettings['dbtype'];
            $settings["characterset"] = isset($dbSettings['characterset']) ? $dbSettings['characterset'] : $_ENV["CHARACTER_SET"];
        } else {
            // Usamos la configuración por defecto
            $settings["script_url"] = $_ENV['URL_PDOCRUD'];
            $settings["hostname"] = $_ENV['DB_HOST'];
            $settings["database"] = $_ENV['DB_NAME'];
            $settings["username"] = $_ENV['DB_USER'];
            $settings["password"] = $_ENV['DB_PASS'];
            $settings["dbtype"] = $_ENV['DB_TYPE'];
            $settings["characterset"] = $_ENV["CHARACTER_SET"];
        }

        // Inicializar PDOCrud con los ajustes proporcionados
        $pdocrud = new PDOCrud($multi, $template, $skin, $settings);
        return $pdocrud;
    }

    public static function settingEmail($smtp = array()){
        $smtp = array(
            "host" => $_ENV['MAIL_HOST'],
            "port" => $_ENV["MAIL_PORT"],
            "SMTPAuth" =>  DB::evalBool($_ENV['SMTP_AUTH']),
            "username" => $_ENV["MAIL_USERNAME"],
            "password" => $_ENV["MAIL_PASSWORD"],
            "SMTPSecure" => $_ENV["SMTP_SECURE"],
            "SMTPKeepAlive" => DB::evalBool($_ENV['SMTP_KEEP_ALIVE'])
        );
        return $smtp;
    }


    /*  Ejemplo de uso 
        
        $to = array("daniel.telematico@gmail.com" => "Daniel Huerta");
        $subject = "Test Email";
        $message = "This is a test email.";
        $from = array("daniel.telematico@gmail.com" => "Daniel Huerta");
        $altMessage = "This is the alternative plain text message.";
        $cc = array();
        $bcc = array();
        $attachments = array();
        $smtp = DB::settingEmail();
        $isHTML = true;

        $pdocrud->sendEmail($to, $subject, $message, $from, $altMessage, $cc, $bcc, $attachments, $smtp, $isHTML);
    */

	public static function evalBool($value)
	{
		return (strcasecmp($value, 'true') ? false : true);
	}

    public static function PDOModel()
    {
        $pdocrud = DB::PDOCrud();
        $pdomodel = $pdocrud->getPDOMOdelObj();
        $pdomodel->fetchType = "OBJ";
        return $pdomodel;
    }

    public static function PHPMail($hacia, $desde, $asunto, $mensaje){
		$pdocrud = DB::PDOCrud();
		// Parámetros para el correo electrónico
		$to = array(
			$hacia => 'Nombre Destinatario 1'
		);
		$subject = $asunto;
		$message = $mensaje;
		$from = array($desde => 'Hospital');
		$altMessage = 'Este es el mensaje alternativo';
		$cc = array();
		$bcc = array();
		$attachments = array();
		$mode = 'SMTP';
		$smtp = array(
			'host' => $_ENV['MAIL_HOST'],
			'port' => $_ENV['MAIL_PORT'],
			'SMTPAuth' => DB::evalBool($_ENV['SMTP_AUTH']),
			'username' => $_ENV['MAIL_USERNAME'],
			'password' => $_ENV['MAIL_PASSWORD'],
			'SMTPSecure' => $_ENV['SMTP_SECURE'],
			'SMTPKeepAlive' => DB::evalBool($_ENV['SMTP_KEEP_ALIVE'])
		);
		$isHTML = true;
		return $pdocrud->sendEmail($to, $subject, $message, $from, $altMessage, $cc, $bcc, $attachments, $mode, $smtp, $isHTML);
	}

	public static function performPagination($registros_por_pagina, $pagina_actual, $tabla, $id, $parametro)
    {
        $pdomodel = DB::PDOModel();

        $totalRegistros = $pdomodel->executeQuery("SELECT COUNT(*) as total FROM $tabla");
        $pagination = $pdomodel->simplepagination($pagina_actual, $totalRegistros[0]["total"], $registros_por_pagina, 'index.php', $parametro);
    
        $inicio = max(0, ($pagina_actual - 1) * $registros_por_pagina);
        $query = "SELECT * FROM $tabla LIMIT $inicio, $registros_por_pagina";
        $resultados = $pdomodel->executeQuery($query);
		
        return ['output' => $pagination, 'resultados' => $resultados];
    }
}