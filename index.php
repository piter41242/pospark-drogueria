<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Droguería | Acceso al Sistema</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="Resources/bootstrap/css/bootstrap.min.css">
    <!-- Iconos Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="Resources/sweetalert/css/sweetalert2.css">
    <link rel="stylesheet" href="Resources/css/login.css">


</head>

<body>

    <div class="login-card">

        <!-- LADO IZQUIERDO (Branding) -->
        <div class="login-image">
            <!-- Pon tu logo de la empresa aquí (el mismo que pusiste en el menú) -->
            <img src="Resources/img/empresa.jpg" style=" border-radius: 50%;" alt="Logo Empresa">
            <h2>SISTEMA DROGUERÍA</h2>
            <p>Gestión de inventarios, ventas y facturación electrónica para tu farmacia.</p>
        </div>

        <!-- LADO DERECHO (Formulario) -->
        <div class="login-form">
            <h3>Bienvenido</h3>
            <p class="subtitle">Ingresa tus credenciales para acceder al sistema.</p>

            <form method="POST" action="Controlador/controlador_sesion.php">

                <div class="input-group-custom">
                    <i class="bi bi-person-fill"></i>
                    <input type="text" id="usuario" name="usuario" placeholder="Usuario" required autocomplete="off">
                </div>

                <div class="input-group-custom">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" id="clave" name="clave" placeholder="Contraseña" required>
                </div>

                <button type="submit" class="btn-login">
                    INGRESAR AL SISTEMA <i class="bi bi-arrow-right ms-2"></i>
                </button>

            </form>
        </div>

    </div>

    <script src="Resources/sweetalert/js/sweetalert2.all.min.js"></script>
    <script src="Resources/js/jquery-latest.js"></script>
    <script>
        window.onload = alert1();
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
            // Lógica de ejemplo
            $estado_pago = false; // Esto vendría de tu base de datos
            if (!$estado_pago) {
                header("Location: nopago.php");
                exit();
            }
        });

        function alert1() {
            var vars = window.location.search;
            if (!vars) return;

            const ps = new URLSearchParams(vars);
            var valor = ps.get('ps');

            if (valor == "bloqueo") {
                Swal.fire({
                    type: 'error',
                    title: 'Usuario Bloqueado',
                    text: 'Contacte al administrador.'
                });
            } else if (valor == "login") {
                Swal.fire({
                    type: 'error',
                    title: 'Error de Acceso',
                    text: 'Usuario o clave incorrectos.'
                });
            } else if (valor == "sede") {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'El usuario no tiene una sede asignada.'
                });
            } else if (valor == "no_autorizado") {
                Swal.fire({
                    type: 'warning',
                    title: 'Acceso Denegado',
                    text: 'No tienes permisos para acceder a esa ruta.',
                    confirmButtonText: 'Entendido'
                });
            }
        }
    </script>
</body>

</html>