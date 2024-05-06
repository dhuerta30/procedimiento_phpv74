
<?php require "layouts/header.php"; ?>
<?php require "layouts/sidebar.php"; ?>
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
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Tipo de Registro</h6>
                                <div class="card-body text-center">
                                    <div class="badge badge-success">Ingreso</div>
                                </div>
                            </div>
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Acción</h6>
                                <div class="card-body text-center">
                                    <button class="btn btn-info btn-sm previsualizar_ingreso">Previsualizar Ingreso</button>
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
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Tipo de Registro</h6>
                                <div class="card-body text-center">
                                    <div class="badge badge-danger">Egreso</div>
                                </div>
                            </div>
                            <div class="card">
                                <h6 class="card-title bg-custom page-title clearfix card-header pdocrud-table-heading p-2 mb-0">Acción</h6>
                                <div class="card-body text-center">
                                    <button class="btn btn-info btn-sm previsualizar_egreso">Previsualizar Egreso</button>
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
<?php require "layouts/footer.php"; ?>