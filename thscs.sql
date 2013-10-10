SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE `clarifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(3) NOT NULL,
  `problem` int(3) NOT NULL,
  `message` varchar(8192) NOT NULL,
  `global` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

CREATE TABLE `pizza` (
  `team` int(3) NOT NULL,
  `cheese` int(1) NOT NULL,
  `pepperoni` int(1) NOT NULL,
  `sausage` int(1) NOT NULL,
  `cost` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `scoreboard` (
  `team` int(3) NOT NULL,
  `score` varchar(3) NOT NULL,
  `division` enum('Novice','Advanced') NOT NULL DEFAULT 'Novice'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(3) NOT NULL,
  `problem` int(2) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subid` varchar(6) NOT NULL,
  `code` longtext NOT NULL,
  `output` longtext NOT NULL,
  `success` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `error` enum('None','Syntax','Compile') NOT NULL DEFAULT 'None',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team` int(3) NOT NULL,
  `school` varchar(256) NOT NULL,
  `division` enum('Novice','Advanced') NOT NULL DEFAULT 'Novice',
  `member1` varchar(256) NOT NULL,
  `member2` varchar(256) NOT NULL,
  `member3` varchar(256) NOT NULL,
  `password` varchar(512) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

CREATE TABLE `written` (
  `name` varchar(128) NOT NULL,
  `school` varchar(256) NOT NULL,
  `score` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
