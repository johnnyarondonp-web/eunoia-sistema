# Eunoia Sistema — Inventario y Ventas

Sistema privado de gestión de inventario, ventas y balance de rentabilidad para Eunoia Cosmetics.

## Requisitos

- PHP 8.2+
- MySQL 8+
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
