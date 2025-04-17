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
                        
                        <div class="row">
                            <div class="col-md-12">
                                <?=$render?>
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
<div id="pdocrud-ajax-loader">
    <img width="300" src="<?=$_ENV["BASE_URL"]?>app/libs/script/images/ajax-loader.gif" class="pdocrud-img-ajax-loader"/>
</div>
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/js/jquery.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/sweetalert2.all.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?=$_ENV["BASE_URL"]?>js/moment.min.js"></script>
<script>
function generarToken(){
    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Polos/generarToken",
        dataType: "json",
        success: function(data){
            var token = data["data"];
            localStorage.setItem("tokenApiPolos", token);
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
    event.preventDefault(); // Evita el envío del formulario

    var rut = $('.rut').val();
    var token = localStorage.getItem("tokenApiPolos");

    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Polos/obtener_polos_por_rut",
        dataType: "json",
        data: {
            rut: rut,
            token: token
        },
        beforeSend: function() {
            // Puedes mostrar un indicador de carga aquí
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
                data: response.data,
                destroy: true,
                columns: [
                    { data: 'rut' },
                    { data: 'poc' },
                    { data: 'dnombre' },
                    { data: 'apellidop' },
                    { data: 'apellidom' },
                    { data: 'especialidad',
                        render: function(data, type, row, meta) {
                            if (data == 1) {
                                return 'OFTALMOLOGIA';
                            } else {
                                return data;
                            }
                        }
                    },
                    { data: 'fechadocumento',
                        render: function(data, type, row, meta) {
                            return data ? moment(data).format('DD-MM-YYYY') : '';
                        }
                     },
                    { data: 'tipodocumento',
                        render: function(data, type, row, meta){
                            const tipos = {
                                1: 'ANGIOGRAFIA',
                                2: 'OCT',
                                3: 'RECUENTO ENDOTELIAL',
                                4: 'ECO OCULAR',
                                5: 'FONDO DE OJO',
                                6: 'AVASTIN',
                                7: 'CAMPO VISUAL',
                                8: 'CONSENTIMIENTO',
                                9: 'BIOMETRIA',
                                10: 'Tratamiento Ortóptico',
                                11: 'Estudio de Estrabismo',
                                12: 'Retinografía',
                                13: 'Paquimetría'
                            };
                            return tipos[data] || data;
                        }
                    },
                    { data: 'observaciones' },
                    { data: 'fecharegistro',
                        render: function(data, type, row, meta) {
                            return data ? moment(data).format('DD-MM-YYYY HH:mm:ss') : '';
                        }
                     },
                    { data: 'rutapdf',
                        render: function(data, type, row, meta){
                            return '<button class="btn btn-info ver_pdf" data-id="'+row.id+'">Ver</button>';
                        } 
                    },
                    { data: 'rutapdf2' },
                    { data: 'rutapdf3' }
                ]
            });
        }
    });
});

$(document).on("click", ".ver_pdf", function() {
    var id = $(this).data("id");

    var token = localStorage.getItem("tokenApiPolos");

    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Polos/obtener_pdf_rut",
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
