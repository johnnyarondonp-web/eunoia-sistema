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
- Node.js + npm
- `mysqldump` disponible en el PATH del servidor (para backups)

## Instalación

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
```

## Backup automático

El comando `db:backup` exporta la base de datos a `storage/app/backups/` y conserva solo los últimos 30 archivos.

Para activarlo en producción, agregar al crontab del servidor:

```
* * * * * cd /ruta-al-proyecto && php artisan schedule:run >> /dev/null 2>&1
```

El backup corre diariamente a las 3:00 AM.

## Categorías de productos

Definidas en `config/categories.php`. Cualquier cambio en ese archivo se refleja automáticamente en la validación y en la UI, sin necesidad de tocar controladores ni Form Requests.

## Tasa BCV

El sistema intenta obtener la tasa automáticamente desde `ve.dolarapi.com`. Si la API no responde, usa la última tasa manual guardada en la tabla `exchange_rates`. Si tampoco existe, cae al valor por defecto definido en `DolarService::DEFAULT_RATE`.
