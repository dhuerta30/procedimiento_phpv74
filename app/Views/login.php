<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Hospital | Login</title>
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link href="<?=$_ENV["BASE_URL"]?>css/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
<style>
    body {
        background: #5d6d7e!important;
    }
    li.list-group-item.bg-primary.text-white.text-center {
        font-size: 20;
        font-weight: 500;
    }

    ul.list-unstyled {
        background: red;
        color: #fff;
        padding: 3px;
        border-radius: 4px;
    }
</style>
<div class="container">
    <div class="row mt-5">
        <div class="col-md-6 m-auto">
            
        <div class="card">
        <ul class="list-group list-group-flush">
            <li class="list-group-item bg-primary text-white text-center">Iniciar Sesión</li>
            <div class="row">
                <div class="col-md-12 text-center">
                    <img src="<?=$_ENV["BASE_URL"]?>theme/img/hospital.png" width="80">
                </div>
            </div>
            <li class="list-group-item bg-white">
            <?= $login; ?>
            <?= $mask; ?>
            </li>
            <li class="list-group-item bg-primary"><a href="<?=$_ENV["BASE_URL"]?>login/reset" class="text-white">¿Olvidaste tu clave?</a></li>
        </ul>
        </div>

        </div>
    </div>
</div>
<div id="pdocrud-ajax-loader">
    <img width="300" src="<?=$_ENV["BASE_URL"]?>app/libs/script/images/ajax-loader.gif" class="pdocrud-img-ajax-loader"/>
</div>
<script src="<?=$_ENV["BASE_URL"]?>js/sweetalert2.all.min.js"></script>
<script>
    $(document).on("change", ".seleccion_de_acceso", function(){
        let val = $(this).val();

        if(val == "rut_clave"){
            $(".rut_col").removeClass("d-none");
            $(".usuario_col").addClass("d-none");
            $(".usuario").attr("disabled", "disabled");
            $(".usuario").removeAttr("required", "required");
            $(".rut").removeAttr("disabled", "disabled");
            $(".rut").attr("required", "required");
            $(".botones").removeClass("d-none");
        }
        
        if(val == "usuario_clave"){
            $(".rut_col").addClass("d-none");
            $(".usuario_col").removeClass("d-none");
            $(".rut").attr("disabled", "disabled");
            $(".rut").removeAttr("required", "required");
            $(".usuario").removeAttr("disabled", "disabled");
            $(".usuario").attr("required", "required");
            $(".botones").removeClass("d-none");
        } 

        if(val == ""){
            $(".usuario_col").addClass("d-none");
            $(".rut_col").addClass("d-none");

            $(".rut").attr("disabled", "disabled");
            $(".usuario").attr("disabled", "disabled");

            $(".usuario").attr("required", "required");
            $(".rut").attr("required", "required");
            $(".botones").addClass("d-none");
        }
    });

    $(".pdocrud-cancel-btn").click(function(){
        $(".usuario_col").addClass("d-none");
        $(".rut_col").addClass("d-none");

        $(".rut").attr("disabled", "disabled");
        $(".usuario").attr("disabled", "disabled");

        $(".usuario").attr("required", "required");
        $(".rut").attr("required", "required");
        $(".botones").addClass("d-none");
    });

    $(document).on("pdocrud_after_submission", function(event, obj, data) {
      $('.pdocrud_error').hide();
      $('.pdocrud_message').hide();

      if(data == "Datos erróneos"){
        Swal.fire({
            title: "Error!",
            text: "El usuario o la contraseña ingresada no coinciden",
            icon: "error",
            confirmButtonText: "Aceptar",
            allowOutsideClick: false
        });
        $(".rut").val("");
        $(".pdocrud-password").val("");
      } else if(data == "El usuario o la contraseña ingresada no coinciden"){
        Swal.fire({
            title: "Error!",
            text: "El usuario o la contraseña ingresada no coinciden",
            icon: "error",
            confirmButtonText: "Aceptar",
            allowOutsideClick: false
        });
        $(".rut").val("");
        $(".pdocrud-password").val("");
      } else if(data == "El usuario ingresado no existe"){
        Swal.fire({
            title: "Error!",
            text: "El usuario ingresado no existe",
            icon: "error",
            confirmButtonText: "Aceptar",
            allowOutsideClick: false
        });
        $(".usuario").val("");
        $(".pdocrud-password").val("");
      } else if(data == "El RUT ingresado no coincide"){
        Swal.fire({
            title: "Error!",
            text: "El RUT ingresado o la contraseña ingresada no coinciden",
            icon: "error",
            confirmButtonText: "Aceptar",
            allowOutsideClick: false
        });
        $(".rut").val("");
        $(".pdocrud-password").val("");
      } else {
        var json = JSON.parse(data);
        var token = json["tokenApi"];
        var tokenpolos = json["tokenApiPolos"];
       
        localStorage.setItem("tokenApiPolos", tokenpolos);
        localStorage.setItem("tokenApi", token);

        Swal.fire({
            title: "Genial!",
            text: "Bienvenido",
            icon: "success",
            confirmButtonText: "Aceptar",
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href="<?=$_ENV["BASE_URL"]?>/home/index";
            }
        });
      }
    });
</script>
</body>
</html>