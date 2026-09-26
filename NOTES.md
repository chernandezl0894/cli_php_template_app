## Docker Commands

- `docker compose build --no-cache php-cli` fuerza la reconstrucción de la imagen.
- `docker compose run --rm php-cli php consoles/app.php <command> <option>` crea un contenedor, ejecuta el script y lo elimina.

## Tasks

- `docker compose run --rm php-cli php consoles/app.php app:task:process-reminders` corre el proceso para enviar los recordatorios por notificacion.
- `docker compose run --rm php-cli php consoles/app.php app:task:list` lista todas las tareas
- `docker compose run --rm php-cli php consoles/app.php app:task:create "<title>" -d "<description>" -r "<remainder>+1 minute"` crea una nueva tarea usando los siguiente argumento.
    -   1. `<title>` titulo de la tarea
    -   2. `<description>` `-d` descripcion de la tarea
    -   3. `<remainder>` `-r` tiempo para el recordatorio

## Mejoras

1. **Entrada mediante API REST / HTTP:**
    - Agregar un directorio `public/index.php` con un enrutador ligero (como `FastRoute` o `Slim Framework`). De esta forma, el código dentro de `src/Tasks/Services/` podrá reutilizarse tanto para peticiones HTTP como para comandos de consola CLI.
2. **Pruebas Automatizadas (Testing):**
    - Incorporar **PHPUnit** y crear pruebas unitarias para los servicios (`TaskService`) mockeando la base de datos y la API de Telegram.
3. **Exceptions**
4. **Contenedor Dedicado de Cron en Docker:**
    - Agregar un servicio `scheduler` en `docker-compose.yml` que ejecute `php consoles/app.php schedule:run` en un bucle continuo cada 60 segundos. Esto elimina la necesidad de configurar manualmente `crontab` en el servidor o laptop host.
5. **Sistema de Migraciones SQL:**
    - En lugar de tener sentencias `CREATE TABLE IF NOT EXISTS` embebidas en `Database.php`, se puede implementar una carpeta `database/migrations/` con un comando `app:migrate` para controlar los cambios de esquema por versión.
6. **Abstracción del Canal de Notificaciones (Interfaces):**
    - Crear una interfaz `NotificationChannelInterface` y hacer que `TelegramService` la implemente. Así, si mañana deseas enviar notificaciones por Discord, Email o Slack, solo implementas la interfaz sin alterar el servicio de tareas.

### ¿Cómo transformar este proyecto en una Plantilla Reutilizable?

1. **Limpiar Dominios Específicos:**
    - Crear una rama llamada `template/base` donde remuevas la carpeta `src/Tasks/` y conserves únicamente `core/`, `config/`, `docker/` y un comando de ejemplo en `src/Greet/`.
2. **Uso de GitHub Template Repository:**
    - Marcar el repositorio en GitHub como **"Template repository"**. De esta forma, cada vez que desees iniciar un nuevo proyecto CLI o microservicio, podrás hacer clic en _"Use this template"_ y obtener la arquitectura lista para usar.

## PHP Stan Levels

- Nivel 0: Errores de sintaxis básicos, clases desconocidas, métodos/funciones no existentes llamados directamente y argumentos faltantes.
- Nivel 1: (El que configuramos) Variables potencialmente no definidas y llamadas a métodos ambiguos sobre $this.
- Nivel 2: Métodos desconocidos llamados en todas las expresiones (no solo $this), validación de PHPDocs básicos.
- Nivel 3: Tipos de retorno de funciones y métodos (verifica que lo que devuelves coincida con lo declarado).
- Nivel 4: Código muerto o inalcanzable (dead code), comparaciones siempre verdaderas/falsas y comprobaciones instanceof redundantes.
- Nivel 5: Tipos de argumentos pasados a funciones y métodos (comprueba que coincidan con los tipos esperados).
- Nivel 6: Obliga a especificar tipos en propiedades, parámetros y retornos cuando faltan (missing typehints).
- Nivel 7: Reporta tipos parcialmente nulos de forma estricta (ej. intentar acceder a un método en una variable que podría ser null).
- Nivel 8: Prohíbe totalmente invocar métodos o acceder a propiedades sobre valores que pueden ser null sin validación previa.
- Nivel 9: Máximo rigor: prohíbe el uso del tipo implícito mixed a menos que sea absolutamente explícito.

## Herramienta,Propósito,Comando en Docker

- PHP-CS-Fixer 🎨 (Verificar)
    - Revisa si el código cumple PSR-12 y tus reglas de estilo.
    - `docker compose run --rm php-cli vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes`
- PHP-CS-Fixer 🧹 (Corregir)
    - Formatea y corrige automáticamente el estilo del código.
    - `docker compose run --rm php-cli vendor/bin/php-cs-fixer fix --allow-risky=yes`
- PHPCS 🛡️ (Verificar)
    - Revisa errores de estándares del código.
    - `docker compose run --rm php-cli vendor/bin/phpcs -n`
- PHPCBF 🩹 (Corregir)
    - Corrige automáticamente lo que PHPCS pueda formatear.
    - `docker compose run --rm php-cli vendor/bin/phpcbf`
- PHPStan 🧠 (Analizar)
    - Análisis estático para detectar errores de tipos o lógica.
    - `docker compose run --rm php-cli vendor/bin/phpstan analyse`
- PHPUnit 🧪 (Probar)
    - Ejecuta el conjunto de pruebas unitarias.
    - `docker compose run --rm php-cli vendor/bin/phpunit --testsuite=Unit`

## SQLite3 Shell

- `docker compose run --rm php-cli sqlite3 database/database.sqlite`

### Commands

1. `.exit` o `.quit` para salir.
2. `.table` Lista todas las tablas de la base de datos.
3. `.schema table_name` Muestra la sentencia `SQL CREATE TABLE` usada para crear esa tabla.
4. `.headers on` Muestra los nombres de las columnas en los resultados de tus consultas.
5. `.mode column` Formatea la salida en columnas alineadas para que sea fácil de leer.
6. `.show` Muestra la configuración actual de la CLI.

### Consultas SQL Habituales

1. `SELECT * FROM tasks;`
2. `SELECT COUNT(*) FROM tasks;`
3. `SELECT id, title, status, is_recurring FROM tasks WHERE id = 1;`

## Terraform

### Commands

1. `docker run --rm -v $(pwd)/terraform:/workspace -w /workspace hashicorp/terraform:latest init`
2. `docker run --rm -v $(pwd)/terraform:/workspace -w /workspace hashicorp/terraform:latest validate`
