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

		/*if (isset($_SESSION["data"]["dXN1YXJpbyMkcnV0QDNkc2ZzZGYqKjk5MzQzMjQ="])) {
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->where("rut", $_SESSION["data"]["dXN1YXJpbyMkcnV0QDNkc2ZzZGYqKjk5MzQzMjQ="]);
			$sesion_users = $pdomodel->select("usuario");
			$_SESSION["usuario"] = $sesion_users;
		}*/

		if (isset($_SESSION["data"])) {
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->where("usuario", $_SESSION["data"]["dXN1YXJpbyMkdXN1YXJpb0AzZHNmc2RmKio5OTM0MzI0"]);
			$sesion_users = $pdomodel->select("usuario");
			$_SESSION["usuario"] = $sesion_users;
		}
	}

    public function index(){
        $pdocrud = DB::PDOCrud();

		$html_template = '
			<div class="row">
				<div class="col-md-12">
					<label>¿Cómo desea ingresar al sistema?</label>
					<select class="form-control seleccion_de_acceso">
						<option value="">Seleccione una Opción</option>
						<option value="rut_clave">Con Rut y Contraseña</option>
						<option value="usuario_clave">Con Usuario y Contraseña</option>
					</select>
				</div>
			</div>
			<div class="row mt-2">
				<div class="col-md-12 usuario_col d-none">
					<div class="form-group">
						<label class="form-label">Usuario:</label>
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>	
							{usuario}
						</div>
						<p class="pdocrud_help_block help-block form-text with-errors"></p>
					</div>
				</div>
				<div class="col-md-12 rut_col d-none">
					<div class="form-group">
						<label class="form-label">Rut:</label>
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon1"><i class="fa fa-credit-card"></i></span>	
							{rut}
						</div>
						<p class="pdocrud_help_block help-block form-text with-errors"></p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<label class="form-label">Contraseña:</label>
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon1"><i class="fa fa-key"></i></span>	
							{password}
						</div>
						<p class="pdocrud_help_block help-block form-text with-errors"></p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 text-center botones d-none">
					<input type="submit" class="btn btn-primary pdocrud-form-control pdocrud-submit mb-3" data-action="selectform" value="Ingresar">
					<button type="reset" class="btn btn-danger pdocrud-form-control pdocrud-button mb-3 pdocrud-cancel-btn">Limpiar</button>
				</div>
			</div>';
		
		$pdocrud->set_template($html_template);
		$pdocrud->buttonHide("submitBtn");
		$pdocrud->buttonHide("cancel");
		$pdocrud->addPlugin("bootstrap-inputmask");
		$pdocrud->fieldCssClass("rut", array("rut"));
		$pdocrud->fieldCssClass("usuario", array("usuario"));
		$pdocrud->formStaticFields("input", "html", "
			<label>Usuario</label>
			<input type='text' class='form-control pdocrud-form-control pdocrud-text usuario' name='usuario'>
		");
		//$pdocrud->fieldAttributes("usuario", array("name"=>"usuario"));
		//$pdocrud->setSettings("encryption", false);
		//$pdocrud->addCallback("before_select", "beforeloginCallback");
		$pdocrud->addCallback("after_select", "afterLoginCallBack");
		//$pdocrud->formRedirection("http://localhost/".$_ENV["BASE_URL"]."Home/datos_paciente", true);
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
		Redirect::to("Login/index");
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