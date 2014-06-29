CREATE DATABASE `inventory` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `inventory`;

DROP TABLE `data`;
CREATE TABLE IF NOT EXISTS `data` (
  `code` varchar(24) NOT NULL,
  `name` tinytext NOT NULL,
  `memo` text,
  `location` tinytext NOT NULL,
  `hash` tinytext NOT NULL,
  `state` int(11) DEFAULT '10',
  PRIMARY KEY (`qr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;