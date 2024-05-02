
<?php require "layouts/header.php"; ?>
<?php require "layouts/sidebar.php"; ?>
<div class="content-wrapper">
    <section class="content">
        <div class="card mt-4">
            <div class="card-body">

                <div class="row procedimiento">
                    <div class="col-md-12">
                        <h5>Carga Masiva Exámenes Pacientes</h5>
                        
                        <?=$render;?>

                        <br>
                        <a class="btn btn-primary" href="<?=$_ENV["BASE_URL"]?>app/libs/script/uploads/datos_paciente.xlsx"><i class="fa fa-download"></i> Descargar Excel para Carga Masiva</a>
                    
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
$(document).on("click", ".carga_masiva", function(){
    $('#carga_masiva').modal('show');
});

$(document).on("pdocrud_after_submission", function(event, obj, data){
    let json = JSON.parse(data);

    $('.pdocrud_error').hide();
    $('.pdocrud_message').hide();
    if(json.message){
        $('.pdocrud-back').click();
        $('#pdocrud_search_btn').click();
        $('.pdocrud-file-input-control').val("");
        $('#carga_masiva').modal('hide');
        Swal.fire({
            title: 'Genial!',
            text: json.message,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    } else {
        Swal.fire({
            title: 'Lo siento!',
            text: json.error,
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    }
});
</script>
<?php require "layouts/footer.php"; ?>