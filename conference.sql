-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1:3306
-- Vytvořeno: Úte 16. led 2018, 20:15
-- Verze serveru: 5.7.19
-- Verze PHP: 7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `conference`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `article`
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `keywords` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `pdf_url` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `status` enum('reviewed','accepted','rejected','new') COLLATE utf8_czech_ci NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`article_id`),
  KEY `author` (`author`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `article`
--

INSERT INTO `article` (`article_id`, `author`, `title`, `description`, `keywords`, `pdf_url`, `status`, `added`, `modified`) VALUES
(1, 14, 'Test1', 'I have to test this function', 'key, word', 'C:/wamp64/www/Conference/articles_pdf/201708001.pdf', 'accepted', '2018-01-09 09:44:57', NULL),
(2, 14, 'Test2', 'hahaha', 'tramtadadááááá', 'C:/wamp64/www/Conference/articles_pdf/AS_65904_TG_611965_US_1094-1.pdf', 'new', '2018-01-09 09:45:33', NULL),
(3, 14, 'Test_userId', 'hahah', 'testing', 'C:/wamp64/www/Conference/articles_pdf/AS_65904_TG_611965_US_1094-1.pdf', 'new', '2018-01-09 14:06:45', '2018-01-09 13:24:32');

-- --------------------------------------------------------

--
-- Struktura tabulky `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE IF NOT EXISTS `review` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `article` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `overview` tinyint(11) DEFAULT NULL,
  `actuality` tinyint(11) DEFAULT NULL,
  `facts` tinyint(11) DEFAULT NULL,
  `comment` varchar(11) COLLATE utf8_czech_ci DEFAULT NULL,
  `changed` timestamp NULL DEFAULT NULL,
  `reviewed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `review`
--

INSERT INTO `review` (`review_id`, `article`, `author`, `overview`, `actuality`, `facts`, `comment`, `changed`, `reviewed`) VALUES
(1, 1, 13, 3, 5, 3, 'asdf', '2018-01-16 17:20:10', '2018-01-16 16:53:10'),
(2, 1, 15, NULL, NULL, NULL, NULL, NULL, '2018-01-16 16:53:10'),
(3, 2, 13, NULL, NULL, NULL, NULL, NULL, '2018-01-16 16:53:23'),
(4, 2, 15, NULL, NULL, NULL, NULL, NULL, '2018-01-16 16:53:23'),
(5, 3, 13, NULL, NULL, NULL, NULL, NULL, '2018-01-16 16:53:27'),
(6, 3, 15, NULL, NULL, NULL, NULL, NULL, '2018-01-16 16:53:27');

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `status` enum('author','reviewer','administrator') COLLATE utf8_czech_ci NOT NULL,
  `mail` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `nickname` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `status`, `mail`, `registered`) VALUES
(12, 'A', '0cc175b9c0f1b6a831c399e269772661', 'administrator', 'a@a.cz', '2017-12-29 17:25:01'),
(13, 'B', '92eb5ffee6ae2fec3ad71c777531578f', 'reviewer', 'b@b.cz', '2017-12-29 18:41:37'),
(14, 'C', '4a8a08f09d37b73795649038408b5f33', 'author', 'c@c.cz', '2018-01-09 09:43:37'),
(15, 'D', '8277e0910d750195b448797616e091ad', 'reviewer', 'd', '2018-01-09 11:57:47');

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`author`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
