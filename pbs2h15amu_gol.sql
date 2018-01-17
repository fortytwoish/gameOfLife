-- phpMyAdmin SQL Dump
-- version 4.4.15.8
-- https://www.phpmyadmin.net
--
-- Host: mysqlpb.pb.bib.de
-- Erstellungszeit: 19. Feb 2017 um 21:47
-- Server-Version: 5.6.30-log
-- PHP-Version: 5.6.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `pbs2h15amu_gol`
--
CREATE DATABASE IF NOT EXISTS `pbs2h15amu_gol` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `pbs2h15amu_gol`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `board`
--

DROP TABLE IF EXISTS `board`;
CREATE TABLE IF NOT EXISTS `board` (
  `bid` varchar(255) NOT NULL,
  `boardstate` longtext NOT NULL,
  `boardname` varchar(64) NOT NULL,
  `sid` varchar(36) DEFAULT NULL,
  `uid` varchar(36) NOT NULL,
  `money` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `progress`
--

DROP TABLE IF EXISTS `progress`;
CREATE TABLE IF NOT EXISTS `progress` (
  `sid` varchar(36) NOT NULL DEFAULT '',
  `score` int(11) NOT NULL,
  `uid` varchar(36) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `size`
--

DROP TABLE IF EXISTS `size`;
CREATE TABLE IF NOT EXISTS `size` (
  `sid` varchar(36) NOT NULL DEFAULT '',
  `dimension` int(11) NOT NULL,
  `description` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `size`
--

INSERT INTO `size` (`sid`, `dimension`, `description`) VALUES
('36169e93-f617-11e6-b099-000c29dc19ed', 15, 'XS'),
('3616a5c6-f617-11e6-b099-000c29dc19ed', 50, 'S'),
('3616ab76-f617-11e6-b099-000c29dc19ed', 100, 'M'),
('3616b12c-f617-11e6-b099-000c29dc19ed', 200, 'L'),
('3616bafc-f617-11e6-b099-000c29dc19ed', 500, 'XL');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `uid` varchar(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `pw` varchar(255) NOT NULL,
  `score` bigint(20) NOT NULL,
  `salt` varchar(40) NOT NULL,
  `achievements` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `board`
--
ALTER TABLE `board`
  ADD PRIMARY KEY (`bid`),
  ADD UNIQUE KEY `boardname` (`boardname`),
  ADD KEY `test3` (`sid`),
  ADD KEY `test4` (`uid`);

--
-- Indizes für die Tabelle `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`sid`,`uid`),
  ADD KEY `test` (`sid`),
  ADD KEY `test2` (`uid`);

--
-- Indizes für die Tabelle `size`
--
ALTER TABLE `size`
  ADD PRIMARY KEY (`sid`),
  ADD KEY `sid` (`sid`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `board`
--
ALTER TABLE `board`
  ADD CONSTRAINT `fk_boardsize` FOREIGN KEY (`sid`) REFERENCES `size` (`sid`),
  ADD CONSTRAINT `fk_boarduser` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`);

--
-- Constraints der Tabelle `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `fk_size` FOREIGN KEY (`sid`) REFERENCES `size` (`sid`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
