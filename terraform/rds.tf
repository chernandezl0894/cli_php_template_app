# 📐 Grupo de subredes privadas para RDS (exige al menos 2 zonas de disponibilidad)
resource "aws_db_subnet_group" "main" {
  name       = "${var.app_name}-db-subnet-group"
  subnet_ids = [aws_subnet.private_1.id, aws_subnet.private_2.id]

  tags = {
    Name = "${var.app_name}-db-subnet-group"
  }
}

# 🗄️ Instancia de Base de Datos RDS (PostgreSQL/MySQL)
resource "aws_db_instance" "default" {
  allocated_storage      = 20
  max_allocated_storage  = 50
  engine                 = "postgres" # O "mysql" según la DB de producción
  engine_version         = "15"
  instance_class         = "db.t4g.micro" # Opción económica para dev/prod inicial
  db_name                = "app_db"
  username               = "db_user"
  password               = "ChangeMeInProduction123!" # Idealmente gestionado en Secrets Manager
  db_subnet_group_name   = aws_db_subnet_group.main.name
  vpc_security_group_ids = [aws_security_group.db.id]
  skip_final_snapshot    = true

  tags = {
    Name = "${var.app_name}-db"
  }
}
