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


                        <div class="modal fade" id="modalPDF" tabindex="-1" aria-labelledby="modalPDFLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalPDFLabel">Ver PDF</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="cargar_modal"></div>
                                </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/js/jquery.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/sweetalert2.all.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/moment.min.js"></script>
<script>
function generarToken(){
    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Busqueda/generarToken",
        dataType: "json",
        success: function(data){
            var token = data["data"];
            localStorage.setItem("tokenApi", token);
        }
    });
}
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

 $(document).on("click", ".buscar", function(event){
    event.preventDefault();

    var rut = $('.rut').val();
    var token = localStorage.getItem("tokenApi");

    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Busqueda/obtener_pacientes_por_rut",
        dataType: "json",
        data: {
            rut: rut,
            token: token
        },
        beforeSend: function() {
            $("#loader").show();
        },
        success: function(response){
            $("#loader").hide();

            if(response["error"]){
                Swal.fire({
                    title: 'error!',
                    text: response["error"],
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false
                });
            } else if(response["mensaje"]){
                Swal.fire({
                    title: 'error!',
                    text: response["mensaje"],
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        generarToken();
                    }
                });
            } else {
                if (!response.data || response.data.length === 0) {
                    Swal.fire({
                        title: 'Sin resultados',
                        text: 'No se encontraron datos con el Rut Ingresado.',
                        icon: 'info',
                        confirmButtonText: 'Aceptar'
                    });
                }
            }
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
                data: response.data,
                destroy: true,
                columns: [
                    { data: 'rut' },
                    { data: 'poc' },
                    { data: 'dnombre' },
                    { data: 'apellidop' },
                    { data: 'apellidom' },
                    { data: 'fechaestudio',
                        render: function(data, type, row, meta) {
                            return data ? moment(data).format('DD-MM-YYYY') : '';
                        }
                     },
                    { data: 'estudio' },
                    { data: 'observaciones' },
                    { data: 'fecha_registro',
                        render: function(data, type, row, meta) {
                            return data ? moment(data).format('DD-MM-YYYY HH:mm:ss') : '';
                        }
                     },
                    { data: 'rutapdf',
                        render: function(data, type, row, meta){
                            if (data) {
                                return '<button class="btn btn-info ver_pdf" data-id="'+row.id+'">Ver</button>';
                            } else {
                                return '';
                            }
                        } 
                    },
                    { data: 'rutapdf2',
                        render: function(data, type, row, meta) {
                            if (data) {
                                return '<button class="btn btn-info ver_pdf" data-id="'+row.id+'">Ver</button>';
                            } else {
                                return '';
                            }
                        }
                     },
                    { data: 'rutapdf3',
                        render: function(data, type, row, meta) {
                            if (data) {
                                return '<button class="btn btn-info ver_pdf" data-id="'+row.id+'">Ver</button>';
                            } else {
                                return '';
                            }
                        }
                     }
                ]
            });
        }
    });
});

$(document).on("click", ".ver_pdf", function() {
    var id = $(this).data("id");

    var token = localStorage.getItem("tokenApi");

    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Busqueda/obtener_pdf_rut",
        dataType: "json",
        data: {
            id: id,
            token: token
        },
        beforeSend: function() {
            $("#loader").show();
        },
        success: function(response) {
            if (response.error) {
                $("#loader").hide();
                Swal.fire({
                    title: 'Error!',
                    text: response.error,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false
                });
            } else {
                $("#loader").hide();
                var pdfUrl = response.data.rutapdf;
                var embedHtml = '<embed src="' + pdfUrl + '" type="application/pdf" width="100%" height="600">';
                $('.cargar_modal').html(embedHtml);
                $('#modalPDF').modal('show');
            }
        }
    });
});
</script>
<?php require "layouts/footer.php"; ?>
