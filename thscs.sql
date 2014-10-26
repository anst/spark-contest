# ************************************************************
# Sequel Pro SQL dump
# Version 4135
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 5.5.34)
# Database: thscs
# Generation Time: 2014-10-12 01:49:11 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table clarifications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `clarifications`;

CREATE TABLE `clarifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(3) NOT NULL,
  `problem` int(3) NOT NULL,
  `message` varchar(8192) NOT NULL,
  `reply` varchar(8219) NOT NULL,
  `global` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table pizza
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pizza`;

CREATE TABLE `pizza` (
  `team` int(3) NOT NULL,
  `cheese` int(1) NOT NULL,
  `pepperoni` int(1) NOT NULL,
  `sausage` int(1) NOT NULL,
  `cost` int(2) NOT NULL,
  `ticket` longtext NOT NULL,
  `paid` enum('yes','no') NOT NULL DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table scoreboard
# ------------------------------------------------------------

DROP TABLE IF EXISTS `scoreboard`;

CREATE TABLE `scoreboard` (
  `team` int(3) NOT NULL,
  `score` int(3) NOT NULL,
  `division` enum('Novice','Advanced') NOT NULL,
  UNIQUE KEY `team` (`team`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table submissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `submissions`;

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(3) NOT NULL,
  `problem` int(2) NOT NULL,
  `time` datetime NOT NULL,
  `subid` varchar(6) NOT NULL,
  `code` longtext NOT NULL,
  `output` longtext NOT NULL,
  `success` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `error` enum('None','Syntax','Runtime','Timeout') NOT NULL DEFAULT 'None',
  `real_output` longtext NOT NULL,
  `appealed` enum('No','Yes') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table teams
# ------------------------------------------------------------

DROP TABLE IF EXISTS `teams`;

CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(3) NOT NULL,
  `school` varchar(256) NOT NULL,
  `division` enum('Novice','Advanced') NOT NULL DEFAULT 'Novice',
  `member1` varchar(256) NOT NULL,
  `member2` varchar(256) NOT NULL,
  `member3` varchar(256) NOT NULL,
  `password` varchar(512) NOT NULL,
  `auth` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table written
# ------------------------------------------------------------

DROP TABLE IF EXISTS `written`;

CREATE TABLE `written` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `team` int(3) NOT NULL,
  `score` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
