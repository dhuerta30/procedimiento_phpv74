<?php

namespace App\Controllers;

use App\core\SessionManager;
use App\core\Token;
use App\core\Request;
use App\core\View;
use App\core\Redirect;
use App\core\DB;
use Xinvoice;
use Coderatio\SimpleBackup\SimpleBackup;
use App\Models\DatosPacienteModel;
use App\Models\PageModel;
use App\Models\UsuarioMenuModel;
use App\Models\UserModel;
use App\Models\ProcedimientoModel;
use App\Models\UsuarioSubMenuModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HomeController
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

	public function comprobarSessionActiva(){
		SessionManager::startSession();
		$Sesusuario = SessionManager::get('usuario');
		if (!isset($Sesusuario)) {
			echo json_encode(['status' => 'active', 'message' => 'La sesión sigue Activa']);
		} else {
			echo json_encode(['status' => 'expired', 'message' => 'La sesión ha expirado vuelva a iniciar sesión']);
		}
	}

	public function index(){
		date_default_timezone_set('America/Santiago');
		$fecha_actual = date('d-m-Y');
		$fecha_registro = date('d-m-Y H:i');

		$pdocrud = DB::PDOCrud();
		$pdocrud->setSettings("required", false);
		$pdocrud->addCallback("before_insert", "insertar_procedimientos");
		$pdocrud->addPlugin("bootstrap-inputmask");
		$pdocrud->fieldGroups("Name",array("fecha_solicitud","procedimiento", "procedimiento_2"));
		$pdocrud->fieldGroups("Name2",array("rut","servicio", "fecha_registro"));
		$pdocrud->fieldGroups("Name3",array("nombres","apellido_paterno", "apellido_materno"));
		$pdocrud->fieldGroups("Name4",array("operacion","profesional_solicitante", "numero_contacto"));
		$pdocrud->fieldGroups("Name5",array("numero_contacto_2","prioridad"));
		
		$pdocrud->fieldCssClass("rut", array("rut"));
		$pdocrud->fieldCssClass("fecha_solicitud", array("fecha_solicitud"));
		$pdocrud->fieldCssClass("servicio", array("servicio"));
		$pdocrud->fieldCssClass("fecha_registro", array("fecha_registro"));
		$pdocrud->fieldCssClass("nombres", array("nombres"));
		$pdocrud->fieldCssClass("apellido_paterno", array("apellido_paterno"));
		$pdocrud->fieldCssClass("apellido_materno", array("apellido_materno"));
		$pdocrud->fieldCssClass("operacion", array("operacion"));
		$pdocrud->fieldCssClass("profesional_solicitante", array("profesional_solicitante"));
		$pdocrud->fieldCssClass("numero_contacto", array("numero_contacto"));
		$pdocrud->fieldCssClass("numero_contacto_2", array("numero_contacto_2"));
		$pdocrud->fieldCssClass("prioridad", array("prioridad"));
		$pdocrud->fieldCssClass("procedimiento", array("especialidad"));
		$pdocrud->fieldCssClass("procedimiento_2", array("procedimiento_2"));

		$pdocrud->fieldRenameLable("servicio", "Procedencia");
		$pdocrud->fieldRenameLable("procedimiento", "Especialidad");
		$pdocrud->fieldRenameLable("procedimiento_2", "Procedimiento");
		$pdocrud->fieldRenameLable("operacion", "Diagnóstico CIE");
		$pdocrud->fieldRenameLable("numero_contacto", "Número de Contacto 1");
		$pdocrud->fieldRenameLable("numero_contacto_2", "Número de Contacto 2");
		$pdocrud->fieldTypes("prioridad", "select");
		$pdocrud->fieldDataBinding("prioridad", array("Si" => "Si","No" => "No"), "", "","array");
		$pdocrud->formFieldValue("fecha_solicitud", $fecha_actual);
		$pdocrud->formFieldValue("fecha_registro", $fecha_registro);
		$pdocrud->formFieldValue("estado", "Pendiente");
		$pdocrud->fieldHideLable("estado");
		$pdocrud->fieldDisplayOrder(array(
			"fecha_solicitud",
			"procedimiento",
			"procedimiento_2",
			"rut", 
			"servicio", 
			"fecha_registro",
			"nombres", 
			"apellido_paterno", 
			"apellido_materno",
			"operacion",
			"profesional_solicitante",
			"numero_contacto",
			"numero_contacto_2",
			"prioridad"
		));
		$pdocrud->formFields(array(
			"fecha_solicitud",
			"procedimiento",
			"procedimiento_2",
			"rut", 
			"servicio", 
			"fecha_registro",
			"nombres", 
			"apellido_paterno", 
			"apellido_materno",
			"operacion",
			"profesional_solicitante",
			"numero_contacto",
			"numero_contacto_2",
			"prioridad"
		));
		$pdocrud->fieldDataAttr("fecha_solicitud", array("readonly"=>"true"));
		$pdocrud->fieldDataAttr("estado", array("style"=>"display:none"));
		$pdocrud->fieldTypes("procedimiento", "input");
		$pdocrud->fieldTypes("fecha_solicitud", "input");
		$pdocrud->fieldTypes("fecha_registro", "input");
		$pdocrud->fieldTypes("nombres", "input");
		$pdocrud->fieldTypes("apellido_paterno", "input");
		$pdocrud->fieldTypes("apellido_materno", "input");
		$pdocrud->fieldTypes("operacion", "input");
		$pdocrud->fieldTypes("profesional_solicitante", "input");
		$pdocrud->fieldTypes("numero_contacto", "input");
		$pdocrud->fieldTypes("numero_contacto_2", "input");
		$pdocrud->fieldTypes("rut", "input");
		$pdocrud->fieldTypes("procedimiento_2", "select");
		$pdocrud->fieldTypes("servicio", "select");
		$pdocrud->fieldDataBinding("servicio", array(
			"Cirugia" => "Cirugia",
			"CMA" => "CMA",
			"Pensionado" => "Pensionado",
			"Ginecologia y Obstetricia" => "Ginecologia y Obstetricia"
		), "", "","array");
		$pdocrud->formStaticFields("buttons", "html", "
			<div class='row'>
				<div class='col-md-12 text-center'>
					<input type='submit' class='btn btn-primary pdocrud-form-control pdocrud-submit' data-action='insert' value='Registrar Procedimiento'>
					<a href='javascript:;' class='btn btn-primary registrar_imprimir'>Registrar e Imprimir PDF</a>
				</div>
			</div>
		");
		$pdocrud->buttonHide("submitBtn");
		$pdocrud->buttonHide("cancel");
		$render = $pdocrud->dbTable("procedimiento")->render("insertform");
		$mask = $pdocrud->loadPluginJsCode("bootstrap-inputmask",".rut", array("mask"=> "'9{1,2}9{3}9{3}-(9|k|K)'", "casing" => "'upper'", "clearIncomplete" => "true", "numericInput"=> "true", "positionCaretOnClick" => "'none'"));

		View::render(
			'procedimientos', ['render' => $render, 'mask' => $mask]
		);
	}

	public static function obtener_menu_por_id_usuario($id_usuario){
		$usuario_menu = new UsuarioMenuModel();
		$data_usuario_menu = $usuario_menu->Obtener_menu_por_id_usuario($id_usuario);
		return $data_usuario_menu;
	}

	public static function Obtener_submenu_por_id_menu($id_menu, $id_usuario){
		$usuario_submenu = new UsuarioSubMenuModel();
		$data_usuario_submenu = $usuario_submenu->Obtener_submenu_por_id_menu($id_menu, $id_usuario);
		return $data_usuario_submenu;
	}

	public function obtener_menu_usuario()
	{
		$request = new Request();

		if ($request->getMethod() === 'POST') {
			$userId = $request->post('userId');

			$data_usuario_menu = HomeController::obtener_menu_por_id_usuario($userId);

			$usuario = new UserModel();
			$data_user = $usuario->obtener_usuario_porId($userId);

			$html = '<ul class="list-none">
				<li>
					<input type="checkbox" value="select-all" name="select_all" class="select-all">
					<span>Marcar Todos / Desmarcar Todos</span>
				</li>
			</ul>';
			$html .= '<ul class="list-none">';
			$html .= '<span>Menus Asignados a ' . $data_user[0]["nombre"] . '</span><br><br>';

			foreach ($data_usuario_menu as $item) {
				$html .= '<li>';

				if ($item["submenu"] == "Si") {
					$isChecked = ($item['visibilidad_menu'] == 'Mostrar' && $item['id_usuario'] ? 'checked' : ''); // Verificar si el menú está asignado al usuario
					$html .= '<input type="checkbox" ' . $isChecked . ' id="' . $item['id_menu'] . '" class="menu-checkbox-pr mr-2" data-type="menu">';
					$html .= '<span><i class="' . $item['icono_menu'] . '"></i> ' . $item['nombre_menu'] . '</span>';
					$html .= '<ul class="list-none">';

					$data_usuario_submenu = HomeController::Obtener_submenu_por_id_menu($item["id_menu"], $userId);

					foreach ($data_usuario_submenu as $submenu) {

						$isCheckedSubmenu = ($submenu['visibilidad_submenu'] == 'Mostrar' && $submenu['id_usuario'] ? 'checked' : ''); // Verificar si el submenu está asignado al usuario
						$html .= '<li>';
						$html .= '<input type="checkbox" ' . $isCheckedSubmenu . ' id="' . $submenu['id_submenu'] . '" class="submenu-checkbox-pr mr-2" data-type="menu" data-parent="'.$item['id_menu'].'">';
						$html .= '<span><i class="' . $submenu['icono_submenu'] . '"></i> ' . $submenu['nombre_submenu'] . '</span>';
						$html .= '</li>';
					}

					$html .= '</ul>';
				} else {
					$isChecked = ($item['visibilidad_menu'] == 'Mostrar' && $item['id_usuario'] ? 'checked' : ''); // Verificar si el menú está asignado al usuario
					$html .= '<input type="checkbox" ' . $isChecked . ' id="' . $item['id_menu'] . '" class="menu-checkbox-pr mr-2" data-type="menu">';
					$html .= '<span><i class="' . $item['icono_menu'] . '"></i> ' . $item['nombre_menu'] . '</span>';
				}

				$html .= '</li>';
			}

			$html .= '<div class="row mt-4">
						<div class="col-md-12">
							<a href="javascript:;" title="Actualizar" class="btn btn-success btn-sm asignar_menu_usuario" data-id="' . $userId . '"><i class="far fa-save"></i> Actualizar</a>
						</div>
					</div>';
			$html .= '</ul>';
			$checkbox =  $html;
			HomeController::modal("menus", "<i class='far fa-eye'></i> Actualizar Menus Asignados", $checkbox);
		}
	}

	
	public function refrescarMenu()
	{
		$request = new Request();
	
		if ($request->getMethod() === 'POST') {
			// Obtén la URL actual
			$currentUrl = $_SERVER['REQUEST_URI'];
			$id_sesion_usuario = $_SESSION["usuario"][0]["id"];

			// Obtén el menú y submenús utilizando funciones existentes
			$menu = HomeController::obtener_menu_por_id_usuario($id_sesion_usuario);

			// Estructura para almacenar el menú
			$menuHtml = '<nav class="mt-2">
							<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">';

			foreach ($menu as $item) {
				if ($_SESSION["usuario"][0]["idrol"] == 1 || $item["nombre_menu"] != "usuarios" && $item["visibilidad_menu"] != "Ocultar") {
					// Obtiene submenús
					$submenus = HomeController::Obtener_submenu_por_id_menu($item['id_menu'], $id_sesion_usuario);
					$tieneSubmenus = ($item["submenu"] == "Si");
					$subMenuAbierto = false;

					// Verifica si algún submenú está activo
					foreach ($submenus as $submenu) {
						if (strpos($currentUrl, $submenu['url_submenu']) !== false) {
							$subMenuAbierto = true;
							break;
						}
					}

					$menuHtml .= '<li class="nav-item' . ($subMenuAbierto ? ' menu-is-opening menu-open' : '') . '">';
					if ($tieneSubmenus) {
						$menuHtml .= '<a href="javascript:;" class="nav-link' . (strpos($currentUrl, $submenu['url_submenu']) !== false ? ' active' : '') . '">
										<i class="' . $item['icono_menu'] . '"></i>
										<p>
											' . $item['nombre_menu'] . '
											<i class="right fas fa-angle-left"></i>
										</p>
									</a>
									<ul class="nav nav-treeview" style="' . ($subMenuAbierto ? 'display: block;' : '') . '">';
						foreach ($submenus as $submenu) {
							if ($submenu["visibilidad_submenu"] != "Ocultar") {
								$menuHtml .= '<li class="nav-item">
												<a href="' . rtrim($_ENV["BASE_URL"], '/') . $submenu['url_submenu'] . '" class="nav-link' . (strpos($currentUrl, $submenu['url_submenu']) !== false ? ' active' : '') . '">
													<i class="' . $submenu['icono_submenu'] . '"></i>
													<p>' . $submenu['nombre_submenu'] . '</p>
												</a>
											</li>';
							}
						}
						$menuHtml .= '</ul>';
					} else {
						if($item["visibilidad_menu"] != "Ocultar"){
						$menuHtml .= '<a href="' . rtrim($_ENV["BASE_URL"], '/') . $item['url_menu'] . '" class="nav-link' . (strpos($currentUrl, $item['url_menu']) !== false ? ' active' : '') . '">
										<i class="' . $item['icono_menu'] . '"></i>
										<p>' . $item['nombre_menu'] . '</p>
									</a>';
						}
					}
					$menuHtml .= '</li>';
				}
			}

			$menuHtml .= '</ul>
						</nav>';

			// Retorna el HTML del menú
			echo json_encode([$menuHtml]);
		}
	}


	public function asignar_menus_usuario()
	{
		$request = new Request();

		if ($request->getMethod() === 'POST') {
			$userId = $request->post("userId");
			$selectedMenus = $request->post("selectedMenus");

			if (is_array($selectedMenus)) {
				$pdocrud = DB::PDOCrud();
				$pdomodel = $pdocrud->getPDOModelObj();

				$menuMarcado = false;
				$menuDesmarcado = false;

				foreach ($selectedMenus as $menu) {
					$menuId = $menu["menuId"];
					$submenuIds = isset($menu["submenuIds"]) ? $menu["submenuIds"] : [];
					$checked = $menu["checked"];

					// Procesar el menú principal
					$existMenu = $pdomodel->where('id_menu', $menuId)
						->where('id_usuario', $userId)
						->select('usuario_menu');

					switch ($checked) {
						case "true":
							if (!$existMenu) {
								$pdomodel->insert('usuario_menu', array(
									"id_usuario" => $userId,
									"id_menu" => $menuId,
									"visibilidad_menu" => "Mostrar"
								));
								$menuMarcado = true;
							} else {
								$pdomodel->where('id_usuario', $userId)
									->where('id_menu', $menuId)
									->update('usuario_menu', array("visibilidad_menu" => "Mostrar"));
								$menuMarcado = true;
							}
							break;

						case "false":
							$pdomodel->where('id_usuario', $userId)
								->where('id_menu', $menuId)
								->update('usuario_menu', array("visibilidad_menu" => "Ocultar"));
							$menuDesmarcado = true;
							break;
					}

					// Procesar los submenús asociados al menú principal
					foreach ($submenuIds as $submenuId) {
						$id_submenu = $submenuId['id'];
						$checked = $submenuId["checked"];

						$existSubmenu = $pdomodel->where('id_submenu', $id_submenu)
							->where('id_usuario', $userId)
							->select('usuario_submenu');

						switch ($checked) {
							case "true":
								if (!$existSubmenu) {
									$pdomodel->insert('usuario_submenu', array(
										"id_usuario" => $userId,
										"id_submenu" => $id_submenu,
										"id_menu" => $menuId,
										"visibilidad_submenu" => "Mostrar"
									));
								} else {
									$pdomodel->where('id_usuario', $userId)
										->where('id_submenu', $id_submenu)
										->where('id_menu', $menuId)
										->update('usuario_submenu', array("visibilidad_submenu" => "Mostrar"));
								}
								break;

							case "false":
								$pdomodel->where('id_usuario', $userId)
									->where('id_submenu', $id_submenu)
									->where('id_menu', $menuId)
									->update('usuario_submenu', array("visibilidad_submenu" => "Ocultar"));
								break;
						}
					}
				}

				$response = [];

				if ($menuMarcado) {
					$response['success'][] = 'Menús asignados correctamente';
				}

				if ($menuDesmarcado) {
					$response['success'][] = 'Menús Actualizados correctamente';
				}

				if (!$menuMarcado && !$menuDesmarcado) {
					$response['error'][] = 'Todos los menús ya fueron asignados previamente';
				}

				echo json_encode($response);
			} else {
				echo json_encode(['error' => 'Debe seleccionar al menos 1 menú de la lista para continuar']);
			}
		}
	}


	public function acceso_menus(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->colRename("idrol", "Rol");
		$pdocrud->colRename("id", "ID");

		$action = "javascript:;";
		$text = '<i class="fa fa-eye"></i>';
		$attr = array("title"=> "Ver");
		$pdocrud->enqueueBtnActions("url", $action, "url", $text, "", $attr, "btn-info ver_menu_usuario");

		$action = "javascript:;";
		$text = '<i class="fa fa-save"></i>';
		$attr = array("title"=> "Asignar");
		$pdocrud->enqueueBtnActions("url2", $action, "url", $text, "", $attr, "btn-success asignar_menu_usuario");

		$pdocrud->relatedData('idrol','rol','idrol','nombre_rol');
		$pdocrud->tableColFormatting("avatar", "html",array("type" =>"html","str"=>'<img width="50" src="'.$_ENV["BASE_URL"].'app/libs/script/uploads/{col-name}">'));
		$pdocrud->crudRemoveCol(array("rol","estatus","password", "token", "token_api", "expiration_token"));
		$pdocrud->setSearchCols(array("id","nombre","email", "usuario", "idrol"));
		$pdocrud->setSettings("addbtn", false);
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->setSettings("editbtn", false);
		$pdocrud->setSettings("delbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$pdocrud->setSettings("template", "acceso_usuarios_menus");
		$pdocrud->setSettings("deleteMultipleBtn", false);
		$pdocrud->setSettings("checkboxCol", false);
		$render = $pdocrud->dbTable("usuario")->render();

		View::render(
			'acceso_menus',[
				'render' => $render
			]
		);
	}

	public function registrar_e_imprimir_pdf(){

		$request = new Request();

		 if ($request->getMethod() === 'POST') {
			$rut = $request->post('rut');
			$fecha_solicitud = $request->post('fecha_solicitud');
			$especialidad = $request->post('especialidad');
			$procedimiento_2 = $request->post('procedimiento_2');
			$servicio = $request->post('servicio');
			$fecha_registro = $request->post('fecha_registro');
			$nombres = $request->post('nombres');
			$apellido_paterno = $request->post('apellido_paterno');
			$apellido_materno = $request->post('apellido_materno');
			$operacion = $request->post('operacion');
			$profesional_solicitante = $request->post('profesional_solicitante');
			$numero_contacto = $request->post('numero_contacto');
			$numero_contacto_2 = $request->post('numero_contacto_2');
			$prioridad = $request->post('prioridad');

			if(!empty($rut) && !empty($especialidad) && !empty($procedimiento_2) && !empty($servicio) && !empty($nombres) && !empty($apellido_paterno) && !empty($apellido_materno) && !empty($operacion) && !empty($profesional_solicitante) && !empty($numero_contacto) && !empty($numero_contacto_2) && !empty($prioridad)){

				$data = [
					'rut' => $rut,
					'fecha_solicitud' => $fecha_solicitud,
					'procedimiento' => $especialidad,
					'procedimiento_2' => $procedimiento_2,
					'servicio' => $servicio,
					'fecha_registro' => $fecha_registro,
					'nombres' => $nombres,
					'apellido_paterno' => $apellido_paterno,
					'apellido_materno' => $apellido_materno,
					'operacion' => $operacion,
					'profesional_solicitante' => $profesional_solicitante,
					'numero_contacto' => $numero_contacto,
					'numero_contacto_2' => $numero_contacto_2,
					'prioridad' => $prioridad
				];
				$pdocedimiento = new ProcedimientoModel();
				$pdocedimiento->insertar_procedimiento($data);

				$url = $_ENV["BASE_URL"];
				$xinvoice = new Xinvoice();
				$xinvoice->setInvoiceDisplaySettings("header","", false);
				$xinvoice->setInvoiceDisplaySettings("to","", false);
				$xinvoice->setInvoiceDisplaySettings("from","", false);
				$xinvoice->setInvoiceDisplaySettings("footer", "", false);
				$xinvoice->setInvoiceDisplaySettings("payment", "", false);
				$xinvoice->setInvoiceDisplaySettings("message", "", false);
				$xinvoice->setInvoiceDisplaySettings("total","subtotal", false);
				$xinvoice->setInvoiceDisplaySettings("total","discount", false);
				$xinvoice->setInvoiceDisplaySettings("total","tax", false);
				$xinvoice->setInvoiceDisplaySettings("total","shipping", false);
				$xinvoice->setInvoiceDisplaySettings("total","grandtotal", false);
				$xinvoice->setInvoiceSections("before_header", "
					<style>
						table {
							width: 100%;
							border-collapse: collapse;
						}
				
						.ancho-extra {
							width: 70%;
							text-align:center;
						}
						.texto {
							height: 50px;
							font-size: 16px;
						}
					</style>
					<table>
						<tr>
							<td>
								<img src='".$url."/theme/img/logo_gobiernochile.jpg'>
							</td>
							<td class='ancho-extra'>
								<h5 class='texto'>SOLICITUD PROCEDIMIENTOS<h5>
							</td>
							<td>
								<img width='150' src='".$url."/theme/img/logo_hsjm.jpg'>
							</td>
						</tr>
					</table>
				");
				$xinvoice->setInvoiceSections("before_items", "
					<style>
						p {
							font-size:12px;
							font-weight: bold;
							text-align: left;
						}
						hr {
							width: 30%;
							margin: 10px auto;
							border-color: #333;
						  }						  
					</style>
					<p>Hospital San Jose de Melipilla</p>
					<h5 style='border: 1px solid #000; padding:5px; background: #ccdcfc; color: #000;'>Informacion Paciente</h5>
					<table>
						<tr>
							<td>
								<p>NOMBRE: ".$nombres."</p>
							</td>
						</tr>
					</table>
					<table>
						<tr>
							<td>
								<p>APELLIDO PATERNO: ".$apellido_paterno."</p>
							</td>
							<td>
								<p>APELLIDO MATERNO: ".$apellido_materno."</p>
							</td>
						</tr>
						<tr>
							<td>
								<p>CONTACTO: ".$numero_contacto."</p>
							</td>
							<td>
								<p>CONTACTO 2: ".$numero_contacto_2."</p>
							</td>
						</tr>
					</table>
					<h5 style='border: 1px solid #000; padding:5px; background: #ccdcfc; color: #000;'>Informacion Solicitud</h5>
					<table>
						<tr>
							<td>
								<p>PROCEDENCIA: ".$servicio."</p>
							</td>
							<td>
								<p>ESPECIALIDAD: ".$especialidad."</p>
							</td>
						</tr>
						<tr>
							<td>
								<p>PROCEDIMIENTO: ".$procedimiento_2."</p>
							</td>
						</tr>
						<tr>
							<td>
								<p>PRIORIDAD: ".$prioridad."</p>
							</td>
						</tr>
						<tr>
							<td>
								<p>DIAGNÓSTICO: ".$operacion."</p>
							</td>
						</tr>
					</table>
				");
				$xinvoice->setInvoiceSections("before_footer", "
						<table style='margin-top:200px;'>
							<tr>
								<td style='text-align:center;'>
									".$profesional_solicitante."
									<hr>
									<p>Medico Tratante</p>
								</td>
							</tr>
						</table>
				");         
				$xinvoice->setSettings("filename", "procedimiento.pdf");
				$xinvoice->setSettings("output", "F");
				$path = $xinvoice->render();
				
				echo json_encode(['mensaje' => 'Datos Guardados con éxito', 'pdf_url' => $path]);
			} else {
				echo json_encode(['error' => 'Todos los campos son obligatorios']);
			}
		}
	}

	public function exportacion_ingreso_egreso(){
		$pdocrud = DB::PDOCrud(false, "", "", array("autoSuggestion" => true, "showAllSearch" => false));
		$pdocrud->colRename("tipo_exportacion", "Tipo Exportación");
		$pdocrud->colRename("fecha_exportacion", "Fecha Exportación");
		$pdocrud->tableHeading("Descarga Ingreso/Egreso");
		$pdocrud->setSettings("deleteMultipleBtn", false);
		$pdocrud->tableColFormatting("folio", "string",array("type" =>"prefix","str"=>"# "));
		$pdocrud->setSettings("checkboxCol", false);
		$pdocrud->setSettings("addbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$pdocrud->setSettings("template", "exportacion_ingreso_egreso");
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->setSettings("encryption", false);
		$pdocrud->setLangData("actions", "Acción");
		$pdocrud->setSettings("editbtn", false);
		$pdocrud->tableColFormatting("tipo_exportacion", "replace",array("Ingresado" =>"<div class='badge badge-success'>Ingresado</div>"));
		$pdocrud->tableColFormatting("tipo_exportacion", "replace",array("Egresado" =>"<div class='badge badge-danger'>Egresado</div>"));
		$pdocrud->tableColFormatting("fecha_corte", "date",array("format" =>"d-m-Y"));
		$pdocrud->tableColFormatting("fecha_exportacion", "date",array("format" =>"d-m-Y"));
		$pdocrud->setLangData("no_data", "No hay Datos Exportados");
		$pdocrud->setSearchCols(array("folio","tipo_exportacion", "fecha_corte", "cantidad_de_registros", "fecha_exportacion", "usuario_exporta"));
		$render = $pdocrud->dbTable("exportacion_ingreso_egreso")->render();
		View::render('exportacion_ingreso_egreso', [
			'render' => $render
		]);
	}

	public function consultar_datos_examenes_ingresados(){
		$request = new Request();
	
		if ($request->getMethod() === 'POST') {
			$hasta = $request->post('val');
			$hasta_formateada = date("Y-m-d", strtotime($hasta));
	
			$crud = DB::PDOCrud();
			$pdomodel = $crud->getPDOModelObj();
			$data = $pdomodel->DBQuery("
				SELECT 
					dp.id_datos_paciente,
					ds.id_detalle_de_solicitud,
					dp.rut,
					CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS paciente,
					dp.telefono,
					dp.apellido_paterno,
					dp.apellido_materno,
					dp.edad,
					ds.folio,
					ds.fecha_egreso,
					ds.motivo_egreso,
					fecha_solicitud as fecha_solicitud,
					ds.estado AS estado,
					codigo_fonasa AS codigo,
					examen,
					ds.fecha as fecha,
					especialidad,
					CONCAT(nombre_profesional, ' ', apellido_profesional) AS profesional
				FROM 
					datos_paciente AS dp
				INNER JOIN
					detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					profesional AS pro ON pro.id_profesional = dg_p.profesional
				WHERE
					dg_p.fecha_solicitud_paciente = ds.fecha_solicitud
					AND ds.fecha_solicitud <= :hasta
					AND ds.estado = 'Ingresado'
					AND ds.folio IS NULL
				GROUP BY
					dp.id_datos_paciente, dp.rut, dp.edad, ds.fecha, ds.fecha_solicitud, examen",
				[':hasta' => $hasta_formateada]
			);
	
			// Contar la cantidad de registros
			date_default_timezone_set('America/Santiago');
			$total_registros = count($data);

			//print_r($total_registros);
			//echo $pdomodel->getLastQuery();
			//die();

			$sesionUsuario = $_SESSION["usuario"][0]["usuario"];
			$fecha_exportacion = date('Y-m-d');

			if($data){
				$pdomodel->insert("exportacion_ingreso_egreso", array(
					"tipo_exportacion" => "Ingresado",
					"fecha_corte" => $hasta_formateada,
					"cantidad_de_registros" => $total_registros,
					"fecha_exportacion" => $fecha_exportacion,
					"usuario_exporta" => $sesionUsuario
				));
				$folio = $pdomodel->lastInsertId;

				foreach ($data as $registro) {
					$pdomodel->where("id_datos_paciente", $registro["id_datos_paciente"], "=", "AND");
					$pdomodel->where("fecha_solicitud", $registro["fecha_solicitud"], "=", "AND");
					$pdomodel->where("estado", "Ingresado");
					$pdomodel->update("detalle_de_solicitud", ["folio" => $folio]);
				}

				echo json_encode(['mensaje' => 'Datos Exportados con éxito']);
			} else {
				echo json_encode(['error' => 'No se econtraron Datos de Ingreso para exportar']);
			}
		}
	}	

	public function consultar_datos_examenes_egresados(){
		$request = new Request();
	
		if ($request->getMethod() === 'POST') {
			$hasta = $request->post('val');
	
			$crud = DB::PDOCrud();
			$pdomodel = $crud->getPDOModelObj();
			$data = $pdomodel->DBQuery("
				SELECT 
					dp.id_datos_paciente,
					ds.id_detalle_de_solicitud,
					dp.rut,
					CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS paciente,
					dp.telefono,
					dp.apellido_paterno,
					dp.apellido_materno,
					dp.edad,
					ds.folio,
					ds.fecha_egreso,
					ds.motivo_egreso,
					fecha_solicitud as fecha_solicitud,
					ds.estado AS estado,
					codigo_fonasa AS codigo,
					examen,
					ds.fecha as fecha,
					especialidad,
					CONCAT(nombre_profesional, ' ', apellido_profesional) AS profesional
				FROM 
					datos_paciente AS dp
				INNER JOIN
					detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					profesional AS pro ON pro.id_profesional = dg_p.profesional
				WHERE 
					dg_p.fecha_solicitud_paciente = ds.fecha_solicitud
					AND ds.fecha_egreso <= :hasta
					AND ds.estado = 'Egresado'
					AND ds.folio IS NULL
				GROUP BY 
					dp.id_datos_paciente, dp.rut, dp.edad, ds.fecha, ds.fecha_solicitud, examen",
				[':hasta' => $hasta]
			);
	
			// Contar la cantidad de registros
			date_default_timezone_set('America/Santiago');
			$total_registros = count($data);
			$sesionUsuario = $_SESSION["usuario"][0]["usuario"];
			$fecha_exportacion = date('Y-m-d');

			if($data){
				$pdomodel->insert("exportacion_ingreso_egreso", array(
					"tipo_exportacion" => "Egresado",
					"fecha_corte" => $hasta,
					"cantidad_de_registros" => $total_registros,
					"fecha_exportacion" => $fecha_exportacion,
					"usuario_exporta" => $sesionUsuario
				));
				$folio = $pdomodel->lastInsertId;

				foreach ($data as $registro) {
					$pdomodel->where("id_datos_paciente", $registro["id_datos_paciente"], "=", "AND");
					$pdomodel->where("fecha_solicitud", $registro["fecha_solicitud"], "=", "AND");
					$pdomodel->where("estado", "Egresado");
					$pdomodel->update("detalle_de_solicitud", ["folio" => $folio]);
				}

				echo json_encode(['mensaje' => 'Datos Exportados con éxito']);
			} else {
				echo json_encode(['error' => 'No se econtraron Datos de Egreso para exportar']);
			}
		}
	}	

	public function descargar_excel(){
		$request = new Request();
		$folio = $request->get('folio');
		$fechacorte = $request->get('fechacorte');
		$estado = $request->get('estado');

		$fecha_corte_formateada = date("Y-m-d", strtotime($fechacorte));

		$pdocrud = DB::PDOCrud();
		$pdomodel = $pdocrud->getPDOModelObj();
		
		if($estado == "Ingresado"){
			$data = $pdomodel->DBQuery("
				SELECT
					ds.id_detalle_de_solicitud AS ID_LOCAL,
					codigo_fonasa AS PRESTA_MIN,
					codigo_fonasa AS PRESTA_MIN_SALIDA,
					SUBSTRING_INDEX(dp.rut, '-', 1) AS RUN,
					SUBSTRING_INDEX(dp.rut, '-', -1) AS DV,
					dp.nombres AS NOMBRES,
					dp.apellido_paterno AS PRIMER_APELLIDO,
					dp.apellido_materno AS SEGUNDO_APELLIDO,
					dp.fecha_nacimiento AS FECHA_NAC,
					dp.sexo AS SEXO,
					NULL AS PLANO,
					NULL AS EXTREMIDAD,
					ds.tipo_examen AS PRESTA_EST,
					ds.fecha_solicitud AS F_ENTRADA,
					ds.fecha_egreso AS F_SALIDA,
					ds.motivo_egreso AS C_SALIDA,
					dg_p.diagnostico AS SOSPECHA_DIAG,
					dg_p.diagnostico_libre AS CONFIR_DIAG,
					dp.direccion AS NOM_CALLE,
					ds.fecha AS F_CITACION
				FROM 
					datos_paciente AS dp
				INNER JOIN
					detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					profesional AS pro ON pro.id_profesional = dg_p.profesional
				WHERE 
					dg_p.fecha_solicitud_paciente = ds.fecha_solicitud
					AND ds.fecha_solicitud <= :fechacorte
					AND ds.estado = :estado
				GROUP BY 
					dp.id_datos_paciente, dp.rut, dp.edad, ds.fecha, ds.fecha_solicitud, examen",
				[':fechacorte' => $fecha_corte_formateada, ':estado' => $estado]
			);
		} else {
			$data = $pdomodel->DBQuery("
				SELECT
					ds.id_detalle_de_solicitud AS ID_LOCAL,
					codigo_fonasa AS PRESTA_MIN,
					codigo_fonasa AS PRESTA_MIN_SALIDA,
					SUBSTRING_INDEX(dp.rut, '-', 1) AS RUN,
					SUBSTRING_INDEX(dp.rut, '-', -1) AS DV,
					dp.nombres AS NOMBRES,
					dp.apellido_paterno AS PRIMER_APELLIDO,
					dp.apellido_materno AS SEGUNDO_APELLIDO,
					dp.fecha_nacimiento AS FECHA_NAC,
					dp.sexo AS SEXO,
					NULL AS PLANO,
					NULL AS EXTREMIDAD,
					ds.tipo_examen AS PRESTA_EST,
					ds.fecha_solicitud AS F_ENTRADA,
					ds.fecha_egreso AS F_SALIDA,
					ds.motivo_egreso AS C_SALIDA,
					dg_p.diagnostico AS SOSPECHA_DIAG,
					dg_p.diagnostico_libre AS CONFIR_DIAG,
					dp.direccion AS NOM_CALLE,
					ds.fecha AS F_CITACION
				FROM 
					datos_paciente AS dp
				INNER JOIN
					detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					profesional AS pro ON pro.id_profesional = dg_p.profesional
				WHERE 
					dg_p.fecha_solicitud_paciente = ds.fecha_solicitud
					AND ds.fecha_egreso <= :fechacorte
					AND ds.estado = :estado
				GROUP BY 
					dp.id_datos_paciente, dp.rut, dp.edad, ds.fecha, ds.fecha_solicitud, examen",
				[':fechacorte' => $fecha_corte_formateada, ':estado' => $estado]
			);
		}

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$columnLetters = [
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
			'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 
			'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 
			'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
			'AN', 'AO', 'AP', 'AQ'
		];

		// Escribir títulos de columnas
		$columnTitles = [
			'SERV_SALUD',
			'RUN',
			'DV',
			'NOMBRES',
			'PRIMER_APELLIDO',
			'SEGUNDO_APELLIDO',
			'FECHA_NAC',
			'SEXO',
			'PREVICION',
			'TIPO_PREST',
			'PRESTA_MIN',
			'PLANO',
			'EXTREMIDAD',
			'PRESTA_EST',
			'F_ENTRADA',
			'ESTAB_ORIG',
			'ESTAB_DEST',
			'F_SALIDA',
			'C_SALIDA',
			'E_OTOR_AT',
			'PRESTA_MIN_SALIDA',
			'PRAIS',
			'REGION',
			'COMUNA',
			'SOSPECHA_DIAG',
			'CONFIR_DIAG',
			'CIUDAD',
			'COND_RURALIDAD',
			'VIA_DIRECCION',
			'NOM_CALLE',
			'NUM_DIRECCION',
			'RESTO_DIRECCION',
			'FONO_FIJO',
			'FONO_MOVIL',
			'EMAIL',
			'F_CITACION',
			'RUN_PROF_SOL',
			'DV_PROF_SOL',
			'RUN_PROF_RESOL',
			'DV_PROF_RESOL',
			'ID_LOCAL',
			'RESULTADO',
			'SIGTE_ID'
		];
	
		// Asignar valores predeterminados a los campos faltantes
		$defaultValues = [
			'SERV_SALUD' => '10',
			'PREVICION' => '1',
			'TIPO_PREST' => '3',
			'PRAIS' => '2',
			'REGION' => '13',
			'COMUNA' => '13501',
			'CIUDAD' => 'MELIPILLA',
			'COND_RURALIDAD' => '01',
			'VIA_DIRECCION' => '04',
			'ESTAB_ORIG' => '110150',
			'ESTAB_DEST' => '110150',
			'E_OTOR_AT' => '110150',
			'NUM_DIRECCION' => '',
			'RESTO_DIRECCION' => '',
			'FONO_FIJO' => '',
			'FONO_MOVIL' => '',
			'EMAIL' => '',
			'RESULTADO' => '',
			'SIGTE_ID' => '',
			'RUN_PROF_RESOL' => '',
			'DV_PROF_RESOL' => '',
			'RUN_PROF_SOL' => '',
			'DV_PROF_SOL' => '',
		];

		foreach ($columnTitles as $index => $title) {
			$sheet->setCellValue($columnLetters[$index] . '1', $title);
		}
	
		$rowIndex = 2;
		foreach ($data as $row) {
			// Inicializar los datos de la fila
			$rowData = [];

			// Asignar los valores de los campos de la consulta
			foreach ($columnTitles as $title) {
				$rowData[$title] = $row[$title] ?? ''; // Si el campo no está definido en la consulta, se añade un valor vacío
			}

			// Añadir los valores predeterminados para los campos faltantes
			foreach ($defaultValues as $field => $value) {
				if (empty($rowData[$field])) {
					$rowData[$field] = $value;
				}
			}

			// Escribir los datos en las letras de columnas correspondientes
			foreach ($rowData as $title => $value) {
				$columnIndex = array_search($title, $columnTitles);
				$sheet->setCellValue($columnLetters[$columnIndex] . $rowIndex, $value);
			}

			$rowIndex++;
		}

		// Crear un objeto Writer y guardar el archivo
		$writer = new Xlsx($spreadsheet);
		$filename = 'archivo.xlsx';
		$writer->save($filename);

		// Descargar el archivo
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
		exit;
	}


	public function profesionales(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->enqueueBtnTopActions("Report",  "<i class='fa fa-upload'></i> Carga Masiva", "javascript:;", array(), "btn-report btn btn-light carga_masiva");
		$pdocrud->addPlugin("bootstrap-inputmask");
		$pdocrud->formDisplayInPopup();
		$pdocrud->fieldCssClass("rut_profesional", array("rut_profesional"));
		$pdocrud->colRename("id_profesional", "ID");
		$pdocrud->setSearchCols(array("nombre_profesional","apellido_profesional", "rut_profesional"));
		$pdocrud->buttonHide("submitBtnSaveBack");
		$pdocrud->setSettings("checkboxCol", false);
		$pdocrud->setSettings("deleteMultipleBtn", false);
		$pdocrud->setSettings("delbtn", false);
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->where("apellido_profesional", "y apellido no especificado", "!=");
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$pdocrud->addCallback("before_insert", "agregar_profesional");
		$pdocrud->addCallback("before_update", "modificar_profesional");
		$render = $pdocrud->dbTable("profesional")->render();
		$mask = $pdocrud->loadPluginJsCode("bootstrap-inputmask",".rut_profesional", array("mask"=> "'9{1,2}9{3}9{3}-(9|k|K)'", "casing" => "'upper'", "clearIncomplete" => "true", "numericInput"=> "true", "positionCaretOnClick" => "'none'"));

		$crud = DB::PDOCrud(true);
		$crud->fieldRenameLable("archivo", "Archivo Excel");
		$crud->setLangData("save", "Subir");
		$crud->setSettings("required", false);
		$crud->fieldTypes("archivo", "FILE_NEW");
		$crud->addCallback("before_insert", "carga_masiva_profesionales_insertar");
		$masivo = $crud->dbTable("carga_masiva_profesionales")->render("insertform");
		View::render(
			'profesional', [
				'render' => $render,
				'mask' => $mask,
				'masivo' => $masivo
			]
		);
	}

	public function imprimir_solicitud(){
		
			$request = new Request();
			$id = $request->get('id');
			$id_detalle_de_solicitud = $request->get('id_detalle_de_solicitud');

			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();
			$data = $pdomodel->DBQuery(
				"SELECT 
				dp.id_datos_paciente,
				ds.id_detalle_de_solicitud,
				dp.rut,
				dp.nombres,
				dp.apellido_paterno,
				dp.apellido_materno,
				dp.edad,
				ds.fecha_egreso,
				fecha_solicitud as fecha_solicitud,
				ds.estado AS estado,
				codigo_fonasa AS codigo,
				examen,
				ds.fecha as fecha,
				especialidad,
				fundamento,
				motivo_egreso,
				observacion,
				CONCAT(nombre_profesional, ' ', apellido_profesional) AS profesional
			FROM 
				datos_paciente AS dp
			INNER JOIN
				detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
			INNER JOIN 
				diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
			INNER JOIN 
				profesional AS pro ON pro.id_profesional = dg_p.profesional
			WHERE 
				dg_p.fecha_solicitud_paciente = ds.fecha_solicitud AND ds.id_detalle_de_solicitud = ".$id_detalle_de_solicitud
			);
			
			$pdomodel->where("id_causal_salida", $data[0]["motivo_egreso"]);
			$motivo_egreso = $pdomodel->select("causal_salida");

			$nombre = isset($motivo_egreso[0]["nombre"]) ? $motivo_egreso[0]["nombre"] : '';

			$fecha = date('d/m/Y H:i:s', strtotime($data[0]["fecha"]));
			$data_fecha = ($fecha != "01/01/1970" && $fecha != "31/12/1969") ? $fecha : 'Sin Fecha';

			$fecha_egreso = date('d/m/Y', strtotime($data[0]["fecha_egreso"]));
			$data_fecha_egreso = ($fecha_egreso != "01/01/1970" && $fecha_egreso != "31/12/1969") ? $fecha_egreso : 'Sin Fecha';

			if($data[0]["fecha_solicitud"] != "0000-00-00 00:00:00") {
				$obt = date('d/m/Y', strtotime($data[0]["fecha_solicitud"]));
			} else {
				$obt = 'Sin Fecha';
			}

			$codigos = explode(',', $data[0]["codigo"]);

			$code = "";
			foreach ($codigos as $codigo) {
				$code .= '<div class="badge badge-info">'. $codigo . '</div>' . '<br>';
			}

			$exam = str_replace(' - ', "<br>", $data[0]["examen"]);

			$examArray = explode('<br>', $exam);
			foreach ($examArray as $key => $element) {
				$examArray[$key] = ($key + 1) . '. ' . $element;
			}

			$edad = ($data[0]["edad"] != 0) ? $data[0]["edad"] : 'Sin Edad';

			// Unir de nuevo el array en una cadena con saltos de línea
			$exam = implode("<br>", $examArray);

			$profesional = str_replace(',', "<br>", $data[0]["profesional"]);
			$especialidad = str_replace(',', "<br>", $data[0]["especialidad"]);
	
			$xinvoice = new Xinvoice();
			$xinvoice->setInvoiceDisplaySettings("header","", false);
			$xinvoice->setInvoiceDisplaySettings("to","", false);
			$xinvoice->setInvoiceDisplaySettings("from","", false);
			$xinvoice->setInvoiceDisplaySettings("footer", "", false);
			$xinvoice->setInvoiceDisplaySettings("payment", "", false);
			$xinvoice->setInvoiceDisplaySettings("message", "", false);
			$xinvoice->setInvoiceDisplaySettings("total","subtotal", false);
			$xinvoice->setInvoiceDisplaySettings("total","discount", false);
			$xinvoice->setInvoiceDisplaySettings("total","tax", false);
			$xinvoice->setInvoiceDisplaySettings("total","shipping", false);
			$xinvoice->setInvoiceDisplaySettings("total","grandtotal", false);
			$url = $_ENV["BASE_URL"];
			$xinvoice->setInvoiceSections("before_header", "
				<style>
					table {
						width: 100%;
						border-collapse: collapse;
					}
			
					.ancho-extra {
						width: 70%;
						text-align:center;
					}
					.texto {
						height: 50px;
						font-size: 16px;
					}
					.table td {
						border: 1px solid #ddd;
					}
				</style>
				<table>
					<tr>
						<td>
							<img src='".$url."theme/img/logo_gobiernochile.jpg'>
						</td>
						<td class='ancho-extra'>
							<h5 class='texto'>LISTA ESPERA EXÁMENES<h5>
						</td>
						<td>
							<img width='150' src='".$url."theme/img/logo_hsjm.jpg'>
						</td>
					</tr>
				</table>
			");
			$xinvoice->setInvoiceSections("before_items", "
				<table class='table table-bordered table-striped table-condensed'>            
					<tbody>
						<tr>
							<td><strong>Rut</strong></td>
							<td>".$data[0]["rut"]."</td>
						</tr>
						<tr>
							<td><strong>Paciente</strong></td>
							<td>".ucwords($data[0]["nombres"]). ' ' . ucwords($data[0]["apellido_paterno"]). ' ' . ucwords($data[0]["apellido_materno"])."</td>
						</tr>
						<tr>
							<td><strong>Edad</strong></td>
							<td>".$edad."</td>
						</tr>
						<tr>
							<td><strong>Fecha Solicitud</strong></td>
							<td>".$obt."</td>
						</tr>
						<tr>
							<td><strong>Estado</strong></td>
							<td>".$data[0]["estado"]."</td>
						</tr>
						<tr>
							<td><strong>Código</td>
							<td>".$code."</td>
						</tr>
						<tr>
							<td><strong>Exámen</strong></td>
							<td>".$exam."</td>
						</tr>
						<tr>
							<td><strong>Fecha Agendada</strong></td>
							<td>".$data_fecha."</td>
						</tr>
						<tr>
							<td><strong>Especialidad</strong></td>
							<td>".$especialidad."</td>
						</tr>
						<tr>
							<td><strong>Profesional</strong></td>
							<td>".$profesional."</td>
						</tr>
						<tr>
							<td><strong>Fundamento</strong></td>
							<td>".$data[0]["fundamento"]."</td>
						</tr>
						<tr>
							<td><strong>Fecha Egreso</strong></td>
							<td>".$data_fecha_egreso."</td>
						</tr>
						<tr>
							<td><strong>Motivo Egreso</strong></td>
							<td>".$nombre."</td>
						</tr>
						<tr>
							<td><strong>Observación</strong></td>
							<td>".$data[0]["observacion"]."</td>
						</tr>
					</tbody>
				</table>
			");
			$xinvoice->setInvoiceSections("before_footer", "
					<table style='margin:100px auto; width: 50%;'>
						<tr>
							<td style='text-align:center;'>
								".ucwords($profesional)."
								<hr>
								<p>Médico Tratante</p>
							</td>
						</tr>
					</table>
			");
			$xinvoice->setSettings("filename", "lista_espera_examenes.pdf");
			//$xinvoice->setSettings("output", "F");
			echo $xinvoice->render();
	}

	public function lista_gestion(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->crudTableCol(array(
			"id",
			"fecha_solicitud",
			"rut", 
			"servicio",
			"procedimiento", 
			"nombres",
			"apellido_paterno", 
			"apellido_materno", 
			"profesional_solicitante", 
			"numero_contacto", 
			"prioridad", 
			"estado"
		));
		$pdocrud->colRename("id", "ID");
		$pdocrud->setSettings("searchbox", false);
		$pdocrud->setSettings("checkboxCol", false);
		$pdocrud->setSettings("deleteMultipleBtn", false);
		$pdocrud->setSettings("addbtn", false);
		$pdocrud->setSettings("recordsPerPageDropdown", false);
		$pdocrud->where("estado", "PENDIENTE", "AND");
		$pdocrud->where("fecha_registro", "CURDATE()");
		$pdocrud->dbOrderBy("id", "desc");
		$pdocrud->setSettings("actionbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$render = $pdocrud->dbTable("procedimiento")->render();
		View::render(
			'lista_gestion', ['render' => $render]
		);
	}

	public function lista_procedimiento(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->where("estado", "null");
		$pdocrud->colRename("id", "ID");
		$pdocrud->crudTableCol(array(
			"id",
			"fecha_registro",
			"fecha_solicitud",
			"servicio",
			"procedimiento",
			"rut",
			"nombres",
			"apellido_paterno",
			"apellido_materno", 
			"profesional_solicitante",
			"numero_contacto",
			"prioridad",
			"estado"
		));
		$pdocrud->setSettings("searchbox", false);
		$pdocrud->setSettings("recordsPerPageDropdown", false);
		$pdocrud->setSettings("deleteMultipleBtn", false);
		$pdocrud->setSettings("checkboxCol", false);
		$pdocrud->setSettings("addbtn", false);
		$pdocrud->setSettings("actionbtn", false);
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->setSettings("editbtn", false);
		$pdocrud->setSettings("delbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$render = $pdocrud->dbTable("procedimiento")->render();
		View::render(
			'lista_procedimiento', ['render' => $render]
		);
	}

	public function procedimientos_pendientes(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->where("estado", "null");
		$pdocrud->colRename("id", "ID");
		$pdocrud->crudTableCol(array(
			"id",
			"fecha_registro",
			"fecha_solicitud",
			"servicio",
			"procedimiento",
			"rut",
			"nombres",
			"apellido_paterno",
			"apellido_materno", 
			"profesional_solicitante",
			"numero_contacto",
			"prioridad",
			"estado"
		));
		$pdocrud->setSettings("searchbox", false);
		$pdocrud->setSettings("recordsPerPageDropdown", false);
		$pdocrud->setSettings("deleteMultipleBtn", false);
		$pdocrud->setSettings("checkboxCol", false);
		$pdocrud->setSettings("addbtn", false);
		$pdocrud->setSettings("actionbtn", false);
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->setSettings("editbtn", false);
		$pdocrud->setSettings("delbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$render = $pdocrud->dbTable("procedimiento")->render();
		View::render(
			'pendiente', ['render' => $render]
		);
	}

	public function procedimientos_realizados(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->where("estado", "null");
		$pdocrud->colRename("id", "ID");
		$pdocrud->crudTableCol(array(
			"id",
			"fecha_registro",
			"fecha_solicitud",
			"servicio",
			"procedimiento",
			"rut",
			"nombres",
			"apellido_paterno",
			"apellido_materno", 
			"profesional_solicitante",
			"numero_contacto",
			"prioridad",
			"estado"
		));
		$pdocrud->setSettings("searchbox", false);
		$pdocrud->setSettings("recordsPerPageDropdown", false);
		$pdocrud->setSettings("deleteMultipleBtn", false);
		$pdocrud->setSettings("checkboxCol", false);
		$pdocrud->setSettings("addbtn", false);
		$pdocrud->setSettings("actionbtn", false);
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->setSettings("editbtn", false);
		$pdocrud->setSettings("delbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$render = $pdocrud->dbTable("procedimiento")->render();
		View::render(
			'realizado', ['render' => $render]
		);
	}

	public function listar_procedimiento_por_rut(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->addPlugin("bootstrap-inputmask");
		$pdocrud->where("estado", "null");
		$pdocrud->colRename("id", "ID");
		$pdocrud->crudTableCol(array(
			"id",
			"fecha_registro",
			"fecha_solicitud",
			"servicio",
			"procedimiento",
			"rut",
			"nombres",
			"apellido_paterno",
			"apellido_materno", 
			"profesional_solicitante",
			"numero_contacto",
			"prioridad",
			"estado"
		));
		$pdocrud->setSettings("searchbox", false);
		$pdocrud->setSearchCols(array("rut"));
		$pdocrud->setSettings("recordsPerPageDropdown", false);
		$pdocrud->setSettings("deleteMultipleBtn", false);
		$pdocrud->setSettings("showAllSearch", false);
		$pdocrud->setSettings("checkboxCol", false);
		$pdocrud->setSettings("addbtn", false);
		$pdocrud->setSettings("actionbtn", false);
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->setSettings("editbtn", false);
		$pdocrud->setSettings("delbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$render = $pdocrud->dbTable("procedimiento")->render();
		$mask = $pdocrud->loadPluginJsCode("bootstrap-inputmask",".rut", array("mask"=> "'9{1,2}9{3}9{3}-(9|k|K)'", "casing" => "'upper'", "clearIncomplete" => "true", "numericInput"=> "true", "positionCaretOnClick" => "'none'"));
		View::render(
			'listar_procedimiento_por_rut', ['render' => $render, 'mask' => $mask]
		);
	}

	public function buscar_rut(){

		$request = new Request();

		if ($request->getMethod() === 'POST') {
			$rut = $request->post('rut');

			$pdocrud = DB::PDOCrud(true);
			$pdocrud->tableColFormatting("fecha_registro", "date",array("format" =>"d-m-Y"));
			if(!empty($rut)){
				$pdocrud->where("rut", "%$rut%", "LIKE");
			} else {
				$pdocrud->where("estado", "null");
			}
			$pdocrud->colRename("id", "ID");
			$pdocrud->crudTableCol(array(
				"id",
				"fecha_registro",
				"fecha_solicitud",
				"servicio",
				"procedimiento",
				"rut",
				"nombres",
				"apellido_paterno",
				"apellido_materno", 
				"profesional_solicitante",
				"numero_contacto",
				"prioridad",
				"estado"
			));
			$pdocrud->enqueueCSS("style", $_ENV["BASE_URL"] . "app/libs/script/css/style.css");
            $pdocrud->enqueueCSS("ui", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui.css");
            $pdocrud->enqueueCSS("uicss", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui-timepicker-addon.css");
            $pdocrud->enqueueCSS("font", $_ENV["BASE_URL"] . "app/libs/script/css/font-awesome.min.css");
            $pdocrud->enqueueCSS("pure", $_ENV["BASE_URL"] . "app/libs/script/skin/advance.css");
			$pdocrud->setSettings("searchbox", false);
			$pdocrud->setSettings("recordsPerPageDropdown", false);
			$pdocrud->setSettings("deleteMultipleBtn", false);
			$pdocrud->setSettings("checkboxCol", false);
			$pdocrud->setSettings("addbtn", false);
			$pdocrud->setSettings("actionbtn", false);
			$pdocrud->setSettings("viewbtn", false);
			$pdocrud->setSettings("editbtn", false);
			$pdocrud->setSettings("delbtn", false);
			$pdocrud->setSettings("printBtn", false);
			$pdocrud->setSettings("pdfBtn", false);
			$pdocrud->setSettings("csvBtn", false);
			$pdocrud->setSettings("excelBtn", false);
			echo $pdocrud->dbTable("procedimiento")->render();
		}
	}

	public function buscar_gestion(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {

			$val = $request->post("val");

			$pdocrud = DB::PDOCrud(true);
			if(!empty($val)){
				$pdocrud->where("id", "%$val%", "LIKE", "OR");
				$pdocrud->where("fecha_registro", "%$val%", "LIKE", "OR");
				$pdocrud->where("fecha_solicitud", "%$val%", "LIKE", "OR");
				$pdocrud->where("servicio", "%$val%", "LIKE", "OR");
				$pdocrud->where("procedimiento", "%$val%", "LIKE", "OR");
				$pdocrud->where("rut", "%$val%", "LIKE", "OR");
				$pdocrud->where("nombres", "%$val%", "LIKE", "OR");
				$pdocrud->where("apellido_paterno", "%$val%", "LIKE", "OR");
				$pdocrud->where("apellido_materno", "%$val%", "LIKE", "OR");
				$pdocrud->where("profesional_solicitante", "%$val%", "LIKE", "OR");
				$pdocrud->where("numero_contacto", "%$val%", "LIKE", "OR");
				$pdocrud->where("prioridad", "%$val%", "LIKE", "OR");
				$pdocrud->where("estado", "%$val%", "LIKE");
			} else {
				$pdocrud->where("estado", "null");
			}

			$pdocrud->enqueueCSS("style", $_ENV["BASE_URL"] . "app/libs/script/css/style.css");
            $pdocrud->enqueueCSS("ui", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui.css");
            $pdocrud->enqueueCSS("uicss", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui-timepicker-addon.css");
            $pdocrud->enqueueCSS("font", $_ENV["BASE_URL"] . "app/libs/script/css/font-awesome.min.css");
            $pdocrud->enqueueCSS("pure", $_ENV["BASE_URL"] . "app/libs/script/skin/advance.css");
			$pdocrud->setSettings("searchbox", false);
			$pdocrud->setSettings("recordsPerPageDropdown", false);
			$pdocrud->setSettings("deleteMultipleBtn", false);
			$pdocrud->setSettings("checkboxCol", false);
			$pdocrud->setSettings("addbtn", false);
			$pdocrud->setSettings("actionbtn", false);
			$pdocrud->setSettings("viewbtn", false);
			$pdocrud->setSettings("editbtn", false);
			$pdocrud->setSettings("delbtn", false);
			$pdocrud->setSettings("printBtn", false);
			$pdocrud->setSettings("pdfBtn", false);
			$pdocrud->setSettings("csvBtn", false);
			$pdocrud->setSettings("excelBtn", false);
			echo $pdocrud->dbTable("procedimiento")->render();
		}
	}

	public function buscar_rango(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {

			$fecha_de = $request->post('fecha_de');
			$fecha_a = $request->post('fecha_a');

			$pdocrud = DB::PDOCrud(true);
			if (!empty($fecha_de) && !empty($fecha_a)) {
				// Si ambos valores están presentes, filtra por rango
				$pdocrud->where("fecha_registro", "%$fecha_de%", "LIKE", "AND");
				$pdocrud->where("fecha_registro", "%$fecha_a%", "LIKE");
			} elseif (!empty($fecha_de)) {
				// Si solo hay fecha_de, filtra por fecha_de
				$pdocrud->where("fecha_registro", "%$fecha_de%", "LIKE");
			} elseif (!empty($fecha_a)) {
				// Si solo hay fecha_a, filtra por fecha_a
				$pdocrud->where("fecha_registro", "%$fecha_a%", "LIKE");
			} else {
				$pdocrud->where("estado", "null");
			}
			$pdocrud->colRename("id", "ID");
			$pdocrud->crudTableCol(array(
				"id",
				"fecha_registro",
				"fecha_solicitud",
				"servicio",
				"procedimiento",
				"rut",
				"nombres",
				"apellido_paterno",
				"apellido_materno", 
				"profesional_solicitante",
				"numero_contacto",
				"prioridad",
				"estado"
			));
			$pdocrud->enqueueCSS("style", $_ENV["BASE_URL"] . "app/libs/script/css/style.css");
            $pdocrud->enqueueCSS("ui", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui.css");
            $pdocrud->enqueueCSS("uicss", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui-timepicker-addon.css");
            $pdocrud->enqueueCSS("font", $_ENV["BASE_URL"] . "app/libs/script/css/font-awesome.min.css");
            $pdocrud->enqueueCSS("pure", $_ENV["BASE_URL"] . "app/libs/script/skin/advance.css");
			$pdocrud->setSettings("searchbox", false);
			$pdocrud->tableColFormatting("fecha_registro", "date",array("format" =>"d-m-Y"));
			$pdocrud->setSettings("recordsPerPageDropdown", false);
			$pdocrud->setSettings("deleteMultipleBtn", false);
			$pdocrud->setSettings("checkboxCol", false);
			$pdocrud->setSettings("addbtn", false);
			$pdocrud->setSettings("actionbtn", false);
			$pdocrud->setSettings("viewbtn", false);
			$pdocrud->setSettings("editbtn", false);
			$pdocrud->setSettings("delbtn", false);
			$pdocrud->setSettings("printBtn", false);
			$pdocrud->setSettings("pdfBtn", false);
			$pdocrud->setSettings("csvBtn", false);
			$pdocrud->setSettings("excelBtn", false);
			echo $pdocrud->dbTable("procedimiento")->render();
		}
	}

	public function buscar_rango_realizado(){
		
		$request = new Request();

		if ($request->getMethod() === 'POST') {

			$fecha_de = $request->post('fecha_de');
			$fecha_a = $request->post('fecha_a');

			$pdocrud = DB::PDOCrud(true);
			if (!empty($fecha_de) && !empty($fecha_a)) {
				// Si ambos valores están presentes, filtra por rango
				$pdocrud->where("fecha_registro", "%$fecha_de%", "LIKE", "AND");
				$pdocrud->where("fecha_registro", "%$fecha_a%", "LIKE");
			} elseif (!empty($fecha_de)) {
				// Si solo hay fecha_de, filtra por fecha_de
				$pdocrud->where("fecha_registro", "%$fecha_de%", "LIKE");
			} elseif (!empty($fecha_a)) {
				// Si solo hay fecha_a, filtra por fecha_a
				$pdocrud->where("fecha_registro", "%$fecha_a%", "LIKE");
			} else {
				$pdocrud->where("estado", "null");
			}
			$pdocrud->colRename("id", "ID");
			$pdocrud->crudTableCol(array(
				"id",
				"fecha_registro",
				"fecha_solicitud",
				"servicio",
				"procedimiento",
				"rut",
				"nombres",
				"apellido_paterno",
				"apellido_materno", 
				"profesional_solicitante",
				"numero_contacto",
				"prioridad",
				"estado"
			));
			$pdocrud->enqueueCSS("style", $_ENV["BASE_URL"] . "app/libs/script/css/style.css");
            $pdocrud->enqueueCSS("ui", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui.css");
            $pdocrud->enqueueCSS("uicss", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui-timepicker-addon.css");
            $pdocrud->enqueueCSS("font", $_ENV["BASE_URL"] . "app/libs/script/css/font-awesome.min.css");
            $pdocrud->enqueueCSS("pure", $_ENV["BASE_URL"] . "app/libs/script/skin/advance.css");
			$pdocrud->setSettings("searchbox", false);
			$pdocrud->tableColFormatting("fecha_registro", "date",array("format" =>"d-m-Y"));
			$pdocrud->setSettings("recordsPerPageDropdown", false);
			$pdocrud->setSettings("deleteMultipleBtn", false);
			$pdocrud->setSettings("checkboxCol", false);
			$pdocrud->setSettings("addbtn", false);
			$pdocrud->setSettings("actionbtn", false);
			$pdocrud->setSettings("viewbtn", false);
			$pdocrud->setSettings("editbtn", false);
			$pdocrud->setSettings("delbtn", false);
			$pdocrud->setSettings("printBtn", false);
			$pdocrud->setSettings("pdfBtn", false);
			$pdocrud->setSettings("csvBtn", false);
			$pdocrud->setSettings("excelBtn", false);
			echo $pdocrud->dbTable("procedimiento")->render();
		}
	}

	public function buscar_rango_pendiente(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {

			$fecha_de = $request->post('fecha_de');
			$fecha_a = $request->post('fecha_a');

			$pdocrud = DB::PDOCrud(true);
			if (!empty($fecha_de) && !empty($fecha_a)) {
				// Si ambos valores están presentes, filtra por rango
				$pdocrud->where("fecha_registro", "%$fecha_de%", "LIKE", "AND");
				$pdocrud->where("fecha_registro", "%$fecha_a%", "LIKE");
			} elseif (!empty($fecha_de)) {
				// Si solo hay fecha_de, filtra por fecha_de
				$pdocrud->where("fecha_registro", "%$fecha_de%", "LIKE");
			} elseif (!empty($fecha_a)) {
				// Si solo hay fecha_a, filtra por fecha_a
				$pdocrud->where("fecha_registro", "%$fecha_a%", "LIKE");
			} else {
				$pdocrud->where("estado", "null");
			}
			$pdocrud->colRename("id", "ID");
			$pdocrud->crudTableCol(array(
				"id",
				"fecha_registro",
				"fecha_solicitud",
				"servicio",
				"procedimiento",
				"rut",
				"nombres",
				"apellido_paterno",
				"apellido_materno", 
				"profesional_solicitante",
				"numero_contacto",
				"prioridad",
				"estado"
			));
			$pdocrud->tableColFormatting("fecha_registro", "date",array("format" =>"d-m-Y"));
			$pdocrud->enqueueCSS("style", $_ENV["BASE_URL"] . "app/libs/script/css/style.css");
            $pdocrud->enqueueCSS("ui", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui.css");
            $pdocrud->enqueueCSS("uicss", $_ENV["BASE_URL"] . "app/libs/script/css/jquery-ui-timepicker-addon.css");
            $pdocrud->enqueueCSS("font", $_ENV["BASE_URL"] . "app/libs/script/css/font-awesome.min.css");
            $pdocrud->enqueueCSS("pure", $_ENV["BASE_URL"] . "app/libs/script/skin/advance.css");
			$pdocrud->setSettings("searchbox", false);
			$pdocrud->setSettings("recordsPerPageDropdown", false);
			$pdocrud->setSettings("deleteMultipleBtn", false);
			$pdocrud->setSettings("checkboxCol", false);
			$pdocrud->setSettings("addbtn", false);
			$pdocrud->setSettings("actionbtn", false);
			$pdocrud->setSettings("viewbtn", false);
			$pdocrud->setSettings("editbtn", false);
			$pdocrud->setSettings("delbtn", false);
			$pdocrud->setSettings("printBtn", false);
			$pdocrud->setSettings("pdfBtn", false);
			$pdocrud->setSettings("csvBtn", false);
			$pdocrud->setSettings("excelBtn", false);
			echo $pdocrud->dbTable("procedimiento")->render();
		}
	}

	public function codigo(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->enqueueBtnTopActions("Report",  "<i class='fa fa-upload'></i> Carga Masiva", "javascript:;", array(), "btn-report btn btn-light carga_masiva");
		$pdocrud->setSearchCols(array("codigo_o","operacion"));
		$pdocrud->colRename("codigo_o", "Código");
		$pdocrud->colRename("operacion", "Descripción");
		$pdocrud->fieldRenameLable("codigo_o", "Código");
		$pdocrud->fieldRenameLable("operacion", "Descripción");
		$pdocrud->tableHeading("Mantenedor CIE-10");
		$pdocrud->formDisplayInPopup();
		$pdocrud->where("codigo_o", "Línea", "!=");
		$pdocrud->crudRemoveCol(array("id"));
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$pdocrud->buttonHide("submitBtnSaveBack");
		$render = $pdocrud->dbTable("codigo")->render();

		$carga = DB::PDOCrud(true);
		$carga->fieldRenameLable("archivo", "Archivo Excel");
		$carga->setLangData("save", "Subir");
		$carga->setSettings("required", false);
		$carga->fieldTypes("archivo", "FILE_NEW");
		$carga->addCallback("before_insert", "carga_masiva_codigo_insertar");
		$render2 = $carga->dbTable("carga_masiva_codigo")->render("insertform");

		View::render("codigo", [
			'render' => $render,
			'render2' => $render2
		]);
	}

	public function usuarios()
	{
		if($_SESSION["usuario"][0]["idrol"] == 1){
            $token = $this->token;
			$pdocrud = DB::PDOCrud();
			$pdocrud->fieldCssClass("id", array("d-none"));
			$pdocrud->tableHeading("Lista de usuarios");
            $pdocrud->formStaticFields("token_form", "html", "<input type='hidden' name='auth_token' value='" . $token . "' />");
			$pdocrud->tableColFormatting("avatar", "html",array("type" =>"html","str"=>'<img width="80" src="'.$_ENV["BASE_URL"].'app/libs/script/uploads/{col-name}">'));
			$pdocrud->fieldDataAttr("password", array("value"=>"", "placeholder" => "*****", "autocomplete" => "new-password"));
			$pdocrud->formDisplayInPopup();
			$pdocrud->fieldGroups("Name",array("nombre","email"));
			$pdocrud->fieldGroups("Name2",array("usuario","password"));
			$pdocrud->fieldGroups("Name3",array("idrol","avatar"));
			$pdocrud->setSettings("required", false);
			$pdocrud->setSettings("checkboxCol", false);
			$pdocrud->setSettings("deleteMultipleBtn", false);
			$pdocrud->colRename("id", "ID");
			$pdocrud->colRename("idrol", "Rol");
			$pdocrud->colRename("email", "Correo");
			$pdocrud->fieldHideLable("id");
			$pdocrud->addCallback("before_insert", "insetar_usuario");
			$pdocrud->addCallback("before_update", "editar_usuario");
			$pdocrud->crudRemoveCol(array("rol","estatus","password", "token", "token_api", "expiration_token"));
			$pdocrud->setSearchCols(array("id","nombre","email", "usuario", "idrol"));
			$pdocrud->where("estatus", 1);
			$pdocrud->recordsPerPage(5);
			$pdocrud->fieldTypes("avatar", "FILE_NEW");
			$pdocrud->fieldTypes("password", "password");
			$pdocrud->fieldRenameLable("nombre", "Nombre Completo");
			$pdocrud->fieldRenameLable("email", "Correo electrónico");
			$pdocrud->fieldRenameLable("password", "Clave de acceso");
			$pdocrud->fieldRenameLable("idrol", "Tipo Usuario");
			$pdocrud->setSettings("viewbtn", false);
			$pdocrud->setSettings("hideAutoIncrement", false);
			$pdocrud->setSettings("template", "usuarios");
			$pdocrud->buttonHide("submitBtnSaveBack");
			$pdocrud->formFields(array("id","nombre","email","password","usuario", "idrol", "avatar"));
			$pdocrud->setRecordsPerPageList(array(5, 10, 15, 'All'=> 'Todo'));
			$pdocrud->setSettings("printBtn", false);
			$pdocrud->setSettings("pdfBtn", false);
			$pdocrud->setSettings("csvBtn", false);
			$pdocrud->setSettings("excelBtn", false);
			$pdocrud->relatedData('idrol','rol','idrol','nombre_rol');
			$render = $pdocrud->dbTable("usuario")->render();

			$carga_masiva_usuarios = DB::PDOCrud(true);
			$carga_masiva_usuarios->fieldRenameLable("archivo", "Archivo Excel");
			$carga_masiva_usuarios->setLangData("save", "Subir");
			$carga_masiva_usuarios->setSettings("required", false);
			$carga_masiva_usuarios->fieldTypes("archivo", "FILE_NEW");
			$carga_masiva_usuarios->addCallback("before_insert", "carga_masiva_usuarios_insertar");
			$upload = $carga_masiva_usuarios->dbTable("carga_masiva_usuarios")->render("insertform");

			View::render('home',
				['render' => $render, 'upload' => $upload]);
		} else {
			Redirect::to("Home/datos_paciente");
		}
	}


	public function generar_datos_usuario(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$usuario_session = $_SESSION["usuario"][0]["id"];
			$usuario = new UserModel();
			$usuariodb = $usuario->obtener_usuario_porId($usuario_session);

			$_SESSION["usuario"] = $usuariodb;

			echo json_encode(['usuario' => $usuariodb]);
		}
	}

	public function generar_edad(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$fecha_nac = $request->post("fecha_nac");

			if(!empty($fecha_nac)){
				$fechaNacimiento = HomeController::calcularFechaNacimiento($fecha_nac);
				if($fechaNacimiento >= 0){
					echo json_encode(['fecha_nacimiento' => $fechaNacimiento]);
				} else {
					echo json_encode(['error' => 'La fecha de nacimiento no se pudo calcular, ingrese una mas antigua']);
				}
			}
		}
	}

	public static function calcularFechaNacimiento($fecha_nac){
		$fecha_nac = strtotime($fecha_nac);
		$edad = date('Y', $fecha_nac);
		if (($mes = (date('m') - date('m', $fecha_nac))) < 0) {
			$edad++;
		} elseif ($mes == 0 && date('d') - date('d', $fecha_nac) < 0) {
			$edad++;
		}
		return date('Y') - $edad;
	}

	public function obtener_pacientes(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->columns = array("id_datos_paciente", "nombres", "apellido_paterno", "apellido_materno");
			$data = $pdomodel->select("datos_paciente");

			if($data){
				echo json_encode(['data' => $data]);
			} else {
				echo json_encode(['error' => 'No hay Pacientes']);
			}
		}
	}

	public function obtener_profesionales(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->columns = array("id_profesional", "nombre_profesional", "apellido_profesional");
			$data = $pdomodel->select("profesional");

			if($data){
				echo json_encode(['data' => $data]);
			} else {
				echo json_encode(['error' => 'No hay Profesionales']);
			}
		}
	}

	public function datos_paciente(){

		date_default_timezone_set('America/Santiago');
		$fecha_registro = date('Y-m-d H:i:s');
		$fecha_solicitud = date('Y-m-d');

		
		//Ejemplo de como usar el paginador simple
		/*$registros_por_pagina = 10;
		$parametro = "pagina";
		$pagina_actual = isset($_GET[$parametro]) ? $_GET[$parametro] : 1; // Suponiendo que la página actual está en la URL.

		// Llama a la función performPagination
		$paginationResult = DB::performPagination($registros_por_pagina, $pagina_actual, 'prestaciones', 'id_prestaciones', $parametro);
		
		// Accede a los resultados y a la salida de paginación
		$resultados = $paginationResult['resultados'];
		$output = $paginationResult['output'];

		// Muestra los resultados y la paginación en tu interfaz de usuario
		foreach ($resultados as $resultado) {
			echo $resultado['especialidad'] . ', ' . $resultado['glosa'] . '<br>';
		}

		echo '<div class="pagination">' . $output . '</div>';*/
		

		//unset($_SESSION['detalle_de_solicitud']);

		$pdocrud = DB::PDOCrud();

		//$pdomodel = $pdocrud->getPDOModelObj();
		//$pdomodel->delete("session_data_detalle_de_solicitud");

		$pdocrud->addPlugin("select2");
		$pdocrud->formFieldValue("estado", "Ingresado");
		$pdocrud->fieldHideLable("estado");
		$pdocrud->fieldDataAttr("estado", array("style"=>"display:none"));
		$pdocrud->addPlugin("bootstrap-inputmask");
		$pdocrud->fieldRenameLable("pasaporte_o_codigo_interno", "Pasaporte o Código Interno");
		$pdocrud->fieldRenameLable("direccion", "Dirección");
		$pdocrud->setLangData("select", "Seleccione sexo");
		$pdocrud->fieldAttributes("direccion", array("placeholder"=>"Buscar Dirección"));
		$pdocrud->fieldCssClass("fecha_y_hora_ingreso", array("fecha_y_hora_ingreso"));
		$pdocrud->fieldCssClass("telefono", array("telefono"));
		$pdocrud->formFieldValue("fecha_y_hora_ingreso", $fecha_registro);
		$pdocrud->fieldAddOnInfo("fecha_y_hora_ingreso", "after", '<div class="input-group-append"><span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span></div>');
		$pdocrud->fieldCssClass("nombres", array("nombres"));
		$pdocrud->fieldCssClass("apellido_paterno", array("apellido_paterno"));
		$pdocrud->fieldCssClass("apellido_materno", array("apellido_materno"));
		$pdocrud->fieldCssClass("edad", array("edad"));
		$pdocrud->fieldCssClass("fecha_nacimiento", array("fecha_nacimiento"));
		$pdocrud->fieldCssClass("direccion", array("direccion"));
		$pdocrud->fieldCssClass("sexo", array("sexo"));
		$pdocrud->fieldCssClass("rut", array("rut"));
		$pdocrud->fieldCssClass("pasaporte_o_codigo_interno", array("pasaporte_o_codigo_interno"));
		$pdocrud->formFields(array("rut", "pasaporte_o_codigo_interno", "nombres", "telefono", "fecha_y_hora_ingreso", "apellido_paterno","apellido_materno", "fecha_nacimiento", "edad", "direccion", "sexo"));
		$pdocrud->fieldGroups("Name",array("rut", "pasaporte_o_codigo_interno", "nombres", "apellido_paterno"));
		$pdocrud->fieldGroups("Name2",array("apellido_materno", "fecha_nacimiento","edad","direccion"));
		$pdocrud->fieldGroups("Name3", array("sexo", "fecha_y_hora_ingreso", "telefono"));
		//$pdocrud->fieldAttributes("edad", array("placeholder"=>"*Verificar Fecha de Nacimiento"));
		$pdocrud->fieldDisplayOrder(array("rut", "pasaporte_o_codigo_interno", "nombres", "apellido_paterno", "apellido_materno","fecha_nacimiento", "edad", "direccion", "sexo", "fecha_y_hora_ingreso", "telefono"));
		$pdocrud->fieldTypes("direccion", "input");
		$pdocrud->fieldTypes("sexo", "select");
		$pdocrud->setSettings("required", false);
		$pdocrud->fieldDataBinding("sexo", "sexo", "id_sexo", "nombre", "db");
		$pdocrud->buttonHide("submitBtn");
		$pdocrud->buttonHide("cancel");
		$pdocrud->formStaticFields("buscar", "html", "
			<div class='row'>
				<div class='col-md-9 mt-4'>
					<a href='javascript:;' class='btn btn-primary buscar mt-3' data-intro='Presione este boton para buscar un paciente o ingrese un paciente rellenando todo el formulario'><i class='fa fa-search'></i> Buscar</a>
					<a href='javascript:;' class='btn btn-danger limpiar d-none mt-3'><i class='fas fa-eraser'></i> Limpiar</a>
				</div>
			</div>               
		");
		$render = $pdocrud->dbTable("datos_paciente")->render("insertform");
		$mask = $pdocrud->loadPluginJsCode("bootstrap-inputmask",".rut", array("mask"=> "'9{1,2}9{3}9{3}-(9|k|K)'", "casing" => "'upper'", "clearIncomplete" => "true", "numericInput"=> "true", "positionCaretOnClick" => "'none'"));
		
		$diagnostico = DB::PDOCrud(true);
		$diagnostico->addPlugin("chosen");
		$diagnostico->fieldTypes("profesional", "select");
		//$diagnostico->fieldTooltip("diagnostico", "Para los no encontrados escriba la palabra Linea");
		//$diagnostico->fieldDataBinding("profesional", "profesional", "id_profesional", array("nombre_profesional","apellido_profesional"), "db", " ");
		$diagnostico->fieldAddOnInfo("diagnostico", "after", 
			'<div class="input-group-append">
				<span class="btn btn-default border eliminar_diagnostico" data-intro="Y al presionar este botón podrá borrar más rápido el contenido ingresado por si se equivoca al ingresarlo." id="basic-addon1">
					<i class="fa fa-trash"></i>
				</span>
			</div>'
		);
		$diagnostico->fieldAddOnInfo("profesional", "after", '<div class="input-group-append"><span class="btn btn-default border agregar_profesional" data-intro="Si desea Agregar mas Profesionales que no existan en el listado, puede hacerlo presionando este botón. " id="basic-addon1"><i class="fa fa-plus"></i></span></div>');
		$diagnostico->formFields(array("especialidad","profesional","diagnostico","sintomas_principales", "diagnostico_libre"));
		$diagnostico->fieldRenameLable("diagnostico", "Diagnóstico CIE-10");
		$diagnostico->fieldRenameLable("sintomas_principales", "Síntomas Principales");
		$diagnostico->fieldRenameLable("diagnostico_libre", "Diagnóstico Libre");
		$diagnostico->fieldCssClass("especialidad", array("especialidad"));
		$diagnostico->fieldCssClass("diagnostico_libre", array("diagnostico_libre"));
		$diagnostico->fieldCssClass("profesional", array("profesional"));
		$diagnostico->fieldCssClass("diagnostico", array("diagnostico"));
		$diagnostico->fieldCssClass("sintomas_principales", array("sintomas_principales"));
		$diagnostico->setSettings("required", false);
		$diagnostico->setSettings("printBtn", false);
		$diagnostico->setSettings("pdfBtn", false);
		$diagnostico->setSettings("csvBtn", false);
		$diagnostico->setSettings("excelBtn", false);
		$diagnostico->buttonHide("submitBtn");
		$diagnostico->buttonHide("cancel");
		$diagnostico->fieldAttributes("profesional", array("placeholder"=>"Nombre del Profesional"));
		$diagnostico->fieldAttributes("diagnostico", array("placeholder"=>"Buscar Diagnóstico", "data-intro"=> "Este campo ofrece autocompletado, lo que significa que a medida que escribe, aparecerá un listado con autosugerencias. Puede buscar por código o nombre del examen. y al presionar sobre le dato se rellenará el campo automáticamente."));
		$diagnostico->fieldAttributes("sintomas_principales", array("placeholder"=>"Síntomas del Paciente"));
		$diagnostico->fieldAttributes("diagnostico_libre", array("style"=>"min-height: 150px"));
		$diagnostico->fieldTypes("especialidad", "select");
		$diagnostico->fieldDataBinding("especialidad", array(
			"Imagenologia" => "Imagenologia",
			"Neurologia" => "Neurologia",
			"Geriatría" => "Geriatría",
			"Broncopulmonar Adulto" => "Broncopulmonar Adulto",
			"Medicina" => "Medicina",
			"Maternidad" => "Maternidad",
			"Cirugía" => "Cirugía",
			"Reumatologia" => "Reumatologia",
			"Diabetología" => "Diabetología",
			"Oftalmología" => "Oftalmología", 
			"Otorrinolaringología" => "Otorrinolaringología",
			"Dermatologia y tegumentos" => "Dermatologia y tegumentos",
			"Cardiología" => "Cardiología",
			"Aparato Respiratorio" => "Aparato Respiratorio",
			"Gastroenterología" => "Gastroenterología",
			"Urologia y nefrología" => "Urologia y nefrología",
			"Ginecología y obstetricia" => "Ginecología y obstetricia",
			"Traumatología" => "Traumatología",
			"Imagenológicos odontológica" => "Imagenológicos odontológica",
			"Servicio Neomatologia" => "Servicio Neomatologia",
			"Servicio Cuidados Intermedio Adulto (UTI)" => "Servicio Cuidados Intermedio Adulto (UTI)",
			"Servicio Obstetrica" => "Servicio Obstetrica",
			"Servicio Hospitalización Domiciliaria" => "Servicio Hospitalización Domiciliaria",
			"Servicio Médico Pediátrico" => "Servicio Médico Pediátrico",
			"Servicio Médico Quirurgico Cirugia" => "Servicio Médico Quirurgico Cirugia",
			"Servicio Médico Quirurgico Medicina" => "Servicio Médico Quirurgico Medicina",
			"Servicio Médico Quirurgico UPC Adulto" => "Servicio Médico Quirurgico UPC Adulto",
			"Servicio Pensionado" => "Servicio Pensionado",
			"Servicio Cirugia Mayor Ambulatoria" => "Servicio Cirugia Mayor Ambulatoria"
		), "", "","array");
		$diagnostico->fieldGroups("Name",array("especialidad","profesional", "diagnostico", "sintomas_principales"));
		$render2 = $diagnostico->dbTable("diagnostico_antecedentes_paciente")->render("insertform");
		$chosen = $diagnostico->loadPluginJsCode("chosen",".especialidad");

		$crud = DB::PDOCrud(true);
		$crud->addPlugin("chosen");
		$crud->fieldCssClass("fecha_solicitud", array("fecha_solicitud"));
		$crud->formFieldValue("fecha_solicitud", $fecha_solicitud);
		$crud->fieldHideLable("fecha_solicitud");
        $crud->fieldDataAttr("fecha_solicitud", array("style"=>"display:none"));
		$crud->fieldHideLable("codigo_fonasa");
		$crud->fieldDataAttr("codigo_fonasa", array("style"=>"display:none"));
		$crud->fieldHideLable("id_datos_paciente");
		$crud->fieldDataAttr("id_datos_paciente", array("style"=>"display:none"));
		$crud->fieldRenameLable("tipo_solicitud", "Tipo Solicitud (*)");
		$crud->fieldRenameLable("tipo_examen", "Tipo Exámen (*)");
		$crud->fieldRenameLable("examen", "Exámen (*)");
		$crud->fieldRenameLable("observacion", "Observación");
		$crud->fieldTypes("examen", "input");
		$crud->fieldTypes("adjuntar", "FILE_NEW");
		//$crud->fieldTypes("id_datos_paciente", "select");
		//$crud->fieldDataBinding("id_datos_paciente", "datos_paciente", "id_datos_paciente", array("nombres","apellido_paterno", "apellido_materno"), "db", " ");
		$crud->formFields(array("id_datos_paciente","tipo_solicitud", "codigo_fonasa", "tipo_examen","examen","sintomas_principales", "diagnostico_libre", "plano", "extremidad", "procedencia", "observacion", "contraste", "adjuntar", "fecha_solicitud"));
		$crud->fieldDisplayOrder(array("codigo_fonasa","id_datos_paciente","tipo_solicitud", "codigo_fonasa", "tipo_examen","examen","sintomas_principales", "diagnostico_libre", "plano", "extremidad", "procedencia", "observacion", "contraste"));
		$crud->fieldAddOnInfo("examen", "after", '<div class="input-group-append eliminar_examen"><span class="btn btn-default border eliminar_examen" id="basic-addon1"><i class="fa fa-remove"></i></span></div>');
		$crud->fieldCssClass("id_datos_paciente", array("paciente"));
		$crud->fieldCssClass("tipo_examen", array("tipo_examen"));
		$crud->fieldCssClass("codigo_fonasa", array("codigo_fonasa"));
		$crud->fieldCssClass("plano", array("plano"));
		$crud->fieldCssClass("extremidad", array("extremidad"));
		$crud->fieldCssClass("procedencia", array("procedencia"));
		$crud->fieldCssClass("tipo_solicitud", array("tipo_solicitud"));
		$crud->fieldCssClass("examen", array("examen"));
		$crud->fieldCssClass("observacion", array("observacion"));
		$crud->fieldCssClass("contraste", array("contraste"));
		$crud->fieldCssClass("adjuntar", array("adjuntar"));
		$crud->fieldAttributes("examen", array("placeholder"=>"Buscar Prestación"));
		$crud->fieldAttributes("observacion", array("placeholder"=>"Observación"));
		$crud->fieldAttributes("observacion", array("style"=>"min-height: 150px"));
		$crud->setSettings("encryption", false);
		$crud->setSettings("required", false);
		//$crud->addCallback("before_insert", "insertar_detalle_solicitud");
		$crud->fieldTypes("tipo_solicitud", "select");
		$crud->fieldDataBinding("tipo_solicitud", array(
			"Imageneologica" => "Imageneologica",
			"Procedimientos" => "Procedimientos"
		), "", "","array");
		$crud->fieldTypes("plano", "select");
		$crud->fieldDataBinding("plano", array(
			"Izquierda" => "Izquierda",
			"Derecha" => "Derecha",
			"Superior" => "Superior",
			"Inferior" => "Inferior",
			"Medio" => "Medio"
		), "", "","array");
		$crud->fieldTypes("tipo_examen", "select");
		/*$crud->fieldDataBinding("tipo_examen", array(
			"Radiografia" => "Radiografia",
			"Scanner" => "Scanner",
			"Ecografia" => "Ecografia",
			"Resonancia magnética" => "Resonancia magnética",
			"Procedimientos diagnisticos neurología" => "Procedimientos diagnisticos neurología",
			"Procedimientos de oftalmología" => "Procedimientos de oftalmología",
			"Procedimientos de Otorrinolaringología" => "Procedimientos de Otorrinolaringología",
			"Procedimientos dermatología y tegumentos" => "Procedimientos dermatología y tegumentos",
			"Proc. Diagnostico y terapeutico cardiología" => "Proc. Diagnostico y terapeutico cardiología",
			"Procedimientos diagnosticos y terapeuticos del aparato respiratorio" => "Procedimientos diagnosticos y terapeuticos del aparato respiratorio",
			"Procedimientos gastroenterologia" => "Procedimientos gastroenterologia",
			"Procedimientos de urología y nefrología" => "Procedimientos de urología y nefrología",
			"Procedimientos ginecologia y obstetricia" => "Procedimientos ginecologia y obstetricia",
			"Procedimientos de traumatología" => "Procedimientos de traumatología",
			"Exámenes imagenológicos odontológica" => "Exámenes imagenológicos odontológica"
		), "", "","array");*/
		$crud->fieldTypes("procedencia", "select");
		$crud->fieldDataBinding("procedencia", array("Hospitalizado" => "Hospitalizado", "Urgencia" => "Urgencia", "Ambulatorio" => "Ambulatorio"), "", "","array");
		$crud->formFieldValue("procedencia", "Ambulatorio");
		$crud->fieldGroups("Name",array("tipo_solicitud","tipo_examen", "examen"));
		$crud->fieldGroups("Name2",array("plano","extremidad", "procedencia"));
		$crud->fieldGroups("Name3",array("observacion","contraste", "adjuntar"));
		$crud->fieldTypes("contraste", "checkbox");
		$crud->fieldDataBinding("contraste", array(
			"Examen con contraste" => "Examen con contraste",
			"Concentimiento informado completo" => "Concentimiento informado completo", 
			"Premedicación" => "Premedicación", 
			"Clearence de creatinina" => "Clearence de creatinina",
			"Protección renal" => "Protección renal"
		), "", "","array");
		$crud->fieldTypes("extremidad", "select");
		$crud->fieldDataBinding("extremidad", array(
			"Dedo" => "Dedo",
			"Mano" => "Mano",
			"Brazo" => "Brazo",
			"Codo" => "Codo",
			"Muñeca" => "Muñeca",
			"Antebrazo" => "Antebrazo",
			"Hombro" => "Hombro",
			"Pie" => "Pie",
			"Tobillo" => "Tobillo",
			"Rodilla" => "Rodilla",
			"Muslo" => "Muslo",
			"Sacro iliaca" => "Sacro iliaca",
			"Cadera" => "Cadera",
			"Pierna" => "Pierna",
			"Acromio clavicular" => "Acromio clavicular",
			"Estemoclavicular" => "Estemoclavicular",
			"Cubito" => "Cubito",
			"Radio" => "Radio"
		), "", "","array");
		$crud->buttonHide("submitBtn");
		$crud->buttonHide("cancel");
		$crud->formStaticFields("personalinfo", "html", "
			<div class='row filed_creatinina d-none'>
				<div class='col-md-6'>
					<label>Creatinina</label>
					<input type='text' class='form-control creatinina' name='creatinina'>
				</div>
			</div>
		");
		$render4 = $crud->dbTable("detalle_de_solicitud")->render("insertform");
		$chosen3 = $crud->loadPluginJsCode("chosen",".tipo_examen, .plano, .extremidad");
		
		$detalle_solicitud = DB::PDOCrud(true);
		$detalle_solicitud->addCallback("format_table_data", "formatTableDetalleSolicitud");
		$detalle_solicitud->tableHeading("Detalle de Solicitud");
		$detalle_solicitud->addPlugin("chosen");
		$detalle_solicitud->formDisplayInPopup();
		//$detalle_solicitud->where("id_datos_paciente", "null");
		$detalle_solicitud->where("usuario_sesion", $_SESSION['usuario'][0]["usuario"]);
		$detalle_solicitud->enqueueBtnTopActions("Report",  "<i class='fas fa-plus-circle'></i> Agregar Detalle de Solicitud", "javascript:;", array(), "btn-report btn btn-primary agregar_detalle_solicitud");
		$detalle_solicitud->crudTableCol(array("codigo_fonasa","tipo_solicitud","tipo_examen","examen", "contraste", "adjuntar", "plano","extremidad", "procedencia"));
		$detalle_solicitud->setLangData("add", "");
		$detalle_solicitud->setLangData("actions", "Eliminar");
		$detalle_solicitud->setLangData("save_and_back", "Guardar");
		$detalle_solicitud->setLangData("back", "Salir");
		$detalle_solicitud->setLangData("no_data", "No se han ingresado Datos");
		$detalle_solicitud->fieldGroups("Name",array("tipo_solicitud","tipo_examen", "examen"));
		$detalle_solicitud->fieldGroups("Name2",array("plano","extremidad"));
		$detalle_solicitud->fieldGroups("Name3",array("observacion","contraste"));
		//$detalle_solicitud->setSettings("searchbox", false);
		$detalle_solicitud->setSettings("sortable", false);
		$detalle_solicitud->setSettings("recordsPerPageDropdown", false);
		$detalle_solicitud->buttonHide("submitBtn");
		//$detalle_solicitud->setSearchCols(array("id_datos_paciente", "tipo_solicitud", "codigo_fonasa", "tipo_examen", "observacion", "contraste", "plano", "extremidad"));
		$detalle_solicitud->fieldAttributes("observacion", array("placeholder"=>"Observación"));
		$detalle_solicitud->setSettings("deleteMultipleBtn", false);
		$detalle_solicitud->setSettings("checkboxCol", false);
		$detalle_solicitud->setSettings("addbtn", false);
		$detalle_solicitud->setSettings("refresh", false);
		$detalle_solicitud->setSettings("showAllSearch", false);
		$detalle_solicitud->setSettings("printBtn", false);
		$detalle_solicitud->setSettings("pdfBtn", false);
		$detalle_solicitud->setSettings("csvBtn", false);
		$detalle_solicitud->setSettings("excelBtn", false);
		$detalle_solicitud->setSettings("editbtn", false);
		$detalle_solicitud->setSettings("viewbtn", false);
		//$detalle_solicitud->relatedData('id_datos_paciente','datos_paciente','id_datos_paciente', "CONCAT(nombres, ' ' ,apellido_paterno, ' ', apellido_materno)");
		$detalle_solicitud->colRename("codigo_fonasa", "Código");
		$detalle_solicitud->colRename("examen", "Exámen");
		$detalle_solicitud->colRename("tipo_solicitud", "Tipo");
		$detalle_solicitud->colRename("tipo_examen", "Nombre del Exámen");
		$detalle_solicitud->colRename("observacion", "Observación");
		$detalle_solicitud->addCallback("before_insert", "insertar_detalle_solicitud");
		$detalle_solicitud->addCallback("before_delete", "eliminar_detalle_solicitud");
		$detalle_solicitud->fieldTypes("contraste", "checkbox");
		$detalle_solicitud->fieldDataBinding("contraste", array(
			"Examen con contraste" => "Examen con contraste",
			"Concentimiento informado completo" => "Concentimiento informado completo", 
			"Premedicación" => "Premedicación", 
			"Clearence de creatinina" => "Clearence de creatinina", 
			"Protección renal" => "Protección renal"
		), "", "","array");

		$detalle_solicitud->fieldTypes("plano", "select");
		$detalle_solicitud->fieldDataBinding("plano", array(
			"Izquierda" => "Izquierda",
			"Derecha" => "Derecha",
			"Superior" => "Superior",
			"Inferior" => "Inferior",
			"Medio" => "Medio"
		), "", "","array");

		$detalle_solicitud->fieldTypes("tipo_examen", "select");
		$detalle_solicitud->fieldDataBinding("tipo_examen", array(
			"Radiografia" => "Radiografia",
			"Scanner" => "Scanner",
			"Ecografia" => "Ecografia",
			"Resonancia magnética" => "Resonancia magnética",
			"Procedimientos diagnisticos neurología" => "Procedimientos diagnisticos neurología",
			"Procedimientos de oftalmología" => "Procedimientos de oftalmología",
			"Procedimientos de Otorrinolaringología" => "Procedimientos de Otorrinolaringología",
			"Procedimientos dermatología y tegumentos" => "Procedimientos dermatología y tegumentos",
			"Proc. Diagnostico y terapeutico cardiología" => "Proc. Diagnostico y terapeutico cardiología",
			"Procedimientos diagnosticos y terapeuticos del aparato respiratorio" => "Procedimientos diagnosticos y terapeuticos del aparato respiratorio",
			"Procedimientos gastroenterologia" => "Procedimientos gastroenterologia",
			"Procedimientos de urología y nefrología" => "Procedimientos de urología y nefrología",
			"Procedimientos ginecologia y obstetricia" => "Procedimientos ginecologia y obstetricia",
			"Procedimientos de traumatología" => "Procedimientos de traumatología",
			"Exámenes imagenológicos odontológica" => "Exámenes imagenológicos odontológica"
		), "", "","array");

		$detalle_solicitud->fieldTypes("extremidad", "select");
		$detalle_solicitud->fieldDataBinding("extremidad", array(
			"Dedo" => "Dedo",
			"Mano" => "Mano",
			"Brazo" => "Brazo",
			"Codo" => "Codo",
			"Muñeca" => "Muñeca",
			"Antebrazo" => "Antebrazo",
			"Hombro" => "Hombro",
			"Pie" => "Pie",
			"Tobillo" => "Tobillo",
			"Rodilla" => "Rodilla",
			"Muslo" => "Muslo",
			"Sacro iliaca" => "Sacro iliaca",
			"Cadera" => "Cadera",
			"Pierna" => "Pierna",
			"Acromio clavicular" => "Acromio clavicular",
			"Estemoclavicular" => "Estemoclavicular",
			"Cubito" => "Cubito",
			"Radio" => "Radio"
		), "", "","array");

		$detalle_solicitud->fieldTypes("tipo_solicitud", "select");
		$detalle_solicitud->fieldDataBinding("tipo_solicitud", array(
			"Imageneologica" => "Imageneologica",
			"Procedimientos" => "Procedimientos"
		), "", "","array");

		$detalle_solicitud->fieldCssClass("tipo_examen", array("tipo_examen"));
		$detalle_solicitud->fieldCssClass("plano", array("plano"));
		$detalle_solicitud->fieldCssClass("extremidad", array("extremidad"));
		$detalle_solicitud->fieldAttributes("observacion", array("style"=>"min-height: 150px"));
		$render3 = $detalle_solicitud->dbTable("session_data_detalle_de_solicitud")->render();
		$chosen2 = $detalle_solicitud->loadPluginJsCode("chosen",".tipo_examen, .plano, .extremidad");

		View::render(
			'datos_paciente',
			[
				'render' => $render, 
				'mask' => $mask, 
				'render2' => $render2, 
				'chosen'=> $chosen,
				'render3'=> $render3,
				'chosen2'=> $chosen2,
				'render4' => $render4,
				'chosen3' => $chosen3
			]
		);
	}

	public function cargar_datos_tipo_examen(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$tipo_solicitud = $request->post("tipo_solicitud");

			if(!empty($tipo_solicitud) && $tipo_solicitud == "Imageneologica"){
				$tipo_examen = [
					"Radiografía" => "Radiografía",
					"Scanner" => "Scanner",
					"Ecografía" => "Ecografía",
					"ECOTOMOGRAFIA" => "ECOTOMOGRAFIA",
					"RESONANCIA MAGNETICA (incluye medio de contraste)" => "RESONANCIA MAGNETICA (incluye medio de contraste)",
					"Resonancia magnética" => "Resonancia magnética",
					"TOMOGRAFIA AXIAL COMPUTARIZADA (TAC) (CON O SIN MEDIO DE CONTRASTE)" => "TOMOGRAFIA AXIAL COMPUTARIZADA (TAC) (CON O SIN MEDIO DE CONTRASTE)"
				];
			} else {
				$tipo_examen = [
					"Procedimientos diagnósticos neurología" => "Procedimientos diagnósticos neurología",
					"Procedimientos de oftalmología" => "Procedimientos de oftalmología",
					"Procedimientos de Otorrinolaringología" => "Procedimientos de Otorrinolaringología",
					"Procedimientos dermatología y tegumentos" => "Procedimientos dermatología y tegumentos",
					"Proc. Diagnóstico y terapéutico cardiología" => "Proc. Diagnóstico y terapéutico cardiología",
					"Procedimientos diagnósticos y terapéuticos del aparato respiratorio" => "Procedimientos diagnósticos y terapéuticos del aparato respiratorio",
					"Procedimientos gastroenterología" => "Procedimientos gastroenterología",
					"Procedimientos de urología y nefrología" => "Procedimientos de urología y nefrología",
					"Procedimientos ginecología y obstetricia" => "Procedimientos ginecología y obstetricia",
					"Procedimientos de traumatología" => "Procedimientos de traumatología",
					"Exámenes imagenológicos odontológicos" => "Exámenes imagenológicos odontológicos"
				];
			}
			echo json_encode(['tipo_examen' => $tipo_examen]);
		}
	}

	public function mostrar_grilla_lista_espera(){
		$crud = DB::PDOCrud(true);
		$pdomodel = $crud->getPDOModelObj();

		$currentDate = date('Y-m-d');
    
		// Primer día del mes actual
		$firstDayOfMonth = date('Y-m-01');
		
		// Último día del mes actual
		$lastDayOfMonth = date('Y-m-t');

		$data = $pdomodel->DBQuery(
			"SELECT 
			dp.id_datos_paciente,
			ds.id_detalle_de_solicitud,
			dp.rut,
			CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS paciente,
			dp.telefono,
			dp.apellido_paterno,
			dp.apellido_materno,
			dp.edad,
			ds.fecha_egreso,
			fecha_solicitud as fecha_solicitud,
			ds.estado AS estado,
			codigo_fonasa AS codigo,
			examen,
			ds.fecha as fecha,
			especialidad,
			CONCAT(nombre_profesional, ' ', apellido_profesional) AS profesional,
			CASE WHEN ds.adjuntar IS NOT NULL AND ds.adjuntar != '' THEN 'Si' ELSE 'No' END AS tiene_adjunto
		FROM 
			datos_paciente AS dp
		INNER JOIN
			detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
		INNER JOIN 
			diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
		INNER JOIN 
			profesional AS pro ON pro.id_profesional = dg_p.profesional
		WHERE
			dg_p.fecha_solicitud_paciente = ds.fecha_solicitud
			AND ds.fecha_solicitud >= '$firstDayOfMonth'
            AND ds.fecha_solicitud <= '$lastDayOfMonth'
		GROUP BY 
			dp.id_datos_paciente, dp.rut, dp.edad, ds.fecha, ds.fecha_solicitud, ds.examen"
		);

		//echo $pdomodel->getLastQuery();
		//die();

		echo json_encode(['data' => $data]);
	}

	public function lista_espera_examenes(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->addPlugin("bootstrap-inputmask");
		$pdocrud->formFields(array(""));
		//$pdocrud->formFields(array("estado","rut","fecha_solicitud", "procedencia", "examen", "nombres", "nombre_profesional", "fecha_solicitud"));
		$pdocrud->setSettings("required", false);
		$pdocrud->joinTable("detalle_de_solicitud", "detalle_de_solicitud.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
		$pdocrud->joinTable("diagnostico_antecedentes_paciente", "diagnostico_antecedentes_paciente.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
		$pdocrud->joinTable("profesional", "profesional.id_profesional = diagnostico_antecedentes_paciente.profesional", "INNER JOIN");
		$pdocrud->fieldAddOnInfo("fecha_solicitud", "after", '<div class="input-group-append"><span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span></div>');
		$pdocrud->fieldCssClass("nombres", array("nombre_paciente"));
		$pdocrud->fieldCssClass("fecha_solicitud", array("fecha_solicitud"));
		$pdocrud->fieldCssClass("rut", array("rut"));
		$pdocrud->fieldCssClass("estado", array("estado"));
		$pdocrud->fieldCssClass("procedencia", array("procedencia"));
		$pdocrud->fieldCssClass("examen", array("prestacion"));
		$pdocrud->fieldCssClass("nombre_profesional", array("profesional"));
		$pdocrud->fieldAttributes("nombre_profesional", array("autocomplete"=>"off"));
		
		$pdocrud->formStaticFields("filtros_busqueda", "html", "
				<div class='row d-flex'>
					<div class='col-xl col-lg-6 col-md-6 flex-grow-1'>
						<label class='control-label col-form-label'>RUN</label>
						<input type='text' class='form-control pdocrud-form-control pdocrud-text rut'>
					</div>
					<div class='col-xl col-lg-6 col-md-6 flex-grow-1'>
						<label class='control-label col-form-label'>Pasaporte o Código Interno</label>
						<input type='text' class='form-control pdocrud-form-control pdocrud-text pasaporte'>
					</div>
					<div class='col-xl col-lg-6 col-md-6 flex-grow-1'>
						<label class='control-label col-form-label'>Nombre Paciente</label>
						<input type='text' class='form-control pdocrud-form-control pdocrud-text nombre_paciente'>
					</div>
					<div class='col-xl col-lg-6 col-md-6 flex-grow-1'>
						<label class='control-label col-form-label'>Prestación</label>
						<input type='text' class='form-control pdocrud-form-control pdocrud-text prestacion'>
					</div>
				</div>
				<div class='row d-flex'>
					<div class='col-xl col-lg-6 col-md-6 flex-grow-1'>
						<label class='control-label col-form-label'>Estado</label>
						<select class='form-control pdocrud-form-control pdocrud-select estado'>
							<option value=''>Seleccionar</option>
							<option value='Ingresado'>Ingresado</option>
							<option value='Agendado'>Agendado</option>
							<option value='Egresado'>Egresado</option>
						</select>
					</div>
					<div class='col-xl col-lg-6 col-md-6 flex-grow-1'>
						<label class='control-label col-form-label'>Procedencia</label>
						<select class='form-control pdocrud-form-control pdocrud-select procedencia'>
							<option value=''>Seleccionar</option>
							<option value='Hospitalizado'>Hospitalizado</option>
							<option value='Urgencia'>Urgencia</option>
							<option value='Ambulatorio'>Ambulatorio</option>
							<option value=''>Sin Procedencia</option>
						</select>
					</div>
					<div class='col-xl col-lg-6 col-md-6 flex-grow-1'>
						<label class='control-label col-form-label'>Fecha Solicitud</label>
						<div class='input-group'>
							<input type='text' class='form-control pdocrud-form-control pdocrud-text fecha_solicitud pdocrud-date flatpickr-input' data-type='date'>                
							<div class='input-group-append'>
								<span class='input-group-text' id='basic-addon1'>
									<i class='fa fa-calendar'></i>
								</span>
							</div> 
						</div>
					</div>

					<div class='col-xl col-lg-6 col-md-6 flex-grow-1'>
						<label class='control-label col-form-label'>Tiene Adjunto</label>
						<div class='input-group'>
							<select class='form-control pdocrud-form-control pdocrud-select adjuntar'>
								<option value=''>Seleccionar</option>
								<option value='Si'>Si</option>
								<option value='No'>No</option>
							</select>
						</div>
					</div>
				</div>
		");

		$pdocrud->formStaticFields("botones_busqueda", "html", "
				<div class='row'>
					<div class='col-md-12 text-center'>
						<a href='javascript:;' class='btn btn-primary buscar'><i class='fa fa-search'></i> Buscar</a>
						<a href='javascript:;' class='btn btn-danger limpiar_filtro'><i class='fas fa-eraser'></i> Limpiar</a>
					</div>
				</div>
		");
		$pdocrud->fieldRenameLable("rut", "RUN");
		$pdocrud->fieldRenameLable("fecha_solicitud", "Fecha Solicitud");
		$pdocrud->fieldRenameLable("nombres", "Nombre Paciente");
		$pdocrud->fieldRenameLable("procedencia", "Procedencia");
		$pdocrud->fieldRenameLable("examen", "Prestación");
		$pdocrud->fieldTypes("examen", "input");
		$pdocrud->fieldTypes("procedencia", "select");
		$pdocrud->fieldDataBinding("procedencia", array("Hospitalizado" => "Hospitalizado", "Urgencia" => "Urgencia", "Ambulatorio" => "Ambulatorio", "" => "Sin Procedencia"), "", "","array");
		$pdocrud->fieldRenameLable("nombre_profesional", "Profesional");
		$pdocrud->fieldTypes("estado", "select");
		$pdocrud->fieldDataBinding("estado", "estado_procedimiento", "nombre as estado_procedimiento", "nombre", "db");
		$pdocrud->fieldGroups("Name",array("rut","nombres", "estado"));
		$pdocrud->fieldGroups("Name2",array("procedencia", "fecha_solicitud"));
		$pdocrud->fieldDisplayOrder(array("rut","nombres","estado", "procedencia", "fecha_solicitud"));
		//$pdocrud->fieldDisplayOrder(array("rut","nombres","estado", "procedencia", "examen", "nombre_profesional", "fecha_solicitud"));
		$pdocrud->buttonHide("submitBtn");
		$pdocrud->buttonHide("cancel");
		$render = $pdocrud->dbTable("datos_paciente")->render("insertform");
		$mask = $pdocrud->loadPluginJsCode("bootstrap-inputmask",".rut", array("mask"=> "'9{1,2}9{3}9{3}-(9|k|K)'", "casing" => "'upper'", "clearIncomplete" => "true", "numericInput"=> "true", "positionCaretOnClick" => "'none'"));

		//$render_crud = $this->mostrar_grilla_lista_espera();
		
		View::render(
			'lista_espera_examenes',
			[
				'render' => $render,
				'mask' => $mask
				//'render_crud' => $render_crud
			]
		);
	}

	public function agregar_profesional(){
		if($_SERVER["REQUEST_METHOD"] === 'POST'){
			$pdocrud = DB::PDOCrud(true);
			$pdocrud->addPlugin("bootstrap-inputmask");
			$pdocrud->fieldCssClass("rut_profesional", array("rut_profesional"));
			$pdocrud->addCallback("before_insert", "agregar_profesional");
			$pdocrud->fieldGroups("Name",array("nombre_profesional","apellido_profesional"));
			$render = $pdocrud->dbTable("profesional")->render("insertform");
			HomeController::modal("Profesional", "<i class='fa fa-plus'></i> Agregar Profesional", $render);
		}
	}

	public static function modal($id, $titulo, $contenido = ""){
		$modal = '<div class="modal fade" id="'.$id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">'.$titulo.'</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						'.$contenido.'
					</div>
				</div>
			</div>
		</div>';
		echo $modal;
	}

	public function cargar_modal_procedimientos(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {

			$id = $request->post('id');
			$fecha_solicitud = $request->post('fecha_solicitud');
			$id_detalle_de_solicitud = $request->post('id_detalle_de_solicitud');

			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();

			$pdomodel->where("id_datos_paciente", $id, "=", "AND");
			$pdomodel->where("id_detalle_de_solicitud", $id_detalle_de_solicitud);
			$detalle_de_solicitud = $pdomodel->select("detalle_de_solicitud");

			$pdomodel->where("id_datos_paciente", $id, "=", "AND");
			$pdomodel->where("fecha_solicitud_paciente", $fecha_solicitud);
			$diagnostico_antecedentes_paciente = $pdomodel->select("diagnostico_antecedentes_paciente");

			$pdocrud->fieldAddOnInfo("fecha", "after", '<div class="input-group-append"><span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span></div>');
			$pdocrud->fieldCssClass("fecha", array("fecha"));
			$pdocrud->fieldCssClass("estado", array("estado_procedimiento"));
			$pdocrud->fieldRenameLable("estado", "Cambiar Estado");
			$pdocrud->fieldRenameLable("fecha", "Fecha Agendada");
			$pdocrud->fieldTypes("estado", "select");
			$pdocrud->fieldDataBinding(
				"estado",                                 
				"estado_procedimiento",                    
				"nombre",
				array("nombre"),
				"db",
				"-",
				array(array("nombre", "Egresado", "!="), array())
			);

			$pdocrud->joinTable("detalle_de_solicitud", "detalle_de_solicitud.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
			$pdocrud->joinTable("diagnostico_antecedentes_paciente", "diagnostico_antecedentes_paciente.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");

			$pdocrud->fieldRenameLable("diagnostico", "Diagnóstico CIE-10");
			$pdocrud->fieldDisplayOrder(array("id_datos_paciente", "estado", "fecha","diagnostico", "fundamento"));
			$pdocrud->setSettings("hideAutoIncrement", false);
			$pdocrud->setSettings("encryption", false);
			//$pdocrud->setSettings("required", false);
			$pdocrud->fieldHideLable("fecha_solicitud");
			$pdocrud->fieldDataAttr("fecha_solicitud", array("style"=>"display:none"));

			$pdocrud->fieldHideLable("id_datos_paciente");
			$pdocrud->fieldDataAttr("id_datos_paciente", array("style"=>"display:none"));

			$pdocrud->formFieldValue("id_detalle_de_solicitud", $detalle_de_solicitud[0]["id_detalle_de_solicitud"]);
			
			$pdocrud->fieldHideLable("id_detalle_de_solicitud");
			$pdocrud->fieldDataAttr("id_detalle_de_solicitud", array("style"=>"display:none"));

			$pdocrud->fieldDataAttr("id_datos_paciente", array("value"=> $detalle_de_solicitud[0]["id_datos_paciente"]));
			$pdocrud->formFieldValue("estado", $detalle_de_solicitud[0]["estado"]);
			$pdocrud->formFieldValue("fecha", $detalle_de_solicitud[0]["fecha"]);
			$pdocrud->fieldAttributes("fecha", array("autocomplete"=>"off"));
			$pdocrud->fieldAttributes("diagnostico", array("autocomplete"=>"off"));
			$pdocrud->fieldAttributes("fundamento", array("autocomplete"=>"off"));
			$pdocrud->fieldNotMandatory("diagnostico");

			$pdocrud->fieldDataAttr("fecha_solicitud", array("value"=> $detalle_de_solicitud[0]["fecha_solicitud"]));

			$diagnostico = isset($diagnostico_antecedentes_paciente[0]["diagnostico"]) ? $diagnostico_antecedentes_paciente[0]["diagnostico"] : '';
			$fundamento = isset($detalle_de_solicitud[0]["fundamento"]) ? $detalle_de_solicitud[0]["fundamento"] : '';
			$pdocrud->fieldDataAttr("diagnostico", array("value"=> $diagnostico));
			$pdocrud->formFieldValue("fundamento", $fundamento);
			$pdocrud->fieldNotMandatory("fundamento");

			$pdocrud->buttonHide("submitBtn");
			$pdocrud->buttonHide("cancel");

			$pdocrud->formStaticFields("buttons", "html", "
				<div class='row justify-content-center'>
					<input type='submit' class='btn btn-info pdocrud-form-control pdocrud-submit' id='pdocrud_submit_jkFdX8o5Z9' data-action='selectform' value='Guardar'>
					<button type='button' class='btn btn-info' data-dismiss='modal'> Cerrar</button>
				</div>
				
			");
			$pdocrud->fieldDisplayOrder(array("fecha", "estado", "id_datos_paciente", "id_detalle_de_solicitud", "fecha_solicitud", "diagnostico", "fundamento", "buttons")); 

			$pdocrud->formFields(array("fecha", "id_datos_paciente", "id_detalle_de_solicitud", "fecha_solicitud", "diagnostico", "fundamento", "estado"));
			$pdocrud->setLangData("login", "Guardar");
			$pdocrud->addCallback("before_select", "editar_procedimientos");
			$render = $pdocrud->dbTable("datos_paciente")->render("selectform");
			HomeController::modal("procedimientos", "<i class='fa fa-folder'></i> Modificar Procedimientos", $render);
		}
	}

	public function cargar_modal_modificar(){
		$request = new Request();

		if ($request->getMethod() === 'POST') {
			$pdocrud = DB::PDOCrud(true);
			$pdocrud->addPlugin("chosen");

			$id = $request->post('id');

			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->columns = array("datos_paciente.id_datos_paciente", "fecha_solicitud", "tipo_solicitud", "tipo_examen", "examen", "observacion");
			$pdomodel->joinTables("detalle_de_solicitud", "detalle_de_solicitud.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");

			$pdomodel->where("datos_paciente.id_datos_paciente", $id, "=", "AND");
			$id_datos_paciente = $pdomodel->select("datos_paciente");

			$pdomodel->where("id_datos_paciente", $id, "=", "AND");
			$diagnostico_antecedentes_paciente = $pdomodel->select("diagnostico_antecedentes_paciente");

			$paciente = new DatosPacienteModel();
			$data = $paciente->PacientePorId($id);
		
			$pdocrud->formFieldValue("id_datos_paciente", $id_datos_paciente[0]["id_datos_paciente"]);
			$pdocrud->formFieldValue("fecha_solicitud", $id_datos_paciente[0]["fecha_solicitud"]);
			$pdocrud->formFieldValue("tipo_solicitud", $id_datos_paciente[0]["tipo_solicitud"]);
			$pdocrud->formFieldValue("tipo_examen", $id_datos_paciente[0]["tipo_examen"]);
			$pdocrud->formFieldValue("examen", $id_datos_paciente[0]["examen"]);

			$pdocrud->formFieldValue("especialidad", $diagnostico_antecedentes_paciente[0]["especialidad"]);

			$pdocrud->fieldAddOnInfo("examen", "after", '<div class="input-group-append eliminar_examen"><span class="btn btn-default border eliminar_examen" id="basic-addon1"><i class="fa fa-remove"></i></span></div>');
			$pdocrud->joinTable("detalle_de_solicitud", "detalle_de_solicitud.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
			$pdocrud->joinTable("diagnostico_antecedentes_paciente", "diagnostico_antecedentes_paciente.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
			$pdocrud->setPK("id_datos_paciente");
			$pdocrud->fieldHideLable("id_datos_paciente");
			$pdocrud->fieldDataAttr("id_datos_paciente", array("style"=>"display:none"));

			$pdocrud->addCallback("before_select", "editar_lista_examenes_modificar");
			$pdocrud->setSettings("hideAutoIncrement", false);
			$pdocrud->buttonHide("submitBtn");
			$pdocrud->buttonHide("cancel");

			$pdocrud->formStaticFields("buttons", "html", "
				<div class='row justify-content-center'>
					<input type='submit' class='btn btn-info pdocrud-form-control pdocrud-submit' id='pdocrud_submit_jkFdX8o5Z9' data-action='selectform' value='Guardar'>
					<button type='button' class='btn btn-info' data-dismiss='modal'> Cerrar</button>
				</div>
				
			");
			$pdocrud->fieldDisplayOrder(array("fecha_solicitud", "especialidad", "tipo_solicitud", "tipo_examen", "examen", "buttons"));   
			
			$pdocrud->fieldCssClass("especialidad", array("especialidad"));

			$pdocrud->fieldTypes("especialidad", "select");
			$pdocrud->fieldDataBinding("especialidad", array(
				"Imagenologia" => "Imagenologia",
				"Neurologia" => "Neurologia",
				"Geriatría" => "Geriatría",
				"PEDIATRIA HME" => "PEDIATRIA HME",
				"GINECOLOGIA HME" => "GINECOLOGIA HME",
				"GERIATRIA HME" => "GERIATRIA HME",
				"Reumatologia Hme" => "Reumatologia Hme",
				"TRAUMATOLOGIA Y ORTOPEDIA ADULTOS HME" => "TRAUMATOLOGIA Y ORTOPEDIA ADULTOS HME",
				"OTORRINOLARINGOLOGIA HME" => "OTORRINOLARINGOLOGIA HME",
				"MEDICINA GENERAL PRAIS HME" => "MEDICINA GENERAL PRAIS HME",
				"BRONCOPULMONAR ADULTO" => "Broncopulmonar Adulto",
				"PSIQUIATRIA PRAIS HME" => "PSIQUIATRIA PRAIS HME",
				"PSIQUIATRIA ADULTOS HME" => "PSIQUIATRIA ADULTOS HME",
				"NEFROLOGIA HME" => "NEFROLOGIA HME",
				"SIN ASIGNAR" => "SIN ASIGNAR",
				"DIABETOLOGIA HME" => "DIABETOLOGIA HME",
				"CIRUGIA COLOPROCTOLOGICA HME" => "Cirugia Coloproctologica",
				"CIRUGIA Y TRAUMATOLOGIA MAXILO FACIAL HME" => "CIRUGIA Y TRAUMATOLOGIA MAXILO FACIAL HME",
				"URGENCIA" => "Urgencia",
				"Medicina" => "Medicina",
				"POLI TACO" => "POLI TACO",
				"Maternidad" => "Maternidad",
				"Medicina General PRAIS" => "Medicina General PRAIS",
				"Cirugía" => "Cirugía",
				"Anestesiología" => "Anestesiología",
				"Cuidados Paliativos" => "Cuidados Paliativos",
				"UTI Adultos" => "UTI Adultos",
				"Psiquiatría PRAIS" => "Psiquiatría PRAIS",
				"CAE Ginecología y Obstetrícia" => "CAE Ginecología y Obstetrícia",
				"UROLOGIA HME" => "Urología",
				"Salud Mental" => "Salud Mental",
				"NEUROLOGIA ADULTO HME" => "Neurología Adulto",
				"Reumatologia" => "Reumatologia",
				"Diabetología" => "Diabetología",
				"Oftalmología" => "Oftalmología",
				"Otorrinolaringología" => "Otorrinolaringología",
				"Dermatologia y tegumentos" => "Dermatologia y tegumentos",
				"Cardiología" => "Cardiología",
				"Aparato Respiratorio" => "Aparato Respiratorio",
				"Gastroenterología" => "Gastroenterología",
				"Urologia y nefrología" => "Urologia y nefrología",
				"Ginecología y obstetricia" => "Ginecología y obstetricia",
				"Traumatología" => "Traumatología",
				"Imagenológicos odontológica" => "Imagenológicos odontológica",
				"Servicio Neomatologia" => "Servicio Neomatologia",
				"Servicio Cuidados Intermedio Adulto (UTI)" => "Servicio Cuidados Intermedio Adulto (UTI)",
				"Servicio Obstetrica" => "Servicio Obstetrica",
				"Servicio Hospitalización Domiciliaria" => "Servicio Hospitalización Domiciliaria",
				"Servicio Médico Pediátrico" => "Servicio Médico Pediátrico",
				"Servicio Médico Quirurgico Cirugia" => "Servicio Médico Quirurgico Cirugia",
				"Servicio Médico Quirurgico Medicina" => "Servicio Médico Quirurgico Medicina",
				"Servicio Médico Quirurgico UPC Adulto" => "Servicio Médico Quirurgico UPC Adulto",
				"Servicio Pensionado" => "Servicio Pensionado",
				"Pensionado" => "Pensionado",
				"Servicio Cirugia Mayor Ambulatoria" => "Servicio Cirugia Mayor Ambulatoria",
				"CirugíaPediátricaHme" => "Cirugía Pediátrica",
				"CirugíaGeneralHme" => "Cirugía General"
			), "", "","array");

			$pdocrud->fieldTypes("tipo_solicitud", "select");
			$pdocrud->fieldDataBinding("tipo_solicitud", array(
				"Imageneologica" => "Imageneologica",
				"Procedimientos" => "Procedimientos"
			), "", "","array");

			$pdocrud->fieldTypes("tipo_examen", "select");

			$pdocrud->fieldCssClass("tipo_solicitud", array("tipo_solicitud"));
			$pdocrud->fieldCssClass("tipo_examen", array("tipo_examen"));
			$pdocrud->fieldCssClass("examen", array("examen"));

			$pdocrud->setSettings("template", "datos_usuario_busqueda");
			$pdocrud->fieldRenameLable("observacion", "Observación");
			$pdocrud->fieldTypes("examen", "input");
			$pdocrud->formFields(array("id_datos_paciente", "fecha_solicitud", "especialidad", "tipo_solicitud", "tipo_examen", "examen"));
			$pdocrud->setLangData("login", "Guardar"); 

			$render = $pdocrud->dbTable("datos_paciente")->render("selectform");
			
			ob_start();
			HomeController::modal("modificar", "<i class='fa fa-edit'></i> Modificar", $render);
			$modal_html = ob_get_clean();

			// Enviamos el HTML del modal y el array de diagnostico como respuesta JSON
			echo json_encode([
				'html' => $modal_html,
				'id_datos_paciente' => $id_datos_paciente,
				'diagnostico_antecedentes_paciente' => $diagnostico_antecedentes_paciente
			]);
		}
	}


	public function cargar_modal_agregar_nota(){
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$pdocrud = DB::PDOCrud(true);

			$id = $request->post('id');
			$fecha_solicitud = $request->post('fecha_solicitud');

			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->columns = array("datos_paciente.id_datos_paciente", "fecha_solicitud", "observacion");
			$pdomodel->joinTables("detalle_de_solicitud", "detalle_de_solicitud.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");

			$pdomodel->where("datos_paciente.id_datos_paciente", $id, "=", "AND");
			$pdomodel->where("detalle_de_solicitud.fecha_solicitud", $fecha_solicitud);
			$id_datos_paciente = $pdomodel->select("datos_paciente");

			$paciente = new DatosPacienteModel();
			$data = $paciente->PacientePorId($id);
		
			if($data){
				$pdocrud->formStaticFields("info_paciente", "html", "
					<h5>Paciente</h5>
					<p>".ucwords($data[0]["nombres"]). ' ' . ucwords($data[0]["apellido_paterno"]). ' ' . ucwords($data[0]["apellido_materno"])."</p>
				");
			}

			$pdocrud->formFieldValue("id_datos_paciente", $id_datos_paciente[0]["id_datos_paciente"]);
			$pdocrud->formFieldValue("observacion", $id_datos_paciente[0]["observacion"]);
			$pdocrud->formFieldValue("fecha_solicitud", $id_datos_paciente[0]["fecha_solicitud"]);

			$pdocrud->joinTable("detalle_de_solicitud", "detalle_de_solicitud.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
			$pdocrud->setPK("id_datos_paciente");
			$pdocrud->fieldHideLable("id_datos_paciente");
			$pdocrud->fieldDataAttr("id_datos_paciente", array("style"=>"display:none"));

			$pdocrud->fieldHideLable("fecha_solicitud");
			$pdocrud->fieldDataAttr("fecha_solicitud", array("style"=>"display:none"));

			$pdocrud->addCallback("before_select", "editar_lista_examenes_notas");
			$pdocrud->setSettings("hideAutoIncrement", false);
			$pdocrud->buttonHide("submitBtn");
			$pdocrud->buttonHide("cancel");

			$pdocrud->formStaticFields("buttons", "html", "
				<div class='row justify-content-center'>
					<input type='submit' class='btn btn-info pdocrud-form-control pdocrud-submit' id='pdocrud_submit_jkFdX8o5Z9' data-action='selectform' value='Guardar'>
					<button type='button' class='btn btn-info' data-dismiss='modal'> Cerrar</button>
				</div>
				
			");
			$pdocrud->fieldDisplayOrder(array("info_paciente", "observacion", "buttons"));      

			$pdocrud->setSettings("template", "datos_usuario_busqueda");
			$pdocrud->fieldRenameLable("observacion", "Observación");
			$pdocrud->formFields(array("id_datos_paciente", "fecha_solicitud", "observacion"));
			$pdocrud->setLangData("login", "Guardar"); 

			$render = $pdocrud->dbTable("datos_paciente")->render("selectform");
			HomeController::modal("agregar_nota", "<i class='fa fa-file-o'></i> Agregar Nota", $render);
		}
	}

	public function cargar_modal_logs(){
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$id = $request->post('id');
			$fecha_solicitud = $request->post('fecha_solicitud');

			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->columns = array(
				"datos_paciente.id_datos_paciente",
				"id_detalle_de_solicitud",
				"rut",
				"nombres",
				"apellido_paterno",
				"apellido_materno",
				"edad",
				"motivo_egreso",
				"adjuntar",
				"fundamento",
				"fecha_egreso",
				"observacion",
				"GROUP_CONCAT(DISTINCT fecha_solicitud) as fecha_solicitud",
				"GROUP_CONCAT(DISTINCT detalle_de_solicitud.estado) AS estado",
				"GROUP_CONCAT(DISTINCT codigo_fonasa) AS codigo",
				"GROUP_CONCAT(DISTINCT examen SEPARATOR ' - ') AS Examen",
				"GROUP_CONCAT(DISTINCT detalle_de_solicitud.fecha) as fecha", 
				"GROUP_CONCAT(DISTINCT especialidad) AS especialidad",
				"GROUP_CONCAT(DISTINCT nombre_profesional, ' ', apellido_profesional) AS profesional", 
			);
			$pdomodel->joinTables("detalle_de_solicitud", "detalle_de_solicitud.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
			$pdomodel->joinTables("diagnostico_antecedentes_paciente", "diagnostico_antecedentes_paciente.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
			$pdomodel->joinTables("profesional", "profesional.id_profesional = diagnostico_antecedentes_paciente.profesional", "INNER JOIN");
			$pdomodel->where("datos_paciente.id_datos_paciente", $id);
			$pdomodel->where("detalle_de_solicitud.fecha_solicitud", $fecha_solicitud);

			$pdomodel->groupByCols = array("id_datos_paciente", "rut", "edad", "detalle_de_solicitud.fecha", "fecha_solicitud");
			$data = $pdomodel->select("datos_paciente");

			$pdomodel->where("id_causal_salida", $data[0]["motivo_egreso"]);
			$motivo_egreso = $pdomodel->select("causal_salida");

			$nombre = isset($motivo_egreso[0]["nombre"]) ? $motivo_egreso[0]["nombre"] : '';

			if($data[0]["fecha_solicitud"] != "0000-00-00 00:00:00") {
				$obt = date('d/m/Y', strtotime($data[0]["fecha_solicitud"]));
			} else {
				$obt = 'Sin Fecha';
			}

			$codigos = explode(',', $data[0]["codigo"]);

			$code = "";
			foreach ($codigos as $codigo) {
				$code .= '<div class="badge badge-info">'. $codigo . '</div>' . '<br>';
			}

			$exam = str_replace(' - ', "<br>", $data[0]["examen"]);

			$examArray = explode('<br>', $exam);
			foreach ($examArray as $key => $element) {
				$examArray[$key] = ($key + 1) . '. ' . $element;
			}

			// Unir de nuevo el array en una cadena con saltos de línea
			$exam = implode("<br>", $examArray);

			$profesional = str_replace(',', "<br>", $data[0]["profesional"]);
			$especialidad = str_replace(',', "<br>", $data[0]["especialidad"]);

			if(!empty($data[0]["adjuntar"])){
				$doc = "<a href='".$_ENV["BASE_URL"] . 'app/libs/script/uploads/' . $data[0]["adjuntar"]."' target='_blank'><i class='fa fa-download' style='font-size:30px; color:#000;'></i></a>";
			} else {
				$doc = "Sin Adjunto";
			}

			HomeController::modal("logs", "<i class='fa fa-exclamation'></i> Ver Log",
				"<table class='table table-bordered table-striped table-condensed'>            
					<tbody>
						<tr>
							<td><strong>Rut</strong></td>
							<td>".$data[0]["rut"]."</td>
						</tr>
						<tr>
							<td><strong>Paciente</strong></td>
							<td>".ucwords($data[0]["nombres"]). ' ' . ucwords($data[0]["apellido_paterno"]). ' ' . ucwords($data[0]["apellido_materno"])."</td>
						</tr>
						<tr>
							<td><strong>Edad</strong></td>
							<td>".$data[0]["edad"]."</td>
						</tr>
						<tr>
							<td><strong>Fecha Solicitud</strong></td>
							<td>".$obt."</td>
						</tr>
						<tr>
							<td><strong>Estado</strong></td>
							<td>".$data[0]["estado"]."</td>
						</tr>
						<tr>
							<td><strong>Código</strong></td>
							<td>".$code."</td>
						</tr>
						<tr>
							<td><strong>Exámen</strong></td>
							<td>".$exam."</td>
						</tr>
						<tr>
							<td><strong>Especialidad</strong></td>
							<td>".$especialidad."</td>
						</tr>
						<tr>
							<td><strong>Profesional</strong></td>
							<td>".$profesional."</td>
						</tr>
						<tr>
							<td><strong>Fundamento</strong></td>
							<td>".$data[0]["fundamento"]."</td>
						</tr>
						<tr>
							<td><strong>Fecha Egreso</strong></td>
							<td>".$data[0]["fecha_egreso"]."</td>
						</tr>
						<tr>
							<td><strong>Motivo Egreso</strong></td>
							<td>". $nombre ."</td>
						</tr>
						<tr>
							<td><strong>Observación</strong></td>
							<td>".$data[0]["observacion"]."</td>
						</tr>
						<tr>
							<td><strong>Adjuntar</strong></td>
							<td>".$doc."</td>
						</tr>
					</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td class='text-right'>                        
							<div class='pdocrud-action-buttons pdocrud-button-delete'>
									<button data-action='back' data-dismiss='modal' class='btn btn-default pdocrud-form-control pdocrud-button pdocrud-back' type='button'><i class='fa fa-arrow-left'></i> Regresar</button>
							</div>                         
						</td>
					</tr>
				</tfoot>
			</table>"
			);
		}
	}

	public function cargar_modal_egresar_solicitud(){

		$request = new Request();

    	if ($request->getMethod() === 'POST') {

			$id = $request->post('id');
			$fecha_solicitud = $request->post('fecha_solicitud');
			$id_detalle_de_solicitud = $request->post('id_detalle_de_solicitud');

			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();

			$pdomodel->where("id_datos_paciente", $id, "=", "AND");
			$pdomodel->where("id_detalle_de_solicitud", $id_detalle_de_solicitud);
			$detalle_de_solicitud = $pdomodel->select("detalle_de_solicitud");

			$fecha_egreso = isset($detalle_de_solicitud[0]["fecha_egreso"]) ? $detalle_de_solicitud[0]["fecha_egreso"] : '';
			$motivo_egreso = isset($detalle_de_solicitud[0]["motivo_egreso"]) ? $detalle_de_solicitud[0]["motivo_egreso"] : '';
			$adjuntar = isset($detalle_de_solicitud[0]["adjuntar"]) ? $detalle_de_solicitud[0]["adjuntar"] : '';
			$adjuntar2 = isset($detalle_de_solicitud[0]["adjuntar2"]) ? $detalle_de_solicitud[0]["adjuntar2"] : '';
			$adjuntar3 = isset($detalle_de_solicitud[0]["adjuntar3"]) ? $detalle_de_solicitud[0]["adjuntar3"] : '';
			$adjuntar4 = isset($detalle_de_solicitud[0]["adjuntar4"]) ? $detalle_de_solicitud[0]["adjuntar4"] : '';

			$pdocrud->formFieldValue("fecha_egreso", $fecha_egreso);
			$pdocrud->formFieldValue("motivo_egreso", $motivo_egreso);
			$pdocrud->fieldAttributes("fecha_egreso", array("autocomplete"=>"off"));

			$pdocrud->formFieldValue("adjuntar", $adjuntar);
			$pdocrud->formFieldValue("adjuntar2", $adjuntar2);
			$pdocrud->formFieldValue("adjuntar3", $adjuntar3);
			$pdocrud->formFieldValue("adjuntar4", $adjuntar4);

			$pdocrud->fieldDesc("adjuntar", "Tamaño máximo de subida 15mb");
			$pdocrud->fieldDesc("adjuntar2", "Tamaño máximo de subida 15mb");
			$pdocrud->fieldDesc("adjuntar3", "Tamaño máximo de subida 15mb");
			$pdocrud->fieldDesc("adjuntar4", "Tamaño máximo de subida 15mb");

			$pdocrud->formFieldValue("observacion", $detalle_de_solicitud[0]["observacion"]);

			$pdocrud->formFieldValue("compra_servicio", $detalle_de_solicitud[0]["compra_servicio"]);
			$pdocrud->formFieldValue("empresas_en_convenio", $detalle_de_solicitud[0]["empresas_en_convenio"]);

			$pdocrud->formFieldValue("id_detalle_de_solicitud", $detalle_de_solicitud[0]["id_detalle_de_solicitud"]);
			
			$pdocrud->fieldHideLable("id_detalle_de_solicitud");
			$pdocrud->fieldDataAttr("id_detalle_de_solicitud", array("style"=>"display:none"));

			$pdocrud->fieldHideLable("id_datos_paciente");
			$pdocrud->fieldDataAttr("id_datos_paciente", array("style"=>"display:none"));

			$pdocrud->formStaticFields("listado_empresas_en_convenio", "html", "<label class='label_empresas_en_convenio mb-0'>Empresas en Convenio</label>");

			$pdocrud->fieldHideLable("empresas_en_convenio");
			$pdocrud->fieldHideLable("fecha_solicitud");
			$pdocrud->fieldDataAttr("fecha_solicitud", array("style"=>"display:none"));

			$pdocrud->buttonHide("submitBtn");
			$pdocrud->buttonHide("cancel");

			$pdocrud->formStaticFields("buttons", "html", "
				<div class='row justify-content-center'>
					<input type='submit' class='btn btn-info pdocrud-form-control pdocrud-submit' id='pdocrud_submit_jkFdX8o5Z9' data-action='selectform' value='Guardar'>
					<button type='button' class='btn btn-info' data-dismiss='modal'> Cerrar</button>
				</div>
				
			");
			$pdocrud->fieldDisplayOrder(array("id_datos_paciente","listado_empresas_en_convenio","empresas_en_convenio","id_detalle_de_solicitud","motivo_egreso","observacion", "fecha_egreso", "fecha_solicitud", "adjuntar", "adjuntar2", "adjuntar3", "adjuntar4", "agregar_mas_adjuntos", "compra_servicio", "buttons"));  

			$pdocrud->addPlugin("bootstrap-inputmask");
			$pdocrud->fieldTypes("motivo_egreso", "select");
			$pdocrud->fieldTypes("adjuntar", "FILE_NEW");
			$pdocrud->fieldTypes("adjuntar2", "FILE_NEW");
			$pdocrud->fieldTypes("adjuntar3", "FILE_NEW");
			$pdocrud->fieldTypes("adjuntar4", "FILE_NEW");
			$pdocrud->fieldAddOnInfo("fecha_egreso", "after", '<div class="input-group-append"><span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span></div>');
			$pdocrud->fieldDataBinding("motivo_egreso", "causal_salida", "id_causal_salida", "nombre");
			$pdocrud->formFields(array("id_datos_paciente","empresas_en_convenio","id_detalle_de_solicitud","motivo_egreso","observacion", "fecha_egreso", "fecha_solicitud", "adjuntar", "adjuntar2", "adjuntar3", "adjuntar4", "agregar_mas_adjuntos", "compra_servicio"));
			$pdocrud->setSettings("required", false);
			$pdocrud->fieldNotMandatory("observacion");
			$pdocrud->fieldNotMandatory("adjuntar");
			$pdocrud->fieldNotMandatory("adjuntar2");
			$pdocrud->fieldNotMandatory("adjuntar3");
			$pdocrud->fieldNotMandatory("adjuntar4");
			$pdocrud->setSettings("hideAutoIncrement", false);
			$pdocrud->fieldRenameLable("observacion", "Observación");

			$pdocrud->fieldRenameLable("adjuntar2", "Adjuntar");
			$pdocrud->fieldRenameLable("adjuntar3", "Adjuntar");
			$pdocrud->fieldRenameLable("adjuntar4", "Adjuntar");

			$pdocrud->fieldTypes("compra_servicio", "radio");
			$pdocrud->fieldTypes("empresas_en_convenio", "select");
			$pdocrud->fieldDataBinding("empresas_en_convenio", array("San Agustin"=> "San Agustin","Clinica maitenes" => "Clinica maitenes", "Red salud" => "Red salud", "Imared"=> "Imared", "Atryx"=> "Atryx"), "", "","array");
			$pdocrud->fieldDataBinding("compra_servicio", array("1"=> "si","2" => "no"), "", "","array");
			//$pdocrud->fieldDisplayOrder(array("id_datos_paciente","fecha_egreso","motivo_egreso","compra_servicio", "listado_empresas_en_convenio", "empresas_en_convenio", "observacion"));
			$pdocrud->joinTable("detalle_de_solicitud", "detalle_de_solicitud.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
			$pdocrud->joinTable("diagnostico_antecedentes_paciente", "diagnostico_antecedentes_paciente.id_datos_paciente = datos_paciente.id_datos_paciente", "INNER JOIN");
			$pdocrud->fieldCssClass("fecha_egreso", array("fecha_egreso"));
			$pdocrud->fieldCssClass("observacion", array("observacion"));
			$pdocrud->fieldCssClass("motivo_egreso", array("motivo_egreso"));
			$pdocrud->fieldCssClass("compra_servicio", array("compra_servicio"));
			$pdocrud->fieldCssClass("agregar_mas_adjuntos", array("agregar_mas_adjuntos"));
			$pdocrud->fieldCssClass("empresas_en_convenio", array("empresas_en_convenio"));

			//$pdomodel->where("id_datos_paciente", $id);
			//$observacion = $pdomodel->select("detalle_de_solicitud");

			$pdocrud->addCallback("before_select", "editar_egresar_solicitud");
			$pdocrud->setLangData("login", "Guardar");
			$render = $pdocrud->dbTable("datos_paciente")->render("selectform");
			HomeController::modal("egresar_solicitud", "<i class='fas fa-sign-out-alt'></i> Egresar Solicitud", $render);
		}
	}

	public function reportes_all(){
		$crud = DB::PDOCrud(true);
		$pdomodel = $crud->getPDOModelObj();
		$data = $pdomodel->DBQuery(
			"SELECT 
			ds.codigo_fonasa AS codigo_fonasa,
			ds.procedencia AS procedencia,
			GROUP_CONCAT(ds.examen) AS examen,
			GROUP_CONCAT(DISTINCT ds.tipo_examen) AS tipo_examen,
			YEAR(ds.fecha_solicitud) AS ano,
			ABS(MIN(DATEDIFF(ds.fecha, ds.fecha_solicitud))) AS cantidad_media,
			COUNT(ds.examen) AS total_examen
		FROM 
			datos_paciente AS dp
		INNER JOIN 
			detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
		WHERE 
			ds.estado != 'Egresado'
		GROUP BY
			ds.codigo_fonasa, ds.procedencia, YEAR(ds.fecha_solicitud)
		ORDER BY 
			ds.fecha ASC;"
		);

		echo json_encode(['data' => $data]);
	}

	public function reportes(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->addPlugin("select2");
		$pdocrud->addPlugin("bootstrap-inputmask");
		$html_data = array('
			<form action="#" method="POST" class="form_search">
			<div class="row">
				<div class="col-md-4">
					<label for="correo">Año Desde</label>
					<div class="input-group-append">
						<select class="form-control ano_desde" type="text" name="ano_desde" id="ano_desde">
							<option value="0">Seleccionar Año Desde</option>
						</select>
						<span class="btn btn-default border" id="basic-addon1">
							<i class="fa fa-calendar"></i>
						</span>
					</div>
				</div>
				<div class="col-md-4">
					<label for="fecha">Año Hasta</label>
					<div class="input-group-append">
						<select class="form-control ano_hasta" type="text" name="ano_hasta" id="ano_hasta">
							<option value="0">Seleccionar Año Hasta</option>
						</select>
						<span class="btn btn-default border" id="basic-addon1">
							<i class="fa fa-calendar"></i>
						</span>
					</div>
				</div>
				<div class="col-md-4">
					<label for="fecha">Procedencia</label>
					<div class="input-group-append">
						<select class="form-control procedencia_filtro" type="text" name="procedencia_filtro" id="procedencia_filtro">
							<option value="0">Seleccionar Procedencia</option>
							<option value="Hospitalizado">Hospitalizado</option>
							<option value="Urgencia">Urgencia</option>
							<option value="Ambulatorio">Ambulatorio</option>
							<option value="Sin Procedencia">Sin Procedencia</option>
						</select>
					</div>
				</div>
			</div>
			<div class="row mt-3 mb-4">
				<div class="col-md-12">
					<a href="javascript:;" class="btn btn-primary btn_search"><i class="fa fa-search"></i> Buscar</a>
					<a href="javascript:;" class="btn btn-danger btn_limpiar"><i class="fas fa-eraser"></i> Limpiar</a>
				</div>
			</div>	
		</form>
		');
		$render = $pdocrud->render("HTML", $html_data);
		$mask = $pdocrud->loadPluginJsCode("bootstrap-inputmask",".rut", array("mask"=> "'9{1,2}9{3}9{3}-(9|k|K)'", "casing" => "'upper'", "clearIncomplete" => "true", "numericInput"=> "true", "positionCaretOnClick" => "'none'"));
		$select2 = $pdocrud->loadPluginJsCode("select2",".ano_desde, .ano_hasta");

		View::render(
			"reportes",[
				'render' => $render,
				'mask' => $mask,
				'select2' => $select2
			]
		);
	}

	public function respaldos(){
		$respaldos = DB::PDOCrud();
        $respaldos->tableHeading("Respaldos");
        $respaldos->fieldTypes("file", "file");
        $respaldos->dbOrderBy("hora desc");
		$respaldos->tableColFormatting("fecha", "date",array("format" =>"d/m/Y"));
		$respaldos->setSearchCols(array("usuario", "fecha", "hora"));
        $respaldos->tableColFormatting("archivo", "html", array("type" => "html", "str" => "<a class='btn btn-success btn-sm' href=\"".$_ENV["BASE_URL"]."app/libs/script/uploads/{col-name}\" data-attribute=\"abc-{col-name}\"><i class=\"fa fa-download\"></i> Descargar Respaldo</a>"));
        $respaldos->setSettings("addbtn", false);
		$respaldos->setSettings("editbtn", false);
        $respaldos->setSettings("viewbtn", false);
        $respaldos->setSettings("printBtn", false);
        $respaldos->setSettings("pdfBtn", false);
        $respaldos->setSettings("csvBtn", false);
        $respaldos->setSettings("excelBtn", false);
		$respaldos->fieldTypes("archivo", "FILE_NEW");
		$respaldos->enqueueBtnTopActions("Report export",  "<i class='fa fa-database'></i> Generar Respaldo", "javascript:;", array(), "btn-report btn btn-success");
		$respaldos->crudRemoveCol(array("id"));
        $respaldos->addCallback("before_delete", "delete_file_data");
        $respaldos->addFilter("UserFilter", "Filtrar por Usuario que generó el respaldo", "usuario", "dropdown");
        $respaldos->setFilterSource("UserFilter", "backup", "usuario", "usuario as pl", "db");
        $respaldos->addFilter("DateFilter", "Filtrar por Fecha", "fecha", "dropdown");
        $respaldos->setFilterSource("DateFilter", "backup", "fecha", "fecha as pl", "db");
        $respaldos->addFilter("HourFilter", "Filtrar por Hora", "hora", "dropdown");
        $respaldos->setFilterSource("HourFilter", "backup", "hora", "hora as pl", "db");

        $render_respaldos = $respaldos->dbTable("backup")->render();

		View::render(
			"respaldos", [
				'render' => $render_respaldos
			]
		);
	}

	public function export_db()
	{
		if($_SERVER['REQUEST_METHOD'] === 'POST'){
			date_default_timezone_set("America/Santiago");
			$date = date('Y-m-d');
			$hour = date('G:i:s');
			$user = $_SESSION['usuario'][0]["usuario"];

			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$id = $pdomodel->select("backup");

			$exportDirectory = realpath(__DIR__ . '/../libs/script/uploads');

			// Verificar si el directorio existe y, si no, intentar crearlo
			if (!is_dir($exportDirectory) && !mkdir($exportDirectory, 0777, true)) {
				die('Error al crear el directorio de exportación');
			}

			$simpleBackup = SimpleBackup::setDatabase([
				$_ENV['DB_NAME'],
				$_ENV['DB_USER'],
				$_ENV['DB_PASS'],
				$_ENV['DB_HOST']
			])->storeAfterExportTo($exportDirectory, "procedimiento" . time() . ".sql");

			$file = $_ENV["BASE_URL"] . $_ENV['UPLOAD_URL'] . $simpleBackup->getExportedName();

			$pdomodel->insert("backup", array("archivo" => basename($file), "fecha" => $date, "hora" => $hour, "usuario" => $user));

			echo json_encode(['file' => $file, 'success' => 'Tus datos se han respaldado con éxito ']);
		}
	}


	public function carga_masiva_prestaciones(){
		$pdocrud = DB::PDOCrud();
		$pdocrud->fieldRenameLable("archivo", "Archivo Excel");
		$pdocrud->setLangData("save", "Subir");
		$pdocrud->setSettings("required", false);
		$pdocrud->fieldTypes("archivo", "FILE_NEW");
		$pdocrud->addCallback("before_insert", "carga_masiva_prestaciones_insertar");
		$render = $pdocrud->dbTable("carga_masiva_prestaciones")->render("insertform");

		$crud = DB::PDOCrud(true);
		$crud->formDisplayInPopup();
		$crud->setSettings("viewbtn", false);
		$crud->enqueueBtnTopActions("Report",  "<i class='fa fa-upload'></i> Carga Masiva", "javascript:;", array(), "btn-report btn btn-light carga_masiva");
		$crud->fieldRenameLable("tipo_de_examen", "Tipo de Exámen");
		$crud->fieldRenameLable("examen", "Exámen");
		$crud->formFields(array("tipo_solicitud","especialidad","tipo_de_examen", "codigo_fonasa", "glosa"));
		$crud->fieldRenameLable("codigo_fonasa", "Código Fonasa");
		$crud->setSearchCols(array("id_prestaciones","tipo_solicitud", "especialidad", "tipo_de_examen", "codigo_fonasa", "glosa"));
		$crud->crudRemoveCol(array("examen"));
		$crud->fieldGroups("Name",array("tipo_solicitud","especialidad"));
		$crud->fieldGroups("Name2",array("tipo_de_examen","codigo_fonasa"));
		$crud->setSettings("template", "prestaciones");
		$crud->colRename("id_prestaciones", "ID");
		$crud->buttonHide("submitBtnSaveBack");
		$crud->setSettings("printBtn", false);
		$crud->setSettings("pdfBtn", false);
		$crud->setSettings("csvBtn", false);
		$crud->setSettings("excelBtn", false);
		$render2 = $crud->dbTable("prestaciones")->render();
		View::render(
			"carga_masiva_prestaciones",[
				'render' => $render,
				'render2' => $render2
			]
		);
	}

	public function buscar_examenes_prestacion() {
		$request = new Request();
	
		if ($request->getMethod() === 'POST') {
			$query = $request->post('query');
			$tipo_examen = $request->post('tipo_examen'); // Nuevo parámetro
	
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
	
			$pdomodel->where("tipo_de_examen", "%$tipo_examen%", "LIKE");
			$pdomodel->where("glosa", "%$query%", "LIKE", "AND");
			$result = $pdomodel->select("prestaciones");
			
			$glosas = [];
			$codigosFonasa = [];
	
			foreach ($result as $row) {
				$glosas[] = $row['glosa'];
				$codigosFonasa[] = $row['codigo_fonasa'];
			}
	
			if (empty($result)) {
				$response = [
					'error' => 'No se encontraron resultados.'
				];
			} else {
				$response = [
					'glosa' => $glosas,
					'codigo_fonasa' => $codigosFonasa
				];
			}
	
			echo json_encode($response, JSON_UNESCAPED_UNICODE);
		}
	}	
	

	public function buscar_codigos_crud_daga(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$query = $request->post('query');
	
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->where("operacion", "%$query%", "LIKE");
			$pdomodel->andOrOperator = "OR";
			$pdomodel->where("codigo_o", "%$query%", "LIKE");
			$result = $pdomodel->select("codigo");
	
			$operaciones = [];
            foreach ($result as $row) {
                $operaciones[] = $row['codigo_o'] . " - " . $row['operacion'];
            }

            $response = [
                'operacion' => $operaciones,
            ];
        
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
		}
	}
	
	public function buscar_profesional(){
		
		$request = new Request();

    	if ($request->getMethod() === 'POST') {
			$query = $request->post('query');
	
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->where("CONCAT(nombre_profesional, ' ', apellido_profesional)", "%$query%", "LIKE");
			$result = $pdomodel->select("profesional");
	
			$nombre_completo = array(); // Inicializar el array de nombres

			foreach ($result as $profesional) {
				$nombre_completo[] = $profesional['nombre_profesional'] . ' ' . $profesional['apellido_profesional'];
			}

			$response = [
				'nombre_profesional' => $nombre_completo
			];

			echo json_encode($response, JSON_UNESCAPED_UNICODE);
		}
	}

	public function buscar_por_rut_o_estado(){
		$request = new Request();
	
		if ($request->getMethod() === 'POST') {
			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->columns = array(
				"count(ds.examen) AS total_examen",
				"ds.examen",
				"ds.codigo_fonasa",
				"ds.tipo_examen",
				"ds.fecha_solicitud",
				"dg_p.diagnostico",
				"dp.nombres",
				"dp.apellido_paterno",
				"dp.apellido_materno",
				"ds.estado",
				"dp.rut",
				"dp.fecha_y_hora_ingreso",
				"ds.fecha"
			);
	
			$pdomodel->joinTables("detalle_de_solicitud as ds", "ds.id_datos_paciente = dp.id_datos_paciente", "INNER JOIN");
			$pdomodel->joinTables("diagnostico_antecedentes_paciente as dg_p", "dg_p.id_datos_paciente = dp.id_datos_paciente", "INNER JOIN");
	
			$rut = $request->post('rut');
			$estado = $request->post('estado');

			if (!empty($rut)) {
				if (!self::validaRut($rut)) {
					$grilla_ingreso_egreso = $this->crud_ingreso_egreso();
					echo json_encode(['rut_invalid' => 'Ingrese un Rut válido', 'default' => $grilla_ingreso_egreso]);
					return;
				}
				$pdomodel->where("dp.rut", $rut);
				//$pdomodel->andOrOperator = "AND";
				//$pdomodel->where("ds.estado", "Agendado", "!=");
			} 
			
			if (!empty($estado)) {
				$pdomodel->where("ds.estado", $estado);
			}

			$pdomodel->groupByCols = array("dp.nombres", "dp.rut", "ds.fecha");
			$pdomodel->where("ds.estado", "Agendado", "!=");
			$data = $pdomodel->select("datos_paciente as dp");
			//echo $pdomodel->getLastQuery();
	
			if (empty($rut) && empty($estado)) {
				$grilla_ingreso_egreso = $this->crud_ingreso_egreso();
				echo json_encode(['error' => 'No se encontraron resultados', 'default' => $grilla_ingreso_egreso]);
            	return;
			}
			// If results exist, render the HTML
			$html = '
				<table class="table table-striped tabla_reportes text-center" style="width:100%">
					<thead class="bg-primary">
						<tr>
							<th>Código Fonasa</th>
							<th>Paciente</th>
							<th>Rut</th>
							<th>Fecha Solicitud</th>
							<th>Diagnóstico CIE-10</th>
							<th>Exámen</th>
							<th>Estado</th>
							<th>Tipo de Exámen</th>
							<th>Año</th>
							<th>Total Exámenes</th>
						</tr>
					</thead>
					<tbody>
			';

			foreach ($data as $row) {
				$nombre_completo = $row["nombres"] . ' ' . $row["apellido_paterno"] . ' ' . $row["apellido_materno"];
				$ano = date('Y', strtotime($row["fecha"]));
				$year = "";
				if($ano != "1970"){
					$year = $ano;
				} else {
					$year = "Sin Año";
				}
				$html .= '
					<tr>
						<td>' . $row['codigo_fonasa'] . '</td>
						<td>' . ucwords($nombre_completo) . '</td>
						<td>' . $row["rut"] . '</td>
						<td>' . date('d-m-Y', strtotime($row["fecha_solicitud"])) . '</td>
						<td>' . $row["diagnostico"] . '</td>
						<td>' . $row["examen"] . '</td>
						<td>' . $row["estado"] . '</td>
						<td>' . $row["tipo_examen"] . '</td>
						<td>' . $year . '</td>
						<td>' . $row["total_examen"] . '</td>
					</tr>
				';
			}

			$html .= '
					</tbody>
				</table>
			';
				
			$html_data = array($html);
			$render = $pdocrud->render("HTML", $html_data);
			echo json_encode(['render' => $render]);
		}
	}	
	

	public function buscar_por_ano() {
		$request = new Request();
	
		if ($request->getMethod() === 'POST') {
			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();
	
			$where = "ds.estado != 'Egresado'";
			$ano_desde = $request->post('ano_desde');
			$ano_hasta = $request->post('ano_hasta');
			$procedencia = $request->post('procedencia');
	
			if ($ano_desde != "0" && $ano_hasta != "0") {
				$where .= " AND YEAR(ds.fecha_solicitud) BETWEEN '$ano_desde' AND '$ano_hasta'";
			} else {
				// Si solo se proporciona un año, filtrar por ese año específico
				if ($ano_desde != "0") {
					$where .= " AND YEAR(ds.fecha_solicitud) = '$ano_desde'";
				}
				if ($ano_hasta != "0") {
					$where .= " AND YEAR(ds.fecha_solicitud) = '$ano_hasta'";
				}
			}
	
			// Construir la condición para filtrar por procedencia
			if ($procedencia != "0") {
				if ($procedencia == 'Sin Procedencia') {
					$where .= " AND (ds.procedencia IS NULL OR ds.procedencia = 'Sin Procedencia')";
				} else {
					$where .= " AND ds.procedencia = '$procedencia'";
				}
			}
	
			$data = $pdomodel->DBQuery(
				"SELECT
				ds.codigo_fonasa AS codigo_fonasa,
				ds.procedencia AS procedencia,
				GROUP_CONCAT(ds.examen) AS examen,
				GROUP_CONCAT(DISTINCT ds.tipo_examen) AS tipo_examen,
				YEAR(ds.fecha_solicitud) AS ano,
				ABS(MIN(DATEDIFF(ds.fecha, ds.fecha_solicitud))) AS cantidad_media,
				COUNT(ds.examen) AS total_examen
				FROM
					datos_paciente AS dp
				INNER JOIN 
					detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
				WHERE 
					".$where."
				GROUP BY
					ds.codigo_fonasa, ds.procedencia, YEAR(ds.fecha_solicitud)
				ORDER BY 
					ds.fecha ASC"
			);

			//echo $pdomodel->getLastQuery();
			//die();
			
			echo json_encode(['data' => $data]);
		}
	}

	public function descargar_excel_reportes_default(){
		$pdocrud = DB::PDOCrud(true);
		$pdomodel = $pdocrud->getPDOModelObj();

		$data = $pdomodel->DBQuery(
			"SELECT
			ds.codigo_fonasa AS codigo_fonasa,
			ds.procedencia AS procedencia,
			GROUP_CONCAT(ds.examen) AS examen,
			GROUP_CONCAT(DISTINCT ds.tipo_examen) AS tipo_examen,
			YEAR(ds.fecha_solicitud) AS ano,
			ABS(MIN(DATEDIFF(ds.fecha, ds.fecha_solicitud))) AS cantidad_media,
			COUNT(ds.examen) AS total_examen
			FROM
				datos_paciente AS dp
			INNER JOIN 
				detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
			GROUP BY
				ds.codigo_fonasa, ds.procedencia, YEAR(ds.fecha_solicitud)
			ORDER BY 
				ds.fecha ASC"
		);

		$dataValues = array_map(function($row) {
			return array_values($row);
		}, $data);

		// Definir los títulos de las columnas
		$columnTitles = [
			'Código Fonasa', 'Procedencia', 'Exámen', 'Tipo de Exámen', 'Año', 'Media', 'Total Exámenes'
		];

		// Insertar los títulos en la primera fila de los datos
		array_unshift($dataValues, $columnTitles);

		// Exportar los datos con los títulos al Excel
		$pdomodel->arrayToExcel($dataValues, "reportes.xlsx");
	}


	public function descargar_excel_reportes(){
		$request = new Request();

		if ($request->getMethod() === 'POST') {
			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();

			$ano_desde = $request->post('ano_desde');
			$ano_hasta = $request->post('ano_hasta');
			$procedencia = $request->post('procedencia');

			$where = "ds.estado != 'Egresado'";
			if ($ano_desde != "0" && $ano_hasta != "0") {
				$where .= " AND YEAR(ds.fecha_solicitud) BETWEEN '$ano_desde' AND '$ano_hasta'";
			} else {
				// Si solo se proporciona un año, filtrar por ese año específico
				if ($ano_desde != "0") {
					$where .= " AND YEAR(ds.fecha_solicitud) = '$ano_desde'";
				}
				if ($ano_hasta != "0") {
					$where .= " AND YEAR(ds.fecha_solicitud) = '$ano_hasta'";
				}
			}

			// Construir la condición para filtrar por procedencia
			if ($procedencia != "0") {
				if ($procedencia == 'Sin Procedencia') {
					$where .= " AND (ds.procedencia IS NULL OR ds.procedencia = 'Sin Procedencia')";
				} else {
					$where .= " AND ds.procedencia = '$procedencia'";
				}
			}

			$data = $pdomodel->DBQuery(
				"SELECT
				ds.codigo_fonasa AS codigo_fonasa,
				ds.procedencia AS procedencia,
				GROUP_CONCAT(ds.examen) AS examen,
				GROUP_CONCAT(DISTINCT ds.tipo_examen) AS tipo_examen,
				YEAR(ds.fecha_solicitud) AS ano,
				ABS(MIN(DATEDIFF(ds.fecha, ds.fecha_solicitud))) AS cantidad_media,
				COUNT(ds.examen) AS total_examen
				FROM
					datos_paciente AS dp
				INNER JOIN 
					detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
				WHERE 
					".$where."
				GROUP BY
					ds.codigo_fonasa, ds.procedencia, YEAR(ds.fecha_solicitud)
				ORDER BY 
					ds.fecha ASC"
			);

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->fromArray($data, null, 'A2');

			// Definir los títulos de las columnas
			$columnTitles = ['Código Fonasa', 'Procedencia', 'Exámen', 'Tipo de Exámen', 'Año', 'Media', 'Total Exámenes'];
			$sheet->fromArray([$columnTitles], null, 'A1');

			// Crear el objeto de escritura de Excel
			$writer = new Xlsx($spreadsheet);

			// Guardar el archivo Excel en el servidor con un nombre único
			$filename = 'reporte.xlsx';
			$filePath = dirname(__DIR__) .'/libs/script/uploads/' . $filename; // Cambiar esto por la ruta donde deseas guardar el archivo

			$writer->save($filePath);

			// Devolver la URL del archivo Excel generado
			$baseUrl = $_ENV["BASE_URL"];
			$excelUrl = $baseUrl . "app/libs/script/uploads/" . $filename;

			echo json_encode(['excel' => $excelUrl]);
		}
	}

	public function buscar_examenes(){
		
		$request = new Request();

		if($request->getMethod() === 'POST'){

			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();

			$currentWeekStart = date('Y-m-d', strtotime('monday this week'));

			$where = "";
			$run = $request->post('run');
			$pasaporte = $request->post('pasaporte');
			$nombre_paciente = $request->post('nombre_paciente');
			$estado = $request->post('estado');
			$procedencia = $request->post('procedencia');
			$prestacion = $request->post('prestacion');
			$profesional = $request->post('profesional');
			$fecha_solicitud = $request->post('fecha_solicitud');
			$adjuntar = $request->post('adjuntar');

			if (empty($run) && empty($pasaporte) && empty($nombre_paciente) && empty($estado) && empty($procedencia) && empty($prestacion) && empty($profesional) && empty($fecha_solicitud) && empty($adjuntar)) {
				echo json_encode(["error" => "Debe ingresar al menos un campo para realizar la búsqueda"]);
				return;
			}

			if (!empty($run)) {

				if (!self::validaRut($run)) {
					echo json_encode(["error" => "Rut Inválido"]);
					return;
				}

				$where .= " AND dp.rut = '$run' ";
			} 

			if (!empty($pasaporte)) {
				$where .= " AND dp.pasaporte_o_codigo_interno LIKE '%$pasaporte%' ";
			}

			if (!empty($nombre_paciente)) {
				$where .= " AND (dp.nombres = '$nombre_paciente' OR CONCAT(dp.nombres, ' ', dp.apellido_paterno) = '$nombre_paciente' OR CONCAT(dp.nombres, ' ', dp.apellido_paterno, ' ', dp.apellido_materno) = '$nombre_paciente' OR CONCAT(dp.nombres, ' ', dp.apellido_materno) = '$nombre_paciente')";
			}

			if (!empty($estado)) {
				$where .= " AND ds.estado = '$estado' ";
			}

			if (!empty($procedencia)) {
				$where .= " AND ds.procedencia = '$procedencia' ";
			}

			if (!empty($prestacion)) {
				$where .= " AND ds.examen LIKE '%$prestacion%' ";
			}

			if (!empty($profesional)) {
				$where .= " AND (pro.nombre_profesional = '$profesional' OR CONCAT(pro.nombre_profesional, ' ', pro.apellido_profesional) = '$profesional' OR pro.apellido_profesional = '$profesional')";
			}

			if (!empty($fecha_solicitud)) { 
				$where .= " AND dg_p.fecha_solicitud_paciente = '$fecha_solicitud' ";
			}

			if (!empty($adjuntar)) {
				// Check if $adjuntar is 'si' or 'no'
				if (strtolower($adjuntar) === 'si') {
					$where .= " AND ds.adjuntar IS NOT NULL AND ds.adjuntar != '' "; // Check for non-empty URL
				} elseif (strtolower($adjuntar) === 'no') {
					$where .= " AND (ds.adjuntar IS NULL OR ds.adjuntar = '') "; // Check for NULL or empty string
				} else {
					// Handle invalid 'adjuntar' value
					echo json_encode(["error" => "Valor inválido para el campo 'adjuntar'"]);
					return;
				}
			}

			$query = "SELECT 
					DISTINCT
					dp.id_datos_paciente,
					ds.id_detalle_de_solicitud,
					dp.rut,
					dp.pasaporte_o_codigo_interno,
					CONCAT(dp.nombres, ' ', dp.apellido_paterno, ' ', dp.apellido_materno) AS paciente,
					dp.telefono,
					dp.edad,
					ds.fecha_egreso,
					ds.fecha_solicitud AS fecha_solicitud,
					ds.estado AS estado,
					ds.codigo_fonasa AS codigo,
					ds.examen,
					ds.procedencia AS procedencia,
					ds.fecha AS fecha,
					dg_p.especialidad AS especialidad,
					CONCAT(pro.nombre_profesional, ' ', pro.apellido_profesional) AS profesional,
					CASE WHEN ds.adjuntar IS NOT NULL AND ds.adjuntar != '' THEN 'Si' ELSE 'No' END AS tiene_adjunto
				FROM 
					datos_paciente dp
				INNER JOIN 
					detalle_de_solicitud ds ON ds.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					diagnostico_antecedentes_paciente dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
				INNER JOIN 
					profesional pro ON pro.id_profesional = dg_p.profesional
				WHERE 
					dg_p.fecha_solicitud_paciente = ds.fecha_solicitud " . $where . "
				GROUP BY 
					dp.id_datos_paciente, dp.rut, dp.edad, ds.fecha, ds.fecha_solicitud, ds.examen
			";

			$data = $pdomodel->DBQuery($query);

			echo json_encode(['data' => $data]);

		}
	}


	public function descargar_excel_lista_espera_examenes_default(){

		$pdocrud = DB::PDOCrud(true);
		$pdomodel = $pdocrud->getPDOModelObj();

		$data = $pdomodel->DBQuery(
			"SELECT 
				DISTINCT
				dp.id_datos_paciente,
				ds.id_detalle_de_solicitud,
				dp.rut,
				CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS paciente,
				dp.telefono,
				dp.edad,
				ds.fecha_egreso,
				ds.motivo_egreso,
				fecha_solicitud as fecha_solicitud,
				ds.estado AS estado,
				codigo_fonasa AS codigo,
				ds.examen,
				ds.procedencia AS procedencia,
				ds.fecha as fecha,
				especialidad AS especialidad,
				CONCAT(nombre_profesional, ' ', apellido_profesional) AS profesional,
				CASE WHEN ds.adjuntar IS NOT NULL AND ds.adjuntar != '' THEN 'Si' ELSE 'No' END AS tiene_adjunto
			FROM 
				datos_paciente AS dp
			INNER JOIN 
				detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
			INNER JOIN 
				diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
			INNER JOIN 
				profesional AS pro ON pro.id_profesional = dg_p.profesional
			WHERE dg_p.fecha_solicitud_paciente = ds.fecha_solicitud
			GROUP BY 
				dp.id_datos_paciente, dp.rut, dp.edad, ds.fecha, ds.fecha_solicitud, ds.examen"
		);

		$dataValues = array_map(function($row) {
			return array_values($row);
		}, $data);

		// Definir los títulos de las columnas
		$columnTitles = [
			'ID Paciente', 'ID Solicitud', 'RUT', 'Paciente', 'Teléfono', 'Edad', 'Fecha Egreso', 'Motivo Egreso',
			'Fecha Solicitud', 'Estado', 'Código Fonasa', 'Examen', 'Procedencia', 'Fecha', 'Especialidad', 'Profesional', 'Adjuntar'
		];

		// Insertar los títulos en la primera fila de los datos
		array_unshift($dataValues, $columnTitles);

		// Exportar los datos con los títulos al Excel
		$pdomodel->arrayToExcel($dataValues, "lista_espera_examenes.xlsx");
	}


	public function descargar_excel_lista_espera_examenes(){

		$request = new Request();

		$pdocrud = DB::PDOCrud(true);
		$pdomodel = $pdocrud->getPDOModelObj();

		$where = "";
		$run = $request->get('run');
		$nombre_paciente = $request->get('nombre_paciente');
		$estado = $request->get('estado');
		$procedencia = $request->get('procedencia');
		$prestacion = $request->get('prestacion');
		$profesional = $request->get('profesional');
		$fecha_solicitud = $request->get('fecha_solicitud');
		$adjuntar = $request->get('adjuntar');

		if (!empty($run)) {

			if (!self::validaRut($run)) {
				echo "<div class='alert alert-danger text-center'>RUT inválido</div>";
				return;
			}

			$where .= " AND dp.rut = '$run' ";
		}

		if (!empty($nombre_paciente)) {
			$where .= " AND (dp.nombres = '$nombre_paciente' OR CONCAT(dp.nombres, ' ', dp.apellido_paterno) = '$nombre_paciente' OR CONCAT(dp.nombres, ' ', dp.apellido_paterno, ' ', dp.apellido_materno) = '$nombre_paciente' OR CONCAT(dp.nombres, ' ', dp.apellido_materno) = '$nombre_paciente')";
		}

		if (!empty($estado)) {
			$where .= " AND ds.estado = '$estado' ";
		}

		if (!empty($procedencia)) {
			$where .= " AND ds.procedencia = '$procedencia' ";
		}

		if (!empty($prestacion)) {
			$where .= " AND ds.examen LIKE '%$prestacion%' ";
		}

		if (!empty($profesional)) {
			$where .= " AND (pro.nombre_profesional = '$profesional' OR CONCAT(pro.nombre_profesional, ' ', pro.apellido_profesional) = '$profesional' OR pro.apellido_profesional = '$profesional')";
		}

		if (!empty($fecha_solicitud)) {
			$where .= " AND dg_p.fecha_solicitud_paciente = '$fecha_solicitud' ";
		}

		if (!empty($adjuntar)) {
			// Check if $adjuntar is 'si' or 'no'
			if (strtolower($adjuntar) === 'si') {
				$where .= " AND ds.adjuntar IS NOT NULL AND ds.adjuntar != '' "; // Check for non-empty URL
			} elseif (strtolower($adjuntar) === 'no') {
				$where .= " AND (ds.adjuntar IS NULL OR ds.adjuntar = '') "; // Check for NULL or empty string
			} else {
				// Handle invalid 'adjuntar' value
				echo json_encode(["error" => "Valor inválido para el campo 'adjuntar'"]);
				return;
			}
		}

		$data = $pdomodel->DBQuery(
			"SELECT 
				dp.id_datos_paciente,
				ds.id_detalle_de_solicitud,
				dp.rut,
				CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS paciente,
				dp.telefono,
				dp.edad,
				ds.fecha_egreso,
				ds.motivo_egreso,
				fecha_solicitud as fecha_solicitud,
				ds.estado AS estado,
				codigo_fonasa AS codigo,
				ds.examen,
				ds.procedencia AS procedencia,
				ds.fecha as fecha,
				especialidad AS especialidad,
				CONCAT(nombre_profesional, ' ', apellido_profesional) AS profesional,
				CASE WHEN ds.adjuntar IS NOT NULL AND ds.adjuntar != '' THEN 'Si' ELSE 'No' END AS tiene_adjunto
			FROM 
				datos_paciente AS dp
			INNER JOIN 
				detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
			INNER JOIN 
				diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
			INNER JOIN 
				profesional AS pro ON pro.id_profesional = dg_p.profesional
			WHERE dg_p.fecha_solicitud_paciente = ds.fecha_solicitud " . $where . "
			GROUP BY 
				dp.id_datos_paciente, dp.rut, dp.edad, ds.fecha, ds.fecha_solicitud, ds.examen"
		);

		$dataValues = array_map(function($row) {
			return array_values($row);
		}, $data);

		// Definir los títulos de las columnas
		$columnTitles = [
			'ID Paciente', 'ID Solicitud', 'RUT', 'Paciente', 'Teléfono', 'Edad', 'Fecha Egreso', 'Motivo Egreso',
			'Fecha Solicitud', 'Estado', 'Código Fonasa', 'Examen', 'Procedencia', 'Fecha', 'Especialidad', 'Profesional', 'Adjuntar'
		];

		// Insertar los títulos en la primera fila de los datos
		array_unshift($dataValues, $columnTitles);

		// Exportar los datos con los títulos al Excel
		$pdomodel->arrayToExcel($dataValues, "lista_espera_examenes.xlsx");
	}


	public function mostrar_adjunto(){
		$request = new Request();
		$id = $request->get('id');
		$id_detalle_de_solicitud = $request->get('id_detalle_de_solicitud');

		$pdocrud = DB::PDOCrud(true);
		$pdomodel = $pdocrud->getPDOModelObj();
		$data = $pdomodel->DBQuery(
			"SELECT 
			dp.id_datos_paciente,
			ds.id_detalle_de_solicitud,
			adjuntar
		FROM 
			datos_paciente AS dp
		INNER JOIN
			detalle_de_solicitud AS ds ON ds.id_datos_paciente = dp.id_datos_paciente
		INNER JOIN
			diagnostico_antecedentes_paciente AS dg_p ON dg_p.id_datos_paciente = dp.id_datos_paciente
		INNER JOIN 
			profesional AS pro ON pro.id_profesional = dg_p.profesional
		WHERE 
			dg_p.fecha_solicitud_paciente = ds.fecha_solicitud AND ds.id_detalle_de_solicitud = ".$id_detalle_de_solicitud
		);

		$pdf = $_ENV["BASE_URL"] . "/app/libs/script/uploads/" . $data[0]["adjuntar"];
		echo "<object data='".$pdf."' type='application/pdf' width='100%' height='100%'>
			<p>No hay archivo Adjunto.</p>
		</object>";
	}


	public static function validaRut($rut)
	{
		// Eliminar puntos y espacios del RUT
		$rut = str_replace('.', '', trim($rut));

		// Verificar que el RUT tenga al menos un guión
		if (strpos($rut, "-") === false) {
			// Si no hay guión, asumir que el último carácter es el dígito verificador
			$RUT[0] = substr($rut, 0, -1);
			$RUT[1] = substr($rut, -1);
		} else {
			// Separar el RUT en la parte numérica y el dígito verificador
			$RUT = explode("-", $rut);
		}

		// Asegurarse de que $RUT[0] solo contenga dígitos
		if (!ctype_digit($RUT[0])) {
			return false;
		}

		// Convertir $RUT[0] a número
		$elRut = $RUT[0];

		$factor = 2;
		$suma = 0;

		// Recorrer los dígitos del RUT desde el final hacia el inicio
		for ($i = strlen($elRut) - 1; $i >= 0; $i--) {
			$factor = $factor > 7 ? 2 : $factor;
			$suma += $elRut[$i] * $factor++;
		}

		// Calcular el dígito verificador esperado
		$resto = $suma % 11;
		$dv = 11 - $resto;
		if ($dv == 11) {
			$dv = 0;
		} else if ($dv == 10) {
			$dv = "k";
		} else {
			$dv = $dv;
		}

		// Comparar el dígito verificador calculado con el proporcionado
		if ($dv == trim(strtolower($RUT[1]))) {
			return true;
		} else {
			return false;
		}
	}

	
	public function agregar_paciente(){
		$request = new Request();

		if ($request->getMethod() === 'POST') {

			$sexo = $request->post('sexo');
			$telefono = $request->post('telefono');
			$fecha_nacimiento = $request->post("fecha_nacimiento");
			$edad = $request->post('edad');
			$rut = $request->post('rut');
			$pasaporte_o_codigo_interno = $request->post('pasaporte_o_codigo_interno');
			$nombres = $request->post('nombres');
			$direccion = $request->post('direccion');
			$apellido_paterno = $request->post('apellido_paterno');
			$apellido_materno = $request->post('apellido_materno');
			$fecha_y_hora_ingreso = $request->post('fecha_y_hora_ingreso');

			$especialidad = $request->post('especialidad');
			$profesional = $request->post('profesional');
			$diagnostico = $request->post('diagnostico');
			$sintomas_principales = $request->post('sintomas_principales');
			$diagnostico_libre = $request->post('diagnostico_libre');

			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();

			if (empty($rut) && empty($pasaporte_o_codigo_interno)) {
				$mensaje = 'Debe ingresar al menos el RUT o el Pasaporte';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if (!self::validaRut($rut)) {
				echo json_encode(['error' => 'RUT inválido']);
				return;
			} else if(empty($nombres)){
				$mensaje = 'El campo Nombres es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($apellido_paterno)){
				$mensaje = 'El campo Apellido paterno es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($apellido_materno)){
				$mensaje = 'El campo Apellido materno es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($fecha_nacimiento)){
				$mensaje = 'El campo Fecha nacimiento es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($edad)){
				$mensaje = 'El campo Edad es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($direccion)){
				$mensaje = 'El campo Dirección es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($sexo)){
				$mensaje = 'El campo Sexo es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($telefono)){
				$mensaje = 'El campo Telefono es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			}  else if(empty($especialidad)){
				$mensaje = 'El campo Especialidad es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($profesional)){
				$mensaje = 'El campo Profesional es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			} else if(empty($diagnostico)){
				$mensaje = 'El campo Diagnóstico CIE-10 es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;
			/*} else if(empty($sintomas_principales)){
				$mensaje = 'El campo Síntomas Principales es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;*/
			} else if(strpos($diagnostico, '--------------------') !== false && empty($diagnostico_libre)){
				$mensaje = 'El campo Diagnóstico Libre es Obligatorio';
				echo json_encode(['error' => $mensaje]);
				return;

			} 

			$session_data_detalle_de_solicitud = $pdomodel->DBQuery(
				"SELECT * FROM session_data_detalle_de_solicitud 
				WHERE usuario_sesion = '".$_SESSION["usuario"][0]["usuario"]."' "
			);

			$contar_sesion_data = count($session_data_detalle_de_solicitud);

			if ($contar_sesion_data <= 0) {
				echo json_encode(['error' => 'Ingrese al menos 1 Detalle de Solicitud']);
				return;
			}

			$pdomodel->where("rut", $rut);
			$pdomodel->andOrOperator = "AND";
			$pdomodel->where("pasaporte_o_codigo_interno", $pasaporte_o_codigo_interno);
			$pdomodel->andOrOperator = "AND";
			$pdomodel->where("nombres", $nombres);
			$pdomodel->andOrOperator = "AND";
			$pdomodel->where("apellido_paterno", $apellido_paterno);
			$pdomodel->andOrOperator = "AND";
			$pdomodel->where("apellido_materno", $apellido_materno);
			$pdomodel->andOrOperator = "AND";
			$pdomodel->where("fecha_nacimiento", $fecha_nacimiento);
			$pdomodel->andOrOperator = "AND";
			$pdomodel->where("edad", $edad);
			$pdomodel->andOrOperator = "AND";
			$pdomodel->where("sexo", $sexo);
			$pdomodel->openBrackets = "(";
			$pdomodel->andOrOperator = "OR";
			$pdomodel->where("direccion", $direccion);
			$pdomodel->andOrOperator = "OR";
			$pdomodel->where("telefono", $telefono);
			$pdomodel->closedBrackets = ")";
			$datos_paciente_exists = $pdomodel->select("datos_paciente");

			if (!empty($datos_paciente_exists)) {
				echo json_encode(['error' => 'El Paciente Agregado ya existe']);
				return;
			} else {
				$pdomodel->insert("datos_paciente", array(
					"rut" => $rut,
					"pasaporte_o_codigo_interno" => $pasaporte_o_codigo_interno,
					"nombres" => $nombres,
					"telefono" => $telefono,
					"apellido_paterno" => $apellido_paterno,
					"apellido_materno" => $apellido_materno,
					"fecha_nacimiento" => $fecha_nacimiento,
					"edad" => $edad,
					"direccion" => $direccion,
					"sexo" => $sexo,
					"fecha_y_hora_ingreso" => $fecha_y_hora_ingreso
				));

				$id = $pdomodel->lastInsertId;
				echo json_encode(['success'=> 'Paciente Agregado con éxito', 'id' => $id]);
			}
		}
	}

	public function ingresar_datos_pacientes() {

		SessionManager::startSession();
		$request = new Request();
	
		if ($request->getMethod() === 'POST') {
	
			date_default_timezone_set('America/Santiago');
			$fecha_ingreso = date('Y-m-d H:i:s');
			$usuario = $_SESSION['usuario'][0]["usuario"];
	
			// Recuperar datos del request
			$paciente = $request->post('paciente');
			$sexo = $request->post('sexo');
			$telefono = $request->post('telefono');
			$fecha_nacimiento = $request->post("fecha_nacimiento");
			$edad = $request->post('edad');
			$rut = $request->post('rut');
			$pasaporte_o_codigo_interno = $request->post('pasaporte_o_codigo_interno');
			$nombres = $request->post('nombres');
			$direccion = $request->post('direccion');
			$apellido_paterno = $request->post('apellido_paterno');
			$apellido_materno = $request->post('apellido_materno');
			$especialidad = $request->post('especialidad');
			$profesional = $request->post('profesional');
			$diagnostico = $request->post('diagnostico');
			$sintomas_principales = $request->post('sintomas_principales');
			$diagnostico_libre = $request->post('diagnostico_libre');
			$fecha_solicitud = $request->post('fecha_solicitud');
	
			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();
	
			// Validaciones
			if (empty($rut) && empty($pasaporte_o_codigo_interno)) {
				echo json_encode(['error' => 'Debe ingresar al menos el RUT o el Pasaporte']);
				return;
			} else if (!self::validaRut($rut)) {
				echo json_encode(['error' => 'RUT inválido']);
				return;
			} else if (empty($nombres)) {
				echo json_encode(['error' => 'El campo Nombres es Obligatorio']);
				return;
			} else if (empty($apellido_paterno)) {
				echo json_encode(['error' => 'El campo Apellido paterno es Obligatorio']);
				return;
			} else if (empty($apellido_materno)) {
				echo json_encode(['error' => 'El campo Apellido materno es Obligatorio']);
				return;
			} else if (empty($fecha_nacimiento)) {
				echo json_encode(['error' => 'El campo Fecha nacimiento es Obligatorio']);
				return;
			} else if (empty($edad)) {
				echo json_encode(['error' => 'El campo Edad es Obligatorio']);
				return;
			} else if (empty($direccion)) {
				echo json_encode(['error' => 'El campo Dirección es Obligatorio']);
				return;
			} else if (empty($sexo)) {
				echo json_encode(['error' => 'El campo Sexo es Obligatorio']);
				return;
			} else if (empty($paciente)) {
				echo json_encode(['error' => 'Ingrese o Busque un Paciente Para continuar']);
				return;
			} else if (empty($telefono)) {
				echo json_encode(['error' => 'El campo Telefono es Obligatorio']);
				return;
			} else if (empty($especialidad)) {
				echo json_encode(['error' => 'El campo Especialidad es Obligatorio']);
				return;
			} else if (empty($profesional)) {
				echo json_encode(['error' => 'El campo Profesional es Obligatorio']);
				return;
			} else if (empty($diagnostico)) {
				echo json_encode(['error' => 'El campo Diagnóstico CIE-10 es Obligatorio']);
				return;
			} else if (strpos($diagnostico, '--------------------') !== false && empty($diagnostico_libre)) {
				echo json_encode(['error' => 'El campo Diagnóstico Libre es Obligatorio']);
				return;
			}
			
			$session_data_detalle_de_solicitud = $pdomodel->DBQuery(
				"SELECT * FROM session_data_detalle_de_solicitud 
				WHERE usuario_sesion = '".$_SESSION["usuario"][0]["usuario"]."' "
			);

			$contar_sesion_data = count($session_data_detalle_de_solicitud);

			if ($contar_sesion_data <= 0) {
				echo json_encode(['error' => 'Ingrese al menos 1 Detalle de Solicitud']);
				return;
			}
	
			// Verificar si el paciente ya existe en la base de datos
			$pdomodel->where("rut", $rut, "=", "AND");
			$pdomodel->where("pasaporte_o_codigo_interno", $pasaporte_o_codigo_interno, "=", "AND");
			$pdomodel->where("nombres", $nombres, "=", "AND");
			$pdomodel->where("apellido_paterno", $apellido_paterno, "=", "AND");
			$pdomodel->where("apellido_materno", $apellido_materno, "=", "AND");
			$pdomodel->where("fecha_nacimiento", $fecha_nacimiento, "=", "AND");
			$pdomodel->where("edad", $edad, "=", "AND");
			$pdomodel->where("direccion", $direccion, "=", "AND");
			$pdomodel->where("sexo", $sexo, "=", "AND");
			$pdomodel->where("telefono", $telefono);
			$datos_paciente_exists = $pdomodel->select("datos_paciente");
			
			if (empty($datos_paciente_exists)) {
				echo json_encode(['error' => 'Paciente no encontrado']);
				return;
			}
			
			$id = $datos_paciente_exists[0]['id_datos_paciente'];
	
			// Insertar datos en la tabla de diagnóstico
			$pdomodel->insert("diagnostico_antecedentes_paciente", array(
				"id_datos_paciente" => $id,
				"especialidad" => $especialidad,
				"profesional" => $profesional,
				"diagnostico" => $diagnostico,
				"sintomas_principales" => $sintomas_principales,
				"diagnostico_libre" => $diagnostico_libre,
				"fecha_solicitud_paciente" => $fecha_solicitud
			));

			 $dataToInsert = [];
			 foreach ($session_data_detalle_de_solicitud as $sesionVal) {
				$archivoAdjunto = '';
				if (isset($sesionVal['adjuntar']) && !empty($sesionVal['adjuntar'])) {
					$archivoAdjunto = basename($sesionVal['adjuntar']);
				}

				 $dataToInsert[] = [
					 'id_datos_paciente' => $id,
					 'codigo_fonasa' => $sesionVal['codigo_fonasa'],
					 'tipo_solicitud' => $sesionVal['tipo_solicitud'],
					 'fecha_solicitud' => $sesionVal['fecha_solicitud'],
					 'tipo_examen' => $sesionVal['tipo_examen'],
					 'examen' => $sesionVal['examen'],
					 'plano' => $sesionVal['plano'],
					 'extremidad' => $sesionVal['extremidad'],
					 'procedencia' => $sesionVal['procedencia'],
					 'observacion' => $sesionVal['observacion'],
					 'contraste' => $sesionVal['contraste'],
					 'adjuntar' => $archivoAdjunto, // Manejar archivo adjunto
					 'creatinina' => $sesionVal['creatinina'],
					 'estado' => $sesionVal['estado'],
					 'usuario' => $usuario,
					 'fecha_ingreso' => $fecha_ingreso
				 ];
			 }
	 
			 $pdomodel->insertBatch("detalle_de_solicitud", $dataToInsert);
	 
			 //unset($_SESSION['detalle_de_solicitud']);
			 $pdomodel->delete("session_data_detalle_de_solicitud");

			// Renderizar los datos actualizados
			$detalle_solicitud = DB::PDOCrud(true);
			$detalle_solicitud->where("usuario_sesion", $_SESSION['usuario'][0]["usuario"]);
			$detalle_solicitud->addCallback("format_table_data", "formatTableDetalleSolicitud");
			$detalle_solicitud->addCallback("before_delete", "eliminar_detalle_solicitud");
			$detalle_solicitud->tableHeading("Detalle de Solicitud");
			$detalle_solicitud->setSettings("addbtn", false);
			$detalle_solicitud->setSettings("editbtn", false);
			$detalle_solicitud->setSettings("viewbtn", false);
			$detalle_solicitud->setSettings("searchbox", false);
			$detalle_solicitud->setSettings("sortable", false);
			$detalle_solicitud->setSettings("showAllSearch", false);
			$detalle_solicitud->setSettings("recordsPerPageDropdown", false);
			$detalle_solicitud->setSettings("deleteMultipleBtn", false);
			$detalle_solicitud->setSettings("checkboxCol", false);
			$detalle_solicitud->setLangData("actions", "Eliminar");
			$detalle_solicitud->setSettings("printBtn", false);
			$detalle_solicitud->setSettings("pdfBtn", false);
			$detalle_solicitud->setSettings("csvBtn", false);
			$detalle_solicitud->setSettings("excelBtn", false);
			$detalle_solicitud->enqueueBtnTopActions("Report", "<i class='fas fa-plus-circle'></i> Agregar Detalle de Solicitud", "javascript:;", array(), "btn-report btn btn-primary agregar_detalle_solicitud");
			$detalle_solicitud->crudTableCol(array("codigo_fonasa", "tipo_solicitud", "tipo_examen", "examen", "contraste", "adjuntar", "plano", "extremidad", "procedencia"));
			
			$render3 = $detalle_solicitud->dbTable("session_data_detalle_de_solicitud")->render();
	
			echo json_encode(['success' => 'Datos Ingresados con éxito', 'render3' => $render3]);
	
		} else {
			echo json_encode(['error' => 'Método de solicitud no válido']);
		}
	}
	
	

	public function ingresar_detalle_solicitud() {
		SessionManager::startSession();
		$request = new Request();
	
		if ($request->getMethod() === 'POST') {
			$codigo_fonasa = $request->post('codigo_fonasa');

			$rut = $request->post('rut');
			
			$paciente = $request->post('paciente');

			$tipo_solicitud = $request->post('tipo_solicitud');
			$fecha_solicitud = $request->post('fecha_solicitud');
			$tipo_examen = $request->post('tipo_examen');
			$examen = $request->post('examen');
			$plano = $request->post('plano');
			$extremidad = $request->post('extremidad');
			$procedencia = $request->post('procedencia');
			$observacion = $request->post('observacion');
			$contraste = $request->post('contraste');
			$contrasteValue = isset($contraste) ? (array)$contraste : [];
			$creatinina = $request->post('creatinina') ?? null;

			$archivoAdjunto = null; // Inicializa la variable

			if (isset($_FILES['adjuntar']) && $_FILES['adjuntar']['error'] === UPLOAD_ERR_OK) {
				$adjuntar = $_FILES['adjuntar'];
				$fileName = basename($adjuntar['name']);
				$fileSize = $adjuntar['size']; // Obtén el tamaño del archivo
				$maxFileSize = 15 * 1024 * 1024;

				if ($fileSize > $maxFileSize) {
					echo json_encode(['error' => 'El archivo excede el tamaño máximo permitido de 15 MB.']);
					return;
				}

				$uploadDir = __DIR__ . '/../libs/script/uploads/';

				// Define un nuevo nombre para el archivo
				$newFileName = time() . '_' . $fileName;
				$tempFilePath = $adjuntar['tmp_name']; // Ruta temporal del archivo subido

				// Verifica que el archivo sea válido
				if ($adjuntar['error'] === UPLOAD_ERR_OK) {
					$archivoAdjunto = 'app/libs/script/uploads/' . $newFileName; // Ruta temporal del archivo subido
				} else if ($adjuntar['error'] !== UPLOAD_ERR_NO_FILE) {
					echo json_encode(['error' => 'Error en el archivo subido.']);
					return;
				}

				if (!file_exists($uploadDir)) {
					mkdir($uploadDir, 0777, true); // Crea el directorio si no existe
				}

				$uploadFile = $uploadDir . $newFileName; // Nueva ruta con el nuevo nombre del archivo

				if ($adjuntar['error'] === UPLOAD_ERR_OK) {
					if (!move_uploaded_file($tempFilePath, $uploadFile)) {
						echo json_encode(['error' => 'Error al mover el archivo subido.']);
						return;
					}

					$uploadDirWeb = 'app/libs/script/uploads/';
					$archivoAdjuntoURL = $_ENV['BASE_URL'] . $uploadDirWeb . $newFileName; // URL con el nuevo nombre
					$archivoAdjunto = $archivoAdjuntoURL;
				} else if ($adjuntar['error'] !== UPLOAD_ERR_NO_FILE) {
					echo json_encode(['error' => 'Error en el archivo subido.']);
					return;
				}
			}

			// Validar que los campos no estén vacíos
			$requiredFields = [
				'tipo_solicitud' => 'Tipo de Solicitud',
				'tipo_examen' => 'Tipo Exámen',
				'examen' => 'Exámen'
				//'observacion' => 'Observación'
			];
	
			foreach ($requiredFields as $fieldName => $fieldLabel) {
				if (empty($$fieldName)) {
					$campo = str_replace('_', ' ', $fieldLabel);
					echo json_encode(['error' => "El campo $campo es obligatorio"]);
					return;
				}
			}
	
			$fecha_formateada = date('Y-m-d', strtotime($fecha_solicitud));
	
			$pdocrud = DB::PDOCrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			
			/*$pdomodel->where("id_datos_paciente", $paciente, "=", "AND");
			$pdomodel->where("examen", $examen, "=", "AND");
			$pdomodel->where("fecha_solicitud", $fecha_formateada);
			$result = $pdomodel->select("detalle_de_solicitud");
	
			if ($result) {
				echo json_encode(['error' => 'El paciente no puede poseer más de una solicitud activa con esta prestación']);
				return;
			}*/

			$session_data_detalle_de_solicitud = $pdomodel->DBQuery(
				"SELECT * FROM session_data_detalle_de_solicitud 
				WHERE id_datos_paciente = ? AND examen = ? AND fecha_solicitud = ?", [$paciente, $examen, $fecha_formateada]
			);

			/*if (!isset($_SESSION['detalle_de_solicitud']) || !is_array($_SESSION['detalle_de_solicitud'])) {
				$_SESSION['detalle_de_solicitud'] = [];
			}*/

			// Validación de paciente para tipo de solicitud y examen específico
			/*$duplicateSolicitud = false;
			foreach ($_SESSION['detalle_de_solicitud'] as $detalle) {
				if ($detalle['id_datos_paciente'] == $paciente && $detalle['examen'] == $examen && $detalle['fecha_solicitud'] == $fecha_formateada) {
					$duplicateSolicitud = true;
					break;
				}
			}*/

			/*$duplicateSolicitud = false;
			if (!empty($session_data_detalle_de_solicitud)) {
				$duplicateSolicitud = true;
			}
	
			if ($duplicateSolicitud) {
				echo json_encode(['error' => 'El paciente no puede poseer más de una solicitud activa con esta prestación']);
				return;
			} else {*/
				$detalle_de_solicitud = [
					"usuario_sesion" => $_SESSION['usuario'][0]["usuario"],
					"codigo_fonasa" => $codigo_fonasa,
					"id_datos_paciente" => $paciente,
					"tipo_solicitud" => $tipo_solicitud,
					"fecha_solicitud" => $fecha_solicitud,
					"tipo_examen" => $tipo_examen,
					"examen" => $examen,
					"plano" => $plano,
					"extremidad" => $extremidad,
					"procedencia" => $procedencia,
					"observacion" => $observacion,
					"contraste" => implode(", ", $contrasteValue),
					"adjuntar" => $archivoAdjunto,  // Ajustado a adjuntar_name
					"creatinina" => $creatinina,
					"estado" => "Ingresado"
				];

				$pdomodel->insert("session_data_detalle_de_solicitud", $detalle_de_solicitud);

				$detalle_solicitud = DB::PDOCrud(true);
				$detalle_solicitud->addCallback("before_delete", "eliminar_detalle_solicitud");
				$detalle_solicitud->addCallback("format_table_data", "formatTableDetalleSolicitud");  
				$detalle_solicitud->tableHeading("Detalle de Solicitud");
				$detalle_solicitud->setSettings("addbtn", false);
				$detalle_solicitud->setSettings("editbtn", false);
				$detalle_solicitud->setSettings("viewbtn", false);
				$detalle_solicitud->setSettings("searchbox", false);
				$detalle_solicitud->setSettings("sortable", false);
				$detalle_solicitud->setSettings("showAllSearch", false);
				$detalle_solicitud->setSettings("recordsPerPageDropdown", false);
				$detalle_solicitud->setSettings("deleteMultipleBtn", false);
				$detalle_solicitud->setSettings("checkboxCol", false);
				$detalle_solicitud->setLangData("actions", "Eliminar");
				$detalle_solicitud->setSettings("printBtn", false);
				$detalle_solicitud->setSettings("pdfBtn", false);
				$detalle_solicitud->setSettings("csvBtn", false);
				$detalle_solicitud->setSettings("excelBtn", false);
				$detalle_solicitud->where("usuario_sesion", $_SESSION['usuario'][0]["usuario"]);
				$detalle_solicitud->enqueueBtnTopActions("Report", "<i class='fas fa-plus-circle'></i> Agregar Detalle de Solicitud", "javascript:;", array(), "btn-report btn btn-primary agregar_detalle_solicitud");
				$detalle_solicitud->crudTableCol(array("codigo_fonasa", "tipo_solicitud", "tipo_examen", "examen", "contraste", "adjuntar", "plano", "extremidad", "procedencia"));
				$render3 = $detalle_solicitud->dbTable("session_data_detalle_de_solicitud")->render();

				// Agregar la solicitud a la sesión
				//$_SESSION['detalle_de_solicitud'][] = $detalle_de_solicitud;
				
				$response = [
					'success' => 'Datos Guardados con éxito Temporalmente',
					'rut' => $rut,
					'data' => $detalle_de_solicitud,
					'render3' => $render3
				];
		
				echo json_encode($response);
			//}
		}
	}	
	
	public function eliminar_dato_detalle_solicitud(){
		SessionManager::startSession();

		$request = new Request();

		if ($request->getMethod() === 'POST') {
			$codigo_fonasa = $request->post('codigo_fonasa');

			foreach ($_SESSION['detalle_de_solicitud'] as $key => $detalle) {
				if ($detalle['codigo_fonasa'] == $codigo_fonasa) {
					// Eliminar el archivo asociado si existe
					if (isset($detalle['adjuntar']) && !empty($detalle['adjuntar'])) {
						$archivoURL = $detalle['adjuntar'];
						
						$uploadDir = __DIR__ . '/../libs/script/uploads/';
                    	
						 // Extraer el nombre del archivo de la URL
						$archivoNombre = basename($archivoURL);

					   	// Crear la ruta del archivo en el servidor
						$archivoPath = $uploadDir . $archivoNombre;
						
						// Asegúrate de que la ruta sea segura
						if (file_exists($archivoPath)) {
							unlink($archivoPath); // Elimina el archivo del servidor
						} else {
							echo json_encode(['error' => 'El archivo no existe.']);
							return;
						}
					}
					
					unset($_SESSION['detalle_de_solicitud'][$key]);
				}
			}

			$_SESSION['detalle_de_solicitud'] = array_values($_SESSION['detalle_de_solicitud']);

			echo json_encode([
				'success' => 'Dato Eliminado con éxito', 
				'data' => $_SESSION['detalle_de_solicitud']
			]);
		}
	}

	public function buscar_datos_pacientes(){

		$request = new Request();

		if ($request->getMethod() === 'POST') {

			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();

			$searchFields = ['rut', 'pasaporte_o_codigo_interno', 'nombres', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'edad', 'direccion', 'sexo'];
			$searchCriteria = [];

			// Check if any search parameter is provided
			foreach ($searchFields as $field) {
				if (!empty($request->post($field))) {
					$searchCriteria[$field] = $request->post($field);
				}
			}

			if (empty($searchCriteria)) {
				echo json_encode(['error' => 'Debe proporcionar al menos un dato para buscar']);
			} else {
				 // Validar el RUT
				 $rut = $request->post('rut');
				 if (!self::validaRut($rut)) {
					 echo json_encode(['error' => 'RUT inválido']);
					 return;
				 } 
 
				// Apply WHERE conditions based on the provided search criteria
				foreach ($searchCriteria as $field => $value) {
					$pdomodel->where($field, $value);
				}

				// Perform SELECT operation on the 'datos_paciente' table
				$data = $pdomodel->select("datos_paciente");

				// Check if data retrieval was successful
				if ($data) {
					echo json_encode(['success' => 'Datos cargados con éxito', 'data' => $data]);
				} else {
					echo json_encode(['error' => 'No se encontraron resultados para los datos buscados']);
				}
			}
		}
	}

	public static function menuDB(){
		$pdocrud = DB::PDOCrud();
		$pdomodel = $pdocrud->getPDOModelObj();
		$pdomodel->orderBy(array("orden_menu asc"));
		$data = $pdomodel->select("menu");
		return $data;
	}

	public static function submenuDB($idMenu){
		$pdocrud = DB::PDOCrud();
		$pdomodel = $pdocrud->getPDOModelObj();
		$pdomodel->where("id_menu", $idMenu, "=");
		$pdomodel->orderBy(array("orden_submenu asc")); // Ajusta el nombre de la columna de ordenación si es diferente
		$data = $pdomodel->select("submenu");
		return $data;
	}	

	public function modulos()
	{
		$pdocrud = DB::PDOCrud();
		$pdocrud->tableHeading("Generador de Módulos");
		$pdocrud->setSearchCols(array("tabla", "activar_filtro_de_busqueda", "botones_de_exportacion", "seleccionar_skin", "seleccionar_template"));
		$pdocrud->crudRemoveCol(array("id_modulos"));
		$pdocrud->fieldDesc("nombre_funcion_antes_de_insertar", "Campo opcional");
		$pdocrud->fieldDesc("nombre_funcion_despues_de_insertar", "Campo opcional");
		$pdocrud->fieldDesc("nombre_funcion_antes_de_actualizar", "Campo opcional");
		$pdocrud->fieldDesc("nombre_funcion_despues_de_actualizar", "Campo opcional");
		$pdocrud->fieldDesc("nombre_funcion_antes_de_eliminar", "Campo opcional");
		$pdocrud->fieldDesc("nombre_funcion_despues_de_eliminar", "Campo opcional");
		$pdocrud->fieldDesc("nombre_funcion_antes_de_actualizar_gatillo", "Campo opcional");
		$pdocrud->fieldDesc("nombre_funcion_despues_de_actualizar_gatillo", "Campo opcional");

		$pdocrud->fieldNotMandatory("nombre_funcion_antes_de_insertar");
		$pdocrud->fieldNotMandatory("nombre_funcion_despues_de_insertar");
		$pdocrud->fieldNotMandatory("nombre_funcion_antes_de_actualizar");
		$pdocrud->fieldNotMandatory("nombre_funcion_despues_de_actualizar");
		$pdocrud->fieldNotMandatory("nombre_funcion_antes_de_eliminar");
		$pdocrud->fieldNotMandatory("nombre_funcion_despues_de_eliminar");
		$pdocrud->fieldNotMandatory("nombre_funcion_antes_de_actualizar_gatillo");
		$pdocrud->fieldNotMandatory("nombre_funcion_despues_de_actualizar_gatillo");

		$pdocrud->fieldGroups("Name", array("seleccionar_skin", "seleccionar_template"));
		$pdocrud->fieldGroups("filtr", array("activar_filtro_de_busqueda", "botones_de_accion", "activar_buscador"));
		$pdocrud->fieldGroups("Name1", array("botones_de_exportacion", "activar_eliminacion_multiple", "activar_modo_popup"));
		$pdocrud->fieldGroups("Name2", array("nombre_funcion_antes_de_insertar", "nombre_funcion_despues_de_insertar"));
		$pdocrud->fieldGroups("Name3", array("nombre_funcion_antes_de_actualizar", "nombre_funcion_despues_de_actualizar"));
		$pdocrud->fieldGroups("Name4", array("nombre_funcion_antes_de_eliminar", "nombre_funcion_despues_de_eliminar"));
		$pdocrud->fieldGroups("Name5", array("nombre_funcion_antes_de_actualizar_gatillo", "nombre_funcion_despues_de_actualizar_gatillo"));
		$pdocrud->fieldTypes("activar_eliminacion_multiple", "radio");
		$pdocrud->fieldDataBinding("activar_eliminacion_multiple", array("si" => "si", "no" => "no"), "", "", "array");
		$pdocrud->fieldTypes("activar_modo_popup", "radio");
		$pdocrud->fieldDataBinding("activar_modo_popup", array("si" => "si", "no" => "no"), "", "", "array");
		$pdocrud->fieldTypes("nulo", "select");
		$pdocrud->fieldDataBinding("nulo", array("si" => "si", "NOT NULL" => "no"), "", "", "array");
		$pdocrud->fieldTypes("activar_buscador", "radio");
		$pdocrud->fieldDataBinding("activar_buscador", array("si" => "si", "no" => "no"), "", "", "array");
		$pdocrud->fieldCssClass("activar_filtro_de_busqueda", array("data_activar_filtro_de_busqueda"));
		$pdocrud->fieldTypes("seleccionar_skin", "select");
		$pdocrud->fieldDataBinding("seleccionar_skin", array("green" => "green", "pure" => "pure", "advance" => "advance", "fair" => "fair", "default" => "default", "hover" => "hover", "dark" => "dark"), "", "", "array");
		$pdocrud->fieldTypes("visibilidad_formulario", "select");
		$pdocrud->fieldTypes("seleccionar_template", "select");
		$pdocrud->fieldDataBinding("seleccionar_template", array("bootstrap" => "bootstrap", "bootstrap4" => "bootstrap4", "pure" => "pure", "simple" => "simple", "Personalizado" => "Personalizado"), "", "", "array");
		$pdocrud->fieldTypes("visibilidad_formulario", "select");
		$pdocrud->fieldDataBinding("visibilidad_formulario", array("Mostrar" => "Mostrar", "Ocultar" => "Ocultar"), "", "", "array");

		$pdocrud->fieldTypes("visibilidad_busqueda", "select");
		$pdocrud->fieldDataBinding("visibilidad_busqueda", array("Mostrar" => "Mostrar", "Ocultar" => "Ocultar"), "", "", "array");

		$pdocrud->fieldTypes("tipo_de_campo", "select");
		$pdocrud->fieldDataBinding("tipo_de_campo", array("Imagen" => "Imagen", "select" => "select", "Input" => "Input", "summernote" => "summernote", "ckeditor" => "ckeditor"), "", "", "array");


		$pdocrud->fieldTypes("visibilidad_grilla", "select");
		$pdocrud->fieldDataBinding("visibilidad_grilla", array("Mostrar" => "Mostrar", "Ocultar" => "Ocultar"), "", "", "array");

		$pdocrud->fieldTypes("visibilidad_de_filtro_busqueda", "select");
		$pdocrud->fieldDataBinding("visibilidad_de_filtro_busqueda", array("Mostrar" => "Mostrar", "Ocultar" => "Ocultar"), "", "", "array");

		$pdocrud->fieldTypes("botones_de_exportacion", "checkbox");
		$pdocrud->fieldDataBinding("botones_de_exportacion", array("imprimir" => "imprimir", "csv" => "csv", "pdf" => "pdf", "excel" => "excel"), "", "", "array");

		$pdocrud->fieldTypes("botones_de_accion", "checkbox");
		$pdocrud->fieldDataBinding("botones_de_accion", array("Agregar" => "Agregar", "Ver" => "Ver", "Editar" => "Editar", "Eliminar" => "Eliminar", "Guardar" => "Guardar", "Guardar y regresar" => "Guardar y regresar", "Regresar" => "Regresar", "Cancelar" => "Cancelar"), "", "", "array");

		$pdocrud->setLangData("no_data", "No hay Módulos creados");
		$pdocrud->fieldTypes("activar_filtro_de_busqueda", "select");
		$pdocrud->fieldDataBinding("activar_filtro_de_busqueda", array("AUTO_INCREMENT" => "si", "no" => "no"), "", "", "array");
		$pdocrud->buttonHide("submitBtnSaveBack");
		$pdocrud->fieldTypes("autoincrementable", "select");
		$pdocrud->fieldDataBinding("autoincrementable", array("AUTO_INCREMENT" => "si", "no" => "no"), "", "", "array");
		$pdocrud->fieldTypes("indice", "select");
		$pdocrud->fieldDataBinding("indice", array("PRIMARY KEY" => " PRIMARY"), "", "", "array");
		$pdocrud->fieldNotMandatory("longitud");
		$pdocrud->fieldNotMandatory("indice");
		$pdocrud->fieldNotMandatory("autoincrementable");
		$pdocrud->fieldNotMandatory("botones_de_exportacion");
		$pdocrud->fieldNotMandatory("botones_de_accion");
		$pdocrud->fieldNotMandatory("script_js");
		$pdocrud->fieldTypes("tipo", "select");
		$pdocrud->addCallback("before_insert", "insertar_modulos");
		$pdocrud->addCallback("before_update", "actualizar_modulo");
		$pdocrud->addCallback("before_delete", "eliminar_modulo");
		$pdocrud->joinTable("campos", "campos.id_modulos = modulos.id_modulos", "LEFT JOIN");
		$pdocrud->fieldDataBinding("tipo", array("INT" => "Número", "VARCHAR" => "Caracteres", "TEXT" => "Contenido", "DATE" => "Fecha"), "", "", "array");
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->fieldRenameLable("nombre", "Nombre campo");
		$pdocrud->fieldRenameLable("tabla", "Nombre Tabla Base de Datos");

		$action = $_ENV["BASE_URL"] . "home/pagina/modulo/{pk}";
		$text = '<i class="fa fa-table" aria-hidden="true"></i>';
		$attr = array("title" => "Ver módulo", "target"=> "_blank");
		$pdocrud->enqueueBtnActions("url btn btn-default btn-sm ", $action, "url", $text, "booking_status", $attr);

		$render = $pdocrud->dbTable("modulos")->render();

		View::render(
			"modulos",
			['render' => $render]
		);
	}

	public function actualizar_orden_menu(){

		$request = new Request();

		if ($request->getMethod() === 'POST') {

			$order = $request->post('order');
			if (isset($order) && is_array($order)) {
				$newOrder = $order;

				foreach ($newOrder as $position => $itemId) {
					$position++;
					$pdocrud = DB::PDOCrud();
					$pdomodel = $pdocrud->getPDOModelObj();
					$pdomodel->where("id_menu", $itemId);
					$pdomodel->update("menu", array("orden_menu" => $position));
				}

				echo json_encode(['success' => 'Orden del menu actualizado correctamente']);
			}
		}
	}

	public function actualizar_orden_submenu(){

		$request = new Request();

		if ($request->getMethod() === 'POST') {

			$order = $request->post('order');
			if (isset($order) && is_array($order)) {
				$newOrder = $order;

				foreach ($newOrder as $position => $itemId) {
					$position++;
					$pdocrud = DB::PDOCrud();
					$pdomodel = $pdocrud->getPDOModelObj();
					$pdomodel->where("id_submenu", $itemId);
					$pdomodel->update("submenu", array("orden_submenu" => $position));
				}

				echo json_encode(['success' => 'Orden del submenu actualizado correctamente']);
			}
		}
	}

	public function editar_iconos_menu(){

		$request = new Request();

		if ($request->getMethod() === 'POST') {
			$id = $request->post('id');

			$pdocrud = DB::PDOcrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->columns = array("icono_menu");
			$pdomodel->where("id_menu", $id);
			$data = $pdomodel->select("menu");

			$ruta_json = "http://" . $_SERVER['HTTP_HOST'] .$_ENV["BASE_URL"] . "js/icons.json";

			// Lee el contenido del archivo JSON
			$contenido_json = file_get_contents($ruta_json);

			// Decodifica el contenido JSON a un array de PHP
			$icons = json_decode($contenido_json, true);

        	echo json_encode(['data' => $data, 'icons' => $icons], JSON_UNESCAPED_UNICODE);
		}
	}

	public function editar_iconos_submenu(){

		$request = new Request();

		if ($request->getMethod() === 'POST') {
			$id = $request->post('id');

			$pdocrud = DB::PDOcrud();
			$pdomodel = $pdocrud->getPDOModelObj();
			$pdomodel->columns = array("icono_submenu");
			$pdomodel->where("id_submenu", $id);
			$data = $pdomodel->select("submenu");

			$ruta_json = "http://" . $_SERVER['HTTP_HOST'] .$_ENV["BASE_URL"] . "js/icons.json";

			// Lee el contenido del archivo JSON
			$contenido_json = file_get_contents($ruta_json);

			// Decodifica el contenido JSON a un array de PHP
			$icons = json_decode($contenido_json, true);

        	echo json_encode(['data' => $data, 'icons' => $icons], JSON_UNESCAPED_UNICODE);
		}
	}

	private function crud_ingreso_egreso(){
		$pdocrud = DB::PDOCrud(true);
		$pdomodel = $pdocrud->getPDOModelObj();
		$pdomodel->columns = array(
			"count(ds.examen) AS total_examen",
			"ds.examen",
			"ds.codigo_fonasa",
			"ds.tipo_examen",
			"ds.fecha_solicitud",
			"dg_p.diagnostico",
			"dp.nombres",
			"ds.procedencia",
			"dp.apellido_paterno",
			"dp.apellido_materno",
			"ds.estado",
			"dp.rut",
			"dp.fecha_y_hora_ingreso",
			"ds.fecha"
		);

		$pdomodel->joinTables("detalle_de_solicitud as ds", "ds.id_datos_paciente = dp.id_datos_paciente", "INNER JOIN");
		$pdomodel->joinTables("diagnostico_antecedentes_paciente as dg_p", "dg_p.id_datos_paciente = dp.id_datos_paciente", "INNER JOIN");

		$pdomodel->groupByCols = array("dp.nombres", "dp.rut", "ds.fecha");
		$pdomodel->where("ds.estado", "Agendado", "!=");
		$pdomodel->andOrOperator = "AND";
		$pdomodel->where("ds.procedencia", "Ambulatorio");
		$data = $pdomodel->select("datos_paciente as dp");

		$html = '
			<table class="table table-striped tabla_reportes text-center" style="width:100%">
				<thead class="bg-primary">
					<tr>
						<th>Código Fonasa</th>
						<th>Procedencia</th>
						<th>Exámen</th>
						<th>Estado</th>
						<th>Tipo de Exámen</th>
						<th>Año</th>
						<th>Total Exámenes</th>
					</tr>
				</thead>
				<tbody>
		';
	
		foreach ($data as $row) {
			$nombre_completo = $row["nombres"] . ' ' . $row["apellido_paterno"] . ' ' . $row["apellido_materno"];
			$ano = date('Y', strtotime($row["fecha"]));

			$year = "";
			if($ano != "1970"){
				$year = $ano;
			} else {
				$year = "Sin Año";
			}

			$html .= '
				<tr>
					<td>' . $row['codigo_fonasa'] . '</td>
					<td>' . $row['procedencia'] . '</td>
					<td>' . $row["examen"] . '</td>
					<td>' . $row["estado"] . '</td>
					<td>' . $row["tipo_examen"] . '</td>
					<td>' . $year . '</td>
					<td>' . $row["total_examen"] . '</td>
				</tr>
			';
		}
	
		$html .= '
				</tbody>
			</table>
		';
	
		$html_data = array($html);
	
		$render = $pdocrud->render("HTML", $html_data);
		return $render;
	}

	public function ingreso_egreso(){

		$pdocrud = DB::PDOCrud();
		$pdocrud->addPlugin("bootstrap-inputmask");
		$pdomodel = $pdocrud->getPDOModelObj();
		$estado = $pdomodel->select("estado_procedimiento");
		
		$opt = '';
		foreach ($estado as $estados) {
			if($estados["nombre"] != "Agendado"){
				$opt .= '<option value="' . $estados["nombre"] . '">' . $estados["nombre"] . '</option>';
			}
		}

		$html_data = array('
			<form action="#" method="POST" class="form_search">
			<div class="row">
				<div class="col-md-6">
					<label for="correo">Rut</label>
					<input class="form-control rut" type="text" name="rut" id="rut"  placeholder="">
				</div>
				<div class="col-md-6">
					<label for="fecha">Estado</label>
					<select class="form-control estado" type="text" name="estado" id="estado">
						<option value="0">Seleccionar</option>
						'.$opt.'
					</select>
				</div>
			</div>
			<div class="row mt-3 mb-4">
				<div class="col-md-12">
					<a href="javascript:;" class="btn btn-primary btn_search"><i class="fa fa-search"></i> Buscar</a>
					<a href="javascript:;" class="btn btn-danger btn_limpiar d-none"><i class="fas fa-eraser"></i> Limpiar</a>
				</div>
			</div>	
		</form>
		');
		$render = $pdocrud->render("HTML", $html_data);
		$mask = $pdocrud->loadPluginJsCode("bootstrap-inputmask",".rut", array("mask"=> "'9{1,2}9{3}9{3}-(9|k|K)'", "casing" => "'upper'", "clearIncomplete" => "true", "numericInput"=> "true", "positionCaretOnClick" => "'none'"));

		$grilla_ingreso_egreso = $this->crud_ingreso_egreso();

		View::render(
			"reportes_ingreso_egreso",[
				'render' => $render,
				'mask' => $mask,
				'grilla_ingreso_egreso' => $grilla_ingreso_egreso
			]
		);
	}

	public function menu(){
		$pdocrud = DB::PDOCrud();

		$pdomodel = $pdocrud->getPDOModelObj();
		$datamenu = $pdomodel->DBQuery("SELECT MAX(orden_menu) as orden FROM menu");
		$newOrdenMenu = $datamenu[0]["orden"] + 1;

		$datasubmenu = $pdomodel->DBQuery("SELECT MAX(orden_submenu) as orden_submenu FROM submenu");
		$newOrdenSubMenu = $datasubmenu[0]["orden_submenu"] + 1;

		$action = "javascript:;";
		$text = '<i class="fas fa-arrows-alt-v"></i>';
		$attr = array("title"=>"Arrastra para Reordenar Fila");
		$pdocrud->enqueueBtnActions("url btn btn-primary btn-sm reordenar_fila", $action, "url",$text,"orden_menu", $attr);
		$pdocrud->multiTableRelationDisplay("tab", "Menu");
		$pdocrud->setSearchCols(array("nombre_menu","url_menu", "icono_menu", "submenu", "orden_menu"));
		$pdocrud->fieldHideLable("orden_menu");
		$pdocrud->fieldDataAttr("orden_menu", array("style"=>"display:none"));
		$pdocrud->fieldHideLable("submenu");
		$pdocrud->fieldDataAttr("submenu", array("style"=>"display:none"));
		$pdocrud->formFieldValue("orden_menu", $newOrdenMenu);
		$pdocrud->formFieldValue("submenu", "No");
		$pdocrud->addPlugin("select2");
		$pdocrud->dbOrderBy("orden_menu asc");
		$pdocrud->addCallback("format_table_data", "formatTableMenu");
		$pdocrud->addCallback("after_insert", "agregar_menu");
		$pdocrud->addCallback("before_delete", "eliminar_menu");
		$pdocrud->fieldTypes("icono_menu", "select");
		$pdocrud->fieldCssClass("icono_menu", array("icono_menu"));
		$pdocrud->fieldCssClass("submenu", array("submenu"));
		$pdocrud->fieldGroups("Name", array("nombre_menu", "url_menu"));
		$pdocrud->crudRemoveCol(array("id_menu"));
		$pdocrud->setSettings("printBtn", false);
		$pdocrud->setSettings("pdfBtn", false);
		$pdocrud->setSettings("csvBtn", false);
		$pdocrud->setSettings("excelBtn", false);
		$pdocrud->setSettings("viewbtn", false);
		$pdocrud->buttonHide("submitBtnSaveBack");

		$submenu = DB::PDOCrud(true);
		$submenu->multiTableRelationDisplay("tab", "SubMenu");
		$action = "javascript:;";
		$text = '<i class="fas fa-arrows-alt-v"></i>';
		$attr = array("title"=>"Arrastra para Reordenar Fila");
		$submenu->enqueueBtnActions("url btn btn-primary btn-sm reordenar_fila_submenu", $action, "url",$text,"orden_submenu", $attr);
		$submenu->fieldHideLable("orden_submenu");
		$submenu->fieldDataAttr("orden_submenu", array("style"=>"display:none"));
		$submenu->fieldHideLable("id_menu");
		$submenu->fieldDataAttr("id_menu", array("style"=>"display:none"));
		$submenu->setSearchCols(array("nombre_submenu","url_submenu", "icono_submenu", "orden_submenu"));
		$submenu->crudTableCol(array("nombre_submenu","url_submenu", "icono_submenu", "orden_submenu"));
		$submenu->formFields(array("id_menu","nombre_submenu","url_submenu", "icono_submenu", "orden_submenu"));
		$submenu->dbTable("submenu");
		$submenu->dbOrderBy("orden_submenu asc");
		$submenu->addCallback("format_table_data", "formatTableSubMenu");
		$submenu->addCallback("before_insert", "insertar_submenu");
		$submenu->addCallback("after_insert", "despues_insertar_submenu");
		$submenu->addCallback("before_update", "modificar_submenu");
		$submenu->addCallback("before_delete", "eliminar_submenu");
		$submenu->fieldGroups("Name", array("nombre_submenu", "url_submenu"));
		$submenu->formFieldValue("orden_submenu", $newOrdenSubMenu);
		$submenu->setSettings("template", "submenu");
		$submenu->setSettings("printBtn", false);
		$submenu->setSettings("pdfBtn", false);
		$submenu->setSettings("csvBtn", false);
		$submenu->setSettings("excelBtn", false);
		$submenu->setSettings("viewbtn", false);
		$submenu->fieldTypes("icono_submenu", "select");
		$submenu->fieldCssClass("icono_submenu", array("icono_submenu"));
		$submenu->buttonHide("submitBtnSaveBack");
		$pdocrud->multiTableRelation("id_menu", "id_menu", $submenu);
		$select2 = $pdocrud->loadPluginJsCode("select2",".icono_menu, .icono_submenu");
		$render = $pdocrud->dbTable("menu")->render();

		View::render(
			"menu",
				[
					'render' => $render,
					'select2' => $select2
				]
		);
	}

	public function pagina()
	{
			$request = new Request();
			$id = $request->get('modulo');
			
			if (is_numeric($id)) {
				Redirect::to("Home/modulos");
			}

			$page = new PageModel();
			$id_page = $page->PageById($id);

			if (!isset($id_page)) {
				Redirect::to("Home/modulos");
			}

			$tabla = $id_page['modulos']['tabla'];
			$activar_filtro_de_busqueda	= $id_page['modulos']['activar_filtro_de_busqueda'];
			$botones_de_accion = explode(',', $id_page['modulos']['botones_de_accion']);
			$activar_buscador = $id_page['modulos']['activar_buscador'];
			$botones_de_exportacion = explode(',', $id_page['modulos']['botones_de_exportacion']);
			$activar_eliminacion_multiple = $id_page['modulos']['activar_eliminacion_multiple'];
			$activar_modo_popup = $id_page['modulos']['activar_modo_popup'];
			$seleccionar_skin = $id_page['modulos']['seleccionar_skin'];
			$activar_eliminacion_multiple = $id_page['modulos']['activar_eliminacion_multiple'];
			$template = $id_page['modulos']['seleccionar_template'];
			$campos = $id_page['campos'];

			/* devoluciones de llamada */
			$nombre_funcion_antes_de_insertar = $id_page['modulos']['nombre_funcion_antes_de_insertar'];
			$nombre_funcion_despues_de_insertar = $id_page['modulos']['nombre_funcion_despues_de_insertar'];
			$nombre_funcion_antes_de_actualizar = $id_page['modulos']['nombre_funcion_antes_de_actualizar'];
			$nombre_funcion_despues_de_actualizar = $id_page['modulos']['nombre_funcion_despues_de_actualizar'];
			$nombre_funcion_antes_de_eliminar = $id_page['modulos']['nombre_funcion_antes_de_eliminar'];
			$nombre_funcion_despues_de_eliminar = $id_page['modulos']['nombre_funcion_despues_de_eliminar'];
			$nombre_funcion_antes_de_actualizar_gatillo = $id_page['modulos']['nombre_funcion_antes_de_actualizar_gatillo'];
			$nombre_funcion_despues_de_actualizar_gatillo = $id_page['modulos']['nombre_funcion_despues_de_actualizar_gatillo'];
			$script_js = $id_page['modulos']['script_js'];

			$pdocrud = DB::PDOCrud();
			if ($activar_filtro_de_busqueda != "no") {
				for ($i = 0; $i < count($campos); $i++) {
					if($campos[$i]["visibilidad_de_filtro_busqueda"] != "Ocultar"){
						$pdocrud->addFilter("filter_" . $campos[$i]["nombre"], "Filtrar por " . $campos[$i]["nombre"], $campos[$i]["nombre"], "dropdown");
						$pdocrud->setFilterSource("filter_" . $campos[$i]["nombre"], $tabla, $campos[$i]['nombre'], $campos[$i]['nombre'] . " as pl", "db");
					}
				}
			}

			$arr_search = [];
			foreach ($campos as $val) {
				if ($val["visibilidad_busqueda"] != "Ocultar") {
					$arr_search[$val["nombre"]] = $val["nombre"];
				}
			}

			$pdocrud->setSearchCols($arr_search);

			$arr_hide = [];
			foreach ($campos as $val) {
				if ($val["visibilidad_grilla"] == "Ocultar") {
					$arr_hide[$val["nombre"]] = $val["nombre"];
				}
			}

			$pdocrud->crudRemoveCol($arr_hide);

			foreach ($campos as $val) {
				if ($val["tipo_de_campo"] == "Imagen") {
					$arr_tipo = $val["nombre"];
				}
				if ($val["tipo_de_campo"] == "summernote") {
					$arr_summer = $val["nombre"];
				}
				if ($val["tipo_de_campo"] == "ckeditor") {
					$arr_ckeditor = $val["nombre"];
				}
				if ($val["tipo_de_campo"] == "Input") {
					$arr_input = $val["nombre"];
				}
			}

			if(isset($arr_tipo)){
				$pdocrud->fieldTypes($arr_tipo, "FILE_NEW");
				$pdocrud->viewColFormatting($arr_tipo, "html", array("type" => "html", "str" => "<img src='" . $_ENV["BASE_URL"] . "{col-name}' width='60' />"));
				$pdocrud->tableColFormatting($arr_tipo, "html", array("type" => "html", "str" => "<img src='" . $_ENV["BASE_URL"] . "{col-name}' width='60' />"));
				$pdocrud->fieldTypes($arr_input, "input");
			}
			
			if ($val["tipo_de_campo"] == "summernote") {
				$pdocrud->addPlugin("summernote");
				$pdocrud->fieldCssClass($arr_summer, array("summernote"));
			}

			if ($val["tipo_de_campo"] == "ckeditor") {
				$pdocrud->addPlugin("ckeditor");
				$pdocrud->fieldCssClass($arr_ckeditor, array("ckeditor"));
			}

			$arr = [];
			foreach ($campos as $form) {
				if ($form["visibilidad_formulario"] == "Mostrar") {
					$arr[$form["nombre"]] = $form["nombre"];
				}
			}

			$pdocrud->formFields($arr);

			$pdocrud->setViewColumns($arr);

			if (isset($nombre_funcion_antes_de_insertar)) {
				$pdocrud->addCallback("before_insert", $nombre_funcion_antes_de_insertar);
			}

			if (isset($nombre_funcion_despues_de_insertar)) {
				$pdocrud->addCallback("after_insert", $nombre_funcion_despues_de_insertar);
			}

			if (isset($nombre_funcion_antes_de_actualizar)) {
				$pdocrud->addCallback("before_update", $nombre_funcion_antes_de_actualizar);
			}

			if (isset($nombre_funcion_despues_de_actualizar)) {
				$pdocrud->addCallback("after_update", $nombre_funcion_despues_de_actualizar);
			}

			if (isset($nombre_funcion_antes_de_eliminar)) {
				$pdocrud->addCallback("before_delete", $nombre_funcion_antes_de_eliminar);
			}

			if (isset($nombre_funcion_despues_de_eliminar)) {
				$pdocrud->addCallback("after_delete", $nombre_funcion_despues_de_eliminar);
			}

			if(isset($nombre_funcion_antes_de_actualizar_gatillo)){
				$pdocrud->addCallback("before_switch_update", $nombre_funcion_antes_de_actualizar_gatillo);
			}

			if(isset($nombre_funcion_despues_de_actualizar_gatillo)){
				$pdocrud->addCallback("after_switch_update", $nombre_funcion_despues_de_actualizar_gatillo);
			}

			if ($activar_buscador != "si") {
				$pdocrud->setSettings("searchbox", false);
			}

			if ($activar_eliminacion_multiple == "no") {
				$pdocrud->setSettings("deleteMultipleBtn", false);
				$pdocrud->setSettings("checkboxCol", false);
			}

			if ($activar_modo_popup == "si") {
				$pdocrud->formDisplayInPopup();
			}
			$pdocrud->setSkin($seleccionar_skin);
			$pdocrud->setSettings("template", $template);

			/* botones de exportacion */
			if (!in_array("imprimir", $botones_de_exportacion)) {
				$pdocrud->setSettings("printBtn", false);
			}

			if (!in_array("csv", $botones_de_exportacion)) {
				$pdocrud->setSettings("csvBtn", false);
			}

			if (!in_array("pdf", $botones_de_exportacion)) {
				$pdocrud->setSettings("pdfBtn", false);
			}

			if (!in_array("excel", $botones_de_exportacion)) {
				$pdocrud->setSettings("excelBtn", false);
			}

			/* botones de accion */
			if (!in_array("Agregar", $botones_de_accion)) {
				$pdocrud->setSettings("addbtn", false);
			}

			if (!in_array("Ver", $botones_de_accion)) {
				$pdocrud->setSettings("viewbtn", false);
			}

			if (!in_array("Editar", $botones_de_accion)) {
				$pdocrud->setSettings("editbtn", false);
			}

			if (!in_array("Eliminar", $botones_de_accion)) {
				$pdocrud->setSettings("delbtn", false);
			}

			if (!in_array("Guardar", $botones_de_accion)) {
				$pdocrud->buttonHide("submitBtn");
			}

			if (!in_array("Guardar y regresar", $botones_de_accion)) {
				$pdocrud->buttonHide("submitBtnSaveBack");
			}

			if (!in_array("Regresar", $botones_de_accion)) {
				$pdocrud->buttonHide("submitBtnBack");
			}

			if (!in_array("Cancelar", $botones_de_accion)) {
				$pdocrud->buttonHide("cancel");
			}

			$render = $pdocrud->dbTable($tabla)->render();

			$tipo_campo = $val["tipo_de_campo"];

			if ($tipo_campo == "summernote") {
				$loadPluginJsCode =  $pdocrud->loadPluginJsCode("summernote", ".summernote");
			}
			
			if ($tipo_campo == "ckeditor") {
				$loadPluginJsCode = $pdocrud->loadPluginJsCode("ckeditor","ZGVtbzQjJGNvbnRlbmlkb0AzZHNmc2RmKio5OTM0MzI0");
			}
		

		View::render(
			"page",
			[
				'id_page' => $id_page,
				'render' => $render,
				'loadPluginJsCode' => $loadPluginJsCode,
				'script_js' => $script_js
			]
		);
	}

	public function perfil()
	{
		$id = $_SESSION['usuario'][0]["id"];
        $token = $this->token;
		$pdocrud = DB::PDOCrud();
		$pdocrud->fieldHideLable("id");
		$pdocrud->fieldCssClass("id", array("d-none"));
		$pdocrud->setSettings("hideAutoIncrement", false);
		$pdocrud->setSettings("required", false);
		$pdocrud->addCallback("before_update", "editar_perfil");
		$pdocrud->fieldGroups("Name",array("nombre","email"));
		$pdocrud->fieldGroups("Name2",array("usuario","password"));
		$pdocrud->fieldGroups("Name3",array("idrol","avatar"));
		$pdocrud->fieldTypes("avatar", "FILE_NEW");
		$pdocrud->fieldTypes("password", "password");
		$pdocrud->fieldRenameLable("nombre", "Nombre Completo");
		$pdocrud->fieldRenameLable("email", "Correo electrónico");
		$pdocrud->fieldRenameLable("password", "Clave de acceso");
		$pdocrud->fieldRenameLable("idrol", "Tipo Usuario");
		$pdocrud->relatedData('idrol','rol','idrol','nombre_rol');
		$pdocrud->formFields(array("id","nombre","email","password","usuario", "idrol", "avatar"));
        $pdocrud->formStaticFields("token_form", "html", "<input type='hidden' name='auth_token' value='" . $token . "' />");
		$pdocrud->fieldDataAttr("password", array("value"=> "", "placeholder" => "*****", "autocomplete" => "new-password"));
		$pdocrud->setPK("id");
		$render = $pdocrud->dbTable("usuario")->render("editform", array("id" => $id));

		View::render(
			"perfil",
			['render' => $render]
		);
	}
}
