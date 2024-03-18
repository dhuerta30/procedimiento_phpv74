<?php require "layouts/header.php"; ?>
<?php require 'layouts/sidebar.php'; ?>
<link href="<?=$_ENV["BASE_URL"]?>css/sweetalert2.min.css" rel="stylesheet">

<div class="modal fade" id="carga_masiva" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Carga Masiva</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?=$render2?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="content-wrapper">
    <section class="content">
        <div class="card">
            <div class="card-body">
                <?=$render?>
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
        $('.pdocrud-button-url').removeClass('pdocrud-actions');
        $('#carga_masiva').modal('hide');
        Swal.fire({
            title: "Genial!",
            text: json.message,
            icon: "success",
            confirmButtonText: "Aceptar"
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
<?php require 'layouts/footer.php'; ?>
