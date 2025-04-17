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
function generarToken(callback) {
    $.ajax({
        type: "POST",
        url: "<?=$_ENV['BASE_URL']?>Polos/generarToken",
        dataType: "json",
        success: function(data) {
            var token = data["data"];
            localStorage.setItem("tokenApiPolos", token);
            if (typeof callback === "function") {
                callback();
            }
        }
    });
}

var token = localStorage.getItem("tokenApiPolos");

$.ajax({
    type: "POST",
    url: "<?=$_ENV["BASE_URL"]?>Polos/obtener_datos_polos",
    data: {
        token: token
    },
    dataType: "json",
    success: function(data){
        $("#pdocrud_search_btn").click();
    }
});

</script>
<?php require "layouts/footer.php"; ?>
