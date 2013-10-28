SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `clarifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(3) NOT NULL,
  `problem` int(3) NOT NULL,
  `message` varchar(8192) NOT NULL,
  `reply` varchar(8219) NOT NULL,
  `global` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

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
  `appealed` enum('Yes','No') NOT NULL DEFAULT 'No',
  `real_output` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

CREATE TABLE `written` (
  `name` varchar(128) NOT NULL,
  `school` varchar(256) NOT NULL,
  `score` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
