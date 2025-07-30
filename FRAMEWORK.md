# WUPOS - AI Development Framework & Project Documentation

**Framework Version:** v3.2.0 Enhanced with Checkpoint Protocols & Priority Hierarchy  
**Project:** WUPOS - WordPress POS System  
**Last Updated:** 2025-07-30  
**Status:** Production Ready with Ultra-Flat Structure and Iterative Quality Loop

---

## 🎯 Framework Overview

This is an ultra-efficient AI development framework optimized for speed, quality, and real-world results. The framework has been enhanced with:

- **Smart contextual questioning** (60% time reduction in PRD creation)
- **Auto-completion with Context7** research integration
- **Realistic automatic estimates** based on current market rates
- **Iterative quality loop** until 8/10+ achieved across all dimensions
- **MCP integration** for Playwright visual analysis and Context7 research
- **Ultra-flat repository structure** for simplified maintenance and contribution
- **Streamlined architecture** with removed complex GitHub workflows
- **Agent task approval system** - Tasks require user approval before completion
- **Task summary requirement** - All tasks must start with clear objective summary

---

## 🚀 Quick Start Commands

### Core Workflow Commands
```bash
# 1. Create PRD (Enhanced with smart questioning)
./ai-dev create

# 2. Generate Tasks (Enhanced with Context7 validation)
./ai-dev generate 01_prd_{session-id}_{project-name}.md

# 3. Execute Implementation (Enhanced with iterative quality)
./ai-dev execute 02_tasks_{session-id}_{project-name}.md

# 4. Run Iterative Quality Loop (NEW - replaces single healing)
./ai-dev iterate quality {session-id}

# 5. Force healing analysis (legacy support)
./ai-dev heal {session-id}
```

### Management Commands
```bash
# List all sessions
./ai-dev list

# Show session details
./ai-dev status {session-id}

# Clean up old sessions
./ai-dev cleanup
```

---

## 📊 WUPOS Project Status (Current Implementation)

### ✅ **COMPLETADO - Sistema POS Completo**
- **Plugin WordPress estándar** con página dedicada full-width
- **API personalizada** /wupos/v1/ con autenticación cookies WordPress
- **Template dedicado** en /pos/ sin interferencias theme
- **CSS escalable** 1366px-1920px con grid dinámico (5-6 columnas)
- **Estados de carga** mejorados (skeleton, overlay, animaciones)
- **Sistema de permisos** WooCommerce funcionando correctamente

### ✅ **ARQUITECTURA IMPLEMENTADA**

#### **Acceso al POS**
1. **URL directa**: `http://localhost:10003/pos/` (página dedicada)
2. **Menú Admin**: WordPress Admin → **POS** (ícono tienda)
3. **Shortcode**: `[wupos_pos_app]` (páginas/posts)

#### **API Endpoints Personalizados**
- `GET /wp-json/wupos/v1/products` - Productos con autenticación WordPress
- `GET /wp-json/wupos/v1/categories` - Categorías productos  
- `POST /wp-json/wupos/v1/orders` - Crear órdenes WooCommerce
- `GET /wp-json/wupos/v1/payment-gateways` - Métodos de pago
- `GET /wp-json/wupos/v1/settings` - Configuración del plugin
- `GET /wp-json/wupos/v1/tax-settings` - Configuración de impuestos WC

### 📁 **Estructura de Archivos Actual**
```
wupos/
├── wupos.php (147 líneas) - Plugin principal con rewrite rules
├── templates/
│   └── pos-page.php (169 líneas) - Template dedicado full-width
├── assets/
│   ├── css/wupos-pos.css (30k+ líneas) - Estilos + loading states
│   └── js/wupos-pos.js (38k+ líneas) - JavaScript + animaciones
├── includes/
│   ├── class-wupos-pos-page.php - Controlador página /pos/
│   ├── class-wupos-api.php - API endpoints personalizados
│   ├── class-wupos-products-controller.php - Gestión productos
│   ├── class-wupos-customers-controller.php - Gestión clientes
│   ├── class-wupos-orders-controller.php - Gestión órdenes
│   ├── class-wupos-payment-gateways-controller.php - Métodos pago
│   ├── class-wupos-settings-controller.php - Configuración
│   ├── class-wupos-auth-controller.php - Autenticación
│   ├── class-wupos-coupons-controller.php - Cupones
│   ├── api-loader.php - Cargador APIs
│   └── class-wupos-admin-settings.php - Configuración admin
└── tests/
    ├── bootstrap.php - Configuración testing
    ├── test-api-endpoints.php - Tests API
    └── test-auth-controller.php - Tests autenticación
```

### 🔧 **Tecnologías y Estándares**

#### **Framework UI**: Bootstrap 5 CDN + CSS personalizado
#### **JavaScript**: Vanilla JS + jQuery (WordPress estándar)  
#### **APIs**: Endpoints WUPOS personalizados + WordPress REST API
#### **Autenticación**: WordPress cookies + nonces (funcionando)
#### **Layout**: Fixed 3-column escalable (Sidebar 80-100px + Products flex + Cart 280-320px)

### 📐 **Diseño Escalable PC**

#### **1366x768 (5 columnas)**
```
┌─────────────────────────────────────────────────────────────────────┐
│ WUPOS Header (65px) - PANTALLA COMPLETA sin admin bar              │
├─────┬───────────────────────────────────────────────────┬─────────┤
│Side │            Products Grid (5 cols)                  │  Cart   │
│80px │ ┌────┬────┬────┬────┬────┐ 160px height cards     │ 280px   │
│     │ ~1066px productos disponibles                      │ Full    │
└─────┴─────────────────────────────────────────────────────┴─────────┘
```

#### **1920x1080 Full HD (6 columnas)**
```
┌─────────────────────────────────────────────────────────────────────┐
│ WUPOS Header (65px) - PANTALLA COMPLETA                            │
├─────┬───────────────────────────────────────────────────┬─────────┤
│Side │            Products Grid (6 cols)                  │  Cart   │
│100px│ ┌───┬───┬───┬───┬───┬───┐ 180px height cards      │ 320px   │
│     │ ~1500px productos disponibles                      │ Full    │
└─────┴─────────────────────────────────────────────────────┴─────────┘
```

### 🔗 **URLs y Accesos**
- **POS Interface**: http://localhost:10003/pos/ (página dedicada full-width)
- **Plugin Path**: /home/ainu/Proyectos/WUPOS/wupos/
- **Git Status**: main branch, múltiples archivos modificados pendientes commit

### 🚀 **Estado Funcional Actual**
- ✅ **Plugin activo** y funcionando
- ✅ **Página /pos/** accesible para usuarios con permisos WooCommerce
- ✅ **API endpoints** implementados con autenticación WordPress
- ✅ **CSS responsive** optimizado para PC (1366px-1920px)
- ✅ **Carrito funcional** con persistencia localStorage
- ✅ **Checkout básico** creando órdenes WooCommerce
- ⏳ **Impuestos** calculados pero no visibles en UI
- ⏳ **Testing** pendiente en entorno real

---

## 🔄 Framework Rules & Standards

### **7. AGENT TASK MANAGEMENT RULES** (Enhanced Protocol v3.2)

#### **7.1. Task Initiation Requirements**
- **7.1.1.** Clear objective summary must precede any task execution
- **7.1.2.** Appropriate specialized agent assignment mandatory based on agent characteristics
- **7.1.3.** User acknowledgment required before starting
- **7.1.4.** Security impact assessment required for all code changes
- **7.1.5.** Emergency escalation path defined for critical issues

#### **7.2. Task Completion Protocol**
- **7.2.1.** No task completion without explicit user approval
- **7.2.2.** Progress updates and status reports required
- **7.2.3.** Quality validation before marking complete
- **7.2.4.** Git commit required upon task completion with all subtasks - **MUST be executed by the most appropriate specialized agent for the task type**
- **7.2.5.** Security validation mandatory for backend changes
- **7.2.6.** Performance impact assessment for critical components
- **7.2.7.** Git push strategy following established methodology (see section 7.3)

#### **7.3. Git Push Methodology**
**Estrategia: Push por Hitos Importantes + Push de Seguridad**

**7.3.1. Push Obligatorio por Hitos:**
- UI completa implementada ✅
- Backend básico funcional ✅
- Funcionalidades core completadas ✅
- Sistema completamente funcional ✅
- Correcciones críticas de bugs ✅

**7.3.2. Push de Seguridad:**
- Después de 4+ horas de trabajo sin push
- Antes de cambios arquitecturales importantes
- Al final de sesiones productivas largas

**7.3.3. Commits vs Push:**
- **Commits**: Frecuentes, por cada funcionalidad pequeña
- **Push**: Solo en hitos importantes o seguridad
- **Ratio recomendado**: 3-5 commits por cada push

**7.3.4. Mensajes de Push:**
- Incluir resumen de hitos completados
- Mencionar funcionalidades principales agregadas
- Indicar si es push de hito o seguridad

**7.3.5. Cuándo NO hacer push:**
- Código que no compila o tiene errores críticos
- Funcionalidades a medio implementar
- Commits de trabajo en progreso (WIP)

#### **7.4. Agent Assignment Criteria**
- **7.4.1.** Match task requirements to agent specializations
- **7.4.2.** Consider agent tools and capabilities
- **7.4.3.** Prioritize agent expertise over convenience
- **7.4.4.** Validate assignment appropriateness before execution
- **7.4.5.** Multi-agent coordination required for complex tasks

#### **7.4B. Git Commit Agent Assignment Protocol** (NEW)
- **7.4B.1.** **Frontend/UI changes**: `pos-ux-designer` handles commits for interface modifications
- **7.4B.2.** **Backend/WordPress code**: `wordpress-backend-developer` handles commits for PHP, APIs, security
- **7.4B.3.** **Infrastructure/deployment**: `wordpress-devops-engineer` handles commits for deployment, configuration, performance
- **7.4B.4.** **Documentation updates**: `wordpress-plugin-docs-writer` handles commits for docs, readme, changelogs
- **7.4B.5.** **Testing/QA**: `wordpress-qa-specialist` handles commits for test files and validation scripts
- **7.4B.6.** **Multi-component changes**: `wupos-product-manager` coordinates and handles commits affecting multiple areas
- **7.4B.7.** **General tasks**: `general-purpose` handles commits for file operations, configurations, minor updates

#### **7.5. Checkpoint Protocol Implementation** (NEW)
- **7.5.1.** **Before Major Changes**: Always execute checkpoint creation
  - Verify current working state and functionality
  - Create git commit/tag as restoration point
  - Document current configuration and dependencies
  - Validate all prerequisites are met
  
- **7.5.2.** **During Implementation**: Incremental checkpoints
  - Commit working increments frequently
  - Test functionality after each significant change
  - Document any deviations from original plan
  
- **7.5.3.** **After Implementation**: Validation checkpoint
  - Run full functionality tests
  - Verify wireframe compliance (Rule #3)
  - Update KNOWN_ISSUES.md with any new discoveries
  - Create final checkpoint commit

#### **7.6. Emergency Response Protocol** (NEW)
- **7.6.1.** Critical security vulnerabilities require immediate escalation
- **7.6.2.** System-breaking issues bypass normal approval process
- **7.6.3.** Emergency fixes require post-implementation validation
- **7.6.4.** Crisis-level issues (40+ problems) require coordinated response
- **7.6.5.** Emergency documentation updates mandatory within 24h

#### **7.7. Multi-Agent Collaboration Guidelines** (NEW)
- **7.7.1.** Primary agent owns task coordination and final deliverable
- **7.7.2.** Supporting agents provide specialized expertise input
- **7.7.3.** All agents must align with WordPress standards and FRAMEWORK.md rules
- **7.7.4.** Conflict resolution through primary agent or product manager escalation
- **7.7.5.** Knowledge transfer required between agent handoffs

### **Critical Rules (Ordered by Priority - Higher numbers = Higher priority)**

**🚨 LEVEL 5: CRITICAL (Framework Integrity)**
1. **WordPress Standards MANDATORY** - ALL code MUST follow WordPress Coding Standards and guidelines from https://developer.wordpress.org/
2. **Error memory system** - Always check KNOWN_ISSUES.md first and document new issues immediately
3. **Wireframe supremacy** - NEVER modify established wireframe designs when implementing new features. All new implementations MUST conform to existing wireframe specifications and design patterns established in project documentation.

**⚡ LEVEL 4: HIGH PRIORITY (Quality & Security)**
4. **Quality threshold enforcement** - Never bypass 8/10 requirement
5. **Explain before implementing** - Always provide short, concise explanation before proposing code changes
6. **DoD validation required** - NEVER mark any task complete without executing full Definition of Done validation protocol
7. **ALL documentation in English** - Never use other languages in files

**🔄 LEVEL 3: OPERATIONAL (Workflow & Process)**
8. **Checkpoint protocols** - Before major changes: verify current state, create restoration point, validate prerequisites
9. **Session-based workflow** - Always maintain session traceability
10. **Git workflow integration** - Follow conventional commits with session-id traceability
11. **Execution tracking mandatory** - Create and maintain execution reports with real-time task completion updates

**🛠️ LEVEL 2: ENHANCEMENT (Tools & Integration)**
12. **Context7 integration** - Use real-time research for all decisions
13. **MCP Playwright validation** - Visual analysis is mandatory
14. **Proactive commit reminders** - ALWAYS remind user to commit after completing any file generation or major milestone
15. **Ultra-flat structure** - Maintain simplified repository architecture

**📋 LEVEL 1: SUPPORT (Methodology)**
16. **No complex automation** - Focus on core framework functionality
17. **Try before escalating** - NEVER create impediment report without attempting at least 2 different solution approaches first

### **WUPOS Project Rules**

1. **WordPress Standards MANDATORY** - Follow ALL WordPress Coding Standards, Plugin Guidelines, and Best Practices from https://developer.wordpress.org/
2. **WooCommerce Standards MANDATORY** - Follow WooCommerce development guidelines and APIs
3. **Cero código basura** - Solo código limpio y necesario
4. **Bootstrap 5 únicamente** - No DaisyUI, no React/Node.js
5. **WordPress APIs ONLY** - Use WordPress native functions, hooks, and APIs
6. **Plugin Security Standards** - Follow WordPress security guidelines (nonces, sanitization, validation)
7. **Commits con cada cambio importante** - Documentación completa
8. **TodoWrite para tracking** - Usar para todas las tareas complejas
9. **Agent specialization mandatory** - Each task to appropriate agent
10. **User approval required** - No task completion without permission

### **Assistant Behavior Rules**

#### **Before Any Code Changes**
1. **Verify WordPress Standards** - Check that implementation follows WordPress Coding Standards
2. **Always explain first** - Provide short, concise explanation of what the change does
3. **State the purpose** - Why this change is needed or beneficial
4. **Describe the impact** - What will be different after the change
5. **WordPress API compliance** - Ensure only WordPress/WooCommerce native functions are used
6. **Then implement** - Only proceed with code after explanation and standards verification

### **Output Standards**
- **File naming:** Follow exact pattern `{step}_{session-id}_{project-name}.md`
- **Language:** English only in all documentation and code
- **Quality:** Professional standards with certification
- **Traceability:** Perfect session linkage across all files

---

## 📊 Quality Standards

### Mandatory Quality Thresholds (ALL must be >= 8/10)

- **Security Compliance:** 30% weight - WordPress security standards, vulnerability assessment
- **Visual Consistency:** 15% weight - Design system adherence
- **CRO Optimization:** 20% weight - Conversion rate optimization  
- **Accessibility:** 15% weight - WCAG 2.1 compliance
- **Architecture Quality:** 10% weight - Code structure quality
- **Performance:** 10% weight - Load times and optimization

### Quality Enforcement

- **Automatic blocking** if any dimension < 8/10
- **Iterative improvement** until threshold met
- **Maximum 5 iterations** before manual review required
- **Quality certification** seal for approved projects

---

## 🎯 Best Practices for Claude

### Before Starting Any Project
1. **Always check** for existing sessions first
2. **Use smart questioning** - let the framework detect project type
3. **Trust auto-completion** - it's based on current market research
4. **Don't skip quality loop** - it ensures professional standards

### During Task Execution
1. **Provide task summary** before starting any work
2. **Assign to appropriate agent** based on specialization
3. **Get user approval** before marking complete
4. **Commit changes** upon completion
5. **Update TASKS.md** with progress

### Before Any Code Changes
1. **Always explain first** - Provide short, concise explanation of what the change does
2. **State the purpose** - Why this change is needed or beneficial
3. **Describe the impact** - What will be different after the change
4. **Then implement** - Only proceed with code after explanation is given

### Agent Specializations Available
- **general-purpose**: Research, file operations, general tasks
- **wordpress-backend-developer**: WordPress/WooCommerce backend development, security implementations
- **wupos-product-manager**: Multi-agent coordination, project management, strategic decisions
- **pos-ux-designer**: POS interface design, user experience, accessibility compliance
- **wordpress-qa-specialist**: Quality assurance, testing, validation protocols
- **wordpress-devops-engineer**: Deployment, infrastructure, performance optimization
- **wordpress-plugin-docs-writer**: Documentation, WordPress compliance, technical writing

### Multi-Agent Coordination Protocols
- **Task Assignment**: Product manager coordinates complex multi-agent tasks
- **Knowledge Sharing**: Agents must document decisions and handoffs
- **Quality Gates**: Each agent validates work within their expertise domain
- **Conflict Resolution**: Product manager mediates technical disagreements
- **Security Reviews**: All code changes require security agent validation

---

## 🚨 Error Handling & Troubleshooting

### Error Memory System
- **Always check KNOWN_ISSUES.md first** before starting work
- **Document new issues immediately** with proper format
- **Reference issue IDs** in task descriptions
- **Update issue status** when resolved

### Current Known Issues
- See `/home/ainu/Proyectos/WUPOS/KNOWN_ISSUES.md` for complete list
- **27 total issues** documented (1 resolved, 26 active)
- **High priority issues** require immediate attention

---

## 📋 Task Management

### Task Files
- **TASKS.md**: Complete task list with assignments and priorities
- **TodoWrite tool**: Real-time task tracking during sessions
- **KNOWN_ISSUES.md**: Issue tracking and resolution status

### Current High Priority Tasks
1. **Arreglar estado por defecto de órdenes** - Backend config not applied
2. **Implementar visualización de impuestos** - UI missing for calculated taxes
3. **Crear modal carritos retenidos** - Hold function works but no UI management
4. **Arreglar órdenes sin monto** - Orders created with $0.00 total
5. **Panel de Control de Vista** - Dynamic view controls per specifications

---

## 🔮 Future Improvements

### Planned Framework Features
- **Automated issue detection** during framework execution
- **Real-time validation** of promised features
- **Cross-platform testing** protocols
- **User feedback integration** system

### WUPOS Development Roadmap
- **Complete checkout flow** with full functionality
- **Advanced POS features** (receipts, reports, etc.)
- **Mobile optimization** for tablet POS usage
- **Multi-location support** for chain stores

---

**Remember:** Every task requires clear summary, appropriate agent assignment, user approval, and commit upon completion. This framework ensures professional quality and systematic progress tracking.

*Framework consolidated from multiple sources - Maintain as single source of truth*