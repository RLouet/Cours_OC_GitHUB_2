-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  sam. 27 fév. 2021 à 17:12
-- Version du serveur :  10.4.10-MariaDB
-- Version de PHP :  7.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `oc_daphpsymfony_p5`
--

-- --------------------------------------------------------

--
-- Structure de la table `blog`
--

DROP TABLE IF EXISTS `blog`;
CREATE TABLE IF NOT EXISTS `blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `logo` varchar(50) NOT NULL,
  `teaser_phrase` text NOT NULL,
  `contact_mail` varchar(254) NOT NULL,
  `cv` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `blog`
--

INSERT INTO `blog` (`id`, `lastname`, `firstname`, `email`, `phone`, `logo`, `teaser_phrase`, `contact_mail`, `cv`) VALUES
(1, 'LOuET', 'Romain', 'romainlouet@romsworld.net', '0614336004', 'logo-Romain_LOUET.png', 'arezergg erg g er gerg er g', 'contact@romsworld.net', 'cv-Romain_LOUET.pdf'),
(2, 'test', 'Mister', 'contact@romsworld.net', '0612345678', 'logo-Mister_test.png', 'Phrase de teaser du blog 2', 'maildecontact@blog2.fr', 'cv-du-blog-2.pdf');

-- --------------------------------------------------------

--
-- Structure de la table `blog_post`
--

DROP TABLE IF EXISTS `blog_post`;
CREATE TABLE IF NOT EXISTS `blog_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(128) NOT NULL,
  `edit_date` datetime NOT NULL,
  `hero_id` int(11) DEFAULT NULL,
  `chapo` tinytext NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_blog_post_fk` (`user_id`) USING BTREE,
  KEY `post_image_blog_post_fk` (`hero_id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `comment`
--

DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `blog_post_id` int(11) NOT NULL,
  `validated` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_comment_fk` (`user_id`),
  KEY `blog_post_comment_fk` (`blog_post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `post_image`
--

DROP TABLE IF EXISTS `post_image`;
CREATE TABLE IF NOT EXISTS `post_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `url` varchar(128) NOT NULL,
  `blog_post_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `blog_post_post_image_fk` (`blog_post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `remembered_login`
--

DROP TABLE IF EXISTS `remembered_login`;
CREATE TABLE IF NOT EXISTS `remembered_login` (
  `token_hash` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`token_hash`),
  KEY `user_remembered_login_fk` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `skill`
--

DROP TABLE IF EXISTS `skill`;
CREATE TABLE IF NOT EXISTS `skill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(30) NOT NULL,
  `blog_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_skill_fk` (`blog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `social_network`
--

DROP TABLE IF EXISTS `social_network`;
CREATE TABLE IF NOT EXISTS `social_network` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `logo` varchar(256) NOT NULL,
  `url` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_social_network_fk` (`blog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `lastname` varchar(128) NOT NULL,
  `firstname` varchar(128) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'ROLE_USER',
  `password_reset_hash` varchar(64) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  `activation_hash` varchar(64) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `new_email` varchar(255) DEFAULT NULL,
  `banished` tinyint(4) NOT NULL DEFAULT 0,
  `registration_date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `password_reset_hash` (`password_reset_hash`),
  UNIQUE KEY `activation_hash` (`activation_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `blog_post`
--
ALTER TABLE `blog_post`
  ADD CONSTRAINT `post_image_blog_post_fk` FOREIGN KEY (`hero_id`) REFERENCES `post_image` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_blog_post_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `blog_post_comment_fk` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_post` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_comment_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `post_image`
--
ALTER TABLE `post_image`
  ADD CONSTRAINT `blog_post_post_image_fk` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_post` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `remembered_login`
--
ALTER TABLE `remembered_login`
  ADD CONSTRAINT `user_remembered_login_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `skill`
--
ALTER TABLE `skill`
  ADD CONSTRAINT `blog_skill_fk` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Contraintes pour la table `social_network`
--
ALTER TABLE `social_network`
  ADD CONSTRAINT `blog_social_network_fk` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
