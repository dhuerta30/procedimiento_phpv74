<?php

namespace App\Controllers;

use App\core\SessionManager;
use App\core\Token;
use App\core\Request;
use App\core\View;
use App\core\Redirect;
use App\core\DB;

class Carga_Masiva_PacientesController
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

    public function index()
    {
        $pdocrud = DB::PDOCrud();
		$pdocrud->fieldRenameLable("archivo", "Archivo Excel");
		$pdocrud->setLangData("save", "Subir");
		$pdocrud->setSettings("required", false);
		$pdocrud->fieldTypes("archivo", "FILE_NEW");
		$pdocrud->addCallback("before_insert", "carga_masiva_pacientes_insertar");
		$render = $pdocrud->dbTable("carga_masiva_pacientes")->render("insertform");
        View::render('carga_masiva_pacientes',[
            'render' => $render
        ]);
    }
}