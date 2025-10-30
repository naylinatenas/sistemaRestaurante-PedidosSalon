<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Restaurante</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/dashboard_style.css">
  <style>
    body {
      margin: 0;
      display: flex;
      height: 100vh;
      font-family: 'Inter', sans-serif;
    }

    .sidebar {
      width: 230px;
      background: #2c3e50;
      color: white;
      padding: 15px;
    }

    .sidebar a {
      display: block;
      color: white;
      text-decoration: none;
      margin: 10px 0;
    }

    .sidebar a:hover {
      background: #34495e;
      padding-left: 10px;
      transition: all 0.3s;
    }

    .main-content {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      background: #f5f6fa;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <h3><i class="fa-solid fa-utensils"></i> Restaurante</h3>
      <a href="#" onclick="cargarPagina(event, 'dashboard.php')" class="nav-link active">
        <i class="fa-solid fa-chart-line"></i> Inicio
      </a>
      <a href="#" onclick="cargarPagina(event, 'mesas.php')" class="nav-link">
        <i class="fa-solid fa-table"></i> Mesas
      </a>
      <a href="#" onclick="cargarPagina(event, 'historialPedidos.php')" class="nav-link">
        <i class="fa-solid fa-receipt"></i> Pedidos
      </a>
      <a href="#" onclick="cargarPagina(event, 'reportes.php')" class="nav-link">
        <i class="fa-solid fa-chart-bar"></i> Reportes
      </a>
      <a href="#" onclick="cargarPagina(event, 'perfil.php')" class="nav-link">
        <i class="fa-solid fa-user"></i> Perfil
      </a>
    </div>

    <form action="../controlador/logout.php" method="post">
      <button type="submit" class="btn btn-danger w-100 mt-3">
        <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
      </button>
    </form>
  </div>

  <!-- Contenido principal -->
  <div class="main-content" id="contenido">
    <!-- Aquí se cargará automáticamente el dashboard -->
  </div>

  <script>
    // ✅ Función para cargar páginas dinámicamente
    function cargarPagina(event, url) {
      if (event) event.preventDefault();
      fetch(url)
        .then(response => {
          if (!response.ok) throw new Error("Error al cargar " + url);
          return response.text();
        })
        .then(html => {
          document.getElementById('contenido').innerHTML = html;
        })
        .catch(err => {
          document.getElementById('contenido').innerHTML = "<p>Error al cargar el contenido.</p>";
          console.error(err);
        });
    }

    // ✅ Cargar el dashboard automáticamente al iniciar
    window.onload = function() {
      cargarPagina(null, 'dashboard.php');
    };
  </script>
</body>
</html>
