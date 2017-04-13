-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: trades_history
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
-- Current Database: `trades_history`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `trades_history` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE `trades_history`;

--
-- Table structure for table `btc_rur`
--

DROP TABLE IF EXISTS `btc_rur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53064 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_rur__bars`
--

DROP TABLE IF EXISTS `btc_rur__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_rur__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=17415 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd`
--

DROP TABLE IF EXISTS `btc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=434246 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `btc_usd__bars`
--

DROP TABLE IF EXISTS `btc_usd__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `btc_usd__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=44660 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc`
--

DROP TABLE IF EXISTS `dsh_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=112364 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_btc__bars`
--

DROP TABLE IF EXISTS `dsh_btc__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_btc__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=12383 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd`
--

DROP TABLE IF EXISTS `dsh_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21213 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dsh_usd__bars`
--

DROP TABLE IF EXISTS `dsh_usd__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dsh_usd__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=7400 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc`
--

DROP TABLE IF EXISTS `eth_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=112254 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_btc__bars`
--

DROP TABLE IF EXISTS `eth_btc__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_btc__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=15407 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur`
--

DROP TABLE IF EXISTS `eth_rur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15038 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_rur__bars`
--

DROP TABLE IF EXISTS `eth_rur__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_rur__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=7387 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd`
--

DROP TABLE IF EXISTS `eth_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48483 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eth_usd__bars`
--

DROP TABLE IF EXISTS `eth_usd__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eth_usd__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=14589 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc`
--

DROP TABLE IF EXISTS `ltc_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=81492 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_btc__bars`
--

DROP TABLE IF EXISTS `ltc_btc__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_btc__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=17049 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd`
--

DROP TABLE IF EXISTS `ltc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=162213 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ltc_usd__bars`
--

DROP TABLE IF EXISTS `ltc_usd__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ltc_usd__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=18141 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc`
--

DROP TABLE IF EXISTS `nmc_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8089 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_btc__bars`
--

DROP TABLE IF EXISTS `nmc_btc__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_btc__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=2333 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd`
--

DROP TABLE IF EXISTS `nmc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28089 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nmc_usd__bars`
--

DROP TABLE IF EXISTS `nmc_usd__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nmc_usd__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=8179 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc`
--

DROP TABLE IF EXISTS `nvc_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7719 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_btc__bars`
--

DROP TABLE IF EXISTS `nvc_btc__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_btc__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=2444 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd`
--

DROP TABLE IF EXISTS `nvc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20741 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nvc_usd__bars`
--

DROP TABLE IF EXISTS `nvc_usd__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nvc_usd__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=8381 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc`
--

DROP TABLE IF EXISTS `ppc_btc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8188 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_btc__bars`
--

DROP TABLE IF EXISTS `ppc_btc__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_btc__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=2554 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd`
--

DROP TABLE IF EXISTS `ppc_usd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19975 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ppc_usd__bars`
--

DROP TABLE IF EXISTS `ppc_usd__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppc_usd__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=3119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rub`
--

DROP TABLE IF EXISTS `usd_rub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rub` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rub__bars`
--

DROP TABLE IF EXISTS `usd_rub__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rub__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur`
--

DROP TABLE IF EXISTS `usd_rur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price` double NOT NULL DEFAULT '0',
  `order_id` bigint(20) NOT NULL DEFAULT '0',
  `trade_id` bigint(20) NOT NULL,
  `flags` int(11) unsigned NOT NULL,
  `volume` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`trade_id`),
  KEY `SCAN` (`id`,`ts`,`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36765 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usd_rur__bars`
--

DROP TABLE IF EXISTS `usd_rur__bars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usd_rur__bars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ts` timestamp NULL DEFAULT NULL,
  `open` double NOT NULL DEFAULT '0',
  `high` double NOT NULL DEFAULT '0',
  `low` double NOT NULL DEFAULT '0',
  `close` double NOT NULL DEFAULT '0',
  `volume` double NOT NULL DEFAULT '0',
  `last_trade` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TIMESTAMP` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=10465 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-20 19:38:36
