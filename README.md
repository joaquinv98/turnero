# Café Pelotero - Sistema de Gestión de Turnos

Sistema de gestión de turnos para cafeterías con área de juegos infantiles.

## Características

- **Gestión de Turnos**: Crea y gestiona turnos con temporizadores en tiempo real
- **Panel Administrativo**: Interfaz completa para administrar turnos, precios y configuración
- **Pantalla Pública**: Visualización en tiempo real de los turnos activos para los clientes
- **Sistema de Precios**: Configuración flexible de precios por cantidad de niños y duración
- **Notificaciones por Email**: Envío automático de confirmaciones con archivos ICS para calendario
- **Reportes**: Generación de reportes diarios y exportación a CSV
- **Historial**: Registro completo de todos los turnos del día

## Requisitos

- PHP 8.0 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)

## Instalación

1. Clona el repositorio en tu servidor web
2. Navega a `http://tu-dominio/setup.php`
3. Completa el formulario de configuración:
   - Credenciales de la base de datos
   - Usuario y contraseña del administrador
   - (Opcional) Configuración SMTP para envío de emails

El script de instalación creará automáticamente:
- La base de datos y todas las tablas necesarias
- El archivo de configuración `config/db.php`
- El usuario administrador
- Los precios por defecto

## Configuración

### Precios
Los precios se configuran desde el panel de administración en la sección "Configuración". Puedes definir precios para:
- 1-4 niños
- Duraciones de 1, 2 o 3 horas

### Email (SMTP)
Configura los parámetros SMTP desde el panel de administración para habilitar el envío de confirmaciones por email con archivos de calendario adjuntos.

### Tiempo de Limpieza
Define cuántos minutos permanecen visibles los turnos finalizados antes de desaparecer de la pantalla pública.

## Uso

### Panel de Administración
Accede a `http://tu-dominio/admin.php` con las credenciales del administrador.

**Características:**
- Crear nuevos turnos
- Ver turnos activos en tiempo real
- Finalizar turnos manualmente
- Ver historial del día
- Generar reportes
- Configurar precios y ajustes

### Pantalla Pública
Accede a `http://tu-dominio/viewer.php` para mostrar los turnos activos en una pantalla visible para los clientes.

**Características:**
- Actualización automática cada 2 segundos
- Temporizadores en tiempo real
- Alertas visuales y sonoras cuando se cumple el tiempo
- Interfaz diseñada para pantallas grandes

## Estructura del Proyecto

```
cafe-pelotero/
├── api/              # Endpoints de la API
├── assets/           # CSS, JS e imágenes
├── config/           # Configuración de la base de datos (generado)
├── admin.php         # Panel de administración
├── viewer.php        # Pantalla pública
├── index.php         # Página de login
└── setup.php         # Instalador (se auto-elimina después de la instalación)
```

## Seguridad

- Las contraseñas se almacenan usando `password_hash()` de PHP
- Todas las consultas a la base de datos usan prepared statements
- El archivo de configuración `config/db.php` está excluido del repositorio
- El script de instalación se auto-elimina después de la configuración inicial

## Desarrollo por

**NEATECH.AR**

## Licencia

Este proyecto es de código abierto. Puedes usarlo y modificarlo libremente para tus necesidades.
