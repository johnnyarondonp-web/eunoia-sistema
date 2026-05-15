# Eunoia Sistema 🌌

**Eunoia** es un sistema avanzado de gestión de inventario y punto de venta (POS) diseñado para optimizar el control de existencias, el cálculo de ganancias reales y el manejo multi-moneda en entornos dinámicos.

---

## 🛠️ Stack Tecnológico

El sistema está construido sobre las versiones más recientes y estables para garantizar rendimiento y seguridad:

### **Backend**
- **PHP:** `8.3.1` (Zend Engine v4.3.1)
- **Framework:** `Laravel 13.5.0`
- **Base de Datos:** MySQL / SQLite (Soporta transacciones ACID para integridad de datos)
- **Servicios Externos:** Integración con [DolarAPI](https://ve.dolarapi.com) para tasas en tiempo real.

### **Frontend**
- **Vite:** `8.0.0` (Bundler de última generación)
- **Tailwind CSS:** `3.1.0` (con plugin de Vite `^4.0.0`)
- **Alpine.js:** `3.4.2` (Reactividad ligera para la UI)
- **Axios:** `1.15.0` (Comunicación asíncrona)

---

## 🧠 Lógica y Funcionamiento del Sistema

Eunoia no es solo un registro de ventas; es una herramienta de inteligencia de negocios que aplica reglas contables precisas.

### 1. Gestión de Inventario mediante Lotes (FIFO)
El sistema utiliza la metodología **FIFO (First-In, First-Out)** para la salida de mercancía.
- **¿Cómo funciona?** Cada vez que registras una compra de mercancía (Gasto/Expense), el sistema crea un "lote" con un costo específico.
- **Lógica de Venta:** Cuando se realiza una venta, el `LotDeductionService` busca el lote más antiguo con existencia y descuenta de allí. Si la venta supera la existencia de un lote, salta al siguiente.
- **Cálculo de Ganancia:** La ganancia (`profit`) se calcula de forma individual para cada `SaleItem` comparando el precio de venta actual contra el costo unitario del lote específico del que salió el producto. Esto permite saber exactamente cuánto se ganó, incluso si compraste el mismo producto a diferentes precios en el tiempo.

### 2. Manejo Multi-Moneda Dinámico
Diseñado para economías con fluctuación cambiaria (como Venezuela).
- **Tasa BCV Automática:** El `DolarService` consulta la API de BCV cada 30 minutos y cachea el resultado para máxima velocidad.
- **Respaldo Manual:** Si la API falla, el sistema utiliza una tasa manual configurada por el administrador en la base de datos.
- **Precios en USD, Pagos en BS:** El sistema permite fijar precios en dólares pero calcula automáticamente el total en Bolívares al momento de la venta usando la tasa más reciente.

### 3. Integridad de Transacciones
Todas las operaciones críticas (Ventas y Cancelaciones) están envueltas en **DB Transactions**.
- **Ventas:** Se descuenta stock, se asignan lotes y se crean los registros de venta en una sola operación atómica. Si algo falla, el stock no se toca.
- **Cancelaciones:** Al cancelar una venta, el sistema es capaz de rastrear exactamente de qué lotes salió la mercancía y **restaurar** la cantidad restante en esos lotes específicos, devolviendo el stock al estado exacto anterior a la venta.

---

## 🚀 Módulos Principales

| Módulo | Descripción |
| :--- | :--- |
| **Dashboard** | Vista general con productos activos y estados de stock. |
| **Productos** | Gestión de catálogo, control de stock mínimo y estados (pausado/activo). |
| **Ventas** | Interfaz de facturación rápida con búsqueda de productos y cálculo de tasa. |
| **Balance** | Análisis de ingresos vs gastos, exportación de datos y gestión de costos. |
| **Configuración** | Control manual de tasas de cambio y perfiles de usuario. |

---

## ⚙️ Instalación y Configuración

1. **Clonar el repositorio:**
   ```bash
   git clone <url-repo>
   ```

2. **Ejecutar script de configuración automática:**
   (Este comando instala dependencias de PHP y JS, genera llaves, corre migraciones y compila assets)
   ```bash
   composer run setup
   ```

3. **Iniciar entorno de desarrollo:**
   ```bash
   composer run dev
   ```

---

## 📁 Estructura de Lógica Clave

- `app/Services/LotDeductionService.php`: El "corazón" del sistema. Maneja la lógica FIFO y la trazabilidad de costos.
- `app/Services/DolarService.php`: Gestiona la obtención y el respaldo de la tasa cambiaria.
- `app/Http/Controllers/SaleController.php`: Orquestador de las transacciones de venta y cancelaciones.
- `app/Models/Expense.php`: Representa los lotes de entrada de mercancía.

---
*Análisis y documentación generada para el sistema Eunoia.*
