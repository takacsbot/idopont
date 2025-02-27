-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Feb 27. 08:12
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- A tábla adatainak kiíratása `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_id`, `token`, `expires_at`) VALUES
(0, 0, '9bf7159adf88c060bdd506595294ebed4509adef1dc43bbad02d6654cd4a4f9d', '2024-11-13 08:51:57'),
(0, 0, '5a93098d86f0a8db812a7d34873909acdfc4729af5c00efedcc04a4ab3eb38fc', '2024-11-13 09:28:08'),
(0, 0, '75cbcafa24004387a101c6048d45a4ed6f8b99c7c6eb4c9d247b8357dc8289c0', '2024-11-15 09:36:07'),
(0, 0, '47ff50cb19a587ad1c7ac19004af4327a80aea9267055eb7a15a841c8856832a', '2024-12-07 08:22:44'),
(0, 0, '9f12c20e08c7109463bb29593809755e2661986d86cb8200db7798a489cfa55c', '2024-12-17 09:36:03'),
(0, 0, 'a4d72e4f627e8260ab9ef0dbdc8ed621722df868fe71e91792cddd0f5cc7ac78', '2024-12-17 10:11:11'),
(0, 0, '057c7a3adf7c1ab8143f43a9ea8171e3a45e742fbf6903939a6a3b96843fe7e7', '2024-12-17 10:13:29'),
(0, 0, 'd155198a664d1f8a46e820ae1dc81a447dbd40585e61a20f92176129cfe5ed7a', '2024-12-17 11:07:29'),
(0, 1, 'd5899785027b5c7cadf2c2dbb7840239a18d073805d388bd3a5ee382689d360b', '2024-12-18 07:03:07'),
(0, 1, 'cf8833004e721d7cd17fc695dce01b82a1cdb886c6cfa8f9a4a4784eecc0a60d', '2024-12-18 07:14:15'),
(0, 1, '4937460d5c334b71da5274998ee89fa973509e0fba59d4dd0c624f5851ba3772', '2024-12-19 11:24:54'),
(0, 1, 'df2017dd2ed0aa14db59a97278c62d0084250d26123efa557253e3454b4878f2', '2025-01-10 12:00:22'),
(0, 1, '754e0e63478aafc8879c604e0fe4df706e1a0fa7b4e9bf95a235d78e1b23f753', '2025-01-14 11:19:42'),
(0, 1, 'fa5a9ade2851182bc62731403010b67da7616fa9c6b79534d31c078d556806bf', '2025-01-16 11:05:31'),
(0, 5, '8918270606808fcf100eef7eb244763684423e5a96a4047d82a21b07d4b08c5b', '2025-01-16 11:24:54'),
(0, 5, '3307f6d38f76f8b9fb870c32a93aeab6ba6c063be91dc03606ab0bd33f29b700', '2025-01-16 11:27:32'),
(0, 5, 'dbbbd0aba032d1c2aa0f3caf096d573311481745328a0bb077e0a0387b5dbed3', '2025-01-16 11:38:07'),
(0, 5, '25cfbfdf90152fb7e7cfb711b29e5a234371cba6b2cd06523b6efd3f7b74c80d', '2025-01-16 11:40:51'),
(0, 1, '44779cf274b792be0c5b4dee92e52470b39838e10f64093f67e2dc96ba3ec552', '2025-01-29 06:44:56'),
(0, 1, '8722c07bee4ec80c6304e2f4a16c99c3b27ae226bb00f616c4dbd6f18023168a', '2025-01-29 06:45:10'),
(0, 1, 'd6308db502a9adebb466f98fd32bf73ce38d77ff40bf57bfffbfe9d1fb413ee4', '2025-01-29 06:45:30'),
(0, 5, '463a74fae7f2abd98965ce2f7055a21e2bef0a813863a6eec7c739823181b109', '2025-01-29 06:46:49'),
(0, 1, 'ff99ed6b792f699ade7fefbac4e4aacd228935a645a5113120a1ccbd38e7a366', '2025-01-29 06:55:36'),
(0, 5, '2ef2a3e149f0cdc57675dfe31595c79779784b72c70c3c71bbe6a6628d4e57c8', '2025-01-29 07:24:19'),
(0, 1, '09cdfc352cf6123aad35fd0bf0a15ed6f7d91ce112e07a62144f5bb7d5b6ee8e', '2025-01-29 07:28:41'),
(0, 1, '792b27b22f9201b5b2c8dfe8c16e737dfdb4b77b0c09645691684e4ed2baf05e', '2025-01-29 07:45:39'),
(0, 5, '0cbbef6cbb97442af4634f0de0503d6640f1557560ea12bb5fb810bd1cc27868', '2025-01-31 08:41:15'),
(0, 1, '79b4406d9b172b78ff84806787e6b1451334c0ae45ca8f1508167a4f306cf7f9', '2025-01-31 08:41:50'),
(0, 1, 'f647a01e3b0ed4befaa8b0b035680af91abd43d3991054493878aae1433b96d0', '2025-01-31 09:00:31'),
(0, 5, '17d67520b5e15f32e99400378ad1cffa2460c6615a90d6567487611dc4f2121b', '2025-01-31 09:00:49'),
(0, 1, '458dbfa04894535ecbfc354ddc2a49d5f694b1981c0f38092c8747ca66ea1c7d', '2025-01-31 09:21:55'),
(0, 1, '4a81a99f1efe7e191e92a94dee354ddd96747d80518b92fc5d01d3c75753efd8', '2025-01-31 09:22:46'),
(0, 5, '2da491aafa9603809a6f3faa78da5b8f3a8ddca32115f29609552861cbb3ec01', '2025-01-31 09:23:00'),
(0, 1, 'fce1804c23d5debf89a3831b38c2eb9838e0cb50edfff75c67710cb9e3c0ef01', '2025-01-31 09:25:49'),
(0, 5, 'c3082fae7c3865d6cbf2bd7d6b47dccba1d9015b5374a4bcea32ed9dff276011', '2025-01-31 09:34:09'),
(0, 1, '11bda6ed00e080bc0651da81e895c2cd93ed0378048a6d636ffbe5c5f14bb28d', '2025-01-31 09:38:25'),
(0, 5, '0ac0ae8bdf678a003a8e8364c4f57a652954bc02ac2e20bf59807637cd10277c', '2025-01-31 09:39:45'),
(0, 1, '5a4005582b3d6f1f822302a0b52314be4f4c2f11e22fa8c06e6258383123c593', '2025-01-31 09:40:10'),
(0, 1, 'b97745bc53863944833538e6181f2991df344ee456c06d8fd1b800ea8ff79f85', '2025-02-01 06:49:14'),
(0, 1, 'bb5b6bf75455d0a47e9501eddebe11523903b5f108141b1bfb8dc3735692c4c3', '2025-02-01 06:56:23'),
(0, 1, '669a53514f8e46d0e3ed4cc12f3cd445c4e984ab856cb2884d88f40ee220519d', '2025-02-01 08:44:37'),
(0, 1, 'c2c9afd94294e88d71149704733908772cd5c6b471764fd8b0bc0c1a4ebbac33', '2025-02-07 10:42:05'),
(0, 1, '5d6d20537b6f135ca8c28b244aebf58e9c929f130e8a91458eaac17e3be07a97', '2025-02-20 11:01:15'),
(0, 1, '365d365c34aff345eb74bd44a58a3cb115df3a705ae73900c25788b40b40aee3', '2025-02-20 11:06:52'),
(0, 5, '19d0608de9ee2bbbfbba213e4b1a74d1c90e4a9fafae53e9611c76f1d5a9605f', '2025-02-20 11:12:58'),
(0, 2, 'ccfb94049762184daacdbddf868350013261b8687c8c37eed650fa8087a4edab', '2025-02-20 11:20:43'),
(0, 1, '9e93a4ced09d7698ca1de3ad6d15de6cd6fd727f9d7e62936916348b01a2dd60', '2025-02-20 11:21:11'),
(0, 5, '6fc71d148890b87363e258493de987eb2a7b13afd63c6e2d3a6000c106b44938', '2025-02-20 11:24:30'),
(0, 2, '9bfa68b0bd065985228d1c4fff1c70f18414c4190faa2125b44eb80d066161cd', '2025-02-20 11:25:30'),
(0, 1, 'ee1b19af2b511852094a5e01ecb6699768c48e8edd40b3bbee54dca0d8c26edf', '2025-02-20 11:25:43'),
(0, 1, '7af4f773460584f63b75e7ac11643b9aca859b412f17511107a9784e7bd6727e', '2025-02-21 11:26:57'),
(0, 1, '2aa45fd83091a4c892d32d5fe1d583251c0b5ed89bd8a44eea4d05250d086723', '2025-02-21 11:30:49'),
(0, 5, '3a2bf8109b9b992a8df265a16395bdd8d5dbc914bdb6bc538723bbe49927560c', '2025-02-21 11:31:12'),
(0, 5, 'd04a6c2d1e24bfd0272254aa5487eaad42b6fe1c1f7aea8f6f1b49149cebffde', '2025-02-21 11:32:29'),
(0, 1, 'd8ac65b1b00b089e16781e5c7685ed66a092a2c1c6a0addf4cc0a4f9a5cb7fe2', '2025-02-21 11:32:36'),
(0, 5, 'b9a959d83fd4144593e1d6da3c791128b3435de492a880d651a127c753e0bc2e', '2025-02-21 11:35:02'),
(0, 5, '82f6535c65e813d8b5f2e1a8cb6b488215e9a4b97744a81cff2cd9d26a5e1fb0', '2025-02-21 11:39:42'),
(0, 1, 'dc63c9e1ca9faa5caf993b9a2fc46f8c9c150d8359288255d51c0ce7344f500b', '2025-02-21 11:40:03'),
(0, 1, '493249cddb9a2404b5eaa63fcb39af06ad8b8ff07b9c1ed53babd72d299c72c8', '2025-02-22 07:17:11'),
(0, 1, 'f823b72b76caa4ace68f1f8d4dc6d8df7bafa5781fed711c3a3582a8b9166d05', '2025-02-27 10:23:01'),
(0, 1, '03112002a999086aebc6ec7e547b5bb62116d1c5a5d3b8f549a03e87c75d3871', '2025-02-27 11:20:14'),
(0, 5, 'c5cabd2bedf30c199560525012375ccd2cd9a09e9d7d5c046ee9fd76111d3fea', '2025-02-27 11:23:39');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_id`, `time_slot_id`, `date`, `status`, `notes`, `created_at`) VALUES
(10, 1, 9, 17, '2025-02-21', 'confirmed', NULL, '2025-02-21 10:43:42');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `recommended_time` varchar(100) DEFAULT NULL,
  `recommended_to` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `services`
--

INSERT INTO `services` (`id`, `name`, `duration`, `price`, `description`, `is_active`, `recommended_time`, `recommended_to`) VALUES
(8, 'Life Coaching Képzés', 60, 5990.00, 'Fejleszd önismereted és találj rá a belső erőforrásaidra! Ebben a képzésben segítünk felismerni, hogyan érheted el a legjobbat önmagadból, és támogatunk abban, hogy magabiztosabban lépj az önmegvalósítás útjára.', 1, '6 hét, heti 1 alkalom', 'azoknak, akik önismereti és életvezetési iránymutatást keresnek'),
(9, 'Business Coaching Tréning', 60, 4990.00, 'Fejleszd vállalkozói képességeidet és válj vezetővé a szakmádban! Ez a képzés segít abban, hogy jobb döntéseket hozz, hatékonyabban kommunikálj és felkészültebben építsd a vállalkozásodat.', 1, '8 hét, heti 2 alkalom', 'vezetőknek és vállalkozóknak, akik fejlődni szeretnének'),
(10, 'Stresszkezelés és Reziliencia Workshop', 60, 4999.00, 'Ismerd meg, hogyan kezelheted hatékonyabban a mindennapi stresszt, és hogyan építheted ki a belső erőforrásaidat, hogy könnyebben alkalmazkodj a kihívásokhoz. Ez a workshop gyakorlati tanácsokkal és technikákkal lát el.', 1, '4 hét, heti 1 alkalom', 'mindenkinek, aki szeretné jobban kezelni a stresszt'),
(11, 'Karriertervezés és Énmárka Építés', 60, 11990.00, 'Ez a program segít a személyes és szakmai énmárka kialakításában, valamint karriered tudatos tervezésében. Tanuld meg, hogyan alakíthatsz ki egyedi és erős énmárkát, amely segíti a céljaid elérését.', 1, '6 hét, heti 1 alkalom', 'azoknak, akik szeretnék tudatosan építeni karrierjüket'),
(12, 'Hatékony Kommunikáció és Konfliktuskezelés', 60, 6590.00, 'Sajátítsd el azokat a kommunikációs technikákat, amelyek segítenek a személyes és szakmai kapcsolataid fejlesztésében. A képzés során gyakorlati példákon keresztül tanulhatod meg a konstruktív konfliktuskezelést és az asszertív kommunikációt.', 1, '5 hét, heti 1 alkalom', 'mindenkinek, aki fejleszteni szeretné kommunikációs készségeit és kapcsolatait'),
(13, 'Mindfulness és Produktivitás Program', 60, 5490.00, 'Fedezd fel, hogyan növelheted a koncentrációdat és produktivitásodat a tudatos jelenlét gyakorlásával. A program ötvözi a mindfulness technikákat a modern időgazdálkodási módszerekkel, hogy segítsen egyensúlyt teremteni az élet különböző területei között.', 1, '7 hét, heti 1 alkalom', 'azoknak, akik szeretnének tudatosabban és hatékonyabban élni és dolgozni');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'min_advance_hours', '24', '2025-01-09 12:26:35'),
(2, 'max_advance_days', '60', '2025-01-09 12:26:35'),
(3, 'work_day_start', '09:00', '2025-01-09 12:26:35'),
(4, 'work_day_end', '17:00', '2025-01-09 12:26:35'),
(5, 'email_notifications', 'false', '2025-02-21 07:17:31'),
(6, 'sms_notifications', 'false', '2025-01-09 12:26:35');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `time_slots`
--

INSERT INTO `time_slots` (`id`, `service_id`, `date`, `start_time`, `end_time`, `is_available`) VALUES
(1, 9, '2025-01-15', '13:42:00', '13:45:00', 0),
(2, 11, '2025-01-12', '11:11:00', '12:12:00', 1),
(3, 9, '2025-01-25', '11:11:00', '12:12:00', 0),
(4, 9, '2025-01-28', '11:25:00', '12:25:00', 1),
(5, 9, '2025-01-29', '10:00:00', '11:00:00', 0),
(6, 9, '2025-02-02', '11:15:00', '12:15:00', 0),
(7, 9, '2025-02-01', '11:15:00', '12:15:00', 0),
(8, 9, '2025-02-02', '10:00:00', '11:00:00', 0),
(9, 9, '2025-02-03', '11:00:00', '12:00:00', 0),
(10, 10, '2025-02-03', '11:15:00', '12:15:00', 0),
(11, 12, '2025-02-04', '10:30:00', '10:45:00', 1),
(12, 9, '2025-02-19', '12:15:00', '13:45:00', 0),
(13, 9, '2025-02-25', '11:00:00', '12:00:00', 1),
(14, 9, '2025-02-22', '11:00:00', '12:00:00', 1),
(15, 9, '2025-02-20', '11:00:00', '12:00:00', 1),
(16, 9, '2025-02-23', '11:00:00', '12:00:00', 1),
(17, 9, '2025-02-21', '13:00:00', '14:00:00', 0),
(18, 9, '2025-02-20', '15:00:00', '16:00:00', 1),
(19, 9, '2025-02-20', '09:00:00', '10:00:00', 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `oauth_provider`, `oauth_id`, `oauth_token`, `oauth_token_expires`, `created_at`, `is_admin`, `is_instructor`, `reset_token`, `reset_token_expires`) VALUES
(1, 'a', 'a@a.com', '$2y$10$2/9fQxbveIEMJUAqwxVUoeXhaSp5oBFRVUCHyPg.gRUYd7o9Dnzke', NULL, NULL, NULL, NULL, '2024-12-17 07:01:28', 1, 1, '44e1cca6a45608d744f9e474f79d9715c197981ed53dd7680bdce717a9be8e67', '2024-12-17 09:11:42'),
(2, 'a2', 'a2@gmail.com', '$2y$10$yaxPnN7.hUnADBm52Cla0eYDgoBA9vLAwueYCmEGXw8Vg7RlgeuBq', NULL, NULL, NULL, NULL, '2024-12-17 07:01:37', 0, 0, NULL, NULL),
(5, 'oktato', 'oktato@gmail.com', '$2y$10$OD9qKgUEk48bl9eteB3/CeR3WmF.1nJ3TkwqDsuTO5cTNOtv.fmyO', NULL, NULL, NULL, NULL, '2025-01-15 11:24:20', 0, 1, NULL, NULL),
(6, 'projekt2025', 'projekt2025Teszt@gmail.com', '$2y$10$zkF.E3pY2lF/KBvs/AeiV.YMVeBX4ujiTvfCjTzPuxKnTYITIAL7O', NULL, NULL, NULL, NULL, '2025-02-26 11:43:22', 0, 0, NULL, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
