# Eunoia — Sistema de Inventario y Ventas

> Sistema web para la gestión de inventario, registro de ventas y análisis de rentabilidad por lotes, diseñado para pequeños negocios de productos cosméticos y de moda.

---

## ¿Qué es?

Eunoia es una aplicación web privada que permite a una emprendedora llevar el control completo de su negocio: desde registrar cada lote de mercancía comprado, hasta ver en tiempo real cuánto ha ganado con ese lote específico a lo largo del tiempo.

El sistema separa de forma inteligente el **flujo de caja mensual** (cuánto invertí y vendí este mes) del **rendimiento histórico de cada lote** (cuánto ha generado ese lote desde que se compró, sin importar en qué mes se vendió). Esto evita el error común de "congelar" el progreso de un lote al filtrar por mes.

---

## Stack tecnológico

| Tecnología | Uso |
|---|---|
| **Laravel 11** | Framework backend (PHP) |
| **PHP 8.2+** | Lenguaje base |
| **MySQL** | Base de datos relacional |
| **Eloquent ORM** | Queries, relaciones y subqueries |
| **Blade** | Motor de plantillas para las vistas |
| **Tailwind CSS** | Estilos y diseño responsive |
| **Vite** | Compilación de assets frontend |
| **Laravel Breeze** | Autenticación (login, registro, perfil) |
| **DolarAPI (ve.dolarapi.com)** | Tasa BCV en tiempo real con caché de 10 min |

---

## Funcionalidades principales

### 📦 Gestión de productos
- Registrar productos con nombre, categoría, precio, stock e imagen
- Al crear un producto, se genera automáticamente su primer **lote de inversión**
- Editar precio, datos e imagen de cualquier producto
- Agregar stock adicional desde la edición (genera un nuevo lote)
- Activar / pausar productos (los pausados no aparecen disponibles para venta)
- Eliminar productos con limpieza de imagen en storage

### 🛒 Registro de ventas
- Formulario para registrar ventas con múltiples productos en una sola transacción
- Lógica **FIFO** (First In, First Out): descuenta stock del lote más antiguo primero
- Conversión automática a bolívares usando la tasa BCV del momento de la venta
- Historial de ventas filtrable por rango de fechas

### 📊 Balance y rentabilidad
- **Tarjetas de flujo de caja mensual**: Inversión del mes, Ventas del mes, Ganancia Neta, ROI
- **Tabla de lotes**: muestra todos los lotes comprados en el período seleccionado con su historial de ventas completo (sin límite de mes), porcentaje de recuperación y ganancia generada
- Filtros por mes y año, buscador por nombre/categoría, ordenamiento por mejor/peor desempeño

### 💱 Conversión de divisas
- Integración con la API pública de DolarAPI para obtener la tasa BCV oficial
- Resultado cacheado 10 minutos para evitar requests innecesarios

---

## Estructura de la base de datos

```
products
  id · name · category · price · stock · image_path · status

expenses  ← "lotes de compra"
  id · product_id (FK) · quantity · remaining_quantity
  cost_usd · bcv_rate · total_bs · description

sales
  id · total_usd · bcv_rate · total_bs

sale_items
  id · sale_id (FK) · product_id (FK) · expense_id (FK)
  quantity · price_at_sale · profit
```

La relación clave es `sale_items.expense_id → expenses.id`, que permite atribuir cada unidad vendida al lote exacto del que provino, habilitando el cálculo de rentabilidad por lote.

---

## Instalación local

### Requisitos previos
- PHP 8.2+
- Composer
- Node.js 18+ y npm
- MySQL

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/johnnyarondonp/eunoia-sistema.git
cd eunoia-sistema

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS
npm install

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar base de datos en .env
# DB_DATABASE=eunoia
# DB_USERNAME=root
# DB_PASSWORD=tu_password

# 6. Ejecutar migraciones
php artisan migrate

# 7. Crear enlace de storage para imágenes
php artisan storage:link

# 8. Compilar assets
npm run dev

# 9. Iniciar servidor
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`.

---

## Variables de entorno relevantes

```env
APP_NAME=Eunoia
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eunoia
DB_USERNAME=root
DB_PASSWORD=

# Sin configuración extra necesaria para DolarAPI — es una API pública
```

---

## Lógica de negocio destacada

### Separación de Flujo de Caja vs. Rendimiento de Lote

El método `balance()` en `ExpenseController` implementa dos cálculos completamente distintos:

- **Tarjetas (flujo de caja):** filtran estrictamente por el mes/año seleccionado. Muestran solo lo que entró y salió de caja en ese período.
- **Tabla de lotes (rendimiento histórico):** el filtro de mes determina *qué lotes se muestran* (los comprados en ese mes), pero las ventas asociadas se suman **sin filtro de fecha** — incluyen todo el historial de ventas de ese lote, incluso si ocurrieron en meses posteriores.

### Sistema FIFO para ventas
Cuando se registra una venta, `SaleController` descuenta el stock del **lote más antiguo con unidades disponibles** (`remaining_quantity > 0`). Si una venta requiere más unidades que las disponibles en un lote, el sistema las distribuye entre múltiples lotes automáticamente.

---

## Estado del proyecto

El sistema está funcional en producción para uso personal. Las próximas mejoras planeadas son:

- [ ] Dashboard con gráficos de ventas mensuales
- [ ] Exportación de reportes a PDF
- [ ] Soporte multi-usuario con roles

---

## Autor

Desarrollado por Johnny Rondón — proyecto personal para la gestión de un emprendimiento propio.
