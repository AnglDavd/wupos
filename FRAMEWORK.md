# WUPOS - AI Development Framework & Project Documentation

**Framework Version:** v3.5.0 Enhanced with Sequential Agent Execution & Session Context Management  
**Project:** WUPOS - WordPress POS System  
**Last Updated:** 2025-08-01  
**Status:** Production Ready with Advanced Cart UX Optimization, Timing Fixes & Component Deconfliction

---

## 🎯 Framework Overview

This is an ultra-efficient AI development framework optimized for speed, quality, and real-world results. The framework has been enhanced with:

- **Quality First Mandate** - Implicit quality-first behavior activated when @FRAMEWORK.md is referenced
- **Auto-Assignment Protocol** - Intelligent agent selection based on keyword detection matrix
- **Product Manager Dispatcher** - Automatic coordination for complex multi-agent tasks
- **Smart contextual questioning** (60% time reduction in PRD creation)
- **Auto-completion with Context7** research integration
- **Realistic automatic estimates** based on current market rates
- **Iterative quality loop** until 8/10+ achieved across all dimensions
- **MCP integration** for Playwright visual analysis and Context7 research
- **Ultra-flat repository structure** for simplified maintenance and contribution
- **Streamlined architecture** with removed complex GitHub workflows
- **Agent task approval system** - Tasks require user approval before completion
- **Task summary requirement** - All tasks must start with clear objective summary
- **Sequential agent execution protocol** - Enhanced agent coordination with structured handoffs
- **Session context management** - Persistent context tracking with .context/ folder system
- **Agent self-evaluation protocols** - Post-task analysis and quality validation system
- **Cart UX optimization system** - Advanced timing fixes and component conflict resolution
- **WooCommerce integration enhancement** - Resolved fatal errors and timing conflicts
- **Tax display consistency** - Forced minimalista design with component deconfliction

---

## 🎯 Quality First Mandate (AUTO-ACTIVATED)

**When @FRAMEWORK.md is referenced in any request, the following principles are automatically activated:**

### 🎯 Quality Framework

**8/10+ Quality Definition:** Professional-grade implementation meeting WordPress standards with proper security, performance optimization, and excellent user experience.

**Quality Metrics (Pass/Fail Gates):**
- **Security Compliance**: WordPress security standards, vulnerability assessment
- **Performance**: Load times and optimization for critical components
- **User Experience**: Interface usability and accessibility compliance

**Quality Gates:**
- **Pre-implementation**: Requirements clarity validation
- **During implementation**: Standards compliance checks
- **Post-implementation**: Quality threshold validation (8/10+ requirement)
- **Security review**: Mandatory for all code changes

### 🤖 Agent Directory

**Auto-Assignment Protocol:** Framework automatically selects agents based on keyword detection.

**Available Agents:**
- **pos-ux-designer** - Interface design, user experience, accessibility
  - *Keywords*: "interface", "design", "layout", "styling", "responsive", "accessibility", "wireframe", "mockup"
- **wordpress-backend-developer** - WordPress/WooCommerce backend, security, APIs
  - *Keywords*: "API", "endpoint", "database", "security", "authentication", "PHP", "WordPress", "WooCommerce"
- **wordpress-qa-specialist** - Testing, validation, quality assurance
  - *Keywords*: "test", "testing", "validation", "QA", "quality assurance", "bug", "debug"
- **wordpress-devops-engineer** - Deployment, performance, infrastructure
  - *Keywords*: "deployment", "performance", "optimization", "server", "configuration", "monitoring"
- **wordpress-plugin-docs-writer** - Documentation, compliance, technical writing
  - *Keywords*: "documentation", "readme", "guide", "instructions", "changelog", "compliance"
- **wupos-product-manager** - Multi-agent coordination, strategic decisions
  - *Keywords*: "coordinate", "manage", "multiple", "complex", "integration", "roadmap", "strategy"
  - *Auto-dispatch*: 3+ agent categories, complex features, coordination needs
- **general-purpose** - Research, file operations, general tasks
  - *Keywords*: "research", "search", "analyze", "investigate", "file operations"

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

### ✅ **COMPLETADO - Sistema POS Completo con Cart UX Optimizado**
- **Plugin WordPress estándar** con página dedicada full-width
- **API personalizada** /wupos/v1/ con autenticación cookies WordPress
- **Template dedicado** en /pos/ sin interferencias theme
- **CSS escalable** 1366px-1920px con grid dinámico (5-6 columnas)
- **Estados de carga** mejorados (skeleton, overlay, animaciones)
- **Sistema de permisos** WooCommerce funcionando correctamente
- **Cart UX optimizado** con timing fixes y display consistency
- **Tax display minimalista** forzado en todos los estados del carrito

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
- ✅ **Plugin activo** y funcionando completamente
- ✅ **Página /pos/** accesible para usuarios con permisos WooCommerce
- ✅ **API endpoints** implementados con autenticación WordPress robusta
- ✅ **CSS responsive** optimizado para PC (1366px-1920px) con escalabilidad
- ✅ **Carrito funcional** con persistencia localStorage optimizada y timing fixes
- ✅ **Checkout básico** creando órdenes WooCommerce con validación completa
- ✅ **Sistema de impuestos** con cálculo dinámico y display minimalista consistente
- ✅ **Cart UX optimization** completamente implementado (acceso post wp_loaded)
- ✅ **Tax display architecture** optimizada con forced fallback design y conflict resolution
- ✅ **WooCommerce timing fixes** resolviendo fatal errors y comportamiento inconsistente
- ✅ **Component deconfliction** eliminando redundancias y conflictos visuales
- ✅ **Stock management backend** con dynamic thresholds y three-tier classification
- ⏳ **Testing** pendiente en entorno real para validación final

---

## 🔄 Framework Rules & Standards

### **7. AGENT TASK MANAGEMENT RULES** (Enhanced Protocol v3.3)

#### **7.1. Task Initiation Requirements** (Quality First Enhanced)
- **7.1.1.** Clear objective summary must precede any task execution
- **7.1.2.** Auto-assignment protocol executes keyword-based agent selection
- **7.1.3.** Quality First Mandate automatically activated when @FRAMEWORK.md referenced
- **7.1.4.** User acknowledgment required before starting
- **7.1.5.** Security impact assessment required for all code changes
- **7.1.6.** Emergency escalation path defined for critical issues
- **7.1.7.** Product manager auto-dispatch for multi-agent coordination needs

#### **7.2. Task Completion Protocol** (Quality First Enhanced)
- **7.2.1.** No task completion without explicit user approval
- **7.2.2.** Progress updates and status reports required with quality metrics
- **7.2.3.** Quality validation before marking complete (8/10+ threshold mandatory)
- **7.2.4.** Best practices compliance verification (WordPress/WooCommerce standards)
- **7.2.5.** Git commit required upon task completion with all subtasks - **Each agent commits their own work with concise messages (token-efficient)**
- **7.2.6.** Security validation mandatory for backend changes
- **7.2.7.** Performance impact assessment for critical components
- **7.2.8.** User experience impact evaluation
- **7.2.9.** Documentation completeness verification
- **7.2.10.** Git push strategy following established methodology (see section 7.3)

### 🗺️ Git Workflow

**Commit Strategy:**
- Frequent commits for each small functionality
- Each agent commits their own work with concise messages
- Git commit required upon task completion with all subtasks

**Push Strategy:**
- Push for important milestones only
- Push after 4+ hours of work without push (security)
- Before major architectural changes
- 3-5 commits per push ratio recommended

**Push Requirements:**
- Complete milestone implementation
- No compilation errors or critical bugs
- No work-in-progress (WIP) commits
- Include summary of completed milestones

**Individual Agent Responsibility:**
- Frontend/UI changes: `pos-ux-designer` commits
- Backend/WordPress code: `wordpress-backend-developer` commits
- Infrastructure/deployment: `wordpress-devops-engineer` commits
- Documentation: `wordpress-plugin-docs-writer` commits
- Testing/QA: `wordpress-qa-specialist` commits
- Multi-component: `wupos-product-manager` coordinates
- General tasks: `general-purpose` commits

#### **7.4. Agent Assignment Criteria** (Auto-Assignment Enhanced)
- **7.4.1.** **Automatic Assignment** - Keyword detection matrix assigns agents automatically
- **7.4.2.** **Product Manager Override** - PM can reassign if automatic selection inappropriate
- **7.4.3.** **Execution Sequence** - PM determines order when multiple agents assigned
- **7.4.4.** Prioritize agent expertise over convenience
- **7.4.5.** Consider agent tools and capabilities in selection algorithm
- **7.4.6.** Multi-agent coordination automatically triggered for complex tasks
- **7.4.7.** Product manager dispatcher activates for 3+ agent category overlap
- **7.4.8.** **Sequential Specialization Model** - Product Manager coordinates specialized agents working in sequence
- **7.4.9.** **Sequential Communication Protocol** - Agents communicate through structured handoff format (Section 7.9)


### 📋 Session Management

**Session Context Protocol:**
- TodoWrite tool maintains real-time context during active session
- Last working agent creates handoff file at session end
- Store in `.context/session-{id}_handoff.md` (maximum 300 words)
- Auto-load previous session context when starting new session

**Agent Collaboration:**
- Primary agent owns task coordination and final deliverable
- Product manager coordinates complex multi-agent tasks
- Sequential execution prevents contradictions
- All agents must align with WordPress standards

**Emergency Response:**
- Critical security vulnerabilities require immediate escalation
- System-breaking issues bypass normal approval process
- Emergency fixes require post-implementation validation

**Agent Self-Evaluation:**
- Post-task analysis: "What you did versus what you said"
- Scope delivery verification and accuracy validation
- Quality self-assessment (8/10+ threshold)
- Document gaps and propose corrections

### **Critical Rules (Ordered by Priority - Higher numbers = Higher priority)**

### 📜 WordPress Standards (MANDATORY)

**ALL code MUST follow WordPress Standards:**
- WordPress Coding Standards from https://developer.wordpress.org/
- WooCommerce development guidelines and APIs
- Plugin Security Standards (nonces, sanitization, validation)
- WordPress APIs ONLY - Use native functions, hooks, and APIs

**CRITICAL RULES:**
1. **Error memory system** - Always check KNOWN_ISSUES.md first and document new issues immediately
2. **Wireframe supremacy** - NEVER modify established wireframe designs when implementing new features
3. **Quality threshold enforcement** - Never bypass 8/10 requirement
4. **Explain before implementing** - Always provide short, concise explanation before proposing code changes
5. **DoD validation required** - NEVER mark any task complete without executing full Definition of Done validation protocol
6. **ALL documentation in English** - Never use other languages in files

**OPERATIONAL RULES:**
- **Checkpoint protocols** - Before major changes: verify current state, create restoration point, validate prerequisites
- **Session-based workflow** - Always maintain session traceability
- **Execution tracking mandatory** - Create and maintain execution reports with real-time task completion updates

**ENHANCEMENT TOOLS:**
- **Context7 integration** - Use real-time research for all decisions
- **MCP Playwright validation** - Visual analysis is mandatory
- **Proactive commit reminders** - ALWAYS remind user to commit after completing milestones
- **Ultra-flat structure** - Maintain simplified repository architecture

**METHODOLOGY:**
- **No complex automation** - Focus on core framework functionality
- **Try before escalating** - Attempt at least 2 solution approaches before creating impediment report

### **WUPOS Project Rules**

1. **Clean code only** - No unnecessary code
2. **Bootstrap 5 only** - No DaisyUI, no React/Node.js
3. **Commits with important changes** - Complete documentation
4. **TodoWrite for tracking** - Use for all complex tasks
5. **Agent specialization mandatory** - Each task to appropriate agent
6. **User approval required** - No task completion without permission
7. **Token Optimization Rule** - ALWAYS use Grep tool for content searches instead of bash commands


### **Output Standards**
- **File naming:** Follow exact pattern `{step}_{session-id}_{project-name}.md`
- **Language:** English only in all documentation and code
- **Quality:** Professional standards with certification
- **Traceability:** Perfect session linkage across all files

---


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
- **TodoWrite tool**: Real-time task tracking during sessions with context transfer capabilities
- **KNOWN_ISSUES.md**: Issue tracking and resolution status
- **Session Context**: `.context/session-{id}_handoff.md` files for session continuity

### Current High Priority Tasks
1. **Fix default order status** - Backend config not applied
2. **Implement tax visualization** - UI missing for calculated taxes
3. **Create held carts modal** - Hold function works but no UI management
4. **Fix zero-amount orders** - Orders created with $0.00 total
5. **View Control Panel** - Dynamic view controls per specifications

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