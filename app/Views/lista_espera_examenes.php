<?php require "layouts/header.php"; ?>
<?php require 'layouts/sidebar.php'; ?>
<link href="<?=$_ENV["BASE_URL"]?>css/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>css/flatpickr.min.css">
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>css/buttons.dataTables.min.css">
<style>
    .page-title.clearfix.card-header.pdocrud-table-heading, .row.pdocrud-options-files {
        display: none;
    }
    .pdocrud-search {
        display: none!important;
    }

    .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody>table>thead>tr>th, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody>table>thead>tr>td, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody>table>tbody>tr>th, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody>table>tbody>tr>td {
        white-space: nowrap;
    }
    
    .btn:not(:disabled):not(.disabled) {
        cursor: pointer;
        margin: 3px;
    }
</style>
<div class="content-wrapper">
	<section class="content">
		<div class="card mt-4">
			<div class="card-body">
				<div class="row mb-3">
				</div>
				<h5>Búsqueda Lista Espera Exámenes</h5>
				<hr>

				<div class="container examenes">
					<div class="row justify-content-center">
                        <div class="col-md-9">
                            <?=$render?>
                            <?=$mask;?>
                        </div>
                    </div>
				</div>

                <div class="datos_search p-0"></div>
              
                <div class="row">
                    <div class="col-md-12">
                        <button class="exportar_excel border-0 p-2 mb-3"><i class="fas fa-file-excel"></i> Exportar a Excel</button>
                    </div>
                </div>
                    <div class='table-responsive tabla_principal'>
                        <table class="table table-striped tabla_reportes text-center" style="width:100%">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Estado</th>
                                    <th>Especialidad</th>
                                    <th>Rut</th>
                                    <th>Paciente</th>
                                    <th>Teléfono</th>
                                    <th>Edad</th>
                                    <th>Código</th>
                                    <th>Exámen</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Fecha Agendada</th>
                                    <th>Fecha Egreso</th>
                                    <th>Profesional</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            
                            </tbody>
                        </table>
                    </div>

                <div class="cargar_modal"></div>
			</div>
		</div>
	</section>
</div>
<div id="pdocrud-ajax-loader">
	<img width="300" src="<?=$_ENV["BASE_URL"]?>app/libs/script/images/ajax-loader.gif" class="pdocrud-img-ajax-loader"/>
</div>
<script src="<?=$_ENV["BASE_URL"]?>js/sweetalert2.all.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/flatpickr.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/moment.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/dataTables.buttons.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/jszip.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/pdfmake.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/buttons.html5.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/buttons.print.min.js"></script>
<script>

var table;
$(document).ready(function(){
    table = $('.tabla_reportes').DataTable({
        searching: false,
        scrollX: true,
        lengthMenu: [10],
        dom: 'rtip',
        /*buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                className: 'btn btn-light',
                filename: function(){
                    return 'reportes';
                },
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] // Define las columnas a exportar
                }
            }
        ],*/
        language: {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
            "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ Entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Ultimo",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        ajax: {
            url: "<?=$_ENV["BASE_URL"]?>home/mostrar_grilla_lista_espera",
            type: "POST",
            dataType: "json"
        },
        columns: [
            { data: 'estado' },
            { data: 'especialidad' },
            { data: 'rut' },
            { data: 'paciente' },
            { data: 'telefono' },
            { data: 'edad',
                render: function(data, type, row, meta){
                    
                    if (data == 0) {
                        return "<div class='badge badge-danger'>Sin Edad</div>";
                    } else {
                        return data;
                    }
                } 
             },
            { data: 'codigo',
                render: function(data, type, row, meta){
                    return "<div class='badge badge-info'>"+ data +"</div>";
                }
            },
            { data: 'examen',
                render: function(data, type, row, meta){
                    if(type === 'display' && data.length > 10){ // Limitar a 10 caracteres
                        return data.substr(0, 10) + '...'; // Mostrar solo los primeros 10 caracteres seguidos de puntos suspensivos
                    } else {
                        return data; // Devolver el dato sin cambios si tiene menos de 10 caracteres
                    }
                }
             },
            { data: 'fecha_solicitud',
                render: function(data, type, row, meta){
                    var fecha = moment(data);

                    if (!fecha.isValid()) {
                        return "<div class='badge badge-danger'>Sin Fecha</div>";
                    }
                    // Formatear la fecha en el formato deseado (d/m/y)
                    var fechaFormateada = fecha.format('DD/MM/Y');
                    return fechaFormateada;
                } 
            },
            { data: 'fecha',
                render: function(data, type, row, meta){
                    var fecha = moment(data);

                    if (!fecha.isValid()) {
                        return "<div class='badge badge-danger'>Sin Fecha</div>";
                    }
                    // Formatear la fecha en el formato deseado (d/m/y)
                    var fechaFormateada = fecha.format('DD/MM/Y');
                    return fechaFormateada;
                } 
            },
            { data: 'fecha_egreso',
                render: function(data, type, row, meta){
                    var fecha = moment(data);

                    if (!fecha.isValid()) {
                        return "<div class='badge badge-danger'>Sin Fecha</div>";
                    }
                    // Formatear la fecha en el formato deseado (d/m/y)
                    var fechaFormateada = fecha.format('DD/MM/Y');
                    return fechaFormateada;
                } 
             },
            { data: 'profesional' },
            {
                render: function(data, type, row) {
                    return '<td>' +
                                '<a href="javascript:;" title="Agregar Nota" class="btn btn-primary btn-sm agregar_notas" data-id="'+ row.id_datos_paciente +'" data-fechasolicitud="'+ row.fecha_solicitud +'"><i class="fa fa-file-o"></i></a>' +
                                '<a href="javascript:;" title="Egresar Solicitud" class="btn btn-success btn-sm egresar_solicitud" data-id="'+ row.id_datos_paciente +'" data-solicitud="'+ row.id_detalle_de_solicitud +'"><i class="fa fa-arrow-right"></i></a>' +
                                '<a href="javascript:;" title="Mostrar Adjunto" class="btn btn-secondary btn-sm mostrar_adjunto" data-id="'+ row.id_datos_paciente +'" data-solicitud="'+ row.id_detalle_de_solicitud +'"><i class="fa fa-file-o"></i></a>' +
                                '<a href="javascript:;" title="Ver PDF" class="btn btn-primary btn-sm imprimir_solicitud" data-id="'+ row.id_datos_paciente +'" data-solicitud="'+ row.id_detalle_de_solicitud +'"><i class="fa fa-file-pdf"></i></a>' +
                                '<a href="javascript:;" title="Procedimientos" class="btn btn-primary btn-sm procedimientos" data-id="'+ row.id_datos_paciente +'" data-solicitud="'+ row.id_detalle_de_solicitud +'" data-fechasolicitud="'+ row.fecha_solicitud +'"><i class="fa fa-folder"></i></a>' +
                            '</td>';
                }
            }
        ]
    });

    $(".fecha_solicitud").flatpickr({
        dateFormat: "Y-m-d",
        allowInput: true,
        //defaultDate: new Date(),
        locale: {
            firstDayOfWeek: 1, // Lunes como primer día de la semana
            weekdays: {
                shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                longhand: [
                    'Domingo',
                    'Lunes',
                    'Martes',
                    'Miércoles',
                    'Jueves',
                    'Viernes',
                    'Sábado'
                ]
            },
            months: {
                shorthand: [
                    'Ene',
                    'Feb',
                    'Mar',
                    'Abr',
                    'May',
                    'Jun',
                    'Jul',
                    'Ago',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dic'
                ],
                longhand: [
                    'Enero',
                    'Febrero',
                    'Marzo',
                    'Abril',
                    'Mayo',
                    'Junio',
                    'Julio',
                    'Agosto',
                    'Septiembre',
                    'Octubre',
                    'Noviembre',
                    'Diciembre'
                ]
            }
        }
    });
});


$(document).on("click", ".buscar", function(){
    // Obtener los valores de los filtros
    let run = $('.rut').val();
    let nombre_paciente = $('.nombre_paciente').val();
    let estado = $('.estado').val();
    let procedencia = $('.procedencia').val();
    let profesional = $('.profesional').val();
    let fecha_solicitud = $('.fecha_solicitud').val();

    $.ajax({
        type: "POST",
        url: "<?=$_ENV["BASE_URL"]?>home/buscar_examenes",
        dataType: "json",
        data: {
            run: run,
            nombre_paciente: nombre_paciente,
            estado: estado,
            procedencia: procedencia,
            profesional: profesional,
            fecha_solicitud: fecha_solicitud
        },
        beforeSend: function() {
            // Puedes mostrar un indicador de carga aquí
            $("#pdocrud-ajax-loader").show();
        },
        success: function(response){
            $("#pdocrud-ajax-loader").hide();
            // Reconstruir la tabla DataTable con los nuevos datos
            table = $('.tabla_reportes').DataTable({
                searching: false,
                scrollX: true,
                lengthMenu: [10],
                dom: 'rtip',
                /*buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-light',
                        filename: function(){
                            return 'reportes';
                        },
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] // Define las columnas a exportar
                        }
                    }
                ],*/
                language: {
                    "decimal": "",
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                    "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                    "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ Entradas",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Ultimo",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                data: response.data, // Los datos filtrados del controlador PHP
                destroy: true,
                columns: [
                    { data: 'estado' },
                    { data: 'especialidad' },
                    { data: 'rut' },
                    { data: 'paciente' },
                    { data: 'telefono' },
                    { data: 'edad',
                        render: function(data, type, row, meta){
                            
                            if (data == 0) {
                                return "<div class='badge badge-danger'>Sin Edad</div>";
                            } else {
                                return data;
                            }
                        } 
                    },
                    { data: 'codigo',
                        render: function(data, type, row, meta){
                            return "<div class='badge badge-info'>"+ data +"</div>";
                        }
                    },
                    { data: 'examen',
                        render: function(data, type, row, meta){
                            if(type === 'display' && data.length > 10){ // Limitar a 10 caracteres
                                return data.substr(0, 10) + '...'; // Mostrar solo los primeros 10 caracteres seguidos de puntos suspensivos
                            } else {
                                return data; // Devolver el dato sin cambios si tiene menos de 10 caracteres
                            }
                        }
                    },
                    { data: 'fecha_solicitud',
                        render: function(data, type, row, meta){
                            var fecha = moment(data);

                            if (!fecha.isValid()) {
                                return "<div class='badge badge-danger'>Sin Fecha</div>";
                            }
                            // Formatear la fecha en el formato deseado (d/m/y)
                            var fechaFormateada = fecha.format('DD/MM/Y');
                            return fechaFormateada;
                        } 
                    },
                    { data: 'fecha',
                        render: function(data, type, row, meta){
                            var fecha = moment(data);

                            if (!fecha.isValid()) {
                                return "<div class='badge badge-danger'>Sin Fecha</div>";
                            }
                            // Formatear la fecha en el formato deseado (d/m/y)
                            var fechaFormateada = fecha.format('DD/MM/Y');
                            return fechaFormateada;
                        } 
                    },
                    { data: 'fecha_egreso',
                        render: function(data, type, row, meta){
                            var fecha = moment(data);

                            if (!fecha.isValid()) {
                                return "<div class='badge badge-danger'>Sin Fecha</div>";
                            }
                            // Formatear la fecha en el formato deseado (d/m/y)
                            var fechaFormateada = fecha.format('DD/MM/Y');
                            return fechaFormateada;
                        } 
                    },
                    { data: 'profesional' },
                    {
                        render: function(data, type, row) {
                            return '<td>' +
                                        '<a href="javascript:;" title="Agregar Nota" class="btn btn-primary btn-sm agregar_notas" data-id="'+ row.id_datos_paciente +'" data-fechasolicitud="'+ row.fecha_solicitud +'"><i class="fa fa-file-o"></i></a>' +
                                        '<a href="javascript:;" title="Egresar Solicitud" class="btn btn-success btn-sm egresar_solicitud" data-id="'+ row.id_datos_paciente +'" data-solicitud="'+ row.id_detalle_de_solicitud +'"><i class="fa fa-arrow-right"></i></a>' +
                                        '<a href="javascript:;" title="Mostrar Adjunto" class="btn btn-secondary btn-sm mostrar_adjunto" data-id="'+ row.id_datos_paciente +'" data-solicitud="'+ row.id_detalle_de_solicitud +'"><i class="fa fa-file-o"></i></a>' +
                                        '<a href="javascript:;" title="Ver PDF" class="btn btn-primary btn-sm imprimir_solicitud" data-id="'+ row.id_datos_paciente +'" data-solicitud="'+ row.id_detalle_de_solicitud +'"><i class="fa fa-file-pdf"></i></a>' +
                                        '<a href="javascript:;" title="Procedimientos" class="btn btn-primary btn-sm procedimientos" data-id="'+ row.id_datos_paciente +'" data-solicitud="'+ row.id_detalle_de_solicitud +'" data-fechasolicitud="'+ row.fecha_solicitud +'"><i class="fa fa-folder"></i></a>' +
                                    '</td>';
                        }
                    }
                ]
            });


        }
    });
});


$(document).on("click", ".limpiar_filtro", function(){
    $('.rut').val("");
    $('.nombre_paciente').val("");
    $('.estado').val("");
    $('.procedencia').val("");
    $('.profesional').val("");
    $('.fecha_solicitud').val("");
    $('.buscar').click();
    $('.cargar_modal').empty();
});


$(document).on('change', '.compra_servicio', function(){
    var valorSeleccionado = $('.compra_servicio:checked').val();
    
    if(valorSeleccionado == "2"){
        $('.label_empresas_en_convenio').hide();
        $('.empresas_en_convenio').hide();
    } else {
        $('.label_empresas_en_convenio').show();
        $('.empresas_en_convenio').show();
    }
});


$(document).on("click", ".egresar_solicitud", function(){
    let id = $(this).data('id');
    let id_detalle_de_solicitud = $(this).data('solicitud');

    $.ajax({
        type: "POST",
        url: "<?=$_ENV["BASE_URL"]?>home/cargar_modal_egresar_solicitud",
        dataType: "html",
        data: {
            id: id,
            id_detalle_de_solicitud: id_detalle_de_solicitud
        },
        beforeSend: function() {
            $("#pdocrud-ajax-loader").show();
        },
        success: function(data){
            $("#pdocrud-ajax-loader").hide();
            $('.cargar_modal').html(data);
            $('#egresar_solicitud').modal('show');

            //$('input[value="2"].compra_servicio').prop('checked', true);
            var valorSeleccionado = $('.compra_servicio:checked').val();
            if(valorSeleccionado == "2" || valorSeleccionado === undefined){
                $('.label_empresas_en_convenio').hide();
                $('.empresas_en_convenio').hide();
            } else {
                $('.label_empresas_en_convenio').show();
                $('.empresas_en_convenio').show();
            }

            $(".fecha_egreso").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true,
                //defaultDate: new Date(),
                locale: {
                    firstDayOfWeek: 1, // Lunes como primer día de la semana
                    weekdays: {
                        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                        longhand: [
                            'Domingo',
                            'Lunes',
                            'Martes',
                            'Miércoles',
                            'Jueves',
                            'Viernes',
                            'Sábado'
                        ]
                    },
                    months: {
                        shorthand: [
                            'Ene',
                            'Feb',
                            'Mar',
                            'Abr',
                            'May',
                            'Jun',
                            'Jul',
                            'Ago',
                            'Sep',
                            'Oct',
                            'Nov',
                            'Dic'
                        ],
                        longhand: [
                            'Enero',
                            'Febrero',
                            'Marzo',
                            'Abril',
                            'Mayo',
                            'Junio',
                            'Julio',
                            'Agosto',
                            'Septiembre',
                            'Octubre',
                            'Noviembre',
                            'Diciembre'
                        ]
                    }
                }
            });

        }
    });
});

$(document).on("click", ".procedimientos", function(){
    let id = $(this).data('id');
    let fecha_solicitud = $(this).data('fechasolicitud');
    let id_detalle_de_solicitud = $(this).data('solicitud');

    $.ajax({
        type: "POST",
        url: "<?=$_ENV["BASE_URL"]?>home/cargar_modal_procedimientos",
        dataType: "html",
        data: {
            id: id,
            fecha_solicitud: fecha_solicitud,
            id_detalle_de_solicitud: id_detalle_de_solicitud
        },
        beforeSend: function() {
            $("#pdocrud-ajax-loader").show();
        },
        success: function(data){
            $("#pdocrud-ajax-loader").hide();
            $('.cargar_modal').html(data);
            $('#procedimientos').modal('show');

            $(".fecha").flatpickr({
                enableTime: true,
                enableSeconds: true,
                dateFormat: "Y-m-d H:i:S",
                allowInput: true,
                //defaultDate: new Date(),
                locale: {
                    firstDayOfWeek: 1, // Lunes como primer día de la semana
                    weekdays: {
                        shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
                    },
                    months: {
                        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
                    }
                }
            });
            
        }
    });
});


$(document).on("click", ".agregar_notas", function(){
    let id = $(this).data('id');
    let fecha_solicitud = $(this).data('fechasolicitud');

    $.ajax({
        type: "POST",
        url: "<?=$_ENV["BASE_URL"]?>home/cargar_modal_agregar_nota",
        dataType: "html",
        data: {
            id: id,
            fecha_solicitud: fecha_solicitud
        },
        beforeSend: function() {
            $("#pdocrud-ajax-loader").show();
        },
        success: function(data){
            $("#pdocrud-ajax-loader").hide();
            $('.cargar_modal').html(data);
            $('#agregar_nota').modal('show');
        }
    });
});

$(document).on("click", ".ver_logs", function(){
    let id = $(this).data('id');
    let fecha_solicitud = $(this).data('fechasolicitud');

    $.ajax({
        type: "POST",
        url: "<?=$_ENV["BASE_URL"]?>home/cargar_modal_logs",
        dataType: "html",
        data: {
            id: id,
            fecha_solicitud: fecha_solicitud
        },
        beforeSend: function() {
            $("#pdocrud-ajax-loader").show();
        },
        success: function(data){
            $("#pdocrud-ajax-loader").hide();
            $('.cargar_modal').html(data);
            $('#logs').modal('show');
        }
    });
});

$(document).on('click', '.imprimir_solicitud', function () {
    let id = $(this).data('id');
    let id_detalle_de_solicitud = $(this).data('solicitud');
    window.open("<?=$_ENV["BASE_URL"]?>home/imprimir_solicitud/id/" + id + "/id_detalle_de_solicitud/" + id_detalle_de_solicitud);
});

$(document).on("click", ".mostrar_adjunto", function(){
    let id = $(this).data('id');
    let id_detalle_de_solicitud = $(this).data('solicitud');
    window.open("<?=$_ENV["BASE_URL"]?>home/mostrar_adjunto/id/" + id + "/id_detalle_de_solicitud/" + id_detalle_de_solicitud);
});

$(document).on("click", ".exportar_excel", function(){
    // obtener las variables de cada filtro y pasarselas a un ajax que hara la query para exportar el excel
    let run = $('.rut').val();
    let nombre_paciente = $('.nombre_paciente').val();
    let estado = $('.estado').val();
    let procedencia = $('.procedencia').val();
    let profesional = $('.profesional').val();
    let fecha_solicitud = $('.fecha_solicitud').val();

    let url = "<?=$_ENV["BASE_URL"]?>home/descargar_excel_lista_espera_examanes";

    // Agregar filtros a la URL según estén presentes
    if (run) url += "/run/" + run;
    if (nombre_paciente) url += "/nombre_paciente/" + nombre_paciente;
    if (estado) url += "/estado/" + estado;
    if (procedencia) url += "/procedencia/" + procedencia;
    if (profesional) url += "/profesional/" + profesional;
    if (fecha_solicitud) url += "/fecha_solicitud/" + fecha_solicitud;

    // Abrir la URL en una nueva ventana
    window.open(url);
});

$(document).on("pdocrud_before_ajax_action", function(event, obj, data){
    $('.titulo_modal').html(`
        <i class="fa fa-file-o"></i> Agregar Nota
    `);
});

$(document).on("pdocrud_after_submission", function(event, obj, data){
    let json = JSON.parse(data);

    if(json.message){
        $('#procedimientos').modal('hide');
        $('#egresar_solicitud').modal('hide');
        $('#agregar_nota').modal('hide');
    
        Swal.fire({
            title: 'Genial!',
            text: json.message,
            icon: 'success',
            confirmButtonText: 'Aceptar',
            allowOutsideClick: false
        }).then((result) => {
            if(result.isConfirmed) {
                $('.buscar').click();
            }
        });
    }
});

$(document).on("change", ".estado_procedimiento", function(){
    let val = $(this).val();
    if(val == "Ingresado"){
        $('.fecha').val("");
    }
});
</script>
<?php require 'layouts/footer.php'; ?>