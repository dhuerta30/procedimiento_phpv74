<?php

namespace App\Controllers;

use App\core\SessionManager;
use App\core\Token;
use App\core\Request;
use App\core\View;
use App\core\Redirect;
use App\core\DB;
use Xinvoice;
        
class BuscarListaEsperaController
{
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
        View::render('buscar_lista_espera', [
            'render' => $render,
			'mask' => $mask
        ]);
    }
}