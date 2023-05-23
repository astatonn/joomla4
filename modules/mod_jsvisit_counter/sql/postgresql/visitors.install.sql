--
-- Tabellenstruktur für Tabelle `#__visitors`
--
CREATE TABLE IF NOT EXISTS #__visitors (
  id int NOT NULL,
  date date NOT NULL,
  count int NOT NULL,
  description varchar(50) NOT NULL,
  PRIMARY KEY (id)
); 
--
-- Daten für Tabelle `#__visitors`
--

INSERT INTO #__visitors (id, date, count, description) VALUES
(1, '2018-10-01', 0, 'Heute'),
(2, '2018-10-01', 0, 'Gestern'),
(3, '2018-10-01', 0, 'Diese Woche'),
(4, '2018-10-01', 0, 'Letzte Woche'),
(5, '2018-10-01', 0, 'Dieser Monat'),
(6, '2018-10-01', 0, 'letzter Monat'),
(7, '2018-10-01', 0, 'Total');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `#__visitors_country`
--
CREATE TABLE IF NOT EXISTS #__visitors_country (
  id SERIAL,
  country char(2) NOT NULL UNIQUE,
  name varchar(64) NOT NULL,
  count int NOT NULL,
  PRIMARY KEY (id,country)
);
