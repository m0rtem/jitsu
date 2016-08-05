-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.17 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for ping
CREATE DATABASE IF NOT EXISTS `ping` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `ping`;


-- Dumping structure for table ping.jitsu
CREATE TABLE IF NOT EXISTS `jitsu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method` varchar(50) NOT NULL DEFAULT '0',
  `key` text NOT NULL,
  `value` text NOT NULL,
  `ruleID` int(11) NOT NULL DEFAULT '0',
  `rule` text NOT NULL,
  `description` varchar(250) NOT NULL DEFAULT '0',
  `tags` varchar(250) NOT NULL DEFAULT '0',
  `impact` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(50) NOT NULL,
  `os` varchar(150) NOT NULL,
  `browser` text NOT NULL,
  `user_agent` text NOT NULL,
  `referrer` text NOT NULL,
  `datetime` datetime NOT NULL,
  `page_url` text NOT NULL,
  `lat` int(11) NOT NULL,
  `long` int(11) NOT NULL,
  `city` text NOT NULL,
  `region` text NOT NULL,
  `country` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
