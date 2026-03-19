# GreenGrid 360

Aplicacion web en PHP (MVC simple) para consultar mediciones ambientales con autenticacion por sesion.

## Objetivo General

El objetivo del proyecto es centralizar la consulta de datos ambientales (temperatura, humedad y calidad del aire) en una interfaz web sencilla, con control de acceso por usuario.

A nivel funcional, el sistema permite:
- Registrar usuarios (modo local por sesion, pensado para migrar a BD).
- Iniciar y cerrar sesion.
- Visualizar una tabla de mediciones ambientales.
- Filtrar las mediciones por fecha y por rangos numericos.

## Enfoque Tecnico

El proyecto usa una arquitectura MVC basica:
- **Modelos**: acceso y transformacion de datos.
- **Controladores**: validaciones y reglas de negocio.
- **Vistas**: render HTML/CSS (sin logica de negocio compleja).
- **Router frontal**: `public/index.php` decide la accion segun `?accion=`.

## Estructura del Proyecto

```text
app/
  controller/
    AuthController.php
    MedicionController.php
  database/
    Database.php
  model/
    MedicionAmbiental.php
    Usuario.php
  view/
    error.php
    login.php
    mediciones.php
    registro.php
css/
  auth.css
  base.css
  index.css
  login.css
  mediciones.css
  registro.css
public/
  index.php
```

## Flujo General del Sistema

1. El usuario entra a `public/index.php`.
2. El router evalua `accion` (`login`, `registro`, `listar`, etc.).
3. Si la accion requiere autenticacion, se valida `$_SESSION['usuario']`.
4. El controlador procesa la solicitud y consulta al modelo.
5. El resultado se renderiza en la vista correspondiente.

## Componentes Principales

### 1) `public/index.php` (Front Controller)
Responsabilidades:
- Iniciar sesion (`session_start()`).
- Cargar controladores y conexion.
- Enrutar por accion:
  - `login`
  - `registro`
  - `logout`
  - `listar`
  - `ver`
  - `crear`
- Proteger rutas de mediciones mediante `requireAuth()`.

### 2) `app/database/Database.php`
Responsabilidades:
- Abrir conexion MySQL con `mysqli`.
- Exponer `getConexion()`.
- Cerrar conexion con `cerrar()`.

Configuracion actual:
- Host: `localhost`
- Usuario: `root`
- Password: vacia
- Base de datos: `greengrid360`

### 3) `app/controller/AuthController.php`
Responsabilidades:
- Mostrar formularios de login y registro.
- Validar datos de autenticacion.
- Crear sesion de usuario.
- Cerrar sesion.
- Forzar autenticacion para rutas privadas.

### 4) `app/model/Usuario.php`
Responsabilidades:
- Gestionar usuarios en memoria de sesion (`$_SESSION['usuarios_locales']`).
- Registrar usuarios nuevos.
- Autenticar correo y contrasena.

Notas:
- Incluye un usuario demo por defecto:
  - Correo: `admin@localhost`
  - Password: `admin123`
- Esta preparado para migrar a tabla `usuarios` en BD.

### 5) `app/controller/MedicionController.php`
Responsabilidades:
- Listar mediciones.
- Leer y validar filtros desde query string.
- Solicitar al modelo los datos filtrados.
- Renderizar vista de tabla.

Filtros soportados:
- `fecha_desde`
- `fecha_hasta`

### 6) `app/model/MedicionAmbiental.php`
Responsabilidades:
- Consultar mediciones desde `medicion_ambiental`.
- Aplicar filtros opcionales en SQL.
- Obtener medicion por ID.
- Insertar nuevas mediciones.

Campos utilizados actualmente:
- `fecha_hora`
- `temperatura`
- `humedad`
- `calidad_aire`

## Vistas y Estilos

### Vistas
- `login.php`: inicio de sesion.
- `registro.php`: alta de usuarios locales.
- `mediciones.php`: panel con resumen, filtros y tabla de datos.
- `error.php`: mensajes de error controlados.

### CSS por pagina
- `base.css`: variables y estilos base compartidos.
- `auth.css`: estilos comunes de autenticacion.
- `login.css`: estilos de login.
- `registro.css`: estilos de registro.
- `mediciones.css`: estilos de panel de mediciones.
- `index.css`: archivo puente (compatibilidad/imports).

## Rutas de Acceso

En XAMPP:
- Login: `http://localhost/greengrid360/public/index.php?accion=login`
- Registro: `http://localhost/greengrid360/public/index.php?accion=registro`
- Listado: `http://localhost/greengrid360/public/index.php?accion=listar`

Si no hay sesion iniciada, `listar` redirige a `login`.

## Requisitos

- PHP 7.4+ (recomendado 8.x)
- MySQL/MariaDB
- XAMPP o entorno equivalente
- Base de datos `greengrid360`

## Ejecucion Local

1. Clonar/copiar el proyecto en `htdocs`.
2. Iniciar Apache y MySQL desde XAMPP.
3. Crear base de datos `greengrid360`.
4. Crear tabla `medicion_ambiental` (segun estructura del proyecto).
5. Abrir:
   - `http://localhost/greengrid360/public/index.php`

## Seguridad y Limitaciones Actuales

- La autenticacion esta en sesion local (no persistente en BD).
- No hay control de roles/permisos.
- Faltan validaciones de negocio mas estrictas para inserciones.
- No hay pruebas automatizadas ni migraciones de esquema.

## Mejoras Futuras Recomendadas

- Migrar usuarios de sesion a tabla `usuarios` en MySQL.
- Implementar CSRF tokens en formularios.
- Agregar paginacion en tabla de mediciones.
- Incorporar pruebas unitarias basicas (modelos/controladores).
- Manejar configuracion por variables de entorno.

## Estado del Proyecto

Proyecto funcional para entorno local, con base MVC clara y preparado para evolucionar a una capa de persistencia de usuarios en base de datos.
