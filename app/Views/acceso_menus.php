
        <?php require "layouts/header.php"; ?>
        <?php require "layouts/sidebar.php"; ?>
        <link href="<?=$_ENV["BASE_URL"]?>css/intro.css" rel="stylesheet">
        <div class="content-wrapper">
            <section class="content">
                <div class="card mt-4">
                    <div class="card-body">

                        <div class="row procedimiento">
                            <div class="col-md-12">
                                <h5>Accesos Usuarios a Menus</h5>
                                <hr>

                                <div class="row no-gutters">
                                    <div class="col-md-3 m-auto">

                                        <ul class="list-none">
                                            <li>
                                                <input type="checkbox" value="select-all" name="pdocrud_select_all" class="pdocrud-select-all">
                                                <span>Marcar Todos / Desmarcar Todos</span>
                                            </li>
                                        </ul>

                                        <div class="menu_list" data-intro='Aquí deberás seleccionar los menús que podrá visualizar cada usuario antes de guardar.'>
                                            <ul class="list-none">
                                            <?php foreach ($menu as $item): ?>                                        
                                                <?php
                                                    // Obtiene submenús
                                                    $submenus = App\Controllers\HomeController::submenuDB($item['id_menu']);
                                                    $tieneSubmenus = ($item["submenu"] == "Si");
                                                    $subMenuAbierto = false;

                                                    // Verifica si algún submenú está activo
                                                    foreach ($submenus as $submenu) {
                                                        if (strpos($current_url, $submenu['url_submenu']) !== false) {
                                                            $subMenuAbierto = true;
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <li>
                                                    <?php if ($tieneSubmenus): ?>
                                                        <input type="checkbox" id="<?= $item['id_menu'] ?>" class="menu-checkbox" data-type="menu">
                                                        <span><i class="<?= $item['icono_menu'] ?>"></i> <?= $item['nombre_menu'] ?></span>
                                                        <ul class="list-none">
                                                            <?php foreach ($submenus as $submenu): ?>
                                                                <li>
                                                                    <input type="checkbox" id="<?= $submenu['id_submenu'] ?>" class="submenu-checkbox" data-type="submenu" data-parent="<?= $item['id_menu'] ?>">
                                                                    <span><i class="<?= $submenu['icono_submenu'] ?>"></i> <?= $submenu['nombre_submenu'] ?></span>
                                                                </li>                                                                  
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <input type="checkbox" id="<?= $item['id_menu'] ?>" class="menu-checkbox" data-type="menu">
                                                        <span><i class="<?= $item['icono_menu'] ?>"></i> <?= $item['nombre_menu'] ?></span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>

                                            </ul>
                                        </div>

                                    </div>
                                    <div class="col-md-8">
                                        <?=$render;?>
                                    </div>
                                </div>

                                <div class="cargar_modal"></div>
                            
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
        <script src="<?=$_ENV["BASE_URL"]?>js/intro.js"></script>
        <script>
            function refrechMenu(){
                $.ajax({
                    type: "POST",
                    url: "<?=$_ENV["BASE_URL"]?>home/refrescarMenu",
                    dataType: "json",
                    success: function(response){
                        $('.menu_generator').html(response);
                    }
                });
            }

            $(document).ready(function () {
                $('.pdocrud-select-all').change(function () {
                    $('.menu-checkbox, .submenu-checkbox').prop('checked', $(this).prop('checked'));
                });

                $(document).on('change', '.select-all', function () {
                    $('.menu-checkbox-pr, .submenu-checkbox-pr').prop('checked', $(this).prop('checked'));
                });
            
                $(document).on('click', '.asignar_menu_usuario', function () {
                    var userId = $(this).data('id');
                    var checkboxValues = {};

                    // Iterar sobre las casillas marcadas y recopilar datos
                    $('.menu-checkbox, .submenu-checkbox, .menu-checkbox-pr, .submenu-checkbox-pr').each(function () {
                        var checkboxId = $(this).attr('id');
                        var isChecked = $(this).prop('checked');
                        var isSubMenu = $(this).hasClass('submenu-checkbox') || $(this).hasClass('submenu-checkbox-pr');
                        var parentMenuId = isSubMenu ? $(this).data('parent') : null;

                        
                            if (isSubMenu && parentMenuId) {
                                // Si es un submenú, asocia el submenú al menú principal
                                if (!checkboxValues[parentMenuId]) {
                                    checkboxValues[parentMenuId] = {
                                        checked: isChecked, // Puedes cambiarlo a isChecked si deseas que los submenús también tengan su propio estado checked
                                        menuId: parentMenuId,
                                        submenuIds: [] // Almacena los IDs de submenús asociados al menú principal
                                    };
                                }
                                checkboxValues[parentMenuId].submenuIds.push({ id: checkboxId, checked: isChecked });
                            } else {
                                // Si es un menú, almacénalo en checkboxValues
                                checkboxValues[checkboxId] = {
                                    checked: isChecked,
                                    menuId: checkboxId,
                                    submenuIds: [] // Un menú puede tener varios submenús asociados
                                };
                            }
                        
                    });

                    // Obtener IDs de menús y submenús
                    var allMenus = Object.values(checkboxValues);

                    var selectedMenus = Object.values(checkboxValues).filter(function (checkbox) {
                        return checkbox.checked;
                    });

                    if (selectedMenus.length > 0) {
                        //Envía datos al servidor usando Ajax
                        $.ajax({
                            url: "<?=$_ENV["BASE_URL"]?>home/asignar_menus_usuario",
                            type: 'POST',
                            dataType: "json",
                            data: {
                                userId: userId,
                                selectedMenus: allMenus
                            },
                            beforeSend: function() {
                                $("#pdocrud-ajax-loader").show();
                            },
                            success: function (response) {
                                $("#pdocrud-ajax-loader").hide();
                                if(response['success']){
                                    $('.pdocrud-select-all').prop('checked', false);
                                    $('.menu-checkbox').prop('checked', false);
                                    $('.submenu-checkbox').prop('checked', false);
                                    $('#menus').modal('hide');
                                    refrechMenu();
                                    Swal.fire({
                                        title: "Genial!",
                                        text: response['success'],
                                        icon: "success",
                                        confirmButtonText: "Aceptar"
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Lo siento!",
                                        text: response['error'],
                                        icon: "error",
                                        confirmButtonText: "Aceptar"
                                    });
                                }
                            }
                        });
                    } else {
                        introJs().setOptions({
                            doneLabel: 'Finalizado', // Personaliza el texto del botón "Done"
                            showStepNumbers: false,    // Puedes ocultar los números de paso si lo deseas
                            showProgress: true
                        }).start();
                    }
                });


                $(document).on("click", ".ver_menu_usuario", function(){
                    var userId = $(this).data('id');

                    $.ajax({
                        type: "POST",
                        url: "<?=$_ENV["BASE_URL"]?>home/obtener_menu_usuario",
                        dataType: "html",
                        data: {
                            userId: userId
                        },
                        beforeSend: function() {
                            $("#pdocrud-ajax-loader").show();
                        },
                        success: function(data){
                            $("#pdocrud-ajax-loader").hide();
                            $('.cargar_modal').html(data);
                            $('#menus').modal('show');
                        }
                    });
                });

                $(document).on('hidden.bs.modal', '#menus', function () {
                    $('.cargar_modal').empty();
                });
                

            });
        </script>
        <?php require "layouts/footer.php"; ?>