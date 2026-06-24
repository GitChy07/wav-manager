-- phpMyAdmin SQL Dump
-- Erstellungszeit: 18. Jun 2026

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `wav_manager`
--
DROP DATABASE IF EXISTS `wav_manager`;
CREATE DATABASE `wav_manager` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `wav_manager`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `songs`
--
CREATE TABLE `songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `bpm` int(11) DEFAULT NULL,
  `music_key` varchar(10) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `samples`
--
CREATE TABLE `samples` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `song_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `bpm` int(11) NOT NULL,
  `music_key` varchar(10) DEFAULT NULL,
  `source_description` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `song_id` (`song_id`),
  CONSTRAINT `samples_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `samples_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `one_shots`
--
CREATE TABLE `one_shots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `song_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `song_id` (`song_id`),
  CONSTRAINT `oneshots_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `oneshots_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `users`
--
INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(3, 'producer1', '$2y$10$gdscqoXyMHnOjSVvKKv5I.dF7/dfBKNWYkRkhSpclnlOPny3le6CO', '2026-06-11 13:47:03');

--
-- Daten für Tabelle `songs`
--
INSERT INTO `songs` (`id`, `user_id`, `title`, `bpm`, `music_key`, `tags`, `file_path`) VALUES
(1, 3, 'Demo Song - Summer Vibes', 120, 'Am', '#summer #house', 'demo_loop.wav');

--
-- Daten für Tabelle `samples`
--
INSERT INTO `samples` (`id`, `user_id`, `song_id`, `title`, `bpm`, `music_key`, `source_description`, `file_path`) VALUES
(1, 3, 1, 'Demo Loop - Synth Chords', 120, 'Am', 'Recorded with Prophet 08', 'demo_loop.wav');

--
-- Daten für Tabelle `one_shots`
--
INSERT INTO `one_shots` (`id`, `user_id`, `song_id`, `title`, `file_path`) VALUES
(1, 3, 1, 'Demo Kick Drum', 'demo_kick.wav'),
(2, 3, 1, 'Demo Snare Drum', 'demo_snare.wav');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
