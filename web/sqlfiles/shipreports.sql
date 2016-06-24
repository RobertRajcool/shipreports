-- MySQL dump 10.14  Distrib 5.5.49-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: shipreports
-- ------------------------------------------------------
-- Server version	5.5.49-MariaDB-1ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acl_classes`
--

DROP TABLE IF EXISTS `acl_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_classes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_type` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_69DD750638A36066` (`class_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_classes`
--

LOCK TABLES `acl_classes` WRITE;
/*!40000 ALTER TABLE `acl_classes` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_entries`
--

DROP TABLE IF EXISTS `acl_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_entries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL,
  `object_identity_id` int(10) unsigned DEFAULT NULL,
  `security_identity_id` int(10) unsigned NOT NULL,
  `field_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ace_order` smallint(5) unsigned NOT NULL,
  `mask` int(11) NOT NULL,
  `granting` tinyint(1) NOT NULL,
  `granting_strategy` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `audit_success` tinyint(1) NOT NULL,
  `audit_failure` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_46C8B806EA000B103D9AB4A64DEF17BCE4289BF4` (`class_id`,`object_identity_id`,`field_name`,`ace_order`),
  KEY `IDX_46C8B806EA000B103D9AB4A6DF9183C9` (`class_id`,`object_identity_id`,`security_identity_id`),
  KEY `IDX_46C8B806EA000B10` (`class_id`),
  KEY `IDX_46C8B8063D9AB4A6` (`object_identity_id`),
  KEY `IDX_46C8B806DF9183C9` (`security_identity_id`),
  CONSTRAINT `FK_46C8B8063D9AB4A6` FOREIGN KEY (`object_identity_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_46C8B806DF9183C9` FOREIGN KEY (`security_identity_id`) REFERENCES `acl_security_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_46C8B806EA000B10` FOREIGN KEY (`class_id`) REFERENCES `acl_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_entries`
--

LOCK TABLES `acl_entries` WRITE;
/*!40000 ALTER TABLE `acl_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_object_identities`
--

DROP TABLE IF EXISTS `acl_object_identities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_object_identities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_object_identity_id` int(10) unsigned DEFAULT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `object_identifier` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `entries_inheriting` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9407E5494B12AD6EA000B10` (`object_identifier`,`class_id`),
  KEY `IDX_9407E54977FA751A` (`parent_object_identity_id`),
  CONSTRAINT `FK_9407E54977FA751A` FOREIGN KEY (`parent_object_identity_id`) REFERENCES `acl_object_identities` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identities`
--

LOCK TABLES `acl_object_identities` WRITE;
/*!40000 ALTER TABLE `acl_object_identities` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_object_identities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_object_identity_ancestors`
--

DROP TABLE IF EXISTS `acl_object_identity_ancestors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_object_identity_ancestors` (
  `object_identity_id` int(10) unsigned NOT NULL,
  `ancestor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`object_identity_id`,`ancestor_id`),
  KEY `IDX_825DE2993D9AB4A6` (`object_identity_id`),
  KEY `IDX_825DE299C671CEA1` (`ancestor_id`),
  CONSTRAINT `FK_825DE2993D9AB4A6` FOREIGN KEY (`object_identity_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_825DE299C671CEA1` FOREIGN KEY (`ancestor_id`) REFERENCES `acl_object_identities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_object_identity_ancestors`
--

LOCK TABLES `acl_object_identity_ancestors` WRITE;
/*!40000 ALTER TABLE `acl_object_identity_ancestors` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_object_identity_ancestors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_security_identities`
--

DROP TABLE IF EXISTS `acl_security_identities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_security_identities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `username` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8835EE78772E836AF85E0677` (`identifier`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_security_identities`
--

LOCK TABLES `acl_security_identities` WRITE;
/*!40000 ALTER TABLE `acl_security_identities` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_security_identities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apps_countries`
--

DROP TABLE IF EXISTS `apps_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CountryCode` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `CountryName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apps_countries`
--

LOCK TABLES `apps_countries` WRITE;
/*!40000 ALTER TABLE `apps_countries` DISABLE KEYS */;
INSERT INTO `apps_countries` VALUES (1,'AF','Afghanistan'),(2,'AL','Albania'),(3,'DZ','Algeria'),(4,'DS','American Samoa'),(5,'AD','Andorra'),(6,'AO','Angola'),(7,'AI','Anguilla'),(8,'AQ','Antarctica'),(9,'AG','Antigua and Barbuda'),(10,'AR','Argentina'),(11,'AM','Armenia'),(12,'AW','Aruba'),(13,'AU','Australia'),(14,'AT','Austria'),(15,'AZ','Azerbaijan'),(16,'BS','Bahamas'),(17,'BH','Bahrain'),(18,'BD','Bangladesh'),(19,'BB','Barbados'),(20,'BY','Belarus'),(21,'BE','Belgium'),(22,'BZ','Belize'),(23,'BJ','Benin'),(24,'BM','Bermuda'),(25,'BT','Bhutan'),(26,'BO','Bolivia'),(27,'BA','Bosnia and Herzegovina'),(28,'BW','Botswana'),(29,'BV','Bouvet Island'),(30,'BR','Brazil'),(31,'IO','British Indian Ocean Territory'),(32,'BN','Brunei Darussalam'),(33,'BG','Bulgaria'),(34,'BF','Burkina Faso'),(35,'BI','Burundi'),(36,'KH','Cambodia'),(37,'CM','Cameroon'),(38,'CA','Canada'),(39,'CV','Cape Verde'),(40,'KY','Cayman Islands'),(41,'CF','Central African Republic'),(42,'TD','Chad'),(43,'CL','Chile'),(44,'CN','China'),(45,'CX','Christmas Island'),(46,'CC','Cocos (Keeling) Islands'),(47,'CO','Colombia'),(48,'KM','Comoros'),(49,'CG','Congo'),(50,'CK','Cook Islands'),(51,'CR','Costa Rica'),(52,'HR','Croatia (Hrvatska)'),(53,'CU','Cuba'),(54,'CY','Cyprus'),(55,'CZ','Czech Republic'),(56,'DK','Denmark'),(57,'DJ','Djibouti'),(58,'DM','Dominica'),(59,'DO','Dominican Republic'),(60,'TP','East Timor'),(61,'EC','Ecuador'),(62,'EG','Egypt'),(63,'SV','El Salvador'),(64,'GQ','Equatorial Guinea'),(65,'ER','Eritrea'),(66,'EE','Estonia'),(67,'ET','Ethiopia'),(68,'FK','Falkland Islands (Malvinas)'),(69,'FO','Faroe Islands'),(70,'FJ','Fiji'),(71,'FI','Finland'),(72,'FR','France'),(73,'FX','France, Metropolitan'),(74,'GF','French Guiana'),(75,'PF','French Polynesia'),(76,'TF','French Southern Territories'),(77,'GA','Gabon'),(78,'GM','Gambia'),(79,'GE','Georgia'),(80,'DE','Germany'),(81,'GH','Ghana'),(82,'GI','Gibraltar'),(83,'GK','Guernsey'),(84,'GR','Greece'),(85,'GL','Greenland'),(86,'GD','Grenada'),(87,'GP','Guadeloupe'),(88,'GU','Guam'),(89,'GT','Guatemala'),(90,'GN','Guinea'),(91,'GW','Guinea-Bissau'),(92,'GY','Guyana'),(93,'HT','Haiti'),(94,'HM','Heard and Mc Donald Islands'),(95,'HN','Honduras'),(96,'HK','Hong Kong'),(97,'HU','Hungary'),(98,'IS','Iceland'),(99,'IN','India'),(100,'IM','Isle of Man'),(101,'ID','Indonesia'),(102,'IR','Iran (Islamic Republic of)'),(103,'IQ','Iraq'),(104,'IE','Ireland'),(105,'IL','Israel'),(106,'IT','Italy'),(107,'CI','Ivory Coast'),(108,'JE','Jersey'),(109,'JM','Jamaica'),(110,'JP','Japan'),(111,'JO','Jordan'),(112,'KZ','Kazakhstan'),(113,'KE','Kenya'),(114,'KI','Kiribati'),(115,'KP','Korea, Democratic People\'s Republic of'),(116,'KR','Korea, Republic of'),(117,'XK','Kosovo'),(118,'KW','Kuwait'),(119,'KG','Kyrgyzstan'),(120,'LA','Lao People\'s Democratic Republic'),(121,'LV','Latvia'),(122,'LB','Lebanon'),(123,'LS','Lesotho'),(124,'LR','Liberia'),(125,'LY','Libyan Arab Jamahiriya'),(126,'LI','Liechtenstein'),(127,'LT','Lithuania'),(128,'LU','Luxembourg'),(129,'MO','Macau'),(130,'MK','Macedonia'),(131,'MG','Madagascar'),(132,'MW','Malawi'),(133,'MY','Malaysia'),(134,'MV','Maldives'),(135,'ML','Mali'),(136,'MT','Malta'),(137,'MH','Marshall Islands'),(138,'MQ','Martinique'),(139,'MR','Mauritania'),(140,'MU','Mauritius'),(141,'TY','Mayotte'),(142,'MX','Mexico'),(143,'FM','Micronesia, Federated States of'),(144,'MD','Moldova, Republic of'),(145,'MC','Monaco'),(146,'MN','Mongolia'),(147,'ME','Montenegro'),(148,'MS','Montserrat'),(149,'MA','Morocco'),(150,'MZ','Mozambique'),(151,'MM','Myanmar'),(152,'NA','Namibia'),(153,'NR','Nauru'),(154,'NP','Nepal'),(155,'NL','Netherlands'),(156,'AN','Netherlands Antilles'),(157,'NC','New Caledonia'),(158,'NZ','New Zealand'),(159,'NI','Nicaragua'),(160,'NE','Niger'),(161,'NG','Nigeria'),(162,'NU','Niue'),(163,'NF','Norfolk Island'),(164,'MP','Northern Mariana Islands'),(165,'NO','Norway'),(166,'OM','Oman'),(167,'PK','Pakistan'),(168,'PW','Palau'),(169,'PS','Palestine'),(170,'PA','Panama'),(171,'PG','Papua New Guinea'),(172,'PY','Paraguay'),(173,'PE','Peru'),(174,'PH','Philippines'),(175,'PN','Pitcairn'),(176,'PL','Poland'),(177,'PT','Portugal'),(178,'PR','Puerto Rico'),(179,'QA','Qatar'),(180,'RE','Reunion'),(181,'RO','Romania'),(182,'RU','Russian Federation'),(183,'RW','Rwanda'),(184,'KN','Saint Kitts and Nevis'),(185,'LC','Saint Lucia'),(186,'VC','Saint Vincent and the Grenadines'),(187,'WS','Samoa'),(188,'SM','San Marino'),(189,'ST','Sao Tome and Principe'),(190,'SA','Saudi Arabia'),(191,'SN','Senegal'),(192,'RS','Serbia'),(193,'SC','Seychelles'),(194,'SL','Sierra Leone'),(195,'SG','Singapore'),(196,'SK','Slovakia'),(197,'SI','Slovenia'),(198,'SB','Solomon Islands'),(199,'SO','Somalia'),(200,'ZA','South Africa'),(201,'GS','South Georgia South Sandwich Islands'),(202,'ES','Spain'),(203,'LK','Sri Lanka'),(204,'SH','St. Helena'),(205,'PM','St. Pierre and Miquelon'),(206,'SD','Sudan'),(207,'SR','Suriname'),(208,'SJ','Svalbard and Jan Mayen Islands'),(209,'SZ','Swaziland'),(210,'SE','Sweden'),(211,'CH','Switzerland'),(212,'SY','Syrian Arab Republic'),(213,'TW','Taiwan'),(214,'TJ','Tajikistan'),(215,'TZ','Tanzania, United Republic of'),(216,'TH','Thailand'),(217,'TG','Togo'),(218,'TK','Tokelau'),(219,'TO','Tonga'),(220,'TT','Trinidad and Tobago'),(221,'TN','Tunisia'),(222,'TR','Turkey'),(223,'TM','Turkmenistan'),(224,'TC','Turks and Caicos Islands'),(225,'TV','Tuvalu'),(226,'UG','Uganda'),(227,'UA','Ukraine'),(228,'AE','United Arab Emirates'),(229,'GB','United Kingdom'),(230,'US','United States'),(231,'UM','United States minor outlying islands'),(232,'UY','Uruguay'),(233,'UZ','Uzbekistan'),(234,'VU','Vanuatu'),(235,'VA','Vatican City State'),(236,'VE','Venezuela'),(237,'VN','Vietnam'),(238,'VG','Virgin Islands (British)'),(239,'VI','Virgin Islands (U.S.)'),(240,'WF','Wallis and Futuna Islands'),(241,'EH','Western Sahara'),(242,'YE','Yemen'),(243,'YU','Yugoslavia'),(244,'ZR','Zaire'),(245,'ZM','Zambia'),(246,'ZW','Zimbabwe');
/*!40000 ALTER TABLE `apps_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calculation_rules`
--

DROP TABLE IF EXISTS `calculation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calculation_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Rules` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `RuleConditions` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `RuleActions` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FD574383F10FA648` (`KpiDetailsId`),
  KEY `IDX_FD574383ABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_FD574383ABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `element_details` (`id`),
  CONSTRAINT `FK_FD574383F10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `kpi_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calculation_rules`
--

LOCK TABLES `calculation_rules` WRITE;
/*!40000 ALTER TABLE `calculation_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `calculation_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart`
--

DROP TABLE IF EXISTS `chart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fromdate` date NOT NULL,
  `todate` date NOT NULL,
  `kpiname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E5562A2AF10FA648` (`KpiDetailsId`),
  CONSTRAINT `FK_E5562A2AF10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `kpi_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart`
--

LOCK TABLES `chart` WRITE;
/*!40000 ALTER TABLE `chart` DISABLE KEYS */;
/*!40000 ALTER TABLE `chart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_details`
--

DROP TABLE IF EXISTS `company_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `CompanyName` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `AdminName` varchar(75) COLLATE utf8_unicode_ci NOT NULL,
  `EmailId` varchar(75) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5274115D3115C37D` (`CompanyName`),
  UNIQUE KEY `UNIQ_5274115DF7E01C19` (`EmailId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_details`
--

LOCK TABLES `company_details` WRITE;
/*!40000 ALTER TABLE `company_details` DISABLE KEYS */;
INSERT INTO `company_details` VALUES (1,'Pioneer','admin','admin@pioneer.com');
/*!40000 ALTER TABLE `company_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_users`
--

DROP TABLE IF EXISTS `company_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` int(11) DEFAULT NULL,
  `UserName` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `EmailId` varchar(75) COLLATE utf8_unicode_ci NOT NULL,
  `companyName` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5372078CF7E01C19` (`EmailId`),
  KEY `IDX_5372078CB7894CAA` (`companyName`),
  KEY `IDX_5372078C57698A6A` (`role`),
  CONSTRAINT `FK_5372078C57698A6A` FOREIGN KEY (`role`) REFERENCES `user_role` (`id`),
  CONSTRAINT `FK_5372078CB7894CAA` FOREIGN KEY (`companyName`) REFERENCES `company_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_users`
--

LOCK TABLES `company_users` WRITE;
/*!40000 ALTER TABLE `company_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `element_details`
--

DROP TABLE IF EXISTS `element_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `element_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ElementName` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CellName` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `CellDetails` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ActivatedDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `Weightage` int(11) NOT NULL,
  `Rules` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_85CD4BDEF10FA648` (`KpiDetailsId`),
  CONSTRAINT `FK_85CD4BDEF10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `element_details`
--

LOCK TABLES `element_details` WRITE;
/*!40000 ALTER TABLE `element_details` DISABLE KEYS */;
INSERT INTO `element_details` VALUES (1,'Claims from Charterer on Speed  and Fuel Consumption','',' ',' ','2016-06-21','2017-12-01',100,NULL,1),(2,'Cargo Hold & Log Readiness','',' ',' ','2016-06-21','2017-12-01',40,NULL,16),(3,'Hatch Cover water tightness','',' ',' ','2016-06-21','2017-12-01',20,NULL,16),(4,'Cargo Crane Operation ','',' ',' ','2016-06-21','2017-12-01',40,NULL,16),(5,'Variance between Tank Soundings & Log Book Figs','',' ',' ','2016-06-21','2017-12-01',100,NULL,31),(6,'Reports to Owner ','',' ',' ','2016-06-21','2017-12-01',50,NULL,46),(7,'Logistics ','',' ',' ','2016-06-21','2017-12-01',50,NULL,46),(8,'SCOC','',' ',' ','2016-06-21','2017-12-01',25,NULL,61),(9,'Lub Oil Bulk Stem ','',' ',' ','2016-06-21','2017-12-01',40,NULL,61),(10,'Late Order Charge & Minimum Delivery Charge ','For Paint & Lubs','',NULL,'2016-06-30','2017-12-31',15,NULL,61),(11,'Bulk Stem Paint','',' ',' ','2016-06-21','2017-12-01',20,NULL,61),(12,'Percentage of Jobs based on PMS','',' ',' ','2016-06-21','2017-12-01',100,NULL,76),(13,'Unplanned Technical Downtime - YTD','',' ',' ','2016-06-21','2017-12-01',80,NULL,91),(14,'Planned Technical Downtime per Vessel - YTD','',' ',' ','2016-06-21','2017-12-01',20,NULL,91),(15,'LTIF for Fleet','',' ',' ','2016-06-21','2017-12-01',30,NULL,106),(16,'TRCF for Fleet','',' ',' ','2016-06-21','2017-12-01',30,NULL,106),(17,'Major Incidents','',' ',' ','2016-06-21','2017-12-01',40,NULL,106),(18,'Total  Operating Expense','',' ',' ','2016-06-21','2017-12-01',30,NULL,121),(19,'Technical Expense','',' ',' ','2016-06-21','2017-12-01',25,NULL,121),(20,'Manning Expense','',' ',' ','2016-06-21','2017-12-01',40,NULL,121),(21,'Voyage Related Expense/OAE','',' ',' ','2016-06-21','2017-12-01',5,NULL,121),(22,'Retention Rate for Indian Crew','',' ',' ','2016-06-21','2017-12-01',20,NULL,136),(23,'Ability to implement specific & cost effective training for deck officers, engineers and crew. ','',' ',' ','2016-06-21','2017-12-01',35,NULL,136),(24,'Medical sign off','',' ',' ','2016-06-21','2017-12-01',25,NULL,136),(25,'Retention rate for top four','',' ',' ','2016-06-21','2017-12-01',20,NULL,136),(26,'Total Docking Cost vs Budget','',' ',' ','2016-06-21','2017-12-01',35,NULL,151),(27,'Total Time in Dock vs Budget','',' ',' ','2016-06-21','2017-12-01',30,NULL,151),(28,'Final Docking Report Timeliness','',' ',' ','2016-06-21','2017-12-01',20,NULL,151),(29,'Vessel Load Ready Timeliness','',' ',' ','2016-06-21','2017-12-01',15,NULL,151),(30,'No. Of PSC Deficiencies Per Inspection','',' ',' ','2016-06-21','2017-12-01',15,NULL,166),(31,'PSC Detention Rate','',' ',' ','2016-06-21','2017-12-01',15,NULL,166),(32,'No. Of PSC Inspections without Deficiencies Per Inspection','',' ',' ','2016-06-21','2017-12-01',15,NULL,166),(33,'DOC (office) Audit  Major NCR ','',' ',' ','2016-06-21','2017-12-01',15,NULL,166),(34,'ISM/ISPS/MLC (shipboard) Audit Major NCR ','',' ',' ','2016-06-21','2017-12-01',20,NULL,166),(35,'Right Ship Rating ','',' ',' ','2016-06-21','2017-12-01',20,NULL,166);
/*!40000 ALTER TABLE `element_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `element_rules`
--

DROP TABLE IF EXISTS `element_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `element_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Rules` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4DB8EDA5ABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_4DB8EDA5ABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `element_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `element_rules`
--

LOCK TABLES `element_rules` WRITE;
/*!40000 ALTER TABLE `element_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `element_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_group`
--

DROP TABLE IF EXISTS `email_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyid` int(11) DEFAULT NULL,
  `groupname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `groupstatus` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F08D054AB104C381` (`companyid`),
  CONSTRAINT `FK_F08D054AB104C381` FOREIGN KEY (`companyid`) REFERENCES `company_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_group`
--

LOCK TABLES `email_group` WRITE;
/*!40000 ALTER TABLE `email_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_users`
--

DROP TABLE IF EXISTS `email_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) DEFAULT NULL,
  `useremailid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_89CEE4667805AC12` (`groupid`),
  CONSTRAINT `FK_89CEE4667805AC12` FOREIGN KEY (`groupid`) REFERENCES `email_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_users`
--

LOCK TABLES `email_users` WRITE;
/*!40000 ALTER TABLE `email_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `excel_file_details`
--

DROP TABLE IF EXISTS `excel_file_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `excel_file_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_of_month` date NOT NULL,
  `userid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `excel_file_details`
--

LOCK TABLES `excel_file_details` WRITE;
/*!40000 ALTER TABLE `excel_file_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `excel_file_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `excel_file_details_ranking`
--

DROP TABLE IF EXISTS `excel_file_details_ranking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `excel_file_details_ranking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data_of_month` date NOT NULL,
  `userid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `company_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `excel_file_details_ranking`
--

LOCK TABLES `excel_file_details_ranking` WRITE;
/*!40000 ALTER TABLE `excel_file_details_ranking` DISABLE KEYS */;
/*!40000 ALTER TABLE `excel_file_details_ranking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fos_user`
--

DROP TABLE IF EXISTS `fos_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fos_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyid` int(11) DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fullname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_957A647992FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_957A6479A0D96FBF` (`email_canonical`),
  KEY `IDX_957A6479B104C381` (`companyid`),
  CONSTRAINT `FK_957A6479B104C381` FOREIGN KEY (`companyid`) REFERENCES `company_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fos_user`
--

LOCK TABLES `fos_user` WRITE;
/*!40000 ALTER TABLE `fos_user` DISABLE KEYS */;
INSERT INTO `fos_user` VALUES (1,NULL,'admin','admin','hariprakashsambath@gmail.com','hariprakashsambath@gmail.com',1,'c19bzzst20owc84k8c8sw8c8c8c8kos','$2y$13$c19bzzst20owc84k8c8swuNTcktp1EW.9NTEt3niTY3COnIPWXwIS','2016-06-24 16:11:34',0,0,NULL,NULL,NULL,'a:1:{i:0;s:10:\"ROLE_ADMIN\";}',0,NULL,NULL,NULL),(9,1,'Siva','siva','sivakumar@pioneermarine.com','sivakumar@pioneermarine.com',1,'25ysejjgy0sgss0cwcckgc080k0skk4','$2y$13$25ysejjgy0sgss0cwcckgOjy2zcTyON5hTI6SHQOPiNAAIgNSQyP.','2016-06-21 15:44:22',0,0,NULL,NULL,NULL,'a:1:{i:0;s:12:\"ROLE_MANAGER\";}',0,NULL,'9840098113','Siva'),(10,1,'V-ships','v-ships','v-ships@gmail.com','v-ships@gmail.com',1,'7nuukxhl6doo8k4k4kc40s44w08wgk4','$2y$13$7nuukxhl6doo8k4k4kc40eyDz1HVQukJIejBv9yLwItE8PeT7APui','2016-06-24 16:10:54',0,0,NULL,NULL,NULL,'a:1:{i:0;s:22:\"ROLE_KPI_INFO_PROVIDER\";}',0,NULL,'9790584839','V-ships'),(11,1,'Manager','manager','manager@gmail.com','manager@gmail.com',1,'82gl2rxnbpk4ko8wcgso4ow8cc0004w','$2y$13$82gl2rxnbpk4ko8wcgso4eS/tezv6I/VXFJzZmfP3NQz8Q6BECJrS','2016-06-22 18:00:11',0,0,NULL,NULL,NULL,'a:1:{i:0;s:12:\"ROLE_MANAGER\";}',0,NULL,'9890786859','kpimanager');
/*!40000 ALTER TABLE `fos_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_details`
--

DROP TABLE IF EXISTS `kpi_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `KpiName` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ActiveDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `CellName` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `CellDetails` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Weightage` int(11) NOT NULL,
  `ShipDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7DB727695041247E` (`ShipDetailsId`),
  CONSTRAINT `FK_7DB727695041247E` FOREIGN KEY (`ShipDetailsId`) REFERENCES `ship_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_details`
--

LOCK TABLES `kpi_details` WRITE;
/*!40000 ALTER TABLE `kpi_details` DISABLE KEYS */;
INSERT INTO `kpi_details` VALUES (1,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,1),(2,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,2),(3,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,3),(4,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,4),(5,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,5),(6,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,6),(7,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,7),(8,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,8),(9,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,9),(10,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,10),(11,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,11),(12,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,12),(13,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,13),(14,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,14),(15,'Vessel Performance','','2016-06-30','2017-12-31','',NULL,10,15),(16,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,1),(17,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,2),(18,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,3),(19,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,4),(20,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,5),(21,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,6),(22,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,7),(23,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,8),(24,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,9),(25,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,10),(26,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,11),(27,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,12),(28,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,13),(29,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,14),(30,'Vessel turn around in Port  ','','2016-06-21','2017-12-01',' ',' ',5,15),(31,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,1),(32,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,2),(33,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,3),(34,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,4),(35,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,5),(36,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,6),(37,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,7),(38,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,8),(39,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,9),(40,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,10),(41,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,11),(42,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,12),(43,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,13),(44,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,14),(45,'Bunker Management','','2016-06-21','2017-12-01',' ',' ',10,15),(46,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,1),(47,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,2),(48,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,3),(49,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,4),(50,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,5),(51,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,6),(52,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,7),(53,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,8),(54,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,9),(55,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,10),(56,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,11),(57,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,12),(58,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,13),(59,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,14),(60,'On Time Delivery','','2016-06-21','2017-12-01',' ',' ',5,15),(61,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,1),(62,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,2),(63,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,3),(64,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,4),(65,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,5),(66,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,6),(67,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,7),(68,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,8),(69,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,9),(70,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,10),(71,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,11),(72,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,12),(73,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,13),(74,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,14),(75,'Lub Oil and Paint Management','','2016-06-21','2017-12-01',' ',' ',5,15),(76,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,1),(77,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,2),(78,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,3),(79,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,4),(80,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,5),(81,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,6),(82,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,7),(83,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,8),(84,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,9),(85,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,10),(86,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,11),(87,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,12),(88,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,13),(89,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,14),(90,'M&R Management','','2016-06-21','2017-12-01',' ',' ',10,15),(91,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,1),(92,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,2),(93,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,3),(94,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,4),(95,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,5),(96,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,6),(97,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,7),(98,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,8),(99,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,9),(100,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,10),(101,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,11),(102,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,12),(103,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,13),(104,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,14),(105,'Technical Off-hire','','2016-06-21','2017-12-01',' ',' ',10,15),(106,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,1),(107,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,2),(108,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,3),(109,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,4),(110,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,5),(111,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,6),(112,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,7),(113,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,8),(114,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,9),(115,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,10),(116,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,11),(117,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,12),(118,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,13),(119,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,14),(120,'Fleet Safety','','2016-06-21','2017-12-01',' ',' ',10,15),(121,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,1),(122,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,2),(123,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,3),(124,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,4),(125,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,5),(126,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,6),(127,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,7),(128,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,8),(129,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,9),(130,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,10),(131,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,11),(132,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,12),(133,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,13),(134,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,14),(135,'Budget performance ','','2016-06-21','2017-12-01',' ',' ',10,15),(136,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,1),(137,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,2),(138,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,3),(139,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,4),(140,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,5),(141,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,6),(142,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,7),(143,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,8),(144,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,9),(145,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,10),(146,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,11),(147,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,12),(148,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,13),(149,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,14),(150,'Crewing','','2016-06-21','2017-12-01',' ',' ',5,15),(151,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,1),(152,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,2),(153,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,3),(154,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,4),(155,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,5),(156,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,6),(157,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,7),(158,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,8),(159,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,9),(160,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,10),(161,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,11),(162,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,12),(163,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,13),(164,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,14),(165,'Dry Dock Management','','2016-06-21','2017-12-01',' ',' ',10,15),(166,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,1),(167,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,2),(168,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,3),(169,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,4),(170,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,5),(171,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,6),(172,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,7),(173,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,8),(174,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,9),(175,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,10),(176,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,11),(177,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,12),(178,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,13),(179,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,14),(180,'Inspections & Audit','','2016-06-21','2017-12-01',' ',' ',10,15);
/*!40000 ALTER TABLE `kpi_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kpi_rules`
--

DROP TABLE IF EXISTS `kpi_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kpi_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Rules` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_38214772F10FA648` (`KpiDetailsId`),
  CONSTRAINT `FK_38214772F10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kpi_rules`
--

LOCK TABLES `kpi_rules` WRITE;
/*!40000 ALTER TABLE `kpi_rules` DISABLE KEYS */;
INSERT INTO `kpi_rules` VALUES (1,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',1),(2,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.58\"}]}}',1),(3,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.58\"}]}}',1),(4,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\".\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',16),(5,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.58\"}]}}',16),(6,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.58\"}]}}',16),(7,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',31),(8,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.58\"}]}}',31),(9,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.58\"}]}}',31),(10,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',46),(11,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',46),(12,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',46),(13,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',61),(14,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',61),(15,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',61),(16,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',76),(17,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',76),(18,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',76),(19,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',91),(20,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',91),(21,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',91),(22,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',106),(23,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',106),(24,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',106),(25,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',121),(26,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',121),(27,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',121),(28,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',136),(29,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',136),(30,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',136),(31,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',151),(32,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',151),(33,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',151),(34,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2.42\"}]}}',166),(35,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"1.48\"}]}}',166),(36,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"1.48\"}]}}',166);
/*!40000 ALTER TABLE `kpi_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking__lookup_data`
--

DROP TABLE IF EXISTS `ranking__lookup_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking__lookup_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `elementdata` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `elementcolor` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `monthdetail` date NOT NULL,
  `ShipDetailsId` int(11) DEFAULT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BBB3C63D5041247E` (`ShipDetailsId`),
  KEY `IDX_BBB3C63DF10FA648` (`KpiDetailsId`),
  KEY `IDX_BBB3C63DABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_BBB3C63D5041247E` FOREIGN KEY (`ShipDetailsId`) REFERENCES `ship_details` (`id`),
  CONSTRAINT `FK_BBB3C63DABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `ranking_element_details` (`id`),
  CONSTRAINT `FK_BBB3C63DF10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `ranking_kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking__lookup_data`
--

LOCK TABLES `ranking__lookup_data` WRITE;
/*!40000 ALTER TABLE `ranking__lookup_data` DISABLE KEYS */;
INSERT INTO `ranking__lookup_data` VALUES (1,'100','Green','2016-02-29',1,1,1),(2,'0','Red','2016-02-29',1,16,2),(3,'0','Red','2016-02-29',1,16,3),(4,'20','Green','2016-02-29',1,16,4),(5,'50','Green','2016-02-29',1,31,5),(6,'50','Green','2016-02-29',1,31,6),(7,'14','Green','2016-02-29',1,46,7),(8,'14','Green','2016-02-29',1,46,8),(9,'14','Green','2016-02-29',1,46,9),(10,'14','Green','2016-02-29',1,46,10),(11,'10','Green','2016-02-29',1,46,11),(12,'10','Green','2016-02-29',1,46,12),(13,'6','Green','2016-02-29',1,46,13),(14,'6','Green','2016-02-29',1,46,14),(15,'0','Red','2016-02-29',1,46,15),(16,'6','Green','2016-02-29',1,46,16),(17,'40','Green','2016-02-29',1,61,17),(18,'20','Green','2016-02-29',1,61,18),(19,'40','Green','2016-02-29',1,61,19),(20,'50','Green','2016-02-29',1,76,20),(21,'50','Green','2016-02-29',1,76,21),(22,'30','Green','2016-02-29',1,91,22),(23,'70','Green','2016-02-29',1,91,23),(24,'100','Green','2016-02-29',1,121,24),(25,'100','Green','2016-01-31',1,1,1),(26,'40','Green','2016-01-31',1,16,2),(27,'0','Red','2016-01-31',1,16,3),(28,'20','Green','2016-01-31',1,16,4),(29,'50','Green','2016-01-31',1,31,5),(30,'50','Green','2016-01-31',1,31,6),(31,'14','Green','2016-01-31',1,46,7),(32,'14','Green','2016-01-31',1,46,8),(33,'14','Green','2016-01-31',1,46,9),(34,'14','Green','2016-01-31',1,46,10),(35,'10','Green','2016-01-31',1,46,11),(36,'10','Green','2016-01-31',1,46,12),(37,'6','Green','2016-01-31',1,46,13),(38,'6','Green','2016-01-31',1,46,14),(39,'6','Green','2016-01-31',1,46,15),(40,'6','Green','2016-01-31',1,46,16),(41,'40','Green','2016-01-31',1,61,17),(42,'20','Green','2016-01-31',1,61,18),(43,'40','Green','2016-01-31',1,61,19),(44,'50','Green','2016-01-31',1,76,20),(45,'50','Green','2016-01-31',1,76,21),(46,'30','Green','2016-01-31',1,91,22),(47,'70','Green','2016-01-31',1,91,23),(48,'100','Green','2016-01-31',1,121,24);
/*!40000 ALTER TABLE `ranking__lookup_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking__lookup_status`
--

DROP TABLE IF EXISTS `ranking__lookup_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking__lookup_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipid` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `dataofmonth` datetime NOT NULL,
  `datetime` datetime NOT NULL,
  `userid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_98BC7A579094736` (`shipid`),
  CONSTRAINT `FK_98BC7A579094736` FOREIGN KEY (`shipid`) REFERENCES `ship_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking__lookup_status`
--

LOCK TABLES `ranking__lookup_status` WRITE;
/*!40000 ALTER TABLE `ranking__lookup_status` DISABLE KEYS */;
INSERT INTO `ranking__lookup_status` VALUES (1,1,4,'2016-01-31 00:00:00','2016-06-22 18:04:04','10'),(2,1,4,'2016-02-29 00:00:00','2016-06-22 18:01:37','10');
/*!40000 ALTER TABLE `ranking__lookup_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_element_details`
--

DROP TABLE IF EXISTS `ranking_element_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_element_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ElementName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `CellName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CellDetails` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ActiveDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `Weightage` int(11) NOT NULL,
  `Rules` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6DF1AF6BF10FA648` (`KpiDetailsId`),
  CONSTRAINT `FK_6DF1AF6BF10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `ranking_kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_element_details`
--

LOCK TABLES `ranking_element_details` WRITE;
/*!40000 ALTER TABLE `ranking_element_details` DISABLE KEYS */;
INSERT INTO `ranking_element_details` VALUES (1,'No. of incidents per month','','',NULL,'2016-06-30','2017-12-31',100,NULL,1),(2,'Manning Expense YTD','',' ',' ','2016-06-22','2017-12-01',40,NULL,16),(3,'Technical Expense YTD','',' ',' ','2016-06-22','2017-12-01',40,NULL,16),(4,'Management Expense YTD','',' ',' ','2016-06-22','2017-12-01',20,NULL,16),(5,'CP Performance','',' ',' ','2016-06-22','2017-12-01',50,NULL,31),(6,'Engine Performance','',' ',' ','2016-06-22','2017-12-01',50,NULL,31),(7,'PSC Detention','',' ',' ','2016-06-22','2017-12-01',14,NULL,46),(8,'ISPS NC','',' ',' ','2016-06-22','2017-12-01',14,NULL,46),(9,'ISM Major NC','',' ',' ','2016-06-22','2017-12-01',14,NULL,46),(10,'MLC Deficiency','',' ',' ','2016-06-22','2017-12-01',14,NULL,46),(11,'Flag State Deficiency','',' ',' ','2016-06-22','2017-12-01',10,NULL,46),(12,'PSC Deficiency/Inspect','',' ',' ','2016-06-22','2017-11-01',10,NULL,46),(13,'COC','',' ',' ','2016-06-22','2017-12-01',6,NULL,46),(14,'Right ship','',' ',' ','2016-06-22','2017-12-01',6,NULL,46),(15,'PSC Nil Deficiency/Inspect','',' ',' ','2016-06-22','2017-11-01',6,NULL,46),(16,'DOC Deficiency','','',NULL,'2016-06-30','2017-12-31',6,NULL,46),(17,'Hold and cargo readiness','',' ',' ','2016-06-22','2017-12-01',40,NULL,61),(18,'Hatch cover tightness','',' ',' ','2016-06-22','2017-12-01',20,NULL,61),(19,'Crane stoppages','',' ',' ','2016-06-22','2017-12-01',40,NULL,61),(20,'LTIF','',' ',' ','2016-06-22','2017-12-01',50,NULL,76),(21,'TRCF','',' ',' ','2016-06-22','2017-12-01',50,NULL,76),(22,'Planned','',' ',' ','2016-06-22','2017-12-01',30,NULL,91),(23,'Unplanned','',' ',' ','2016-06-22','2017-12-01',70,NULL,91),(24,'Variation','',' ',' ','2016-06-22','2017-12-01',100,NULL,121);
/*!40000 ALTER TABLE `ranking_element_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_element_rules`
--

DROP TABLE IF EXISTS `ranking_element_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_element_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Rules` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C37F74B6ABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_C37F74B6ABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `ranking_element_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_element_rules`
--

LOCK TABLES `ranking_element_rules` WRITE;
/*!40000 ALTER TABLE `ranking_element_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ranking_element_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_kpi_details`
--

DROP TABLE IF EXISTS `ranking_kpi_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_kpi_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `KpiName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ActiveDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `CellName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CellDetails` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Weightage` int(11) NOT NULL,
  `ShipDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D7E51E605041247E` (`ShipDetailsId`),
  CONSTRAINT `FK_D7E51E605041247E` FOREIGN KEY (`ShipDetailsId`) REFERENCES `ship_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_kpi_details`
--

LOCK TABLES `ranking_kpi_details` WRITE;
/*!40000 ALTER TABLE `ranking_kpi_details` DISABLE KEYS */;
INSERT INTO `ranking_kpi_details` VALUES (1,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,1),(2,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,2),(3,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,3),(4,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,4),(5,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,5),(6,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,6),(7,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,7),(8,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,8),(9,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,9),(10,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,10),(11,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,11),(12,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,12),(13,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,13),(14,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,14),(15,'Major Incidents','','2016-06-22','2017-12-01',' ',' ',14,15),(16,'Opex','','2016-06-22','2017-12-01',' ',' ',14,1),(17,'Opex','','2016-06-22','2017-12-01',' ',' ',14,2),(18,'Opex','','2016-06-22','2017-12-01',' ',' ',14,3),(19,'Opex','','2016-06-22','2017-12-01',' ',' ',14,4),(20,'Opex','','2016-06-22','2017-12-01',' ',' ',14,5),(21,'Opex','','2016-06-22','2017-12-01',' ',' ',14,6),(22,'Opex','','2016-06-22','2017-12-01',' ',' ',14,7),(23,'Opex','','2016-06-22','2017-12-01',' ',' ',14,8),(24,'Opex','','2016-06-22','2017-12-01',' ',' ',14,9),(25,'Opex','','2016-06-22','2017-12-01',' ',' ',14,10),(26,'Opex','','2016-06-22','2017-12-01',' ',' ',14,11),(27,'Opex','','2016-06-22','2017-12-01',' ',' ',14,12),(28,'Opex','','2016-06-22','2017-12-01',' ',' ',14,13),(29,'Opex','','2016-06-22','2017-12-01',' ',' ',14,14),(30,'Opex','','2016-06-22','2017-12-01',' ',' ',14,15),(31,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,1),(32,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,2),(33,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,3),(34,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,4),(35,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,5),(36,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,6),(37,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,7),(38,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,8),(39,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,9),(40,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,10),(41,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,11),(42,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,12),(43,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,13),(44,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,14),(45,'Vessel Performance','','2016-06-22','2017-12-01',' ',' ',14,15),(46,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,1),(47,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,2),(48,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,3),(49,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,4),(50,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,5),(51,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,6),(52,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,7),(53,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,8),(54,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,9),(55,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,10),(56,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,11),(57,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,12),(58,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,13),(59,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,14),(60,'Third party inspection','','2016-06-30','2017-12-31','',NULL,12,15),(61,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,1),(62,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,2),(63,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,3),(64,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,4),(65,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,5),(66,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,6),(67,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,7),(68,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,8),(69,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,9),(70,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,10),(71,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,11),(72,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,12),(73,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,13),(74,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,14),(75,'Cargo claims','','2016-06-22','2017-12-01',' ',' ',12,15),(76,'Safety','','2016-06-22','2017-12-01',' ',' ',10,1),(77,'Safety','','2016-06-22','2017-12-01',' ',' ',10,2),(78,'Safety','','2016-06-22','2017-12-01',' ',' ',10,3),(79,'Safety','','2016-06-22','2017-12-01',' ',' ',10,4),(80,'Safety','','2016-06-22','2017-12-01',' ',' ',10,5),(81,'Safety','','2016-06-22','2017-12-01',' ',' ',10,6),(82,'Safety','','2016-06-22','2017-12-01',' ',' ',10,7),(83,'Safety','','2016-06-22','2017-12-01',' ',' ',10,8),(84,'Safety','','2016-06-22','2017-12-01',' ',' ',10,9),(85,'Safety','','2016-06-22','2017-12-01',' ',' ',10,10),(86,'Safety','','2016-06-22','2017-12-01',' ',' ',10,11),(87,'Safety','','2016-06-22','2017-12-01',' ',' ',10,12),(88,'Safety','','2016-06-22','2017-12-01',' ',' ',10,13),(89,'Safety','','2016-06-22','2017-12-01',' ',' ',10,14),(90,'Safety','','2016-06-22','2017-12-01',' ',' ',10,15),(91,'Down time','','2016-06-22','2017-12-01',' ',' ',10,1),(92,'Down time','','2016-06-22','2017-12-01',' ',' ',10,2),(93,'Down time','','2016-06-22','2017-12-01',' ',' ',10,3),(94,'Down time','','2016-06-22','2017-12-01',' ',' ',10,4),(95,'Down time','','2016-06-22','2017-12-01',' ',' ',10,5),(96,'Down time','','2016-06-22','2017-12-01',' ',' ',10,6),(97,'Down time','','2016-06-22','2017-12-01',' ',' ',10,7),(98,'Down time','','2016-06-22','2017-12-01',' ',' ',10,8),(99,'Down time','','2016-06-22','2017-12-01',' ',' ',10,9),(100,'Down time','','2016-06-22','2017-12-01',' ',' ',10,10),(101,'Down time','','2016-06-22','2017-12-01',' ',' ',10,11),(102,'Down time','','2016-06-22','2017-12-01',' ',' ',10,12),(103,'Down time','','2016-06-22','2017-12-01',' ',' ',10,13),(104,'Down time','','2016-06-22','2017-12-01',' ',' ',10,14),(105,'Down time','','2016-06-22','2017-12-01',' ',' ',10,15),(106,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,1),(107,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,2),(108,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,3),(109,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,4),(110,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,5),(111,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,6),(112,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,7),(113,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,8),(114,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,9),(115,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,10),(116,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,11),(117,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,12),(118,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,13),(119,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,14),(120,'Vessel age','','2016-06-22','2017-12-01',' ',' ',7,15),(121,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,1),(122,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,2),(123,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,3),(124,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,4),(125,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,5),(126,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,6),(127,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,7),(128,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,8),(129,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,9),(130,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,10),(131,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,11),(132,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,12),(133,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,13),(134,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,14),(135,'Bunker','','2016-06-22','2017-12-01',' ',' ',7,15);
/*!40000 ALTER TABLE `ranking_kpi_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_kpi_rules`
--

DROP TABLE IF EXISTS `ranking_kpi_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_kpi_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Rules` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_253B384FF10FA648` (`KpiDetailsId`),
  CONSTRAINT `FK_253B384FF10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `ranking_kpi_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_kpi_rules`
--

LOCK TABLES `ranking_kpi_rules` WRITE;
/*!40000 ALTER TABLE `ranking_kpi_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `ranking_kpi_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_monthly_data`
--

DROP TABLE IF EXISTS `ranking_monthly_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_monthly_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monthdetail` date NOT NULL,
  `status` int(11) NOT NULL,
  `ShipDetailsId` int(11) DEFAULT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_40D230035041247E` (`ShipDetailsId`),
  KEY `IDX_40D23003F10FA648` (`KpiDetailsId`),
  KEY `IDX_40D23003ABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_40D230035041247E` FOREIGN KEY (`ShipDetailsId`) REFERENCES `ship_details` (`id`),
  CONSTRAINT `FK_40D23003ABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `ranking_element_details` (`id`),
  CONSTRAINT `FK_40D23003F10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `ranking_kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_monthly_data`
--

LOCK TABLES `ranking_monthly_data` WRITE;
/*!40000 ALTER TABLE `ranking_monthly_data` DISABLE KEYS */;
INSERT INTO `ranking_monthly_data` VALUES (1,'0','2016-01-31',3,1,1,1),(2,'5','2016-01-31',3,1,16,2),(3,'-65','2016-01-31',3,1,16,3),(4,'99','2016-01-31',3,1,16,4),(5,'0','2016-01-31',3,1,31,5),(6,'10','2016-01-31',3,1,31,6),(7,'0','2016-01-31',3,1,46,7),(8,'0','2016-01-31',3,1,46,8),(9,'00','2016-01-31',3,1,46,9),(10,'0','2016-01-31',3,1,46,10),(11,'0','2016-01-31',3,1,46,11),(12,'0','2016-01-31',3,1,46,12),(13,'0','2016-01-31',3,1,46,13),(14,'4','2016-01-31',3,1,46,14),(15,'100','2016-01-31',3,1,46,15),(16,'0','2016-01-31',3,1,46,16),(17,'0','2016-01-31',3,1,61,17),(18,'0','2016-01-31',3,1,61,18),(19,'0','2016-01-31',3,1,61,19),(20,'0','2016-01-31',3,1,76,20),(21,'0','2016-01-31',3,1,76,21),(22,'0','2016-01-31',3,1,91,22),(23,'3','2016-01-31',3,1,91,23),(24,'0','2016-01-31',3,1,121,24),(25,'0','2016-02-29',3,1,1,1),(26,'-9','2016-02-29',3,1,16,2),(27,'-47','2016-02-29',3,1,16,3),(28,'-6','2016-02-29',3,1,16,4),(29,'0','2016-02-29',3,1,31,5),(30,'10','2016-02-29',3,1,31,6),(31,'0','2016-02-29',3,1,46,7),(32,'0','2016-02-29',3,1,46,8),(33,'0','2016-02-29',3,1,46,9),(34,'0','2016-02-29',3,1,46,10),(35,'0','2016-02-29',3,1,46,11),(36,'0','2016-02-29',3,1,46,12),(37,'0','2016-02-29',3,1,46,13),(38,'4','2016-02-29',3,1,46,14),(39,'0','2016-02-29',3,1,46,15),(40,'0','2016-02-29',3,1,46,16),(41,'0','2016-02-29',3,1,61,17),(42,'0','2016-02-29',3,1,61,18),(43,'0','2016-02-29',3,1,61,19),(44,'0','2016-02-29',3,1,76,20),(45,'0','2016-02-29',3,1,76,21),(46,'0','2016-02-29',3,1,91,22),(47,'0','2016-02-29',3,1,91,23),(48,'0','2016-02-29',3,1,121,24);
/*!40000 ALTER TABLE `ranking_monthly_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_rules`
--

DROP TABLE IF EXISTS `ranking_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Rules` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_845ECE8DF10FA648` (`KpiDetailsId`),
  KEY `IDX_845ECE8DABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_845ECE8DABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `ranking_element_details` (`id`),
  CONSTRAINT `FK_845ECE8DF10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `ranking_kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_rules`
--

LOCK TABLES `ranking_rules` WRITE;
/*!40000 ALTER TABLE `ranking_rules` DISABLE KEYS */;
INSERT INTO `ranking_rules` VALUES (1,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',1,1),(2,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',1,1),(3,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',1,1),(4,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"0\"}]}}',16,2),(5,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-5\"}]}}',16,2),(6,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-5\"}]}}',16,2),(7,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"0\"}]}}',16,3),(8,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-5\"}]}}',16,3),(9,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-5\"}]}}',16,3),(10,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"0\"}]}}',16,4),(11,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-5\"}]}}',16,4),(12,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-5\"}]}}',16,4),(13,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"2\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-2\"}]}}',31,5),(14,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"3\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-3\"}]}}',31,5),(15,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"3\"},{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-3\"}]}}',31,5),(16,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"10\"}]}}',31,6),(17,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"5\"}]}}',31,6),(18,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',31,6),(19,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',46,7),(20,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',46,7),(21,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',46,7),(22,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',46,8),(23,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',46,8),(24,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',46,8),(25,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',46,9),(26,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',46,9),(27,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',46,9),(28,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',46,10),(29,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',46,10),(30,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',46,10),(31,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',46,11),(32,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"2\"}]}}',46,11),(33,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"2\"}]}}',46,11),(34,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',46,12),(35,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0.8\"}]}}',46,12),(36,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"0.8\"}]}}',46,12),(37,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',46,13),(38,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',46,13),(39,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',46,13),(40,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"4\"}]}}',46,14),(41,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"2\"}]}}',46,14),(42,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"2\"}]}}',46,14),(43,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"60\"}]}}',46,15),(44,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"55\"}]}}',46,15),(45,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"55\"}]}}',46,15),(46,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',46,16),(47,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',46,16),(48,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',46,16),(49,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',61,17),(50,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"1\"}]}}',61,17),(51,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"2\"}]}}',61,17),(52,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',61,18),(53,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"1\"}]}}',61,18),(54,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"2\"}]}}',61,18),(55,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',61,19),(56,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"1\"}]}}',61,19),(57,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"2\"}]}}',61,19),(58,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"0.2\"}]}}',76,20),(59,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"0.25\"}]}}',76,20),(60,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"0.25\"}]}}',76,20),(61,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"0.2\"}]}}',76,21),(62,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"0.25\"}]}}',76,21),(63,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"0.25\"}]}}',76,21),(64,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"12\"}]}}',91,22),(65,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"12\"},{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"24\"}]}}',91,22),(66,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"24\"}]}}',91,22),(67,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"12\"}]}}',91,23),(68,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"12\"},{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"24\"}]}}',91,23),(69,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"24\"}]}}',91,23),(70,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-1\"}]}}',121,24),(71,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-2\"},{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"2\"}]}}',121,24),(72,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"2\"},{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-2\"}]}}',121,24);
/*!40000 ALTER TABLE `ranking_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reading_kpi_values`
--

DROP TABLE IF EXISTS `reading_kpi_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reading_kpi_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `monthdetail` date NOT NULL,
  `status` int(11) NOT NULL,
  `ShipDetailsId` int(11) DEFAULT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5A6D4EA95041247E` (`ShipDetailsId`),
  KEY `IDX_5A6D4EA9F10FA648` (`KpiDetailsId`),
  KEY `IDX_5A6D4EA9ABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_5A6D4EA95041247E` FOREIGN KEY (`ShipDetailsId`) REFERENCES `ship_details` (`id`),
  CONSTRAINT `FK_5A6D4EA9ABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `element_details` (`id`),
  CONSTRAINT `FK_5A6D4EA9F10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2626 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reading_kpi_values`
--

LOCK TABLES `reading_kpi_values` WRITE;
/*!40000 ALTER TABLE `reading_kpi_values` DISABLE KEYS */;
INSERT INTO `reading_kpi_values` VALUES (1,'0','2016-01-31',3,1,1,1),(2,'0','2016-01-31',3,1,16,2),(3,'0','2016-01-31',3,1,16,3),(4,'0','2016-01-31',3,1,16,4),(5,'0.03','2016-01-31',3,1,31,5),(6,'11','2016-01-31',3,1,46,6),(7,'0','2016-01-31',3,1,46,7),(8,'1.73','2016-01-31',3,1,61,8),(9,'33','2016-01-31',3,1,61,9),(10,'0','2016-01-31',3,1,61,10),(11,'0','2016-01-31',3,1,61,11),(12,'17.36','2016-01-31',3,1,76,12),(13,'3','2016-01-31',3,1,91,13),(14,'0','2016-01-31',3,1,91,14),(15,'4.15','2016-01-31',3,1,106,15),(16,'16.6','2016-01-31',3,1,106,16),(17,'500','2016-01-31',3,1,106,17),(18,'-13','2016-01-31',3,1,121,18),(19,'-66','2016-01-31',3,1,121,19),(20,'5','2016-01-31',3,1,121,20),(21,'99','2016-01-31',3,1,121,21),(22,'90','2016-01-31',3,1,136,22),(23,'0','2016-01-31',3,1,136,23),(24,'0','2016-01-31',3,1,136,24),(25,'69','2016-01-31',3,1,136,25),(26,'0','2016-01-31',3,1,151,26),(27,'0','2016-01-31',3,1,151,27),(28,'0','2016-01-31',3,1,151,28),(29,'0','2016-01-31',3,1,151,29),(30,'0','2016-01-31',3,1,166,30),(31,'0','2016-01-31',3,1,166,31),(32,'75','2016-01-31',3,1,166,32),(33,'0','2016-01-31',3,1,166,33),(34,'0','2016-01-31',3,1,166,34),(35,'4','2016-01-31',3,1,166,35),(36,'0','2016-02-29',3,1,1,1),(37,'0','2016-02-29',3,1,16,2),(38,'0','2016-02-29',3,1,16,3),(39,'0','2016-02-29',3,1,16,4),(40,'-3.1','2016-02-29',3,1,31,5),(41,'11','2016-02-29',3,1,46,6),(42,'0','2016-02-29',3,1,46,7),(43,'1.73','2016-02-29',3,1,61,8),(44,'25','2016-02-29',3,1,61,9),(45,'0','2016-02-29',3,1,61,10),(46,'0','2016-02-29',3,1,61,11),(47,'3.38','2016-02-29',3,1,76,12),(48,'3','2016-02-29',3,1,91,13),(49,'0','2016-02-29',3,1,91,14),(50,'4.26','2016-02-29',3,1,106,15),(51,'10.65','2016-02-29',3,1,106,16),(52,'0','2016-02-29',3,1,106,17),(53,'-17','2016-02-29',3,1,121,18),(54,'-48','2016-02-29',3,1,121,19),(55,'-9','2016-02-29',3,1,121,20),(56,'-11','2016-02-29',3,1,121,21),(57,'90','2016-02-29',3,1,136,22),(58,'0','2016-02-29',3,1,136,23),(59,'0','2016-02-29',3,1,136,24),(60,'69','2016-02-29',3,1,136,25),(61,'0','2016-02-29',3,1,151,26),(62,'0','2016-02-29',3,1,151,27),(63,'0','2016-02-29',3,1,151,28),(64,'0','2016-02-29',3,1,151,29),(65,'0','2016-02-29',3,1,166,30),(66,'0','2016-02-29',3,1,166,31),(67,'75','2016-02-29',3,1,166,32),(68,'0','2016-02-29',3,1,166,33),(69,'0','2016-02-29',3,1,166,34),(70,'4','2016-02-29',3,1,166,35),(71,'0','2016-01-31',3,2,1,1),(72,'0','2016-01-31',3,2,16,2),(73,'0','2016-01-31',3,2,16,3),(74,'0','2016-01-31',3,2,16,4),(75,'1','2016-01-31',3,2,31,5),(76,'11','2016-01-31',3,2,46,6),(77,'0','2016-01-31',3,2,46,7),(78,'1.5','2016-01-31',3,2,61,8),(79,'33','2016-01-31',3,2,61,9),(80,'0','2016-01-31',3,2,61,10),(81,'0','2016-01-31',3,2,61,11),(82,'6','2016-01-31',3,2,76,12),(83,'3','2016-01-31',3,2,91,13),(84,'0','2016-01-31',3,2,91,14),(85,'4.15','2016-01-31',3,2,106,15),(86,'16.6','2016-01-31',3,2,106,16),(87,'0','2016-01-31',3,2,106,17),(88,'9','2016-01-31',3,2,121,18),(89,'47','2016-01-31',3,2,121,19),(90,'-3','2016-01-31',3,2,121,20),(91,'29','2016-01-31',3,2,121,21),(92,'95','2016-01-31',3,2,136,22),(93,'0','2016-01-31',3,2,136,23),(94,'0','2016-01-31',3,2,136,24),(95,'65','2016-01-31',3,2,136,25),(96,'0','2016-01-31',3,2,151,26),(97,'0','2016-01-31',3,2,151,27),(98,'0','2016-01-31',3,2,151,28),(99,'0','2016-01-31',3,2,151,29),(100,'0','2016-01-31',3,2,166,30),(101,'0','2016-01-31',3,2,166,31),(102,'75','2016-01-31',3,2,166,32),(103,'0','2016-01-31',3,2,166,33),(104,'0','2016-01-31',3,2,166,34),(105,'5','2016-01-31',3,2,166,35),(106,'0','2016-02-29',3,2,1,1),(107,'0','2016-02-29',3,2,16,2),(108,'0','2016-02-29',3,2,16,3),(109,'0','2016-02-29',3,2,16,4),(110,'4','2016-02-29',3,2,31,5),(111,'11','2016-02-29',3,2,46,6),(112,'0','2016-02-29',3,2,46,7),(113,'1.5','2016-02-29',3,2,61,8),(114,'25','2016-02-29',3,2,61,9),(115,'0','2016-02-29',3,2,61,10),(116,'0','2016-02-29',3,2,61,11),(117,'3','2016-02-29',3,2,76,12),(118,'3','2016-02-29',3,2,91,13),(119,'0','2016-02-29',3,2,91,14),(120,'4.26','2016-02-29',3,2,106,15),(121,'10.65','2016-02-29',3,2,106,16),(122,'220','2016-02-29',3,2,106,17),(123,'-6','2016-02-29',3,2,121,18),(124,'36','2016-02-29',3,2,121,19),(125,'-21','2016-02-29',3,2,121,20),(126,'26','2016-02-29',3,2,121,21),(127,'95','2016-02-29',3,2,136,22),(128,'0','2016-02-29',3,2,136,23),(129,'2','2016-02-29',3,2,136,24),(130,'69','2016-02-29',3,2,136,25),(131,'0','2016-02-29',3,2,151,26),(132,'0','2016-02-29',3,2,151,27),(133,'0','2016-02-29',3,2,151,28),(134,'0','2016-02-29',3,2,151,29),(135,'0','2016-02-29',3,2,166,30),(136,'0','2016-02-29',3,2,166,31),(137,'75','2016-02-29',3,2,166,32),(138,'0','2016-02-29',3,2,166,33),(139,'0','2016-02-29',3,2,166,34),(140,'5','2016-02-29',3,2,166,35),(141,'0','2016-01-31',3,3,1,1),(142,'0','2016-01-31',3,3,16,2),(143,'0','2016-01-31',3,3,16,3),(144,'0','2016-01-31',3,3,16,4),(145,'0','2016-01-31',3,3,31,5),(146,'11','2016-01-31',3,3,46,6),(147,'0','2016-01-31',3,3,46,7),(148,'1.46','2016-01-31',3,3,61,8),(149,'33','2016-01-31',3,3,61,9),(150,'0','2016-01-31',3,3,61,10),(151,'0','2016-01-31',3,3,61,11),(152,'2','2016-01-31',3,3,76,12),(153,'3','2016-01-31',3,3,91,13),(154,'0','2016-01-31',3,3,91,14),(155,'4.15','2016-01-31',3,3,106,15),(156,'16.6','2016-01-31',3,3,106,16),(157,'0','2016-01-31',3,3,106,17),(158,'1','2016-01-31',3,3,121,18),(159,'4','2016-01-31',3,3,121,19),(160,'8','2016-01-31',3,3,121,20),(161,'74','2016-01-31',3,3,121,21),(162,'90','2016-01-31',3,3,136,22),(163,'0','2016-01-31',3,3,136,23),(164,'0','2016-01-31',3,3,136,24),(165,'69','2016-01-31',3,3,136,25),(166,'0','2016-01-31',3,3,151,26),(167,'0','2016-01-31',3,3,151,27),(168,'0','2016-01-31',3,3,151,28),(169,'0','2016-01-31',3,3,151,29),(170,'0','2016-01-31',3,3,166,30),(171,'0','2016-01-31',3,3,166,31),(172,'75','2016-01-31',3,3,166,32),(173,'0','2016-01-31',3,3,166,33),(174,'0','2016-01-31',3,3,166,34),(175,'5','2016-01-31',3,3,166,35),(176,'0','2016-02-29',3,3,1,1),(177,'0','2016-02-29',3,3,16,2),(178,'0','2016-02-29',3,3,16,3),(179,'0','2016-02-29',3,3,16,4),(180,'0','2016-02-29',3,3,31,5),(181,'11','2016-02-29',3,3,46,6),(182,'0','2016-02-29',3,3,46,7),(183,'1.46','2016-02-29',3,3,61,8),(184,'25','2016-02-29',3,3,61,9),(185,'0','2016-02-29',3,3,61,10),(186,'0','2016-02-29',3,3,61,11),(187,'1','2016-02-29',3,3,76,12),(188,'3','2016-02-29',3,3,91,13),(189,'0','2016-02-29',3,3,91,14),(190,'4.26','2016-02-29',3,3,106,15),(191,'10.65','2016-02-29',3,3,106,16),(192,'10000','2016-02-29',3,3,106,17),(193,'-4','2016-02-29',3,3,121,18),(194,'-5','2016-02-29',3,3,121,19),(195,'-5','2016-02-29',3,3,121,20),(196,'60','2016-02-29',3,3,121,21),(197,'90','2016-02-29',3,3,136,22),(198,'0','2016-02-29',3,3,136,23),(199,'0','2016-02-29',3,3,136,24),(200,'69','2016-02-29',3,3,136,25),(201,'0','2016-02-29',3,3,151,26),(202,'0','2016-02-29',3,3,151,27),(203,'0','2016-02-29',3,3,151,28),(204,'0','2016-02-29',3,3,151,29),(205,'4','2016-02-29',3,3,166,30),(206,'0','2016-02-29',3,3,166,31),(207,'75','2016-02-29',3,3,166,32),(208,'0','2016-02-29',3,3,166,33),(209,'0','2016-02-29',3,3,166,34),(210,'5','2016-02-29',3,3,166,35),(211,'0','2016-01-31',3,4,1,1),(212,'2','2016-01-31',3,4,16,2),(213,'0','2016-01-31',3,4,16,3),(214,'0','2016-01-31',3,4,16,4),(215,'-1','2016-01-31',3,4,31,5),(216,'11','2016-01-31',3,4,46,6),(217,'0','2016-01-31',3,4,46,7),(218,'1.5','2016-01-31',3,4,61,8),(219,'33','2016-01-31',3,4,61,9),(220,'0','2016-01-31',3,4,61,10),(221,'0','2016-01-31',3,4,61,11),(222,'11','2016-01-31',3,4,76,12),(223,'3','2016-01-31',3,4,91,13),(224,'0','2016-01-31',3,4,91,14),(225,'4.15','2016-01-31',3,4,106,15),(226,'16.6','2016-01-31',3,4,106,16),(227,'209','2016-01-31',3,4,106,17),(228,'1','2016-01-31',3,4,121,18),(229,'47','2016-01-31',3,4,121,19),(230,'-2','2016-01-31',3,4,121,20),(231,'-54','2016-01-31',3,4,121,21),(232,'95','2016-01-31',3,4,136,22),(233,'0','2016-01-31',3,4,136,23),(234,'0','2016-01-31',3,4,136,24),(235,'69','2016-01-31',3,4,136,25),(236,'0','2016-01-31',3,4,151,26),(237,'0','2016-01-31',3,4,151,27),(238,'0','2016-01-31',3,4,151,28),(239,'0','2016-01-31',3,4,151,29),(240,'0','2016-01-31',3,4,166,30),(241,'0','2016-01-31',3,4,166,31),(242,'75','2016-01-31',3,4,166,32),(243,'0','2016-01-31',3,4,166,33),(244,'0','2016-01-31',3,4,166,34),(245,'4','2016-01-31',3,4,166,35),(246,'0','2016-02-29',3,4,1,1),(247,'0','2016-02-29',3,4,16,2),(248,'0','2016-02-29',3,4,16,3),(249,'0','2016-02-29',3,4,16,4),(250,'.18','2016-02-29',3,4,31,5),(251,'11','2016-02-29',3,4,46,6),(252,'0','2016-02-29',3,4,46,7),(253,'1.5','2016-02-29',3,4,61,8),(254,'25','2016-02-29',3,4,61,9),(255,'0','2016-02-29',3,4,61,10),(256,'0','2016-02-29',3,4,61,11),(257,'12','2016-02-29',3,4,76,12),(258,'3','2016-02-29',3,4,91,13),(259,'0','2016-02-29',3,4,91,14),(260,'4.26','2016-02-29',3,4,106,15),(261,'10.65','2016-02-29',3,4,106,16),(262,'0','2016-02-29',3,4,106,17),(263,'-11','2016-02-29',3,4,121,18),(264,'4','2016-02-29',3,4,121,19),(265,'17','2016-02-29',3,4,121,20),(266,'-33','2016-02-29',3,4,121,21),(267,'95','2016-02-29',3,4,136,22),(268,'0','2016-02-29',3,4,136,23),(269,'0','2016-02-29',3,4,136,24),(270,'69','2016-02-29',3,4,136,25),(271,'0','2016-02-29',3,4,151,26),(272,'0','2016-02-29',3,4,151,27),(273,'0','2016-02-29',3,4,151,28),(274,'0','2016-02-29',3,4,151,29),(275,'0','2016-02-29',3,4,166,30),(276,'0','2016-02-29',3,4,166,31),(277,'75','2016-02-29',3,4,166,32),(278,'0','2016-02-29',3,4,166,33),(279,'0','2016-02-29',3,4,166,34),(280,'4','2016-02-29',3,4,166,35),(281,'0','2016-01-31',3,5,1,1),(282,'0','2016-01-31',3,5,16,2),(283,'0','2016-01-31',3,5,16,3),(284,'0','2016-01-31',3,5,16,4),(285,'0','2016-01-31',3,5,31,5),(286,'11','2016-01-31',3,5,46,6),(287,'0','2016-01-31',3,5,46,7),(288,'1.26','2016-01-31',3,5,61,8),(289,'33','2016-01-31',3,5,61,9),(290,'1','2016-01-31',3,5,61,10),(291,'0','2016-01-31',3,5,61,11),(292,'0','2016-01-31',3,5,76,12),(293,'3','2016-01-31',3,5,91,13),(294,'0','2016-01-31',3,5,91,14),(295,'4.15','2016-01-31',3,5,106,15),(296,'16.6','2016-01-31',3,5,106,16),(297,'0','2016-01-31',3,5,106,17),(298,'1','2016-01-31',3,5,121,18),(299,'13','2016-01-31',3,5,121,19),(300,'-2','2016-01-31',3,5,121,20),(301,'59','2016-01-31',3,5,121,21),(302,'95','2016-01-31',3,5,136,22),(303,'0','2016-01-31',3,5,136,23),(304,'0','2016-01-31',3,5,136,24),(305,'69','2016-01-31',3,5,136,25),(306,'0','2016-01-31',3,5,151,26),(307,'0','2016-01-31',3,5,151,27),(308,'0','2016-01-31',3,5,151,28),(309,'0','2016-01-31',3,5,151,29),(310,'0','2016-01-31',3,5,166,30),(311,'0','2016-01-31',3,5,166,31),(312,'75','2016-01-31',3,5,166,32),(313,'0','2016-01-31',3,5,166,33),(314,'0','2016-01-31',3,5,166,34),(315,'4','2016-01-31',3,5,166,35),(316,'0','2016-02-29',3,5,1,1),(317,'0','2016-02-29',3,5,16,2),(318,'0','2016-02-29',3,5,16,3),(319,'0','2016-02-29',3,5,16,4),(320,'1.45','2016-02-29',3,5,31,5),(321,'11','2016-02-29',3,5,46,6),(322,'0','2016-02-29',3,5,46,7),(323,'1.26','2016-02-29',3,5,61,8),(324,'25','2016-02-29',3,5,61,9),(325,'1','2016-02-29',3,5,61,10),(326,'0','2016-02-29',3,5,61,11),(327,'2','2016-02-29',3,5,76,12),(328,'3','2016-02-29',3,5,91,13),(329,'0','2016-02-29',3,5,91,14),(330,'4.26','2016-02-29',3,5,106,15),(331,'10.65','2016-02-29',3,5,106,16),(332,'0','2016-02-29',3,5,106,17),(333,'-7','2016-02-29',3,5,121,18),(334,'5','2016-02-29',3,5,121,19),(335,'-12','2016-02-29',3,5,121,20),(336,'23','2016-02-29',3,5,121,21),(337,'95','2016-02-29',3,5,136,22),(338,'0','2016-02-29',3,5,136,23),(339,'0','2016-02-29',3,5,136,24),(340,'69','2016-02-29',3,5,136,25),(341,'0','2016-02-29',3,5,151,26),(342,'0','2016-02-29',3,5,151,27),(343,'0','2016-02-29',3,5,151,28),(344,'0','2016-02-29',3,5,151,29),(345,'0','2016-02-29',3,5,166,30),(346,'0','2016-02-29',3,5,166,31),(347,'75','2016-02-29',3,5,166,32),(348,'0','2016-02-29',3,5,166,33),(349,'0','2016-02-29',3,5,166,34),(350,'5','2016-02-29',3,5,166,35),(351,'0','2016-02-29',3,14,1,1),(352,'0','2016-02-29',3,14,16,2),(353,'0','2016-02-29',3,14,16,3),(354,'0','2016-02-29',3,14,16,4),(355,'0.07','2016-02-29',3,14,31,5),(356,'11','2016-02-29',3,14,46,6),(357,'0','2016-02-29',3,14,46,7),(358,'1.1','2016-02-29',3,14,61,8),(359,'25','2016-02-29',3,14,61,9),(360,'0','2016-02-29',3,14,61,10),(361,'0','2016-02-29',3,14,61,11),(362,'0.61','2016-02-29',3,14,76,12),(363,'3','2016-02-29',3,14,91,13),(364,'0','2016-02-29',3,14,91,14),(365,'4.26','2016-02-29',3,14,106,15),(366,'10.65','2016-02-29',3,14,106,16),(367,'0','2016-02-29',3,14,106,17),(368,'-22','2016-02-29',3,14,121,18),(369,'-67','2016-02-29',3,14,121,19),(370,'-12','2016-02-29',3,14,121,20),(371,'-81','2016-02-29',3,14,121,21),(372,'83','2016-02-29',3,14,136,22),(373,'1','2016-02-29',3,14,136,23),(374,'1','2016-02-29',3,14,136,24),(375,'69','2016-02-29',3,14,136,25),(376,'0','2016-02-29',3,14,151,26),(377,'0','2016-02-29',3,14,151,27),(378,'0','2016-02-29',3,14,151,28),(379,'0','2016-02-29',3,14,151,29),(380,'0','2016-02-29',3,14,166,30),(381,'0','2016-02-29',3,14,166,31),(382,'75','2016-02-29',3,14,166,32),(383,'0','2016-02-29',3,14,166,33),(384,'0','2016-02-29',3,14,166,34),(385,'5','2016-02-29',3,14,166,35),(386,'0','2016-01-31',3,14,1,1),(387,'0','2016-01-31',3,14,16,2),(388,'0','2016-01-31',3,14,16,3),(389,'0','2016-01-31',3,14,16,4),(390,'0','2016-01-31',3,14,31,5),(391,'11','2016-01-31',3,14,46,6),(392,'0','2016-01-31',3,14,46,7),(393,'1.1','2016-01-31',3,14,61,8),(394,'33','2016-01-31',3,14,61,9),(395,'0','2016-01-31',3,14,61,10),(396,'0','2016-01-31',3,14,61,11),(397,'1','2016-01-31',3,14,76,12),(398,'3','2016-01-31',3,14,91,13),(399,'0','2016-01-31',3,14,91,14),(400,'4.15','2016-01-31',3,14,106,15),(401,'16.6','2016-01-31',3,14,106,16),(402,'0','2016-01-31',3,14,106,17),(403,'-27','2016-01-31',3,14,121,18),(404,'-158','2016-01-31',3,14,121,19),(405,'4','2016-01-31',3,14,121,20),(406,'--179','2016-01-31',3,14,121,21),(407,'83','2016-01-31',3,14,136,22),(408,'1','2016-01-31',3,14,136,23),(409,'0','2016-01-31',3,14,136,24),(410,'69','2016-01-31',3,14,136,25),(411,'0','2016-01-31',3,14,151,26),(412,'0','2016-01-31',3,14,151,27),(413,'0','2016-01-31',3,14,151,28),(414,'0','2016-01-31',3,14,151,29),(415,'0','2016-01-31',3,14,166,30),(416,'0','2016-01-31',3,14,166,31),(417,'75','2016-01-31',3,14,166,32),(418,'0','2016-01-31',3,14,166,33),(419,'0','2016-01-31',3,14,166,34),(420,'5','2016-01-31',3,14,166,35),(421,'0','2016-01-31',3,6,1,1),(422,'0','2016-01-31',3,6,16,2),(423,'0','2016-01-31',3,6,16,3),(424,'0','2016-01-31',3,6,16,4),(425,'-0.25','2016-01-31',3,6,31,5),(426,'11','2016-01-31',3,6,46,6),(427,'0','2016-01-31',3,6,46,7),(428,'1.48','2016-01-31',3,6,61,8),(429,'33','2016-01-31',3,6,61,9),(430,'0','2016-01-31',3,6,61,10),(431,'0','2016-01-31',3,6,61,11),(432,'27.37','2016-01-31',3,6,76,12),(433,'3','2016-01-31',3,6,91,13),(434,'0','2016-01-31',3,6,91,14),(435,'4.15','2016-01-31',3,6,106,15),(436,'16.6','2016-01-31',3,6,106,16),(437,'0','2016-01-31',3,6,106,17),(438,'8','2016-01-31',3,6,121,18),(439,'2','2016-01-31',3,6,121,19),(440,'12','2016-01-31',3,6,121,20),(441,'76','2016-01-31',3,6,121,21),(442,'82','2016-01-31',3,6,136,22),(443,'0','2016-01-31',3,6,136,23),(444,'0','2016-01-31',3,6,136,24),(445,'69','2016-01-31',3,6,136,25),(446,'0','2016-01-31',3,6,151,26),(447,'0','2016-01-31',3,6,151,27),(448,'0','2016-01-31',3,6,151,28),(449,'0','2016-01-31',3,6,151,29),(450,'0','2016-01-31',3,6,166,30),(451,'0','2016-01-31',3,6,166,31),(452,'75','2016-01-31',3,6,166,32),(453,'0','2016-01-31',3,6,166,33),(454,'0','2016-01-31',3,6,166,34),(455,'5','2016-01-31',3,6,166,35),(456,'0','2016-02-29',3,6,1,1),(457,'0','2016-02-29',3,6,16,2),(458,'0','2016-02-29',3,6,16,3),(459,'0','2016-02-29',3,6,16,4),(460,'-0.71','2016-02-29',3,6,31,5),(461,'11','2016-02-29',3,6,46,6),(462,'0','2016-02-29',3,6,46,7),(463,'1.48','2016-02-29',3,6,61,8),(464,'25','2016-02-29',3,6,61,9),(465,'0','2016-02-29',3,6,61,10),(466,'0','2016-02-29',3,6,61,11),(467,'3.6','2016-02-29',3,6,76,12),(468,'3','2016-02-29',3,6,91,13),(469,'0','2016-02-29',3,6,91,14),(470,'4.26','2016-02-29',3,6,106,15),(471,'10.65','2016-02-29',3,6,106,16),(472,'0','2016-02-29',3,6,106,17),(473,'-8','2016-02-29',3,6,121,18),(474,'6','2016-02-29',3,6,121,19),(475,'-11','2016-02-29',3,6,121,20),(476,'74','2016-02-29',3,6,121,21),(477,'85','2016-02-29',3,6,136,22),(478,'0','2016-02-29',3,6,136,23),(479,'0','2016-02-29',3,6,136,24),(480,'69','2016-02-29',3,6,136,25),(481,'0','2016-02-29',3,6,151,26),(482,'0','2016-02-29',3,6,151,27),(483,'0','2016-02-29',3,6,151,28),(484,'0','2016-02-29',3,6,151,29),(485,'0','2016-02-29',3,6,166,30),(486,'0','2016-02-29',3,6,166,31),(487,'75','2016-02-29',3,6,166,32),(488,'0','2016-02-29',3,6,166,33),(489,'0','2016-02-29',3,6,166,34),(490,'5','2016-02-29',3,6,166,35),(491,'0','2016-01-31',3,7,1,1),(492,'0','2016-01-31',3,7,16,2),(493,'0','2016-01-31',3,7,16,3),(494,'2','2016-01-31',3,7,16,4),(495,'0','2016-01-31',3,7,31,5),(496,'11','2016-01-31',3,7,46,6),(497,'0','2016-01-31',3,7,46,7),(498,'1.46','2016-01-31',3,7,61,8),(499,'33','2016-01-31',3,7,61,9),(500,'0','2016-01-31',3,7,61,10),(501,'0','2016-01-31',3,7,61,11),(502,'8.6','2016-01-31',3,7,76,12),(503,'3','2016-01-31',3,7,91,13),(504,'0','2016-01-31',3,7,91,14),(505,'4.15','2016-01-31',3,7,106,15),(506,'16.6','2016-01-31',3,7,106,16),(507,'6594','2016-01-31',3,7,106,17),(508,'15','2016-01-31',3,7,121,18),(509,'38','2016-01-31',3,7,121,19),(510,'8','2016-01-31',3,7,121,20),(511,'40','2016-01-31',3,7,121,21),(512,'90','2016-01-31',3,7,136,22),(513,'0','2016-01-31',3,7,136,23),(514,'0','2016-01-31',3,7,136,24),(515,'69','2016-01-31',3,7,136,25),(516,'0','2016-01-31',3,7,151,26),(517,'0','2016-01-31',3,7,151,27),(518,'0','2016-01-31',3,7,151,28),(519,'0','2016-01-31',3,7,151,29),(520,'0','2016-01-31',3,7,166,30),(521,'0','2016-01-31',3,7,166,31),(522,'75','2016-01-31',3,7,166,32),(523,'0','2016-01-31',3,7,166,33),(524,'0','2016-01-31',3,7,166,34),(525,'5','2016-01-31',3,7,166,35),(526,'0','2016-02-29',3,7,1,1),(527,'0','2016-02-29',3,7,16,2),(528,'0','2016-02-29',3,7,16,3),(529,'0','2016-02-29',3,7,16,4),(530,'0.04','2016-02-29',3,7,31,5),(531,'11','2016-02-29',3,7,46,6),(532,'0','2016-02-29',3,7,46,7),(533,'1.46','2016-02-29',3,7,61,8),(534,'25','2016-02-29',3,7,61,9),(535,'0','2016-02-29',3,7,61,10),(536,'0','2016-02-29',3,7,61,11),(537,'4.4','2016-02-29',3,7,76,12),(538,'3','2016-02-29',3,7,91,13),(539,'0','2016-02-29',3,7,91,14),(540,'4.26','2016-02-29',3,7,106,15),(541,'10.65','2016-02-29',3,7,106,16),(542,'0','2016-02-29',3,7,106,17),(543,'-5','2016-02-29',3,7,121,18),(544,'-7','2016-02-29',3,7,121,19),(545,'-6','2016-02-29',3,7,121,20),(546,'20','2016-02-29',3,7,121,21),(547,'90','2016-02-29',3,7,136,22),(548,'0','2016-02-29',3,7,136,23),(549,'0','2016-02-29',3,7,136,24),(550,'69','2016-02-29',3,7,136,25),(551,'0','2016-02-29',3,7,151,26),(552,'0','2016-02-29',3,7,151,27),(553,'0','2016-02-29',3,7,151,28),(554,'0','2016-02-29',3,7,151,29),(555,'0','2016-02-29',3,7,166,30),(556,'0','2016-02-29',3,7,166,31),(557,'75','2016-02-29',3,7,166,32),(558,'0','2016-02-29',3,7,166,33),(559,'0','2016-02-29',3,7,166,34),(560,'5','2016-02-29',3,7,166,35),(561,'0','2016-01-31',3,8,1,1),(562,'0','2016-01-31',3,8,16,2),(563,'0','2016-01-31',3,8,16,3),(564,'0','2016-01-31',3,8,16,4),(565,'0.82','2016-01-31',3,8,31,5),(566,'11','2016-01-31',3,8,46,6),(567,'0','2016-01-31',3,8,46,7),(568,'1.44','2016-01-31',3,8,61,8),(569,'33','2016-01-31',3,8,61,9),(570,'0','2016-01-31',3,8,61,10),(571,'0','2016-01-31',3,8,61,11),(572,'16.12','2016-01-31',3,8,76,12),(573,'3','2016-01-31',3,8,91,13),(574,'0','2016-01-31',3,8,91,14),(575,'4.15','2016-01-31',3,8,106,15),(576,'16.6','2016-01-31',3,8,106,16),(577,'0','2016-01-31',3,8,106,17),(578,'14','2016-01-31',3,8,121,18),(579,'61','2016-01-31',3,8,121,19),(580,'0','2016-01-31',3,8,121,20),(581,'70','2016-01-31',3,8,121,21),(582,'90','2016-01-31',3,8,136,22),(583,'0','2016-01-31',3,8,136,23),(584,'0','2016-01-31',3,8,136,24),(585,'69','2016-01-31',3,8,136,25),(586,'0','2016-01-31',3,8,151,26),(587,'0','2016-01-31',3,8,151,27),(588,'0','2016-01-31',3,8,151,28),(589,'0','2016-01-31',3,8,151,29),(590,'1','2016-01-31',3,8,166,30),(591,'0','2016-01-31',3,8,166,31),(592,'75','2016-01-31',3,8,166,32),(593,'0','2016-01-31',3,8,166,33),(594,'0','2016-01-31',3,8,166,34),(595,'5','2016-01-31',3,8,166,35),(596,'0','2016-02-29',3,8,1,1),(597,'0','2016-02-29',3,8,16,2),(598,'0','2016-02-29',3,8,16,3),(599,'0','2016-02-29',3,8,16,4),(600,'-0.76','2016-02-29',3,8,31,5),(601,'11','2016-02-29',3,8,46,6),(602,'0','2016-02-29',3,8,46,7),(603,'1.44','2016-02-29',3,8,61,8),(604,'25','2016-02-29',3,8,61,9),(605,'0','2016-02-29',3,8,61,10),(606,'0','2016-02-29',3,8,61,11),(607,'3.39','2016-02-29',3,8,76,12),(608,'3','2016-02-29',3,8,91,13),(609,'0','2016-02-29',3,8,91,14),(610,'4.26','2016-02-29',3,8,106,15),(611,'10.65','2016-02-29',3,8,106,16),(612,'5000','2016-02-29',3,8,106,17),(613,'-6','2016-02-29',3,8,121,18),(614,'13','2016-02-29',3,8,121,19),(615,'-13','2016-02-29',3,8,121,20),(616,'39','2016-02-29',3,8,121,21),(617,'90','2016-02-29',3,8,136,22),(618,'0','2016-02-29',3,8,136,23),(619,'0','2016-02-29',3,8,136,24),(620,'69','2016-02-29',3,8,136,25),(621,'0','2016-02-29',3,8,151,26),(622,'0','2016-02-29',3,8,151,27),(623,'0','2016-02-29',3,8,151,28),(624,'0','2016-02-29',3,8,151,29),(625,'0','2016-02-29',3,8,166,30),(626,'0','2016-02-29',3,8,166,31),(627,'75','2016-02-29',3,8,166,32),(628,'0','2016-02-29',3,8,166,33),(629,'0','2016-02-29',3,8,166,34),(630,'5','2016-02-29',3,8,166,35),(631,'0','2016-01-31',3,9,1,1),(632,'0','2016-01-31',3,9,16,2),(633,'0','2016-01-31',3,9,16,3),(634,'0','2016-01-31',3,9,16,4),(635,'31','2016-01-31',3,9,31,5),(636,'11','2016-01-31',3,9,46,6),(637,'0','2016-01-31',3,9,46,7),(638,'1.12','2016-01-31',3,9,61,8),(639,'33','2016-01-31',3,9,61,9),(640,'1','2016-01-31',3,9,61,10),(641,'0','2016-01-31',3,9,61,11),(642,'1.48','2016-01-31',3,9,76,12),(643,'3','2016-01-31',3,9,91,13),(644,'0','2016-01-31',3,9,91,14),(645,'4.15','2016-01-31',3,9,106,15),(646,'16.6','2016-01-31',3,9,106,16),(647,'13.75','2016-01-31',3,9,106,17),(648,'3','2016-01-31',3,9,121,18),(649,'21','2016-01-31',3,9,121,19),(650,'-2','2016-01-31',3,9,121,20),(651,'93','2016-01-31',3,9,121,21),(652,'95','2016-01-31',3,9,136,22),(653,'0','2016-01-31',3,9,136,23),(654,'0','2016-01-31',3,9,136,24),(655,'69','2016-01-31',3,9,136,25),(656,'00','2016-01-31',3,9,151,26),(657,'0','2016-01-31',3,9,151,27),(658,'0','2016-01-31',3,9,151,28),(659,'0','2016-01-31',3,9,151,29),(660,'1','2016-01-31',3,9,166,30),(661,'0','2016-01-31',3,9,166,31),(662,'75','2016-01-31',3,9,166,32),(663,'0','2016-01-31',3,9,166,33),(664,'0','2016-01-31',3,9,166,34),(665,'4','2016-01-31',3,9,166,35),(666,'0','2016-02-29',3,9,1,1),(667,'0','2016-02-29',3,9,16,2),(668,'0','2016-02-29',3,9,16,3),(669,'0','2016-02-29',3,9,16,4),(670,'0','2016-02-29',3,9,31,5),(671,'11','2016-02-29',3,9,46,6),(672,'0','2016-02-29',3,9,46,7),(673,'1.12','2016-02-29',3,9,61,8),(674,'25','2016-02-29',3,9,61,9),(675,'1','2016-02-29',3,9,61,10),(676,'0','2016-02-29',3,9,61,11),(677,'0.21','2016-02-29',3,9,76,12),(678,'3','2016-02-29',3,9,91,13),(679,'0','2016-02-29',3,9,91,14),(680,'4.26','2016-02-29',3,9,106,15),(681,'10.65','2016-02-29',3,9,106,16),(682,'0','2016-02-29',3,9,106,17),(683,'-8','2016-02-29',3,9,121,18),(684,'-10','2016-02-29',3,9,121,19),(685,'-9','2016-02-29',3,9,121,20),(686,'-15','2016-02-29',3,9,121,21),(687,'95','2016-02-29',3,9,136,22),(688,'0','2016-02-29',3,9,136,23),(689,'0','2016-02-29',3,9,136,24),(690,'69','2016-02-29',3,9,136,25),(691,'0','2016-02-29',3,9,151,26),(692,'0','2016-02-29',3,9,151,27),(693,'0','2016-02-29',3,9,151,28),(694,'0','2016-02-29',3,9,151,29),(695,'0','2016-02-29',3,9,166,30),(696,'0','2016-02-29',3,9,166,31),(697,'75','2016-02-29',3,9,166,32),(698,'0','2016-02-29',3,9,166,33),(699,'0','2016-02-29',3,9,166,34),(700,'4','2016-02-29',3,9,166,35),(701,'0','2016-01-31',3,10,1,1),(702,'0','2016-01-31',3,10,16,2),(703,'0','2016-01-31',3,10,16,3),(704,'0','2016-01-31',3,10,16,4),(705,'-0.36','2016-01-31',3,10,31,5),(706,'11','2016-01-31',3,10,46,6),(707,'0','2016-01-31',3,10,46,7),(708,'1.34','2016-01-31',3,10,61,8),(709,'33','2016-01-31',3,10,61,9),(710,'0','2016-01-31',3,10,61,10),(711,'0','2016-01-31',3,10,61,11),(712,'25','2016-01-31',3,10,76,12),(713,'3','2016-01-31',3,10,91,13),(714,'0','2016-01-31',3,10,91,14),(715,'4.15','2016-01-31',3,10,106,15),(716,'16.6','2016-01-31',3,10,106,16),(717,'0','2016-01-31',3,10,106,17),(718,'13','2016-01-31',3,10,121,18),(719,'37','2016-01-31',3,10,121,19),(720,'8','2016-01-31',3,10,121,20),(721,'--383','2016-01-31',3,10,121,21),(722,'60','2016-01-31',3,10,136,22),(723,'0','2016-01-31',3,10,136,23),(724,'0','2016-01-31',3,10,136,24),(725,'69','2016-01-31',3,10,136,25),(726,'0','2016-01-31',3,10,151,26),(727,'0','2016-01-31',3,10,151,27),(728,'0','2016-01-31',3,10,151,28),(729,'0','2016-01-31',3,10,151,29),(730,'0','2016-01-31',3,10,166,30),(731,'0','2016-01-31',3,10,166,31),(732,'75','2016-01-31',3,10,166,32),(733,'0','2016-01-31',3,10,166,33),(734,'0','2016-01-31',3,10,166,34),(735,'5','2016-01-31',3,10,166,35),(736,'0','2016-02-29',3,10,1,1),(737,'0','2016-02-29',3,10,16,2),(738,'0','2016-02-29',3,10,16,3),(739,'0','2016-02-29',3,10,16,4),(740,'2.38','2016-02-29',3,10,31,5),(741,'11','2016-02-29',3,10,46,6),(742,'0','2016-02-29',3,10,46,7),(743,'1.34','2016-02-29',3,10,61,8),(744,'25','2016-02-29',3,10,61,9),(745,'0','2016-02-29',3,10,61,10),(746,'0','2016-02-29',3,10,61,11),(747,'7','2016-02-29',3,10,76,12),(748,'3','2016-02-29',3,10,91,13),(749,'0','2016-02-29',3,10,91,14),(750,'4.26','2016-02-29',3,10,106,15),(751,'10.65','2016-02-29',3,10,106,16),(752,'30000','2016-02-29',3,10,106,17),(753,'-13','2016-02-29',3,10,121,18),(754,'-31','2016-02-29',3,10,121,19),(755,'-8','2016-02-29',3,10,121,20),(756,'-326','2016-02-29',3,10,121,21),(757,'70','2016-02-29',3,10,136,22),(758,'0','2016-02-29',3,10,136,23),(759,'0','2016-02-29',3,10,136,24),(760,'69','2016-02-29',3,10,136,25),(761,'0','2016-02-29',3,10,151,26),(762,'0','2016-02-29',3,10,151,27),(763,'0','2016-02-29',3,10,151,28),(764,'0','2016-02-29',3,10,151,29),(765,'0','2016-02-29',3,10,166,30),(766,'0','2016-02-29',3,10,166,31),(767,'75','2016-02-29',3,10,166,32),(768,'0','2016-02-29',3,10,166,33),(769,'0','2016-02-29',3,10,166,34),(770,'5','2016-02-29',3,10,166,35),(771,'0','2016-01-31',3,11,1,1),(772,'0','2016-01-31',3,11,16,2),(773,'0','2016-01-31',3,11,16,3),(774,'0','2016-01-31',3,11,16,4),(775,'1.72','2016-01-31',3,11,31,5),(776,'11','2016-01-31',3,11,46,6),(777,'0','2016-01-31',3,11,46,7),(778,'1.47','2016-01-31',3,11,61,8),(779,'33','2016-01-31',3,11,61,9),(780,'0','2016-01-31',3,11,61,10),(781,'0','2016-01-31',3,11,61,11),(782,'30.52','2016-01-31',3,11,76,12),(783,'3','2016-01-31',3,11,91,13),(784,'0','2016-01-31',3,11,91,14),(785,'4.15','2016-01-31',3,11,106,15),(786,'16.6','2016-01-31',3,11,106,16),(787,'0','2016-01-31',3,11,106,17),(788,'0','2016-01-31',3,11,121,18),(789,'16','2016-01-31',3,11,121,19),(790,'-3','2016-01-31',3,11,121,20),(791,'65','2016-01-31',3,11,121,21),(792,'90','2016-01-31',3,11,136,22),(793,'0','2016-01-31',3,11,136,23),(794,'0','2016-01-31',3,11,136,24),(795,'69','2016-01-31',3,11,136,25),(796,'0','2016-01-31',3,11,151,26),(797,'0','2016-01-31',3,11,151,27),(798,'0','2016-01-31',3,11,151,28),(799,'0','2016-01-31',3,11,151,29),(800,'0','2016-01-31',3,11,166,30),(801,'0','2016-01-31',3,11,166,31),(802,'75','2016-01-31',3,11,166,32),(803,'0','2016-01-31',3,11,166,33),(804,'0','2016-01-31',3,11,166,34),(805,'5','2016-01-31',3,11,166,35),(806,'0','2016-02-29',3,11,1,1),(807,'0','2016-02-29',3,11,16,2),(808,'0','2016-02-29',3,11,16,3),(809,'0','2016-02-29',3,11,16,4),(810,'-1.78','2016-02-29',3,11,31,5),(811,'11','2016-02-29',3,11,46,6),(812,'0','2016-02-29',3,11,46,7),(813,'1.47','2016-02-29',3,11,61,8),(814,'25','2016-02-29',3,11,61,9),(815,'0','2016-02-29',3,11,61,10),(816,'0','2016-02-29',3,11,61,11),(817,'7.39','2016-02-29',3,11,76,12),(818,'3','2016-02-29',3,11,91,13),(819,'0','2016-02-29',3,11,91,14),(820,'4.26','2016-02-29',3,11,106,15),(821,'10.65','2016-02-29',3,11,106,16),(822,'187.5','2016-02-29',3,11,106,17),(823,'-3','2016-02-29',3,11,121,18),(824,'16','2016-02-29',3,11,121,19),(825,'-9','2016-02-29',3,11,121,20),(826,'76','2016-02-29',3,11,121,21),(827,'90','2016-02-29',3,11,136,22),(828,'0','2016-02-29',3,11,136,23),(829,'0','2016-02-29',3,11,136,24),(830,'69','2016-02-29',3,11,136,25),(831,'0','2016-02-29',3,11,151,26),(832,'0','2016-02-29',3,11,151,27),(833,'0','2016-02-29',3,11,151,28),(834,'0','2016-02-29',3,11,151,29),(835,'0','2016-02-29',3,11,166,30),(836,'0','2016-02-29',3,11,166,31),(837,'75','2016-02-29',3,11,166,32),(838,'0','2016-02-29',3,11,166,33),(839,'0','2016-02-29',3,11,166,34),(840,'5','2016-02-29',3,11,166,35),(841,'0','2016-01-31',3,12,1,1),(842,'0','2016-01-31',3,12,16,2),(843,'0','2016-01-31',3,12,16,3),(844,'0','2016-01-31',3,12,16,4),(845,'-0.75','2016-01-31',3,12,31,5),(846,'11','2016-01-31',3,12,46,6),(847,'0','2016-01-31',3,12,46,7),(848,'1.45','2016-01-31',3,12,61,8),(849,'33','2016-01-31',3,12,61,9),(850,'0','2016-01-31',3,12,61,10),(851,'0','2016-01-31',3,12,61,11),(852,'28','2016-01-31',3,12,76,12),(853,'3','2016-01-31',3,12,91,13),(854,'0','2016-01-31',3,12,91,14),(855,'4.15','2016-01-31',3,12,106,15),(856,'16.6','2016-01-31',3,12,106,16),(857,'24420','2016-01-31',3,12,106,17),(858,'22','2016-01-31',3,12,121,18),(859,'76','2016-01-31',3,12,121,19),(860,'7','2016-01-31',3,12,121,20),(861,'94','2016-01-31',3,12,121,21),(862,'90','2016-01-31',3,12,136,22),(863,'0','2016-01-31',3,12,136,23),(864,'0','2016-01-31',3,12,136,24),(865,'69','2016-01-31',3,12,136,25),(866,'0','2016-01-31',3,12,151,26),(867,'0','2016-01-31',3,12,151,27),(868,'0','2016-01-31',3,12,151,28),(869,'0','2016-01-31',3,12,151,29),(870,'0','2016-01-31',3,12,166,30),(871,'0','2016-01-31',3,12,166,31),(872,'75','2016-01-31',3,12,166,32),(873,'0','2016-01-31',3,12,166,33),(874,'0','2016-01-31',3,12,166,34),(875,'5','2016-01-31',3,12,166,35),(876,'0','2016-02-29',3,12,1,1),(877,'0','2016-02-29',3,12,16,2),(878,'0','2016-02-29',3,12,16,3),(879,'0','2016-02-29',3,12,16,4),(880,'-0.53','2016-02-29',3,12,31,5),(881,'11','2016-02-29',3,12,46,6),(882,'0','2016-02-29',3,12,46,7),(883,'1.45','2016-02-29',3,12,61,8),(884,'25','2016-02-29',3,12,61,9),(885,'0','2016-02-29',3,12,61,10),(886,'0','2016-02-29',3,12,61,11),(887,'13','2016-02-29',3,12,76,12),(888,'3','2016-02-29',3,12,91,13),(889,'0','2016-02-29',3,12,91,14),(890,'4.26','2016-02-29',3,12,106,15),(891,'10.65','2016-02-29',3,12,106,16),(892,'0','2016-02-29',3,12,106,17),(893,'-2','2016-02-29',3,12,121,18),(894,'16','2016-02-29',3,12,121,19),(895,'-9','2016-02-29',3,12,121,20),(896,'37','2016-02-29',3,12,121,21),(897,'90','2016-02-29',3,12,136,22),(898,'0','2016-02-29',3,12,136,23),(899,'0','2016-02-29',3,12,136,24),(900,'69','2016-02-29',3,12,136,25),(901,'0','2016-02-29',3,12,151,26),(902,'0','2016-02-29',3,12,151,27),(903,'0','2016-02-29',3,12,151,28),(904,'0','2016-02-29',3,12,151,29),(905,'0','2016-02-29',3,12,166,30),(906,'0','2016-02-29',3,12,166,31),(907,'75','2016-02-29',3,12,166,32),(908,'0','2016-02-29',3,12,166,33),(909,'0','2016-02-29',3,12,166,34),(910,'5','2016-02-29',3,12,166,35),(911,'0','2016-01-31',3,13,1,1),(912,'0','2016-01-31',3,13,16,2),(913,'0','2016-01-31',3,13,16,3),(914,'0','2016-01-31',3,13,16,4),(915,'-0.31','2016-01-31',3,13,31,5),(916,'11','2016-01-31',3,13,46,6),(917,'0','2016-01-31',3,13,46,7),(918,'1.36','2016-01-31',3,13,61,8),(919,'33','2016-01-31',3,13,61,9),(920,'0','2016-01-31',3,13,61,10),(921,'0','2016-01-31',3,13,61,11),(922,'5.47','2016-01-31',3,13,76,12),(923,'3','2016-01-31',3,13,91,13),(924,'0','2016-01-31',3,13,91,14),(925,'4.15','2016-01-31',3,13,106,15),(926,'16.6','2016-01-31',3,13,106,16),(927,'0','2016-01-31',3,13,106,17),(928,'24','2016-01-31',3,13,121,18),(929,'19','2016-01-31',3,13,121,19),(930,'9','2016-01-31',3,13,121,20),(931,'45','2016-01-31',3,13,121,21),(932,'95','2016-01-31',3,13,136,22),(933,'0','2016-01-31',3,13,136,23),(934,'0','2016-01-31',3,13,136,24),(935,'69','2016-01-31',3,13,136,25),(936,'0','2016-01-31',3,13,151,26),(937,'0','2016-01-31',3,13,151,27),(938,'0','2016-01-31',3,13,151,28),(939,'0','2016-01-31',3,13,151,29),(940,'1','2016-01-31',3,13,166,30),(941,'0','2016-01-31',3,13,166,31),(942,'75','2016-01-31',3,13,166,32),(943,'0','2016-01-31',3,13,166,33),(944,'0','2016-01-31',3,13,166,34),(945,'5','2016-01-31',3,13,166,35),(946,'1540','2016-02-29',3,13,1,1),(947,'0','2016-02-29',3,13,16,2),(948,'0','2016-02-29',3,13,16,3),(949,'2','2016-02-29',3,13,16,4),(950,'045','2016-02-29',3,13,31,5),(951,'11','2016-02-29',3,13,46,6),(952,'0','2016-02-29',3,13,46,7),(953,'1.36','2016-02-29',3,13,61,8),(954,'25','2016-02-29',3,13,61,9),(955,'0','2016-02-29',3,13,61,10),(956,'0','2016-02-29',3,13,61,11),(957,'2','2016-02-29',3,13,76,12),(958,'3','2016-02-29',3,13,91,13),(959,'0','2016-02-29',3,13,91,14),(960,'4.26','2016-02-29',3,13,106,15),(961,'10.65','2016-02-29',3,13,106,16),(962,'90','2016-02-29',3,13,106,17),(963,'-5','2016-02-29',3,13,121,18),(964,'15','2016-02-29',3,13,121,19),(965,'-12','2016-02-29',3,13,121,20),(966,'54','2016-02-29',3,13,121,21),(967,'95','2016-02-29',3,13,136,22),(968,'0','2016-02-29',3,13,136,23),(969,'0','2016-02-29',3,13,136,24),(970,'69','2016-02-29',3,13,136,25),(971,'0','2016-02-29',3,13,151,26),(972,'0','2016-02-29',3,13,151,27),(973,'0','2016-02-29',3,13,151,28),(974,'0','2016-02-29',3,13,151,29),(975,'0','2016-02-29',3,13,166,30),(976,'0','2016-02-29',3,13,166,31),(977,'75','2016-02-29',3,13,166,32),(978,'0','2016-02-29',3,13,166,33),(979,'0','2016-02-29',3,13,166,34),(980,'5','2016-02-29',3,13,166,35),(981,'0','2016-01-31',3,15,1,1),(982,'0','2016-01-31',3,15,16,2),(983,'2','2016-01-31',3,15,16,3),(984,'2','2016-01-31',3,15,16,4),(985,'0','2016-01-31',3,15,31,5),(986,'11','2016-01-31',3,15,46,6),(987,'0','2016-01-31',3,15,46,7),(988,'1.6','2016-01-31',3,15,61,8),(989,'33','2016-01-31',3,15,61,9),(990,'0','2016-01-31',3,15,61,10),(991,'0','2016-01-31',3,15,61,11),(992,'0','2016-01-31',3,15,76,12),(993,'3','2016-01-31',3,15,91,13),(994,'0','2016-01-31',3,15,91,14),(995,'4.15','2016-01-31',3,15,106,15),(996,'16.6','2016-01-31',3,15,106,16),(997,'0','2016-01-31',3,15,106,17),(998,'0','2016-01-31',3,15,121,18),(999,'0','2016-01-31',3,15,121,19),(1000,'0','2016-01-31',3,15,121,20),(1001,'0','2016-01-31',3,15,121,21),(1002,'0','2016-01-31',3,15,136,22),(1003,'0','2016-01-31',3,15,136,23),(1004,'0','2016-01-31',3,15,136,24),(1005,'69','2016-01-31',3,15,136,25),(1006,'0','2016-01-31',3,15,151,26),(1007,'0','2016-01-31',3,15,151,27),(1008,'0','2016-01-31',3,15,151,28),(1009,'0','2016-01-31',3,15,151,29),(1010,'0','2016-01-31',3,15,166,30),(1011,'0','2016-01-31',3,15,166,31),(1012,'75','2016-01-31',3,15,166,32),(1013,'0','2016-01-31',3,15,166,33),(1014,'0','2016-01-31',3,15,166,34),(1015,'5','2016-01-31',3,15,166,35),(1016,'0','2016-02-29',3,15,1,1),(1017,'0','2016-02-29',3,15,16,2),(1018,'0','2016-02-29',3,15,16,3),(1019,'0','2016-02-29',3,15,16,4),(1020,'5.61','2016-02-29',3,15,31,5),(1021,'11','2016-02-29',3,15,46,6),(1022,'0','2016-02-29',3,15,46,7),(1023,'1.45','2016-02-29',3,15,61,8),(1024,'25','2016-02-29',3,15,61,9),(1025,'0','2016-02-29',3,15,61,10),(1026,'0','2016-02-29',3,15,61,11),(1027,'1.3','2016-02-29',3,15,76,12),(1028,'3','2016-02-29',3,15,91,13),(1029,'0','2016-02-29',3,15,91,14),(1030,'4.26','2016-02-29',3,15,106,15),(1031,'10.65','2016-02-29',3,15,106,16),(1032,'0','2016-02-29',3,15,106,17),(1033,'-6','2016-02-29',3,15,121,18),(1034,'-9','2016-02-29',3,15,121,19),(1035,'-5','2016-02-29',3,15,121,20),(1036,'60','2016-02-29',3,15,121,21),(1037,'9','2016-02-29',3,15,136,22),(1038,'2','2016-02-29',3,15,136,23),(1039,'0','2016-02-29',3,15,136,24),(1040,'69','2016-02-29',3,15,136,25),(1041,'0','2016-02-29',3,15,151,26),(1042,'0','2016-02-29',3,15,151,27),(1043,'0','2016-02-29',3,15,151,28),(1044,'0','2016-02-29',3,15,151,29),(1045,'0','2016-02-29',3,15,166,30),(1046,'0','2016-02-29',3,15,166,31),(1047,'75','2016-02-29',3,15,166,32),(1048,'0','2016-02-29',3,15,166,33),(1049,'0','2016-02-29',3,15,166,34),(1050,'5','2016-02-29',3,15,166,35),(1051,'0','2016-03-31',1,1,1,1),(1052,'0','2016-03-31',1,1,16,2),(1053,'0','2016-03-31',1,1,16,3),(1054,'0','2016-03-31',1,1,16,4),(1055,'1.34','2016-03-31',1,1,31,5),(1056,'9','2016-03-31',1,1,46,6),(1057,'0','2016-03-31',1,1,46,7),(1058,'1.73','2016-03-31',1,1,61,8),(1059,'46','2016-03-31',1,1,61,9),(1060,'0','2016-03-31',1,1,61,10),(1061,'1','2016-03-31',1,1,61,11),(1062,'16.07','2016-03-31',1,1,76,12),(1063,'3','2016-03-31',1,1,91,13),(1064,'0','2016-03-31',1,1,91,14),(1065,'4.26','2016-03-31',1,1,106,15),(1066,'9.81','2016-03-31',1,1,106,16),(1067,'27985','2016-03-31',1,1,106,17),(1068,'-22','2016-03-31',1,1,121,18),(1069,'-63','2016-03-31',1,1,121,19),(1070,'-10','2016-03-31',1,1,121,20),(1071,'-75','2016-03-31',1,1,121,21),(1072,'90','2016-03-31',1,1,136,22),(1073,'0','2016-03-31',1,1,136,23),(1074,'0','2016-03-31',1,1,136,24),(1075,'71','2016-03-31',1,1,136,25),(1076,'0','2016-03-31',1,1,151,26),(1077,'0','2016-03-31',1,1,151,27),(1078,'0','2016-03-31',1,1,151,28),(1079,'0','2016-03-31',1,1,151,29),(1080,'16','2016-03-31',1,1,166,30),(1081,'0','2016-03-31',1,1,166,31),(1082,'42','2016-03-31',1,1,166,32),(1083,'0','2016-03-31',1,1,166,33),(1084,'0','2016-03-31',1,1,166,34),(1085,'2','2016-03-31',1,1,166,35),(1086,'0','2016-03-31',1,2,1,1),(1087,'0','2016-03-31',1,2,16,2),(1088,'0','2016-03-31',1,2,16,3),(1089,'0','2016-03-31',1,2,16,4),(1090,'0','2016-03-31',1,2,31,5),(1091,'9','2016-03-31',1,2,46,6),(1092,'1','2016-03-31',1,2,46,7),(1093,'1.5','2016-03-31',1,2,61,8),(1094,'46','2016-03-31',1,2,61,9),(1095,'1','2016-03-31',1,2,61,10),(1096,'1','2016-03-31',1,2,61,11),(1097,'17','2016-03-31',1,2,76,12),(1098,'3','2016-03-31',1,2,91,13),(1099,'0','2016-03-31',1,2,91,14),(1100,'4.26','2016-03-31',1,2,106,15),(1101,'9.81','2016-03-31',1,2,106,16),(1102,'0','2016-03-31',1,2,106,17),(1103,'-15','2016-03-31',1,2,121,18),(1104,'-19','2016-03-31',1,2,121,19),(1105,'-15','2016-03-31',1,2,121,20),(1106,'-22','2016-03-31',1,2,121,21),(1107,'93','2016-03-31',1,2,136,22),(1108,'0','2016-03-31',1,2,136,23),(1109,'1','2016-03-31',1,2,136,24),(1110,'71','2016-03-31',1,2,136,25),(1111,'0','2016-03-31',1,2,151,26),(1112,'0','2016-03-31',1,2,151,27),(1113,'0','2016-03-31',1,2,151,28),(1114,'0','2016-03-31',1,2,151,29),(1115,'0','2016-03-31',1,2,166,30),(1116,'0','2016-03-31',1,2,166,31),(1117,'42','2016-03-31',1,2,166,32),(1118,'0','2016-03-31',1,2,166,33),(1119,'0','2016-03-31',1,2,166,34),(1120,'5','2016-03-31',1,2,166,35),(1121,'0','2016-03-31',1,3,1,1),(1122,'0','2016-03-31',1,3,16,2),(1123,'0','2016-03-31',1,3,16,3),(1124,'0','2016-03-31',1,3,16,4),(1125,'0','2016-03-31',1,3,31,5),(1126,'9','2016-03-31',1,3,46,6),(1127,'0','2016-03-31',1,3,46,7),(1128,'1.46','2016-03-31',1,3,61,8),(1129,'46','2016-03-31',1,3,61,9),(1130,'0','2016-03-31',1,3,61,10),(1131,'0','2016-03-31',1,3,61,11),(1132,'4','2016-03-31',1,3,76,12),(1133,'3','2016-03-31',1,3,91,13),(1134,'0','2016-03-31',1,3,91,14),(1135,'4.26','2016-03-31',1,3,106,15),(1136,'9.81','2016-03-31',1,3,106,16),(1137,'0','2016-03-31',1,3,106,17),(1138,'-1','2016-03-31',1,3,121,18),(1139,'10','2016-03-31',1,3,121,19),(1140,'-6','2016-03-31',1,3,121,20),(1141,'-23','2016-03-31',1,3,121,21),(1142,'90','2016-03-31',1,3,136,22),(1143,'0','2016-03-31',1,3,136,23),(1144,'0','2016-03-31',1,3,136,24),(1145,'71','2016-03-31',1,3,136,25),(1146,'0','2016-03-31',1,3,151,26),(1147,'0','2016-03-31',1,3,151,27),(1148,'0','2016-03-31',1,3,151,28),(1149,'0','2016-03-31',1,3,151,29),(1150,'0','2016-03-31',1,3,166,30),(1151,'0','2016-03-31',1,3,166,31),(1152,'42','2016-03-31',1,3,166,32),(1153,'0','2016-03-31',1,3,166,33),(1154,'0','2016-03-31',1,3,166,34),(1155,'5','2016-03-31',1,3,166,35),(1156,'0','2016-03-31',1,4,1,1),(1157,'0','2016-03-31',1,4,16,2),(1158,'0','2016-03-31',1,4,16,3),(1159,'0','2016-03-31',1,4,16,4),(1160,'0.12','2016-03-31',1,4,31,5),(1161,'9','2016-03-31',1,4,46,6),(1162,'0','2016-03-31',1,4,46,7),(1163,'1.5','2016-03-31',1,4,61,8),(1164,'46','2016-03-31',1,4,61,9),(1165,'0','2016-03-31',1,4,61,10),(1166,'0','2016-03-31',1,4,61,11),(1167,'10','2016-03-31',1,4,76,12),(1168,'3','2016-03-31',1,4,91,13),(1169,'0','2016-03-31',1,4,91,14),(1170,'4.26','2016-03-31',1,4,106,15),(1171,'9.81','2016-03-31',1,4,106,16),(1172,'0','2016-03-31',1,4,106,17),(1173,'-6','2016-03-31',1,4,121,18),(1174,'14','2016-03-31',1,4,121,19),(1175,'-13','2016-03-31',1,4,121,20),(1176,'-55','2016-03-31',1,4,121,21),(1177,'93','2016-03-31',1,4,136,22),(1178,'0','2016-03-31',1,4,136,23),(1179,'0','2016-03-31',1,4,136,24),(1180,'71','2016-03-31',1,4,136,25),(1181,'0','2016-03-31',1,4,151,26),(1182,'0','2016-03-31',1,4,151,27),(1183,'0','2016-03-31',1,4,151,28),(1184,'0','2016-03-31',1,4,151,29),(1185,'0','2016-03-31',1,4,166,30),(1186,'0','2016-03-31',1,4,166,31),(1187,'42','2016-03-31',1,4,166,32),(1188,'0','2016-03-31',1,4,166,33),(1189,'0','2016-03-31',1,4,166,34),(1190,'4','2016-03-31',1,4,166,35),(1191,'0','2016-03-31',1,5,1,1),(1192,'0','2016-03-31',1,5,16,2),(1193,'0','2016-03-31',1,5,16,3),(1194,'0','2016-03-31',1,5,16,4),(1195,'-0.25','2016-03-31',1,5,31,5),(1196,'9','2016-03-31',1,5,46,6),(1197,'0','2016-03-31',1,5,46,7),(1198,'1.26','2016-03-31',1,5,61,8),(1199,'46','2016-03-31',1,5,61,9),(1200,'0','2016-03-31',1,5,61,10),(1201,'0','2016-03-31',1,5,61,11),(1202,'7','2016-03-31',1,5,76,12),(1203,'3','2016-03-31',1,5,91,13),(1204,'0','2016-03-31',1,5,91,14),(1205,'4.26','2016-03-31',1,5,106,15),(1206,'9.81','2016-03-31',1,5,106,16),(1207,'0','2016-03-31',1,5,106,17),(1208,'-4','2016-03-31',1,5,121,18),(1209,'13','2016-03-31',1,5,121,19),(1210,'-11','2016-03-31',1,5,121,20),(1211,'-16','2016-03-31',1,5,121,21),(1212,'100','2016-03-31',1,5,136,22),(1213,'0','2016-03-31',1,5,136,23),(1214,'0','2016-03-31',1,5,136,24),(1215,'71','2016-03-31',1,5,136,25),(1216,'0','2016-03-31',1,5,151,26),(1217,'0','2016-03-31',1,5,151,27),(1218,'0','2016-03-31',1,5,151,28),(1219,'0','2016-03-31',1,5,151,29),(1220,'3','2016-03-31',1,5,166,30),(1221,'0','2016-03-31',1,5,166,31),(1222,'42','2016-03-31',1,5,166,32),(1223,'0','2016-03-31',1,5,166,33),(1224,'0','2016-03-31',1,5,166,34),(1225,'5','2016-03-31',1,5,166,35),(1226,'0','2016-03-31',1,6,1,1),(1227,'0','2016-03-31',1,6,16,2),(1228,'0','2016-03-31',1,6,16,3),(1229,'0','2016-03-31',1,6,16,4),(1230,'1.89','2016-03-31',1,6,31,5),(1231,'9','2016-03-31',1,6,46,6),(1232,'0','2016-03-31',1,6,46,7),(1233,'1.48','2016-03-31',1,6,61,8),(1234,'46','2016-03-31',1,6,61,9),(1235,'0','2016-03-31',1,6,61,10),(1236,'0','2016-03-31',1,6,61,11),(1237,'11.31','2016-03-31',1,6,76,12),(1238,'3','2016-03-31',1,6,91,13),(1239,'0','2016-03-31',1,6,91,14),(1240,'4.26','2016-03-31',1,6,106,15),(1241,'9.81','2016-03-31',1,6,106,16),(1242,'0','2016-03-31',1,6,106,17),(1243,'-4','2016-03-31',1,6,121,18),(1244,'9','2016-03-31',1,6,121,19),(1245,'-9','2016-03-31',1,6,121,20),(1246,'25','2016-03-31',1,6,121,21),(1247,'95','2016-03-31',1,6,136,22),(1248,'0','2016-03-31',1,6,136,23),(1249,'0','2016-03-31',1,6,136,24),(1250,'71','2016-03-31',1,6,136,25),(1251,'0','2016-03-31',1,6,151,26),(1252,'0','2016-03-31',1,6,151,27),(1253,'0','2016-03-31',1,6,151,28),(1254,'0','2016-03-31',1,6,151,29),(1255,'0','2016-03-31',1,6,166,30),(1256,'0','2016-03-31',1,6,166,31),(1257,'42','2016-03-31',1,6,166,32),(1258,'0','2016-03-31',1,6,166,33),(1259,'0','2016-03-31',1,6,166,34),(1260,'5','2016-03-31',1,6,166,35),(1261,'0','2016-03-31',1,7,1,1),(1262,'0','2016-03-31',1,7,16,2),(1263,'0','2016-03-31',1,7,16,3),(1264,'0','2016-03-31',1,7,16,4),(1265,'0','2016-03-31',1,7,31,5),(1266,'9','2016-03-31',1,7,46,6),(1267,'0','2016-03-31',1,7,46,7),(1268,'1.46','2016-03-31',1,7,61,8),(1269,'46','2016-03-31',1,7,61,9),(1270,'1','2016-03-31',1,7,61,10),(1271,'0','2016-03-31',1,7,61,11),(1272,'11.19','2016-03-31',1,7,76,12),(1273,'3','2016-03-31',1,7,91,13),(1274,'0','2016-03-31',1,7,91,14),(1275,'4.26','2016-03-31',1,7,106,15),(1276,'9.81','2016-03-31',1,7,106,16),(1277,'16949','2016-03-31',1,7,106,17),(1278,'-20','2016-03-31',1,7,121,18),(1279,'-72','2016-03-31',1,7,121,19),(1280,'-6','2016-03-31',1,7,121,20),(1281,'32','2016-03-31',1,7,121,21),(1282,'91','2016-03-31',1,7,136,22),(1283,'0','2016-03-31',1,7,136,23),(1284,'0','2016-03-31',1,7,136,24),(1285,'71','2016-03-31',1,7,136,25),(1286,'0','2016-03-31',1,7,151,26),(1287,'0','2016-03-31',1,7,151,27),(1288,'0','2016-03-31',1,7,151,28),(1289,'0','2016-03-31',1,7,151,29),(1290,'0','2016-03-31',1,7,166,30),(1291,'0','2016-03-31',1,7,166,31),(1292,'42','2016-03-31',1,7,166,32),(1293,'0','2016-03-31',1,7,166,33),(1294,'0','2016-03-31',1,7,166,34),(1295,'5','2016-03-31',1,7,166,35),(1296,'0','2016-03-31',1,8,1,1),(1297,'0','2016-03-31',1,8,16,2),(1298,'0','2016-03-31',1,8,16,3),(1299,'0','2016-03-31',1,8,16,4),(1300,'-0.46','2016-03-31',1,8,31,5),(1301,'9','2016-03-31',1,8,46,6),(1302,'0','2016-03-31',1,8,46,7),(1303,'1.44','2016-03-31',1,8,61,8),(1304,'46','2016-03-31',1,8,61,9),(1305,'0','2016-03-31',1,8,61,10),(1306,'0','2016-03-31',1,8,61,11),(1307,'7.35','2016-03-31',1,8,76,12),(1308,'3','2016-03-31',1,8,91,13),(1309,'0','2016-03-31',1,8,91,14),(1310,'4.26','2016-03-31',1,8,106,15),(1311,'9.81','2016-03-31',1,8,106,16),(1312,'0','2016-03-31',1,8,106,17),(1313,'-23','2016-03-31',1,8,121,18),(1314,'-61','2016-03-31',1,8,121,19),(1315,'-13','2016-03-31',1,8,121,20),(1316,'-35','2016-03-31',1,8,121,21),(1317,'94','2016-03-31',1,8,136,22),(1318,'0','2016-03-31',1,8,136,23),(1319,'0','2016-03-31',1,8,136,24),(1320,'71','2016-03-31',1,8,136,25),(1321,'0','2016-03-31',1,8,151,26),(1322,'0','2016-03-31',1,8,151,27),(1323,'0','2016-03-31',1,8,151,28),(1324,'0','2016-03-31',1,8,151,29),(1325,'0','2016-03-31',1,8,166,30),(1326,'0','2016-03-31',1,8,166,31),(1327,'42','2016-03-31',1,8,166,32),(1328,'0','2016-03-31',1,8,166,33),(1329,'0','2016-03-31',1,8,166,34),(1330,'5','2016-03-31',1,8,166,35),(1331,'0','2016-03-31',1,9,1,1),(1332,'0','2016-03-31',1,9,16,2),(1333,'0','2016-03-31',1,9,16,3),(1334,'0','2016-03-31',1,9,16,4),(1335,'0','2016-03-31',1,9,31,5),(1336,'9','2016-03-31',1,9,46,6),(1337,'0','2016-03-31',1,9,46,7),(1338,'1.12','2016-03-31',1,9,61,8),(1339,'46','2016-03-31',1,9,61,9),(1340,'1','2016-03-31',1,9,61,10),(1341,'0','2016-03-31',1,9,61,11),(1342,'1.01','2016-03-31',1,9,76,12),(1343,'3','2016-03-31',1,9,91,13),(1344,'0','2016-03-31',1,9,91,14),(1345,'4.26','2016-03-31',1,9,106,15),(1346,'9.81','2016-03-31',1,9,106,16),(1347,'0','2016-03-31',1,9,106,17),(1348,'-6','2016-03-31',1,9,121,18),(1349,'11','2016-03-31',1,9,121,19),(1350,'-13','2016-03-31',1,9,121,20),(1351,'-55','2016-03-31',1,9,121,21),(1352,'90','2016-03-31',1,9,136,22),(1353,'0','2016-03-31',1,9,136,23),(1354,'0','2016-03-31',1,9,136,24),(1355,'71','2016-03-31',1,9,136,25),(1356,'0','2016-03-31',1,9,151,26),(1357,'0','2016-03-31',1,9,151,27),(1358,'0','2016-03-31',1,9,151,28),(1359,'0','2016-03-31',1,9,151,29),(1360,'0','2016-03-31',1,9,166,30),(1361,'0','2016-03-31',1,9,166,31),(1362,'42','2016-03-31',1,9,166,32),(1363,'0','2016-03-31',1,9,166,33),(1364,'0','2016-03-31',1,9,166,34),(1365,'4','2016-03-31',1,9,166,35),(1366,'0','2016-03-31',1,10,1,1),(1367,'0','2016-03-31',1,10,16,2),(1368,'0','2016-03-31',1,10,16,3),(1369,'0','2016-03-31',1,10,16,4),(1370,'0.03','2016-03-31',1,10,31,5),(1371,'9','2016-03-31',1,10,46,6),(1372,'0','2016-03-31',1,10,46,7),(1373,'1.34','2016-03-31',1,10,61,8),(1374,'46','2016-03-31',1,10,61,9),(1375,'2','2016-03-31',1,10,61,10),(1376,'0','2016-03-31',1,10,61,11),(1377,'6.21','2016-03-31',1,10,76,12),(1378,'3','2016-03-31',1,10,91,13),(1379,'0','2016-03-31',1,10,91,14),(1380,'4.26','2016-03-31',1,10,106,15),(1381,'9.81','2016-03-31',1,10,106,16),(1382,'0','2016-03-31',1,10,106,17),(1383,'-10','2016-03-31',1,10,121,18),(1384,'-27','2016-03-31',1,10,121,19),(1385,'-7','2016-03-31',1,10,121,20),(1386,'-232','2016-03-31',1,10,121,21),(1387,'90','2016-03-31',1,10,136,22),(1388,'0','2016-03-31',1,10,136,23),(1389,'0','2016-03-31',1,10,136,24),(1390,'71','2016-03-31',1,10,136,25),(1391,'0','2016-03-31',1,10,151,26),(1392,'0','2016-03-31',1,10,151,27),(1393,'0','2016-03-31',1,10,151,28),(1394,'0','2016-03-31',1,10,151,29),(1395,'1','2016-03-31',1,10,166,30),(1396,'0','2016-03-31',1,10,166,31),(1397,'42','2016-03-31',1,10,166,32),(1398,'0','2016-03-31',1,10,166,33),(1399,'0','2016-03-31',1,10,166,34),(1400,'5','2016-03-31',1,10,166,35),(1401,'0','2016-03-31',1,11,1,1),(1402,'0','2016-03-31',1,11,16,2),(1403,'0','2016-03-31',1,11,16,3),(1404,'0','2016-03-31',1,11,16,4),(1405,'-0.24','2016-03-31',1,11,31,5),(1406,'9','2016-03-31',1,11,46,6),(1407,'0','2016-03-31',1,11,46,7),(1408,'1.47','2016-03-31',1,11,61,8),(1409,'46','2016-03-31',1,11,61,9),(1410,'0','2016-03-31',1,11,61,10),(1411,'0','2016-03-31',1,11,61,11),(1412,'14.08','2016-03-31',1,11,76,12),(1413,'3','2016-03-31',1,11,91,13),(1414,'0','2016-03-31',1,11,91,14),(1415,'4.26','2016-03-31',1,11,106,15),(1416,'9.81','2016-03-31',1,11,106,16),(1417,'0','2016-03-31',1,11,106,17),(1418,'-7','2016-03-31',1,11,121,18),(1419,'-5','2016-03-31',1,11,121,19),(1420,'-8','2016-03-31',1,11,121,20),(1421,'-37','2016-03-31',1,11,121,21),(1422,'100','2016-03-31',1,11,136,22),(1423,'0','2016-03-31',1,11,136,23),(1424,'1','2016-03-31',1,11,136,24),(1425,'71','2016-03-31',1,11,136,25),(1426,'0','2016-03-31',1,11,151,26),(1427,'0','2016-03-31',1,11,151,27),(1428,'0','2016-03-31',1,11,151,28),(1429,'0','2016-03-31',1,11,151,29),(1430,'0','2016-03-31',1,11,166,30),(1431,'0','2016-03-31',1,11,166,31),(1432,'42','2016-03-31',1,11,166,32),(1433,'0','2016-03-31',1,11,166,33),(1434,'0','2016-03-31',1,11,166,34),(1435,'5','2016-03-31',1,11,166,35),(1436,'0','2016-03-31',1,12,1,1),(1437,'0','2016-03-31',1,12,16,2),(1438,'0','2016-03-31',1,12,16,3),(1439,'0','2016-03-31',1,12,16,4),(1440,'-0.82','2016-03-31',1,12,31,5),(1441,'9','2016-03-31',1,12,46,6),(1442,'0','2016-03-31',1,12,46,7),(1443,'1.45','2016-03-31',1,12,61,8),(1444,'46','2016-03-31',1,12,61,9),(1445,'0','2016-03-31',1,12,61,10),(1446,'0','2016-03-31',1,12,61,11),(1447,'32.83','2016-03-31',1,12,76,12),(1448,'3','2016-03-31',1,12,91,13),(1449,'0','2016-03-31',1,12,91,14),(1450,'4.26','2016-03-31',1,12,106,15),(1451,'9.81','2016-03-31',1,12,106,16),(1452,'0','2016-03-31',1,12,106,17),(1453,'-5','2016-03-31',1,12,121,18),(1454,'-2','2016-03-31',1,12,121,19),(1455,'-6','2016-03-31',1,12,121,20),(1456,'3','2016-03-31',1,12,121,21),(1457,'94','2016-03-31',1,12,136,22),(1458,'0','2016-03-31',1,12,136,23),(1459,'0','2016-03-31',1,12,136,24),(1460,'71','2016-03-31',1,12,136,25),(1461,'0','2016-03-31',1,12,151,26),(1462,'0','2016-03-31',1,12,151,27),(1463,'0','2016-03-31',1,12,151,28),(1464,'0','2016-03-31',1,12,151,29),(1465,'0','2016-03-31',1,12,166,30),(1466,'0','2016-03-31',1,12,166,31),(1467,'42','2016-03-31',1,12,166,32),(1468,'0','2016-03-31',1,12,166,33),(1469,'0','2016-03-31',1,12,166,34),(1470,'5','2016-03-31',1,12,166,35),(1471,'0','2016-03-31',1,13,1,1),(1472,'0','2016-03-31',1,13,16,2),(1473,'0','2016-03-31',1,13,16,3),(1474,'0','2016-03-31',1,13,16,4),(1475,'0.08','2016-03-31',1,13,31,5),(1476,'9','2016-03-31',1,13,46,6),(1477,'0','2016-03-31',1,13,46,7),(1478,'1.36','2016-03-31',1,13,61,8),(1479,'46','2016-03-31',1,13,61,9),(1480,'0','2016-03-31',1,13,61,10),(1481,'0','2016-03-31',1,13,61,11),(1482,'7.88','2016-03-31',1,13,76,12),(1483,'3','2016-03-31',1,13,91,13),(1484,'0','2016-03-31',1,13,91,14),(1485,'4.26','2016-03-31',1,13,106,15),(1486,'9.81','2016-03-31',1,13,106,16),(1487,'0','2016-03-31',1,13,106,17),(1488,'-3','2016-03-31',1,13,121,18),(1489,'-2','2016-03-31',1,13,121,19),(1490,'-4','2016-03-31',1,13,121,20),(1491,'47','2016-03-31',1,13,121,21),(1492,'80','2016-03-31',1,13,136,22),(1493,'0','2016-03-31',1,13,136,23),(1494,'0','2016-03-31',1,13,136,24),(1495,'71','2016-03-31',1,13,136,25),(1496,'0','2016-03-31',1,13,151,26),(1497,'0','2016-03-31',1,13,151,27),(1498,'0','2016-03-31',1,13,151,28),(1499,'0','2016-03-31',1,13,151,29),(1500,'1','2016-03-31',1,13,166,30),(1501,'0','2016-03-31',1,13,166,31),(1502,'42','2016-03-31',1,13,166,32),(1503,'0','2016-03-31',1,13,166,33),(1504,'0','2016-03-31',1,13,166,34),(1505,'5','2016-03-31',1,13,166,35),(1506,'0','2016-03-31',1,14,1,1),(1507,'0','2016-03-31',1,14,16,2),(1508,'0','2016-03-31',1,14,16,3),(1509,'0','2016-03-31',1,14,16,4),(1510,'0.51','2016-03-31',1,14,31,5),(1511,'9','2016-03-31',1,14,46,6),(1512,'0','2016-03-31',1,14,46,7),(1513,'1.1','2016-03-31',1,14,61,8),(1514,'46','2016-03-31',1,14,61,9),(1515,'0','2016-03-31',1,14,61,10),(1516,'0','2016-03-31',1,14,61,11),(1517,'1.28','2016-03-31',1,14,76,12),(1518,'3','2016-03-31',1,14,91,13),(1519,'0','2016-03-31',1,14,91,14),(1520,'4.26','2016-03-31',1,14,106,15),(1521,'9.81','2016-03-31',1,14,106,16),(1522,'0','2016-03-31',1,14,106,17),(1523,'-12','2016-03-31',1,14,121,18),(1524,'-33','2016-03-31',1,14,121,19),(1525,'-8','2016-03-31',1,14,121,20),(1526,'-19','2016-03-31',1,14,121,21),(1527,'100','2016-03-31',1,14,136,22),(1528,'0','2016-03-31',1,14,136,23),(1529,'0','2016-03-31',1,14,136,24),(1530,'71','2016-03-31',1,14,136,25),(1531,'0','2016-03-31',1,14,151,26),(1532,'0','2016-03-31',1,14,151,27),(1533,'0','2016-03-31',1,14,151,28),(1534,'0','2016-03-31',1,14,151,29),(1535,'0','2016-03-31',1,14,166,30),(1536,'0','2016-03-31',1,14,166,31),(1537,'42','2016-03-31',1,14,166,32),(1538,'0','2016-03-31',1,14,166,33),(1539,'0','2016-03-31',1,14,166,34),(1540,'5','2016-03-31',1,14,166,35),(1541,'0','2016-03-31',1,15,1,1),(1542,'0','2016-03-31',1,15,16,2),(1543,'0','2016-03-31',1,15,16,3),(1544,'0','2016-03-31',1,15,16,4),(1545,'0','2016-03-31',1,15,31,5),(1546,'9','2016-03-31',1,15,46,6),(1547,'0','2016-03-31',1,15,46,7),(1548,'1.45','2016-03-31',1,15,61,8),(1549,'46','2016-03-31',1,15,61,9),(1550,'0','2016-03-31',1,15,61,10),(1551,'0','2016-03-31',1,15,61,11),(1552,'0.22','2016-03-31',1,15,76,12),(1553,'3','2016-03-31',1,15,91,13),(1554,'0','2016-03-31',1,15,91,14),(1555,'4.26','2016-03-31',1,15,106,15),(1556,'9.81','2016-03-31',1,15,106,16),(1557,'4265','2016-03-31',1,15,106,17),(1558,'1','2016-03-31',1,15,121,18),(1559,'13','2016-03-31',1,15,121,19),(1560,'-3','2016-03-31',1,15,121,20),(1561,'13','2016-03-31',1,15,121,21),(1562,'0','2016-03-31',1,15,136,22),(1563,'1','2016-03-31',1,15,136,23),(1564,'0','2016-03-31',1,15,136,24),(1565,'71','2016-03-31',1,15,136,25),(1566,'0','2016-03-31',1,15,151,26),(1567,'0','2016-03-31',1,15,151,27),(1568,'0','2016-03-31',1,15,151,28),(1569,'0','2016-03-31',1,15,151,29),(1570,'0','2016-03-31',1,15,166,30),(1571,'0','2016-03-31',1,15,166,31),(1572,'42','2016-03-31',1,15,166,32),(1573,'0','2016-03-31',1,15,166,33),(1574,'0','2016-03-31',1,15,166,34),(1575,'5','2016-03-31',1,15,166,35),(1576,'0','2016-04-30',1,1,1,1),(1577,'0','2016-04-30',1,1,16,2),(1578,'0','2016-04-30',1,1,16,3),(1579,'0','2016-04-30',1,1,16,4),(1580,'1.20','2016-04-30',1,1,31,5),(1581,'10','2016-04-30',1,1,46,6),(1582,'0','2016-04-30',1,1,46,7),(1583,'1.42','2016-04-30',1,1,61,8),(1584,'32','2016-04-30',1,1,61,9),(1585,'0','2016-04-30',1,1,61,10),(1586,'0','2016-04-30',1,1,61,11),(1587,'14.58','2016-04-30',1,1,76,12),(1588,'3','2016-04-30',1,1,91,13),(1589,'0','2016-04-30',1,1,91,14),(1590,'8.47','2016-04-30',1,1,106,15),(1591,'12.70','2016-04-30',1,1,106,16),(1592,'10000','2016-04-30',1,1,106,17),(1593,'-16','2016-04-30',1,1,121,18),(1594,'-47','2016-04-30',1,1,121,19),(1595,'-8','2016-04-30',1,1,121,20),(1596,'-58','2016-04-30',1,1,121,21),(1597,'95','2016-04-30',1,1,136,22),(1598,'0','2016-04-30',1,1,136,23),(1599,'0','2016-04-30',1,1,136,24),(1600,'60','2016-04-30',1,1,136,25),(1601,'0','2016-04-30',1,1,151,26),(1602,'0','2016-04-30',1,1,151,27),(1603,'0','2016-04-30',1,1,151,28),(1604,'0','2016-04-30',1,1,151,29),(1605,'0','2016-04-30',1,1,166,30),(1606,'0','2016-04-30',1,1,166,31),(1607,'100','2016-04-30',1,1,166,32),(1608,'0','2016-04-30',1,1,166,33),(1609,'0','2016-04-30',1,1,166,34),(1610,'4','2016-04-30',1,1,166,35),(1611,'0','2016-04-30',1,2,1,1),(1612,'0','2016-04-30',1,2,16,2),(1613,'0','2016-04-30',1,2,16,3),(1614,'0','2016-04-30',1,2,16,4),(1615,'1','2016-04-30',1,2,31,5),(1616,'10','2016-04-30',1,2,46,6),(1617,'0','2016-04-30',1,2,46,7),(1618,'1.3','2016-04-30',1,2,61,8),(1619,'32','2016-04-30',1,2,61,9),(1620,'0','2016-04-30',1,2,61,10),(1621,'0','2016-04-30',1,2,61,11),(1622,'18','2016-04-30',1,2,76,12),(1623,'3','2016-04-30',1,2,91,13),(1624,'0','2016-04-30',1,2,91,14),(1625,'8','2016-04-30',1,2,106,15),(1626,'13','2016-04-30',1,2,106,16),(1627,'10000','2016-04-30',1,2,106,17),(1628,'-8','2016-04-30',1,2,121,18),(1629,'-3','2016-04-30',1,2,121,19),(1630,'-6','2016-04-30',1,2,121,20),(1631,'-48','2016-04-30',1,2,121,21),(1632,'89','2016-04-30',1,2,136,22),(1633,'0','2016-04-30',1,2,136,23),(1634,'0','2016-04-30',1,2,136,24),(1635,'60','2016-04-30',1,2,136,25),(1636,'0','2016-04-30',1,2,151,26),(1637,'0','2016-04-30',1,2,151,27),(1638,'0','2016-04-30',1,2,151,28),(1639,'0','2016-04-30',1,2,151,29),(1640,'0','2016-04-30',1,2,166,30),(1641,'0','2016-04-30',1,2,166,31),(1642,'100','2016-04-30',1,2,166,32),(1643,'0','2016-04-30',1,2,166,33),(1644,'0','2016-04-30',1,2,166,34),(1645,'5','2016-04-30',1,2,166,35),(1646,'0','2016-04-30',1,4,1,1),(1647,'0','2016-04-30',1,4,16,2),(1648,'0','2016-04-30',1,4,16,3),(1649,'0','2016-04-30',1,4,16,4),(1650,'0','2016-04-30',1,4,31,5),(1651,'10','2016-04-30',1,4,46,6),(1652,'0','2016-04-30',1,4,46,7),(1653,'1.42','2016-04-30',1,4,61,8),(1654,'32','2016-04-30',1,4,61,9),(1655,'1','2016-04-30',1,4,61,10),(1656,'0','2016-04-30',1,4,61,11),(1657,'6','2016-04-30',1,4,76,12),(1658,'3','2016-04-30',1,4,91,13),(1659,'0','2016-04-30',1,4,91,14),(1660,'8.47','2016-04-30',1,4,106,15),(1661,'12.7','2016-04-30',1,4,106,16),(1662,'0','2016-04-30',1,4,106,17),(1663,'-2','2016-04-30',1,4,121,18),(1664,'24','2016-04-30',1,4,121,19),(1665,'-10','2016-04-30',1,4,121,20),(1666,'-29','2016-04-30',1,4,121,21),(1667,'89','2016-04-30',1,4,136,22),(1668,'0','2016-04-30',1,4,136,23),(1669,'0','2016-04-30',1,4,136,24),(1670,'60','2016-04-30',1,4,136,25),(1671,'0','2016-04-30',1,4,151,26),(1672,'0','2016-04-30',1,4,151,27),(1673,'0','2016-04-30',1,4,151,28),(1674,'0','2016-04-30',1,4,151,29),(1675,'0','2016-04-30',1,4,166,30),(1676,'0','2016-04-30',1,4,166,31),(1677,'100','2016-04-30',1,4,166,32),(1678,'0','2016-04-30',1,4,166,33),(1679,'0','2016-04-30',1,4,166,34),(1680,'4','2016-04-30',1,4,166,35),(1681,'0','2016-04-30',1,3,1,1),(1682,'0','2016-04-30',1,3,16,2),(1683,'0','2016-04-30',1,3,16,3),(1684,'0','2016-04-30',1,3,16,4),(1685,'1','2016-04-30',1,3,31,5),(1686,'10','2016-04-30',1,3,46,6),(1687,'0','2016-04-30',1,3,46,7),(1688,'1.36','2016-04-30',1,3,61,8),(1689,'32','2016-04-30',1,3,61,9),(1690,'1','2016-04-30',1,3,61,10),(1691,'0','2016-04-30',1,3,61,11),(1692,'6','2016-04-30',1,3,76,12),(1693,'3','2016-04-30',1,3,91,13),(1694,'0','2016-04-30',1,3,91,14),(1695,'8.47','2016-04-30',1,3,106,15),(1696,'12.70','2016-04-30',1,3,106,16),(1697,'0','2016-04-30',1,3,106,17),(1698,'4','2016-04-30',1,3,121,18),(1699,'0','2016-04-30',1,3,121,19),(1700,'0','2016-04-30',1,3,121,20),(1701,'4','2016-04-30',1,3,121,21),(1702,'89','2016-04-30',1,3,136,22),(1703,'0','2016-04-30',1,3,136,23),(1704,'0','2016-04-30',1,3,136,24),(1705,'60','2016-04-30',1,3,136,25),(1706,'0','2016-04-30',1,3,151,26),(1707,'0','2016-04-30',1,3,151,27),(1708,'0','2016-04-30',1,3,151,28),(1709,'0','2016-04-30',1,3,151,29),(1710,'0','2016-04-30',1,3,166,30),(1711,'0','2016-04-30',1,3,166,31),(1712,'100','2016-04-30',1,3,166,32),(1713,'0','2016-04-30',1,3,166,33),(1714,'0','2016-04-30',1,3,166,34),(1715,'5','2016-04-30',1,3,166,35),(1716,'0','2016-04-30',1,14,1,1),(1717,'0','2016-04-30',1,14,16,2),(1718,'0','2016-04-30',1,14,16,3),(1719,'0','2016-04-30',1,14,16,4),(1720,'0','2016-04-30',1,14,31,5),(1721,'10','2016-04-30',1,14,46,6),(1722,'0','2016-04-30',1,14,46,7),(1723,'1.05','2016-04-30',1,14,61,8),(1724,'32','2016-04-30',1,14,61,9),(1725,'0','2016-04-30',1,14,61,10),(1726,'0','2016-04-30',1,14,61,11),(1727,'3','2016-04-30',1,14,76,12),(1728,'3','2016-04-30',1,14,91,13),(1729,'0','2016-04-30',1,14,91,14),(1730,'8.47','2016-04-30',1,14,106,15),(1731,'12.70','2016-04-30',1,14,106,16),(1732,'0','2016-04-30',1,14,106,17),(1733,'-10','2016-04-30',1,14,121,18),(1734,'-34','2016-04-30',1,14,121,19),(1735,'-5','2016-04-30',1,14,121,20),(1736,'2','2016-04-30',1,14,121,21),(1737,'95','2016-04-30',1,14,136,22),(1738,'0','2016-04-30',1,14,136,23),(1739,'0','2016-04-30',1,14,136,24),(1740,'60','2016-04-30',1,14,136,25),(1741,'0','2016-04-30',1,14,151,26),(1742,'0','2016-04-30',1,14,151,27),(1743,'0','2016-04-30',1,14,151,28),(1744,'0','2016-04-30',1,14,151,29),(1745,'0','2016-04-30',1,14,166,30),(1746,'0','2016-04-30',1,14,166,31),(1747,'100','2016-04-30',1,14,166,32),(1748,'0','2016-04-30',1,14,166,33),(1749,'0','2016-04-30',1,14,166,34),(1750,'5','2016-04-30',1,14,166,35),(1751,'0','2016-04-30',1,5,1,1),(1752,'0','2016-04-30',1,5,16,2),(1753,'0','2016-04-30',1,5,16,3),(1754,'0','2016-04-30',1,5,16,4),(1755,'2','2016-04-30',1,5,31,5),(1756,'10','2016-04-30',1,5,46,6),(1757,'1','2016-04-30',1,5,46,7),(1758,'1.29','2016-04-30',1,5,61,8),(1759,'32','2016-04-30',1,5,61,9),(1760,'1','2016-04-30',1,5,61,10),(1761,'0','2016-04-30',1,5,61,11),(1762,'7','2016-04-30',1,5,76,12),(1763,'3','2016-04-30',1,5,91,13),(1764,'0','2016-04-30',1,5,91,14),(1765,'8.47','2016-04-30',1,5,106,15),(1766,'12.70','2016-04-30',1,5,106,16),(1767,'2500','2016-04-30',1,5,106,17),(1768,'-10','2016-04-30',1,5,121,18),(1769,'-10','2016-04-30',1,5,121,19),(1770,'-11','2016-04-30',1,5,121,20),(1771,'-32','2016-04-30',1,5,121,21),(1772,'89','2016-04-30',1,5,136,22),(1773,'0','2016-04-30',1,5,136,23),(1774,'0','2016-04-30',1,5,136,24),(1775,'60','2016-04-30',1,5,136,25),(1776,'0','2016-04-30',1,5,151,26),(1777,'0','2016-04-30',1,5,151,27),(1778,'0','2016-04-30',1,5,151,28),(1779,'0','2016-04-30',1,5,151,29),(1780,'0','2016-04-30',1,5,166,30),(1781,'0','2016-04-30',1,5,166,31),(1782,'100','2016-04-30',1,5,166,32),(1783,'0','2016-04-30',1,5,166,33),(1784,'0','2016-04-30',1,5,166,34),(1785,'4','2016-04-30',1,5,166,35),(1786,'0','2016-04-30',1,6,1,1),(1787,'0','2016-04-30',1,6,16,2),(1788,'0','2016-04-30',1,6,16,3),(1789,'0','2016-04-30',1,6,16,4),(1790,'2','2016-04-30',1,6,31,5),(1791,'10','2016-04-30',1,6,46,6),(1792,'0','2016-04-30',1,6,46,7),(1793,'1.51','2016-04-30',1,6,61,8),(1794,'32','2016-04-30',1,6,61,9),(1795,'0','2016-04-30',1,6,61,10),(1796,'0','2016-04-30',1,6,61,11),(1797,'21','2016-04-30',1,6,76,12),(1798,'3','2016-04-30',1,6,91,13),(1799,'0','2016-04-30',1,6,91,14),(1800,'8.47','2016-04-30',1,6,106,15),(1801,'12.70','2016-04-30',1,6,106,16),(1802,'0','2016-04-30',1,6,106,17),(1803,'-5','2016-04-30',1,6,121,18),(1804,'1','2016-04-30',1,6,121,19),(1805,'-8','2016-04-30',1,6,121,20),(1806,'41','2016-04-30',1,6,121,21),(1807,'89','2016-04-30',1,6,136,22),(1808,'0','2016-04-30',1,6,136,23),(1809,'0','2016-04-30',1,6,136,24),(1810,'60','2016-04-30',1,6,136,25),(1811,'0','2016-04-30',1,6,151,26),(1812,'0','2016-04-30',1,6,151,27),(1813,'0','2016-04-30',1,6,151,28),(1814,'0','2016-04-30',1,6,151,29),(1815,'0','2016-04-30',1,6,166,30),(1816,'0','2016-04-30',1,6,166,31),(1817,'100','2016-04-30',1,6,166,32),(1818,'0','2016-04-30',1,6,166,33),(1819,'0','2016-04-30',1,6,166,34),(1820,'5','2016-04-30',1,6,166,35),(1821,'0','2016-04-30',1,12,1,1),(1822,'0','2016-04-30',1,12,16,2),(1823,'0','2016-04-30',1,12,16,3),(1824,'0','2016-04-30',1,12,16,4),(1825,'0','2016-04-30',1,12,31,5),(1826,'10','2016-04-30',1,12,46,6),(1827,'0','2016-04-30',1,12,46,7),(1828,'1.67','2016-04-30',1,12,61,8),(1829,'32','2016-04-30',1,12,61,9),(1830,'0','2016-04-30',1,12,61,10),(1831,'0','2016-04-30',1,12,61,11),(1832,'15','2016-04-30',1,12,76,12),(1833,'3','2016-04-30',1,12,91,13),(1834,'0','2016-04-30',1,12,91,14),(1835,'8.47','2016-04-30',1,12,106,15),(1836,'12.70','2016-04-30',1,12,106,16),(1837,'13685','2016-04-30',1,12,106,17),(1838,'-8','2016-04-30',1,12,121,18),(1839,'-25','2016-04-30',1,12,121,19),(1840,'-4','2016-04-30',1,12,121,20),(1841,'-66','2016-04-30',1,12,121,21),(1842,'94','2016-04-30',1,12,136,22),(1843,'0','2016-04-30',1,12,136,23),(1844,'0','2016-04-30',1,12,136,24),(1845,'60','2016-04-30',1,12,136,25),(1846,'0','2016-04-30',1,12,151,26),(1847,'0','2016-04-30',1,12,151,27),(1848,'0','2016-04-30',1,12,151,28),(1849,'0','2016-04-30',1,12,151,29),(1850,'0','2016-04-30',1,12,166,30),(1851,'0','2016-04-30',1,12,166,31),(1852,'100','2016-04-30',1,12,166,32),(1853,'0','2016-04-30',1,12,166,33),(1854,'0','2016-04-30',1,12,166,34),(1855,'5','2016-04-30',1,12,166,35),(1856,'0','2016-04-30',1,15,1,1),(1857,'0','2016-04-30',1,15,16,2),(1858,'0','2016-04-30',1,15,16,3),(1859,'0','2016-04-30',1,15,16,4),(1860,'0','2016-04-30',1,15,31,5),(1861,'10','2016-04-30',1,15,46,6),(1862,'0','2016-04-30',1,15,46,7),(1863,'1.1','2016-04-30',1,15,61,8),(1864,'32','2016-04-30',1,15,61,9),(1865,'0','2016-04-30',1,15,61,10),(1866,'0','2016-04-30',1,15,61,11),(1867,'1','2016-04-30',1,15,76,12),(1868,'3','2016-04-30',1,15,91,13),(1869,'0','2016-04-30',1,15,91,14),(1870,'8.47','2016-04-30',1,15,106,15),(1871,'12.70','2016-04-30',1,15,106,16),(1872,'0','2016-04-30',1,15,106,17),(1873,'2','2016-04-30',1,15,121,18),(1874,'15','2016-04-30',1,15,121,19),(1875,'-1','2016-04-30',1,15,121,20),(1876,'44','2016-04-30',1,15,121,21),(1877,'95','2016-04-30',1,15,136,22),(1878,'1','2016-04-30',1,15,136,23),(1879,'0','2016-04-30',1,15,136,24),(1880,'60','2016-04-30',1,15,136,25),(1881,'0','2016-04-30',1,15,151,26),(1882,'0','2016-04-30',1,15,151,27),(1883,'0','2016-04-30',1,15,151,28),(1884,'0','2016-04-30',1,15,151,29),(1885,'0','2016-04-30',1,15,166,30),(1886,'0','2016-04-30',1,15,166,31),(1887,'100','2016-04-30',1,15,166,32),(1888,'0','2016-04-30',1,15,166,33),(1889,'0','2016-04-30',1,15,166,34),(1890,'5','2016-04-30',1,15,166,35),(1891,'0','2016-04-30',1,7,1,1),(1892,'0','2016-04-30',1,7,16,2),(1893,'0','2016-04-30',1,7,16,3),(1894,'0','2016-04-30',1,7,16,4),(1895,'0','2016-04-30',1,7,31,5),(1896,'10','2016-04-30',1,7,46,6),(1897,'0','2016-04-30',1,7,46,7),(1898,'1.46','2016-04-30',1,7,61,8),(1899,'32','2016-04-30',1,7,61,9),(1900,'0','2016-04-30',1,7,61,10),(1901,'0','2016-04-30',1,7,61,11),(1902,'11','2016-04-30',1,7,76,12),(1903,'3','2016-04-30',1,7,91,13),(1904,'0','2016-04-30',1,7,91,14),(1905,'8.47','2016-04-30',1,7,106,15),(1906,'12.70','2016-04-30',1,7,106,16),(1907,'0','2016-04-30',1,7,106,17),(1908,'-18','2016-04-30',1,7,121,18),(1909,'-79','2016-04-30',1,7,121,19),(1910,'-1','2016-04-30',1,7,121,20),(1911,'12','2016-04-30',1,7,121,21),(1912,'78','2016-04-30',1,7,136,22),(1913,'0','2016-04-30',1,7,136,23),(1914,'0','2016-04-30',1,7,136,24),(1915,'60','2016-04-30',1,7,136,25),(1916,'0','2016-04-30',1,7,151,26),(1917,'0','2016-04-30',1,7,151,27),(1918,'0','2016-04-30',1,7,151,28),(1919,'0','2016-04-30',1,7,151,29),(1920,'0','2016-04-30',1,7,166,30),(1921,'0','2016-04-30',1,7,166,31),(1922,'100','2016-04-30',1,7,166,32),(1923,'0','2016-04-30',1,7,166,33),(1924,'0','2016-04-30',1,7,166,34),(1925,'5','2016-04-30',1,7,166,35),(1926,'0','2016-04-30',1,8,1,1),(1927,'0','2016-04-30',1,8,16,2),(1928,'0','2016-04-30',1,8,16,3),(1929,'0','2016-04-30',1,8,16,4),(1930,'0','2016-04-30',1,8,31,5),(1931,'10','2016-04-30',1,8,46,6),(1932,'0','2016-04-30',1,8,46,7),(1933,'1.44','2016-04-30',1,8,61,8),(1934,'32','2016-04-30',1,8,61,9),(1935,'0','2016-04-30',1,8,61,10),(1936,'0','2016-04-30',1,8,61,11),(1937,'15','2016-04-30',1,8,76,12),(1938,'3','2016-04-30',1,8,91,13),(1939,'0','2016-04-30',1,8,91,14),(1940,'8.47','2016-04-30',1,8,106,15),(1941,'12.70','2016-04-30',1,8,106,16),(1942,'0','2016-04-30',1,8,106,17),(1943,'-19','2016-04-30',1,8,121,18),(1944,'-52','2016-04-30',1,8,121,19),(1945,'-10','2016-04-30',1,8,121,20),(1946,'-94','2016-04-30',1,8,121,21),(1947,'89','2016-04-30',1,8,136,22),(1948,'0','2016-04-30',1,8,136,23),(1949,'0','2016-04-30',1,8,136,24),(1950,'60','2016-04-30',1,8,136,25),(1951,'0','2016-04-30',1,8,151,26),(1952,'0','2016-04-30',1,8,151,27),(1953,'0','2016-04-30',1,8,151,28),(1954,'0','2016-04-30',1,8,151,29),(1955,'0','2016-04-30',1,8,166,30),(1956,'0','2016-04-30',1,8,166,31),(1957,'100','2016-04-30',1,8,166,32),(1958,'0','2016-04-30',1,8,166,33),(1959,'0','2016-04-30',1,8,166,34),(1960,'5','2016-04-30',1,8,166,35),(1961,'0','2016-04-30',1,9,1,1),(1962,'0','2016-04-30',1,9,16,2),(1963,'0','2016-04-30',1,9,16,3),(1964,'0','2016-04-30',1,9,16,4),(1965,'0','2016-04-30',1,9,31,5),(1966,'10','2016-04-30',1,9,46,6),(1967,'0','2016-04-30',1,9,46,7),(1968,'0.94','2016-04-30',1,9,61,8),(1969,'32','2016-04-30',1,9,61,9),(1970,'0','2016-04-30',1,9,61,10),(1971,'0','2016-04-30',1,9,61,11),(1972,'17','2016-04-30',1,9,76,12),(1973,'3','2016-04-30',1,9,91,13),(1974,'0','2016-04-30',1,9,91,14),(1975,'8.47','2016-04-30',1,9,106,15),(1976,'12.70','2016-04-30',1,9,106,16),(1977,'0','2016-04-30',1,9,106,17),(1978,'-10','2016-04-30',1,9,121,18),(1979,'-6','2016-04-30',1,9,121,19),(1980,'-11','2016-04-30',1,9,121,20),(1981,'-46','2016-04-30',1,9,121,21),(1982,'79','2016-04-30',1,9,136,22),(1983,'0','2016-04-30',1,9,136,23),(1984,'0','2016-04-30',1,9,136,24),(1985,'60','2016-04-30',1,9,136,25),(1986,'0','2016-04-30',1,9,151,26),(1987,'0','2016-04-30',1,9,151,27),(1988,'0','2016-04-30',1,9,151,28),(1989,'0','2016-04-30',1,9,151,29),(1990,'0','2016-04-30',1,9,166,30),(1991,'0','2016-04-30',1,9,166,31),(1992,'100','2016-04-30',1,9,166,32),(1993,'0','2016-04-30',1,9,166,33),(1994,'0','2016-04-30',1,9,166,34),(1995,'4','2016-04-30',1,9,166,35),(1996,'0','2016-04-30',1,10,1,1),(1997,'0','2016-04-30',1,10,16,2),(1998,'0','2016-04-30',1,10,16,3),(1999,'0','2016-04-30',1,10,16,4),(2000,'2','2016-04-30',1,10,31,5),(2001,'10','2016-04-30',1,10,46,6),(2002,'0','2016-04-30',1,10,46,7),(2003,'1.34','2016-04-30',1,10,61,8),(2004,'32','2016-04-30',1,10,61,9),(2005,'0','2016-04-30',1,10,61,10),(2006,'0','2016-04-30',1,10,61,11),(2007,'9','2016-04-30',1,10,76,12),(2008,'3','2016-04-30',1,10,91,13),(2009,'0','2016-04-30',1,10,91,14),(2010,'8.47','2016-04-30',1,10,106,15),(2011,'12.70','2016-04-30',1,10,106,16),(2012,'0','2016-04-30',1,10,106,17),(2013,'-4','2016-04-30',1,10,121,18),(2014,'0','2016-04-30',1,10,121,19),(2015,'-6','2016-04-30',1,10,121,20),(2016,'-161','2016-04-30',1,10,121,21),(2017,'100','2016-04-30',1,10,136,22),(2018,'0','2016-04-30',1,10,136,23),(2019,'1','2016-04-30',1,10,136,24),(2020,'60','2016-04-30',1,10,136,25),(2021,'0','2016-04-30',1,10,151,26),(2022,'0','2016-04-30',1,10,151,27),(2023,'0','2016-04-30',1,10,151,28),(2024,'0','2016-04-30',1,10,151,29),(2025,'0','2016-04-30',1,10,166,30),(2026,'0','2016-04-30',1,10,166,31),(2027,'100','2016-04-30',1,10,166,32),(2028,'0','2016-04-30',1,10,166,33),(2029,'0','2016-04-30',1,10,166,34),(2030,'5','2016-04-30',1,10,166,35),(2031,'0','2016-04-30',1,11,1,1),(2032,'0','2016-04-30',1,11,16,2),(2033,'0','2016-04-30',1,11,16,3),(2034,'0','2016-04-30',1,11,16,4),(2035,'0','2016-04-30',1,11,31,5),(2036,'10','2016-04-30',1,11,46,6),(2037,'0','2016-04-30',1,11,46,7),(2038,'1.47','2016-04-30',1,11,61,8),(2039,'32','2016-04-30',1,11,61,9),(2040,'0','2016-04-30',1,11,61,10),(2041,'0','2016-04-30',1,11,61,11),(2042,'24','2016-04-30',1,11,76,12),(2043,'3','2016-04-30',1,11,91,13),(2044,'0','2016-04-30',1,11,91,14),(2045,'8.47','2016-04-30',1,11,106,15),(2046,'12.70','2016-04-30',1,11,106,16),(2047,'0','2016-04-30',1,11,106,17),(2048,'-6','2016-04-30',1,11,121,18),(2049,'-3','2016-04-30',1,11,121,19),(2050,'-8','2016-04-30',1,11,121,20),(2051,'32','2016-04-30',1,11,121,21),(2052,'94','2016-04-30',1,11,136,22),(2053,'0','2016-04-30',1,11,136,23),(2054,'0','2016-04-30',1,11,136,24),(2055,'60','2016-04-30',1,11,136,25),(2056,'0','2016-04-30',1,11,151,26),(2057,'0','2016-04-30',1,11,151,27),(2058,'0','2016-04-30',1,11,151,28),(2059,'0','2016-04-30',1,11,151,29),(2060,'0','2016-04-30',1,11,166,30),(2061,'0','2016-04-30',1,11,166,31),(2062,'100','2016-04-30',1,11,166,32),(2063,'0','2016-04-30',1,11,166,33),(2064,'0','2016-04-30',1,11,166,34),(2065,'5','2016-04-30',1,11,166,35),(2066,'0','2016-04-30',1,13,1,1),(2067,'0','2016-04-30',1,13,16,2),(2068,'0','2016-04-30',1,13,16,3),(2069,'0','2016-04-30',1,13,16,4),(2070,'0','2016-04-30',1,13,31,5),(2071,'10','2016-04-30',1,13,46,6),(2072,'0','2016-04-30',1,13,46,7),(2073,'1.24','2016-04-30',1,13,61,8),(2074,'32','2016-04-30',1,13,61,9),(2075,'0','2016-04-30',1,13,61,10),(2076,'0','2016-04-30',1,13,61,11),(2077,'4','2016-04-30',1,13,76,12),(2078,'3','2016-04-30',1,13,91,13),(2079,'0','2016-04-30',1,13,91,14),(2080,'8.47','2016-04-30',1,13,106,15),(2081,'12.70','2016-04-30',1,13,106,16),(2082,'0','2016-04-30',1,13,106,17),(2083,'2','2016-04-30',1,13,121,18),(2084,'14','2016-04-30',1,13,121,19),(2085,'-2','2016-04-30',1,13,121,20),(2086,'32','2016-04-30',1,13,121,21),(2087,'83','2016-04-30',1,13,136,22),(2088,'0','2016-04-30',1,13,136,23),(2089,'0','2016-04-30',1,13,136,24),(2090,'60','2016-04-30',1,13,136,25),(2091,'0','2016-04-30',1,13,151,26),(2092,'0','2016-04-30',1,13,151,27),(2093,'0','2016-04-30',1,13,151,28),(2094,'0','2016-04-30',1,13,151,29),(2095,'0','2016-04-30',1,13,166,30),(2096,'0','2016-04-30',1,13,166,31),(2097,'100','2016-04-30',1,13,166,32),(2098,'0','2016-04-30',1,13,166,33),(2099,'0','2016-04-30',1,13,166,34),(2100,'5','2016-04-30',1,13,166,35),(2101,'0','2016-05-31',1,1,1,1),(2102,'0','2016-05-31',1,1,16,2),(2103,'0','2016-05-31',1,1,16,3),(2104,'0','2016-05-31',1,1,16,4),(2105,'-1.51','2016-05-31',1,1,31,5),(2106,'9','2016-05-31',1,1,46,6),(2107,'0','2016-05-31',1,1,46,7),(2108,'1.3','2016-05-31',1,1,61,8),(2109,'26','2016-05-31',1,1,61,9),(2110,'0','2016-05-31',1,1,61,10),(2111,'0','2016-05-31',1,1,61,11),(2112,'12.18','2016-05-31',1,1,76,12),(2113,'3','2016-05-31',1,1,91,13),(2114,'0','2016-05-31',1,1,91,14),(2115,'3.37','2016-05-31',1,1,106,15),(2116,'6.70','2016-05-31',1,1,106,16),(2117,'0','2016-05-31',1,1,106,17),(2118,'-17','2016-05-31',1,1,121,18),(2119,'-55','2016-05-31',1,1,121,19),(2120,'-6','2016-05-31',1,1,121,20),(2121,'-65','2016-05-31',1,1,121,21),(2122,'77','2016-05-31',1,1,136,22),(2123,'0','2016-05-31',1,1,136,23),(2124,'0','2016-05-31',1,1,136,24),(2125,'63','2016-05-31',1,1,136,25),(2126,'0','2016-05-31',1,1,151,26),(2127,'0','2016-05-31',1,1,151,27),(2128,'0','2016-05-31',1,1,151,28),(2129,'0','2016-05-31',1,1,151,29),(2130,'0','2016-05-31',1,1,166,30),(2131,'0','2016-05-31',1,1,166,31),(2132,'75','2016-05-31',1,1,166,32),(2133,'0','2016-05-31',1,1,166,33),(2134,'0','2016-05-31',1,1,166,34),(2135,'4','2016-05-31',1,1,166,35),(2136,'0','2016-05-31',1,2,1,1),(2137,'0','2016-05-31',1,2,16,2),(2138,'0','2016-05-31',1,2,16,3),(2139,'0','2016-05-31',1,2,16,4),(2140,'-1','2016-05-31',1,2,31,5),(2141,'9','2016-05-31',1,2,46,6),(2142,'0','2016-05-31',1,2,46,7),(2143,'1.19','2016-05-31',1,2,61,8),(2144,'26','2016-05-31',1,2,61,9),(2145,'0','2016-05-31',1,2,61,10),(2146,'0','2016-05-31',1,2,61,11),(2147,'22','2016-05-31',1,2,76,12),(2148,'3','2016-05-31',1,2,91,13),(2149,'0','2016-05-31',1,2,91,14),(2150,'3.37','2016-05-31',1,2,106,15),(2151,'6.70','2016-05-31',1,2,106,16),(2152,'0','2016-05-31',1,2,106,17),(2153,'-10','2016-05-31',1,2,121,18),(2154,'-13','2016-05-31',1,2,121,19),(2155,'-10','2016-05-31',1,2,121,20),(2156,'-55','2016-05-31',1,2,121,21),(2157,'95','2016-05-31',1,2,136,22),(2158,'0','2016-05-31',1,2,136,23),(2159,'0','2016-05-31',1,2,136,24),(2160,'63','2016-05-31',1,2,136,25),(2161,'0','2016-05-31',1,2,151,26),(2162,'0','2016-05-31',1,2,151,27),(2163,'0','2016-05-31',1,2,151,28),(2164,'0','2016-05-31',1,2,151,29),(2165,'0','2016-05-31',1,2,166,30),(2166,'0','2016-05-31',1,2,166,31),(2167,'75','2016-05-31',1,2,166,32),(2168,'0','2016-05-31',1,2,166,33),(2169,'0','2016-05-31',1,2,166,34),(2170,'5','2016-05-31',1,2,166,35),(2171,'0','2016-05-31',1,4,1,1),(2172,'0','2016-05-31',1,4,16,2),(2173,'0','2016-05-31',1,4,16,3),(2174,'0','2016-05-31',1,4,16,4),(2175,'0','2016-05-31',1,4,31,5),(2176,'9','2016-05-31',1,4,46,6),(2177,'0','2016-05-31',1,4,46,7),(2178,'1.28','2016-05-31',1,4,61,8),(2179,'26','2016-05-31',1,4,61,9),(2180,'1','2016-05-31',1,4,61,10),(2181,'0','2016-05-31',1,4,61,11),(2182,'4','2016-05-31',1,4,76,12),(2183,'3','2016-05-31',1,4,91,13),(2184,'0','2016-05-31',1,4,91,14),(2185,'3.37','2016-05-31',1,4,106,15),(2186,'6.70','2016-05-31',1,4,106,16),(2187,'0','2016-05-31',1,4,106,17),(2188,'-2','2016-05-31',1,4,121,18),(2189,'20','2016-05-31',1,4,121,19),(2190,'-9','2016-05-31',1,4,121,20),(2191,'-54','2016-05-31',1,4,121,21),(2192,'90','2016-05-31',1,4,136,22),(2193,'0','2016-05-31',1,4,136,23),(2194,'0','2016-05-31',1,4,136,24),(2195,'63','2016-05-31',1,4,136,25),(2196,'0','2016-05-31',1,4,151,26),(2197,'0','2016-05-31',1,4,151,27),(2198,'0','2016-05-31',1,4,151,28),(2199,'0','2016-05-31',1,4,151,29),(2200,'0','2016-05-31',1,4,166,30),(2201,'0','2016-05-31',1,4,166,31),(2202,'75','2016-05-31',1,4,166,32),(2203,'0','2016-05-31',1,4,166,33),(2204,'0','2016-05-31',1,4,166,34),(2205,'4','2016-05-31',1,4,166,35),(2206,'0','2016-05-31',1,3,1,1),(2207,'0','2016-05-31',1,3,16,2),(2208,'0','2016-05-31',1,3,16,3),(2209,'0','2016-05-31',1,3,16,4),(2210,'1','2016-05-31',1,3,31,5),(2211,'9','2016-05-31',1,3,46,6),(2212,'0','2016-05-31',1,3,46,7),(2213,'1.29','2016-05-31',1,3,61,8),(2214,'26','2016-05-31',1,3,61,9),(2215,'0','2016-05-31',1,3,61,10),(2216,'0','2016-05-31',1,3,61,11),(2217,'0.98','2016-05-31',1,3,76,12),(2218,'3','2016-05-31',1,3,91,13),(2219,'0','2016-05-31',1,3,91,14),(2220,'3.37','2016-05-31',1,3,106,15),(2221,'6.70','2016-05-31',1,3,106,16),(2222,'0','2016-05-31',1,3,106,17),(2223,'4','2016-05-31',1,3,121,18),(2224,'17','2016-05-31',1,3,121,19),(2225,'0','2016-05-31',1,3,121,20),(2226,'1','2016-05-31',1,3,121,21),(2227,'86','2016-05-31',1,3,136,22),(2228,'0','2016-05-31',1,3,136,23),(2229,'0','2016-05-31',1,3,136,24),(2230,'63','2016-05-31',1,3,136,25),(2231,'0','2016-05-31',1,3,151,26),(2232,'0','2016-05-31',1,3,151,27),(2233,'0','2016-05-31',1,3,151,28),(2234,'0','2016-05-31',1,3,151,29),(2235,'0','2016-05-31',1,3,166,30),(2236,'0','2016-05-31',1,3,166,31),(2237,'75','2016-05-31',1,3,166,32),(2238,'0','2016-05-31',1,3,166,33),(2239,'0','2016-05-31',1,3,166,34),(2240,'5','2016-05-31',1,3,166,35),(2241,'0','2016-05-31',1,14,1,1),(2242,'0','2016-05-31',1,14,16,2),(2243,'0','2016-05-31',1,14,16,3),(2244,'0','2016-05-31',1,14,16,4),(2245,'0','2016-05-31',1,14,31,5),(2246,'9','2016-05-31',1,14,46,6),(2247,'0','2016-05-31',1,14,46,7),(2248,'0.95','2016-05-31',1,14,61,8),(2249,'26','2016-05-31',1,14,61,9),(2250,'0','2016-05-31',1,14,61,10),(2251,'0','2016-05-31',1,14,61,11),(2252,'4','2016-05-31',1,14,76,12),(2253,'3','2016-05-31',1,14,91,13),(2254,'0','2016-05-31',1,14,91,14),(2255,'3.37','2016-05-31',1,14,106,15),(2256,'6.70','2016-05-31',1,14,106,16),(2257,'0','2016-05-31',1,14,106,17),(2258,'-4','2016-05-31',1,14,121,18),(2259,'-12','2016-05-31',1,14,121,19),(2260,'-3','2016-05-31',1,14,121,20),(2261,'24','2016-05-31',1,14,121,21),(2262,'100','2016-05-31',1,14,136,22),(2263,'0','2016-05-31',1,14,136,23),(2264,'0','2016-05-31',1,14,136,24),(2265,'63','2016-05-31',1,14,136,25),(2266,'0','2016-05-31',1,14,151,26),(2267,'0','2016-05-31',1,14,151,27),(2268,'0','2016-05-31',1,14,151,28),(2269,'0','2016-05-31',1,14,151,29),(2270,'0','2016-05-31',1,14,166,30),(2271,'0','2016-05-31',1,14,166,31),(2272,'75','2016-05-31',1,14,166,32),(2273,'0','2016-05-31',1,14,166,33),(2274,'0','2016-05-31',1,14,166,34),(2275,'5','2016-05-31',1,14,166,35),(2276,'0','2016-05-31',1,5,1,1),(2277,'0','2016-05-31',1,5,16,2),(2278,'0','2016-05-31',1,5,16,3),(2279,'0','2016-05-31',1,5,16,4),(2280,'-1.67','2016-05-31',1,5,31,5),(2281,'9','2016-05-31',1,5,46,6),(2282,'0','2016-05-31',1,5,46,7),(2283,'1.21','2016-05-31',1,5,61,8),(2284,'26','2016-05-31',1,5,61,9),(2285,'1','2016-05-31',1,5,61,10),(2286,'0','2016-05-31',1,5,61,11),(2287,'2','2016-05-31',1,5,76,12),(2288,'3','2016-05-31',1,5,91,13),(2289,'0','2016-05-31',1,5,91,14),(2290,'3.37','2016-05-31',1,5,106,15),(2291,'6.70','2016-05-31',1,5,106,16),(2292,'0','2016-05-31',1,5,106,17),(2293,'-8','2016-05-31',1,5,121,18),(2294,'-9','2016-05-31',1,5,121,19),(2295,'-9','2016-05-31',1,5,121,20),(2296,'-46','2016-05-31',1,5,121,21),(2297,'100','2016-05-31',1,5,136,22),(2298,'0','2016-05-31',1,5,136,23),(2299,'0','2016-05-31',1,5,136,24),(2300,'63','2016-05-31',1,5,136,25),(2301,'0','2016-05-31',1,5,151,26),(2302,'0','2016-05-31',1,5,151,27),(2303,'0','2016-05-31',1,5,151,28),(2304,'0','2016-05-31',1,5,151,29),(2305,'0','2016-05-31',1,5,166,30),(2306,'0','2016-05-31',1,5,166,31),(2307,'75','2016-05-31',1,5,166,32),(2308,'0','2016-05-31',1,5,166,33),(2309,'0','2016-05-31',1,5,166,34),(2310,'4','2016-05-31',1,5,166,35),(2311,'0','2016-05-31',1,6,1,1),(2312,'0','2016-05-31',1,6,16,2),(2313,'0','2016-05-31',1,6,16,3),(2314,'0','2016-05-31',1,6,16,4),(2315,'1','2016-05-31',1,6,31,5),(2316,'9','2016-05-31',1,6,46,6),(2317,'0','2016-05-31',1,6,46,7),(2318,'1.36','2016-05-31',1,6,61,8),(2319,'26','2016-05-31',1,6,61,9),(2320,'0','2016-05-31',1,6,61,10),(2321,'0','2016-05-31',1,6,61,11),(2322,'17','2016-05-31',1,6,76,12),(2323,'3','2016-05-31',1,6,91,13),(2324,'0','2016-05-31',1,6,91,14),(2325,'3.37','2016-05-31',1,6,106,15),(2326,'6.70','2016-05-31',1,6,106,16),(2327,'0','2016-05-31',1,6,106,17),(2328,'-2','2016-05-31',1,6,121,18),(2329,'14','2016-05-31',1,6,121,19),(2330,'-9','2016-05-31',1,6,121,20),(2331,'25','2016-05-31',1,6,121,21),(2332,'84','2016-05-31',1,6,136,22),(2333,'0','2016-05-31',1,6,136,23),(2334,'0','2016-05-31',1,6,136,24),(2335,'63','2016-05-31',1,6,136,25),(2336,'0','2016-05-31',1,6,151,26),(2337,'0','2016-05-31',1,6,151,27),(2338,'0','2016-05-31',1,6,151,28),(2339,'0','2016-05-31',1,6,151,29),(2340,'0','2016-05-31',1,6,166,30),(2341,'0','2016-05-31',1,6,166,31),(2342,'75','2016-05-31',1,6,166,32),(2343,'0','2016-05-31',1,6,166,33),(2344,'0','2016-05-31',1,6,166,34),(2345,'5','2016-05-31',1,6,166,35),(2346,'0','2016-05-31',1,12,1,1),(2347,'0','2016-05-31',1,12,16,2),(2348,'0','2016-05-31',1,12,16,3),(2349,'0','2016-05-31',1,12,16,4),(2350,'0','2016-05-31',1,12,31,5),(2351,'9','2016-05-31',1,12,46,6),(2352,'0','2016-05-31',1,12,46,7),(2353,'1.44','2016-05-31',1,12,61,8),(2354,'26','2016-05-31',1,12,61,9),(2355,'1','2016-05-31',1,12,61,10),(2356,'0','2016-05-31',1,12,61,11),(2357,'28','2016-05-31',1,12,76,12),(2358,'3','2016-05-31',1,12,91,13),(2359,'0','2016-05-31',1,12,91,14),(2360,'3.37','2016-05-31',1,12,106,15),(2361,'6.70','2016-05-31',1,12,106,16),(2362,'0','2016-05-31',1,12,106,17),(2363,'-5','2016-05-31',1,12,121,18),(2364,'-14','2016-05-31',1,12,121,19),(2365,'-3','2016-05-31',1,12,121,20),(2366,'-88','2016-05-31',1,12,121,21),(2367,'90','2016-05-31',1,12,136,22),(2368,'0','2016-05-31',1,12,136,23),(2369,'0','2016-05-31',1,12,136,24),(2370,'63','2016-05-31',1,12,136,25),(2371,'0','2016-05-31',1,12,151,26),(2372,'0','2016-05-31',1,12,151,27),(2373,'0','2016-05-31',1,12,151,28),(2374,'0','2016-05-31',1,12,151,29),(2375,'0','2016-05-31',1,12,166,30),(2376,'0','2016-05-31',1,12,166,31),(2377,'75','2016-05-31',1,12,166,32),(2378,'0','2016-05-31',1,12,166,33),(2379,'0','2016-05-31',1,12,166,34),(2380,'5','2016-05-31',1,12,166,35),(2381,'0','2016-05-31',1,15,1,1),(2382,'0','2016-05-31',1,15,16,2),(2383,'0','2016-05-31',1,15,16,3),(2384,'0','2016-05-31',1,15,16,4),(2385,'-1','2016-05-31',1,15,31,5),(2386,'9','2016-05-31',1,15,46,6),(2387,'0','2016-05-31',1,15,46,7),(2388,'1.05','2016-05-31',1,15,61,8),(2389,'26','2016-05-31',1,15,61,9),(2390,'0','2016-05-31',1,15,61,10),(2391,'0','2016-05-31',1,15,61,11),(2392,'0','2016-05-31',1,15,76,12),(2393,'3','2016-05-31',1,15,91,13),(2394,'0','2016-05-31',1,15,91,14),(2395,'3.37','2016-05-31',1,15,106,15),(2396,'6.70','2016-05-31',1,15,106,16),(2397,'0','2016-05-31',1,15,106,17),(2398,'-1','2016-05-31',1,15,121,18),(2399,'4','2016-05-31',1,15,121,19),(2400,'1','2016-05-31',1,15,121,20),(2401,'49','2016-05-31',1,15,121,21),(2402,'83','2016-05-31',1,15,136,22),(2403,'1','2016-05-31',1,15,136,23),(2404,'0','2016-05-31',1,15,136,24),(2405,'63','2016-05-31',1,15,136,25),(2406,'0','2016-05-31',1,15,151,26),(2407,'0','2016-05-31',1,15,151,27),(2408,'0','2016-05-31',1,15,151,28),(2409,'0','2016-05-31',1,15,151,29),(2410,'0','2016-05-31',1,15,166,30),(2411,'0','2016-05-31',1,15,166,31),(2412,'75','2016-05-31',1,15,166,32),(2413,'0','2016-05-31',1,15,166,33),(2414,'0','2016-05-31',1,15,166,34),(2415,'5','2016-05-31',1,15,166,35),(2416,'0','2016-05-31',1,7,1,1),(2417,'0','2016-05-31',1,7,16,2),(2418,'0','2016-05-31',1,7,16,3),(2419,'0','2016-05-31',1,7,16,4),(2420,'0','2016-05-31',1,7,31,5),(2421,'9','2016-05-31',1,7,46,6),(2422,'0','2016-05-31',1,7,46,7),(2423,'1.46','2016-05-31',1,7,61,8),(2424,'26','2016-05-31',1,7,61,9),(2425,'1','2016-05-31',1,7,61,10),(2426,'0','2016-05-31',1,7,61,11),(2427,'9','2016-05-31',1,7,76,12),(2428,'3','2016-05-31',1,7,91,13),(2429,'0','2016-05-31',1,7,91,14),(2430,'3.37','2016-05-31',1,7,106,15),(2431,'6.70','2016-05-31',1,7,106,16),(2432,'0','2016-05-31',1,7,106,17),(2433,'-14','2016-05-31',1,7,121,18),(2434,'-63','2016-05-31',1,7,121,19),(2435,'1','2016-05-31',1,7,121,20),(2436,'22','2016-05-31',1,7,121,21),(2437,'77','2016-05-31',1,7,136,22),(2438,'0','2016-05-31',1,7,136,23),(2439,'0','2016-05-31',1,7,136,24),(2440,'63','2016-05-31',1,7,136,25),(2441,'0','2016-05-31',1,7,151,26),(2442,'0','2016-05-31',1,7,151,27),(2443,'0','2016-05-31',1,7,151,28),(2444,'0','2016-05-31',1,7,151,29),(2445,'0','2016-05-31',1,7,166,30),(2446,'0','2016-05-31',1,7,166,31),(2447,'75','2016-05-31',1,7,166,32),(2448,'0','2016-05-31',1,7,166,33),(2449,'0','2016-05-31',1,7,166,34),(2450,'5','2016-05-31',1,7,166,35),(2451,'0','2016-05-31',1,8,1,1),(2452,'0','2016-05-31',1,8,16,2),(2453,'0','2016-05-31',1,8,16,3),(2454,'0','2016-05-31',1,8,16,4),(2455,'0','2016-05-31',1,8,31,5),(2456,'9','2016-05-31',1,8,46,6),(2457,'0','2016-05-31',1,8,46,7),(2458,'1.44','2016-05-31',1,8,61,8),(2459,'26','2016-05-31',1,8,61,9),(2460,'0','2016-05-31',1,8,61,10),(2461,'0','2016-05-31',1,8,61,11),(2462,'11','2016-05-31',1,8,76,12),(2463,'3','2016-05-31',1,8,91,13),(2464,'0','2016-05-31',1,8,91,14),(2465,'3.37','2016-05-31',1,8,106,15),(2466,'6.70','2016-05-31',1,8,106,16),(2467,'0','2016-05-31',1,8,106,17),(2468,'-16','2016-05-31',1,8,121,18),(2469,'-40','2016-05-31',1,8,121,19),(2470,'-9','2016-05-31',1,8,121,20),(2471,'-44','2016-05-31',1,8,121,21),(2472,'90','2016-05-31',1,8,136,22),(2473,'0','2016-05-31',1,8,136,23),(2474,'0','2016-05-31',1,8,136,24),(2475,'63','2016-05-31',1,8,136,25),(2476,'0','2016-05-31',1,8,151,26),(2477,'0','2016-05-31',1,8,151,27),(2478,'0','2016-05-31',1,8,151,28),(2479,'0','2016-05-31',1,8,151,29),(2480,'0','2016-05-31',1,8,166,30),(2481,'0','2016-05-31',1,8,166,31),(2482,'75','2016-05-31',1,8,166,32),(2483,'0','2016-05-31',1,8,166,33),(2484,'0','2016-05-31',1,8,166,34),(2485,'5','2016-05-31',1,8,166,35),(2486,'0','2016-05-31',1,9,1,1),(2487,'0','2016-05-31',1,9,16,2),(2488,'0','2016-05-31',1,9,16,3),(2489,'0','2016-05-31',1,9,16,4),(2490,'0','2016-05-31',1,9,31,5),(2491,'9','2016-05-31',1,9,46,6),(2492,'0','2016-05-31',1,9,46,7),(2493,'0.94','2016-05-31',1,9,61,8),(2494,'26','2016-05-31',1,9,61,9),(2495,'0','2016-05-31',1,9,61,10),(2496,'1','2016-05-31',1,9,61,11),(2497,'22','2016-05-31',1,9,76,12),(2498,'3','2016-05-31',1,9,91,13),(2499,'0','2016-05-31',1,9,91,14),(2500,'3.37','2016-05-31',1,9,106,15),(2501,'6.70','2016-05-31',1,9,106,16),(2502,'0','2016-05-31',1,9,106,17),(2503,'-8','2016-05-31',1,9,121,18),(2504,'-5','2016-05-31',1,9,121,19),(2505,'-10','2016-05-31',1,9,121,20),(2506,'-39','2016-05-31',1,9,121,21),(2507,'90','2016-05-31',1,9,136,22),(2508,'0','2016-05-31',1,9,136,23),(2509,'0','2016-05-31',1,9,136,24),(2510,'63','2016-05-31',1,9,136,25),(2511,'0','2016-05-31',1,9,151,26),(2512,'0','2016-05-31',1,9,151,27),(2513,'0','2016-05-31',1,9,151,28),(2514,'0','2016-05-31',1,9,151,29),(2515,'5','2016-05-31',1,9,166,30),(2516,'0','2016-05-31',1,9,166,31),(2517,'75','2016-05-31',1,9,166,32),(2518,'0','2016-05-31',1,9,166,33),(2519,'0','2016-05-31',1,9,166,34),(2520,'4','2016-05-31',1,9,166,35),(2521,'0','2016-05-31',1,10,1,1),(2522,'0','2016-05-31',1,10,16,2),(2523,'0','2016-05-31',1,10,16,3),(2524,'0','2016-05-31',1,10,16,4),(2525,'0','2016-05-31',1,10,31,5),(2526,'9','2016-05-31',1,10,46,6),(2527,'0','2016-05-31',1,10,46,7),(2528,'1.28','2016-05-31',1,10,61,8),(2529,'26','2016-05-31',1,10,61,9),(2530,'0','2016-05-31',1,10,61,10),(2531,'0','2016-05-31',1,10,61,11),(2532,'14','2016-05-31',1,10,76,12),(2533,'3','2016-05-31',1,10,91,13),(2534,'0','2016-05-31',1,10,91,14),(2535,'3.37','2016-05-31',1,10,106,15),(2536,'6.70','2016-05-31',1,10,106,16),(2537,'0','2016-05-31',1,10,106,17),(2538,'-5','2016-05-31',1,10,121,18),(2539,'-20','2016-05-31',1,10,121,19),(2540,'-6','2016-05-31',1,10,121,20),(2541,'-124','2016-05-31',1,10,121,21),(2542,'93','2016-05-31',1,10,136,22),(2543,'0','2016-05-31',1,10,136,23),(2544,'0','2016-05-31',1,10,136,24),(2545,'63','2016-05-31',1,10,136,25),(2546,'0','2016-05-31',1,10,151,26),(2547,'0','2016-05-31',1,10,151,27),(2548,'0','2016-05-31',1,10,151,28),(2549,'0','2016-05-31',1,10,151,29),(2550,'0','2016-05-31',1,10,166,30),(2551,'0','2016-05-31',1,10,166,31),(2552,'75','2016-05-31',1,10,166,32),(2553,'0','2016-05-31',1,10,166,33),(2554,'0','2016-05-31',1,10,166,34),(2555,'5','2016-05-31',1,10,166,35),(2556,'0','2016-05-31',1,11,1,1),(2557,'0','2016-05-31',1,11,16,2),(2558,'0','2016-05-31',1,11,16,3),(2559,'0','2016-05-31',1,11,16,4),(2560,'0','2016-05-31',1,11,31,5),(2561,'9','2016-05-31',1,11,46,6),(2562,'0','2016-05-31',1,11,46,7),(2563,'1.36','2016-05-31',1,11,61,8),(2564,'26','2016-05-31',1,11,61,9),(2565,'0','2016-05-31',1,11,61,10),(2566,'0','2016-05-31',1,11,61,11),(2567,'18','2016-05-31',1,11,76,12),(2568,'3','2016-05-31',1,11,91,13),(2569,'0','2016-05-31',1,11,91,14),(2570,'3.37','2016-05-31',1,11,106,15),(2571,'6.70','2016-05-31',1,11,106,16),(2572,'1500','2016-05-31',1,11,106,17),(2573,'-4','2016-05-31',1,11,121,18),(2574,'5','2016-05-31',1,11,121,19),(2575,'-8','2016-05-31',1,11,121,20),(2576,'22','2016-05-31',1,11,121,21),(2577,'95','2016-05-31',1,11,136,22),(2578,'0','2016-05-31',1,11,136,23),(2579,'0','2016-05-31',1,11,136,24),(2580,'63','2016-05-31',1,11,136,25),(2581,'0','2016-05-31',1,11,151,26),(2582,'0','2016-05-31',1,11,151,27),(2583,'0','2016-05-31',1,11,151,28),(2584,'0','2016-05-31',1,11,151,29),(2585,'0','2016-05-31',1,11,166,30),(2586,'0','2016-05-31',1,11,166,31),(2587,'75','2016-05-31',1,11,166,32),(2588,'0','2016-05-31',1,11,166,33),(2589,'0','2016-05-31',1,11,166,34),(2590,'5','2016-05-31',1,11,166,35),(2591,'0','2016-05-31',1,13,1,1),(2592,'0','2016-05-31',1,13,16,2),(2593,'0','2016-05-31',1,13,16,3),(2594,'0','2016-05-31',1,13,16,4),(2595,'0','2016-05-31',1,13,31,5),(2596,'9','2016-05-31',1,13,46,6),(2597,'0','2016-05-31',1,13,46,7),(2598,'1.24','2016-05-31',1,13,61,8),(2599,'26','2016-05-31',1,13,61,9),(2600,'0','2016-05-31',1,13,61,10),(2601,'0','2016-05-31',1,13,61,11),(2602,'0','2016-05-31',1,13,76,12),(2603,'3','2016-05-31',1,13,91,13),(2604,'0','2016-05-31',1,13,91,14),(2605,'3.37','2016-05-31',1,13,106,15),(2606,'6.70','2016-05-31',1,13,106,16),(2607,'0','2016-05-31',1,13,106,17),(2608,'2','2016-05-31',1,13,121,18),(2609,'12','2016-05-31',1,13,121,19),(2610,'-2','2016-05-31',1,13,121,20),(2611,'11','2016-05-31',1,13,121,21),(2612,'85','2016-05-31',1,13,136,22),(2613,'0','2016-05-31',1,13,136,23),(2614,'0','2016-05-31',1,13,136,24),(2615,'63','2016-05-31',1,13,136,25),(2616,'0','2016-05-31',1,13,151,26),(2617,'0','2016-05-31',1,13,151,27),(2618,'0','2016-05-31',1,13,151,28),(2619,'0','2016-05-31',1,13,151,29),(2620,'2','2016-05-31',1,13,166,30),(2621,'0','2016-05-31',1,13,166,31),(2622,'75','2016-05-31',1,13,166,32),(2623,'0','2016-05-31',1,13,166,33),(2624,'0','2016-05-31',1,13,166,34),(2625,'5','2016-05-31',1,13,166,35);
/*!40000 ALTER TABLE `reading_kpi_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rules`
--

DROP TABLE IF EXISTS `rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Rules` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_899A993CF10FA648` (`KpiDetailsId`),
  KEY `IDX_899A993CABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_899A993CABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `element_details` (`id`),
  CONSTRAINT `FK_899A993CF10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rules`
--

LOCK TABLES `rules` WRITE;
/*!40000 ALTER TABLE `rules` DISABLE KEYS */;
INSERT INTO `rules` VALUES (1,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',1,1),(2,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"10000\"}]}}',1,1),(3,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"10000\"}]}}',1,1),(4,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',16,2),(5,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"1\"}]}}',16,2),(6,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"2\"}]}}',16,2),(7,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',16,3),(8,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"1\"}]}}',16,3),(9,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"2\"}]}}',16,3),(10,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',16,4),(11,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"1\"}]}}',16,4),(12,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"2\"}]}}',16,4),(13,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-2\"},{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"2\"}]}}',31,5),(14,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-3\"},{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"3\"}]}}',31,5),(15,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-3\"},{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"3\"}]}}',31,5),(16,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"9\"}]}}',46,6),(17,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"10\"}]}}',46,6),(18,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"10\"}]}}',46,6),(19,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"5\"}]}}',46,7),(20,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"operator\":\"lessThanEqual\",\"value\":\"7\"}]}}',46,7),(21,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"7\"}]}}',46,7),(22,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1.35\"}]}}',61,8),(23,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1.5\"}]}}',61,8),(24,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1.5\"}]}}',61,8),(25,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"80\"}]}}',61,9),(26,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"70\"}]}}',61,9),(27,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"70\"}]}}',61,9),(28,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',61,10),(29,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"3\"}]}}',61,10),(30,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"3\"}]}}',61,10),(31,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"80\"}]}}',61,11),(32,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"operator\":\"greaterThanEqual\",\"value\":\"70\"}]}}',61,11),(33,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"70\"}]}}',61,11),(34,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"3\"}]}}',76,12),(35,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"5\"}]}}',76,12),(36,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"5\"}]}}',76,12),(37,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"1\"}]}}',91,13),(38,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"2\"}]}}',91,13),(39,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"3\"}]}}',91,13),(40,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',91,14),(41,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"2\"}]}}',91,14),(42,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"3\"}]}}',91,14),(43,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0.61\"}]}}',106,15),(44,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',106,15),(45,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',106,15),(46,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"2\"}]}}',106,16),(47,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"2.1\"}]}}',106,16),(48,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"2.1\"}]}}',106,16),(49,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"equalTo\",\"value\":\"0\"}]}}',106,17),(50,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"10000\"}]}}',106,17),(51,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"10000\"}]}}',106,17),(52,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"0\"}]}}',121,18),(53,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"0\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-5\"}]}}',121,18),(54,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-5\"}]}}',121,18),(55,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"0\"}]}}',121,19),(56,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"0\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-5\"}]}}',121,19),(57,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-5\"}]}}',121,19),(58,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"0\"}]}}',121,20),(59,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"0\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-5\"}]}}',121,20),(60,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-5\"}]}}',121,20),(61,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"0\"}]}}',121,21),(62,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"0\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-5\"}]}}',121,21),(63,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-5\"}]}}',121,21),(64,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"80\"}]}}',136,22),(65,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"80\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"75\"}]}}',136,22),(66,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"75\"}]}}',136,22),(67,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',136,23),(68,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"2\"}]}}',136,23),(69,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"2\"}]}}',136,23),(70,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',136,24),(71,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1.25\"}]}}',136,24),(72,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1.25\"}]}}',136,24),(73,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"80\"}]}}',136,25),(74,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"80\"},{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"70\"}]}}',136,25),(75,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"70\"}]}}',136,25),(76,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',151,26),(77,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"-2\"}]}}',151,26),(78,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"-2\"}]}}',151,26),(79,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',151,27),(80,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"2\"}]}}',151,27),(81,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"2\"}]}}',151,27),(82,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"30\"}]}}',151,28),(83,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"32\"}]}}',151,28),(84,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"32\"}]}}',151,28),(85,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',151,29),(86,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',151,29),(87,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',151,29),(88,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0.8\"}]}}',166,30),(89,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',166,30),(90,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',166,30),(91,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',166,31),(92,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',166,31),(93,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',166,31),(94,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"60\"}]}}',166,32),(95,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"50\"}]}}',166,32),(96,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"50\"}]}}',166,32),(97,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',166,33),(98,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',166,33),(99,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',166,33),(100,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"0\"}]}}',166,34),(101,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThanEqual\",\"value\":\"1\"}]}}',166,34),(102,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThan\",\"value\":\"1\"}]}}',166,34),(103,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Green\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"4\"}]}}',166,35),(104,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Yellow\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"greaterThanEqual\",\"value\":\"3\"}]}}',166,35),(105,'{\"operators\":{},\"actions\":{\"name\":\"action-select\",\"value\":\"Red\"},\"conditions\":{\"all\":[{\"name\":\"ageField\",\"operator\":\"lessThan\",\"value\":\"3\"}]}}',166,35);
/*!40000 ALTER TABLE `rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scorecard__lookup_data`
--

DROP TABLE IF EXISTS `scorecard__lookup_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scorecard__lookup_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `individualKpiAverageScore` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `elementcolor` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `kpiColor` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `monthdetail` date NOT NULL,
  `shipDetailsId` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `KpiDetailsId` int(11) DEFAULT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2587F07BF10FA648` (`KpiDetailsId`),
  KEY `IDX_2587F07BABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_2587F07BABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `element_details` (`id`),
  CONSTRAINT `FK_2587F07BF10FA648` FOREIGN KEY (`KpiDetailsId`) REFERENCES `kpi_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2136 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scorecard__lookup_data`
--

LOCK TABLES `scorecard__lookup_data` WRITE;
/*!40000 ALTER TABLE `scorecard__lookup_data` DISABLE KEYS */;
INSERT INTO `scorecard__lookup_data` VALUES (1576,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',121,18),(1577,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',121,19),(1578,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',121,20),(1579,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',121,21),(1580,'2','Yellow','Yellow','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',31,5),(1581,'2.6','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',136,22),(1582,'2.6','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',136,23),(1583,'2.6','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',136,24),(1584,'2.6','Red','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',136,25),(1585,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',151,26),(1586,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',151,27),(1587,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',151,28),(1588,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',151,29),(1589,'1.4','Red','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',106,15),(1590,'1.4','Red','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',106,16),(1591,'1.4','Yellow','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',106,17),(1592,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,30),(1593,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,31),(1594,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,32),(1595,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,33),(1596,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,34),(1597,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,35),(1598,'1.55','Yellow','Yellow','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',61,8),(1599,'1.55','Red','Yellow','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',61,9),(1600,'1.55','Green','Yellow','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',61,10),(1601,'1.55','Red','Yellow','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',61,11),(1602,'1','Red','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',76,12),(1603,'2','Red','Yellow','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',46,6),(1604,'2','Green','Yellow','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',46,7),(1605,'1.4','Red','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',91,13),(1606,'1.4','Green','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',91,14),(1607,'3','Green','Green','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',1,1),(1608,'0','false','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',16,2),(1609,'0','false','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',16,3),(1610,'0','false','Red','2016-01-31','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',16,4),(2101,'1.35','Red','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',121,18),(2102,'1.35','Yellow','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',121,19),(2103,'1.35','Red','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',121,20),(2104,'1.35','Green','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',121,21),(2105,'0','false','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',31,5),(2106,'2.35','Green','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',136,22),(2107,'2.35','Green','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',136,23),(2108,'2.35','Yellow','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',136,24),(2109,'2.35','Red','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',136,25),(2110,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',151,26),(2111,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',151,27),(2112,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',151,28),(2113,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',151,29),(2114,'1.4','Red','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',106,15),(2115,'1.4','Red','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',106,16),(2116,'1.4','Yellow','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',106,17),(2117,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,30),(2118,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,31),(2119,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,32),(2120,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,33),(2121,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,34),(2122,'3','Green','Green','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',166,35),(2123,'1.55','Yellow','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',61,8),(2124,'1.55','Red','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',61,9),(2125,'1.55','Green','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',61,10),(2126,'1.55','Red','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',61,11),(2127,'2','Yellow','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',76,12),(2128,'2','Red','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',46,6),(2129,'2','Green','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',46,7),(2130,'1.4','Red','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',91,13),(2131,'1.4','Green','Red','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',91,14),(2132,'2','Yellow','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',1,1),(2133,'1.8','Green','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',16,2),(2134,'1.8','Green','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',16,3),(2135,'1.8','false','Yellow','2016-02-29','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',16,4);
/*!40000 ALTER TABLE `scorecard__lookup_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scorecard__lookup_status`
--

DROP TABLE IF EXISTS `scorecard__lookup_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scorecard__lookup_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `dataofmonth` datetime NOT NULL,
  `datetime` datetime NOT NULL,
  `userid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scorecard__lookup_status`
--

LOCK TABLES `scorecard__lookup_status` WRITE;
/*!40000 ALTER TABLE `scorecard__lookup_status` DISABLE KEYS */;
INSERT INTO `scorecard__lookup_status` VALUES (1,'1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',4,'2016-01-31 00:00:00','2016-06-22 16:44:20','10'),(2,'1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',4,'2016-02-29 00:00:00','2016-06-22 16:52:55','10'),(3,'1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',1,'2016-03-31 00:00:00','2016-06-23 21:07:25','10'),(4,'1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',1,'2016-04-30 00:00:00','2016-06-24 15:43:15','10'),(5,'1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',1,'2016-05-31 00:00:00','2016-06-24 16:10:37','10');
/*!40000 ALTER TABLE `scorecard__lookup_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `send_command`
--

DROP TABLE IF EXISTS `send_command`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_command` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `clientemail` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `useremialid` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `kpiid` int(11) DEFAULT NULL,
  `shipid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `send_command`
--

LOCK TABLES `send_command` WRITE;
/*!40000 ALTER TABLE `send_command` DISABLE KEYS */;
/*!40000 ALTER TABLE `send_command` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `send_command_ranking`
--

DROP TABLE IF EXISTS `send_command_ranking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_command_ranking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `clientemail` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `useremialid` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `kpiid` int(11) DEFAULT NULL,
  `shipid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `send_command_ranking`
--

LOCK TABLES `send_command_ranking` WRITE;
/*!40000 ALTER TABLE `send_command_ranking` DISABLE KEYS */;
/*!40000 ALTER TABLE `send_command_ranking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ship_details`
--

DROP TABLE IF EXISTS `ship_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ship_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` int(11) DEFAULT NULL,
  `ShipName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `imoNumber` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(125) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `manufacturingYear` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `built` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `size` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `gt` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `shipType` int(11) DEFAULT NULL,
  `companyDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7C0B3BE7FAE99B7` (`ShipName`),
  KEY `IDX_7C0B3BE72425D2CE` (`shipType`),
  KEY `IDX_7C0B3BE75373C966` (`country`),
  KEY `IDX_7C0B3BE7C8A54A33` (`companyDetailsId`),
  CONSTRAINT `FK_7C0B3BE72425D2CE` FOREIGN KEY (`shipType`) REFERENCES `ship_types` (`id`),
  CONSTRAINT `FK_7C0B3BE75373C966` FOREIGN KEY (`country`) REFERENCES `apps_countries` (`id`),
  CONSTRAINT `FK_7C0B3BE7C8A54A33` FOREIGN KEY (`companyDetailsId`) REFERENCES `company_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ship_details`
--

LOCK TABLES `ship_details` WRITE;
/*!40000 ALTER TABLE `ship_details` DISABLE KEYS */;
INSERT INTO `ship_details` VALUES (1,100,'Azure Bay','9302918','Bulk Lumber Carrier','World Wide ','2005 March','Saiki Heavy Idustries','172m','19799',1,1),(2,100,'Calm Bay','9317705','General Cargo','World Wide ','2006 May','Saiki Heavy Idustries','177.85m','22698',1,1),(3,100,'Emerald Bay','9385075','General Cargo','World Wide ','2008 July','Kanda Ship Building','177.13m','20242',1,1),(4,100,'Eden Bay','9445203','Bulk Lumber Carrier','World Wide ','2008 April','Shimanami Shipyard','169.37m','17018',1,1),(5,100,'Fortune Bay','9296327','Bulk Lumber Carrier','World Wide ','2006 Feburary','Shin Kochijyuko','176.63m','17660',1,1),(6,100,'Halong Bay','9343625','General Cargo','World Wide ','2006 March','Kanda Ship Building','177.13m','20236',1,1),(7,100,'Mykonos Bay','9517549','Bulk Lumber Carrier','World Wide ','2009 January','Jinse Ship Building ','180.34m','21497',1,1),(8,100,'Orion Bay','9414474','Bulk Lumber Carrier','World Wide ','2012 September','Tsuji Ship Building','178.7m','19999',1,1),(9,100,'Paradise Bay','9263241','Bulk Carrier','World Wide ','2003 August','Oshima Ship Building','183m','25557',1,1),(10,100,'Reunion Bay','93338137','General Cargo','World Wide ','2006 September','Kanda Ship Building','177.13m','20236',1,1),(11,100,'Teal Bay','9343637','General Cargo','World Wide ','2007 November','Kanda Ship Building','177.13m','20236',1,1),(12,100,'Jupiter Bay','9414448','Bulk Lumber Carrier','World Wide ','2012 June','Tsuji Ship Building','178.7m','19999',1,1),(13,100,'Venus Bay','9414450','Bulk Lumber Carrier','World Wide ','2012 July','Tsuji Ship Building','178.7m','19999',1,1),(14,100,'Falcon Bay','9741724','Bulk Carrier','World Wide ','2015 August','Guoyu Ship Building','179.95m','25598',1,1),(15,100,'Kite Bay','9741736','Bulk Carrier','World Wide ','2016 January','Guoyu Ship Building','179.95m','25598',1,1);
/*!40000 ALTER TABLE `ship_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ship_status_details`
--

DROP TABLE IF EXISTS `ship_status_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ship_status_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ActiveDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `status` varchar(125) COLLATE utf8_unicode_ci NOT NULL,
  `ShipDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4D63CA015041247E` (`ShipDetailsId`),
  CONSTRAINT `FK_4D63CA015041247E` FOREIGN KEY (`ShipDetailsId`) REFERENCES `ship_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ship_status_details`
--

LOCK TABLES `ship_status_details` WRITE;
/*!40000 ALTER TABLE `ship_status_details` DISABLE KEYS */;
INSERT INTO `ship_status_details` VALUES (1,'2016-06-21 15:50:49',NULL,'1',1),(2,NULL,'2016-06-21 15:51:07','0',1),(3,'2016-06-21 15:51:11',NULL,'1',1),(4,'2016-06-21 15:52:23',NULL,'1',2),(5,'2016-06-21 15:54:39',NULL,'1',3),(6,'2016-06-21 15:56:03',NULL,'1',4),(7,'2016-06-21 15:57:13',NULL,'1',5),(8,'2016-06-21 15:59:07',NULL,'1',6),(9,'2016-06-21 16:01:44',NULL,'1',7),(10,'2016-06-21 16:02:56',NULL,'1',8),(11,'2016-06-21 16:03:55',NULL,'1',9),(12,'2016-06-21 16:05:10',NULL,'1',10),(13,'2016-06-21 16:06:03',NULL,'1',11),(14,'2016-06-21 16:06:59',NULL,'1',12),(15,'2016-06-21 16:07:51',NULL,'1',13),(16,'2016-06-21 16:08:51',NULL,'1',14),(17,'2016-06-21 16:09:48',NULL,'1',15);
/*!40000 ALTER TABLE `ship_status_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ship_types`
--

DROP TABLE IF EXISTS `ship_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ship_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ShipType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ship_types`
--

LOCK TABLES `ship_types` WRITE;
/*!40000 ALTER TABLE `ship_types` DISABLE KEYS */;
INSERT INTO `ship_types` VALUES (1,'Cargo Ships'),(2,'Fishing Ships'),(3,'Passenger/Cruise ships'),(4,'Tankers'),(5,'High speed crafts'),(6,'Military ships');
/*!40000 ALTER TABLE `ship_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sub_element_details`
--

DROP TABLE IF EXISTS `sub_element_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sub_element_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SubElementName` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `CellName` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `CellDetails` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `ActivatedDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `Weightage` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ElementDetailsId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_9F763BF1ABFC5A8E` (`ElementDetailsId`),
  CONSTRAINT `FK_9F763BF1ABFC5A8E` FOREIGN KEY (`ElementDetailsId`) REFERENCES `element_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sub_element_details`
--

LOCK TABLES `sub_element_details` WRITE;
/*!40000 ALTER TABLE `sub_element_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `sub_element_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `RoleName` varchar(75) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2DE8C6A3FE6E2EE6` (`RoleName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role`
--

LOCK TABLES `user_role` WRITE;
/*!40000 ALTER TABLE `user_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_role` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-06-24 16:11:38
