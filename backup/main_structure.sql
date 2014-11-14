-- MySQL dump 10.13  Distrib 5.5.38, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: order
-- ------------------------------------------------------
-- Server version	5.5.38-0ubuntu0.12.04.1

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
-- Table structure for table `UUID_user`
--

DROP TABLE IF EXISTS `UUID_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UUID_user` (
  `UUID` text COLLATE utf8_bin NOT NULL,
  `userID` int(11) NOT NULL,
  `push_token` text COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `districtID` int(11) NOT NULL AUTO_INCREMENT,
  `province` text COLLATE utf8_bin NOT NULL,
  `city` text COLLATE utf8_bin NOT NULL,
  `district` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`districtID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `beacons`
--

DROP TABLE IF EXISTS `beacons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beacons` (
  `beaconID` int(11) NOT NULL,
  `beaconUUID` tinytext COLLATE utf8_bin,
  `major` int(11) NOT NULL,
  `minor` int(11) NOT NULL,
  `storeID` int(11) NOT NULL,
  `relevantText` text COLLATE utf8_bin,
  PRIMARY KEY (`beaconID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `customerID` int(11) NOT NULL AUTO_INCREMENT,
  `customerName` tinytext COLLATE utf8_bin,
  `email` tinytext COLLATE utf8_bin,
  `tel` tinytext COLLATE utf8_bin,
  `QQ` text COLLATE utf8_bin NOT NULL,
  `purse` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`customerID`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dishCategory`
--

DROP TABLE IF EXISTS `dishCategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dishCategory` (
  `storeID` int(11) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `categoryName` tinytext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`storeID`,`categoryID`),
  CONSTRAINT `fStoreID` FOREIGN KEY (`storeID`) REFERENCES `stores` (`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dishes`
--

DROP TABLE IF EXISTS `dishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dishes` (
  `dishID` int(11) NOT NULL DEFAULT '0',
  `dishName` tinytext COLLATE utf8_bin,
  `storeID` int(11) NOT NULL DEFAULT '0',
  `price` double DEFAULT NULL,
  `catagory` int(11) DEFAULT NULL,
  `picPath` tinytext COLLATE utf8_bin,
  `description` text COLLATE utf8_bin NOT NULL,
  `note` text COLLATE utf8_bin NOT NULL,
  `orderCount` int(11) NOT NULL DEFAULT '0',
  `upCount` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dishID`,`storeID`),
  KEY `dishID` (`dishID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `userID` int(11) NOT NULL,
  `date` tinytext COLLATE utf8_bin NOT NULL,
  `time` tinytext COLLATE utf8_bin NOT NULL,
  `body` text COLLATE utf8_bin NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orderDetails`
--

DROP TABLE IF EXISTS `orderDetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orderDetails` (
  `dishID` int(11) NOT NULL DEFAULT '0',
  `storeID` int(11) NOT NULL DEFAULT '0',
  `orderID` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`dishID`,`storeID`,`orderID`),
  CONSTRAINT `dishID` FOREIGN KEY (`dishID`, `storeID`) REFERENCES `dishes` (`dishID`, `storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL AUTO_INCREMENT,
  `storeID` int(11) NOT NULL,
  `date` tinytext COLLATE utf8_bin NOT NULL,
  `time` tinytext COLLATE utf8_bin NOT NULL,
  `tableID` int(11) NOT NULL,
  `customerID` int(11) DEFAULT NULL,
  `payFlag` int(11) DEFAULT '0',
  `orderFlag` int(11) DEFAULT '0',
  `fetchFlag` int(11) DEFAULT '0',
  `totalPrice` double NOT NULL DEFAULT '0',
  `paymentID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`orderID`,`storeID`),
  KEY `customerID` (`customerID`),
  KEY `orderID` (`orderID`),
  KEY `storeID` (`storeID`),
  CONSTRAINT `customerID` FOREIGN KEY (`customerID`) REFERENCES `customers` (`customerID`),
  CONSTRAINT `storeID` FOREIGN KEY (`storeID`) REFERENCES `stores` (`storeID`)
) ENGINE=InnoDB AUTO_INCREMENT=1251 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `pingpp` char(32) COLLATE utf8_bin NOT NULL,
  `client_ip` tinytext COLLATE utf8_bin NOT NULL,
  `channel` tinytext COLLATE utf8_bin NOT NULL,
  `userID` int(11) NOT NULL,
  `storeID` int(11) NOT NULL,
  `amount` double NOT NULL,
  `date` tinytext COLLATE utf8_bin NOT NULL,
  `time` tinytext COLLATE utf8_bin NOT NULL,
  `pay_status` tinytext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`paymentID`),
  KEY `pingpp` (`pingpp`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_call`
--

DROP TABLE IF EXISTS `service_call`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_call` (
  `storeID` int(11) NOT NULL,
  `tableID` int(11) NOT NULL,
  `date` tinytext COLLATE utf8_bin NOT NULL,
  `time` tinytext COLLATE utf8_bin NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`storeID`,`tableID`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `specials`
--

DROP TABLE IF EXISTS `specials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `specials` (
  `storeID` int(11) NOT NULL,
  `dishID` int(11) NOT NULL,
  PRIMARY KEY (`dishID`,`storeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stores`
--

DROP TABLE IF EXISTS `stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stores` (
  `storeID` int(11) NOT NULL DEFAULT '0',
  `storeName` tinytext COLLATE utf8_bin,
  `beaconID` int(11) DEFAULT NULL,
  `notification` text COLLATE utf8_bin,
  `tel` tinytext COLLATE utf8_bin NOT NULL,
  `contact` tinytext COLLATE utf8_bin,
  `email` tinytext COLLATE utf8_bin,
  `addr` tinytext COLLATE utf8_bin NOT NULL,
  `cuisine` tinytext COLLATE utf8_bin,
  `businessHour` tinytext COLLATE utf8_bin,
  `logoFile` tinytext COLLATE utf8_bin,
  `description` mediumtext COLLATE utf8_bin,
  `supportFlag` int(11) DEFAULT '0',
  `selfTakeMealFlag` int(11) DEFAULT '0',
  `tableFlag` int(11) DEFAULT '0',
  `useBlackFont` int(11) DEFAULT '0',
  PRIMARY KEY (`storeID`),
  KEY `beaconID` (`beaconID`),
  CONSTRAINT `beaconID` FOREIGN KEY (`beaconID`) REFERENCES `beacons` (`beaconID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_QQ`
--

DROP TABLE IF EXISTS `user_QQ`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_QQ` (
  `userID` int(11) NOT NULL,
  `QQ` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_login`
--

DROP TABLE IF EXISTS `user_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_login` (
  `userID` int(11) NOT NULL,
  `username` text COLLATE utf8_bin NOT NULL,
  `password` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `verification_code`
--

DROP TABLE IF EXISTS `verification_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verification_code` (
  `tel` varchar(11) COLLATE utf8_bin NOT NULL,
  `code` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`tel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-14 22:25:15
