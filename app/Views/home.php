<?php require "layouts/header.php"; ?>
<?php require 'layouts/sidebar.php'; ?>
<div class="content-wrapper">
	<section class="content">
		<div class="card mt-4">
			<div class="card-body">
				<div class="row mb-3">
				</div>
				<?=$render?>
			</div>
		</div>
	</section>
</div>
<div id="pdocrud-ajax-loader">
    <img width="300" src="<?=$_ENV["BASE_URL"]?>app/libs/script/images/ajax-loader.gif" class="pdocrud-img-ajax-loader"/>
</div>
<script>
    $(document).on("pdocrud_after_submission", function(event, obj, data) {
      $.ajax({
        type: "POST",
        url: "<?=$_ENV["BASE_URL"]?>home/generar_datos_usuario",
        dataType: "json",
        success: function(response) {
          $('.nombre_usuario').text(response['usuario'][0]["nombre"]);
          $(".avatar").attr('src', "<?=$_ENV["BASE_URL"]?>app/libs/script/uploads/" + response['usuario'][0]['avatar']);
        }
      });
    });
</script>
<?php require 'layouts/footer.php'; ?>