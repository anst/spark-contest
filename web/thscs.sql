SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `reply` varchar(8219) NOT NULL,
  `global` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

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
  `time` datetime NOT NULL,
  `subid` varchar(6) NOT NULL,
  `code` longtext NOT NULL,
  `output` longtext NOT NULL,
  `success` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `error` enum('None','Syntax','Runtime','Timeout') NOT NULL DEFAULT 'None',
  `real_output` longtext NOT NULL,
  `appealed` enum('No','Yes') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

CREATE TABLE `written` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `team` int(3) NOT NULL,
  `score` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
