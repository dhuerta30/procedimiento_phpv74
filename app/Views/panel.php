
<?php require "layouts/header.php"; ?>
<?php require "layouts/sidebar.php"; ?>
<style>
#loader img {
    position: absolute;
    left: 50%;
    top: 50%;
}
</style>
<div class="content-wrapper">
    <section class="content">
        <div class="card mt-4">
            <div class="card-body">

                <?php
                    $current_url = $_SERVER['REQUEST_URI'];
                    $id_sesion_usuario = $_SESSION["usuario"][0]["id"];
                    $menu = App\Controllers\HomeController::obtener_menu_por_id_usuario($id_sesion_usuario);
                ?>

                <div class="row mb-4">
                    <div class="col-md-12 border rounded-3 p-5 bg-secondary">
                        <h1 class="display-5 fw-bold">Bienvenidos</h1>
                        <h3> Sistema de Procedimientos del Hospital San Jose de Melipilla</h3>
                        <p class="col-md-8 fs-4">Usuario conectado: <?=$_SESSION["usuario"][0]["rut"]?></p>       
                    </div>
                </div>

                <div class="row align-items-md-stretch">
                <?php foreach ($menu as $item): ?>
                    <?php if ($item["visibilidad_menu"] != "Ocultar" && $item["nombre_menu"] != "Salir" && $item["nombre_menu"] != "Perfil" && $item["nombre_menu"] != "usuarios" && $item["nombre_menu"] != "Home" && $item["nombre_menu"] != "Acceso Menus" && $item["nombre_menu"] != "Respalda tus Datos" && $item["nombre_menu"] != "Mantenedores" && $item["nombre_menu"] != "Mantenedor Menu" && $item["nombre_menu"] != "Panel Principal"): ?>
                            <?php
                                $submenus = App\Controllers\HomeController::Obtener_submenu_por_id_menu($item['id_menu'], $id_sesion_usuario);
                                $tieneSubmenus = ($item["submenu"] == "Si");
                                $subMenuAbierto = false;

                                foreach ($submenus as $submenu) {
                                    if (strpos($current_url, $submenu['url_submenu']) !== false) {
                                        $subMenuAbierto = true;
                                        break;
                                    }
                                }
                            ?>

                            <div class="col-xl-3 mb-3">
                                <div class="h-100 p-5 border rounded-3 bg-info">
                                    <h6><?= $item['nombre_menu'] ?></h6>
                                    <?php if ($tieneSubmenus): ?>
                                        <?php foreach ($submenus as $submenu): ?>
                                            <?php if($submenu["visibilidad_submenu"] != "Ocultar"): ?>
                                                <a class="btn btn-dark mt-2" href="<?= rtrim($_ENV["BASE_URL"], '/') . $submenu['url_submenu'] ?>" role="button">
                                                    Ir a <?= $submenu['nombre_submenu'] ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <a class="btn btn-dark mt-2" href="<?= rtrim($_ENV["BASE_URL"], '/') . $item['url_menu'] ?>" role="button">
                                            Ir a <?= $item['nombre_menu'] ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>


            </div>
        </div>
    </section>
</div>
<script src="<?=$_ENV["BASE_URL"]?>app/libs/script/js/jquery.min.js"></script>
<div id="pdocrud-ajax-loader">
    <img width="300" src="<?=$_ENV["BASE_URL"]?>app/libs/script/images/ajax-loader.gif" class="pdocrud-img-ajax-loader"/>
</div>
<?php require "layouts/footer.php"; ?>