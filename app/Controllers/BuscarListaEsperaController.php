<?php

namespace App\Controllers;

use App\core\SessionManager;
use App\core\Token;
use App\core\Request;
use App\core\View;
use App\core\Redirect;
use App\core\DB;
use Xinvoice;
use App\Controllers\HomeController;

class BuscarListaEsperaController
{
    public function __construct()
	{
		SessionManager::startSession();
		$Sesusuario = SessionManager::get('usuario');
		if (!isset($Sesusuario)) {
			Redirect::to("Login/index");
		}
	}

    public function index()
    {
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
        View::render('buscar_lista_espera', [
            'render' => $render,
			'mask' => $mask
        ]);
    }

    public function mostrar_grilla_lista_espera(){
		$crud = DB::PDOCrud(true);
		$pdomodel = $crud->getPDOModelObj();
    
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
			tipo_examen,
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

    public function buscar_examenes_lista_espera(){
		
		$request = new Request();

		if($request->getMethod() === 'POST'){

			$pdocrud = DB::PDOCrud(true);
			$pdomodel = $pdocrud->getPDOModelObj();

			$where = "";
			$run = $request->post('run');
            $pasaporte = $request->post('pasaporte');
			$nombre_paciente = $request->post('nombre_paciente');

			if (empty($run) && empty($pasaporte) && empty($nombre_paciente)) {
				echo json_encode(["error" => "Debe ingresar al menos un campo para realizar la búsqueda"]);
				return;
			}

            if (!empty($run)) {

                if (!HomeController::validaRut($run)) {
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
					ds.tipo_examen,
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
}