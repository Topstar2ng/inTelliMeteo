--
-- Database: `intellimeteo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `airports`
--

DROP TABLE IF EXISTS `airports`;
CREATE TABLE IF NOT EXISTS `airports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `airport_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iata_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icao_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('international','domestic','military','private') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_scores`
--

DROP TABLE IF EXISTS `assessment_scores`;
CREATE TABLE IF NOT EXISTS `assessment_scores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category` varchar(50) NOT NULL,
  `score_achieved` int NOT NULL,
  `total_questions` int NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ngcities`
--

DROP TABLE IF EXISTS `ngcities`;
CREATE TABLE IF NOT EXISTS `ngcities` (
  `tblid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`tblid`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

DROP TABLE IF EXISTS `quiz_questions`;
CREATE TABLE IF NOT EXISTS `quiz_questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` text NOT NULL,
  `option_b` text NOT NULL,
  `option_c` text NOT NULL,
  `option_d` text NOT NULL,
  `correct_option` char(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sun_data`
--

DROP TABLE IF EXISTS `sun_data`;
CREATE TABLE IF NOT EXISTS `sun_data` (
  `tblid` int NOT NULL AUTO_INCREMENT,
  `location_id` int NOT NULL,
  `data_date` date NOT NULL,
  `data_time` time NOT NULL,
  `sunrise` int NOT NULL,
  `sunset` int NOT NULL,
  `date` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`tblid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `workplace` varchar(150) DEFAULT NULL,
  `profession` varchar(100) DEFAULT NULL,
  `purpose` varchar(150) DEFAULT NULL,
  `role` enum('student','observer','admin') DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE IF NOT EXISTS `user_settings` (
  `user_id` int NOT NULL,
  `home_station` varchar(100) DEFAULT 'Abuja',
  `unit_system` enum('metric','imperial') DEFAULT 'metric',
  `timezone` varchar(50) DEFAULT 'Africa/Lagos',
  `theme_preference` enum('light','dark') DEFAULT 'light',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weather_data`
--

DROP TABLE IF EXISTS `weather_data`;
CREATE TABLE IF NOT EXISTS `weather_data` (
  `tblid` int NOT NULL,
  `location_id` int NOT NULL,
  `data_date` date NOT NULL,
  `data_time` time NOT NULL,
  `temperature` float NOT NULL,
  `feels_like` float NOT NULL,
  `humidity` float NOT NULL,
  `pressure` float NOT NULL,
  `sea_level` float NOT NULL,
  `grnd_level` float NOT NULL,
  `wind_direction` int NOT NULL,
  `wind_speed` int NOT NULL,
  `gust` int NOT NULL,
  `weather_id` int NOT NULL,
  `weather_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cloud` int NOT NULL,
  `visibility` int NOT NULL,
  `date` datetime NOT NULL,
  `status` int NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weather_forecast`
--

DROP TABLE IF EXISTS `weather_forecast`;
CREATE TABLE IF NOT EXISTS `weather_forecast` (
  `tblid` int NOT NULL AUTO_INCREMENT,
  `cityid` int NOT NULL,
  `recorddate` datetime NOT NULL,
  `forecastdate` datetime NOT NULL,
  `temperature` float NOT NULL,
  `humidity` float NOT NULL,
  `pressure` float NOT NULL,
  `wind_direction` int NOT NULL,
  `wind_speed` float NOT NULL,
  `weatherid` int NOT NULL,
  `visibility` float NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`tblid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `weather_forecast_observed_view`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `weather_forecast_observed_view`;
CREATE TABLE IF NOT EXISTS `weather_forecast_observed_view` (
`datetime` varchar(21)
,`forecast_temperature` double
,`observed_temperature` float
);

-- --------------------------------------------------------

--
-- Table structure for table `weather_reports`
--

DROP TABLE IF EXISTS `weather_reports`;
CREATE TABLE IF NOT EXISTS `weather_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `city_name` varchar(100) NOT NULL,
  `icao_code` varchar(10) DEFAULT 'DNXX',
  `temp` decimal(5,2) NOT NULL,
  `humidity` int NOT NULL,
  `pressure` int NOT NULL,
  `wind_speed_kt` decimal(5,2) NOT NULL,
  `metar_string` text NOT NULL,
  `planting_status` varchar(20) NOT NULL,
  `agro_advice` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `website_comment`
--

DROP TABLE IF EXISTS `website_comment`;
CREATE TABLE IF NOT EXISTS `website_comment` (
  `tblid` int NOT NULL AUTO_INCREMENT,
  `_name` varchar(100) NOT NULL,
  `_email` varchar(100) NOT NULL,
  `_profession` varchar(100) NOT NULL,
  `_comment` text NOT NULL,
  `_status` int NOT NULL DEFAULT '0',
  `_date` datetime NOT NULL,
  `_adminid` int NOT NULL,
  `_approveddate` datetime NOT NULL,
  PRIMARY KEY (`tblid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `website_contact`
--

DROP TABLE IF EXISTS `website_contact`;
CREATE TABLE IF NOT EXISTS `website_contact` (
  `tblid` int NOT NULL AUTO_INCREMENT,
  `_name` varchar(100) NOT NULL,
  `_email` varchar(100) NOT NULL,
  `_subject` varchar(100) NOT NULL,
  `_message` text NOT NULL,
  `_status` int NOT NULL DEFAULT '0',
  `_date` datetime NOT NULL,
  `_adminid` int NOT NULL,
  `_approveddate` datetime NOT NULL,
  PRIMARY KEY (`tblid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure for view `weather_forecast_observed_view`
--
DROP TABLE IF EXISTS `weather_forecast_observed_view`;

DROP VIEW IF EXISTS `weather_forecast_observed_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`tecsfrzo`@`localhost` SQL SECURITY DEFINER VIEW `weather_forecast_observed_view`  AS SELECT `forecast_data`.`datetime` AS `datetime`, `forecast_data`.`forecast_temperature` AS `forecast_temperature`, `observed_data`.`observed_temperature` AS `observed_temperature` FROM ((select date_format(`wf`.`forecastdate`,'%Y-%m-%d %H:%i') AS `datetime`,round((`wf`.`temperature` - 273.15),2) AS `forecast_temperature` from `weather_forecast` `wf` where ((time_format(`wf`.`forecastdate`,'%i') = '00') and (`wf`.`cityid` = '5') and (`wf`.`forecastdate` between '2024-04-29 00:00' and '2024-04-29 23:59') and (hour(`wf`.`forecastdate`) in (0,3,6,9,12,15,18)))) `forecast_data` left join (select date_format((`wd`.`date` + interval 3 hour),'%Y-%m-%d %H:%i') AS `datetime`,`wd`.`temperature` AS `observed_temperature` from `weather_data` `wd` where ((time_format(`wd`.`data_time`,'%i') = '00') and (`wd`.`location_id` = '5') and (`wd`.`date` between '2024-04-28 20:00' and '2024-04-29 19:59') and (hour(`wd`.`date`) in (0,3,6,9,12,15,18)))) `observed_data` on((`forecast_data`.`datetime` = `observed_data`.`datetime`))) ORDER BY `forecast_data`.`datetime` ASC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessment_scores`
--
ALTER TABLE `assessment_scores`
  ADD CONSTRAINT `assessment_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;