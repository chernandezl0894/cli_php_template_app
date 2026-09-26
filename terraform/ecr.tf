# 📦 Repositorio ECR para la aplicación PHP
resource "aws_ecr_repository" "app" {
  name                 = var.app_name
  image_tag_mutability = "IMMUTABLE" # 🔒 Evita sobrescribir tags existentes

  image_scanning_configuration {
    scan_on_push = true # 🛡️ Escanea vulnerabilidades al subir la imagen
  }

  tags = {
    Name = "${var.app_name}-ecr"
  }
}

# 🧹 Regla de limpieza para no acumular imágenes antiguas y ahorrar costos
resource "aws_ecr_lifecycle_policy" "app_policy" {
  repository = aws_ecr_repository.app.name

  policy = jsonencode({
    rules = [
      {
        rulePriority = 1
        description  = "Conservar solo las ultimas 5 imagenes"
        selection = {
          tagStatus   = "any"
          countType   = "sinceImagePushed"
          countUnit   = "days"
          countNumber = 30
        }
        action = {
          type = "expire"
        }
      }
    ]
  })
}
