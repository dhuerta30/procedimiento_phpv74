
<?php require "layouts/header.php"; ?>
<?php require "layouts/sidebar.php"; ?>
<link href="<?=$_ENV["BASE_URL"]?>css/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>css/buttons.dataTables.min.css">
<div class="content-wrapper">
    <section class="content">
        <div class="card mt-4">
            <div class="card-body">

                <div class="row procedimiento">
                    <div class="col-md-12">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <form id="form1" name="form1" action="rango.php" onsubmit="return validar();">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input class="form-control" type="date" name="ingreso" id="ingreso" title="Ingrese fecha de Inicio de Busqueda">
                                        </div>
                                        <div class="col-md-4">
                                            <input class="form-control" type="date" name="termino" id="termino" title="Ingrese Fecha de Termino de Busqueda"> 
                                        </div>
                                        <div class="col-md-4">
                                            <input type="submit" id="enviar" name="enviar" value="Buscar" class="btn btn-primary" title="Buscar" style="cursor:hand">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">

                            <div class='table-responsive tabla_principal'>
                                <table class="table table-striped tabla_rango_fechas text-center" style="width:100%">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th>N° Rut</th>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>Apellido Paterno</th>
                                            <th>Apellido Materno</th>
                                            <th>Fecha Estudio</th>
                                            <th>Estudio</th>
                                            <th>Observaciones</th>
                                            <th>Fecha Registro</th>
                                            <th>Documento 1</th>
                                            <th>Documento 2</th>
                                            <th>Documento 3</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    
                                    </tbody>
                                </table>
                            </div>

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
    function createDataTable(selector, ajaxUrl, columns, options = {}) {
    // Configuración predeterminada para el DataTable
    const defaultOptions = {
        searching: false,
        scrollX: true,
        lengthMenu: [10],
        dom: 'rtip',
        language: {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
            "infoEmpty": "Mostrando 0 a 0 de 0 Entradas",
            "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            "thousands": ",",
            "lengthMenu": "Mostrar _MENU_ Entradas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "zeroRecords": "Sin resultados encontrados",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        ajax: {
            url: ajaxUrl,
            type: "POST",
            dataType: "json"
        },
        columns: columns
    };

    // Combinar opciones predeterminadas con las proporcionadas por el usuario
    const config = $.extend(true, {}, defaultOptions, options);

    // Inicializar DataTable
    return $(selector).DataTable(config);
}


//createDataTable('.tabla_rango_fechas', "<?//$_ENV['BASE_URL']?>home/mostrar_grilla_rango_fechas", columns);


</script>
<?php require "layouts/footer.php"; ?>