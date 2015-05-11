-- MySQL dump 10.13  Distrib 5.6.24, for osx10.8 (x86_64)
--
-- Host: localhost    Database: gallery_dev
-- ------------------------------------------------------
-- Server version	5.6.24

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
-- Table structure for table `galc_cat`
--

DROP TABLE IF EXISTS `galc_cat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `galc_cat` (
  `galc_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `galc_name` varchar(38) DEFAULT NULL,
  `galc_desc` varchar(64) DEFAULT NULL,
  `galc_active` char(1) DEFAULT NULL,
  `galc_date` date DEFAULT NULL,
  PRIMARY KEY (`galc_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gali_img`
--

DROP TABLE IF EXISTS `gali_img`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gali_img` (
  `gali_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `galc_id` smallint(6) DEFAULT NULL,
  `gali_name` varchar(38) DEFAULT NULL,
  `gali_type` char(4) DEFAULT NULL,
  `gali_display` varchar(38) DEFAULT NULL,
  `gali_active` char(1) DEFAULT NULL,
  `gali_date` date DEFAULT NULL,
  PRIMARY KEY (`gali_id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
