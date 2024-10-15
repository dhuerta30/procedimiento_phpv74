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
                                <form id="form1" name="form1" onsubmit="return buscarPacientes(event);">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Ingrese fecha de Inicio de Búsqueda</label>
                                            <input class="form-control" type="date" name="ingreso" id="ingreso" title="Ingrese fecha de Inicio de Busqueda" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Ingrese fecha de Término de Búsqueda</label>
                                            <input class="form-control" type="date" name="termino" id="termino" title="Ingrese Fecha de Termino de Busqueda" required> 
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <input type="submit" id="enviar" name="enviar" value="Buscar" class="btn btn-primary" title="Buscar">
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
                                            <!-- Los datos se llenarán aquí con AJAX -->
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
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializa DataTable
    var table = $(".tabla_rango_fechas").DataTable();

    window.buscarPacientes = function(event) {
        event.preventDefault(); // Evita el envío del formulario

        var ingreso = $('#ingreso').val();
        var termino = $('#termino').val();

        $.ajax({
            url: "<?=$_ENV['BASE_URL']?>Busqueda/obtener_rango_fechas_pacientes",
            type: 'POST', // Cambia a GET si la API lo permite
            dataType: 'json',
            data: {
                ingreso: ingreso,
                termino: termino
            },
            success: function(response) {
                // Limpia la tabla
                table.clear();

                // Verifica si hay datos
                if (response.success) {
                    // Crea un array para los datos de DataTable
                    let tableData = response.data.map(item => [
                        item.rut,
                        item.poc,
                        item.dnombre,
                        item.apellidop,
                        item.apellidom,
                        item.fechaestudio,
                        item.estudio,
                        item.observaciones,
                        item.fecha_registro,
                        item.rutapdf,
                        item.rutapdf2,
                        item.rutapdf3
                    ]);

                    // Rellena la tabla con nuevos datos
                    table.rows.add(tableData).draw();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud AJAX:", status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al obtener los datos.'
                });
            }
        });
    };
});
</script>
<?php require "layouts/footer.php"; ?>
