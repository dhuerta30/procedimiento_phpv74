<?php 

namespace App\Controllers;

use App\core\SessionManager;
use App\core\Token;
use App\core\Redirect;
use App\core\View;
use App\core\DB;
use App\Models\UserModel;

class LoginController {

    public function __construct()
	{
		SessionManager::startSession();

		if (isset($_SESSION["data"]["usuario"]["rut"])) {
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->where("rut", $_SESSION["data"]["usuario"]["rut"]);
			$sesion_users = $pdomodel->select("usuario");
			$_SESSION["usuario"] = $sesion_users;
		}

		$Sesusuario = SessionManager::get('usuario');
		if (isset($Sesusuario)) {
			Redirect::to("home/datos_paciente");
		}
	}

    public function index(){
        $pdocrud = DB::PDOCrud();
		$pdocrud->addPlugin("bootstrap-inputmask");
		$pdocrud->fieldCssClass("usuario", array("usuario"));
		$pdocrud->fieldCssClass("rut", array("rut"));
		$pdocrud->fieldCssClass("password", array("password"));
		$pdocrud->fieldDisplayOrder(array("usuario", "rut", "password"));
		$pdocrud->fieldGroups("Name",array("usuario","rut"));
		$pdocrud->fieldNotMandatory("usuario");
		$pdocrud->fieldNotMandatory("rut");
		$pdocrud->fieldRenameLable("password", "Contraseña");
        $pdocrud->fieldAddOnInfo("usuario", "before", '<div class="input-group-append"><span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span></div>');
		$pdocrud->fieldAddOnInfo("rut", "before", '<div class="input-group-append"><span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span></div>');
        $pdocrud->fieldAddOnInfo("password", "before", '<div class="input-group-append"><span class="input-group-text" id="basic-addon1"><i class="fa fa-key"></i></span></div>');
		$pdocrud->addCallback("before_select", "beforeloginCallback");
		$pdocrud->formFields(array("usuario", "rut", "password"));
		$pdocrud->setLangData("login", "Ingresar");
		$login = $pdocrud->dbTable("usuario")->render("selectform");
		$mask = $pdocrud->loadPluginJsCode("bootstrap-inputmask",".rut", array("mask"=> "'9{1,2}9{3}9{3}-(9|k|K)'", "casing" => "'upper'", "clearIncomplete" => "true", "numericInput"=> "true", "positionCaretOnClick" => "'none'"));

        View::render('login', ['login' => $login, 'mask' => $mask]);
    }

	public function users()
	{
		$users = new UserModel();
		$result = $users->select_users();

		echo json_encode($result);
	}

    public function salir()
	{
		SessionManager::startSession();
		SessionManager::destroy();
		Redirect::to("login/index");
	}

    public function reset()
	{
		$pdocrud = DB::PDOCrud();
		$pdocrud->fieldRenameLable("email", "Correo");
		$pdocrud->fieldAddOnInfo("email", "before", '<div class="input-group-append"><span class="input-group-text" id="basic-addon1"><i class="fa fa-envelope-o"></i></span></div>');
		$pdocrud->addCallback("before_select", "resetloginCallback");
		$pdocrud->formFields(array("email"));
		$pdocrud->setLangData("login", "Recuperar");
		$reset = $pdocrud->dbTable("usuario")->render("selectform");

		View::render('reset', ['reset' => $reset]);
	}
}