
<?php require "layouts/header.php"; ?>
<?php require "layouts/sidebar.php"; ?>
<link href="<?=$_ENV["BASE_URL"]?>css/sweetalert2.min.css" rel="stylesheet">
<style>
    .h-custom {
        height: 118px;
    }

    .bg-custom {
        background: #34495e!important;
        color: #f0e68c;
        font-size: 14px;
    }

    .card-group {
        display: -ms-flexbox;
        display: flex;
        -ms-flex-flow: row wrap;
        flex-flow: row wrap;
        width: 100%;
        max-width: 620px;
        margin: 0 auto;
        justify-content: center;
    }
</style>
<div class="content-wrapper">
    <section class="content">
        <div class="card mt-4">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="text-center">Exportar Ingresos Procedimientos</h6>
                        <div class="card-group">
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Período</h6>
                                <div class="card-body">
                                    <input type="date" class="form-control periodo_ingreso">
                                </div>
                            </div>
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Tipo de Registro</h6>
                                <div class="card-body text-center">
                                    <div class="badge badge-success">Ingresado</div>
                                </div>
                            </div>
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Acción</h6>
                                <div class="card-body text-center">
                                    <button class="btn btn-info btn-sm previsualizar_ingreso"><i class="fa fa-eye"></i> Previsualizar Ingreso</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <h6 class="text-center">Exportar Egresos Procedimientos</h6>
                        <div class="card-group">
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Período</h6>
                                <div class="card-body">
                                    <input type="date" class="form-control periodo_egreso">
                                </div>
                            </div>
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Tipo de Registro</h6>
                                <div class="card-body text-center">
                                    <div class="badge badge-danger">Egresado</div>
                                </div>
                            </div>
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Acción</h6>
                                <div class="card-body text-center">
                                    <button class="btn btn-info btn-sm previsualizar_egreso"><i class="fa fa-eye"></i> Previsualizar Egreso</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="grilla">
                            <?=$render;?>
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
<script>
    $(document).on("click", ".previsualizar_ingreso", function(){
        let val = $('.periodo_ingreso').val();
        
        if(val != ""){
            $.ajax({
                type: "POST",
                url: "<?=$_ENV["BASE_URL"]?>home/consultar_datos_examenes_ingresados",
                data: {
                    val: val
                },
                dataType: "json",
                beforeSend: function() {
                    $("#pdocrud-ajax-loader").show();
                },
                success: function(data){
                    $("#pdocrud-ajax-loader").hide();
                    if(data['mensaje']){
                        $("#pdocrud_search_btn").click();
                        Swal.fire({
                            title: 'Genial!',
                            text: data["mensaje"],
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            allowOutsideClick: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Lo siento!',
                            text: data["error"],
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            allowOutsideClick: false
                        });
                    }
                }
            });
        } else {
            Swal.fire({
                title: 'Lo siento!',
                text: 'Ingrese un Período para realizar la exportación',
                icon: 'error',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false
            });
        }
    });


    $(document).on("click", ".previsualizar_egreso", function(){
        let val = $('.periodo_egreso').val();
        
        if(val != ""){
            $.ajax({
                type: "POST",
                url: "<?=$_ENV["BASE_URL"]?>home/consultar_datos_examenes_egresados",
                data: {
                    val: val
                },
                dataType: "json",
                beforeSend: function() {
                    $("#pdocrud-ajax-loader").show();
                },
                success: function(data){
                    $("#pdocrud-ajax-loader").hide();
                    if(data['mensaje']){
                        $("#pdocrud_search_btn").click();
                        Swal.fire({
                            title: 'Genial!',
                            text: data["mensaje"],
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            allowOutsideClick: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Lo siento!',
                            text: data["error"],
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            allowOutsideClick: false
                        });
                    }
                }
            });
        } else {
            Swal.fire({
                title: 'Lo siento!',
                text: 'Ingrese un Período para realizar la exportación',
                icon: 'error',
                confirmButtonText: 'Aceptar',
                allowOutsideClick: false
            });
        }
    });

    $(document).on("change", ".pdocrud_search_cols", function(){
        let val = $(this).val();
        let input = $('#pdocrud_search_box');

        if (val == "fecha_corte" || val == "fecha_exportacion") {
            input.prop('type', 'date');
        } else {
            input.prop('type', 'text');
            $("#pdocrud_search_box").val("");
        }
    });


    $(document).on("pdocrud_after_ajax_action",function(event, obj, data){
        let val = $('.pdocrud_search_cols').val();
        let input = $('#pdocrud_search_box');

        if (val == "fecha_corte" || val == "fecha_exportacion") {
            input.prop('type', 'date');
        } else {
            input.prop('type', 'text');
            $("#pdocrud_search_box").val("");
        }
    });

    $(document).on("click", ".descargar_excel", function(){
        let folio = $(this).data("folio");
        let fechacorte = $(this).data("fechacorte");
        let estado = $(this).data("estado");
    });
</script>
<?php require "layouts/footer.php"; ?>
