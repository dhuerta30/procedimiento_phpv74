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
                                            <input type="submit" id="enviar" name="enviar" value="Buscar" class="btn btn-primary buscar" title="Buscar">
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

    $(document).on("click", ".buscar", function(){
        event.preventDefault(); // Evita el envío del formulario

        var ingreso = $('#ingreso').val();
        var termino = $('#termino').val();

        $.ajax({
            type: "POST",
            url: "<?=$_ENV['BASE_URL']?>Busqueda/obtener_rango_fechas_pacientes",
            dataType: "json",
            data: {
                ingreso: ingreso,
                termino: termino
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
                    $(".rut").val("");
                }
                // Reconstruir la tabla DataTable con los nuevos datos
                table = $('.tabla_rango_fechas').DataTable({
                    searching: false,
                    scrollX: true,
                    lengthMenu: [10],
                    dom: 'rtip',
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
                        { data: 'rut' },
                        { data: 'poc' },
                        { data: 'dnombre' },
                        { data: 'apellidop' },
                        { data: 'apellidom' },
                        { data: 'fechaestudio',
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
                        { data: 'estudio'},
                        { data: 'observaciones'},
                        { data: 'fecha_registro',
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
                        { data: 'rutapdf'},
                        { data: 'rutapdf2'},
                        { data: 'rutapdf3'}, 
                        {
                            render: function(data, type, row) {
                                return '<td>' +
                                            '<a href="javascript:;" title="Ver" class="btn btn-info btn-sm modificar" data-id="'+ row.id +'"><i class="fa fa-eye"></i> Ver</a>'+
                                        '</td>';
                            }
                        }
                    ]
                });
            }
    });
});
</script>
<?php require "layouts/footer.php"; ?>
