SCANTEC DMS - Notas de la Versión (Release Notes)

Versión: 1.5.0 (Release Candidate - pendiente de validación en cliente)
Fecha de Despliegue: 2026-11-06
Codename: Hardened Access

Resumen Ejecutivo

Esta versión está dedicada a la seguridad. Corrige las vulnerabilidades críticas detectadas en la auditoría de código de la versión 1.4.1: restablecimiento de contraseña sin verificación, ejecución de acciones sin sesión, lectura de archivos del servidor por ruta, renombrado arbitrario de archivos, código inyectado en las bitácoras de auditoría y escalada al rol de Administrador Scantec por nombre de usuario. Además establece una jerarquía de roles estricta, valida el grupo del usuario en todas las operaciones sobre documentos y endurece el servidor web para que solo index.php pueda ejecutarse.

Se recomienda actualizar todas las instalaciones con la versión 1.4.1 o anterior.

Seguridad (Security Fixes)

1. Autenticación y sesión

    Restablecimiento de contraseña: el restablecimiento público (nombre + usuario) queda deshabilitado; lo realiza el administrador desde Gestión de usuarios.

    Sesión obligatoria centralizada: la clase base de controladores exige sesión iniciada en todas las rutas salvo una lista explícita de rutas públicas (inicio de sesión, registro y formularios externos de Legajos). Las redirecciones de acceso detienen siempre la ejecución.

    Sin sesión, sin accesos: un usuario sin sesión o con un rol inválido no recibe ningún acceso del catálogo de funcionalidades.

    Endurecimiento de la sesión: se documenta la configuración recomendada de php.ini (session.cookie_httponly, session.use_strict_mode, session.cookie_samesite).

2. Roles y permisos

    Administrador Scantec: el rol 1 ya no se asigna por nombre de usuario ("root"/"scantec") ni puede otorgarse desde la web. Todas las comprobaciones de superadministrador usan una única regla (id_rol = 1).

    Jerarquía de roles: cada rol gestiona solo usuarios de menor privilegio. El rol de Seguridad no ve acciones ni puede modificar a usuarios con rol 1 o 2; solo el Administrador Scantec y el Administrador (rol 2) pueden otorgar el rol de Administrador. Nadie puede cambiar su propio rol.

    Altas y modificaciones de usuarios: nombres de usuario reservados (root, scantec, admin, administrador) en registro, alta y edición; contraseña mínima de 10 caracteres; validación de correo, estado y grupo (debe existir y estar activo).

    Catálogo de funcionalidades: los ítems sin la marca 'defecto' nacen cerrados para roles no configurados; una clave de acceso inexistente ahora deniega (antes permitía). Nuevos ítems: "Servidor Active Directory", "Servidor de correo (SMTP)" y "Editar y anular documentos".

3. Expedientes (grupos)

    Lectura de archivos: se eliminan los métodos que recibían una ruta de archivo (api_verExpediente, obtener_metadatos_pdf, modificar_nombre y renombrar). La visualización, la marca de agua y los metadatos se resuelven por id de expediente y validan que el grupo del usuario tenga permiso vigente sobre el tipo de documento.

    Almacenamiento: la validación de rutas funciona con carpeta local (por ejemplo D:\), carpetas enlazadas y NAS por ruta UNC (\\servidor\recurso).

    Gestión de documentos: editar, modificar, anular y reactivar requieren el permiso "Editar y anular documentos", pertenencia del documento a un tipo del grupo y token CSRF.

    Permisos revocados: las consultas, búsquedas y reportes respetan el estado del permiso del grupo; los reportes por rango de índice y de duplicados filtran por grupo.

    Redirecciones: se elimina la redirección abierta de ver_expediente; el retorno después de editar o anular se valida contra una lista de pantallas internas.

4. Auditoría

    Bitácoras protegidas: las pantallas de auditoría (sesiones, fallos de sesión, visitas, log del sistema, log de documentos y monitor de conexiones) escapan todos los datos antes de mostrarlos.

    Reportes Excel: las columnas de texto de los reportes de auditoría y expedientes se escriben como texto explícito (sin interpretación de fórmulas).

    Registro de intentos: los textos guardados en la bitácora de fallos se limitan en longitud y sin caracteres de control.

    Permisos de reportes: cada PDF y Excel de auditoría valida el permiso del ítem correspondiente.

5. Servidor web (.htaccess)

    Solo index.php puede ejecutarse como PHP; cualquier otro archivo PHP responde 403.

    Carpetas internas (Config, Controller, Models, Views, Libraries, Helpers, logs, temp, tools, BD, backups) responden 404 cuando se solicita un archivo real; las rutas de la aplicación con el mismo nombre (por ejemplo logs/views) siguen funcionando.

    Archivos sensibles bloqueados: .env y respaldos, licencias (.lic), logs, volcados SQL, documentación, composer.json/lock y system_properties.json.

    Cabeceras: se agrega Referrer-Policy y se desactiva X-XSS-Protection (obsoleta). expose_php pasa a configurarse en php.ini.

Correcciones Técnicas y Mantenimiento (Bug Fixes & Refactoring)

    Rendimiento: la verificación de roles base ya no se ejecuta en cada petición (se eliminan dos actualizaciones sobre la tabla de usuarios por clic); se aplica una sola vez como migración al iniciar sesión.

    Configuración del Active Directory y del correo: las pantallas quedaban accesibles para cualquier usuario por una clave de permiso inexistente; ahora requieren el permiso correspondiente.

    Auditoría: las rutas de Auditoría dejaban de funcionar por coincidir con la carpeta física logs/; corregido.

    Formulario de registro: el correo se valida sin alteraciones; el token del formulario se compara en tiempo constante y no se reutiliza.

    Grupos: los grupos con estado vacío (NULL) se consideran activos.

    Logs de Umango: la exportación por fecha valida la fecha recibida.

    JavaScript: se elimina una función duplicada de metadatos que reemplazaba a la de la pantalla; tables.js se versiona para evitar que el navegador use una copia vieja.

    Código sin uso: se eliminan métodos de ejemplo y consultas heredadas sin filtro de grupo.

Herramientas de Despliegue

    tools\prueba_predespliegue.ps1: sintaxis PHP, configuración (.env y php.ini), patrones inseguros conocidos, archivos que no deben viajar en el paquete y pruebas HTTP del .htaccess. Guarda el reporte y lo copia al portapapeles. No modifica nada.

    tools\generar_jwt_secret.php: genera o rota el JWT_SECRET de la instalación (solo por consola, sin mostrar el valor).

Cambios que requieren acción al actualizar (Upgrade Notes)

    Roles sin configuración: los roles que no tengan accesos guardados en "Accesos por rol y departamento" solo verán Buscador, Dashboard y Buscar legajos. Revisar cada rol después de actualizar.

    Nuevo permiso de documentos: para que un rol distinto de 1 y 2 pueda editar o anular documentos, habilitar "Editar y anular documentos".

    Administrador Scantec: solo se crea o asigna por consola, con acceso al servidor. Las cuentas existentes se conservan.

    Restablecimiento de contraseña: informar a los usuarios que lo soliciten al administrador.

    Integraciones que llamaban archivos .php directamente: el servidor ahora responde 403; deben usar la API REST.

    JWT_SECRET: verificar que sea propio de la instalación (prueba_predespliegue.ps1). Si se rota, las integraciones deben volver a iniciar sesión.

    Base de datos: UPDATE usu_grupo SET estado = 'ACTIVO' WHERE estado IS NULL;

    php.ini: session.cookie_httponly = 1, session.use_strict_mode = 1, session.cookie_samesite = "Lax", expose_php = Off. session.cookie_secure = 1 solo si el sistema se accede siempre por HTTPS. Reiniciar Apache.

    Navegadores: si algún usuario ve comportamientos viejos (por ejemplo, error en el modal de metadatos), borrar los datos del sitio (service worker de una versión anterior).

    PHP: sin cambios. Compatible con PHP 8.1 y 8.2; entregar el zip ionCube que corresponde al PHP de cada cliente.

Instrucciones de Despliegue (Deployment)

Para actualizar desde la versión v1.4.1 en entornos de producción:

    Backup: Realizar un backup de la base de datos y de la carpeta del sistema.

    Actualizar: Reemplazar el .zip de aplicacion\ y ejecutar Instalar-SCANTEC.ps1 -Paso Aplicacion.

    Base de datos: Ejecutar la corrección de grupos (UPDATE usu_grupo ... WHERE estado IS NULL).

    Configuración: Ajustar php.ini y reiniciar Apache. Verificar el JWT_SECRET.

    Comprobar: Ejecutar Verificar-Actualizacion.ps1 y tools\prueba_predespliegue.ps1 -BaseUrl "<url del cliente>" (0 FALLAS).

    Primer ingreso: Iniciar sesión con el Administrador Scantec (aplica la migración de roles base) y revisar "Accesos por rol y departamento".

    Pruebas: Inicio de sesión local y de dominio; Expedientes (buscar, ver, metadatos, subir, editar, anular); usuario de otro grupo no accede a documentos ajenos; Legajos (armar, verificar, PDF final, formulario externo); Auditoría (pantallas, PDF y Excel); rol de Seguridad sin acceso a roles 1 y 2; API (inicio de sesión, renovación y descarga).

    Reversión: Restaurar la carpeta y la base desde el backup.

Para más detalles, consulte el manual de instalación o comuníquese con el equipo de soporte técnico.

© 2026 SCANTEC - PRINTEC SA. Proprietary Software. All rights reserved.