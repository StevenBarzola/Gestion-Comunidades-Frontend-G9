# Gestion-Comunidades-Frontend-G9

## Estructura del Proyecto (Frontend)

```text
/
├── assets/                 # Archivos estáticos
│   └── style.css           # Estilos base y variables compartidas del proyecto
│
├── config/                 # Configuraciones globales
│   └── api.php             # Archivo base para el consumo dinámico de la API (cURL)
│
├── includes/               # Componentes visuales reutilizables
│   ├── header.php          # Etiquetas <head>, importación de estilos y barra de navegación
│   └── footer.php          # Cierre de etiquetas HTML y carga de scripts
│
├── auth/                   # Manejo dinámico de sesiones
│   ├── session_check.php   # Script validador para proteger rutas privadas
│   └── logout.php          # Destrucción de la sesión HTTP y redirección
│
├── procesos/               # Lógica del servidor (Consumo de API y redirecciones)
│   ├── login_process.php   # Autenticación de usuarios
│   ├── club_process.php    # (Steven) Procesamiento de módulos de Clubes y Membresías
│   ├── inventario_process.php # (Julio) Procesamiento del registro de bienes tecnológicos
│   └── voto_process.php    # (Isaac) Validación y emisión del voto único
│
├── views/                  # Vistas principales de la aplicación (Interfaces)
│   ├── directorio.php      # (Steven) Catálogo público de agrupaciones
│   ├── inventario.php      # (Julio) Panel administrativo de control de ítems
│   ├── asambleas.php       # (Isaac) Listado de elecciones activas y cerradas
│   └── votar.php           # (Isaac) Papeleta interactiva de votación
│
└── index.php               # Punto de entrada principal