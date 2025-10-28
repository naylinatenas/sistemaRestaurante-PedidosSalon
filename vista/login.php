<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Restaurante Grupo 7</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #ff914d, #ffde59);
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            background-color: #fff;
            padding: 2rem;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-custom {
            background-color: #ff914d;
            color: white;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background-color: #ff7b00;
        }

        .login-icon {
            font-size: 3rem;
            color: #ff914d;
            margin-bottom: 1rem;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card text-center">
                <i class="fa-solid fa-utensils login-icon"></i>
                <h4 class="mb-3">Sistema de Restaurante</h4>
                <h5 class="text-muted mb-4">Iniciar Sesión</h5>

                <form method="POST" action="../controlador/loginControlador.php">
                    <div class="mb-3 text-start">
                        <label for="correo" class="form-label"><i class="fa-solid fa-envelope me-2"></i>Correo</label>
                        <input type="email" name="correo" id="correo" class="form-control" placeholder="Ingrese su correo" required>
                    </div>
                    <div class="mb-4 text-start">
                        <label for="clave" class="form-label"><i class="fa-solid fa-lock me-2"></i>Contraseña</label>
                        <input type="password" name="clave" id="clave" class="form-control" placeholder="Ingrese su contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Ingresar
                    </button>
                </form>

                <?php if (isset($_GET['error'])): ?>
                    <div class="error mt-3">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        Correo o contraseña incorrectos
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
