
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
                                            <label>Ingrese fecha de Inicio de Búsqueda</label>
                                            <input class="form-control" type="date" name="ingreso" id="ingreso" title="Ingrese fecha de Inicio de Busqueda">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Ingrese fecha de Término de Búsqueda</label>
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
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/js/jquery.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/sweetalert2.all.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/flatpickr.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/moment.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/pdfmake.min.js"></script>
<script>
$(document).ready(function() {
    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Busqueda/listar_rango_tabla_nulla",
        dataType: "json",
        success: function(data) {
            // Verifica si hay datos y luego inicializa la DataTable
            if (data.data && data.data.length > 0) {
                // Crear un array para los datos de DataTable
                let tableData = data.data.map(item => [
                    item.rut,
                    item.codigo,
                    item.nombre,
                    item.apaterno,
                    item.amaterno,
                    item.fechaestudio,
                    item.estudio,
                    item.observaciones,
                    item.fecharegistro,
                    item.documento1,
                    item.documento2,
                    item.documento3
                ]);

                // Inicializa la DataTable con los datos
                $(".tabla_rango_fechas").DataTable({
                    data: tableData,
                    columns: [
                        { title: "rut" },     // Títulos de las columnas
                        { title: "codigo" },
                        { title: "nombre" },
                        { title: "apaterno" },
                        { title: "amaterno" },
                        { title: "fechaestudio" },
                        { title: "estudio" },
                        { title: "observaciones" },
                        { title: "fecharegistro" },
                        { title: "documento1" },
                        { title: "documento2" },
                        { title: "documento3" }
                    ]
                });
            } else {
                console.log("No hay datos para mostrar.");
            }
        },
        error: function(xhr, status, error) {
            // Manejo de errores
            console.error("Error en la solicitud AJAX:", status, error);
        }
    });
});

</script>
<?php require "layouts/footer.php"; ?>