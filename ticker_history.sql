-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: ticker_history
-- ------------------------------------------------------
-- Server version	5.7.17

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
-- Current Database: `ticker_history`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `ticker_history` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_bin */;

USE `ticker_history`;

--
-- Table structure for table `btc_rur`
--

DROP TABLE IF EXISTS `btc_rur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd`
--

DROP TABLE IF EXISTS `btc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data__old`
--

DROP TABLE IF EXISTS `data__old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data__old` (
  `ts` datetime(3) NOT NULL,
  `ticker_id` tinyint(11) unsigned NOT NULL,
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` float NOT NULL DEFAULT '0',
  `volume_cur` float NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`updated`,`ticker_id`) USING BTREE,
  UNIQUE KEY `DATA` (`ts`,`ticker_id`),
  KEY `ts` (`ts`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc`
--

DROP TABLE IF EXISTS `dsh_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd`
--

DROP TABLE IF EXISTS `dsh_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc`
--

DROP TABLE IF EXISTS `eth_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur`
--

DROP TABLE IF EXISTS `eth_rur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd`
--

DROP TABLE IF EXISTS `eth_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc`
--

DROP TABLE IF EXISTS `ltc_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd`
--

DROP TABLE IF EXISTS `ltc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc`
--

DROP TABLE IF EXISTS `nmc_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd`
--

DROP TABLE IF EXISTS `nmc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc`
--

DROP TABLE IF EXISTS `nvc_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd`
--

DROP TABLE IF EXISTS `nvc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pair_map`
--

DROP TABLE IF EXISTS `pair_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pair_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pair` varchar(31) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc`
--

DROP TABLE IF EXISTS `ppc_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd`
--

DROP TABLE IF EXISTS `ppc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur`
--

DROP TABLE IF EXISTS `usd_rur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur` (
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3),
  `buy` double NOT NULL DEFAULT '0',
  `sell` double NOT NULL DEFAULT '0',
  `last` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `volume_cur` double NOT NULL DEFAULT '0',
  `updated` timestamp(3) NULL DEFAULT NULL,
  PRIMARY KEY (`ts`),
  UNIQUE KEY `updated` (`updated`),
  KEY `volume` (`volume`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-20 19:38:48
