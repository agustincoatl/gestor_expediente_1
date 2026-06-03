# Gestor de Expedientes Docentes

Sistema Yii2 Advanced para gestionar expedientes docentes del ITSV.

## Requisitos

- PHP 8.2 o superior
- Composer
- MySQL/MariaDB
- Extensiones PHP: `pdo_mysql`, `zip`, `mbstring`, `intl`
- Servidor web Apache/Nginx

## Instalacion en servidor dummy

1. Clonar el repositorio.

```bash
git clone https://github.com/agustincoatl/gestor_expediente_1.git
cd gestor_expediente_1
```

2. Instalar dependencias.

```bash
composer install
```

3. Inicializar Yii2.

```bash
php init
```

Seleccionar el ambiente que corresponda. Para pruebas internas normalmente sirve `dev`; para servidor publicado usar `prod`.

4. Crear la base de datos.

```sql
CREATE DATABASE gestor_expediente CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

5. Importar la base dummy.

```bash
mysql -u usuario -p gestor_expediente < database/dummy_schema.sql
```

6. Configurar `common/config/main-local.php` con los datos reales del servidor.

Ejemplo basico:

```php
'db' => [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=gestor_expediente',
    'username' => 'usuario_bd',
    'password' => 'password_bd',
    'charset' => 'utf8',
],
```

7. Crear permisos de escritura para:

- `backend/runtime`
- `frontend/runtime`
- `console/runtime`
- `backend/web/assets`
- `frontend/web/assets`
- `backend/web/uploads`
- `backend/web/uploads/documents`

En Linux:

```bash
chmod -R 775 backend/runtime frontend/runtime console/runtime backend/web/assets frontend/web/assets backend/web/uploads
```

## Usuario inicial dummy

El SQL `database/dummy_schema.sql` crea un usuario administrador:

- Usuario: `admin`
- Password: `Admin1234`

Cambiar esta contrasena al primer ingreso.

## URLs esperadas

Dependiendo de la configuracion del servidor:

- Frontend: `/frontend/web/index.php`
- Backend: `/backend/web/index.php`

## Archivos que no se suben al repositorio

Por seguridad y limpieza, no se versionan:

- `vendor/`
- archivos de `runtime/`
- archivos generados en `web/assets/`
- documentos cargados en `backend/web/uploads/`
- configs locales con credenciales reales
- copias `.zip` del proyecto

## Notas para despliegue

- No subir documentos reales de docentes al repositorio.
- No subir claves SMTP ni contrasenas de base de datos.
- Si se requiere correo real, configurar el `mailer` en `common/config/main-local.php` directamente en el servidor.
- El servidor debe tener activa la extension PHP `zip` para descargar expedientes completos en comprimido.
