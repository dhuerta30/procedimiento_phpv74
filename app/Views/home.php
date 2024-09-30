<?php require "layouts/header.php"; ?>
<?php require 'layouts/sidebar.php'; ?>
<link href="<?=$_ENV["BASE_URL"]?>css/sweetalert2.min.css" rel="stylesheet">
<div class="content-wrapper">
	<section class="content">
		<div class="card mt-4">
			<div class="card-body">
				<div class="row mb-3">
				</div>
				<?=$render?>

        <br>
        <br>
        <div class="carga_masiva d-none">
          <?=$upload?>
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
    $(document).on("pdocrud_after_submission", function(event, obj, data) {
      let json = JSON.parse(data);

      $('.pdocrud_error').hide();
      $('.pdocrud_message').hide();

      if(json.message){
        $.ajax({
          type: "POST",
          url: "<?=$_ENV["BASE_URL"]?>home/generar_datos_usuario",
          dataType: "json",
          success: function(response) {
            $('.nombre_usuario').text(response['usuario'][0]["nombre"]);
            $(".avatar").attr('src', "<?=$_ENV["BASE_URL"]?>app/libs/script/uploads/" + response['usuario'][0]['avatar']);
          }
        });
        $('.pdocrud-back').click();
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