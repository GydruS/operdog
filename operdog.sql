/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE IF NOT EXISTS `operdog` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `operdog`;

CREATE TABLE IF NOT EXISTS `adverts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `categoryId` int(11) NOT NULL DEFAULT '0',
  `alias` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `text` text NOT NULL,
  `phone` varchar(64) NOT NULL DEFAULT '',
  `number` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  `addedTimestamp` int(10) NOT NULL DEFAULT '0',
  `modifiedTimestamp` int(10) NOT NULL DEFAULT '0',
  `userCreatorId` int(10) NOT NULL DEFAULT '0',
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `lastSeenPlace` text NOT NULL,
  `city` varchar(128) NOT NULL COMMENT 'Населенный пункт',
  `addedDatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hash` varchar(64) NOT NULL DEFAULT '',
  `reward` varchar(255) NOT NULL DEFAULT '',
  `viewsCount` int(11) NOT NULL DEFAULT '0',
  `contacterName` varchar(128) NOT NULL DEFAULT '',
  `nickname` varchar(64) NOT NULL DEFAULT '',
  `color` varchar(64) NOT NULL DEFAULT '',
  `ears` varchar(64) NOT NULL DEFAULT '',
  `tail` varchar(64) NOT NULL DEFAULT '',
  `eyes` varchar(64) NOT NULL DEFAULT '',
  `breed` varchar(64) NOT NULL DEFAULT '',
  `age` varchar(64) NOT NULL DEFAULT '',
  `gender` enum('Male','Female','') NOT NULL DEFAULT '',
  `specialSigns` varchar(128) NOT NULL DEFAULT '',
  `eventDatetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `active` (`active`),
  KEY `userCreator` (`userCreatorId`),
  KEY `number` (`number`),
  KEY `categoriId` (`categoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL,
  `number` int(11) NOT NULL DEFAULT '0',
  `nameMale` varchar(128) NOT NULL DEFAULT '',
  `nameFemale` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `number` (`number`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

DELETE FROM `categories`;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `parentId`, `name`, `number`, `nameMale`, `nameFemale`) VALUES
	(1, 0, 'Собака', 0, 'Пёс', 'Собака'),
	(2, 0, 'Кошка/Кот', 0, 'Кот', 'Кошка'),
	(3, 0, 'Птица', 0, '', ''),
	(4, 0, 'Грызун', 0, '', '');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(512) NOT NULL,
  `width` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `filesize` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `userId` int(11) NOT NULL DEFAULT '0',
  `itemId` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT 'When dbrecord created',
  `addedDatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creationDatetime` datetime NOT NULL COMMENT 'When image created',
  `number` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL,
  `value` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

DELETE FROM `storage`;
/*!40000 ALTER TABLE `storage` DISABLE KEYS */;
INSERT INTO `storage` (`id`, `key`, `value`) VALUES
	(1, 'settings:siteName', 'Поиск Животных: Operdog.ru'),
	(2, 'settings:style', ''),
	(3, 'settings:footer', ''),
	(4, 'settings:counters', '');
/*!40000 ALTER TABLE `storage` ENABLE KEYS */;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL DEFAULT '',
  `salt` varchar(64) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '1',
  `active` int(11) NOT NULL DEFAULT '1',
  `lastLogin` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT '',
  `middleName` varchar(24) NOT NULL DEFAULT '',
  `lastName` varchar(24) NOT NULL DEFAULT '',
  `gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `email` varchar(64) NOT NULL DEFAULT '',
  `loginsCount` int(10) NOT NULL DEFAULT '0',
  `token` varchar(40) NOT NULL DEFAULT '',
  `timezone` varchar(64) NOT NULL DEFAULT '',
  `advancedData` text NOT NULL,
  `birthDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `registrationDatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastActivity` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `login` (`login`),
  KEY `lastActivity` (`lastActivity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

