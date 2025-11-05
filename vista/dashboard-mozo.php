<?php
// 🔹 Cargar clases
require_once '../modelo/Mesa.php';
require_once '../modelo/Plato.php';
require_once '../modelo/Pedido.php';
require_once '../modelo/PedidoDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 Validar sesión y rol
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'mozo') {
    header("Location: ./login.php");
    exit();
}

// 🔸 Inicializar datos si es necesario
PedidoDAO::inicializarMesas();
PedidoDAO::inicializarMenu();

// 🔸 Obtener datos desde la base de datos
$mesas = PedidoDAO::getTodasLasMesas();
$pedidos = PedidoDAO::obtenerTodosLosPedidosDB();

// 🔹 Variables para los indicadores del dashboard
$mesasOcupadas = 0;
$ingresoTotal = 0;
$conteoPlatos = [];

// 🔸 Calcular estadísticas
foreach ($pedidos as $pedido) {
    if ($pedido['estado_pedido'] === 'abierto' || $pedido['estado_pedido'] === 'cerrado') {
        $ingresoTotal += $pedido['total'] ?? 0;

        $detalles = PedidoDAO::obtenerDetallesPedidoDB($pedido['id_pedido']);

        foreach ($detalles as $detalle) {
            $nombrePlato = $detalle['plato_nombre'];
            $cantidad = $detalle['cantidad'];

            if (!isset($conteoPlatos[$nombrePlato])) {
                $conteoPlatos[$nombrePlato] = 0;
            }
            $conteoPlatos[$nombrePlato] += $cantidad;
        }
    }
}

// 🔸 Contar mesas ocupadas
foreach ($mesas as $mesa) {
    if ($mesa->getEstado() === 'ocupada') {
        $mesasOcupadas++;
    }
}

// 🔸 Plato más pedido
$platoMasPedido = !empty($conteoPlatos)
    ? array_search(max($conteoPlatos), $conteoPlatos)
    : 'Ninguno aún';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Mozo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --orange-primary: #ff8c00;
            --orange-dark: #e67e00;
            --orange-light: #fff4e6;
            --bg-light: #fffaf5;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            margin: 0;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, var(--orange-primary), var(--orange-dark));
            color: white;
            padding: 2rem 1rem;
            position: fixed;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.15);
        }

        .sidebar h3 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
        }

        .nav-link {
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            padding: 0.7rem 1rem;
            border-radius: 10px;
            transition: all 0.3s;
            margin-bottom: 0.5rem;
            text-decoration: none;
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.25);
        }

        .logout {
            background: #fff;
            color: var(--orange-dark);
            text-align: center;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.7rem 1rem;
            text-decoration: none;
            transition: all 0.3s;
        }

        .logout:hover {
            background: var(--orange-light);
        }

        .main-content {
            flex: 1;
            margin-left: 240px;
            height: 100vh;
            overflow: hidden;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: var(--bg-light);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module">
        let codigo = null;
        const crearPlato = document.getElementById('crearPlato');
        const formularioModalPlato = document.getElementById('formularioModalPlato');
        const formulario = document.getElementById('formularioPlato');
        const btnGuardar = document.getElementById('btnGuardar');
        const botonesEditar = document.querySelectorAll('button[data-bs-modal="edit"]');
        const botonesEliminar = document.querySelectorAll('button[data-bs-modal="delete"]');

        const modalFormulario = new bootstrap.Modal(formularioModalPlato, {
            keyboard: false
        });

        crearPlato.addEventListener('click', () => {
            codigo = null;
            btnGuardar.disabled = false;
            formularioModalPlato.querySelector('#formularioModalPlatoLabel').textContent = 'Crear';
            formularioModalPlato.querySelector('#codigo').value = '';
            formularioModalPlato.querySelector('#nombre').value = '';
            formularioModalPlato.querySelector('#categoria').value = '';
            formularioModalPlato.querySelector('#precio').value = '';
            formularioModalPlato.querySelector('#estado').value = 'activo';
            modalFormulario.show();
        });

        btnGuardar.addEventListener('click', () => {
            formulario.requestSubmit();
        });

        formulario.addEventListener('submit', async (event) => {
            event.preventDefault();

            const campos = ['codigo', 'nombre', 'categoria', 'precio', 'estado'];
            const datos = {};
            let valido = true;

            campos.forEach(id => {
                const campo = document.getElementById(id);
                const valor = campo.value.trim();

                if (!valor) {
                    campo.classList.add('is-invalid');
                    valido = false;
                } else {
                    campo.classList.remove('is-invalid');
                    datos[id] = valor;
                }
            });

            if (!valido) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Algunos campos son inválidos'
                });
                return;
            }

            // Agregamos la acción esperada por el backend
            datos.accion = 'crear';
            if (codigo) {
                datos.accion = 'editar';
                datos.id_plato_original = codigo;
            }

            // Convertimos a formato x-www-form-urlencoded
            const formData = new URLSearchParams();
            for (const clave in datos) {
                formData.append(clave, datos[clave]);
            }

            btnGuardar.disabled = true;

            try {
                const respuesta = await fetch('../controlador/platoControlador.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                });

                if (!respuesta.ok) throw new Error(`Error ${respuesta.status}`);
                const resultado = await respuesta.json();

                if (resultado.status !== 'error') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado',
                        text: 'Se ha guardado correctamente el plato',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resultado.message ?? 'Ocurrió un error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (error) {
                console.error('Error al enviar el formulario:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error inesperado'
                });
            } finally {
                btnGuardar.disabled = false;
            }
        });

        botonesEditar.forEach(boton => {
            boton.addEventListener('click', () => {
                codigo = boton.dataset.id;
                const nombre = boton.dataset.nombre;
                const categoria = boton.dataset.categoria;
                const precio = boton.dataset.precio;
                const estado = boton.dataset.estado;

                btnGuardar.disabled = false;
                formularioModalPlato.querySelector('#formularioModalPlatoLabel').textContent = 'Editar ' + codigo;
                formularioModalPlato.querySelector('#codigo').value = codigo;
                formularioModalPlato.querySelector('#nombre').value = nombre;
                formularioModalPlato.querySelector('#categoria').value = categoria;
                formularioModalPlato.querySelector('#precio').value = precio;
                formularioModalPlato.querySelector('#estado').value = estado;
                modalFormulario.show();
            });
        });

        botonesEliminar.forEach(boton => {
            boton.addEventListener('click', async () => {
                const codigo = boton.dataset.id;
                const confirm = await Swal.fire({
                    title: '¿Eliminar plato?',
                    text: "Está seguro que desea eliminar el plato #" + codigo + ". Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });

                if (!confirm.isConfirmed) return;

                try {
                    const params = new URLSearchParams();
                    params.append('accion', 'eliminar');
                    params.append('id', codigo);

                    const respuesta = await fetch(`../controlador/platoControlador.php?${params.toString()}`, {
                        method: 'GET'
                    });

                    if (!respuesta.ok) throw new Error(`Error ${respuesta.status}`);
                    const resultado = await respuesta.json();

                    if (resultado.status !== 'error') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'Se ha eliminado correctamente el plato',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: resultado.message ?? 'Ocurrió un error',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    console.error('Error al enviar la solicitud:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error inesperado'
                    });
                }

            });
        });
    </script>
</head>

<body>
    <div class="sidebar">
        <div>
            <h3><i class="bi bi-cup-hot"></i> Restaurante</h3>
            <a href="dashboardInicio.php" class="nav-link active">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a href="mesas.php" class="nav-link">
                <i class="bi bi-grid-3x3-gap"></i> Mesas
            </a>
            <a href="historialPedidos.php" class="nav-link">
                <i class="bi bi-clipboard-check"></i> Pedidos
            </a>
        </div>
        <a href="../controlador/logout.php" class="logout">
            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
        </a>
    </div>

    <!-- Contenido dinámico -->
    <?= $contenido ?? '' ?>
</body>

</html>