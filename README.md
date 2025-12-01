# Proyecto Simply

Sistema de gestión para centros médicos, laboratorios y colaboradores (buddies) con autenticación por roles.

## Características

- **Sistema de Login**: Autenticación por email y contraseña
- **Roles de Usuario**: CENTRO, LABORATORIO, BUDDY, ADMIN
- **Interfaces Separadas**: Cada rol tiene su propia carpeta con prefijo "site_"
- **Colores por Rol**: Cada rol tiene su color distintivo en la interfaz
  - **CENTRO**: Azul (#4FC3F7) - Fiable, Seguro, Profesional
  - **BUDDY**: Verde (#66BB6A) - Pacífico, Equilibrio, Crecimiento  
  - **LABORATORIO**: Naranja (#FF7043) - Amistad, Disponibilidad, Alegría, Confianza
  - **ADMIN**: Púrpura (#9C27B0) - Autoridad, Lujo, Creatividad
- **Gestión de Datos**: Centros, laboratorios y proyectos
- **Barra Superior**: Con información del usuario, colores por rol y logout

## Estructura del Proyecto

```
proyecto_simply/
├── config/
│   └── database.php          # Configuración de base de datos
├── includes/
│   ├── auth.php             # Sistema de autenticación
│   ├── header.php           # Header común con barra superior (colores por rol)
│   └── footer.php           # Footer común
├── site_centro/             # Interfaz para usuarios CENTRO (Color: Azul #4FC3F7)
│   ├── index.php           # Dashboard de centro
│   └── centros.php         # Gestión de centros
├── site_laboratorio/        # Interfaz para usuarios LABORATORIO (Color: Naranja #FF7043)
│   ├── index.php          # Dashboard de laboratorio
│   └── proyectos.php      # Gestión de proyectos
├── site_buddy/             # Interfaz para usuarios BUDDY (Color: Verde #66BB6A)
│   ├── index.php         # Dashboard de buddy
│   ├── proyectos.php     # Explorar proyectos
│   └── centros.php       # Ver centros disponibles
├── site_admin/             # Interfaz para usuarios ADMIN (Color: Púrpura #9C27B0)
│   ├── index.php         # Dashboard de administración
│   ├── laboratorios.php  # ABM de laboratorios
│   ├── centros.php       # ABM de centros
│   ├── buddys.php        # ABM de BUDDYs
│   └── proyectos.php     # ABM de proyectos
├── index.php             # Página de login
├── logout.php           # Proceso de logout
├── database.sql         # Estructura y datos de la base de datos
└── README.md           # Este archivo
```

## Instalación

1. **Configurar XAMPP**:
   - Asegúrate de que XAMPP esté ejecutándose
   - Apache y MySQL deben estar activos

2. **Crear la Base de Datos**:
   - Abre phpMyAdmin (http://localhost/phpmyadmin)
   - Importa el archivo `database.sql` o ejecuta las consultas manualmente

3. **Configurar la Base de Datos**:
   - Edita `config/database.php` si necesitas cambiar las credenciales
   - Por defecto usa: host=localhost, db=drakon_simply, user=root, pass=''

4. **Acceder al Sistema**:
   - Navega a `http://localhost/proyecto_simply/`
   - Usa las credenciales de prueba

## Usuarios de Prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| CENTRO | centro@test.com | 123456 |
| LABORATORIO | laboratorio@test.com | 123456 |
| BUDDY | buddy@test.com | 123456 |
| ADMIN | admin@test.com | 123456 |

## Funcionalidades por Rol

### CENTRO
- Dashboard con estadísticas
- Gestión de centros médicos
- Visualización de reportes

### LABORATORIO
- Dashboard con proyectos propios
- Creación y gestión de proyectos
- Visualización de centros colaboradores

### BUDDY
- Dashboard con proyectos disponibles
- Exploración de proyectos con filtros
- Visualización de centros disponibles
- Solicitud de colaboraciones

### ADMIN
- Dashboard con estadísticas del sistema
- ABM completo de laboratorios
- ABM completo de centros médicos
- ABM completo de usuarios BUDDY
- ABM completo de proyectos
- Reportes y estadísticas generales

## Datos de Prueba

El sistema incluye:
- 10 centros médicos de prueba
- 10 laboratorios de prueba
- 5 proyectos de ejemplo
- 3 usuarios de prueba (uno por cada rol)

## Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL
- **Frontend**: Bootstrap 5, Font Awesome
- **Autenticación**: Sesiones PHP
- **Seguridad**: MD5 para contraseñas (en producción usar bcrypt)

## Notas de Seguridad

- Las contraseñas están hasheadas con MD5 (cambiar a bcrypt en producción)
- Se incluye validación básica de entrada
- Las sesiones se manejan de forma segura
- Se incluye protección contra inyección SQL con PDO

## Desarrollo Futuro

- Implementar funciones de edición y eliminación
- Añadir sistema de recuperación de contraseña
- Mejorar la seguridad con bcrypt
- Añadir más validaciones
- Implementar sistema de notificaciones
