<?php
/**
 * POS Interface Template
 *
 * WUPOS Interface Template - EXACTA replica del wireframe wireframe-wupos-pos-final.html
 *
 * @package WUPOS
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wupos-pos-system <?php echo isset($atts['class']) ? esc_attr($atts['class']) : ''; ?>" id="wupos-pos-system">
    <!-- Loading Screen -->
    <div class="wupos-loading" id="wupos-loading">
        <div class="loading-content">
            <div class="wupos-logo">
                <h2><?php _e('WUPOS', 'wupos'); ?></h2>
                <p><?php _e('WooCommerce Ultimate Point of Sale', 'wupos'); ?></p>
            </div>
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
            <p class="loading-text"><?php _e('Initializing POS System...', 'wupos'); ?></p>
        </div>
    </div>
    
    <!-- Main POS Interface - WIREFRAME EXACT REPLICA -->
    <div class="wupos-app" id="wupos-pos-interface" style="display: none;">
        <!-- Header con cliente a la derecha -->
        <header class="wupos-header">
            <div class="wupos-logo">
                <i class="fas fa-cash-register"></i>
                WUPOS
            </div>
            <div class="wupos-search">
                <input type="text" placeholder="<?php _e('Buscar productos...', 'wupos'); ?>">
            </div>
            <div class="wupos-selectors-header">
                <div class="wupos-selector-header-group">
                    <span class="wupos-header-label"><?php _e('Cliente:', 'wupos'); ?></span>
                    <input type="text" class="wupos-customer-search" placeholder="<?php _e('Buscar cliente...', 'wupos'); ?>">
                </div>
                <div class="wupos-selector-header-group">
                    <span class="wupos-header-label"><?php _e('Usuario:', 'wupos'); ?></span>
                    <select class="wupos-header-select">
                        <?php $current_user = wp_get_current_user(); ?>
                        <option><?php echo esc_html($current_user->display_name); ?> - Caja 01</option>
                        <option>Vendedor - Caja 02</option>
                        <option>Supervisor - Caja 03</option>
                    </select>
                </div>
                <a href="<?php echo admin_url(); ?>" class="wupos-admin-btn" title="<?php _e('Ir al panel de administración', 'wupos'); ?>">
                    <i class="fas fa-cog"></i>
                    <span><?php _e('Admin', 'wupos'); ?></span>
                </a>
            </div>
        </header>

        <!-- Sidebar de navegación -->
        <aside class="wupos-sidebar">
            <button class="wupos-category-btn active" title="<?php _e('Punto de Venta', 'wupos'); ?>">
                <div class="wupos-category-icon"><i class="fas fa-cash-register"></i></div>
                <div class="wupos-category-text"><?php _e('POS', 'wupos'); ?></div>
            </button>
            <button class="wupos-category-btn" title="<?php _e('Gestión de Productos', 'wupos'); ?>">
                <div class="wupos-category-icon"><i class="fas fa-box"></i></div>
                <div class="wupos-category-text"><?php _e('Productos', 'wupos'); ?></div>
            </button>
            <button class="wupos-category-btn" title="<?php _e('Gestión de Órdenes', 'wupos'); ?>">
                <div class="wupos-category-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="wupos-category-text"><?php _e('Órdenes', 'wupos'); ?></div>
            </button>
            <button class="wupos-category-btn" title="<?php _e('Gestión de Clientes', 'wupos'); ?>">
                <div class="wupos-category-icon"><i class="fas fa-users"></i></div>
                <div class="wupos-category-text"><?php _e('Clientes', 'wupos'); ?></div>
            </button>
            <button class="wupos-category-btn" title="<?php _e('Reportes y Estadísticas', 'wupos'); ?>">
                <div class="wupos-category-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="wupos-category-text"><?php _e('Reportes', 'wupos'); ?></div>
            </button>
        </aside>

        <!-- Splitter redimensionable -->
        <div class="wupos-splitter" title="<?php _e('Arrastrar para redimensionar', 'wupos'); ?>"></div>

        <!-- Productos sobrios -->
        <main class="wupos-products">
            <div class="wupos-products-header">
                <h1 class="wupos-products-title"><?php _e('Punto de Venta - Productos', 'wupos'); ?></h1>
                <div class="wupos-view-controls">
                    <button class="wupos-view-btn active"><i class="fas fa-th"></i></button>
                    <button class="wupos-view-btn"><i class="fas fa-list"></i></button>
                </div>
            </div>
            <div class="wupos-products-grid" id="wupos-product-grid">
                <!-- Producto 1 -->
                <div class="wupos-product-card" onclick="addToCart(1)">
                    <div class="wupos-product-image">
                        <i class="fas fa-hamburger"></i>
                    </div>
                    <div class="wupos-product-name"><?php _e('Hamburguesa Clásica', 'wupos'); ?></div>
                    <div class="wupos-product-price">$12.99</div>
                    <div class="wupos-stock-badge wupos-stock-high"><?php _e('25 en stock', 'wupos'); ?></div>
                </div>

                <!-- Producto 2 -->
                <div class="wupos-product-card" onclick="addToCart(2)">
                    <div class="wupos-product-image">
                        <i class="fas fa-pizza-slice"></i>
                    </div>
                    <div class="wupos-product-name"><?php _e('Pizza Margherita', 'wupos'); ?></div>
                    <div class="wupos-product-price">$18.50</div>
                    <div class="wupos-stock-badge wupos-stock-medium"><?php _e('8 en stock', 'wupos'); ?></div>
                </div>

                <!-- Producto 3 -->
                <div class="wupos-product-card" onclick="addToCart(3)">
                    <div class="wupos-product-image">
                        <i class="fas fa-mug-hot"></i>
                    </div>
                    <div class="wupos-product-name"><?php _e('Café Americano', 'wupos'); ?></div>
                    <div class="wupos-product-price">$3.75</div>
                    <div class="wupos-stock-badge wupos-stock-low"><?php _e('2 en stock', 'wupos'); ?></div>
                </div>

                <!-- Producto 4 -->
                <div class="wupos-product-card" onclick="addToCart(4)">
                    <div class="wupos-product-image">
                        <i class="fas fa-ice-cream"></i>
                    </div>
                    <div class="wupos-product-name"><?php _e('Helado Vainilla', 'wupos'); ?></div>
                    <div class="wupos-product-price">$5.25</div>
                    <div class="wupos-stock-badge wupos-stock-high"><?php _e('15 en stock', 'wupos'); ?></div>
                </div>

                <!-- Producto 5 -->
                <div class="wupos-product-card" onclick="addToCart(5)">
                    <div class="wupos-product-image">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="wupos-product-name"><?php _e('Agua Mineral', 'wupos'); ?></div>
                    <div class="wupos-product-price">$2.00</div>
                    <div class="wupos-stock-badge wupos-stock-high"><?php _e('50 en stock', 'wupos'); ?></div>
                </div>
            </div>
        </main>

        <!-- Carrito maximizado -->
        <aside class="wupos-cart">
            <!-- Header del Carrito -->
            <div class="wupos-cart-header">
                <span><?php _e('Cant.', 'wupos'); ?></span>
                <span><?php _e('Nombre', 'wupos'); ?></span>
                <span><?php _e('Precio', 'wupos'); ?></span>
                <span><?php _e('Total', 'wupos'); ?></span>
                <span></span>
            </div>
            
            <!-- Items del Carrito (máximo espacio) -->
            <div class="wupos-cart-items" id="wupos-cart-items">
                <div class="wupos-cart-item">
                    <input type="number" class="wupos-qty-input" value="2" min="1" max="999">
                    <input type="text" class="wupos-item-name-input" value="<?php _e('Hamburguesa Clásica', 'wupos'); ?>" placeholder="<?php _e('Nombre del producto', 'wupos'); ?>">
                    <input type="text" class="wupos-item-price-input" value="$12.99" placeholder="$0.00">
                    <div class="wupos-item-total">$25.98</div>
                    <button class="wupos-remove-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <div class="wupos-cart-item">
                    <input type="number" class="wupos-qty-input" value="1" min="1" max="999">
                    <input type="text" class="wupos-item-name-input" value="<?php _e('Café Americano', 'wupos'); ?>" placeholder="<?php _e('Nombre del producto', 'wupos'); ?>">
                    <input type="text" class="wupos-item-price-input" value="$3.75" placeholder="$0.00">
                    <div class="wupos-item-total">$3.75</div>
                    <button class="wupos-remove-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <!-- Sección fija inferior -->
            <div class="wupos-cart-footer">
                <!-- Totales -->
                <div class="wupos-cart-totals">
                    <div class="wupos-total-row">
                        <span class="wupos-total-label"><?php _e('Subtotal:', 'wupos'); ?></span>
                        <span class="wupos-total-amount" id="wupos-subtotal">$29.73</span>
                    </div>
                    <div class="wupos-total-row">
                        <span class="wupos-total-label"><?php _e('Impuestos (15%):', 'wupos'); ?></span>
                        <span class="wupos-total-amount" id="wupos-tax">$4.46</span>
                    </div>
                    <div class="wupos-total-row wupos-total-final">
                        <span class="wupos-total-label"><?php _e('TOTAL:', 'wupos'); ?></span>
                        <span class="wupos-total-amount" id="wupos-total">$34.19</span>
                    </div>
                </div>

                <!-- Checkout compacto -->
                <div class="wupos-checkout-section">
                    <div class="wupos-payment-section">
                        <div class="wupos-payment-methods">
                            <div class="wupos-payment-select-container">
                                <select class="wupos-payment-select" id="paymentMethod">
                                    <option value="cash">💵 <?php _e('Efectivo', 'wupos'); ?></option>
                                    <option value="card">💳 <?php _e('Tarjeta', 'wupos'); ?></option>
                                    <option value="digital">📱 <?php _e('Digital', 'wupos'); ?></option>
                                </select>
                            </div>
                            <div class="wupos-note-container">
                                <button class="wupos-note-btn" id="noteBtn" title="<?php _e('Agregar nota al pedido', 'wupos'); ?>">
                                    <i class="fas fa-sticky-note"></i>
                                    <span><?php _e('Notas', 'wupos'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wupos-final-buttons">
                        <button class="wupos-process-sale-btn" id="wupos-checkout">
                            <i class="fas fa-cash-register"></i>
                            <?php _e('PAGAR', 'wupos'); ?> $34.19
                        </button>
                        <button class="wupos-discard-cart-btn" title="<?php _e('Descartar carrito', 'wupos'); ?>">
                            <i class="fas fa-trash-alt"></i>
                            <span><?php _e('Limpiar', 'wupos'); ?></span>
                        </button>
                    </div>
                </div>

                <!-- Carritos en espera -->
                <div class="wupos-pending-carts">
                    <div class="wupos-pending-carts-container">
                        <button class="wupos-cart-tab active" data-cart="1">$34.19</button>
                        <button class="wupos-cart-tab" data-cart="2">$15.50</button>
                        <button class="wupos-cart-tab" data-cart="3">$8.75</button>
                        <button class="wupos-cart-tab new-cart" title="<?php _e('Nuevo carrito', 'wupos'); ?>">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Footer -->
        <footer class="wupos-footer">
            <div class="wupos-footer-left">
                <div class="wupos-status-indicator">
                    <div class="wupos-status-dot online"></div>
                    <span><?php _e('Sistema Online', 'wupos'); ?></span>
                </div>
                <div class="wupos-status-indicator">
                    <div class="wupos-status-dot online"></div>
                    <span><?php _e('Impresora Conectada', 'wupos'); ?></span>
                </div>
                <div class="wupos-status-indicator">
                    <div class="wupos-status-dot warning"></div>
                    <span><?php _e('Sincronización Pendiente', 'wupos'); ?></span>
                </div>
                <div class="wupos-status-indicator">
                    <div class="wupos-status-dot error"></div>
                    <span><?php _e('Base de Datos - Error', 'wupos'); ?></span>
                </div>
            </div>
            <div class="wupos-footer-right">
                <span>WUPOS v<?php echo defined('WUPOS_VERSION') ? WUPOS_VERSION : '2.0'; ?> | 1366x768</span>
                <span><?php _e('Terminal:', 'wupos'); ?> <strong>CAJA-01</strong></span>
                <span id="current-time">14:25:33</span>
            </div>
        </footer>
    </div>
</div>

<!-- FontAwesome Icons - Necesario para iconos del wireframe -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Hidden elements for dialogs/modals -->
<div id="wupos-modals-container"></div>

<script>
// JavaScript EXACTO del wireframe para funcionalidad básica
let cart = [];

function addToCart(productId) {
    console.log('Agregando producto', productId, 'al carrito');
}

// Método de pago
const paymentSelect = document.getElementById('paymentMethod');
if (paymentSelect) {
    paymentSelect.addEventListener('change', function() {
        console.log('Método de pago seleccionado:', this.value);
    });
}

// Carritos en espera
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wupos-cart-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            if (this.classList.contains('new-cart')) {
                console.log('Creando nuevo carrito...');
            } else {
                document.querySelectorAll('.wupos-cart-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const cartId = this.dataset.cart;
                console.log('Cambiando a carrito:', cartId);
            }
        });
    });

    // Botón de notas
    const noteBtn = document.getElementById('noteBtn');
    if (noteBtn) {
        noteBtn.addEventListener('click', function() {
            const currentNote = this.dataset.note || '';
            const newNote = prompt('Nota para el pedido:', currentNote);
            
            if (newNote !== null) {
                if (newNote.trim()) {
                    this.dataset.note = newNote;
                    this.classList.add('has-note');
                    this.title = `Nota: ${newNote}`;
                    console.log('Nota agregada:', newNote);
                } else {
                    delete this.dataset.note;
                    this.classList.remove('has-note');
                    this.title = 'Agregar nota al pedido';
                    console.log('Nota eliminada');
                }
            }
        });
    }

    // Botón de pago
    const processBtn = document.querySelector('.wupos-process-sale-btn');
    if (processBtn) {
        processBtn.addEventListener('click', function() {
            alert('Procesando pago...');
        });
    }

    // Categorías
    document.querySelectorAll('.wupos-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.wupos-category-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Reloj en tiempo real
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-ES', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }

    updateTime();
    setInterval(updateTime, 1000);

    // ACTIVAR MODO PANTALLA COMPLETA PARA POS
    document.body.classList.add('wupos-fullscreen-mode');
    
    // Mostrar interfaz después de loading
    setTimeout(function() {
        const loading = document.getElementById('wupos-loading');
        const interface = document.getElementById('wupos-pos-interface');
        if (loading) loading.style.display = 'none';
        if (interface) interface.style.display = 'grid';
    }, 1500);
    
    console.log('WUPOS: Sistema POS inicializado en modo pantalla completa');
});

// Limpieza al salir de la página
window.addEventListener('beforeunload', function() {
    document.body.classList.remove('wupos-fullscreen-mode');
});
</script>