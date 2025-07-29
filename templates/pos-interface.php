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
        <!-- Header Section: Logo, Search, User Controls -->
        <header class="wupos-header" role="banner">
            <!-- Logo Section -->
            <div class="wupos-header-brand">
                <div class="wupos-logo" role="img" aria-label="<?php _e('WUPOS Logo', 'wupos'); ?>">
                    <i class="fas fa-cash-register" aria-hidden="true"></i>
                    <span class="wupos-logo-text">WUPOS</span>
                </div>
            </div>
            
            <!-- Search Section -->
            <div class="wupos-header-search">
                <form class="wupos-search-form" role="search" aria-label="<?php _e('Buscar productos', 'wupos'); ?>">
                    <label for="wupos-search-input" class="sr-only"><?php _e('Buscar productos', 'wupos'); ?></label>
                    <input 
                        type="search" 
                        id="wupos-search-input"
                        class="wupos-search-input" 
                        placeholder="<?php _e('Buscar productos...', 'wupos'); ?>"
                        aria-describedby="wupos-search-description"
                    >
                    <span id="wupos-search-description" class="sr-only"><?php _e('Ingrese el nombre o código del producto', 'wupos'); ?></span>
                </form>
            </div>
            
            <!-- User Controls Section -->
            <div class="wupos-header-controls">
                <div class="wupos-control-group">
                    <label for="wupos-customer-search" class="wupos-control-label"><?php _e('Cliente:', 'wupos'); ?></label>
                    <input 
                        type="search" 
                        id="wupos-customer-search"
                        class="wupos-customer-search" 
                        placeholder="<?php _e('Buscar cliente...', 'wupos'); ?>"
                        aria-describedby="wupos-customer-description"
                    >
                    <span id="wupos-customer-description" class="sr-only"><?php _e('Buscar cliente por nombre o teléfono', 'wupos'); ?></span>
                </div>
                
                <div class="wupos-control-group">
                    <label for="wupos-user-select" class="wupos-control-label"><?php _e('Usuario:', 'wupos'); ?></label>
                    <select id="wupos-user-select" class="wupos-user-select" aria-describedby="wupos-user-description">
                        <?php $current_user = wp_get_current_user(); ?>
                        <option value="current"><?php echo esc_html($current_user->display_name); ?> - Caja 01</option>
                        <option value="vendor">Vendedor - Caja 02</option>
                        <option value="supervisor">Supervisor - Caja 03</option>
                    </select>
                    <span id="wupos-user-description" class="sr-only"><?php _e('Seleccionar usuario y terminal de venta', 'wupos'); ?></span>
                </div>
                
                <a href="<?php echo admin_url(); ?>" class="wupos-admin-link" title="<?php _e('Ir al panel de administración', 'wupos'); ?>" aria-label="<?php _e('Acceder al panel de administración de WordPress', 'wupos'); ?>">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                    <span class="wupos-admin-text"><?php _e('Admin', 'wupos'); ?></span>
                </a>
            </div>
        </header>

        <!-- Navigation Sidebar -->
        <aside class="wupos-sidebar" role="navigation" aria-label="<?php _e('Navegación principal del sistema POS', 'wupos'); ?>">
            <nav class="wupos-nav">
                <ul class="wupos-nav-list" role="list">
                    <li class="wupos-nav-item">
                        <button 
                            type="button"
                            class="wupos-nav-btn wupos-nav-btn--active" 
                            data-section="pos"
                            title="<?php _e('Punto de Venta - Gestión de ventas', 'wupos'); ?>"
                            aria-pressed="true"
                            aria-describedby="pos-description"
                        >
                            <span class="wupos-nav-icon" aria-hidden="true">
                                <i class="fas fa-cash-register"></i>
                            </span>
                            <span class="wupos-nav-text"><?php _e('POS', 'wupos'); ?></span>
                        </button>
                        <span id="pos-description" class="sr-only"><?php _e('Interfaz principal de punto de venta para procesar transacciones', 'wupos'); ?></span>
                    </li>
                    
                    <li class="wupos-nav-item">
                        <button 
                            type="button"
                            class="wupos-nav-btn" 
                            data-section="products"
                            title="<?php _e('Gestión de Productos - Catálogo e inventario', 'wupos'); ?>"
                            aria-pressed="false"
                            aria-describedby="products-description"
                        >
                            <span class="wupos-nav-icon" aria-hidden="true">
                                <i class="fas fa-box"></i>
                            </span>
                            <span class="wupos-nav-text"><?php _e('Productos', 'wupos'); ?></span>
                        </button>
                        <span id="products-description" class="sr-only"><?php _e('Gestión de catálogo de productos e inventario', 'wupos'); ?></span>
                    </li>
                    
                    <li class="wupos-nav-item">
                        <button 
                            type="button"
                            class="wupos-nav-btn" 
                            data-section="orders"
                            title="<?php _e('Gestión de Órdenes - Historial de ventas', 'wupos'); ?>"
                            aria-pressed="false"
                            aria-describedby="orders-description"
                        >
                            <span class="wupos-nav-icon" aria-hidden="true">
                                <i class="fas fa-shopping-cart"></i>
                            </span>
                            <span class="wupos-nav-text"><?php _e('Órdenes', 'wupos'); ?></span>
                        </button>
                        <span id="orders-description" class="sr-only"><?php _e('Historial y gestión de órdenes de venta', 'wupos'); ?></span>
                    </li>
                    
                    <li class="wupos-nav-item">
                        <button 
                            type="button"
                            class="wupos-nav-btn" 
                            data-section="customers"
                            title="<?php _e('Gestión de Clientes - Base de datos de clientes', 'wupos'); ?>"
                            aria-pressed="false"
                            aria-describedby="customers-description"
                        >
                            <span class="wupos-nav-icon" aria-hidden="true">
                                <i class="fas fa-users"></i>
                            </span>
                            <span class="wupos-nav-text"><?php _e('Clientes', 'wupos'); ?></span>
                        </button>
                        <span id="customers-description" class="sr-only"><?php _e('Base de datos y gestión de información de clientes', 'wupos'); ?></span>
                    </li>
                    
                    <li class="wupos-nav-item">
                        <button 
                            type="button"
                            class="wupos-nav-btn" 
                            data-section="reports"
                            title="<?php _e('Reportes y Estadísticas - Análisis de ventas', 'wupos'); ?>"
                            aria-pressed="false"
                            aria-describedby="reports-description"
                        >
                            <span class="wupos-nav-icon" aria-hidden="true">
                                <i class="fas fa-chart-bar"></i>
                            </span>
                            <span class="wupos-nav-text"><?php _e('Reportes', 'wupos'); ?></span>
                        </button>
                        <span id="reports-description" class="sr-only"><?php _e('Reportes financieros y estadísticas de ventas', 'wupos'); ?></span>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Resizable Splitter -->
        <div 
            class="wupos-splitter" 
            role="separator" 
            aria-orientation="vertical"
            title="<?php _e('Arrastrar para redimensionar paneles', 'wupos'); ?>"
            aria-label="<?php _e('Separador redimensionable entre navegación y productos', 'wupos'); ?>"
            tabindex="0"
        ></div>

        <!-- Products Main Section -->
        <main class="wupos-products" role="main" aria-label="<?php _e('Área principal de productos para venta', 'wupos'); ?>">
            <!-- Products Header -->
            <header class="wupos-products-header">
                <h1 class="wupos-products-title"><?php _e('Punto de Venta - Productos', 'wupos'); ?></h1>
                <div class="wupos-view-controls" role="group" aria-label="<?php _e('Controles de vista de productos', 'wupos'); ?>">
                    <button 
                        type="button"
                        class="wupos-view-btn wupos-view-btn--active" 
                        data-view="grid"
                        title="<?php _e('Vista de cuadrícula', 'wupos'); ?>"
                        aria-pressed="true"
                        aria-describedby="grid-view-description"
                    >
                        <i class="fas fa-th" aria-hidden="true"></i>
                        <span class="sr-only"><?php _e('Vista de cuadrícula', 'wupos'); ?></span>
                    </button>
                    <button 
                        type="button"
                        class="wupos-view-btn" 
                        data-view="list"
                        title="<?php _e('Vista de lista', 'wupos'); ?>"
                        aria-pressed="false"
                        aria-describedby="list-view-description"
                    >
                        <i class="fas fa-list" aria-hidden="true"></i>
                        <span class="sr-only"><?php _e('Vista de lista', 'wupos'); ?></span>
                    </button>
                    <span id="grid-view-description" class="sr-only"><?php _e('Mostrar productos en formato de cuadrícula con imágenes', 'wupos'); ?></span>
                    <span id="list-view-description" class="sr-only"><?php _e('Mostrar productos en formato de lista detallada', 'wupos'); ?></span>
                </div>
            </header>
            
            <!-- Products Grid Container -->
            <section class="wupos-products-container" aria-label="<?php _e('Catálogo de productos disponibles', 'wupos'); ?>">
                <!-- Loading State -->
                <div class="wupos-products-loading" id="wupos-products-loading" style="display: none;">
                    <div class="loading-spinner" aria-hidden="true"></div>
                    <p><?php _e('Cargando productos...', 'wupos'); ?></p>
                </div>
                
                <!-- Error State -->
                <div class="wupos-products-error" id="wupos-products-error" style="display: none;">
                    <div class="error-icon" aria-hidden="true">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3><?php _e('Error cargando productos', 'wupos'); ?></h3>
                    <p id="wupos-error-message"></p>
                    <button type="button" class="btn btn-primary" id="wupos-retry-products">
                        <?php _e('Reintentar', 'wupos'); ?>
                    </button>
                </div>
                
                <!-- Empty State -->
                <div class="wupos-products-empty" id="wupos-products-empty" style="display: none;">
                    <div class="empty-icon" aria-hidden="true">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3><?php _e('No se encontraron productos', 'wupos'); ?></h3>
                    <p><?php _e('Intenta con otro término de búsqueda o verifica que WooCommerce esté configurado correctamente.', 'wupos'); ?></p>
                </div>
                
                <!-- Products Grid -->
                <div class="wupos-products-grid" id="wupos-product-grid" role="grid" aria-label="<?php _e('Cuadrícula de productos', 'wupos'); ?>">
                    <!-- Products will be populated dynamically via AJAX from WooCommerce -->
                </div>
                
                <!-- Pagination -->
                <div class="wupos-products-pagination" id="wupos-products-pagination" style="display: none;">
                    <button type="button" class="btn btn-outline-primary" id="wupos-prev-page" disabled>
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        <?php _e('Anterior', 'wupos'); ?>
                    </button>
                    <span class="pagination-info" id="wupos-pagination-info">
                        <?php _e('Página 1 de 1', 'wupos'); ?>
                    </span>
                    <button type="button" class="btn btn-outline-primary" id="wupos-next-page" disabled>
                        <?php _e('Siguiente', 'wupos'); ?>
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </section>
        </main>

        <!-- Shopping Cart Panel -->
        <aside class="wupos-cart" role="complementary" aria-label="<?php _e('Carrito de compras y procesamiento de pago', 'wupos'); ?>">
            <!-- Cart Header -->
            <header class="wupos-cart-header" role="banner">
                <h2 class="sr-only"><?php _e('Carrito de Compras', 'wupos'); ?></h2>
                <div class="wupos-cart-columns" role="row">
                    <span class="wupos-cart-column" role="columnheader"><?php _e('Cant.', 'wupos'); ?></span>
                    <span class="wupos-cart-column" role="columnheader"><?php _e('Nombre', 'wupos'); ?></span>
                    <span class="wupos-cart-column" role="columnheader"><?php _e('Precio', 'wupos'); ?></span>
                    <span class="wupos-cart-column" role="columnheader"><?php _e('Total', 'wupos'); ?></span>
                    <span class="wupos-cart-column" role="columnheader">
                        <span class="sr-only"><?php _e('Acciones', 'wupos'); ?></span>
                    </span>
                </div>
            </header>
            
            <!-- Cart Items Section -->
            <section class="wupos-cart-items" id="wupos-cart-items" role="table" aria-label="<?php _e('Items en el carrito de compras', 'wupos'); ?>">
                <!-- Sample Cart Item 1 -->
                <article class="wupos-cart-item" role="row" data-item-id="item-1">
                    <div class="wupos-item-quantity" role="cell">
                        <label for="qty-item-1" class="sr-only"><?php _e('Cantidad para Hamburguesa Clásica', 'wupos'); ?></label>
                        <input 
                            type="number" 
                            id="qty-item-1"
                            class="wupos-qty-input" 
                            value="2" 
                            min="1" 
                            max="999"
                            aria-describedby="qty-item-1-description"
                        >
                        <span id="qty-item-1-description" class="sr-only"><?php _e('Ingrese la cantidad de unidades', 'wupos'); ?></span>
                    </div>
                    
                    <div class="wupos-item-name" role="cell">
                        <label for="name-item-1" class="sr-only"><?php _e('Nombre del producto', 'wupos'); ?></label>
                        <input 
                            type="text" 
                            id="name-item-1"
                            class="wupos-item-name-input" 
                            value="<?php _e('Hamburguesa Clásica', 'wupos'); ?>" 
                            placeholder="<?php _e('Nombre del producto', 'wupos'); ?>"
                        >
                    </div>
                    
                    <div class="wupos-item-price" role="cell">
                        <label for="price-item-1" class="sr-only"><?php _e('Precio unitario', 'wupos'); ?></label>
                        <input 
                            type="text" 
                            id="price-item-1"
                            class="wupos-item-price-input" 
                            value="$12.99" 
                            placeholder="$0.00"
                            aria-describedby="price-item-1-description"
                        >
                        <span id="price-item-1-description" class="sr-only"><?php _e('Precio por unidad del producto', 'wupos'); ?></span>
                    </div>
                    
                    <div class="wupos-item-total" role="cell" aria-label="<?php _e('Total del item: veinticinco dólares con noventa y ocho centavos', 'wupos'); ?>">$25.98</div>
                    
                    <div class="wupos-item-actions" role="cell">
                        <button 
                            type="button"
                            class="wupos-remove-btn" 
                            aria-label="<?php _e('Eliminar Hamburguesa Clásica del carrito', 'wupos'); ?>"
                            title="<?php _e('Eliminar este item del carrito', 'wupos'); ?>"
                        >
                            <i class="fas fa-trash" aria-hidden="true"></i>
                            <span class="sr-only"><?php _e('Eliminar', 'wupos'); ?></span>
                        </button>
                    </div>
                </article>

                <!-- Sample Cart Item 2 -->
                <article class="wupos-cart-item" role="row" data-item-id="item-2">
                    <div class="wupos-item-quantity" role="cell">
                        <label for="qty-item-2" class="sr-only"><?php _e('Cantidad para Café Americano', 'wupos'); ?></label>
                        <input 
                            type="number" 
                            id="qty-item-2"
                            class="wupos-qty-input" 
                            value="1" 
                            min="1" 
                            max="999"
                            aria-describedby="qty-item-2-description"
                        >
                        <span id="qty-item-2-description" class="sr-only"><?php _e('Ingrese la cantidad de unidades', 'wupos'); ?></span>
                    </div>
                    
                    <div class="wupos-item-name" role="cell">
                        <label for="name-item-2" class="sr-only"><?php _e('Nombre del producto', 'wupos'); ?></label>
                        <input 
                            type="text" 
                            id="name-item-2"
                            class="wupos-item-name-input" 
                            value="<?php _e('Café Americano', 'wupos'); ?>" 
                            placeholder="<?php _e('Nombre del producto', 'wupos'); ?>"
                        >
                    </div>
                    
                    <div class="wupos-item-price" role="cell">
                        <label for="price-item-2" class="sr-only"><?php _e('Precio unitario', 'wupos'); ?></label>
                        <input 
                            type="text" 
                            id="price-item-2"
                            class="wupos-item-price-input" 
                            value="$3.75" 
                            placeholder="$0.00"
                            aria-describedby="price-item-2-description"
                        >
                        <span id="price-item-2-description" class="sr-only"><?php _e('Precio por unidad del producto', 'wupos'); ?></span>
                    </div>
                    
                    <div class="wupos-item-total" role="cell" aria-label="<?php _e('Total del item: tres dólares con setenta y cinco centavos', 'wupos'); ?>">$3.75</div>
                    
                    <div class="wupos-item-actions" role="cell">
                        <button 
                            type="button"
                            class="wupos-remove-btn" 
                            aria-label="<?php _e('Eliminar Café Americano del carrito', 'wupos'); ?>"
                            title="<?php _e('Eliminar este item del carrito', 'wupos'); ?>"
                        >
                            <i class="fas fa-trash" aria-hidden="true"></i>
                            <span class="sr-only"><?php _e('Eliminar', 'wupos'); ?></span>
                        </button>
                    </div>
                </article>
            </section>

            <!-- Cart Footer: Totals, Payment, Actions -->
            <footer class="wupos-cart-footer" role="contentinfo">
                <!-- Totals Section -->
                <section class="wupos-cart-totals" aria-labelledby="totals-heading">
                    <h3 id="totals-heading" class="sr-only"><?php _e('Resumen de totales de la venta', 'wupos'); ?></h3>
                    
                    <div class="wupos-total-row">
                        <span class="wupos-total-label"><?php _e('Subtotal:', 'wupos'); ?></span>
                        <span class="wupos-total-amount" id="wupos-subtotal" aria-label="<?php _e('Subtotal: veintinueve dólares con setenta y tres centavos', 'wupos'); ?>">$29.73</span>
                    </div>
                    
                    <!-- Tax Breakdown Section -->
                    <div id="wupos-tax-section" class="wupos-tax-section" style="display: none;">
                        <!-- Dynamic tax breakdown will be inserted here -->
                    </div>
                    
                    <!-- Fallback Single Tax Row (shown when taxes disabled or loading) -->
                    <div id="wupos-tax-fallback" class="wupos-total-row">
                        <span class="wupos-total-label" id="wupos-tax-label"><?php _e('Impuestos:', 'wupos'); ?></span>
                        <span class="wupos-total-amount" id="wupos-tax" aria-label="<?php _e('Impuestos: cero dólares', 'wupos'); ?>">$0.00</span>
                    </div>
                    
                    <div class="wupos-total-row wupos-total-final">
                        <span class="wupos-total-label"><?php _e('TOTAL:', 'wupos'); ?></span>
                        <span class="wupos-total-amount" id="wupos-total" aria-label="<?php _e('Total a pagar: treinta y cuatro dólares con diecinueve centavos', 'wupos'); ?>">$34.19</span>
                    </div>
                </section>

                <!-- Checkout Section -->
                <section class="wupos-checkout-section" aria-labelledby="checkout-heading">
                    <h3 id="checkout-heading" class="sr-only"><?php _e('Opciones de pago y finalización de venta', 'wupos'); ?></h3>
                    
                    <!-- Payment Methods -->
                    <div class="wupos-payment-section">
                        <div class="wupos-payment-methods">
                            <div class="wupos-payment-select-container">
                                <label for="paymentMethod" class="sr-only"><?php _e('Seleccionar método de pago', 'wupos'); ?></label>
                                <select 
                                    class="wupos-payment-select" 
                                    id="paymentMethod"
                                    aria-describedby="payment-method-description"
                                >
                                    <option value="cash">💵 <?php _e('Efectivo', 'wupos'); ?></option>
                                    <option value="card">💳 <?php _e('Tarjeta', 'wupos'); ?></option>
                                    <option value="digital">📱 <?php _e('Digital', 'wupos'); ?></option>
                                </select>
                                <span id="payment-method-description" class="sr-only"><?php _e('Seleccione el método de pago para procesar la transacción', 'wupos'); ?></span>
                            </div>
                            
                            <div class="wupos-note-container">
                                <button 
                                    type="button"
                                    class="wupos-note-btn" 
                                    id="noteBtn" 
                                    title="<?php _e('Agregar nota al pedido', 'wupos'); ?>"
                                    aria-describedby="note-btn-description"
                                >
                                    <i class="fas fa-sticky-note" aria-hidden="true"></i>
                                    <span><?php _e('Notas', 'wupos'); ?></span>
                                </button>
                                <span id="note-btn-description" class="sr-only"><?php _e('Agregar una nota o comentario especial a esta venta', 'wupos'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="wupos-final-buttons" role="group" aria-labelledby="action-buttons-heading">
                        <h4 id="action-buttons-heading" class="sr-only"><?php _e('Acciones finales de la venta', 'wupos'); ?></h4>
                        
                        <button 
                            type="button"
                            class="wupos-process-sale-btn" 
                            id="wupos-checkout"
                            aria-describedby="process-sale-description"
                        >
                            <i class="fas fa-cash-register" aria-hidden="true"></i>
                            <span><?php _e('PAGAR', 'wupos'); ?> $34.19</span>
                        </button>
                        <span id="process-sale-description" class="sr-only"><?php _e('Procesar el pago y completar la venta por un total de treinta y cuatro dólares con diecinueve centavos', 'wupos'); ?></span>
                        
                        <button 
                            type="button"
                            class="wupos-discard-cart-btn" 
                            title="<?php _e('Descartar carrito', 'wupos'); ?>"
                            aria-describedby="discard-cart-description"
                        >
                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                            <span><?php _e('Limpiar', 'wupos'); ?></span>
                        </button>
                        <span id="discard-cart-description" class="sr-only"><?php _e('Eliminar todos los items del carrito actual', 'wupos'); ?></span>
                    </div>
                </section>

                <!-- Pending Carts Section -->
                <section class="wupos-pending-carts" aria-labelledby="pending-carts-heading">
                    <h3 id="pending-carts-heading" class="sr-only"><?php _e('Carritos en espera', 'wupos'); ?></h3>
                    
                    <div class="wupos-pending-carts-container" role="tablist" aria-label="<?php _e('Carritos de venta múltiples', 'wupos'); ?>">
                        <button 
                            type="button"
                            class="wupos-cart-tab wupos-cart-tab--active" 
                            data-cart="1"
                            role="tab"
                            aria-selected="true"
                            aria-controls="cart-panel-1"
                            aria-describedby="cart-1-description"
                        >$34.19</button>
                        <span id="cart-1-description" class="sr-only"><?php _e('Carrito 1 activo con total de treinta y cuatro dólares', 'wupos'); ?></span>
                        
                        <button 
                            type="button"
                            class="wupos-cart-tab" 
                            data-cart="2"
                            role="tab"
                            aria-selected="false"
                            aria-controls="cart-panel-2"
                            aria-describedby="cart-2-description"
                        >$15.50</button>
                        <span id="cart-2-description" class="sr-only"><?php _e('Carrito 2 en espera con total de quince dólares', 'wupos'); ?></span>
                        
                        <button 
                            type="button"
                            class="wupos-cart-tab" 
                            data-cart="3"
                            role="tab"
                            aria-selected="false"
                            aria-controls="cart-panel-3"
                            aria-describedby="cart-3-description"
                        >$8.75</button>
                        <span id="cart-3-description" class="sr-only"><?php _e('Carrito 3 en espera con total de ocho dólares', 'wupos'); ?></span>
                        
                        <button 
                            type="button"
                            class="wupos-cart-tab wupos-cart-tab--new" 
                            title="<?php _e('Crear nuevo carrito', 'wupos'); ?>"
                            aria-label="<?php _e('Crear nuevo carrito de venta', 'wupos'); ?>"
                        >
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            <span class="sr-only"><?php _e('Nuevo carrito', 'wupos'); ?></span>
                        </button>
                    </div>
                </section>
            </footer>
        </aside>

        <!-- System Status Footer -->
        <footer class="wupos-footer" role="contentinfo" aria-label="<?php _e('Estado del sistema y información de terminal', 'wupos'); ?>">
            <!-- System Status Indicators -->
            <div class="wupos-footer-left" role="status" aria-live="polite" aria-label="<?php _e('Indicadores de estado del sistema', 'wupos'); ?>">
                <div class="wupos-status-indicator" aria-describedby="system-status-description">
                    <div class="wupos-status-dot wupos-status-dot--online" aria-hidden="true"></div>
                    <span><?php _e('Sistema Online', 'wupos'); ?></span>
                </div>
                <span id="system-status-description" class="sr-only"><?php _e('El sistema POS está funcionando correctamente', 'wupos'); ?></span>
                
                <div class="wupos-status-indicator" aria-describedby="printer-status-description">
                    <div class="wupos-status-dot wupos-status-dot--online" aria-hidden="true"></div>
                    <span><?php _e('Impresora Conectada', 'wupos'); ?></span>
                </div>
                <span id="printer-status-description" class="sr-only"><?php _e('La impresora de recibos está conectada y lista', 'wupos'); ?></span>
                
                <div class="wupos-status-indicator" aria-describedby="sync-status-description">
                    <div class="wupos-status-dot wupos-status-dot--warning" aria-hidden="true"></div>
                    <span><?php _e('Sincronización Pendiente', 'wupos'); ?></span>
                </div>
                <span id="sync-status-description" class="sr-only"><?php _e('Hay datos pendientes de sincronización con el servidor', 'wupos'); ?></span>
                
                <div class="wupos-status-indicator" aria-describedby="database-status-description">
                    <div class="wupos-status-dot wupos-status-dot--error" aria-hidden="true"></div>
                    <span><?php _e('Base de Datos - Error', 'wupos'); ?></span>
                </div>
                <span id="database-status-description" class="sr-only"><?php _e('Se detectó un error en la conexión a la base de datos', 'wupos'); ?></span>
            </div>
            
            <!-- System Information -->
            <div class="wupos-footer-right" aria-label="<?php _e('Información del sistema y terminal', 'wupos'); ?>">
                <span class="wupos-version-info" aria-label="<?php _e('Versión del sistema y resolución de pantalla', 'wupos'); ?>">
                    WUPOS v<?php echo defined('WUPOS_VERSION') ? WUPOS_VERSION : '2.0'; ?> | 1366x768
                </span>
                
                <span class="wupos-terminal-info" aria-label="<?php _e('Identificación de terminal: Caja cero uno', 'wupos'); ?>">
                    <?php _e('Terminal:', 'wupos'); ?> <strong>CAJA-01</strong>
                </span>
                
                <time 
                    id="current-time" 
                    class="wupos-current-time"
                    aria-live="off"
                    aria-label="<?php _e('Hora actual del sistema', 'wupos'); ?>"
                >14:25:33</time>
            </div>
        </footer>
    </div>
</div>

<!-- FontAwesome Icons - Required for wireframe icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Screen Reader Only Utility Styles -->
<style>
.sr-only {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}

.sr-only-focusable:focus {
    position: static !important;
    width: auto !important;
    height: auto !important;
    padding: inherit !important;
    margin: inherit !important;
    overflow: visible !important;
    clip: auto !important;
    white-space: normal !important;
}
</style>

<!-- Accessibility Live Regions -->
<div aria-live="polite" aria-atomic="true" class="sr-only" id="wupos-status-announcements"></div>
<div aria-live="assertive" aria-atomic="true" class="sr-only" id="wupos-error-announcements"></div>

<!-- Modal Container for Dialogs -->
<div id="wupos-modals-container" role="dialog" aria-hidden="true"></div>

<!-- App-level Accessibility Information -->
<div id="wupos-app-info" class="sr-only">
    <h1><?php _e('WUPOS - Sistema de Punto de Venta', 'wupos'); ?></h1>
    <p><?php _e('Sistema completo de punto de venta optimizado para terminales táctiles y uso comercial.', 'wupos'); ?></p>
    <p><?php _e('Navegue usando las teclas Tab, Enter y flechas. Presione Escape para salir de modales.', 'wupos'); ?></p>
</div>

<script>
/**
 * WUPOS POS Interface - Basic Functionality (Phase 1)
 * 
 * This script provides the foundational functionality for the WUPOS interface.
 * Event handlers are properly bound to semantic HTML elements for accessibility.
 */

/**
 * WUPOS Cart System - Global Variables and Configuration
 * Following WordPress JavaScript standards and accessibility guidelines
 */

// Cart system configuration
const WUPOS_CART_CONFIG = {
    maxCarts: 10,
    maxQuantityPerItem: 999,
    minQuantityPerItem: 1,
    currencySymbol: '<?php echo html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'); ?>',
    decimalPlaces: <?php echo wc_get_price_decimals(); ?>,
    storageKey: 'wupos_carts_data',
    currentCartKey: 'wupos_current_cart',
    taxCalculationEndpoint: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('wupos_nonce'); ?>'
};

// WooCommerce tax settings (loaded asynchronously)
let WUPOS_TAX_SETTINGS = {
    tax_enabled: false,
    tax_inclusive: false,
    tax_suffix: '',
    tax_display_cart: 'excl',
    tax_display_shop: 'excl',
    currency_symbol: '$',
    currency_position: 'left',
    decimals: 2,
    loaded: false
};

// Tax calculation state
let taxCalculationInProgress = false;
let lastTaxCalculation = null;

// Global cart storage - Multi-cart system
let cartsData = {};
let currentCartId = 1;

// Dynamic products cache loaded from WooCommerce
let WUPOS_PRODUCTS_CACHE = {};
let WUPOS_PRODUCTS_PAGINATION = {
    currentPage: 1,
    totalPages: 1,
    totalProducts: 0,
    hasMore: false
};

// Product loading state management
let WUPOS_PRODUCTS_STATE = {
    isLoading: false,
    lastSearchTerm: '',
    lastCategory: '',
    loadingTimeout: null
};

// Wait for DOM content to be fully loaded
document.addEventListener('DOMContentLoaded', async function() {
    // Initialize cart system first (async)
    await initializeCartSystem();
    
    // Initialize POS interface
    initializePOSInterface();
    
    // Set up event listeners
    setupEventListeners();
    
    // Start system clock
    startSystemClock();
    
    // Load initial products
    loadWooCommerceProducts();
    
    // Activate fullscreen mode
    activateFullscreenMode();
    
    // Show interface after loading delay
    showInterface();
    
    console.log('WUPOS: Sistema POS inicializado correctamente');
});

/**
 * Initialize the cart system with localStorage persistence
 * Following WordPress JavaScript standards for data management
 */
async function initializeCartSystem() {
    try {
        // Load WooCommerce tax settings first
        await loadWooCommerceTaxSettings();
        
        // Load existing cart data from localStorage
        loadCartsFromStorage();
        
        // Initialize current cart if empty
        if (!cartsData[currentCartId]) {
            cartsData[currentCartId] = createEmptyCart();
        }
        
        // Update existing carts with current tax settings if they don't have them
        updateCartsWithTaxSettings();
        
        // Update UI with current cart data
        updateCartUI();
        updateCartTabs();
        
        console.log('WUPOS: Sistema de carritos inicializado correctamente');
        announceToScreenReader('Sistema de carritos cargado correctamente');
        
    } catch (error) {
        console.error('WUPOS Error: Error inicializando sistema de carritos:', error);
        announceToScreenReader('Error cargando sistema de carritos', true);
        
        // Initialize with empty cart as fallback
        cartsData[currentCartId] = createEmptyCart();
        updateCartUI();
    }
}

/**
 * Update existing carts with current tax settings
 */
function updateCartsWithTaxSettings() {
    Object.keys(cartsData).forEach(cartId => {
        const cart = cartsData[cartId];
        
        // Update tax settings if cart doesn't have them or they're outdated
        if (cart.taxEnabled === undefined || cart.taxSuffix === undefined) {
            cart.taxEnabled = WUPOS_TAX_SETTINGS.tax_enabled;
            cart.taxInclusive = WUPOS_TAX_SETTINGS.tax_inclusive;
            cart.taxSuffix = WUPOS_TAX_SETTINGS.tax_suffix;
            cart.updatedAt = new Date().toISOString();
        }
    });
    
    // Save updated carts
    saveCartsToStorage();
}

/**
 * Create an empty cart structure
 * @returns {Object} Empty cart object
 */
function createEmptyCart() {
    return {
        id: currentCartId,
        items: [],
        subtotal: 0,
        tax: 0,
        total: 0,
        taxBreakdown: [],
        taxEnabled: WUPOS_TAX_SETTINGS.tax_enabled,
        taxInclusive: WUPOS_TAX_SETTINGS.tax_inclusive,
        taxSuffix: WUPOS_TAX_SETTINGS.tax_suffix,
        paymentMethod: 'cash',
        note: '',
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString()
    };
}

/**
 * Load WooCommerce tax settings
 * @returns {Promise} Promise that resolves when tax settings are loaded
 */
function loadWooCommerceTaxSettings() {
    return new Promise((resolve, reject) => {
        // If already loaded, resolve immediately
        if (WUPOS_TAX_SETTINGS.loaded) {
            resolve(WUPOS_TAX_SETTINGS);
            return;
        }

        // Prepare request data
        const requestData = {
            action: 'wupos_get_tax_settings',
            nonce: WUPOS_CART_CONFIG.nonce
        };

        // Make AJAX request
        fetch(WUPOS_CART_CONFIG.taxCalculationEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Update global tax settings
                WUPOS_TAX_SETTINGS = {
                    ...data.data,
                    loaded: true
                };
                
                // Update WUPOS_CART_CONFIG with currency info
                // Decode HTML entities for currency symbol
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.data.currency_symbol || '$';
                WUPOS_CART_CONFIG.currencySymbol = tempDiv.textContent || tempDiv.innerText || '$';
                WUPOS_CART_CONFIG.decimalPlaces = data.data.decimals || <?php echo wc_get_price_decimals(); ?>;
                
                console.log('WUPOS: Tax settings loaded successfully:', WUPOS_TAX_SETTINGS);
                resolve(WUPOS_TAX_SETTINGS);
            } else {
                console.error('WUPOS Error: Failed to load tax settings:', data.data || 'Unknown error');
                // Use defaults if loading fails
                WUPOS_TAX_SETTINGS.loaded = true;
                resolve(WUPOS_TAX_SETTINGS);
            }
        })
        .catch(error => {
            console.error('WUPOS Error: Tax settings request failed:', error);
            // Use defaults if request fails
            WUPOS_TAX_SETTINGS.loaded = true;
            resolve(WUPOS_TAX_SETTINGS);
        });
    });
}

/**
 * Load carts data from localStorage
 * Following WordPress security practices for data validation
 */
function loadCartsFromStorage() {
    try {
        const storedCarts = localStorage.getItem(WUPOS_CART_CONFIG.storageKey);
        const storedCurrentCart = localStorage.getItem(WUPOS_CART_CONFIG.currentCartKey);
        
        if (storedCarts) {
            const parsedCarts = JSON.parse(storedCarts);
            
            // Validate cart data structure
            if (parsedCarts && typeof parsedCarts === 'object') {
                cartsData = parsedCarts;
            }
        }
        
        if (storedCurrentCart) {
            const parsedCurrentCart = parseInt(storedCurrentCart, 10);
            if (!isNaN(parsedCurrentCart) && parsedCurrentCart > 0) {
                currentCartId = parsedCurrentCart;
            }
        }
        
    } catch (error) {
        console.error('WUPOS Error: Error cargando datos del localStorage:', error);
        // Continue with empty cart system
        cartsData = {};
        currentCartId = 1;
    }
}

/**
 * Save carts data to localStorage
 * Following WordPress security practices with error handling
 */
function saveCartsToStorage() {
    try {
        localStorage.setItem(WUPOS_CART_CONFIG.storageKey, JSON.stringify(cartsData));
        localStorage.setItem(WUPOS_CART_CONFIG.currentCartKey, currentCartId.toString());
        
    } catch (error) {
        console.error('WUPOS Error: Error guardando datos en localStorage:', error);
        announceToScreenReader('Error guardando datos del carrito', true);
    }
}

/**
 * Initialize the POS interface components
 */
function initializePOSInterface() {
    // Add accessibility announcements
    announceToScreenReader('Sistema POS cargado y listo para usar');
    
    // Set initial focus (will be handled by CSS later)
    const searchInput = document.getElementById('wupos-search-input');
    if (searchInput) {
        // searchInput.focus(); // Uncomment when styling is complete
    }
}

/**
 * Set up all event listeners for the interface
 */
function setupEventListeners() {
    // Product buttons
    setupProductEventListeners();
    
    // Navigation buttons
    setupNavigationEventListeners();
    
    // Cart functionality
    setupCartEventListeners();
    
    // Payment method selection
    setupPaymentEventListeners();
    
    // View controls
    setupViewControlEventListeners();
    
    // Product search and retry functionality
    setupProductSearchEventListeners();
}

/**
 * Set up product-related event listeners
 */
function setupProductEventListeners() {
    document.querySelectorAll('.wupos-product-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.wupos-product-card');
            const productId = productCard.dataset.productId;
            addToCart(productId);
        });
        
        // Add keyboard support
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
}

/**
 * Set up navigation event listeners
 */
function setupNavigationEventListeners() {
    document.querySelectorAll('.wupos-nav-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active state from all buttons
            document.querySelectorAll('.wupos-nav-btn').forEach(btn => {
                btn.classList.remove('wupos-nav-btn--active');
                btn.setAttribute('aria-pressed', 'false');
            });
            
            // Set active state for clicked button
            this.classList.add('wupos-nav-btn--active');
            this.setAttribute('aria-pressed', 'true');
            
            const section = this.dataset.section;
            console.log('Navegando a sección:', section);
            announceToScreenReader(`Sección ${section} seleccionada`);
        });
    });
}

/**
 * Set up cart-related event listeners
 * Following WordPress accessibility and event handling standards
 */
function setupCartEventListeners() {
    // Cart tabs - Multi-cart system
    document.querySelectorAll('.wupos-cart-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            if (this.classList.contains('wupos-cart-tab--new')) {
                createNewCart();
            } else {
                const cartId = parseInt(this.dataset.cart, 10);
                if (!isNaN(cartId)) {
                    switchToCart(cartId);
                }
            }
        });
    });
    
    // Quantity input changes - Use event delegation for dynamic content
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('wupos-qty-input')) {
            const itemElement = e.target.closest('.wupos-cart-item');
            if (itemElement) {
                const itemId = itemElement.dataset.itemId;
                const newQuantity = parseInt(e.target.value, 10);
                updateItemQuantity(itemId, newQuantity);
            }
        }
    });
    
    // Price input changes - Allow manual price adjustment
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('wupos-item-price-input')) {
            const itemElement = e.target.closest('.wupos-cart-item');
            if (itemElement) {
                const itemId = itemElement.dataset.itemId;
                const newPrice = parseFloat(e.target.value.replace(/[^\d.-]/g, ''));
                updateItemPrice(itemId, newPrice);
            }
        }
    });
    
    // Remove item buttons - Use event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.wupos-remove-btn')) {
            const itemElement = e.target.closest('.wupos-cart-item');
            if (itemElement) {
                const itemId = itemElement.dataset.itemId;
                removeFromCart(itemId);
            }
        }
    });
    
    // Process sale button
    const processBtn = document.querySelector('.wupos-process-sale-btn');
    if (processBtn) {
        processBtn.addEventListener('click', function() {
            processCartCheckout();
        });
    }
    
    // Clear cart button
    const clearBtn = document.querySelector('.wupos-discard-cart-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            clearCurrentCart();
        });
    }
}

/**
 * Set up payment method event listeners
 */
function setupPaymentEventListeners() {
    const paymentSelect = document.getElementById('paymentMethod');
    if (paymentSelect) {
        paymentSelect.addEventListener('change', function() {
            console.log('Método de pago seleccionado:', this.value);
            announceToScreenReader(`Método de pago cambiado a ${this.options[this.selectedIndex].text}`);
        });
    }
    
    // Notes button
    const noteBtn = document.getElementById('noteBtn');
    if (noteBtn) {
        noteBtn.addEventListener('click', function() {
            const currentNote = this.dataset.note || '';
            const newNote = prompt('Nota para el pedido:', currentNote);
            
            if (newNote !== null) {
                if (newNote.trim()) {
                    this.dataset.note = newNote;
                    this.classList.add('has-note');
                    this.setAttribute('title', `Nota: ${newNote}`);
                    console.log('Nota agregada:', newNote);
                    announceToScreenReader('Nota agregada al pedido');
                } else {
                    delete this.dataset.note;
                    this.classList.remove('has-note');
                    this.setAttribute('title', 'Agregar nota al pedido');
                    console.log('Nota eliminada');
                    announceToScreenReader('Nota eliminada del pedido');
                }
            }
        });
    }
}

/**
 * Set up view control event listeners
 */
function setupViewControlEventListeners() {
    document.querySelectorAll('.wupos-view-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active state from all view buttons
            document.querySelectorAll('.wupos-view-btn').forEach(btn => {
                btn.classList.remove('wupos-view-btn--active');
                btn.setAttribute('aria-pressed', 'false');
            });
            
            // Set active state for clicked button
            this.classList.add('wupos-view-btn--active');
            this.setAttribute('aria-pressed', 'true');
            
            const view = this.dataset.view;
            console.log('Vista cambiada a:', view);
            announceToScreenReader(`Vista cambiada a ${view}`);
        });
    });
}

/**
 * Set up product search and retry event listeners
 */
function setupProductSearchEventListeners() {
    // Search input
    const searchInput = document.getElementById('wupos-search-input');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Debounce search to avoid too many requests
            searchTimeout = setTimeout(() => {
                if (searchTerm.length >= 2 || searchTerm.length === 0) {
                    loadWooCommerceProducts(1, searchTerm);
                }
            }, 300);
        });
    }
    
    // Retry button
    const retryButton = document.getElementById('wupos-retry-products');
    if (retryButton) {
        retryButton.addEventListener('click', function() {
            loadWooCommerceProducts(
                1,
                WUPOS_PRODUCTS_STATE.lastSearchTerm,
                WUPOS_PRODUCTS_STATE.lastCategory
            );
        });
    }
    
    // Clear search button (if exists)
    const clearSearchButton = document.getElementById('wupos-search-clear');
    if (clearSearchButton) {
        clearSearchButton.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                loadWooCommerceProducts(1, '');
            }
        });
    }
}

/**
 * Load WooCommerce products via AJAX
 * Following WordPress AJAX and security standards
 * @param {number} page - Page number for pagination (default: 1)
 * @param {string} search - Search term (default: '')
 * @param {string} category - Category filter (default: '')
 */
function loadWooCommerceProducts(page = 1, search = '', category = '') {
    // Check if wupos_ajax is defined
    if (typeof wupos_ajax === 'undefined') {
        console.error('WUPOS Error: wupos_ajax object not defined');
        showProductsError('Error de configuración: Variables AJAX no definidas');
        return;
    }
    
    if (!wupos_ajax.ajax_url || !wupos_ajax.nonce) {
        console.error('WUPOS Error: wupos_ajax missing required properties', wupos_ajax);
        showProductsError('Error de configuración: URL AJAX o nonce no definido');
        return;
    }
    
    // Prevent multiple simultaneous requests
    if (WUPOS_PRODUCTS_STATE.isLoading) {
        return;
    }

    WUPOS_PRODUCTS_STATE.isLoading = true;
    WUPOS_PRODUCTS_STATE.lastSearchTerm = search;
    WUPOS_PRODUCTS_STATE.lastCategory = category;

    // Show loading state
    showProductsLoadingState();

    // Clear any existing timeout
    if (WUPOS_PRODUCTS_STATE.loadingTimeout) {
        clearTimeout(WUPOS_PRODUCTS_STATE.loadingTimeout);
    }

    // Prepare AJAX data
    const ajaxData = {
        action: 'wupos_get_products',
        page: page,
        search: search,
        category: category,
        nonce: wupos_ajax.nonce
    };

    console.log('WUPOS: Loading products with params:', {page, search, category});
    console.log('WUPOS: AJAX data being sent:', ajaxData);
    console.log('WUPOS: AJAX URL:', wupos_ajax.ajax_url);
    console.log('WUPOS: Nonce:', wupos_ajax.nonce);

    // Make AJAX request
    fetch(wupos_ajax.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(ajaxData)
    })
    .then(response => {
        console.log('WUPOS: Received response status:', response.status);
        console.log('WUPOS: Response headers:', response.headers);
        return response.json();
    })
    .then(data => {
        WUPOS_PRODUCTS_STATE.isLoading = false;
        console.log('WUPOS: Response data:', data);
        
        if (data.success) {
            // Update products cache and pagination
            updateProductsCache(data.data.products);
            updateProductsPagination(data.data);
            
            // Render products grid
            renderProductsGrid(data.data.products);
            
            // Update pagination UI
            updatePaginationUI(data.data);
            
            // Announce success to screen readers
            const productCount = data.data.products.length;
            const message = search 
                ? `Se encontraron ${productCount} productos para "${search}"`
                : `Se cargaron ${productCount} productos`;
            announceToScreenReader(message);
            
        } else {
            console.error('WUPOS Error: Request failed with data:', data);
            showProductsError(data.data || 'Error desconocido cargando productos');
        }
    })
    .catch(error => {
        WUPOS_PRODUCTS_STATE.isLoading = false;
        console.error('WUPOS Error: Network/parsing error:', error);
        console.error('WUPOS Error: Error stack:', error.stack);
        showProductsError('Error de conexión. Verifica tu conexión a internet.');
    });
}

/**
 * Update products cache with new data
 * @param {Array} products - Array of product objects
 */
function updateProductsCache(products) {
    products.forEach(product => {
        WUPOS_PRODUCTS_CACHE[product.id] = {
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            stock: product.stock_quantity || 0,
            stock_status: product.stock_status,
            formatted_price: product.formatted_price,
            image_url: product.image_url,
            image_alt: product.image_alt,
            has_image: product.has_image,
            sku: product.sku,
            short_description: product.short_description,
            tax_info: product.tax_info || {
                tax_enabled: WUPOS_TAX_SETTINGS.tax_enabled,
                tax_suffix: WUPOS_TAX_SETTINGS.tax_suffix,
                tax_inclusive: WUPOS_TAX_SETTINGS.tax_inclusive
            }
        };
    });
}

/**
 * Update pagination data
 * @param {Object} paginationData - Pagination information from API
 */
function updateProductsPagination(paginationData) {
    WUPOS_PRODUCTS_PAGINATION = {
        currentPage: paginationData.current_page,
        totalPages: paginationData.total_pages,
        totalProducts: paginationData.total_products,
        hasMore: paginationData.has_more
    };
}

/**
 * Render products grid with dynamic data
 * @param {Array} products - Array of product objects
 */
function renderProductsGrid(products) {
    const gridContainer = document.getElementById('wupos-product-grid');
    
    // Hide all states first
    hideAllProductStates();
    
    if (!products || products.length === 0) {
        showProductsEmptyState();
        return;
    }
    
    // Clear existing products
    gridContainer.innerHTML = '';
    
    // Create product cards
    products.forEach(product => {
        const productCard = createProductCard(product);
        gridContainer.appendChild(productCard);
    });
    
    // Show grid and setup event listeners
    gridContainer.style.display = 'grid';
    setupProductEventListeners();
    
    console.log(`WUPOS: Rendered ${products.length} products`);
}

/**
 * Create a product card element
 * @param {Object} product - Product data object
 * @returns {HTMLElement} Product card element
 */
function createProductCard(product) {
    const article = document.createElement('article');
    article.className = 'wupos-product-card';
    article.setAttribute('role', 'gridcell');
    article.setAttribute('data-product-id', product.id);
    
    // Set stock status data attributes for styling
    if (product.stock_status === 'outofstock' || (product.stock_quantity && parseInt(product.stock_quantity) === 0)) {
        article.setAttribute('data-out-of-stock', 'true');
    }
    
    // Check if product is already in current cart
    const currentCart = cartsData[currentCartId];
    if (currentCart && currentCart.items.some(item => item.productId === product.id)) {
        article.setAttribute('data-in-cart', 'true');
    }
    
    // Create button element
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'wupos-product-btn';
    button.setAttribute('aria-describedby', `product-${product.id}-details`);
    
    // Create product image
    const imageDiv = document.createElement('div');
    imageDiv.className = 'wupos-product-image';
    imageDiv.setAttribute('role', 'img');
    imageDiv.setAttribute('aria-label', product.image_alt || product.name);
    
    if (product.has_image && product.image_url) {
        const img = document.createElement('img');
        img.src = product.image_url;
        img.alt = product.image_alt || product.name;
        img.loading = 'lazy';
        imageDiv.appendChild(img);
    } else {
        // Fallback icon
        const icon = document.createElement('i');
        icon.className = 'fas fa-cube';
        icon.setAttribute('aria-hidden', 'true');
        imageDiv.appendChild(icon);
    }
    
    // Create product info
    const infoDiv = document.createElement('div');
    infoDiv.className = 'wupos-product-info';
    
    const nameH3 = document.createElement('h3');
    nameH3.className = 'wupos-product-name';
    nameH3.textContent = product.name;
    
    const priceDiv = document.createElement('div');
    priceDiv.className = 'wupos-product-price';
    priceDiv.setAttribute('aria-label', formatCurrencyForAria(product.price));
    
    // Format price without tax suffix (clean product card appearance)
    // Tax suffixes are shown only in cart, not on product cards
    const priceText = formatCurrency(product.price);
    priceDiv.textContent = priceText;
    
    // Create stock badge
    const stockDiv = document.createElement('div');
    const stockInfo = getStockBadgeInfo(product);
    stockDiv.className = `wupos-stock-badge ${stockInfo.badgeClass}`;
    stockDiv.setAttribute('aria-label', stockInfo.ariaLabel);
    stockDiv.textContent = stockInfo.text;
    
    // Assemble elements
    infoDiv.appendChild(nameH3);
    infoDiv.appendChild(priceDiv);
    infoDiv.appendChild(stockDiv);
    
    button.appendChild(imageDiv);
    button.appendChild(infoDiv);
    
    // Create screen reader details
    const details = document.createElement('span');
    details.id = `product-${product.id}-details`;
    details.className = 'sr-only';
    details.textContent = `${product.name}, precio ${product.formatted_price}, ${stockInfo.text}`;
    
    article.appendChild(button);
    article.appendChild(details);
    
    return article;
}

/**
 * Get stock badge information
 * @param {Object} product - Product data
 * @returns {Object} Stock badge info
 */
function getStockBadgeInfo(product) {
    const stockQuantity = parseInt(product.stock_quantity) || 0;
    
    // Handle out of stock
    if (product.stock_status === 'outofstock' || stockQuantity === 0) {
        return {
            badgeClass: 'wupos-stock-out',
            text: 'Sin stock',
            ariaLabel: 'Sin stock disponible'
        };
    }
    
    // Handle backorder
    if (product.stock_status === 'onbackorder') {
        return {
            badgeClass: 'wupos-stock-medium',
            text: 'Bajo pedido',
            ariaLabel: 'Producto disponible bajo pedido'
        };
    }
    
    // If stock quantity is available, show specific numbers with color coding
    if (product.stock_quantity !== null && product.stock_quantity !== undefined) {
        if (stockQuantity <= 2) {
            return {
                badgeClass: 'wupos-stock-low',
                text: `${stockQuantity} en stock`,
                ariaLabel: `Stock bajo: ${stockQuantity} unidades disponibles`
            };
        } else if (stockQuantity <= 10) {
            return {
                badgeClass: 'wupos-stock-medium',
                text: `${stockQuantity} en stock`,
                ariaLabel: `Stock medio: ${stockQuantity} unidades disponibles`
            };
        } else {
            return {
                badgeClass: 'wupos-stock-high',
                text: `${stockQuantity} en stock`,
                ariaLabel: `Stock alto: ${stockQuantity} unidades disponibles`
            };
        }
    }
    
    // Fallback for products without stock management - show as in stock
    return {
        badgeClass: 'wupos-stock-high',
        text: 'En stock',
        ariaLabel: 'Producto en stock'
    };
}

/**
 * Show products loading state
 */
function showProductsLoadingState() {
    hideAllProductStates();
    document.getElementById('wupos-products-loading').style.display = 'block';
    announceToScreenReader('Cargando productos...');
}

/**
 * Show products error state
 * @param {string} errorMessage - Error message to display
 */
function showProductsError(errorMessage) {
    hideAllProductStates();
    document.getElementById('wupos-error-message').textContent = errorMessage;
    document.getElementById('wupos-products-error').style.display = 'block';
    announceToScreenReader(`Error: ${errorMessage}`, true);
}

/**
 * Show products empty state
 */
function showProductsEmptyState() {
    hideAllProductStates();
    document.getElementById('wupos-products-empty').style.display = 'block';
    announceToScreenReader('No se encontraron productos');
}

/**
 * Hide all product states
 */
function hideAllProductStates() {
    document.getElementById('wupos-products-loading').style.display = 'none';
    document.getElementById('wupos-products-error').style.display = 'none';
    document.getElementById('wupos-products-empty').style.display = 'none';
    document.getElementById('wupos-product-grid').style.display = 'none';
}

/**
 * Update pagination UI
 * @param {Object} paginationData - Pagination data from API
 */
function updatePaginationUI(paginationData) {
    const paginationContainer = document.getElementById('wupos-products-pagination');
    const prevButton = document.getElementById('wupos-prev-page');
    const nextButton = document.getElementById('wupos-next-page');
    const paginationInfo = document.getElementById('wupos-pagination-info');
    
    if (paginationData.total_pages <= 1) {
        paginationContainer.style.display = 'none';
        return;
    }
    
    // Show pagination
    paginationContainer.style.display = 'flex';
    
    // Update buttons state
    prevButton.disabled = paginationData.current_page <= 1;
    nextButton.disabled = paginationData.current_page >= paginationData.total_pages;
    
    // Update info text
    paginationInfo.textContent = `Página ${paginationData.current_page} de ${paginationData.total_pages}`;
    
    // Add event listeners if not already added
    if (!prevButton.hasAttribute('data-listener-added')) {
        prevButton.addEventListener('click', () => {
            if (WUPOS_PRODUCTS_PAGINATION.currentPage > 1) {
                loadWooCommerceProducts(
                    WUPOS_PRODUCTS_PAGINATION.currentPage - 1,
                    WUPOS_PRODUCTS_STATE.lastSearchTerm,
                    WUPOS_PRODUCTS_STATE.lastCategory
                );
            }
        });
        prevButton.setAttribute('data-listener-added', 'true');
    }
    
    if (!nextButton.hasAttribute('data-listener-added')) {
        nextButton.addEventListener('click', () => {
            if (WUPOS_PRODUCTS_PAGINATION.hasMore) {
                loadWooCommerceProducts(
                    WUPOS_PRODUCTS_PAGINATION.currentPage + 1,
                    WUPOS_PRODUCTS_STATE.lastSearchTerm,
                    WUPOS_PRODUCTS_STATE.lastCategory
                );
            }
        });
        nextButton.setAttribute('data-listener-added', 'true');
    }
}

/**
 * Add product to cart with proper validation and duplicate handling
 * Following WordPress security and accessibility standards
 * @param {string} productId - Product ID to add
 * @param {number} quantity - Quantity to add (default: 1)
 */
function addToCart(productId, quantity = 1) {
    try {
        // Validate input parameters
        if (!productId || !WUPOS_PRODUCTS_CACHE[productId]) {
            console.error('WUPOS Error: Producto no válido:', productId);
            announceToScreenReader('Error: Producto no encontrado', true);
            return false;
        }
        
        // Validate quantity
        const qty = Math.max(WUPOS_CART_CONFIG.minQuantityPerItem, parseInt(quantity, 10) || 1);
        const productStock = WUPOS_PRODUCTS_CACHE[productId].stock_quantity || 0;
        const maxQty = Math.min(WUPOS_CART_CONFIG.maxQuantityPerItem, productStock);
        
        // Check stock status
        if (WUPOS_PRODUCTS_CACHE[productId].stock_status === 'outofstock') {
            announceToScreenReader('Producto sin stock disponible', true);
            return false;
        }
        
        if (qty > maxQty && maxQty > 0) {
            announceToScreenReader(`Stock insuficiente. Máximo disponible: ${maxQty}`, true);
            return false;
        }
        
        // Get current cart
        const currentCart = cartsData[currentCartId];
        if (!currentCart) {
            console.error('WUPOS Error: Carrito actual no encontrado');
            return false;
        }
        
        // Check if product already exists in cart
        const existingItemIndex = currentCart.items.findIndex(item => item.productId === productId);
        
        if (existingItemIndex !== -1) {
            // Update existing item quantity
            const newQuantity = currentCart.items[existingItemIndex].quantity + qty;
            
            if (newQuantity > maxQty) {
                announceToScreenReader(`No se puede agregar más. Stock máximo: ${maxQty}`, true);
                return false;
            }
            
            currentCart.items[existingItemIndex].quantity = newQuantity;
            currentCart.items[existingItemIndex].total = newQuantity * currentCart.items[existingItemIndex].price;
            
        } else {
            // Add new item to cart
            const product = WUPOS_PRODUCTS_CACHE[productId];
            const newItem = {
                id: generateItemId(),
                productId: productId,
                name: product.name,
                price: product.price,
                quantity: qty,
                total: product.price * qty
            };
            
            currentCart.items.push(newItem);
        }
        
        // Update cart totals and UI
        calculateCartTotals(currentCart);
        updateCartUI();
        updateCartTabs();
        saveCartsToStorage();
        
        // Update product card visual state
        updateProductCardState(productId, true);
        
        // Accessibility announcement
        const productName = WUPOS_PRODUCTS_CACHE[productId].name;
        announceToScreenReader(`${productName} agregado al carrito. Cantidad: ${qty}`);
        
        console.log('WUPOS: Producto agregado exitosamente:', productName, 'Cantidad:', qty);
        return true;
        
    } catch (error) {
        console.error('WUPOS Error: Error agregando producto al carrito:', error);
        announceToScreenReader('Error agregando producto al carrito', true);
        return false;
    }
}

/**
 * Remove item from cart with proper validation
 * @param {string} itemId - Item ID to remove
 */
function removeFromCart(itemId) {
    try {
        if (!itemId) {
            console.error('WUPOS Error: ID de item no válido');
            return false;
        }
        
        const currentCart = cartsData[currentCartId];
        if (!currentCart) {
            console.error('WUPOS Error: Carrito actual no encontrado');
            return false;
        }
        
        // Find and remove item
        const itemIndex = currentCart.items.findIndex(item => item.id === itemId);
        
        if (itemIndex === -1) {
            console.error('WUPOS Error: Item no encontrado en el carrito:', itemId);
            announceToScreenReader('Error: Producto no encontrado en el carrito', true);
            return false;
        }
        
        // Get item data for announcement and state update before removal
        const removedItem = currentCart.items[itemIndex];
        const itemName = removedItem.name;
        const removedProductId = removedItem.productId;
        
        // Remove item from cart
        currentCart.items.splice(itemIndex, 1);
        
        // Update cart totals and UI
        calculateCartTotals(currentCart);
        updateCartUI();
        updateCartTabs();
        saveCartsToStorage();
        
        // Update product card visual state (check if product is still in cart)
        const stillInCart = currentCart.items.some(item => item.productId === removedProductId);
        if (!stillInCart) {
            updateProductCardState(removedProductId, false);
        }
        
        // Accessibility announcement
        announceToScreenReader(`${itemName} eliminado del carrito`);
        
        console.log('WUPOS: Item eliminado exitosamente:', itemName);
        return true;
        
    } catch (error) {
        console.error('WUPOS Error: Error eliminando item del carrito:', error);
        announceToScreenReader('Error eliminando producto del carrito', true);
        return false;
    }
}

/**
 * Clear current cart with confirmation
 */
function clearCurrentCart() {
    try {
        if (!confirm('¿Está seguro de que desea limpiar el carrito actual?')) {
            return false;
        }
        
        const currentCart = cartsData[currentCartId];
        if (!currentCart) {
            console.error('WUPOS Error: Carrito actual no encontrado');
            return false;
        }
        
        // Clear cart items and reset totals
        currentCart.items = [];
        currentCart.subtotal = 0;
        currentCart.tax = 0;
        currentCart.total = 0;
        currentCart.note = '';
        currentCart.updatedAt = new Date().toISOString();
        
        // Update UI and save
        updateCartUI();
        updateCartTabs();
        updateAllProductCardStates();
        saveCartsToStorage();
        
        // Clear note button state
        const noteBtn = document.getElementById('noteBtn');
        if (noteBtn) {
            delete noteBtn.dataset.note;
            noteBtn.classList.remove('has-note');
            noteBtn.setAttribute('title', 'Agregar nota al pedido');
        }
        
        // Accessibility announcement
        announceToScreenReader('Carrito limpiado completamente');
        
        console.log('WUPOS: Carrito limpiado exitosamente');
        return true;
        
    } catch (error) {
        console.error('WUPOS Error: Error limpiando carrito:', error);
        announceToScreenReader('Error limpiando carrito', true);
        return false;
    }
}

/**
 * Update item quantity in cart
 * @param {string} itemId - Item ID to update
 * @param {number} newQuantity - New quantity value
 */
function updateItemQuantity(itemId, newQuantity) {
    try {
        if (!itemId) {
            console.error('WUPOS Error: ID de item no válido');
            return false;
        }
        
        const currentCart = cartsData[currentCartId];
        if (!currentCart) {
            console.error('WUPOS Error: Carrito actual no encontrado');
            return false;
        }
        
        // Find item in cart
        const itemIndex = currentCart.items.findIndex(item => item.id === itemId);
        
        if (itemIndex === -1) {
            console.error('WUPOS Error: Item no encontrado:', itemId);
            return false;
        }
        
        // Validate quantity
        const qty = Math.max(WUPOS_CART_CONFIG.minQuantityPerItem, parseInt(newQuantity, 10) || 1);
        const productId = currentCart.items[itemIndex].productId;
        const productStock = WUPOS_PRODUCTS_CACHE[productId]?.stock_quantity || 999;
        const maxQty = Math.min(WUPOS_CART_CONFIG.maxQuantityPerItem, productStock);
        
        if (qty > maxQty) {
            // Reset input to previous valid value
            const input = document.querySelector(`[data-item-id="${itemId}"] .wupos-qty-input`);
            if (input) {
                input.value = currentCart.items[itemIndex].quantity;
            }
            announceToScreenReader(`Stock insuficiente. Máximo disponible: ${maxQty}`, true);
            return false;
        }
        
        // Update item
        currentCart.items[itemIndex].quantity = qty;
        currentCart.items[itemIndex].total = qty * currentCart.items[itemIndex].price;
        
        // Update cart totals and UI
        calculateCartTotals(currentCart);
        updateCartUI();
        updateCartTabs();
        saveCartsToStorage();
        
        // Accessibility announcement
        const itemName = currentCart.items[itemIndex].name;
        announceToScreenReader(`Cantidad de ${itemName} actualizada a ${qty}`);
        
        return true;
        
    } catch (error) {
        console.error('WUPOS Error: Error actualizando cantidad:', error);
        announceToScreenReader('Error actualizando cantidad del producto', true);
        return false;
    }
}

/**
 * Update item price in cart
 * @param {string} itemId - Item ID to update
 * @param {number} newPrice - New price value
 */
function updateItemPrice(itemId, newPrice) {
    try {
        if (!itemId) {
            console.error('WUPOS Error: ID de item no válido');
            return false;
        }
        
        const currentCart = cartsData[currentCartId];
        if (!currentCart) {
            console.error('WUPOS Error: Carrito actual no encontrado');
            return false;
        }
        
        // Find item in cart
        const itemIndex = currentCart.items.findIndex(item => item.id === itemId);
        
        if (itemIndex === -1) {
            console.error('WUPOS Error: Item no encontrado:', itemId);
            return false;
        }
        
        // Validate price
        const price = Math.max(0, parseFloat(newPrice) || 0);
        
        if (price === 0) {
            // Reset input to previous valid value
            const input = document.querySelector(`[data-item-id="${itemId}"] .wupos-item-price-input`);
            if (input) {
                input.value = formatCurrency(currentCart.items[itemIndex].price);
            }
            announceToScreenReader('Precio no válido', true);
            return false;
        }
        
        // Update item
        currentCart.items[itemIndex].price = price;
        currentCart.items[itemIndex].total = currentCart.items[itemIndex].quantity * price;
        
        // Update cart totals and UI
        calculateCartTotals(currentCart);
        updateCartUI();
        updateCartTabs();
        saveCartsToStorage();
        
        // Update price input formatting
        const input = document.querySelector(`[data-item-id="${itemId}"] .wupos-item-price-input`);
        if (input) {
            input.value = formatCurrency(price);
        }
        
        // Accessibility announcement
        const itemName = currentCart.items[itemIndex].name;
        announceToScreenReader(`Precio de ${itemName} actualizado a ${formatCurrency(price)}`);
        
        return true;
        
    } catch (error) {
        console.error('WUPOS Error: Error actualizando precio:', error);
        announceToScreenReader('Error actualizando precio del producto', true);
        return false;
    }
}

/**
 * Multi-cart system functions
 */

/**
 * Create a new cart and switch to it
 */
function createNewCart() {
    try {
        // Find next available cart ID
        let newCartId = 1;
        while (cartsData[newCartId] && Object.keys(cartsData).length < WUPOS_CART_CONFIG.maxCarts) {
            newCartId++;
        }
        
        if (Object.keys(cartsData).length >= WUPOS_CART_CONFIG.maxCarts) {
            announceToScreenReader('Máximo número de carritos alcanzado', true);
            return false;
        }
        
        // Create new cart
        cartsData[newCartId] = createEmptyCart();
        cartsData[newCartId].id = newCartId;
        
        // Switch to new cart
        switchToCart(newCartId);
        
        announceToScreenReader(`Nuevo carrito ${newCartId} creado`);
        console.log('WUPOS: Nuevo carrito creado:', newCartId);
        
        return true;
        
    } catch (error) {
        console.error('WUPOS Error: Error creando nuevo carrito:', error);
        announceToScreenReader('Error creando nuevo carrito', true);
        return false;
    }
}

/**
 * Switch to a different cart
 * @param {number} cartId - Cart ID to switch to
 */
function switchToCart(cartId) {
    try {
        if (!cartsData[cartId]) {
            console.error('WUPOS Error: Carrito no encontrado:', cartId);
            return false;
        }
        
        // Update current cart ID
        currentCartId = cartId;
        
        // Update UI
        updateCartUI();
        updateCartTabs();
        updateAllProductCardStates();
        saveCartsToStorage();
        
        announceToScreenReader(`Carrito ${cartId} seleccionado`);
        console.log('WUPOS: Carrito cambiado a:', cartId);
        
        return true;
        
    } catch (error) {
        console.error('WUPOS Error: Error cambiando carrito:', error);
        announceToScreenReader('Error cambiando carrito', true);
        return false;
    }
}

/**
 * Calculate cart totals with WooCommerce tax calculation
 * @param {Object} cart - Cart object to calculate
 * @param {boolean} forceRecalculate - Force tax recalculation even if in progress
 */
function calculateCartTotals(cart, forceRecalculate = false) {
    try {
        if (!cart || !Array.isArray(cart.items)) {
            console.error('WUPOS Error: Estructura de carrito inválida');
            return;
        }
        
        // Calculate subtotal
        cart.subtotal = cart.items.reduce((sum, item) => {
            return sum + (parseFloat(item.total) || 0);
        }, 0);
        
        // If cart is empty, reset totals
        if (cart.items.length === 0) {
            cart.tax = 0;
            cart.total = cart.subtotal;
            cart.taxBreakdown = [];
            cart.updatedAt = new Date().toISOString();
            return;
        }
        
        // Use cached tax calculation if available and cart hasn't changed
        if (lastTaxCalculation && 
            lastTaxCalculation.cartId === cart.id && 
            lastTaxCalculation.subtotal === cart.subtotal &&
            lastTaxCalculation.itemsHash === getCartItemsHash(cart.items) &&
            !forceRecalculate) {
            
            cart.tax = lastTaxCalculation.tax;
            cart.total = cart.subtotal + cart.tax;
            cart.taxBreakdown = lastTaxCalculation.taxBreakdown || [];
            cart.updatedAt = new Date().toISOString();
            return;
        }
        
        // Set temporary values while calculating
        if (!cart.hasOwnProperty('tax')) {
            cart.tax = 0;
        }
        cart.total = cart.subtotal + cart.tax;
        cart.updatedAt = new Date().toISOString();
        
        // Calculate WooCommerce taxes asynchronously only if taxes are enabled
        if (cart.taxEnabled) {
            calculateWooCommerceTaxes(cart);
        } else {
            // Taxes disabled - ensure tax display shows correct state
            cart.tax = 0;
            cart.taxBreakdown = [];
            updateTaxDisplay(cart, {
                tax_enabled: false,
                tax_breakdown: [],
                tax_inclusive: cart.taxInclusive,
                tax_suffix: cart.taxSuffix
            });
        }
        
    } catch (error) {
        console.error('WUPOS Error: Error calculando totales:', error);
        
        // Set safe defaults
        cart.subtotal = 0;
        cart.tax = 0;
        cart.total = 0;
        cart.taxBreakdown = [];
    }
}

/**
 * Generate hash for cart items to detect changes
 * @param {Array} items - Cart items array
 * @return {string} Hash string
 */
function getCartItemsHash(items) {
    const itemsString = items.map(item => `${item.id}-${item.quantity}-${item.price}`).join('|');
    return btoa(itemsString).replace(/[^a-zA-Z0-9]/g, '');
}

/**
 * Calculate WooCommerce taxes for cart
 * @param {Object} cart - Cart object
 */
function calculateWooCommerceTaxes(cart) {
    if (taxCalculationInProgress) {
        return;
    }
    
    taxCalculationInProgress = true;
    
    // Show loading state in tax section
    updateTaxDisplay(cart, { loading: true });
    
    // Prepare cart items for API
    const cartItems = cart.items.map(item => ({
        id: item.id,
        quantity: item.quantity,
        price: item.price
    }));
    
    // Prepare request data
    const requestData = {
        action: 'wupos_calculate_taxes',
        nonce: WUPOS_CART_CONFIG.nonce,
        cart_items: JSON.stringify(cartItems),
        customer_data: JSON.stringify({}) // TODO: Add customer location data if needed
    };
    
    // Debug log request data
    console.log('WUPOS DEBUG: Making tax calculation request:', requestData);
    console.log('WUPOS DEBUG: Cart items being sent:', cartItems);
    
    // Make AJAX request
    fetch(WUPOS_CART_CONFIG.taxCalculationEndpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(requestData)
    })
    .then(response => {
        console.log('WUPOS DEBUG: Response status:', response.status);
        console.log('WUPOS DEBUG: Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.text().then(text => {
            console.log('WUPOS DEBUG: Raw response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('WUPOS DEBUG: JSON parse error:', e);
                console.error('WUPOS DEBUG: Response text that failed to parse:', text);
                throw new Error('Invalid JSON response');
            }
        });
    })
    .then(data => {
        taxCalculationInProgress = false;
        console.log('WUPOS DEBUG: Parsed response data:', data);
        
        if (data.success && data.data) {
            const taxData = data.data;
            
            // Update cart with tax data
            cart.tax = parseFloat(taxData.tax_total) || 0;
            cart.subtotal = parseFloat(taxData.subtotal) || 0;
            cart.total = parseFloat(taxData.total) || 0;
            cart.taxBreakdown = taxData.tax_breakdown || [];
            cart.taxEnabled = taxData.tax_enabled;
            cart.taxInclusive = taxData.tax_inclusive;
            cart.taxSuffix = taxData.tax_suffix || '';
            
            // Cache the calculation
            lastTaxCalculation = {
                cartId: cart.id,
                subtotal: cart.subtotal,
                itemsHash: getCartItemsHash(cart.items),
                tax: cart.tax,
                taxBreakdown: cart.taxBreakdown
            };
            
            // Update UI
            console.log('WUPOS: Tax calculation completed for cart:', cart.id, 'Tax data:', taxData);
            updateCartUI();
            updateTaxDisplay(cart, taxData);
            
        } else {
            console.error('WUPOS Error: Tax calculation failed - Success flag false or no data');
            console.error('WUPOS Error: Full response:', data);
            updateTaxDisplay(cart, { error: data.data || 'Tax calculation failed' });
        }
    })
    .catch(error => {
        taxCalculationInProgress = false;
        console.error('WUPOS Error: Tax calculation request failed:', error);
        updateTaxDisplay(cart, { error: 'Network error calculating taxes' });
    });
}

/**
 * Update tax display in UI
 * @param {Object} cart - Cart object
 * @param {Object} options - Display options (loading, error, etc.)
 */
function updateTaxDisplay(cart, options = {}) {
    const taxSection = document.getElementById('wupos-tax-section');
    const taxFallback = document.getElementById('wupos-tax-fallback');
    const taxElement = document.getElementById('wupos-tax');
    const taxLabel = document.getElementById('wupos-tax-label');
    
    if (!taxSection || !taxFallback) {
        return;
    }
    
    // Handle loading state
    if (options.loading) {
        taxSection.innerHTML = `
            <div class="wupos-tax-loading" role="status" aria-live="polite">
                <span class="sr-only">Calculando impuestos...</span>
                Calculando impuestos...
            </div>
        `;
        taxSection.style.display = 'block';
        return;
    }
    
    // Handle error state
    if (options.error) {
        const errorMessage = escapeHtml(options.error);
        taxSection.innerHTML = `
            <div class="wupos-tax-error" role="alert" aria-live="assertive">
                <span class="sr-only">Error: </span>
                ${errorMessage}
            </div>
        `;
        taxSection.style.display = 'block';
        
        // Also update fallback
        if (taxElement) {
            taxElement.textContent = formatCurrency(cart.tax || 0);
            taxElement.setAttribute('aria-label', `Impuestos: ${formatCurrencyForAria(cart.tax || 0)}`);
        }
        
        // Announce error to screen readers
        announceToScreenReader(`Error calculando impuestos: ${errorMessage}`, true);
        return;
    }
    
    // Check if taxes are enabled and there's breakdown data
    if (options.tax_enabled && options.tax_breakdown && options.tax_breakdown.length > 0) {
        // Show detailed tax breakdown
        let breakdownHTML = '';
        let screenReaderText = 'Desglose de impuestos: ';
        
        options.tax_breakdown.forEach((taxItem, index) => {
            const taxAmount = parseFloat(taxItem.amount) || 0;
            const taxLabel = escapeHtml(taxItem.label);
            const formattedAmount = escapeHtml(taxItem.formatted_amount);
            
            breakdownHTML += `
                <div class="wupos-tax-breakdown-item" role="listitem">
                    <span class="wupos-tax-breakdown-label">${taxLabel}:</span>
                    <span class="wupos-tax-breakdown-amount" 
                          aria-label="${taxLabel}: ${formatCurrencyForAria(taxAmount)}">${formattedAmount}</span>
                </div>
            `;
            
            // Build screen reader announcement
            screenReaderText += `${taxLabel}: ${formatCurrencyForAria(taxAmount)}`;
            if (index < options.tax_breakdown.length - 1) {
                screenReaderText += ', ';
            }
        });
        
        // Add total if multiple tax rates
        if (options.tax_breakdown.length > 1) {
            const taxSuffix = options.tax_suffix || cart.taxSuffix || '';
            const suffixDisplay = taxSuffix ? ` (${taxSuffix})` : '';
            
            breakdownHTML += `
                <div class="wupos-tax-breakdown-item wupos-tax-breakdown-total" role="listitem">
                    <span class="wupos-tax-breakdown-label">Total Impuestos${suffixDisplay}:</span>
                    <span class="wupos-tax-breakdown-amount" 
                          aria-label="Total impuestos: ${formatCurrencyForAria(cart.tax)}">${formatCurrency(cart.tax)}</span>
                </div>
            `;
            
            screenReaderText += `. Total impuestos: ${formatCurrencyForAria(cart.tax)}`;
        }
        
        // Wrap in accessible container
        taxSection.innerHTML = `
            <div role="list" aria-label="Desglose de impuestos aplicados">
                ${breakdownHTML}
            </div>
        `;
        taxSection.style.display = 'block';
        
        // Announce changes to screen readers (but not on initial load)
        if (!options.initialLoad) {
            announceToScreenReader(screenReaderText);
        }
        
    } else {
        // Use fallback display
        taxSection.style.display = 'none';
        
        if (taxElement && taxLabel) {
            const taxSuffix = options.tax_suffix || cart.taxSuffix || '';
            const suffixDisplay = taxSuffix ? ` (${taxSuffix})` : '';
            
            if (options.tax_enabled === false) {
                taxLabel.textContent = 'Impuestos (no aplicable):';
                taxElement.textContent = formatCurrency(0);
                taxElement.setAttribute('aria-label', 'Impuestos no aplicables');
                
                // Add semantic meaning for screen readers
                taxFallback.setAttribute('role', 'status');
                taxFallback.setAttribute('aria-live', 'polite');
            } else {
                taxLabel.textContent = `Impuestos${suffixDisplay}:`;
                taxElement.textContent = formatCurrency(cart.tax || 0);
                taxElement.setAttribute('aria-label', `Impuestos: ${formatCurrencyForAria(cart.tax || 0)}`);
                
                // Add semantic meaning for screen readers
                taxFallback.setAttribute('role', 'status');
                taxFallback.setAttribute('aria-live', 'polite');
            }
        }
    }
}

/**
 * Escape HTML characters for safe display
 * @param {string} unsafe - Unsafe string
 * @return {string} Safe HTML string
 */
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Update cart UI with current cart data
 * Maintains exact wireframe structure while updating content
 */
function updateCartUI() {
    try {
        const currentCart = cartsData[currentCartId];
        if (!currentCart) {
            console.error('WUPOS Error: Carrito actual no encontrado para UI update');
            return;
        }
        
        const cartItemsContainer = document.getElementById('wupos-cart-items');
        if (!cartItemsContainer) {
            console.error('WUPOS Error: Contenedor de items del carrito no encontrado');
            return;
        }
        
        // Clear existing items
        cartItemsContainer.innerHTML = '';
        
        // Add cart items
        currentCart.items.forEach((item) => {
            const itemElement = createCartItemElement(item);
            cartItemsContainer.appendChild(itemElement);
        });
        
        // Update totals
        updateCartTotals(currentCart);
        
        // Update checkout button
        updateCheckoutButton(currentCart);
        
    } catch (error) {
        console.error('WUPOS Error: Error actualizando UI del carrito:', error);
        announceToScreenReader('Error actualizando vista del carrito', true);
    }
}

/**
 * Create cart item DOM element maintaining wireframe structure
 * @param {Object} item - Cart item object
 * @returns {HTMLElement} Cart item element
 */
function createCartItemElement(item) {
    const article = document.createElement('article');
    article.className = 'wupos-cart-item';
    article.setAttribute('role', 'row');
    article.setAttribute('data-item-id', item.id);
    
    article.innerHTML = `
        <div class="wupos-item-quantity" role="cell">
            <label for="qty-${item.id}" class="sr-only">Cantidad para ${escapeHtml(item.name)}</label>
            <input 
                type="number" 
                id="qty-${item.id}"
                class="wupos-qty-input" 
                value="${item.quantity}" 
                min="1" 
                max="999"
                aria-describedby="qty-${item.id}-description"
            >
            <span id="qty-${item.id}-description" class="sr-only">Ingrese la cantidad de unidades</span>
        </div>
        
        <div class="wupos-item-name" role="cell">
            <label for="name-${item.id}" class="sr-only">Nombre del producto</label>
            <input 
                type="text" 
                id="name-${item.id}"
                class="wupos-item-name-input" 
                value="${escapeHtml(item.name)}" 
                placeholder="Nombre del producto"
            >
        </div>
        
        <div class="wupos-item-price" role="cell">
            <label for="price-${item.id}" class="sr-only">Precio unitario</label>
            <input 
                type="text" 
                id="price-${item.id}"
                class="wupos-item-price-input" 
                value="${formatCurrency(item.price)}" 
                placeholder="$0.00"
                aria-describedby="price-${item.id}-description"
            >
            <span id="price-${item.id}-description" class="sr-only">Precio por unidad del producto</span>
        </div>
        
        <div class="wupos-item-total" role="cell" aria-label="Total del item: ${formatCurrencyForAria(item.total)}">${formatCurrency(item.total)}</div>
        
        <div class="wupos-item-actions" role="cell">
            <button 
                type="button"
                class="wupos-remove-btn" 
                aria-label="Eliminar ${escapeHtml(item.name)} del carrito"
                title="Eliminar este item del carrito"
            >
                <i class="fas fa-trash" aria-hidden="true"></i>
                <span class="sr-only">Eliminar</span>
            </button>
        </div>
    `;
    
    return article;
}

/**
 * Update cart totals display
 * @param {Object} cart - Cart object with calculated totals
 */
function updateCartTotals(cart) {
    try {
        // Update subtotal with tax suffix if applicable
        const subtotalElement = document.getElementById('wupos-subtotal');
        if (subtotalElement) {
            let subtotalText = formatCurrency(cart.subtotal);
            
            // Add tax suffix for tax-exclusive pricing
            if (cart.taxSuffix && cart.taxEnabled && !cart.taxInclusive) {
                subtotalText += ` ${cart.taxSuffix}`;
            }
            
            subtotalElement.textContent = subtotalText;
            subtotalElement.setAttribute('aria-label', `Subtotal: ${formatCurrencyForAria(cart.subtotal)}`);
        }
        
        // Tax display is handled by updateTaxDisplay() function
        
        // Update total with tax suffix if applicable
        const totalElement = document.getElementById('wupos-total');
        if (totalElement) {
            let totalText = formatCurrency(cart.total);
            
            // Add tax suffix based on WooCommerce configuration
            if (cart.taxSuffix && cart.taxEnabled) {
                // For tax-inclusive pricing, show that taxes are included in total
                // For tax-exclusive pricing, show that taxes have been added
                totalText += ` ${cart.taxSuffix}`;
            }
            
            totalElement.textContent = totalText;
            totalElement.setAttribute('aria-label', `Total a pagar: ${formatCurrencyForAria(cart.total)}`);
        }
        
        // Update tax display with current cart data (determine if this is initial load)
        const isInitialLoad = !cart.items || cart.items.length === 0;
        const hasValidTaxData = cart.taxBreakdown && cart.taxBreakdown.length > 0;
        
        // Check if tax calculation is in progress or if we have valid tax data
        if (taxCalculationInProgress) {
            // Show loading state while calculation is in progress
            updateTaxDisplay(cart, { loading: true });
        } else if (!isInitialLoad && cart.taxEnabled && !hasValidTaxData && cart.items.length > 0) {
            // Show loading state if we have items but no tax data yet
            updateTaxDisplay(cart, { loading: true });
        } else {
            // Show normal tax display
            const taxData = {
                tax_enabled: cart.taxEnabled !== false, // Default to true if not set
                tax_breakdown: cart.taxBreakdown || [],
                tax_inclusive: cart.taxInclusive || false,
                tax_suffix: cart.taxSuffix || '',
                initialLoad: isInitialLoad // Prevent screen reader announcements on initial load
            };
            updateTaxDisplay(cart, taxData);
        }
        
    } catch (error) {
        console.error('WUPOS Error: Error actualizando totales en UI:', error);
    }
}

/**
 * Update cart tabs display
 */
function updateCartTabs() {
    try {
        const tabsContainer = document.querySelector('.wupos-pending-carts-container');
        if (!tabsContainer) {
            console.error('WUPOS Error: Contenedor de tabs no encontrado');
            return;
        }
        
        // Clear existing tabs (except new cart button)
        const newCartBtn = tabsContainer.querySelector('.wupos-cart-tab--new');
        tabsContainer.innerHTML = '';
        
        // Add cart tabs
        Object.keys(cartsData).sort((a, b) => parseInt(a) - parseInt(b)).forEach(cartId => {
            const cart = cartsData[cartId];
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `wupos-cart-tab ${cartId == currentCartId ? 'wupos-cart-tab--active' : ''}`;
            button.setAttribute('data-cart', cartId);
            button.setAttribute('role', 'tab');
            button.setAttribute('aria-selected', cartId == currentCartId ? 'true' : 'false');
            button.setAttribute('aria-controls', `cart-panel-${cartId}`);
            button.setAttribute('aria-describedby', `cart-${cartId}-description`);
            button.textContent = formatCurrency(cart.total);
            
            tabsContainer.appendChild(button);
            
            // Add description span
            const description = document.createElement('span');
            description.id = `cart-${cartId}-description`;
            description.className = 'sr-only';
            description.textContent = `Carrito ${cartId} ${cartId == currentCartId ? 'activo' : 'en espera'} con total de ${formatCurrencyForAria(cart.total)}`;
            tabsContainer.appendChild(description);
        });
        
        // Re-add new cart button
        if (newCartBtn) {
            tabsContainer.appendChild(newCartBtn);
        }
        
    } catch (error) {
        console.error('WUPOS Error: Error actualizando tabs del carrito:', error);
    }
}

/**
 * Update product card visual state
 * @param {string} productId - Product ID
 * @param {boolean} inCart - Whether product is in cart
 */
function updateProductCardState(productId, inCart) {
    try {
        const productCard = document.querySelector(`[data-product-id="${productId}"]`);
        if (productCard) {
            if (inCart) {
                productCard.setAttribute('data-in-cart', 'true');
            } else {
                productCard.removeAttribute('data-in-cart');
            }
        }
    } catch (error) {
        console.error('WUPOS Error: Error updating product card state:', error);
    }
}

/**
 * Update all product cards states based on current cart
 */
function updateAllProductCardStates() {
    try {
        const currentCart = cartsData[currentCartId];
        if (!currentCart) return;
        
        // Reset all cards
        document.querySelectorAll('.wupos-product-card[data-in-cart]').forEach(card => {
            card.removeAttribute('data-in-cart');
        });
        
        // Set state for products in cart
        currentCart.items.forEach(item => {
            updateProductCardState(item.productId, true);
        });
        
    } catch (error) {
        console.error('WUPOS Error: Error updating all product card states:', error);
    }
}

/**
 * Update checkout button with current total
 * @param {Object} cart - Current cart object
 */
function updateCheckoutButton(cart) {
    try {
        const checkoutBtn = document.getElementById('wupos-checkout');
        if (checkoutBtn) {
            const span = checkoutBtn.querySelector('span');
            if (span) {
                span.textContent = `PAGAR ${formatCurrency(cart.total)}`;
            }
            
            // Update accessibility description
            const description = document.getElementById('process-sale-description');
            if (description) {
                description.textContent = `Procesar el pago y completar la venta por un total de ${formatCurrencyForAria(cart.total)}`;
            }
        }
        
    } catch (error) {
        console.error('WUPOS Error: Error actualizando botón de checkout:', error);
    }
}

/**
 * Process cart checkout (placeholder for now)
 */
function processCartCheckout() {
    try {
        const currentCart = cartsData[currentCartId];
        if (!currentCart || currentCart.items.length === 0) {
            announceToScreenReader('El carrito está vacío', true);
            return false;
        }
        
        console.log('WUPOS: Iniciando proceso de pago para carrito:', currentCartId);
        console.log('Total a pagar:', formatCurrency(currentCart.total));
        
        announceToScreenReader('Iniciando proceso de pago');
        
        // TODO: Implement actual checkout process in future phases
        alert(`Proceso de pago iniciado\nTotal: ${formatCurrency(currentCart.total)}\n\n(Funcionalidad completa será implementada en futuras fases)`);
        
        return true;
        
    } catch (error) {
        console.error('WUPOS Error: Error en proceso de checkout:', error);
        announceToScreenReader('Error procesando el pago', true);
        return false;
    }
}

/**
 * Utility functions
 */

/**
 * Generate unique item ID
 * @returns {string} Unique item ID
 */
function generateItemId() {
    return 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

/**
 * Format currency value
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    const num = parseFloat(amount) || 0;
    const formattedAmount = num.toFixed(WUPOS_CART_CONFIG.decimalPlaces);
    let symbol = WUPOS_CART_CONFIG.currencySymbol;
    
    // Ensure symbol is properly decoded (fallback safety)
    if (symbol.includes('&#')) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = symbol;
        symbol = tempDiv.textContent || tempDiv.innerText || '$';
    }
    
    // Use WooCommerce currency position if available
    if (WUPOS_TAX_SETTINGS.loaded && WUPOS_TAX_SETTINGS.currency_position) {
        switch (WUPOS_TAX_SETTINGS.currency_position) {
            case 'left':
                return symbol + formattedAmount;
            case 'right':
                return formattedAmount + symbol;
            case 'left_space':
                return symbol + ' ' + formattedAmount;
            case 'right_space':
                return formattedAmount + ' ' + symbol;
            default:
                return symbol + formattedAmount;
        }
    }
    
    // Fallback to default left position
    return symbol + formattedAmount;
}

/**
 * Format currency for screen reader announcements
 * @param {number} amount - Amount to format
 * @returns {string} Screen reader friendly currency string
 */
function formatCurrencyForAria(amount) {
    const num = parseFloat(amount) || 0;
    const formatted = num.toFixed(WUPOS_CART_CONFIG.decimalPlaces);
    const [dollars, cents] = formatted.split('.');
    
    if (cents === '00') {
        return `${dollars} ${dollars === '1' ? 'dólar' : 'dólares'}`;
    } else {
        return `${dollars} ${dollars === '1' ? 'dólar' : 'dólares'} con ${cents} centavos`;
    }
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Start the system clock
 */
function startSystemClock() {
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
            timeElement.setAttribute('datetime', now.toISOString());
        }
    }
    
    updateTime();
    setInterval(updateTime, 1000);
}

/**
 * Activate fullscreen mode for POS
 */
function activateFullscreenMode() {
    document.body.classList.add('wupos-fullscreen-mode');
}

/**
 * Show interface after loading delay
 */
function showInterface() {
    setTimeout(function() {
        const loading = document.getElementById('wupos-loading');
        const interface = document.getElementById('wupos-pos-interface');
        
        if (loading) loading.style.display = 'none';
        if (interface) interface.style.display = 'grid';
        
        announceToScreenReader('Interfaz POS completamente cargada');
    }, 1500);
}

/**
 * Announce messages to screen readers
 */
function announceToScreenReader(message, isError = false) {
    const announceElement = document.getElementById(
        isError ? 'wupos-error-announcements' : 'wupos-status-announcements'
    );
    
    if (announceElement) {
        announceElement.textContent = message;
        // Clear after 2 seconds to avoid repetition
        setTimeout(() => {
            announceElement.textContent = '';
        }, 2000);
    }
}

// Cleanup when leaving the page
window.addEventListener('beforeunload', function() {
    document.body.classList.remove('wupos-fullscreen-mode');
});

// Export functions for debugging (remove in production)
window.WUPOS_DEBUG = {
    addToCart,
    removeFromCart,
    clearCurrentCart,
    updateItemQuantity,
    updateItemPrice,
    createNewCart,
    switchToCart,
    calculateCartTotals,
    updateCartUI,
    updateProductCardState,
    updateAllProductCardStates,
    processCartCheckout,
    announceToScreenReader,
    cartsData,
    currentCartId,
    WUPOS_CART_CONFIG
};
</script>