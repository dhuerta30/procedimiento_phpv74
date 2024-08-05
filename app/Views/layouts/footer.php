<footer class="main-footer">
    <div class="float-right d-none d-sm-block">
        <b>Version</b> 3.2.0
    </div>
    <strong>Copyright &copy; <?php echo date('Y')?></strong> Todos los derechos reservados.
</footer>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- Bootstrap 4 -->
<script src="<?=$_ENV["BASE_URL"]?>theme/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?=$_ENV["BASE_URL"]?>theme/dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script>
$('#loader').show();
setTimeout(function() {
$('#loader').hide();
}, 1500);

function verificarSesion() {
    fetch('<?=$_ENV["BASE_URL"]?>home/comprobarSessionActiva')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'expired') {
                // Redirigir al usuario a la página de login si la sesión ha expirado
                Swal.fire({
                    title: 'Genial!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false
                }).then((result) => {
                    if(result.isConfirmed) {
                        window.location.href = '<?=$_ENV["BASE_URL"]?>login/salir';
                    }
                });
            }
        })
        .catch(error => console.error('Error al verificar la sesión:', error));
}

// Ejecutar la verificación cada 1 minuto (60000 ms)
setInterval(verificarSesion, 60000);
</script>
</body>

</html>