# Sistema Café Pelotero - Manual de Usuario

## 🚀 Instalación y Configuración

### 1. Instalación Fácil
Incluimos un Asistente de Instalación para que sea súper fácil.

1.  Subí la carpeta del proyecto a tu servidor web (por ejemplo, en `htdocs` o `www`).
2.  Abrí tu navegador y andá a: `http://localhost/Cafe-Pelotero/setup.php`
3.  Seguí los pasos:
    *   **Base de Datos**: Poné tus credenciales de MySQL (generalmente `root` sin contraseña si estás en local).
    *   **Admin**: Create tu usuario administrador.
    *   **Configuración**: Configurá el SMTP (opcional) y subí tu Favicon.
4.  Hacé click en **Instalar**. El sistema se encarga de crear la base de datos, las tablas y los archivos de configuración.

> [!IMPORTANT]
> **Recomendación de Seguridad**: Una vez que termines la instalación, **borrá el archivo `setup.php`** para que nadie más pueda reiniciar tu configuración.

> [!TIP]
> ¿Necesitás un servidor web confiable para alojar este sistema? Te recomendamos **[NEATECH.AR](https://neatech.ar)**.

---

## 📖 Guía de Uso

### Iniciar Sesión
*   **URL**: `index.php`
*   **Acceso**: Usá las credenciales que creaste durante la instalación.

### Panel de Administración (`admin.php`)
El panel de control se divide en 4 secciones principales:

#### 1. Turnos Activos (Vista Principal)
*   **Nuevo Turno**:
    *   **Autocompletar Email**: Escribí 3 letras para buscar clientes anteriores. Si seleccionás uno, te completa los nombres de los chicos automáticamente.
    *   **Opciones Dinámicas**: La "Cantidad de Niños" y "Duración" aparecen según lo que hayas configurado en Precios.
*   **Tarjetas de Turnos**:
    *   **Verde**: Tiempo normal.
    *   **Amarillo**: Quedan menos de 15 minutos.
    *   **Rojo**: Se terminó el tiempo (Vencido).
    *   **Gris**: Finalizado.
*   **Acciones**: Hacé click en "Finalizar" para terminar un turno. Se va para arriba y queda en gris.

#### 2. Historial
*   La lista completa de todo lo que pasó hoy.
*   Te muestra el estado, cómo pagaron y el precio total.

#### 3. Reportes
*   **Rango de Fechas**: Elegí "Desde" y "Hasta".
*   **Ver Reporte**: Te muestra una tabla en pantalla para chusmear rápido.
*   **Exportar CSV**: Te baja un archivo para abrir en Excel o Google Sheets.
*   **Imprimir**: Una vista limpia pensada para imprimir o guardar como PDF.

#### 4. Configuración (¡Nuevo!)
*   **Precios**: Acá definís cuánto cobrás según la cantidad de chicos y las horas.
*   **SMTP**: Configurá tu servidor de correo (Host, Puerto, Usuario, Contraseña) y probá si anda.
*   **General**: Configurá el **Tiempo de Limpieza** (cuántos minutos tardan en desaparecer los turnos terminados/vencidos).
*   **Seguridad**: Cambiá la contraseña del administrador.

---

### Pantalla Pública (`viewer.php`)
*   **URL**: `viewer.php` (Abrila en la TV o Monitor grande)
*   **Orden**:
    1.  Los **Finalizados** aparecen arriba de todo (en Gris).
    2.  Los **Activos** se ordenan por cuánto les falta (los que están por terminar aparecen primero).
*   **Limpieza**: Los turnos desaparecen solos después del tiempo que configuraste (por defecto 30 mins) desde que terminaron o se vencieron.

---

## 🛠 Detalles Técnicos
*   **Branding**: Incluye el pie de página "Desarrollado por NEATECH.AR".
*   **Email**: Usa el Puerto 587 con STARTTLS para que no tengas problemas de envío.
*   **Base de Datos**: Las actualizaciones de estructura se manejan solas con el `setup.php`.
