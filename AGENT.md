# Guía de Arquitectura y Agente (AGENT.md)

Este documento detalla la estructura, patrones y arquitectura de la aplicación CLI en PHP. Sirve como referencia técnica para extender el proyecto de manera consistente.

---

## Árbol del Proyecto y Propósito de Archivos

```bash
cli_php_template_app/
├── config/                  # Archivos de configuración PHP (devuelven arrays)
│   ├── app.php              # Ajustes generales (zona horaria, entorno)
│   ├── database.php         # Parámetros de conexión a SQLite/PDO
│   ├── logging.php          # Niveles y rutas de archivos de log
│   └── telegram.php         # Tokens y Chat ID de la API de Telegram
├── consoles/
│   └── app.php              # Punto de entrada ejecutable de la CLI (Symfony Console)
├── core/                    # Núcleo e infraestructura reutilizable
│   ├── Config.php           # Gestor de configuración con sintaxis dot-notation
│   ├── ContainerFactory.php # Factoría que inicializa el contenedor PHP-DI
│   ├── Database.php         # Wrapper PDO para SQLite y auto-creación de esquema
│   ├── Event.php            # Despachador de eventos del sistema
│   ├── Kernel.php           # Definición de tareas programadas del Scheduler
│   ├── LoggerService.php    # Servicio centralizado de logs con Monolog
│   └── Scheduler.php        # Planificador ligero de comandos tipo Cron
├── database/
│   ├── database.sqlite      # Base de datos SQLite local
│   └── schema.sql           # Script DDL inicial de la base de datos
├── docker/
│   └── Dockerfile           # Imagen de PHP-CLI con extensiones (PDO, etc.)
├── src/                     # Código de dominio de la aplicación
│   ├── Greet/               # Dominio de saludo (ejemplo básico)
│   ├── Heartbeat/           # Dominio de verificación de estado
│   ├── Shared/              # Servicios y comandos transversales
│   │   ├── Commands/        # ScheduleRunCommand (ejecutor del ciclo del Scheduler)
│   │   └── Services/        # TelegramService (cliente para la API de Telegram)
│   └── Tasks/               # Dominio principal GTD
│       ├── Commands/        # CreateTaskCommand, ListTasksCommand, ProcessRemindersCommand
│       ├── DTOs/            # CreateTaskData (Objeto de transferencia de datos)
│       ├── Entities/        # Task (Entidad pura de dominio)
│       ├── Repositories/    # TaskRepository (Acceso a datos con PDO)
│       └── Services/        # TaskService (Lógica de negocio y recordatorios)
├── bootstrap.php            # Carga de Composer, variables .env y constantes globales
├── composer.json            # Gestión de dependencias PHP
└── docker-compose.yml       # Definición de contenedores Docker
```
---

## Clases de la Capa `Core` y Uso Recomendado

La capa `Core` provee los cimientos técnicos de la aplicación. Se deben usar mediante **Inyección de Dependencias** a través del contenedor `PHP-DI`.

### 1. `Config` 🛠️
* **Función:** Carga los archivos de `config/` y permite leer valores usando notación de puntos (ejemplo: `config->get('database.database')`).
* **Uso:** Inyectar en cualquier servicio que necesite parámetros del archivo `.env` o configuraciones globales.

### 2. `ContainerFactory` 🏗️
* **Función:** Construye y devuelve la instancia del contenedor de dependencias (`Psr\Container\ContainerInterface`).
* **Uso:** Se llama principalmente en `consoles/app.php` para instanciar comandos y servicios con sus dependencias resueltas automáticamente.

### 3. `Database` 🗄️
* **Función:** Administra la conexión `PDO` a SQLite asegurando una única instancia y aplicando el esquema inicial si no existe la tabla.
* **Uso:** Inyectar exclusivamente dentro de las clases `Repository`.

### 4. `LoggerService` 📋
* **Función:** Encapsula **Monolog** para escribir logs en la carpeta de almacenamiento.
* **Uso:** Inyectar en servicios para registrar errores o eventos relevantes (`$logger->info(...)`, `$logger->error(...)`).

### 5. `Scheduler` y `Kernel` ⏱️
* **Función:** `Kernel` define la programación de comandos (frecuencia tipo Cron) y `Scheduler` evalúa y ejecuta las tareas pendientes.
* **Uso:** Modificar `Kernel::schedule()` para añadir nuevos comandos que deban ejecutarse periódicamente.

---

## Guía: Cómo Implementar un Nuevo Dominio en `src/`

Para agregar un nuevo dominio (por ejemplo, `Projects`), sigue la arquitectura por capas presente en `src/Tasks/`:

1. **Entidad (`src/Projects/Entities/Project.php`):** Define la clase que representa el modelo con sus propiedades y tipos.
2. **DTO (`src/Projects/DTOs/CreateProjectData.php`):** Crea un objeto inmutable (`readonly`) para transportar y validar los datos de entrada.
3. **Repositorio (`src/Projects/Repositories/ProjectRepository.php`):** Recibe `Core\Database` en el constructor e implementa las consultas SQL mediante *Prepared Statements* de PDO.
4. **Servicio (`src/Projects/Services/ProjectService.php`):** Encapsula las reglas de negocio e interactúa con el repositorio y servicios compartidos (como `LoggerService` o `TelegramService`).
5. **Comando CLI (`src/Projects/Commands/CreateProjectCommand.php`):** Extiende de `Symfony\Component\Console\Command\Command`, usa la anotación `#[AsCommand]` e inyecta el servicio de negocio.
6. **Registro en CLI:** Registra el nuevo comando en `consoles/app.php` agregándolo al contenedor:
```php
$application->addCommand($container->get(\App\Projects\Commands\CreateProjectCommand::class));
```
