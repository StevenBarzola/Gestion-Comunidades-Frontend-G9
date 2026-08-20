# Gestion-Comunidades-Frontend-G9

## Requisitos previos
- PHP 7.4 o superior instalado
- El backend (Django) corriendo localmente (ver README del backend)

## 1. Clonar el repositorio
```bash
git clone <URL_DEL_REPOSITORIO_FRONTEND>
cd <carpeta-frontend>
```

## 2. Configurar la URL del backend
Este frontend consume la API poniendo la URL del backend directamente en un archivo de configuración.

Abrir el archivo:
```
config/api.php
```

Y reemplazar la URL actual (la que apuntaba al Codespace) por la del backend en local:
```php
// Antes (Codespace)
define('API_BASE_URL', 'https://ominous-xylophone-wrrvj5ppqp9725jwq-[puerto].app.github.dev/api/');

// Ahora (local)
define('API_BASE_URL', 'http://localhost:[puerto]/api/');
```

## 3. Levantar el servidor local de PHP
Desde la carpeta del proyecto:
```bash
php -S localhost:[puerto]
```

## 4. Probar la aplicación
Abrir en el navegador:
```
http://localhost:[puerto]
```

## Resumen del flujo completo para el profesor
1. Levantar el backend (`python manage.py runserver`) → puerto 8000.
2. Confirmar que `http://localhost:8000/api/...` muestra datos en el navegador.
3. Editar la URL de la API en el frontend PHP para que apunte a `http://localhost:8000/api`.
4. Levantar el frontend (`php -S localhost:8080`).
5. Probar la aplicación en `http://localhost:8080`.
