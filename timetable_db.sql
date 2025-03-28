-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Már 24. 07:59
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Adatbázis: `timetable_db`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `time_slot_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_hungarian_ci;

--
-- A tábla adatainak kiíratása `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_id`, `time_slot_id`, `date`, `status`, `notes`, `created_at`) VALUES
(17, 9, 9, 32, '2025-03-21', 'confirmed', NULL, '2025-03-21 07:38:48');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `created_at` datetime NOT NULL,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `recommended_time` varchar(100) DEFAULT NULL,
  `recommended_to` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_hungarian_ci;

--
-- A tábla adatainak kiíratása `services`
--

INSERT INTO `services` (`id`, `name`, `instructor_id`, `duration`, `price`, `description`, `is_active`, `recommended_time`, `recommended_to`) VALUES
(8, 'Life Coaching Képzés', 10, 60, 5990.00, 'Fejleszd önismereted és találj rá a belső erőforrásaidra! Ebben a képzésben segítünk felismerni, hogyan érheted el a legjobbat önmagadból, és támogatunk abban, hogy magabiztosabban lépj az önmegvalósítás útjára.', 1, '6 hét, heti 1 alkalom', 'azoknak, akik önismereti és életvezetési iránymutatást keresnek'),
(9, 'Business Coaching Tréning', 11, 60, 4990.00, 'Fejleszd vállalkozói képességeidet és válj vezetővé a szakmádban! Ez a képzés segít abban, hogy jobb döntéseket hozz, hatékonyabban kommunikálj és felkészültebben építsd a vállalkozásodat.', 1, '8 hét, heti 2 alkalom', 'vezetőknek és vállalkozóknak, akik fejlődni szeretnének'),
(10, 'Stresszkezelés és Reziliencia Workshop', 12, 60, 4999.00, 'Ismerd meg, hogyan kezelheted hatékonyabban a mindennapi stresszt, és hogyan építheted ki a belső erőforrásaidat, hogy könnyebben alkalmazkodj a kihívásokhoz. Ez a workshop gyakorlati tanácsokkal és technikákkal lát el.', 1, '4 hét, heti 1 alkalom', 'mindenkinek, aki szeretné jobban kezelni a stresszt'),
(11, 'Karriertervezés és Énmárka Építés', 11, 60, 11990.00, 'Ez a program segít a személyes és szakmai énmárka kialakításában, valamint karriered tudatos tervezésében. Tanuld meg, hogyan alakíthatsz ki egyedi és erős énmárkát, amely segíti a céljaid elérését.', 1, '6 hét, heti 1 alkalom', 'azoknak, akik szeretnék tudatosan építeni karrierjüket'),
(12, 'Hatékony Kommunikáció és Konfliktuskezelés', 10, 60, 6590.00, 'Sajátítsd el azokat a kommunikációs technikákat, amelyek segítenek a személyes és szakmai kapcsolataid fejlesztésében. A képzés során gyakorlati példákon keresztül tanulhatod meg a konstruktív konfliktuskezelést és az asszertív kommunikációt.', 1, '5 hét, heti 1 alkalom', 'mindenkinek, aki fejleszteni szeretné kommunikációs készségeit és kapcsolatait'),
(13, 'Mindfulness és Produktivitás Program', 12, 60, 5490.00, 'Fedezd fel, hogyan növelheted a koncentrációdat és produktivitásodat a tudatos jelenlét gyakorlásával. A program ötvözi a mindfulness technikákat a modern időgazdálkodási módszerekkel, hogy segítsen egyensúlyt teremteni az élet különböző területei között.', 1, '7 hét, heti 1 alkalom', 'azoknak, akik szeretnének tudatosabban és hatékonyabban élni és dolgozni');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_hungarian_ci;

--
-- A tábla adatainak kiíratása `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'min_advance_hours', '24', '2025-01-09 12:26:35'),
(2, 'max_advance_days', '60', '2025-01-09 12:26:35'),
(3, 'work_day_start', '09:00', '2025-01-09 12:26:35'),
(4, 'work_day_end', '17:00', '2025-01-09 12:26:35');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `oauth_provider` varchar(20) DEFAULT NULL,
  `oauth_id` varchar(100) DEFAULT NULL,
  `oauth_token` text DEFAULT NULL,
  `oauth_token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `is_instructor` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_hungarian_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `oauth_provider`, `oauth_id`, `oauth_token`, `oauth_token_expires`, `created_at`, `is_admin`, `is_instructor`) VALUES
(1, 'a', 'a@a.com', '$2y$10$2/9fQxbveIEMJUAqwxVUoeXhaSp5oBFRVUCHyPg.gRUYd7o9Dnzke', NULL, NULL, NULL, NULL, '2024-12-17 07:01:28', 1, 1),
(2, 'a2', 'a2@gmail.com', '$2y$10$yaxPnN7.hUnADBm52Cla0eYDgoBA9vLAwueYCmEGXw8Vg7RlgeuBq', NULL, NULL, NULL, NULL, '2024-12-17 07:01:37', 0, 0),
(5, 'oktato', 'oktato@gmail.com', '$2y$10$OD9qKgUEk48bl9eteB3/CeR3WmF.1nJ3TkwqDsuTO5cTNOtv.fmyO', NULL, NULL, NULL, NULL, '2025-01-15 11:24:20', 0, 1),
(8, 'firestarter', 'firestarterakademia@gmail.com', '$2y$10$/kEpQNHK66wUxjfaVl9dR.hb9IEyWSiNalSiKVCARurM8U.AqXEjq', NULL, NULL, NULL, NULL, '2025-03-07 07:34:25', 0, 0),
(9, '2025 Projekt', 'projekt2025teszt@gmail.com', '$2y$10$6B5O8l2bet2C66I8Iznrc.g66pqEodCRQ5XctDiivmAfulXkTjT9C', 'google', '113675111541686473444', NULL, NULL, '2025-03-07 10:32:09', 0, 0),
(10, 'kiss.janos', 'janos@firestarter.hu', '$2y$10$QL97/RtmJHvYMjstq9oho.RIICd1VRoM0oJNw9Bu/u4/nxU9IxZdK', NULL, NULL, NULL, NULL, '2025-03-24 10:50:32', 0, 1),
(11, 'nagy.anna', 'anna@firestarter.hu', '$2y$10$.I2VQS3f0eXcg5btq86X0uoP/si6ZZWLovsfcKDRpdghsINIFrj0O', NULL, NULL, NULL, NULL, '2025-03-24 10:50:50', 0, 1),
(12, 'kovacs.peter', 'peter@firestarter.hu', '$2y$10$ARoXYKEFkjhlNNR9I0G3Uu8GIRRRXuQdOBQpkQq8Y6OT.vkmNcgT.', NULL, NULL, NULL, NULL, '2025-03-24 10:51:36', 0, 1);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `time_slot_id` (`time_slot_id`);

--
-- A tábla indexei `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- A tábla indexei `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_oauth` (`oauth_provider`,`oauth_id`),
  ADD KEY `idx_email_oauth` (`email`,`oauth_provider`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT a táblához `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT a táblához `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT a táblához `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`time_slot_id`) REFERENCES `time_slots` (`id`);

--
-- Megkötések a táblához `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `time_slots`
--
ALTER TABLE `time_slots`
  ADD CONSTRAINT `time_slots_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
