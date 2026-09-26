# 📈 Grupo de Logs en CloudWatch
resource "aws_cloudwatch_log_group" "ecs" {
  name              = "/ecs/${var.app_name}"
  retention_in_days = 30 # Retener logs por 30 días para optimizar costos

  tags = {
    Name = "${var.app_name}-logs"
  }
}
