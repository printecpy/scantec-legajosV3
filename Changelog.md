SCANTEC DMS - Notas de la Versión (Release Notes)

Versión: 1.2.0 (Stable Release)
Fecha de Despliegue: 2026-02-17
Codename: Security Hardening & Core Refactoring
Resumen Ejecutivo

Esta actualización mayor introduce una reingeniería del núcleo de procesamiento de documentos (DMS Core), enfocada en la compatibilidad estricta con PHP 8.x, la mitigación proactiva de vulnerabilidades de seguridad (OWASP Top 10) y la implementación de una arquitectura de almacenamiento semántica. Se han optimizado los algoritmos de rasterización de imágenes y la inyección de metadatos para garantizar la integridad forense de los archivos digitales.
Nuevas Capacidades (Features)
1. Inyección de Metadatos y Taxonomía Documental

    Native Metadata Embedding: Implementación de la librería FPDI extendida para la inyección programática de metadatos XMP en los archivos PDF generados. Los documentos ahora incluyen propiedades de Título, Autor, Asunto y Palabras Clave (Keywords) basadas en los índices de la base de datos, facilitando la indexación externa y la auditoría forense.

    Semantic Storage Architecture: Se ha migrado el sistema de almacenamiento de archivos planos (Flat File System) a una Estructura Jerárquica Semántica. Los activos digitales ahora se organizan físicamente en directorios nombrados según la taxonomía del documento (ej: /Expedientes/Facturas_Proveedores/), eliminando la ofuscación por IDs numéricos y mejorando la gestión directa del servidor.

2. Motor de Procesamiento de Imágenes (Rasterization Pipeline)

    Alpha Channel Flattening: Nuevo algoritmo de pre-procesamiento mediante GD Library para la normalización de imágenes. El sistema detecta automáticamente canales alfa (transparencias) en archivos PNG y realiza un flattening sobre lienzo blanco antes de la conversión a PDF, eliminando artefactos visuales y errores de renderizado ("Páginas en Blanco").

    MIME-Type Standardization: Validación estricta y conversión al vuelo de flujos de datos de imagen (JPG/JPEG/PNG) para asegurar la compatibilidad total con el estándar PDF 1.4+.

Seguridad y Hardening (Security Compliance)
1. Saneamiento de Vectores de Entrada

    Path Traversal Mitigation: Implementación de expresiones regulares estrictas (Regex Sanitization) en los controladores de archivos para neutralizar intentos de navegación de directorios (../). Se fuerza la limpieza de caracteres no alfanuméricos en los nombres de rutas físicas.

    Input Sanitization Refactoring: Migración completa de la limpieza de variables $_POST y $_GET utilizando filter_input() con la flag FILTER_SANITIZE_FULL_SPECIAL_CHARS, mitigando riesgos de XSS (Cross-Site Scripting) e inyección de código.

2. Integridad del Flujo de Trabajo

    CSRF Token Enforcement: Validación robusta de tokens anti-CSRF en los endpoints de fusión de documentos (UnirpdfController), rechazando cualquier petición que no provenga de un origen firmado por la sesión del usuario.

    Directory Permissions (ACLs): La creación dinámica de directorios ahora aplica máscaras de permisos 0777 (ajustable según umask del servidor) de forma recursiva, asegurando la accesibilidad controlada por el servicio web.

🔧 Correcciones Técnicas y Mantenimiento (Bug Fixes & Refactoring)

    PHP 8.x Compatibility: Resolución de advertencias E_DEPRECATED y errores fatales relacionados con el tipado estricto de funciones nativas (intval, preg_replace). El sistema es ahora 100% compatible con entornos de ejecución modernos.

    Database Schema Alignment: Corrección en el mapeo ORM del modelo UnirpdfModel. Se han alineado las sentencias SQL INSERT para coincidir con la definición DDL de la tabla histórica, resolviendo excepciones PDOException: Column not found.

    Memory Management Optimization: Ajuste dinámico de memory_limit y set_time_limit(0) en tiempo de ejecución para procesos batch de alto volumen, previniendo desbordamientos de buffer durante la fusión de expedientes extensos.

Instrucciones de Despliegue (Deployment)

Para actualizar desde la versión v1.1.x en entornos de producción:

    Backup: Realizar una instantánea (Snapshot) de la base de datos y del directorio /Expedientes.

    Code Pull: Desplegar los nuevos controladores y modelos vía git pull origin main.

    Dependency Check: Verificar que la extensión php-gd esté habilitada en el php.ini.

    Cache Clear: Limpiar la caché de OPcache si aplica.

    Nota para Auditoría: Esta versión cumple con los requisitos técnicos preliminares para la gestión segura de documentos electrónicos.

Para más detalles sobre las características y mejoras específicas, consulte la documentación oficial del sistema o comuníquese con el equipo de soporte técnico.

¡Gracias por utilizar el Sistema de Gestión Documental SCANTEC!

© 2026 SCANTEC - PRINTEC SA. Proprietary Software. All rights reserved.