-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: taller_mecanico
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asignaciones`
--

DROP TABLE IF EXISTS `asignaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `solicitud_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `encargado_id` int(11) NOT NULL,
  `fecha_asignacion` datetime DEFAULT current_timestamp(),
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_finalizacion` datetime DEFAULT NULL,
  `estado` enum('asignada','en_proceso','completada','cancelada') DEFAULT 'asignada',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitud_id` (`solicitud_id`),
  KEY `trabajador_id` (`trabajador_id`),
  KEY `encargado_id` (`encargado_id`),
  CONSTRAINT `asignaciones_ibfk_1` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`),
  CONSTRAINT `asignaciones_ibfk_2` FOREIGN KEY (`trabajador_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `asignaciones_ibfk_3` FOREIGN KEY (`encargado_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones`
--

LOCK TABLES `asignaciones` WRITE;
/*!40000 ALTER TABLE `asignaciones` DISABLE KEYS */;
INSERT INTO `asignaciones` VALUES (1,2,2,1,'2025-12-28 21:57:22','2024-12-20 08:00:00','2025-12-28 22:40:43','completada','Requiere piezas especiales'),(2,3,3,1,'2025-12-28 21:57:22','2024-12-21 09:00:00',NULL,'en_proceso','Diagnóstico completo necesario'),(3,4,4,1,'2025-12-28 21:57:22','2024-12-19 10:00:00',NULL,'completada','Trabajo finalizado satisfactoriamente'),(4,5,5,1,'2025-12-28 21:57:22','2024-12-22 08:30:00',NULL,'asignada','Pendiente de inicio'),(5,7,2,1,'2025-12-28 21:57:22','2024-12-23 11:00:00',NULL,'en_proceso','En diagnóstico'),(6,8,3,1,'2025-12-28 21:57:22','2024-12-24 09:00:00',NULL,'asignada','Programado para mañana'),(7,9,4,1,'2025-12-28 21:57:22','2024-12-18 08:00:00',NULL,'completada','Cliente satisfecho'),(8,2,5,1,'2025-12-28 21:57:22','2024-12-25 10:00:00',NULL,'asignada','Segunda revisión'),(9,3,2,1,'2025-12-28 21:57:22','2025-12-28 22:42:07',NULL,'en_proceso','Seguimiento'),(10,5,3,1,'2025-12-28 21:57:22','2024-12-27 09:00:00',NULL,'asignada','Revisión de alineación'),(11,1,5,1,'2025-12-28 22:24:10',NULL,NULL,'asignada',''),(12,1,3,1,'2025-12-28 22:44:16',NULL,NULL,'asignada','Urgente'),(13,11,4,1,'2025-12-29 01:50:49','2025-12-29 01:53:07','2025-12-29 01:54:03','completada','Con calma'),(14,1,4,1,'2025-12-31 22:13:02',NULL,NULL,'asignada','Trabajo urgente'),(15,1,2,1,'2025-12-31 22:13:42',NULL,NULL,'asignada','Urgente'),(16,12,2,1,'2025-12-31 22:17:18','2025-12-31 22:17:52','2025-12-31 22:18:21','completada','Trabajo');
/*!40000 ALTER TABLE `asignaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes_trabajo`
--

DROP TABLE IF EXISTS `reportes_trabajo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reportes_trabajo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asignacion_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `piezas_utilizadas` text DEFAULT NULL,
  `horas_trabajadas` decimal(5,2) DEFAULT NULL,
  `costo_total` decimal(10,2) DEFAULT NULL,
  `fecha_reporte` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `trabajador_id` (`trabajador_id`),
  CONSTRAINT `reportes_trabajo_ibfk_1` FOREIGN KEY (`asignacion_id`) REFERENCES `asignaciones` (`id`),
  CONSTRAINT `reportes_trabajo_ibfk_2` FOREIGN KEY (`trabajador_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes_trabajo`
--

LOCK TABLES `reportes_trabajo` WRITE;
/*!40000 ALTER TABLE `reportes_trabajo` DISABLE KEYS */;
INSERT INTO `reportes_trabajo` VALUES (1,3,4,'Se realizó revisión completa. Se encontraron desgastes en el sistema de frenos y se reemplazaron las balatas delanteras.','Balatas delanteras x2, Líquido de frenos',4.50,2200.00,'2025-12-28 21:57:22'),(2,7,4,'Reparación completa de suspensión. Se cambiaron amortiguadores y bujes.','Amortiguadores x4, Bujes x8, Grasas especiales',6.00,2800.00,'2025-12-28 21:57:22'),(3,1,2,'Cambio de balatas y discos. Se probó el sistema y funciona correctamente.','Discos de freno x2, Balatas x4',3.00,2100.00,'2025-12-28 21:57:22'),(4,2,3,'Afinación mayor completada. Motor funcionando óptimamente.','Bujías x4, Filtros x3, Aceite sintético',3.50,1600.00,'2025-12-28 21:57:22'),(5,3,4,'Revisión de 50,000 km completada sin problemas mayores.','Filtro de aceite, Aceite 10W-30',2.00,550.00,'2025-12-28 21:57:22'),(6,4,5,'Alineación y balanceo realizados. Vehículo estable.','Pesas de balanceo',1.50,420.00,'2025-12-28 21:57:22'),(7,5,2,'Diagnóstico eléctrico. Se reparó alternador.','Alternador, Cables eléctricos',4.00,1350.00,'2025-12-28 21:57:22'),(8,6,3,'Mantenimiento preventivo completo.','Kit de mantenimiento completo',2.50,280.00,'2025-12-28 21:57:22'),(9,7,4,'Suspensión trasera reparada exitosamente.','Kit de suspensión trasera',5.50,2850.00,'2025-12-28 21:57:22'),(10,1,2,'Segunda revisión de frenos, ajustes finales realizados.','Ninguna pieza adicional',1.00,150.00,'2025-12-28 21:57:22'),(11,1,2,'Se completó la tarea','6',20.00,2200.00,'2025-12-28 22:40:43'),(12,13,4,'Se realizó el trabajo dentro del tiempo','0',20.00,1000.00,'2025-12-29 01:54:03'),(13,16,2,'Trabajo completado','3',18.00,950.00,'2025-12-31 22:18:21');
/*!40000 ALTER TABLE `reportes_trabajo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'encargado','Administrador del taller con acceso completo'),(2,'trabajador','Mecánico que realiza los servicios'),(3,'cliente','Usuario que solicita servicios');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicios`
--

DROP TABLE IF EXISTS `servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_estimado` decimal(10,2) DEFAULT NULL,
  `duracion_estimada` int(11) DEFAULT NULL COMMENT 'Duración en horas',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicios`
--

LOCK TABLES `servicios` WRITE;
/*!40000 ALTER TABLE `servicios` DISABLE KEYS */;
INSERT INTO `servicios` VALUES (1,'Cambio de aceite','Cambio de aceite y filtro de motor',265.00,1,'2025-12-28 21:57:22'),(2,'Afinación mayor','Afinación completa del motor',2000.00,3,'2025-12-28 21:57:22'),(3,'Cambio de frenos','Cambio de balatas y discos de freno',2000.00,2,'2025-12-28 21:57:22'),(4,'Alineación y balanceo','Alineación de ruedas y balanceo',400.00,1,'2025-12-28 21:57:22'),(5,'Revisión general','Diagnóstico completo del vehículo',500.00,2,'2025-12-28 21:57:22'),(6,'Cambio de transmisión','Servicio completo de transmisión',3500.00,4,'2025-12-28 21:57:22'),(7,'Reparación de suspensión','Reparación del sistema de suspensión',2500.00,3,'2025-12-28 21:57:22'),(8,'Aire acondicionado','Recarga y reparación de A/C',800.00,2,'2025-12-28 21:57:22'),(9,'Sistema eléctrico','Diagnóstico y reparación eléctrica',1200.00,2,'2025-12-28 21:57:22'),(10,'Cambio de llantas','Instalación de llantas nuevas',3000.00,1,'2025-12-28 21:57:22'),(11,'Pintado','Pintado de vehículo',1360.00,6,'2025-12-28 22:26:09'),(12,'Calibración','Calibración',350.00,20,'2025-12-31 22:14:52');
/*!40000 ALTER TABLE `servicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes`
--

DROP TABLE IF EXISTS `solicitudes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `tipo_vehiculo_id` int(11) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `marca_vehiculo` varchar(50) DEFAULT NULL,
  `modelo_vehiculo` varchar(50) DEFAULT NULL,
  `año_vehiculo` int(11) DEFAULT NULL,
  `placa_vehiculo` varchar(20) DEFAULT NULL,
  `descripcion_problema` text DEFAULT NULL,
  `estado` enum('pendiente','asignada','en_proceso','completada','cancelada') DEFAULT 'pendiente',
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `tipo_vehiculo_id` (`tipo_vehiculo_id`),
  KEY `servicio_id` (`servicio_id`),
  CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `solicitudes_ibfk_2` FOREIGN KEY (`tipo_vehiculo_id`) REFERENCES `tipos_vehiculo` (`id`),
  CONSTRAINT `solicitudes_ibfk_3` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes`
--

LOCK TABLES `solicitudes` WRITE;
/*!40000 ALTER TABLE `solicitudes` DISABLE KEYS */;
INSERT INTO `solicitudes` VALUES (1,6,1,1,'Toyota','Corolla',2020,'ABC-123','Necesito cambio de aceite regular','asignada','2025-12-28 21:57:22','2025-12-31 22:13:42'),(2,7,2,3,'Honda','CR-V',2019,'XYZ-456','Los frenos hacen ruido al frenar','completada','2025-12-28 21:57:22','2025-12-28 22:40:43'),(3,8,3,2,'Ford','F-150',2021,'DEF-789','El motor pierde potencia','pendiente','2025-12-28 21:57:22','2025-12-29 01:31:06'),(4,9,1,5,'Nissan','Sentra',2018,'GHI-012','Revisión de 50,000 km','completada','2025-12-28 21:57:22','2025-12-28 21:57:22'),(5,10,4,4,'Mazda','Mazda3',2022,'JKL-345','El volante vibra al conducir','pendiente','2025-12-28 21:57:22','2025-12-29 00:18:41'),(6,11,2,8,'Chevrolet','Equinox',2020,'MNO-678','El aire acondicionado no enfría','pendiente','2025-12-28 21:57:22','2025-12-28 21:57:22'),(7,6,1,9,'Toyota','Camry',2019,'PQR-901','Problemas eléctricos intermitentes','en_proceso','2025-12-28 21:57:22','2025-12-28 21:57:22'),(8,7,5,1,'BMW','320i',2021,'STU-234','Mantenimiento preventivo','asignada','2025-12-28 21:57:22','2025-12-28 21:57:22'),(9,8,2,7,'Jeep','Wrangler',2020,'VWX-567','Suspensión dañada','completada','2025-12-28 21:57:22','2025-12-28 21:57:22'),(10,9,1,1,'Hyundai','Elantra',2022,'YZA-890','Cambio de aceite y filtro','pendiente','2025-12-28 21:57:22','2025-12-28 21:57:22'),(11,6,11,2,'Toyota','HV2000',2020,'145-klj','','completada','2025-12-29 01:47:33','2025-12-29 01:54:03'),(12,6,12,12,'Toyota','1990',1990,'asd-456','Servicio de calibracion','completada','2025-12-31 22:16:35','2025-12-31 22:18:21');
/*!40000 ALTER TABLE `solicitudes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_vehiculo`
--

DROP TABLE IF EXISTS `tipos_vehiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_vehiculo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_vehiculo`
--

LOCK TABLES `tipos_vehiculo` WRITE;
/*!40000 ALTER TABLE `tipos_vehiculo` DISABLE KEYS */;
INSERT INTO `tipos_vehiculo` VALUES (1,'Sedán','Vehículo de 4 puertas para uso familiar','2025-12-28 21:57:22'),(2,'SUV','Vehículo deportivo utilitario','2025-12-28 21:57:22'),(3,'Pickup','Camioneta con capacidad de carga','2025-12-28 21:57:22'),(4,'Hatchback','Vehículo compacto de 5 puertas','2025-12-28 21:57:22'),(5,'Coupé','Vehículo deportivo de 2 puertas','2025-12-28 21:57:22'),(6,'Minivan','Vehículo familiar de gran capacidad','2025-12-28 21:57:22'),(7,'Crossover','Vehículo híbrido entre sedán y SUV','2025-12-28 21:57:22'),(8,'Convertible','Vehículo con techo retráctil y deportivo','2025-12-28 21:57:22'),(9,'Motocicleta','Vehículo de dos ruedas','2025-12-28 21:57:22'),(10,'Camión','Vehículo de carga pesada','2025-12-28 21:57:22'),(11,'Minibús','16 personas','2025-12-28 23:58:26'),(12,'Micro','Vehículo grande','2025-12-31 22:15:18');
/*!40000 ALTER TABLE `tipos_vehiculo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol_id` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Carlos Mendoza','encargado@taller.com','$2a$12$agnTnrZEgxPta33SVNyPIuS/GNT931aAATeiJ6EO4OescKrCLzRvO','555-0001',1,'2025-12-28 21:57:22',1),(2,'Juan Jose Perez','juan.perez@taller.com','$2a$12$agnTnrZEgxPta33SVNyPIuS/GNT931aAATeiJ6EO4OescKrCLzRvO','222222',2,'2025-12-28 21:57:22',1),(3,'María González','maria.gonzalez@taller.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','587878',2,'2025-12-28 21:57:22',1),(4,'Pedro Sánchez','pedro.sanchez@taller.com','$2a$12$agnTnrZEgxPta33SVNyPIuS/GNT931aAATeiJ6EO4OescKrCLzRvO','555-0004',2,'2025-12-28 21:57:22',1),(5,'Luis Ramírez','luis.ramirez@taller.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','1447877',2,'2025-12-28 21:57:22',1),(6,'Ana Martínez','ana.martinez@email.com','$2a$12$agnTnrZEgxPta33SVNyPIuS/GNT931aAATeiJ6EO4OescKrCLzRvO','555-1001',3,'2025-12-28 21:57:22',1),(7,'Roberto López','roberto.lopez@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','555-1002',3,'2025-12-28 21:57:22',1),(8,'Carmen Díaz','carmen.diaz@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','555-1003',3,'2025-12-28 21:57:22',1),(9,'Jorge Torres','jorge.torres@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','555-1004',3,'2025-12-28 21:57:22',1),(10,'Laura Fernández','laura.fernandez@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','555-1005',3,'2025-12-28 21:57:22',1),(11,'Miguel Vargas','miguel.vargas@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','555-1006',3,'2025-12-28 21:57:22',1),(12,'jose','jose@gmail.com','$2y$10$pOUpdT8qzWqzaCZ0wUnt8.0IBZmN.bEX5uQJ6HVTPpSMtTbp/WoJi','1646546',3,'2025-12-28 22:03:05',1),(13,'Carlitos Perez','carlos@gmail.com','$2y$10$347DSeJkILIMOE8ZILxBWuk.IoxJ.rS9UXVsDsHIBihhEjnJqNCLm','21321',2,'2025-12-28 22:27:46',1),(14,'Luis','luis@gmail.com','$2y$10$5psKMqqwyi9R.eB4zLq/S.lZhi6iYx9Yv0jBll5.Z4WhjpImiyQ4O','12656',2,'2025-12-28 23:32:30',1),(15,'Pablo Muñoz','pablo@gmail.com','$2y$10$M0w.OlCJGCqVeElI5UIfQulPyYkY6rh3XX8UONqo0aoVgv3VYq.FW','233456',2,'2025-12-28 23:39:32',1),(16,'Julio Lopez','julio@gmail.com','$2y$10$e8fL92xj583qYD8sIFvlwuyNN2N6SMt/RhC8QziLaNj2KlLgZAsjS','489546',2,'2025-12-28 23:51:31',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'taller_mecanico'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-05 23:25:39
