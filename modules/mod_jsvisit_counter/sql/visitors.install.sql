--
-- Tabellenstruktur für Tabelle `#__visitors`
--
CREATE TABLE IF NOT EXISTS `#__visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `count` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `#__visitors`
--

INSERT INTO `#__visitors` (`id`, `date`, `count`, `description`) VALUES
(1, '2018-10-01', 0, 'Heute'),
(2, '2018-10-01', 0, 'Gestern'),
(3, '2018-10-01', 0, 'Diese Woche'),
(4, '2018-10-01', 0, 'Letzte Woche'),
(5, '2018-10-01', 0, 'Dieser Monat'),
(6, '2018-10-01', 0, 'letzter Monat'),
(7, '2018-10-01', 0, 'Total') ON DUPLICATE KEY UPDATE description = VALUES(description);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `#__visitors_country`
--
CREATE TABLE IF NOT EXISTS `#__visitors_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` char(2) NOT NULL UNIQUE,
  `name` varchar(64) NOT NULL,
  `count` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`,`country`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur für Tabelle `#__visitors_debug`
--
-- DROP TABLE IF EXISTS `#__visitors_debug`;
-- CREATE TABLE IF NOT EXISTS `#__visitors_debug` (
--  `id` int(11) NOT NULL AUTO_INCREMENT,
--  `date` datetime NOT NULL,
--  `ip` varchar(16) NOT NULL,
--  `host` varchar(255) NOT NULL,
--  `country` varchar(5) NOT NULL,
--  `server` varchar(255) NOT NULL,  
--  PRIMARY KEY (`id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
--
-- delete old Tables
--
DROP TABLE IF EXISTS `#__visitors_ip2nation` ;
DROP TABLE IF EXISTS `#__visitors_ip2nationcountries` ;