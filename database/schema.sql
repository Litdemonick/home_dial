
CREATE DATABASE IF NOT EXISTS homedial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE homedial;

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
DROP TABLE IF EXISTS `alertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alertas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` int unsigned NOT NULL,
  `sesion_id` int unsigned DEFAULT NULL,
  `tipo_alerta` enum('Retencion_leve','Retencion_severa','Turbidez_peritonitis','Hipoglucemia','Hiperglucemia') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT '0',
  `generada_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_alertas_sesion` (`sesion_id`),
  KEY `idx_alertas_paciente` (`paciente_id`,`leida`,`generada_en` DESC),
  CONSTRAINT `fk_alertas_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_alertas_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesiones_dialisis` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `balance_diario_resumen`;
/*!50001 DROP VIEW IF EXISTS `balance_diario_resumen`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `balance_diario_resumen` AS SELECT 
 1 AS `sesion_id`,
 1 AS `paciente_id`,
 1 AS `fecha_sesion`,
 1 AS `tipo_sistema_dp`,
 1 AS `total_infusion`,
 1 AS `total_drenaje`,
 1 AS `balance_final`,
 1 AS `recambios_turbios`,
 1 AS `estado_balance`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `medicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nefrología',
  `idoneidad` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_medicos_usuario` (`usuario_id`),
  KEY `idx_medicos_busqueda` (`apellido`,`nombre`),
  CONSTRAINT `fk_medicos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pacientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pacientes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int unsigned NOT NULL,
  `medico_id` int unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `sexo` enum('M','F','O') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cedula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` text COLLATE utf8mb4_unicode_ci,
  `tipo_sistema_dp` enum('Baxter','Fresenius Medical Care') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peso_kg` decimal(5,2) DEFAULT NULL,
  `talla_cm` decimal(5,1) DEFAULT NULL,
  `tipo_sangre` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_inicio_dp` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pacientes_cedula` (`cedula`),
  KEY `idx_pacientes_usuario` (`usuario_id`),
  KEY `idx_pacientes_medico` (`medico_id`),
  KEY `idx_pacientes_busqueda` (`apellido`,`nombre`),
  CONSTRAINT `fk_pacientes_medico` FOREIGN KEY (`medico_id`) REFERENCES `medicos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pacientes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recambios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recambios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sesion_id` int unsigned NOT NULL,
  `numero_recambio` tinyint unsigned NOT NULL,
  `concentracion` enum('1.5%','2.5%','7.5%') COLLATE utf8mb4_unicode_ci NOT NULL,
  `infusion_ml` smallint unsigned NOT NULL DEFAULT '2000',
  `drenaje_ml` smallint unsigned NOT NULL,
  `balance_ml` smallint NOT NULL,
  `cualidad` enum('Claro','Turbio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Claro',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_recambio_sesion` (`sesion_id`,`numero_recambio`),
  KEY `idx_recambios_sesion` (`sesion_id`),
  KEY `idx_recambios_cualidad` (`sesion_id`,`cualidad`),
  CONSTRAINT `fk_recambios_sesion` FOREIGN KEY (`sesion_id`) REFERENCES `sesiones_dialisis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ck_drenaje_positivo` CHECK ((`drenaje_ml` >= 0)),
  CONSTRAINT `ck_recambio_numero` CHECK ((`numero_recambio` between 1 and 4))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_recambio_before_insert` BEFORE INSERT ON `recambios` FOR EACH ROW BEGIN
    SET NEW.balance_ml = CAST(NEW.infusion_ml AS SIGNED) - CAST(NEW.drenaje_ml AS SIGNED);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_alerta_balance_after_insert` AFTER INSERT ON `recambios` FOR EACH ROW BEGIN
    DECLARE v_balance_final  SMALLINT;
    DECLARE v_turbios        TINYINT;
    DECLARE v_paciente_id    INT UNSIGNED;
    DECLARE v_total_recambios TINYINT;
    SELECT COUNT(*), s.paciente_id
    INTO   v_total_recambios, v_paciente_id
    FROM   recambios r
    JOIN   sesiones_dialisis s ON s.id = r.sesion_id
    WHERE  r.sesion_id = NEW.sesion_id
    GROUP  BY s.paciente_id;
    IF v_total_recambios = 4 THEN
        SELECT SUM(balance_ml),
               COUNT(CASE WHEN cualidad = 'Turbio' THEN 1 END)
        INTO   v_balance_final, v_turbios
        FROM   recambios
        WHERE  sesion_id = NEW.sesion_id;
        IF v_balance_final > 2000 THEN
            INSERT INTO alertas (paciente_id, sesion_id, tipo_alerta, mensaje)
            VALUES (v_paciente_id, NEW.sesion_id, 'Retencion_severa',
                    CONCAT('ALERTA: Excesiva retención de líquidos. Balance final: ',
                           v_balance_final, ' ml.'));
        ELSEIF v_balance_final BETWEEN 1 AND 2000 THEN
            INSERT INTO alertas (paciente_id, sesion_id, tipo_alerta, mensaje)
            VALUES (v_paciente_id, NEW.sesion_id, 'Retencion_leve',
                    CONCAT('Retención de líquidos considerable. Balance final: ',
                           v_balance_final, ' ml.'));
        END IF;
        IF v_turbios >= 2 THEN
            INSERT INTO alertas (paciente_id, sesion_id, tipo_alerta, mensaje)
            VALUES (v_paciente_id, NEW.sesion_id, 'Turbidez_peritonitis',
                    'ALERTA MÉDICO: Posible riesgo de peritonitis. Dos o más recambios turbios.');
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_recambio_before_update` BEFORE UPDATE ON `recambios` FOR EACH ROW BEGIN
    SET NEW.balance_ml = CAST(NEW.infusion_ml AS SIGNED) - CAST(NEW.drenaje_ml AS SIGNED);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `registros_glucosa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registros_glucosa` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` int unsigned NOT NULL,
  `fecha_medicion` datetime NOT NULL,
  `glucosa_mgdl` smallint unsigned NOT NULL,
  `momento` enum('ayunas','antes_comida','2h_despues') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_glucemico` enum('Hipoglucemia','Normal','Prediabetes','Hiperglucemia') COLLATE utf8mb4_unicode_ci NOT NULL,
  `registrado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_glucosa_paciente_fecha` (`paciente_id`,`fecha_medicion` DESC),
  KEY `idx_glucosa_estado` (`paciente_id`,`estado_glucemico`),
  CONSTRAINT `fk_glucosa_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ck_glucosa_valor` CHECK (((`glucosa_mgdl` > 0) and (`glucosa_mgdl` < 1200)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sesiones_dialisis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones_dialisis` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `paciente_id` int unsigned NOT NULL,
  `fecha_sesion` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `tipo_sistema_dp` enum('Baxter','Fresenius Medical Care') COLLATE utf8mb4_unicode_ci NOT NULL,
  `presion_sistol` smallint unsigned DEFAULT NULL,
  `presion_diast` smallint unsigned DEFAULT NULL,
  `pulso` smallint unsigned DEFAULT NULL,
  `registrado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sesion_dia` (`paciente_id`,`fecha_sesion`),
  KEY `idx_sesiones_fecha` (`paciente_id`,`fecha_sesion` DESC),
  KEY `idx_sesiones_rango` (`fecha_sesion`),
  CONSTRAINT `fk_sesiones_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` tinyint unsigned NOT NULL DEFAULT '2',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_un` (`nombre_usuario`),
  KEY `fk_usuarios_rol` (`rol_id`),
  KEY `idx_usuarios_acceso` (`nombre_usuario`,`activo`),
  CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50001 DROP VIEW IF EXISTS `balance_diario_resumen`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `balance_diario_resumen` AS select `s`.`id` AS `sesion_id`,`s`.`paciente_id` AS `paciente_id`,`s`.`fecha_sesion` AS `fecha_sesion`,`s`.`tipo_sistema_dp` AS `tipo_sistema_dp`,sum(`r`.`infusion_ml`) AS `total_infusion`,sum(`r`.`drenaje_ml`) AS `total_drenaje`,sum(`r`.`balance_ml`) AS `balance_final`,count((case when (`r`.`cualidad` = 'Turbio') then 1 end)) AS `recambios_turbios`,(case when (sum(`r`.`balance_ml`) <= 0) then 'Favorable' when (sum(`r`.`balance_ml`) between 1 and 2000) then 'Retencion_leve' else 'Retencion_severa' end) AS `estado_balance` from (`sesiones_dialisis` `s` join `recambios` `r` on((`r`.`sesion_id` = `s`.`id`))) group by `s`.`id`,`s`.`paciente_id`,`s`.`fecha_sesion`,`s`.`tipo_sistema_dp` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


-- catalogo de roles (no es dato de prueba, hace falta siempre)
INSERT INTO roles (id, nombre) VALUES (1, 'admin'), (2, 'paciente'), (3, 'medico');

-- usuario admin base, sin contraseña todavia (ver database/seed.sql para ponerle una)
INSERT INTO usuarios (nombre_usuario, password_hash, rol_id, activo) VALUES ('admin', '', 1, 1);
