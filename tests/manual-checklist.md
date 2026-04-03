# SCANTEC Manual QA Checklist

## Authentication & Login

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-AUTH-001 | Usuario activo existente con clave válida. | 1. Abrir login. 2. Ingresar usuario y clave válidos. 3. Enviar formulario. | El sistema inicia sesión, crea sesión activa y redirige al dashboard. | [ ] |
| TC-AUTH-002 | Usuario existente con clave inválida. | 1. Abrir login. 2. Ingresar clave incorrecta. 3. Enviar. | El acceso es rechazado, se muestra mensaje de error y se registra intento fallido. | [ ] |
| TC-AUTH-003 | Usuario inactivo. | 1. Intentar iniciar sesión con usuario inactivo. | El sistema rechaza el acceso por estado inactivo. | [ ] |
| TC-AUTH-004 | Usuario expirado o fuera de vigencia. | 1. Intentar iniciar sesión con usuario expirado. | El acceso es rechazado por vigencia expirada. | [ ] |
| TC-AUTH-005 | Dos navegadores disponibles. | 1. Iniciar sesión en navegador A. 2. Repetir login con el mismo usuario en navegador B. | El sistema detecta sesión duplicada y obliga a confirmar o rechazar continuidad. | [ ] |
| TC-AUTH-006 | Sesión activa abierta. | 1. Permanecer sin actividad hasta expirar CSRF/sesión. 2. Enviar formulario protegido. | El sistema rechaza el POST por token vencido y redirige correctamente. | [ ] |
| TC-AUTH-007 | Admin con acceso a monitor de sesiones. | 1. Abrir monitor de sesiones. 2. Invalidar la sesión de otro usuario. 3. Pedir acción al usuario invalidado. | La sesión remota queda cerrada y el usuario ya no puede continuar operando. | [ ] |

## Legajo Creation Wizard

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-LEG-001 | Usuario con permiso `create`. | 1. Ir a `Armar legajo`. 2. Completar todos los datos obligatorios. 3. Guardar. | Se crea el legajo en estado inicial esperado y aparece en búsqueda. | [ ] |
| TC-LEG-002 | Usuario con permiso `create`. | 1. Intentar guardar sin CI o sin tipo de legajo. | El sistema bloquea la creación e informa campos obligatorios faltantes. | [ ] |
| TC-LEG-003 | Legajo existente en borrador. | 1. Completar datos base. 2. Guardar cambios. | El estado cambia según la lógica configurada y el badge visible coincide. | [ ] |
| TC-LEG-004 | Legajo con todos los documentos obligatorios cargados. | 1. Abrir legajo. 2. Verificar porcentaje de completitud. | El porcentaje coincide con la matriz de requisitos obligatorios. | [ ] |
| TC-LEG-005 | Legajo completado pero sin PDF final. | 1. Buscar el legajo. 2. Revisar dashboard y listados. | Se muestra en la bandeja/estado que corresponda según la lógica actual del sistema. | [ ] |
| TC-LEG-006 | Legajo listo para generar PDF. | 1. Hacer clic en `Armar`. 2. Esperar finalización. | Se genera el PDF final, el estado visible cambia a `Generado` y queda disponible para verificación. | [ ] |
| TC-LEG-007 | Legajo generado. | 1. Modificar datos o documentos del legajo. 2. Guardar. | Si la lógica vigente lo exige, el estado vuelve a completado y exige regenerar PDF. | [ ] |

## Document Upload UI

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-DOC-001 | Legajo abierto en edición. | 1. Subir un PDF válido. | El documento se guarda, queda vinculado al requisito y se ve en la grilla. | [ ] |
| TC-DOC-002 | Legajo abierto en edición. | 1. Arrastrar y soltar archivo válido en el área de carga. | La UI acepta el archivo y dispara la misma carga que el selector manual. | [ ] |
| TC-DOC-003 | Legajo abierto en edición. | 1. Subir imagen JPG/PNG válida. | El sistema la acepta y la previsualización funciona si corresponde. | [ ] |
| TC-DOC-004 | Legajo abierto en edición. | 1. Intentar subir `.exe` o tipo no permitido. | La carga es rechazada con mensaje claro. | [ ] |
| TC-DOC-005 | Legajo abierto en edición. | 1. Intentar subir archivo por encima del límite permitido. | La carga es rechazada por tamaño máximo. | [ ] |
| TC-DOC-006 | Requisito con `REEMPLAZAR`. | 1. Cargar un documento. 2. Cargar otro sobre el mismo requisito. | El nuevo archivo reemplaza al anterior según la política. | [ ] |
| TC-DOC-007 | Requisito con `UNIR_AL_INICIO`. | 1. Cargar documento existente. 2. Cargar segundo documento. | El sistema une/agrega el nuevo documento al inicio. | [ ] |
| TC-DOC-008 | Requisito con `UNIR_AL_FINAL`. | 1. Cargar documento existente. 2. Cargar segundo documento. | El sistema une/agrega el nuevo documento al final. | [ ] |
| TC-DOC-009 | Requisito con `NO_PERMITIR`. | 1. Cargar documento existente. 2. Intentar cargar otro. | La segunda carga se rechaza. | [ ] |
| TC-DOC-010 | Documento con fecha de emisión y vigencia. | 1. Cargar documento. 2. Revisar fecha de vencimiento calculada. | El vencimiento = emisión + vigencia base configurada. | [ ] |
| TC-DOC-011 | Documento cercano al vencimiento. | 1. Revisar estado documental. | El sistema marca `POR_VENCER`. | [ ] |
| TC-DOC-012 | Documento vencido. | 1. Revisar estado documental. | El sistema marca `VENCIDO`. | [ ] |

## Permission Matrix UI

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-PERM-001 | Usuario Super Admin (`id_rol=1`). | 1. Entrar a módulos restringidos. | Puede acceder aunque otros permisos no estén marcados. | [ ] |
| TC-PERM-002 | Usuario no admin sin permiso específico. | 1. Intentar entrar a vista restringida. | El acceso es bloqueado y la vista no se muestra. | [ ] |
| TC-PERM-003 | Rol con permisos parciales de legajos. | 1. Revisar matriz `create/edit/verify/close/delete`. 2. Probar cada acción. | Solo se habilitan las acciones autorizadas. | [ ] |
| TC-PERM-004 | Grupo con tipos de legajo limitados. | 1. Abrir tipos visibles. 2. Comparar con permisos por grupo. | Solo aparecen tipos permitidos para ese grupo/rol. | [ ] |
| TC-PERM-005 | Usuario RRHH sin acceso a Seguridad. | 1. Iniciar sesión como RRHH. 2. Revisar menú lateral. | El menú/vista Seguridad no se muestra. | [ ] |

## Expedientes

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-EXP-001 | Usuario con permiso de expedientes. | 1. Crear expediente con todos los índices requeridos. | El expediente se guarda correctamente. | [ ] |
| TC-EXP-002 | Requisito de matriz existente. | 1. Asociar expediente a un `id_requisito`. | El expediente queda vinculado al requisito correcto. | [ ] |
| TC-EXP-003 | Expediente existente. | 1. Cambiar estado de validación a `VIGENTE`. | El cambio se guarda y se refleja en la consulta. | [ ] |
| TC-EXP-004 | Expediente existente. | 1. Cambiar estado a `POR_VENCER`, `VENCIDO`, `RECHAZADO` y `HISTORICO`. | El sistema admite y refleja cada estado válido. | [ ] |
| TC-EXP-005 | Datos disponibles en vista consolidada. | 1. Buscar expediente desde filtros de búsqueda. | La consulta devuelve datos desde `v_expedientes` con filtros correctos. | [ ] |
| TC-EXP-006 | Bandeja de búsqueda con filtros. | 1. Filtrar por índices, fecha y estado. | Los resultados coinciden exactamente con los filtros. | [ ] |

## REST API

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-API-001 | JWT válido y endpoint protegido disponible. | 1. Hacer GET con `Authorization: Bearer <token>`. | Respuesta 200 con payload esperado. | [ ] |
| TC-API-002 | Sin token JWT. | 1. Hacer GET sin cabecera Authorization. | Respuesta 401. | [ ] |
| TC-API-003 | JWT mal formado o firma inválida. | 1. Enviar token alterado. | Respuesta 401 y registro del fallo. | [ ] |
| TC-API-004 | Endpoint mutador disponible. | 1. Hacer POST autenticado. 2. Hacer PUT autenticado. 3. Hacer DELETE autenticado. | Las tres operaciones quedan registradas en log API. | [ ] |
| TC-API-005 | Misma IP con varios fallos previos. | 1. Repetir autenticación inválida hasta superar umbral. | La IP queda bloqueada o rate limited según la política. | [ ] |

## Admin Panel

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-ADM-001 | Admin autenticado. | 1. Entrar a gestión de usuarios. 2. Crear usuario. | El usuario se crea con rol, grupo y estado correctos. | [ ] |
| TC-ADM-002 | Admin autenticado. | 1. Editar usuario existente. 2. Cambiar rol/departamento/estado. | Los cambios se guardan y se reflejan al volver a abrir. | [ ] |
| TC-ADM-003 | Admin autenticado. | 1. Abrir monitor de sesiones. 2. Revisar sesiones activas. | Se listan sesiones vigentes con usuario y origen. | [ ] |
| TC-ADM-004 | Admin autenticado. | 1. Cerrar sesión remota desde el monitor. | La sesión seleccionada deja de estar activa. | [ ] |

## SMTP Configuration

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-SMTP-001 | Acceso a configuración SMTP. | 1. Cargar host, puerto, usuario, clave y remitente válidos. 2. Guardar. | La configuración se persiste sin errores. | [ ] |
| TC-SMTP-002 | Configuración SMTP válida. | 1. Ejecutar envío de prueba. | El correo de prueba llega al destinatario configurado. | [ ] |
| TC-SMTP-003 | Configuración SMTP inválida. | 1. Guardar credenciales erróneas. 2. Ejecutar envío de prueba. | El sistema informa fallo de autenticación o conexión claramente. | [ ] |

## License Validation Screen

| Test Case ID | Preconditions | Steps to Reproduce | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-LIC-001 | Archivo de licencia válido disponible. | 1. Abrir pantalla de validación/licencia. | El sistema muestra licencia válida, estado y vencimiento correctos. | [ ] |
| TC-LIC-002 | Licencia alterada o inválida. | 1. Reemplazar por licencia corrupta. 2. Abrir pantalla. | La licencia se rechaza y se informa error de validación. | [ ] |
| TC-LIC-003 | Licencia vencida. | 1. Cargar licencia expirada. | El sistema detecta expiración y restringe el uso según la política. | [ ] |
| TC-LIC-004 | Licencia con datos cifrados sensibles. | 1. Validar licencia. 2. Revisar información mostrada. | Los datos sensibles no se exponen indebidamente en pantalla. | [ ] |
