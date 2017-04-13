-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: depth_history
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
-- Current Database: `depth_history`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `depth_history` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE `depth_history`;

--
-- Table structure for table `btc_rur__asks`
--

DROP TABLE IF EXISTS `btc_rur__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=282 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_rur__bids`
--

DROP TABLE IF EXISTS `btc_rur__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=636 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_rur__diff`
--

DROP TABLE IF EXISTS `btc_rur__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `OPT` (`ts`,`price`)
) ENGINE=InnoDB AUTO_INCREMENT=1450284 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_rur__full`
--

DROP TABLE IF EXISTS `btc_rur__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `OPT` (`ts`,`price`)
) ENGINE=InnoDB AUTO_INCREMENT=5430104 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_rur__spreads`
--

DROP TABLE IF EXISTS `btc_rur__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=121113 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_rur__stats`
--

DROP TABLE IF EXISTS `btc_rur__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_rur__stats_old`
--

DROP TABLE IF EXISTS `btc_rur__stats_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur__stats_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=119462 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd__asks`
--

DROP TABLE IF EXISTS `btc_usd__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=687784 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd__bids`
--

DROP TABLE IF EXISTS `btc_usd__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=649255 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd__diff`
--

DROP TABLE IF EXISTS `btc_usd__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=189543280 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd__full`
--

DROP TABLE IF EXISTS `btc_usd__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=96956149 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd__full_map`
--

DROP TABLE IF EXISTS `btc_usd__full_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd__full_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ref_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd__spreads`
--

DROP TABLE IF EXISTS `btc_usd__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=191675 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd__stats`
--

DROP TABLE IF EXISTS `btc_usd__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`),
  UNIQUE KEY `ts` (`ts`),
  UNIQUE KEY `ts_2` (`ts`),
  UNIQUE KEY `ts_3` (`ts`),
  UNIQUE KEY `ts_4` (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc__asks`
--

DROP TABLE IF EXISTS `dsh_btc__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc__bids`
--

DROP TABLE IF EXISTS `dsh_btc__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=553 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc__diff`
--

DROP TABLE IF EXISTS `dsh_btc__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=519311 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc__full`
--

DROP TABLE IF EXISTS `dsh_btc__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1884078 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc__spreads`
--

DROP TABLE IF EXISTS `dsh_btc__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=79774 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc__stats`
--

DROP TABLE IF EXISTS `dsh_btc__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc__stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=117006 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd__asks`
--

DROP TABLE IF EXISTS `dsh_usd__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd__bids`
--

DROP TABLE IF EXISTS `dsh_usd__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=514 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd__diff`
--

DROP TABLE IF EXISTS `dsh_usd__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=464597 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd__full`
--

DROP TABLE IF EXISTS `dsh_usd__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1753000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd__spreads`
--

DROP TABLE IF EXISTS `dsh_usd__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=91346 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd__stats`
--

DROP TABLE IF EXISTS `dsh_usd__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd__stats_old`
--

DROP TABLE IF EXISTS `dsh_usd__stats_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd__stats_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=70195 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc__asks`
--

DROP TABLE IF EXISTS `eth_btc__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=957 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc__bids`
--

DROP TABLE IF EXISTS `eth_btc__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=394 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc__diff`
--

DROP TABLE IF EXISTS `eth_btc__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=788685 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc__full`
--

DROP TABLE IF EXISTS `eth_btc__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1970436 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc__spreads`
--

DROP TABLE IF EXISTS `eth_btc__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=94057 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc__stats`
--

DROP TABLE IF EXISTS `eth_btc__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc__stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=140815 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur__asks`
--

DROP TABLE IF EXISTS `eth_rur__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur__bids`
--

DROP TABLE IF EXISTS `eth_rur__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur__diff`
--

DROP TABLE IF EXISTS `eth_rur__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=397262 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur__full`
--

DROP TABLE IF EXISTS `eth_rur__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=675105 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur__spreads`
--

DROP TABLE IF EXISTS `eth_rur__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=65413 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur__stats`
--

DROP TABLE IF EXISTS `eth_rur__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur__stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92437 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd__asks`
--

DROP TABLE IF EXISTS `eth_usd__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=810 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd__bids`
--

DROP TABLE IF EXISTS `eth_usd__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=892 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd__diff`
--

DROP TABLE IF EXISTS `eth_usd__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1333194 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd__full`
--

DROP TABLE IF EXISTS `eth_usd__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=4564156 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd__spreads`
--

DROP TABLE IF EXISTS `eth_usd__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=112428 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd__stats`
--

DROP TABLE IF EXISTS `eth_usd__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd__stats_old`
--

DROP TABLE IF EXISTS `eth_usd__stats_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd__stats_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=105163 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `last_sync`
--

DROP TABLE IF EXISTS `last_sync`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `last_sync` (
  `data_type` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  UNIQUE KEY `MASTER` (`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Для контроля за скриптами обновления данных';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__asks`
--

DROP TABLE IF EXISTS `ltc_btc__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=254400 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__bids`
--

DROP TABLE IF EXISTS `ltc_btc__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=95892 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__diff`
--

DROP TABLE IF EXISTS `ltc_btc__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=46271237 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__diff_map`
--

DROP TABLE IF EXISTS `ltc_btc__diff_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__diff_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ref_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__full`
--

DROP TABLE IF EXISTS `ltc_btc__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=51552077 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__full_map`
--

DROP TABLE IF EXISTS `ltc_btc__full_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__full_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ref_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__spreads`
--

DROP TABLE IF EXISTS `ltc_btc__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=127828 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__stats`
--

DROP TABLE IF EXISTS `ltc_btc__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__stats_old`
--

DROP TABLE IF EXISTS `ltc_btc__stats_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__stats_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=4172150 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd__asks`
--

DROP TABLE IF EXISTS `ltc_usd__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=2443260 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd__bids`
--

DROP TABLE IF EXISTS `ltc_usd__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=2425131 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd__diff`
--

DROP TABLE IF EXISTS `ltc_usd__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=3942560 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd__full`
--

DROP TABLE IF EXISTS `ltc_usd__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=15140693 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd__spreads`
--

DROP TABLE IF EXISTS `ltc_usd__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=102382 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd__stats`
--

DROP TABLE IF EXISTS `ltc_usd__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd__stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125812 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc__asks`
--

DROP TABLE IF EXISTS `nmc_btc__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1154 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc__bids`
--

DROP TABLE IF EXISTS `nmc_btc__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc__diff`
--

DROP TABLE IF EXISTS `nmc_btc__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=90358 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc__full`
--

DROP TABLE IF EXISTS `nmc_btc__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1223704 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc__spreads`
--

DROP TABLE IF EXISTS `nmc_btc__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=64819 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc__stats`
--

DROP TABLE IF EXISTS `nmc_btc__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc__stats_old`
--

DROP TABLE IF EXISTS `nmc_btc__stats_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc__stats_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=50446 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd__asks`
--

DROP TABLE IF EXISTS `nmc_usd__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1634 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd__bids`
--

DROP TABLE IF EXISTS `nmc_usd__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd__diff`
--

DROP TABLE IF EXISTS `nmc_usd__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=102179 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd__full`
--

DROP TABLE IF EXISTS `nmc_usd__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1779188 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd__spreads`
--

DROP TABLE IF EXISTS `nmc_usd__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=52099 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd__stats`
--

DROP TABLE IF EXISTS `nmc_usd__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd__stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71235 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc__asks`
--

DROP TABLE IF EXISTS `nvc_btc__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1127 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc__bids`
--

DROP TABLE IF EXISTS `nvc_btc__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc__diff`
--

DROP TABLE IF EXISTS `nvc_btc__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL,
  `volume` double NOT NULL,
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=7418799 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc__full`
--

DROP TABLE IF EXISTS `nvc_btc__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL,
  `volume` double NOT NULL,
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=30011104 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc__spreads`
--

DROP TABLE IF EXISTS `nvc_btc__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=67122 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc__stats`
--

DROP TABLE IF EXISTS `nvc_btc__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc__stats_old`
--

DROP TABLE IF EXISTS `nvc_btc__stats_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc__stats_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL,
  `best_bid` float NOT NULL,
  `volume_asks` float NOT NULL,
  `volume_bids` float NOT NULL,
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=4472353 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd__asks`
--

DROP TABLE IF EXISTS `nvc_usd__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1276 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd__bids`
--

DROP TABLE IF EXISTS `nvc_usd__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=496 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd__diff`
--

DROP TABLE IF EXISTS `nvc_usd__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=9891663 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd__full`
--

DROP TABLE IF EXISTS `nvc_usd__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=28619560 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd__spreads`
--

DROP TABLE IF EXISTS `nvc_usd__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=52107 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd__stats`
--

DROP TABLE IF EXISTS `nvc_usd__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd__stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4111533 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__asks`
--

DROP TABLE IF EXISTS `ppc_btc__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=978 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__bids`
--

DROP TABLE IF EXISTS `ppc_btc__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__diff`
--

DROP TABLE IF EXISTS `ppc_btc__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL,
  `volume` double NOT NULL,
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=14390770 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__diff_map`
--

DROP TABLE IF EXISTS `ppc_btc__diff_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__diff_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ref_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__full`
--

DROP TABLE IF EXISTS `ppc_btc__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL,
  `volume` double NOT NULL,
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=24865737 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__full_map`
--

DROP TABLE IF EXISTS `ppc_btc__full_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__full_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ref_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__spreads`
--

DROP TABLE IF EXISTS `ppc_btc__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=66247 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__stats`
--

DROP TABLE IF EXISTS `ppc_btc__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__stats_old`
--

DROP TABLE IF EXISTS `ppc_btc__stats_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__stats_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL,
  `best_bid` float NOT NULL,
  `volume_asks` float NOT NULL,
  `volume_bids` float NOT NULL,
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=4458911 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__asks`
--

DROP TABLE IF EXISTS `ppc_usd__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1226 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__bids`
--

DROP TABLE IF EXISTS `ppc_usd__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__diff`
--

DROP TABLE IF EXISTS `ppc_usd__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=9368055 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__diff_map`
--

DROP TABLE IF EXISTS `ppc_usd__diff_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__diff_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ref_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__full`
--

DROP TABLE IF EXISTS `ppc_usd__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=21351810 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__full_map`
--

DROP TABLE IF EXISTS `ppc_usd__full_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__full_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ref_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__spreads`
--

DROP TABLE IF EXISTS `ppc_usd__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=52586 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__stats`
--

DROP TABLE IF EXISTS `ppc_usd__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2651555 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rub__diff`
--

DROP TABLE IF EXISTS `usd_rub__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rub__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rub__full`
--

DROP TABLE IF EXISTS `usd_rub__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rub__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rub__last`
--

DROP TABLE IF EXISTS `usd_rub__last`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rub__last` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rub__stats`
--

DROP TABLE IF EXISTS `usd_rub__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rub__stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur__asks`
--

DROP TABLE IF EXISTS `usd_rur__asks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur__asks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1420 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur__bids`
--

DROP TABLE IF EXISTS `usd_rur__bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur__bids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OPT` (`price`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=542 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur__diff`
--

DROP TABLE IF EXISTS `usd_rur__diff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur__diff` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp(3) NULL DEFAULT NULL,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=682389 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur__full`
--

DROP TABLE IF EXISTS `usd_rur__full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur__full` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=4900945 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur__spreads`
--

DROP TABLE IF EXISTS `usd_rur__spreads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur__spreads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `buy_0.1` float NOT NULL DEFAULT '0',
  `buy_1` float NOT NULL DEFAULT '0',
  `buy_10` float NOT NULL DEFAULT '0',
  `buy_100` float NOT NULL DEFAULT '0',
  `buy_1000` float NOT NULL DEFAULT '0',
  `buy_10k` float NOT NULL DEFAULT '0',
  `sell_0.1` float NOT NULL DEFAULT '0',
  `sell_1` float NOT NULL DEFAULT '0',
  `sell_10` float NOT NULL DEFAULT '0',
  `sell_100` float NOT NULL DEFAULT '0',
  `sell_1000` float NOT NULL DEFAULT '0',
  `sell_10k` float NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=124639 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur__stats`
--

DROP TABLE IF EXISTS `usd_rur__stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur__stats` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur__stats_old`
--

DROP TABLE IF EXISTS `usd_rur__stats_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur__stats_old` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `best_ask` float NOT NULL DEFAULT '0',
  `best_bid` float NOT NULL DEFAULT '0',
  `volume_asks` float NOT NULL DEFAULT '0',
  `volume_bids` float NOT NULL DEFAULT '0',
  `cost_asks` float NOT NULL DEFAULT '0',
  `cost_bids` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=80899 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-20 19:37:25
