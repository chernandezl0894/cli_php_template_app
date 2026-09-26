# 🧱 Security Group para la Aplicación en ECS
resource "aws_security_group" "ecs_tasks" {
  name        = "${var.app_name}-ecs-tasks-sg"
  description = "Permite acceso a los contenedores de la aplicacion"
  vpc_id      = aws_vpc.main.id

  egress {
    protocol    = "-1"
    from_port   = 0
    to_port     = 0
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# 🔒 Security Group para la Base de Datos RDS
resource "aws_security_group" "db" {
  name        = "${var.app_name}-db-sg"
  description = "Acceso restringido a la base de datos"
  vpc_id      = aws_vpc.main.id

  # 🔑 Regla Clave: SOLO acepta conexiones en el puerto 3306 (MySQL) o 5432 (Postgres)
  # provenientes del Security Group de nuestra App (ecs_tasks)
  ingress {
    from_port       = 3306
    to_port         = 3306
    protocol        = "tcp"
    security_groups = [aws_security_group.ecs_tasks.id]
  }

  egress {
    protocol    = "-1"
    from_port   = 0
    to_port     = 0
    cidr_blocks = ["0.0.0.0/0"]
  }
}
