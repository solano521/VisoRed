
<?php
include("conexion.php"); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// lo que se muestra en el loggin principal, funciones 
$errorLoginMsg = "";
$logueado = isset($_SESSION['usuario']);

if (isset($_POST['login'])) {
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $passwordCheck = $_POST['password'];
    $rolElegido = $_POST['tipoAcceso'];

    $stmt = $conexion->prepare("SELECT nombre, password, rol FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        if (password_verify($passwordCheck, $usuario['password'])) {
            if ($usuario['rol'] == $rolElegido) {
                $_SESSION['usuario'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                $logueado = true;
                header("Location: index.php");
                exit();
            } else {
                $errorLoginMsg = "El rol seleccionado no corresponde a esta cuenta.";
            }
        } else {
            // Validación para cuentas antiguas en texto plano
            if ($passwordCheck === $usuario['password'] && $usuario['rol'] == $rolElegido) {
                $_SESSION['usuario'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                $logueado = true;
                header("Location: index.php");
                exit();
            }
            $errorLoginMsg = "Contraseña incorrecta.";
        }
    } else {
        $errorLoginMsg = "El correo no está registrado.";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
// si se cumple todo, te manda a el index, lo cual es esta parte del codigo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visored - Gestión de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --azul-acero: #4682B4;
            --blanco-humo: #F8F9FA;
            --verde-mate: #6B8E23;
            --alerta-rojo: #dc3545;
        }
        body { background-color: var(--blanco-humo); font-family: 'Segoe UI', sans-serif; }
        #pantalla-login {
            height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--azul-acero), #2c3e50);
            position: fixed; width: 100%; z-index: 2000;
        }
        .login-card {
            background: white; padding: 2.5rem; border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3); width: 100%; max-width: 420px;
        }
        #contenido-tienda { display: <?= $logueado ? 'block' : 'none' ?>; }
        .navbar-custom { background-color: var(--azul-acero); }
        .oculto { display: none !important; }
    </style>
</head>

<body>
    <div id="pantalla-login" style="display: <?= $logueado ? 'none' : 'flex' ?>;">
        <div class="login-card text-center">
            <h1 class="fw-bold mb-2" style="color: var(--azul-acero)">VISORED</h1>
            <p class="text-muted mb-4">Manejo de Inventario y Logística</p>
            
            <?php if(isset($_GET['registro'])): ?>
                <div class="alert alert-success small p-2">¡Cuenta creada con éxito! Ya puedes entrar.</div>
            <?php endif; ?>

            <?php if($errorLoginMsg != ""): ?>
                <div class="alert alert-danger small p-2"><?= $errorLoginMsg ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" placeholder="gmail" required>
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label small fw-bold">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="mb-4">
                    <label class="form-label d-block small fw-bold text-muted">Ingresar como:</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="tipoAcceso" id="tipoCliente" value="Cliente" checked>
                        <label class="btn btn-outline-primary" for="tipoCliente">Cliente</label>
                        <input type="radio" class="btn-check" name="tipoAcceso" id="tipoAdmin" value="Admin">
                        <label class="btn btn-outline-danger" for="tipoAdmin">Administrador</label>
                    </div>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">INICIAR SESIÓN</button>
                <hr>
                <p class="small text-muted">¿No tienes cuenta? <a href="crear_cuenta.php" class="fw-bold text-primary">Regístrate aquí</a></p>
            </form>
        </div>
    </div>
 
    <div id="contenido-tienda">
        <nav class="navbar navbar-expand-lg navbar-custom sticky-top shadow">
            <div class="container">
                <a class="navbar-brand fw-bold text-white fs-2" href="#" onclick="filtrar('populares')">VISORED</a>
                
                <div id="controles-admin" class="<?= ($_SESSION['rol'] ?? '') == 'Admin' ? '' : 'd-none' ?> ms-2">
                    <button class="btn btn-warning fw-bold shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#modalAdmin">
                        <i class="bi bi-plus-circle-fill"></i> Nuevo Producto
                    </button>
                    <button class="btn btn-info fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalLogistica">
                        <i class="bi bi-truck"></i> Logística
                    </button>
                </div>

                <div class="dropdown ms-3">
                    <button class="btn text-white fs-3 border-0" data-bs-toggle="dropdown"><i class="bi bi-list"></i></button>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item" href="#" onclick="filtrar('populares')">Productos más vendidos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="filtrar('lacteos')">Lácteos</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filtrar('limpieza')">Limpieza</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filtrar('refrescos')">Refrescos</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filtrar('sabritas')">Sabritas</a></li>
                    </ul>
                </div>

                <div class="d-flex flex-grow-1 mx-4">
    <div class="input-group">
        <input type="text" id="inputBuscador" class="form-control border-0 shadow-none" 
               placeholder="¿Qué buscas hoy?" onkeyup="ejecutarBusqueda()">
        
        <button class="btn btn-light" type="button" onclick="ejecutarBusqueda()">
            <i class="bi bi-search"></i>
        </button>
    </div>
</div>
                <div class="d-flex align-items-center text-white">
                    <span id="userDisplay" class="me-3 small fw-bold">
                        <?= isset($_SESSION['usuario']) ? (($_SESSION['rol']=='Admin' ? '<span class="badge bg-danger">ADMIN</span> ' : '<i class="bi bi-person-circle"></i> ') . $_SESSION['usuario']) : '' ?>
                    </span>
                    <button class="position-relative btn-cart-trigger me-3" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCarrito">
                        <i class="bi bi-cart3 fs-3"></i>
                        <span id="contador-carrito" class="cart-badge">0</span>
                    </button>
                    <a href="index.php?logout=1" class="btn btn-sm btn-outline-light">Salir</a>
                </div>
            </div>
        </nav>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCarrito">
            <div class="offcanvas-header bg-light">
                <h5 class="offcanvas-title fw-bold">Resumen de Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <div id="lista-carrito-detallada"></div>
                <div id="footer-carrito" class="mt-4 d-none">
                    <hr>
                    <div class="d-flex justify-content-between fs-4 fw-bold mb-3">
                        <span>Total:</span><span id="total-carrito-precio">$0.00</span>
                    </div>
                    <button class="btn btn-success w-100 py-2 fw-bold" onclick="confirmarCompra()">FINALIZAR PEDIDO</button>
                </div>
            </div>
        </div>

<div class="container my-5">
    <h3 id="titulo-seccion" class="mb-4 fw-bold">Productos mas vendidos</h3>
    
    <div class="row row-cols-2 row-cols-md-5 g-4" id="lista-productos">
        
        <div class="col item-producto" data-categoria="populares refrescos">
            <div class="card product-card p-2 shadow-sm">
                <span id="stock-badge-coca" class="badge bg-success stock-tag">Stock: 10</span>
                <div class="img-container">
                    <img src="https://m.media-amazon.com/images/I/51v8ny56pRL._SL1000_.jpg" class="img-fluid">
                </div>
                <div class="card-body p-2 text-center mt-auto">
                    <h6>Coca Cola 600ml</h6>
                    <p class="price-tag m-0">$18.00</p>
                    <input type="number" class="form-control form-control-sm my-2 mx-auto w-75" value="1" min="1">
                    <button class="btn btn-primary btn-sm w-100" onclick="agregarConStock(this, 'Coca Cola 600ml', 18.00, 'stock-badge-coca')">Agregar</button>
                </div>
            </div>
        </div>

        <div class="col item-producto" data-categoria="Limpieza"> 
    <div class="card product-card p-2 shadow-sm">
        <span id="stock-badge-zote" class="badge bg-success stock-tag">Stock: 15</span>
        <div class="img-container">
            <img src="URL_DE_TU_IMAGEN" class="img-fluid" style="height: 150px; object-fit: contain;">
        </div>
        <div class="card-body p-2 text-center mt-auto">
            <h6>jabon zote</h6>
            <p class="price-tag m-0">$20.00</p>
            <input type="number" class="form-control form-control-sm my-2 mx-auto w-75" value="1" min="1">
            <button class="btn btn-primary btn-sm w-100" onclick="agregarConStock(this, 'jabon zote', 20.00, 'stock-badge-jabon zote')">Agregar</button>
        </div>
    </div>
</div>

    <div class="modal fade" id="modalAdmin" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Gestión de Inventario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoProducto" onsubmit="adminAgregarProducto(event)">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre del Producto</label>
                            <input type="text" id="nuevoNombre" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Precio ($)</label>
                                <input type="number" step="0.5" id="nuevoPrecio" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Stock Inicial</label>
                                <input type="number" id="nuevoStock" class="form-control" value="20" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Categoría</label>
                                <select id="nuevaCategoria" class="form-select">
                                    <option value="sabritas">Sabritas</option>
                                    <option value="refrescos">Refrescos</option>
                                    <option value="lacteos">Lacteos</option>
                                    <option value="limpieza">Limpieza</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">URL de la Imagen</label>
                            <input type="url" id="nuevaImagen" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">REGISTRAR PRODUCTO</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLogistica" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-map-fill"></i> Logística de Entregas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <button class="btn btn-primary w-100 fw-bold shadow-sm" onclick="generarRutaOptimizada()">GENERAR RUTA DE HOY</button>
                    <div id="contenedor-ruta" class="mt-4 d-none text-start">
                        <div class="alert alert-secondary p-3">
                            <ul id="lista-ruta" class="list-group list-group-flush small"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toastNotificacion" class="toast border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body"><i class="bi bi-info-circle-fill me-2"></i><span id="mensaje-notificacion"></span></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-toast="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // TODA TU LÓGICA DE JAVASCRIPT SE MANTIENE INTACTA
        let inventarioReal = { "Coca Cola 600ml": 10, "Chetos Flaming Hot": 21 };
        let carrito = [];

        function agregarConStock(btn, nombre, precio, badgeId) {
            const cantPedida = parseInt(btn.previousElementSibling.value);
            const stockActual = inventarioReal[nombre] || 0;
            if (cantPedida > stockActual) {
                mostrarNotificacion(`No hay suficiente stock. Solo quedan ${stockActual} unidades.`, true);
                return;
            }
            inventarioReal[nombre] -= cantPedida;
            const nuevoStock = inventarioReal[nombre];
            const badge = document.getElementById(badgeId);
            badge.innerText = `Stock: ${nuevoStock}`;
            if (nuevoStock <= 3) {
                badge.className = "badge bg-danger stock-tag";
                mostrarNotificacion(`¡ALERTA! Stock crítico de ${nombre}: ${nuevoStock} pzas.`, true);
            } else if (nuevoStock <= 5) {
                badge.className = "badge bg-warning text-dark stock-tag";
            }
            agregarAlCarrito(nombre, precio, cantPedida);
        }

        function agregarAlCarrito(nombre, precio, cant) {
            const item = carrito.find(p => p.nombre === nombre);
            if (item) { item.cantidad += cant; item.subtotal = item.cantidad * precio; }
            else { carrito.push({ nombre, precio, cantidad: cant, subtotal: precio * cant }); }
            renderCarrito();
            mostrarNotificacion(`Agregaste ${cant} de ${nombre}`, false);
        }

        function renderCarrito() {
            const list = document.getElementById('lista-carrito-detallada');
            const foot = document.getElementById('footer-carrito');
            const badg = document.getElementById('contador-carrito');
            const totl = document.getElementById('total-carrito-precio');
            if (carrito.length === 0) {
                list.innerHTML = '<p class="text-center text-muted my-5">Carrito vacío</p>';
                foot.classList.add('d-none');
                badg.innerText = "0"; return;
            }
            let sumTotal = 0, sumCant = 0;
            list.innerHTML = "";
            carrito.forEach((p, idx) => {
                sumTotal += p.subtotal; sumCant += p.cantidad;
                list.innerHTML += `<div class="cart-item d-flex justify-content-between">
                    <div><h6 class="mb-0 fw-bold">${p.nombre}</h6><small>${p.cantidad} x $${p.precio.toFixed(2)}</small></div>
                    <div class="text-end"><span class="fw-bold d-block">$${p.subtotal.toFixed(2)}</span><button class="btn btn-sm text-danger p-0" onclick="eliminar(${idx})">Quitar</button></div>
                </div>`;
            });
            badg.innerText = sumCant; totl.innerText = `$${sumTotal.toFixed(2)}`;
            foot.classList.remove('d-none');
        }

        function eliminar(i) { carrito.splice(i, 1); renderCarrito(); }

        function confirmarCompra() {
            alert(`¡Pedido Confirmado!`);
            carrito = []; renderCarrito();
            bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasCarrito')).hide();
        }

        function adminAgregarProducto(e) {
            e.preventDefault();
            const nombre = document.getElementById('nuevoNombre').value;
            const precio = parseFloat(document.getElementById('nuevoPrecio').value);
            const stock = parseInt(document.getElementById('nuevoStock').value);
            const cat = document.getElementById('nuevaCategoria').value;
            const img = document.getElementById('nuevaImagen').value;
            const idBadge = "badge-" + Date.now();
            inventarioReal[nombre] = stock;
            const contenedor = document.getElementById('lista-productos');
            const nuevoCol = document.createElement('div');
            nuevoCol.className = "col item-producto";
            nuevoCol.setAttribute('data-categoria', cat);
            nuevoCol.innerHTML = `
                <div class="card product-card p-2 shadow-sm">
                    <span id="${idBadge}" class="badge bg-success stock-tag">Stock: ${stock}</span>
                    <div class="img-container"><img src="${img}"></div>
                    <div class="card-body p-2 text-center mt-auto">
                        <h6>${nombre}</h6><p class="price-tag m-0">$${precio.toFixed(2)}</p>
                        <input type="number" class="form-control form-control-sm my-2 mx-auto w-75" value="1" min="1">
                        <button class="btn btn-primary btn-sm w-100" onclick="agregarConStock(this, '${nombre}', ${precio}, '${idBadge}')">Agregar</button>
                    </div>
                </div>`;
            contenedor.appendChild(nuevoCol);
            document.getElementById('formNuevoProducto').reset();
            bootstrap.Modal.getInstance(document.getElementById('modalAdmin')).hide();
            mostrarNotificacion(`Producto registrado con stock de ${stock}`, false);
        }

        function mostrarNotificacion(msg, esAlerta) {
            const toastElement = document.getElementById('toastNotificacion');
            toastElement.className = esAlerta ? "toast toast-alerta border-0" : "toast toast-azul border-0";
            document.getElementById('mensaje-notificacion').innerText = msg;
            new bootstrap.Toast(toastElement).show();
        }

        function filtrar(c) {
            const p = document.querySelectorAll('.item-producto');
            document.getElementById('titulo-seccion').innerText = c === 'populares' ? '' : 'Categoría: ' + c.toUpperCase();
            p.forEach(i => {
                if (i.getAttribute('data-categoria').includes(c)) i.classList.remove('oculto');
                else i.classList.add('oculto');
            });
        }

        function ejecutarBusqueda() {
            const t = document.getElementById('inputBuscador').value.toLowerCase().trim();
            const p = document.querySelectorAll('.item-producto');
            p.forEach(i => {
                const nombre = i.querySelector('h6').innerText.toLowerCase();
                if (nombre.includes(t)) i.classList.remove('oculto');
                else i.classList.add('oculto');
            });
        }

        function generarRutaOptimizada() {
            const destinos = ["Tienda Central", "Sucursal Norte", "Cliente VIP - Centro", "Punto Entrega Sur"];
            const lista = document.getElementById('lista-ruta');
            lista.innerHTML = "";
            destinos.forEach((d, i) => {
                lista.innerHTML += `<li class="list-group-item"><i class="bi bi-geo-alt-fill text-danger"></i> ${i+1}. ${d}</li>`;
            });
            document.getElementById('contenedor-ruta').classList.remove('d-none');
        }
    </script>
</body>
</html>
