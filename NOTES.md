## Docker Commands

- `docker compose build --no-cache php-cli` fuerza la reconstrucción de la imagen.
- `docker compose run --rm php-cli php consoles/app.php <command> <option>` crea un contenedor, ejecuta el script y lo elimina.

## Tasks

- `docker compose run --rm php-cli php consoles/app.php app:task:process-reminders` corre el proceso para enviar los recordatorios por notificacion.
- `docker compose run --rm php-cli php consoles/app.php app:task:list` lista todas las tareas
- `docker compose run --rm php-cli php consoles/app.php app:task:create "<title>" -d "<description>" -r "<remainder>+1 minute"` crea una nueva tarea usando los siguiente argumento.
  - 1. `<title>` titulo de la tarea
  - 2. `<description>` `-d` descripcion de la tarea
  - 3. `<remainder>` `-r` tiempo para el recordatorio
