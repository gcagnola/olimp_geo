Actuá como desarrollador senior Laravel, analista funcional y arquitecto de sistemas para el proyecto **Olimpiada de Geografía — FHUC / UNL**.

Trabajá directamente sobre el proyecto abierto en VSCode.

# 1. Regla principal de trabajo

Antes de modificar archivos:

1. Leé `AGENTS.md` si existe.
2. Leé `README.md`.
3. Revisá la estructura actual del proyecto.
4. Revisá las migraciones existentes.
5. Revisá `routes/web.php`.
6. Revisá los controladores, modelos, vistas y archivos CSS/JS existentes.
7. Revisá `.env.example`, pero no expongas secretos de `.env`.
8. No reemplaces código funcional sin justificarlo.
9. No inventes tablas, campos, relaciones ni reglas sin validar primero el contexto indicado en este documento.
10. No ejecutes cambios destructivos sobre la base de datos.
11. No uses `migrate:fresh`, `db:wipe`, `DROP DATABASE` ni comandos equivalentes.
12. Trabajá por etapas pequeñas, verificables y con commits lógicos.
13. Antes de cada etapa, indicá brevemente qué archivos vas a crear o modificar.
14. Después de cada etapa, indicá cómo probarla.
15. Si encontrás una contradicción entre el código existente y este documento, detenete y explicala antes de continuar.

# 2. Estado técnico actual

El proyecto está instalado en:

```text
~/proyectos/olimp_geo
```

El código Laravel está en:

```text
~/proyectos/olimp_geo/src
```

Dentro del contenedor, Laravel está montado en:

```text
/var/www/html
```

Docker Compose está en:

```text
~/proyectos/olimp_geo/docker-compose.yml
```

Servicios:

```text
olimp-geo-app
olimp-geo-node
olimp-geo-db
```

Tecnologías:

```text
Laravel 13.17.0
PHP 8.4
Apache
PostgreSQL 17
Node.js 22
Vite 8
Blade
Bootstrap 5.3
Sass
JavaScript nativo
```

No introducir sin autorización:

```text
Livewire
Inertia
React
Vue
Alpine.js
Tailwind
jQuery
```

# 3. Ejecución de comandos

Todos los comandos deben ejecutarse dentro de Docker.

## Artisan

Usar:

```bash
cd ~/proyectos/olimp_geo

docker compose exec \
  --user www-data \
  -e HOME=/tmp \
  app \
  php artisan COMANDO
```

## Composer

Usar el contenedor `app`, respetando permisos.

## npm y Vite

Usar:

```bash
docker compose exec \
  --user "$(id -u):$(id -g)" \
  -e HOME=/tmp \
  node \
  npm COMANDO
```

Para compilar:

```bash
docker compose exec \
  --user "$(id -u):$(id -g)" \
  -e HOME=/tmp \
  node \
  npm run build
```

No ejecutar npm en el host.

# 4. URL y publicación

URL pública:

```text
https://fhuc0.unl.edu.ar/olimp_geo
```

Backend interno:

```text
http://192.168.50.24:8081
```

Laravel funciona bajo el subdirectorio:

```text
/olimp_geo
```

Configuración esperada:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=https://fhuc0.unl.edu.ar/olimp_geo
ASSET_URL=https://fhuc0.unl.edu.ar/olimp_geo
APP_TIMEZONE=America/Argentina/Cordoba

SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_PATH=/olimp_geo
SESSION_DOMAIN=null
```

No romper el funcionamiento bajo `/olimp_geo`.

Toda ruta, redirección, formulario, enlace, asset y middleware debe funcionar correctamente bajo ese prefijo.

# 5. Estética institucional

La interfaz debe respetar la estética del Campus Administrativo FHUC.

Características obligatorias:

```text
Logo institucional FHUC
Color naranja principal: #f47920
Fondo gris muy claro
Franja naranja horizontal
Tarjetas blancas con sombra suave
Bordes redondeados
Botones tipo píldora
Formularios claros y simples
Diseño responsive
Tipografía sobria
Pie institucional
```

El logo está en:

```text
public/imagenes/fhuc_2.png
```

No cambiar el logo ni reemplazarlo por uno genérico.

El sistema debe mostrar:

```text
Olimpiada de Geografía
Facultad de Humanidades y Ciencias
Universidad Nacional del Litoral
```

# 6. Objetivo funcional

Construir un sistema web para gestionar la Olimpiada de Geografía.

Debe contemplar:

```text
Administradores
Responsables o tutores
Escuelas
Categorías
Alumnos
Inscripciones
Evaluaciones en PDF
Historial de archivos
Ediciones de la olimpíada
```

# 7. Tipos de usuario

## 7.1 Administrador

El administrador:

```text
ingresa desde Campus
puede gestionar todo el sistema
puede crear y editar olimpíadas
puede gestionar escuelas
puede gestionar responsables
puede gestionar categorías
puede gestionar alumnos
puede corregir inscripciones
puede subir o reemplazar evaluaciones
puede consultar historial
puede exportar información
puede habilitar o cerrar etapas
```

La autenticación definitiva desde Campus se implementará más adelante mediante un puente seguro.

Mientras tanto, preparar una autenticación local de desarrollo para administradores, claramente separada de la futura integración con Campus.

No acoplar toda la aplicación a una autenticación temporal.

Crear una abstracción que permita reemplazar luego el mecanismo local por autenticación Campus.

## 7.2 Responsable o tutor

El responsable:

```text
ingresa directamente a Olimp Geo
usa correo electrónico y contraseña
puede pertenecer a una o varias escuelas
puede estar asociado a una o ambas categorías
puede consultar sus escuelas
puede registrar alumnos
puede editar alumnos mientras la etapa esté abierta
puede subir evaluaciones PDF
puede reemplazar una evaluación
puede consultar el archivo vigente
no puede acceder a escuelas ajenas
no puede acceder a alumnos ajenos
```

Contraseña inicial:

```text
basada en el número de documento
```

En el primer ingreso debe ser obligatorio cambiarla.

Las contraseñas deben almacenarse con `Hash::make()`.

Nunca almacenar contraseñas en texto plano ni MD5.

# 8. Categorías

Las categorías iniciales son:

```text
Categoría A
Categoría B
```

Una escuela puede participar en:

```text
Categoría A
Categoría B
ambas categorías
```

Un responsable puede estar vinculado a:

```text
una o varias escuelas
una o ambas categorías
```

Las categorías deben estar normalizadas en una tabla, no codificadas rígidamente en controladores o vistas.

# 9. Reglas de alumnos e inscripción

Datos mínimos del alumno:

```text
apellido
nombre
tipo de documento
número de documento
fecha de nacimiento, si corresponde
correo electrónico, opcional
escuela
categoría
olimpíada
estado
observaciones
```

Reglas obligatorias:

1. Un alumno no puede estar inscripto en dos categorías diferentes dentro de la misma olimpíada.
2. Un alumno no puede estar inscripto en dos escuelas diferentes dentro de la misma olimpíada.
3. La identidad principal del alumno se controla por documento.
4. Si existiera más de un tipo de documento, usar combinación de tipo de documento + número de documento.
5. Las restricciones importantes deben existir también en la base de datos cuando sea técnicamente posible.
6. No depender únicamente de validaciones de formulario.
7. No eliminar físicamente una inscripción si ya tiene movimientos o archivos.
8. Usar estados o baja lógica donde corresponda.

# 10. Escuelas

Debe existir una tabla conceptual compatible con:

```text
escuelas
```

Datos sugeridos, sujetos a revisión de estructuras existentes:

```text
id
nombre
cue
domicilio
localidad
codigo_postal
provincia
region
telefono
email_institucional
tipo_gestion
niveles
activa
timestamps
```

No asumir que todos los campos existen: revisar primero las estructuras heredadas o archivos de referencia del proyecto.

Si ya existe una definición heredada de `escuelas`, conservar compatibilidad conceptual.

# 11. Olimpíadas

Debe existir una tabla conceptual compatible con:

```text
olimpiadas
```

Campos esperados:

```text
id
nombre
anio
fecha_inicio
fecha_fin
fecha_inicio_inscripcion
fecha_fin_inscripcion
fecha_inicio_carga_evaluaciones
fecha_fin_carga_evaluaciones
estado
activa
timestamps
```

Estados sugeridos:

```text
borrador
inscripcion_abierta
inscripcion_cerrada
evaluaciones_abiertas
evaluaciones_cerradas
finalizada
```

Debe haber una sola olimpíada activa operativamente, salvo que el modelo existente indique otra cosa.

# 12. Modelo de datos propuesto

Antes de crear migraciones, revisar si estas tablas ya existen.

Estructura conceptual recomendada:

```text
usuarios
roles
usuario_roles

escuelas
olimpiadas
categorias

responsables
responsable_escuela
responsable_escuela_categoria

alumnos
inscripciones

evaluaciones
evaluacion_archivos
auditorias
```

Podés simplificar roles usando un campo controlado si la complejidad no justifica tablas separadas, pero explicá la decisión.

Relaciones mínimas:

```text
responsable pertenece a uno o varios usuarios
responsable pertenece a varias escuelas
responsable puede operar varias categorías por escuela
alumno tiene identidad propia
inscripción vincula alumno + escuela + categoría + olimpíada
evaluación pertenece a una inscripción
evaluación puede tener varias versiones de archivo
solo una versión está vigente
```

# 13. Archivos PDF

Las evaluaciones deben almacenarse fuera de `public`.

Directorio persistente montado:

```text
storage/app/private/evaluaciones
```

El volumen host está relacionado con:

```text
~/proyectos/olimp_geo/pdf
```

Reglas obligatorias:

```text
solo PDF
validar MIME real
validar extensión
validar tamaño máximo
generar nombre interno no predecible
no usar directamente el nombre original
guardar nombre original como metadato
guardar tamaño
guardar hash SHA-256
guardar usuario que subió
guardar fecha
guardar versión
guardar motivo de reemplazo, cuando corresponda
```

No sobrescribir archivos históricos.

Cada reemplazo debe:

```text
crear una nueva versión
marcar la anterior como no vigente
mantener trazabilidad
```

La descarga debe pasar por un controlador autorizado.

Nunca exponer directamente el directorio de archivos.

# 14. Auditoría

Registrar acciones relevantes:

```text
inicio de sesión
cambio de contraseña
alta de escuela
edición de escuela
alta de responsable
edición de responsable
alta de alumno
edición de alumno
creación de inscripción
cambio de categoría
subida de PDF
reemplazo de PDF
cambio de estado de olimpíada
acciones administrativas sensibles
```

Datos mínimos:

```text
usuario
acción
entidad
id de entidad
datos anteriores
datos posteriores
IP
user agent
fecha
```

Evitar guardar contraseñas, tokens o secretos en auditoría.

# 15. Seguridad

Implementar:

```text
CSRF
validación mediante Form Requests
Policies
Gates cuando corresponda
middleware de autenticación
middleware de cambio obligatorio de contraseña
autorización por escuela
autorización por categoría
rate limiting para login
protección contra acceso horizontal
protección de archivos privados
regeneración de sesión al iniciar sesión
invalidación de sesión al cerrar sesión
```

No confiar en IDs enviados desde formularios.

Siempre verificar que el usuario tenga relación válida con la escuela, categoría, alumno e inscripción solicitados.

# 16. Pantallas requeridas

## Públicas

```text
Inicio
Ingreso de responsables
Recuperación de contraseña
Cambio de contraseña inicial
Información de la olimpíada
```

## Responsable

```text
Panel principal
Mis escuelas
Mis categorías
Listado de alumnos
Alta de alumno
Edición de alumno
Inscripciones
Carga de evaluación PDF
Historial de archivos
Perfil
Cambio de contraseña
```

## Administrador

```text
Dashboard
Olimpíadas
Categorías
Escuelas
Responsables
Alumnos
Inscripciones
Evaluaciones
Historial de archivos
Auditoría
Reportes
Configuración
```

# 17. Dashboard administrativo

Mostrar al menos:

```text
escuelas participantes
responsables registrados
alumnos inscriptos
inscriptos en categoría A
inscriptos en categoría B
evaluaciones cargadas
evaluaciones pendientes
inscripciones por localidad
inscripciones por provincia
estado actual de la olimpíada
fechas importantes
```

Evitar dashboards decorativos sin datos reales.

# 18. Reportes y exportaciones

Preparar exportaciones CSV o XLSX para:

```text
escuelas
responsables
alumnos
inscripciones
evaluaciones presentadas
evaluaciones pendientes
inscripciones por categoría
inscripciones por escuela
inscripciones por localidad
```

No incorporar una librería pesada sin justificarla.

# 19. Seeders

Crear seeders mínimos para desarrollo:

```text
un administrador local
una olimpíada activa
categoría A
categoría B
dos escuelas de prueba
dos responsables
alumnos de prueba
```

No usar datos personales reales.

Documentar las credenciales de desarrollo únicamente en `README.md` o `.env.example`, nunca en producción.

# 20. Convenciones Laravel

Usar:

```text
Eloquent
Form Requests
Policies
Services para reglas complejas
Actions cuando una operación lo justifique
Enums nativos de PHP para estados
Database Transactions
Soft Deletes cuando corresponda
casts
route model binding
named routes
Blade components reutilizables
```

Evitar:

```text
SQL crudo sin necesidad
lógica de negocio dentro de Blade
controladores gigantes
modelos con responsabilidades excesivas
duplicación de validaciones
IDs mágicos
estados escritos como strings repetidos
```

# 21. Componentes Blade

Crear componentes reutilizables para:

```text
layout institucional
encabezado
barra naranja
pie
tarjetas
títulos de página
mensajes flash
errores de validación
botones
tablas
paginación
confirmaciones
formularios
badges de estado
```

Mantener la estética Campus en todas las pantallas.

# 22. Base de datos y migraciones

Antes de crear migraciones:

1. Inspeccionar migraciones existentes.
2. Inspeccionar tablas actuales.
3. Proponer el modelo final.
4. Mostrar claves primarias.
5. Mostrar claves foráneas.
6. Mostrar índices únicos.
7. Mostrar restricciones.
8. Explicar cómo se impide la doble inscripción.

Toda migración debe tener método `down()` válido.

No modificar migraciones ya ejecutadas sin explicar el impacto.

Crear migraciones nuevas para cambios posteriores.

# 23. Flujo de desarrollo

Trabajar en este orden:

## Etapa 1

```text
documentar estado actual
crear o actualizar AGENTS.md
crear README técnico
confirmar rutas y estructura
```

## Etapa 2

```text
modelo de datos
migraciones
enums
modelos
relaciones
factories
seeders
```

## Etapa 3

```text
autenticación local de responsables
cambio obligatorio de contraseña
recuperación de contraseña
sesiones
middleware
```

## Etapa 4

```text
layout definitivo Campus
componentes Blade
navegación
panel de responsable
panel administrativo
```

## Etapa 5

```text
ABM de olimpíadas
ABM de categorías
ABM de escuelas
ABM de responsables
```

## Etapa 6

```text
ABM de alumnos
inscripciones
validación de duplicados
restricciones de seguridad
```

## Etapa 7

```text
subida de evaluaciones
versionado
descarga privada
historial
auditoría
```

## Etapa 8

```text
reportes
exportaciones
dashboard
pruebas
```

## Etapa 9

```text
integración con autenticación Campus
endurecimiento de seguridad
configuración de producción
APP_DEBUG=false
pruebas finales
documentación operativa
```

No intentes implementar todas las etapas en una sola respuesta ni en un solo cambio.

# 24. Pruebas

Crear pruebas Feature y Unit para:

```text
login
cambio obligatorio de contraseña
permisos
acceso por escuela
acceso por categoría
alta de alumno
duplicidad de documento
doble inscripción
carga de PDF
rechazo de archivos no PDF
reemplazo de evaluación
historial de versiones
descarga autorizada
descarga no autorizada
cierre de períodos
```

Usar base de datos de prueba separada.

No ejecutar pruebas destructivas sobre la base principal.

# 25. Entregables

El proyecto debe quedar con:

```text
código funcional
migraciones
seeders
factories
tests
README
AGENTS.md
documentación del modelo de datos
documentación de despliegue
documentación de backup
documentación de restauración
documentación de integración con Campus
```

# 26. Primer trabajo a realizar ahora

No empieces todavía a construir todo.

Primero hacé solamente esto:

1. Revisá el proyecto completo.
2. Mostrá el inventario actual de archivos relevantes.
3. Indicá qué está implementado y qué falta.
4. Revisá si existe `AGENTS.md`.
5. Revisá si existen migraciones propias.
6. Revisá las rutas.
7. Revisá la configuración de Vite.
8. Revisá el layout institucional actual.
9. Revisá la conexión PostgreSQL.
10. Proponé el modelo de datos definitivo.
11. Marcá cualquier duda funcional.
12. No modifiques archivos hasta mostrar el diagnóstico.

Clasificá cada conclusión como:

```text
CONFIRMADO
INFERIDO
PROPUESTA
PENDIENTE
```

Después del diagnóstico, esperá autorización antes de crear migraciones o código funcional.
