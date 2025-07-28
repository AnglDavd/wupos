# Panel de Control de Vista - Sistema POS WUPOS

## Descripción General

El Panel de Control de Vista es un componente integral diseñado para optimizar la experiencia de usuario en el sistema POS WUPOS, proporcionando controles dinámicos para la visualización y organización de productos en el punto de venta.

## Especificaciones Técnicas

### Ubicación e Integración
- **Posición**: Ubicado entre la barra de búsqueda y el grid de productos
- **Integración**: Se integra armoniosamente con el header existente del sistema
- **Z-index**: 20 para asegurar visibilidad sin interferir con elementos críticos
- **Responsivo**: Se adapta a diferentes tamaños de pantalla manteniendo funcionalidad

### Arquitectura del Componente

#### 1. Controles de Grid Dinámico
```
┌─────────────────────────────────────┐
│ VISTA                               │
│ [2×2] [3×3] [4×4] [6×6] [≡ Lista]  │
└─────────────────────────────────────┘
```

**Opciones de Layout:**
- **2 Columnas**: Grid 2×2 para productos grandes, ideal para tablets
- **3 Columnas**: Grid 3×3 balanceado para pantallas medianas
- **4 Columnas**: Grid 4×4 configuración por defecto, óptima para desktop
- **6 Columnas**: Grid 6×6 para pantallas grandes y alta densidad
- **Vista Lista**: Layout lineal con información extendida

**Estados Visuales:**
- Normal: Borde gris claro (#e9ecef)
- Hover: Borde azul primario con sombra sutil
- Activo: Fondo azul primario, texto blanco, sombra pronunciada

#### 2. Controles de Ordenamiento
```
┌─────────────────────────────────────┐
│ ORDENAR                             │
│ [Nombre ▼] [↑]                     │
└─────────────────────────────────────┘
```

**Opciones de Ordenamiento:**
- **Nombre**: Alfabético A-Z / Z-A
- **Precio**: Menor a mayor / Mayor a menor
- **Stock**: Mayor a menor / Menor a mayor
- **Popularidad**: Más vendidos primero
- **Fecha**: Productos nuevos primero

**Indicadores de Dirección:**
- ↑ Ascendente (A-Z, menor a mayor)
- ↓ Descendente (Z-A, mayor a menor)

#### 3. Controles Adicionales
```
┌─────────────────────────────────────┐
│ [○ Ocultar sin stock]               │
│ [⭕○⚪] [−][100%][+]                │
└─────────────────────────────────────┘
```

**Toggle Ocultar Sin Stock:**
- Switch animado con estados activo/inactivo
- Color naranja (#d97706) cuando está activo
- Transición suave de 0.2s

**Vista Compacta/Normal/Detallada:**
- Compacta: Información mínima, más productos visibles
- Normal: Balance entre información y densidad
- Detallada: Máxima información por producto

**Control de Zoom:**
- Niveles: 75%, 90%, 100%, 110%, 125%
- Botones con estados disabled en extremos
- Indicador central con porcentaje actual

## Especificaciones de Diseño

### Paleta de Colores (Cumple WCAG 2.1 AA)
- **Primario**: #2563eb (Azul profesional, contraste 4.5:1)
- **Primario Hover**: #3b82f6 (Azul hover)
- **Primario Light**: #dbeafe (Azul claro para fondos)
- **Gris Neutral**: #6c757d (Texto secundario)
- **Fondo Controles**: #ffffff (Blanco)
- **Borde**: #e9ecef (Gris claro)
- **Warning**: #d97706 (Naranja para stock toggle)

### Tipografía
- **Etiquetas**: 0.75rem, peso 600, uppercase, espaciado 0.5px
- **Contenido**: 0.8125rem, peso 500
- **Indicadores**: 0.75rem, peso 600, centrado

### Espaciado y Dimensiones
- **Touch Targets**: Mínimo 44px x 44px (cumple accesibilidad)
- **Gap entre controles**: 0.75rem
- **Padding interno**: 0.375rem - 1rem según elemento
- **Border Radius**: 6px - 8px para modernidad
- **Box Shadow**: 0 2px 4px rgba(0,0,0,0.05) para profundidad

## Wireframes

### Estado por Defecto (Desktop)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│ ┌─── VISTA ───┐ ┌── ORDENAR ──┐ ┌─── ADICIONALES ────────────────────┐      │
│ │Vista        │ │Ordenar      │ │[○ Ocultar sin stock]               │      │
│ │[2][3][4][6]│ │[Nombre▼][↑]│ │[⭕○⚪] [−][100%][+]               │      │
│ │    [≡]     │ │             │ │                                    │      │
│ └────────────┘ └─────────────┘ └────────────────────────────────────┘      │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Estado Responsive (Tablet)
```
┌─────────────────────────────────────────────────────────┐
│ ┌─── VISTA ───┐ ┌── ORDENAR ──┐                        │
│ │[2][3][4][6]│ │[Nombre▼][↑]│                        │
│ │    [≡]     │ │             │                        │
│ └────────────┘ └─────────────┘                        │
│ ┌─── ADICIONALES ──────────────────────────────────────┐ │
│ │[○ Ocultar sin stock] [⭕○⚪]                       │ │
│ └──────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### Estado Mobile
```
┌─────────────────────────────────────┐
│ ┌─── VISTA ─────────────────────────┐ │
│ │     [2][3][4] [≡]              │ │
│ └───────────────────────────────────┘ │
│ ┌─── ORDENAR ───────────────────────┐ │
│ │   [Nombre ▼] [↑]               │ │
│ └───────────────────────────────────┘ │
│ ┌─── ADICIONALES ───────────────────┐ │
│ │ [○ Ocultar sin stock]           │ │
│ └───────────────────────────────────┘ │
└─────────────────────────────────────┘
```

## Estados de Interacción

### Estados de Botones
1. **Normal**: 
   - Fondo: blanco
   - Borde: #e9ecef
   - Color texto: #6c757d

2. **Hover**:
   - Borde: #2563eb
   - Color texto: #2563eb
   - Sombra: 0 2px 6px rgba(37, 99, 235, 0.15)
   - Transform: translateY(-1px)

3. **Activo**:
   - Fondo: #2563eb
   - Borde: #2563eb
   - Color texto: blanco
   - Sombra: 0 2px 8px rgba(37, 99, 235, 0.3)

4. **Disabled**:
   - Opacidad: 0.5
   - Cursor: not-allowed

### Transiciones
- Duración: 0.2s ease para cambios de estado
- Duración: 0.3s ease para cambios de layout
- Sin transiciones en modo `prefers-reduced-motion`

## Implementación Técnica

### Clases CSS Principales
```css
.wupos-view-controls         /* Contenedor principal */
.grid-layout-controls        /* Grupo de controles de grid */
.sort-controls              /* Grupo de controles de ordenamiento */
.additional-controls        /* Controles adicionales */
.grid-layout-btn            /* Botones de layout */
.sort-select               /* Selector de ordenamiento */
.stock-filter-toggle       /* Toggle de stock */
.view-toggle               /* Controles de vista */
.zoom-control              /* Controles de zoom */
```

### JavaScript API
```javascript
// Métodos principales
WUPOS.changeGridLayout(layout)    // Cambiar layout de grid
WUPOS.changeSortOrder(by, dir)    // Cambiar ordenamiento
WUPOS.changeViewType(type)        // Cambiar tipo de vista
WUPOS.toggleStockFilter()         // Toggle filtro stock
WUPOS.adjustZoom(direction)       // Ajustar zoom

// Persistencia
WUPOS.saveViewPreferences()       // Guardar preferencias
WUPOS.loadViewPreferences()       // Cargar preferencias
```

### Almacenamiento Local
```javascript
// Estructura de datos guardada en localStorage
{
  gridLayout: 'grid-4-cols',      // Layout actual
  viewType: 'view-normal',        // Tipo de vista
  sortBy: 'name',                 // Campo de ordenamiento
  sortDirection: 'asc',           // Dirección
  hideOutOfStock: false,          // Filtro stock
  zoomLevel: 100                  // Nivel de zoom
}
```

## Beneficios de UX

1. **Eficiencia Operacional**:
   - Acceso rápido a diferentes vistas sin navegación compleja
   - Ordenamiento instantáneo para encontrar productos rápidamente
   - Filtros inteligentes para gestión de inventario

2. **Personalización**:
   - Preferencias guardadas por usuario/sesión
   - Adaptación a diferentes tipos de monitor y resoluciones
   - Flexibilidad según el flujo de trabajo

3. **Accesibilidad**:
   - Cumple WCAG 2.1 AA con contraste 4.5:1+
   - Soporte completo de teclado
   - Estados visuales claros para todos los controles
   - Touch targets de 44px+ para dispositivos táctiles

4. **Performance**:
   - Cambios de vista instantáneos mediante CSS
   - Filtrado eficiente sin recarga de datos
   - Transiciones suaves optimizadas

## Consideraciones de Implementación

### Prioridades de Desarrollo
1. **Alta**: Controles de grid y ordenamiento básico
2. **Media**: Toggle de stock y zoom
3. **Baja**: Vista detallada y animaciones avanzadas

### Compatibilidad
- Navegadores: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- Dispositivos: Desktop, tablet, móvil
- Resoluciones: 320px - 4K+

### Testing
- Pruebas de usabilidad con cajeros reales
- Testing de accesibilidad con screen readers
- Pruebas de performance en dispositivos de gama baja
- Validación en diferentes tamaños de inventario

## Métricas de Éxito

1. **Adopción**: >80% de usuarios utilizan al menos un control
2. **Eficiencia**: Reducción 30% en tiempo de búsqueda de productos
3. **Satisfacción**: Puntuación >4.5/5 en encuestas de usabilidad
4. **Accesibilidad**: 100% cumplimiento WCAG 2.1 AA

---

*Este documento define las especificaciones completas para el Panel de Control de Vista del sistema POS WUPOS, diseñado para optimizar la experiencia de usuario manteniendo los más altos estándares de accesibilidad y usabilidad.*