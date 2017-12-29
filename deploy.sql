-- MySQL dump 10.13  Distrib 5.7.20, for Linux (x86_64)
--
-- Host: localhost    Database: deploy
-- ------------------------------------------------------
-- Server version	5.7.20-0ubuntu0.16.04.1

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
-- Table structure for table `hostname`
--

DROP TABLE IF EXISTS `hostname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostname` (
  `host` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  PRIMARY KEY (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hostname`
--

LOCK TABLES `hostname` WRITE;
/*!40000 ALTER TABLE `hostname` DISABLE KEYS */;
INSERT INTO `hostname` VALUES ('dev-be','192.168.2.17'),('dev-dmz','192.168.2.12'),('dev-fe','192.168.2.23'),('hsb-dmz','192.168.2.22'),('hsb-fe','192.168.2.15'),('prod-be','192.168.2.18'),('prod-dmz','192.168.2.12'),('prod-fe','192.168.2.25'),('qa-be','192.168.2.16'),('qa-dmz','192.168.2.21'),('qa-fe','192.168.2.29');
/*!40000 ALTER TABLE `hostname` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `version` (
  `bundle` varchar(255) NOT NULL,
  `version` int(11) DEFAULT NULL,
  `deprecated` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `version`
--

LOCK TABLES `version` WRITE;
/*!40000 ALTER TABLE `version` DISABLE KEYS */;
INSERT INTO `version` VALUES ('testBundle',1,NULL),('testBundle',2,NULL),('testBundle',3,NULL),('testBundle',4,NULL),('testBundle',5,NULL),('testBundle',6,NULL),('testBundle',7,NULL),('testBundle',8,NULL),('testBundle',9,NULL),('testBundle',10,NULL),('testBundle',11,NULL),('testBundle',12,NULL),('testBundle',13,NULL),('testBundle',14,NULL),('testBundle',15,NULL),('testBundle',16,NULL),('lolpoop',1,NULL),('lolpoop',2,NULL),('testtest',1,NULL),('lol',1,NULL),('lol',2,NULL),('thisisatest',1,NULL),('thisisatest',2,NULL),('thisisatest',3,NULL),('thisisatest',4,NULL),('thisisatest',5,NULL),('thisisatest',6,NULL),('thisisatest',7,NULL),('thisisatest',8,NULL),('thisisatest',9,NULL);
/*!40000 ALTER TABLE `version` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-12-03 18:31:53
