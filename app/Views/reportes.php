<?php require "layouts/header.php"; ?>
<?php require 'layouts/sidebar.php'; ?>
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>css/buttons.dataTables.min.css">
<link href="<?=$_ENV["BASE_URL"]?>css/sweetalert2.min.css" rel="stylesheet">
<style>
    .page-title.clearfix.card-header.pdocrud-table-heading, .row.pdocrud-options-files {
        display: none;
    }

    .select2-container .select2-selection--single {
        height: 38px!important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 5px!important;
    }

    table.dataTable {
        white-space: nowrap;
    }
</style>
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>css/flatpickr.min.css">
<div class="content-wrapper">
    <section class="content">
        <div class="card mt-4">
            <div class="card-body">

                <div class="row procedimiento">
                    <div class="col-md-12">
                        <h5>Buscar Reportes por Año</h5>
                        <hr>

                        <?=$render?>
                        <?=$mask?>
                        <?=$select2?>


                        <div class="row">
                            <div class="col-md-12">
                                <button class="exportar_excel border-0 p-2 mb-3"><i class="fas fa-file-excel"></i> Exportar a Excel</button>
                            </div>
                        </div>

                       <div class="reportes">
                        <table class="table table-striped tabla_reportes text-center" style="width:100%">
                            <thead class="bg-primary">
                                <tr>
                                    <th>Código Fonasa</th>
                                    <th>Procedencia</th>
                                    <th>Exámen</th>
                                    <th>Tipo de Exámen</th>
                                    <th>Año</th>
                                    <th>Media</th>
                                    <th>Total Exámenes</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>

                       </div>


                       <div class="resultados">
                            <div class='table-responsive'>

                            </div>
                        </div>

                       
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
<div id="pdocrud-ajax-loader">
    <img width="300" src="<?=$_ENV["BASE_URL"]?>app/libs/script/images/ajax-loader.gif" class="pdocrud-img-ajax-loader"/>
</div>
<script src="<?=$_ENV["BASE_URL"]?>js/flatpickr.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/moment.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/sweetalert2.all.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/dataTables.buttons.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/jszip.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/pdfmake.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/buttons.html5.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/buttons.print.min.js"></script>
<script>
function datatable(){
    $('.tabla_reportes').DataTable({
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
                    columns: [0, 1, 2, 3, 4, 5, 6] // Define las columnas a exportar
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
            url: "<?=$_ENV["BASE_URL"]?>home/reportes_all",
            type: "POST",
            dataType: "json"
        },
        columns: [
            { data: 'codigo_fonasa' },
            { data: 'procedencia',
                render: function(data, type, row, meta){
                    if(!data){
                        return "<div class='badge badge-danger'>Sin Procedencia</div>";
                    } else {
                        return data;
                    }
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
            { data: 'tipo_examen' },
            { data: 'ano', 
                render: function(data, type, row, meta){
                    var fecha = moment(data);

                    if (!fecha.isValid()) {
                        return "<div class='badge badge-danger'>Sin Año</div>";
                    }
                    var fechaFormateada = fecha.format('Y');
                    return fechaFormateada;
                }
             },
            { data: 'cantidad_media' },
            { data: 'total_examen' }
        ]
    });
}

function datatable_search(){
    $('.tabla_reportes_search').DataTable({
        searching: false,
        scrollX: true,
        lengthMenu: [10],
        paging: ($('.tabla_reportes_search tbody tr').length > 10) ? true : false,
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
                    columns: [0, 1, 2, 3, 4, 5, 6] // Define las columnas a exportar
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
        }
    });
}

$(document).ready(function(){
    datatable();
    ComboAno();
});

$(document).on("click", ".btn_search", function(){
    let ano_desde = $('#ano_desde').val();
    let ano_hasta = $('#ano_hasta').val();
    let procedencia = $('#procedencia_filtro').val();

    if(ano_desde == 0 && ano_hasta == 0 && procedencia == 0){
       $("#pdocrud-ajax-loader").show();
       setTimeout(() => {
        $("#pdocrud-ajax-loader").hide();
        $('.reportes').show();
       }, 1000);
    } else {
        $.ajax({
            type: "POST",
            url: "<?=$_ENV["BASE_URL"]?>home/buscar_por_ano",
            dataType: "html",
            data: {
                ano_desde: ano_desde,
                ano_hasta: ano_hasta,
                procedencia: procedencia
            },
            beforeSend: function() {
                $("#pdocrud-ajax-loader").show();
            },
            success: function(data){
                $("#pdocrud-ajax-loader").hide();
                $(".reportes").hide();
                $('.resultados').show();
                $('.resultados').html(data);
                $('.btn_limpiar').removeClass('d-none');
                datatable_search();
            }
        });
    }
});


$(document).on("click", ".exportar_excel", function(){
    // obtener las variables de cada filtro y pasarselas a un ajax que hara la query para exportar el excel
    let ano_desde = $('#ano_desde').val();
    let ano_hasta = $('#ano_hasta').val();
    let procedencia = $('#procedencia_filtro').val();

    let url = "<?=$_ENV["BASE_URL"]?>home/descargar_excel_reportes";

    // Agregar filtros a la URL según estén presentes
    if (ano_desde != "0") url += "/ano_desde/" + ano_desde;
    if (ano_hasta != "0") url += "/ano_hasta/" + ano_hasta;
    if (procedencia != "0") url += "/procedencia/" + procedencia;

    // Abrir la URL en una nueva ventana
    window.open(url);
});

$("#fecha").flatpickr({
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

function ComboAno() {
   var n = (new Date()).getFullYear();
   var selectDesde = document.getElementById("ano_desde");
   var selectHasta = document.getElementById("ano_hasta");

   for (var i = n; i >= 1900; i--) {
      selectDesde.options.add(new Option(i, i));
      selectHasta.options.add(new Option(i, i));
   }
}

//window.onload = ComboAno;

$(document).on("click", ".btn_limpiar", function(){

    $('.btn_limpiar').addClass('d-none');
    $(".reportes").show();
    $('.resultados').hide();
    $('#rut').val("");
    $('.ano_desde').select2('destroy');
    $('.ano_desde').val("");
    $('.ano_desde').select2();
    $('.ano_desde').html('<option value="0">Seleccionar Año Desde</option>');
    $('.procedencia_filtro').val("0");

    $('.ano_hasta').select2('destroy');
    $('.ano_hasta').val("");
    $('.ano_hasta').select2();
    $('.ano_hasta').html('<option value="0">Seleccionar Año Hasta</option>');
    ComboAno();
    //$('.btn_search').click();
});

$(document).on("click", ".btn_search", function(){
    $('.btn_limpiar').addClass('d-none');
});
</script>
<?php require 'layouts/footer.php'; ?>