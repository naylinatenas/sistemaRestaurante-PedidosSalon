# 🍽️ Sistema de Restaurante (Pedidos en Salón)

## 📖 Descripción General
Sistema para gestionar pedidos en un restaurante, reemplazando las anotaciones en papel.  
Permite controlar qué mesa pidió qué platos y calcular el total a pagar.

## 👥 Usuarios del Sistema
- **Administrador:** gestiona platos y precios.  
- **Mozo:** gestiona los pedidos de las mesas.

## 🗃️ Tablas Principales
- **usuario** (`id_usuario`, `nombre`, `correo`, `clave`, `rol [admin, mozo]`, `estado`)
- **mesa** (`id_mesa`, `numero_mesa`, `estado_mesa [libre/ocupada/limpiando]`)
- **plato** (`id_plato`, `nombre`, `categoria`, `precio`, `estado`)
- **pedido** (`id_pedido`, `mesa_id`, `mozo_id`, `hora_inicio`, `hora_cierre`, `total`, `estado_pedido [abierto/cerrado]`)
- **detalle_pedido** (`id_detalle`, `pedido_id`, `plato_id`, `cantidad`, `subtotal`)

## ⚙️ Casos de Uso
- Abrir pedido para una mesa (mesa → **ocupada**).  
- Agregar platos al pedido (tipo carrito).  
- Calcular total automáticamente.  
- Cerrar pedido (pedido → **cerrado**, mesa → **limpiando**).

## 🖥️ Pantallas Principales
- **Login**
- **Dashboard:**
  - Mesas ocupadas actualmente  
  - Ingreso total del día  
  - Plato más pedido del día
- **CRUD de Platos**
- **Gestión de Mesas:** vista general para abrir/cerrar pedidos.  
- **Pedido Actual:** agregar platos y visualizar total.

## 🚫 Reglas Especiales
- No se puede cerrar un pedido sin al menos un plato.  
- Una mesa no puede abrir un nuevo pedido si tiene uno abierto.  
- El mozo **no puede** modificar precios.

## 🪪Credenciales de ingreso
### Admin:
-correo:admin@restaurante.com 
-clave: admin123

### Mozo 01:
-correo:Alonso@restaurante.com 
-clave: mozo123

### Mozo 02:
-correo:maria@restaurante.com
-clave: mozo123

📌 **Desarrollado por el Grupo 7**
-Acosta Plasencia, Naylin 
-Chuquipoma Medina, Sthefany
-Mantilla Sanchez, Elsa

**Acceso al Sistema**
  Sistema Web: https://sistemarestaurante-pedidosalon.xo.je
  Repositorio: https://github.com/naylinatenas/sistemaRestaurante-PedidosSalon