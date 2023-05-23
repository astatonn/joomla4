--
-- Tabellenstruktur fÃ¼r Tabelle `#__visitors_debug`
--
DROP TABLE IF EXISTS `#__visitors_debug`;
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