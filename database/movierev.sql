-- MovieREV database
--
-- This file is the project's complete database: the 5 application tables
-- (users, movies, reviews, lists, list_movies) exported from the live
-- `movierev` database. It is kept in sync with that database.
--
-- The 8 Laravel framework tables (sessions, cache, cache_locks, jobs,
-- job_batches, failed_jobs, password_reset_tokens, migrations) are
-- deliberately NOT here: they were dropped, and the surrounding code was
-- moved off them. Sessions, cache and the queue no longer use the database
-- (see .env / config), so these 5 tables are all the app needs to run.
--
-- `users` combines Laravel's stock structure with movierev.sql's columns:
--     id, name, email, email_verified_at, password, remember_token,
--     created_at, updated_at   <- Laravel's stock structure
--     username, usertype       <- added from movierev.sql
-- `password_hash` from movierev.sql is folded into `password`.
-- lists.user_id and reviews.user_id are bigint unsigned to match
-- users.id, which is what MySQL requires of an FK column.
--
-- Data: only `users` (accounts created through the register form —
-- re-export the users INSERTs after new sign-ups so this file stays in
-- sync). movies, reviews, lists and list_movies are empty.
--
-- To import: select the `movierev` database in phpMyAdmin and use Import,
-- or from a shell:
--     mysql -h 127.0.0.1 -u root movierev < database/movierev.sql
-- DROP TABLE IF EXISTS in each section makes re-importing safe.

-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: movierev
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `list_movies`
--

DROP TABLE IF EXISTS `list_movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `list_movies` (
  `list_id` int NOT NULL,
  `movie_id` int NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`list_id`,`movie_id`),
  KEY `movie_id` (`movie_id`),
  CONSTRAINT `list_movies_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `lists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `list_movies_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `list_movies`
--

/*!40000 ALTER TABLE `list_movies` DISABLE KEYS */;
/*!40000 ALTER TABLE `list_movies` ENABLE KEYS */;

--
-- Table structure for table `lists`
--

DROP TABLE IF EXISTS `lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lists`
--

/*!40000 ALTER TABLE `lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `lists` ENABLE KEYS */;

--
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `release_year` smallint DEFAULT NULL,
  `genre` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `poster_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movies`
--

/*!40000 ALTER TABLE `movies` DISABLE KEYS */;
/*!40000 ALTER TABLE `movies` ENABLE KEYS */;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `movie_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `body` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `one_review_per_user` (`user_id`,`movie_id`),
  KEY `movie_id` (`movie_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usertype` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'Jesse Rivera','jes@example.com',NULL,'$2y$12$0um933qGXk0/1E3don7Eje1rTy2EPUVj/kqcfpO0lON0vax1aeUYS','HuWxayHCUkqUj5liE8duJtd0fVoRs6oAjZ4j4MU4ckjCg60NoXyw6kc17Lfn','jes_','user','2026-10-06 12:02:53','2026-10-06 12:02:53'),(8,'Wincie Jade Breva','brevawinciejade@gmail.com',NULL,'$2y$12$Q8TGqoKDGfIz9dev7XVJvu4rJeCV2TTNKZ5lTe9.0k0P0ynEDS6yW','Ta9SLbtS3LPLEa72D2KQGKNuoMH52iyFYxZKUz5E5ylarWEnsFaCJ96Am0rI','wnc','user','2026-10-08 01:52:24','2026-10-08 01:52:24');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-14 11:12:56
