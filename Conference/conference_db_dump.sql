-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1:3306
-- Vytvořeno: Úte 23. led 2018, 05:41
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `article`
--

INSERT INTO `article` (`article_id`, `author`, `title`, `description`, `keywords`, `pdf_url`, `status`, `added`, `modified`) VALUES
(4, 16, 'Article about MES', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Mauris elementum mauris vitae tortor. Aenean placerat. Mauris tincidunt sem sed arcu. Nulla quis diam. Aliquam erat volutpat.', 'Lorem ipsum dolor sit amet', 'C:/wamp64/www/Conference/articles_pdf/16_Analyza_2017.pdf', 'accepted', '2018-01-22 18:26:48', NULL),
(5, 16, 'Second article bout MES', 'Lorem ipsum dolor sit amet,Lorem ipsum dolor sit amet,Lorem ipsum dolor sit amet,Lorem ipsum dolor sit amet,Lorem ipsum dolor sit amet,Lorem ipsum dolor sit amet,', 'Lorem ipsum dolor sit amet,', 'C:/wamp64/www/Conference/articles_pdf/16_Analyza2016.pdf', 'new', '2018-01-22 18:27:26', NULL),
(6, 17, 'Another article:)', 'blablablahblablablahblablablahblablablahblablablahblablablahblablablahblablablah', 'blablablahblablablah', 'C:/wamp64/www/Conference/articles_pdf/17_Analyza_2017.pdf', 'new', '2018-01-22 18:27:56', NULL),
(7, 17, 'Wooooaahhhh!', 'testingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtestingtesting', 'testing testing testing', 'C:/wamp64/www/Conference/articles_pdf/17_DataMan 8600 Datasheet.pdf', 'new', '2018-01-22 18:28:26', NULL);

--
-- Spouště `article`
--
DROP TRIGGER IF EXISTS `deleteReviews`;
DELIMITER $$
CREATE TRIGGER `deleteReviews` AFTER DELETE ON `article` FOR EACH ROW DELETE FROM review 
WHERE review.article = old.article_id
$$
DELIMITER ;

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
  `comment` varchar(1024) COLLATE utf8_czech_ci DEFAULT NULL,
  `changed` timestamp NULL DEFAULT NULL,
  `reviewed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `review`
--

INSERT INTO `review` (`review_id`, `article`, `author`, `overview`, `actuality`, `facts`, `comment`, `changed`, `reviewed`) VALUES
(9, 4, 13, 1, 3, 5, 'hahahahahahahahahahahahahahahahahahahahahahahahahahahahahahahahahahahaha', '2018-01-22 20:39:55', '2018-01-22 21:36:42'),
(10, 4, 14, 3, 2, 3, 'Co si já myslím?', '2018-01-22 20:40:50', '2018-01-22 21:36:42'),
(11, 4, 15, 5, 5, 3, 'To bylo super', '2018-01-22 20:41:17', '2018-01-22 21:36:42'),
(12, 5, 13, 5, 4, 5, 'COMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENTCOMMENT', '2018-01-22 20:40:25', '2018-01-22 21:36:46'),
(13, 5, 14, NULL, NULL, NULL, NULL, NULL, '2018-01-22 21:36:46'),
(14, 6, 15, NULL, NULL, NULL, NULL, NULL, '2018-01-22 21:36:51'),
(15, 6, 13, NULL, NULL, NULL, NULL, NULL, '2018-01-22 21:36:51'),
(16, 7, 14, NULL, NULL, NULL, NULL, NULL, '2018-01-22 21:36:55'),
(17, 7, 13, NULL, NULL, NULL, NULL, NULL, '2018-01-22 21:36:55');

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Vypisuji data pro tabulku `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `status`, `mail`, `registered`) VALUES
(12, 'A', '0cc175b9c0f1b6a831c399e269772661', 'administrator', 'a@a.cz', '2017-12-29 17:25:01'),
(13, 'B', '92eb5ffee6ae2fec3ad71c777531578f', 'reviewer', 'b@b.cz', '2017-12-29 18:41:37'),
(14, 'C', '4a8a08f09d37b73795649038408b5f33', 'reviewer', 'c@c.cz', '2018-01-09 09:43:37'),
(15, 'D', '8277e0910d750195b448797616e091ad', 'reviewer', 'd', '2018-01-09 11:57:47'),
(16, 'Author_1', '8a8bb7cd343aa2ad99b7d762030857a2', 'author', 'author@a1.cz', '2018-01-22 18:22:30'),
(17, 'Author_2', '693a9fdd4c2fd0700968fba0d07ff3c0', 'author', 'a@author_2.cz', '2018-01-22 18:22:57'),
(21, 'test', '7ed21143076d0cca420653d4345baa2f', 'author', 'jjsdf', '2018-01-23 05:35:36'),
(22, 'Test2', 'c81e728d9d4c2f636f067f89cc14862c', 'author', '2', '2018-01-23 05:36:19');

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
