<?php require "layouts/header.php"; ?>
<?php require "layouts/sidebar.php"; ?>
<link href="<?=$_ENV["BASE_URL"]?>css/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="<?=$_ENV["BASE_URL"]?>css/buttons.dataTables.min.css">
<style>
     .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody>table>thead>tr>th, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody>table>thead>tr>td, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody>table>tbody>tr>th, .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody>table>tbody>tr>td {
        white-space: nowrap;
    }
</style>
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
                                            <label>(Rut sin puntos y con guion)</label>
                                            <input class="form-control rut" type="text" name="rut" placeholder="Ingresar Rut o Pasaporte" required>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <input type="submit" id="enviar" name="enviar" value="Buscar" class="btn btn-primary buscar" title="Buscar">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class='table-responsive tabla_principal'>
                                    <table class="table table-striped tabla_por_rut text-center" style="width:100%">
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
<script src="<?=$_ENV["BASE_URL"]?>js/moment.min.js"></script>
<script>
var table;
$(document).ready(function(){
    table = $('.tabla_por_rut').DataTable({
        searching: true,
        scrollX: true,
        lengthMenu: [10],
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
});

 $(document).on("click", ".buscar", function(){
    event.preventDefault(); // Evita el envío del formulario

    var rut = $('.rut').val();

    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Busqueda/obtener_pacientes_por_rut",
        dataType: "json",
        data: {
            rut: rut
        },
        beforeSend: function() {
            // Puedes mostrar un indicador de carga aquí
            $("#pdocrud-ajax-loader").show();
        },
        success: function(response){
            $("#pdocrud-ajax-loader").hide();

            if(response["error"]){
                Swal.fire({
                    title: 'error!',
                    text: response["error"],
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false
                });
            } else {
                // Verificar que la respuesta contenga datos
                if (!response.data || response.data.length === 0) {
                    Swal.fire({
                        title: 'Sin resultados',
                        text: 'No se encontraron datos con el Rut Ingresado.',
                        icon: 'info',
                        confirmButtonText: 'Aceptar'
                    });
                }
            }
            // Reconstruir la tabla DataTable con los nuevos datos
            table = $('.tabla_por_rut').DataTable({
                searching: true,
                scrollX: true,
                lengthMenu: [10],
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
                    { data: 'rut' }, // Ensure this key exists in the returned objects
                    { data: 'poc' },
                    { data: 'dnombre' },
                    { data: 'apellidop' },
                    { data: 'apellidom' },
                    { data: 'fechaestudio' },
                    { data: 'estudio' },
                    { data: 'observaciones' },
                    { data: 'fecha_registro' },
                    { data: 'rutapdf',
                        render: function(data, type, row, meta){
                           return '<a class="btn btn-info" href='+ row.id +'>Ver</a>';
                        } 
                    },
                    { data: 'rutapdf2' },
                    { data: 'rutapdf3' }
                ]
            });
        }
    });
});
</script>
<?php require "layouts/footer.php"; ?>
