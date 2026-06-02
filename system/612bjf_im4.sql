SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `animals`
--

CREATE TABLE `animals` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `animals`
--

INSERT INTO `animals` (`id`, `name`) VALUES
(1, 'Elefant'),
(2, 'Löwe'),
(3, 'Zebra');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `boxes`
--

CREATE TABLE `boxes` (
  `id` int(11) NOT NULL,
  `serial_id` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `figures`
--

CREATE TABLE `figures` (
  `id` int(11) NOT NULL,
  `serial_id` varchar(100) NOT NULL,
  `animal_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sensordata`
--

CREATE TABLE `sensordata` (
  `id` int(11) NOT NULL,
  `figure_id` varchar(100) NOT NULL,
  `zeit` timestamp NOT NULL DEFAULT current_timestamp(),
  `device_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stories`
--

CREATE TABLE `stories` (
  `id` int(11) NOT NULL,
  `animal_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `intro` text DEFAULT NULL,
  `audio_path` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `stories`
--

INSERT INTO `stories` (`id`, `animal_id`, `title`, `intro`, `audio_path`) VALUES
(1, 1, 'Emma der Elefant', 'Emma war ein kleiner Elefant, der in einem wunderschönen Wald lebte. Eines Tages fand sie einen glitzernden Stein am Fluss...', '/audio/emma_der_elefant.mp3'),
(2, 1, 'Emma und die versteckte Wasserstelle', 'Emma war ein kleiner Elefant, der mit seiner Herde durch die weite Savanne zog. Eines Tages erinnerte sie sich an eine alte Geschichte über einen geheimen Wasserplatz...', '/audio/emma_und_die_versteckte_wasserstelle.mp3'),
(3, 1, 'Emma und der silberne Mondpfad', 'Emma war eine neugierige kleine Elefantin, die nachts gerne die Sterne beobachtete. Eines Abends entdeckte sie im Mondlicht einen geheimnisvollen Pfad durch den Wald...', 'audio/emma_und_der_silberne_Mondpfad.mp3'),
(4, 2, 'Leo lernt Brüllen', 'Leo war ein junger Löwe, der noch nicht richtig brüllen konnte. Seine Freunde halfen ihm dabei...', '/audio/leo_lernt_brüllen.mp3'),
(5, 2, 'Leo und der tanzende Schatten', 'Leo war ein junger Löwe, der mutig jedes Abenteuer suchte. Doch eines Abends erschrak er plötzlich vor einem grossen Schatten im Feuerlicht...', '/audio/leo_und_der_tanzende_schatten.mp3'),
(6, 2, 'Leo und der goldene Hügel', 'Leo war ein junger Löwe, der Abenteuer über alles liebte und jeden Tag neue Orte in der Savanne entdeckte. Eines Morgens beschloss er, das Geheimnis eines goldenen Hügels in der Ferne zu erkunden...', '/audio/leo_und_der_goldene_huegel.mp3'),
(7, 3, 'Zara das Zebra', 'Zara war stolz auf ihre schönen Streifen. Eines Tages lernte sie, dass jedes Zebra einzigartig ist…', '/audio/Zara das Zebra.mp3'),
(8, 3, 'Zara und der blaue Schmetterling', 'Zara war stolz auf ihre wunderschönen Streifen und liebte es, durch die Savanne zu galoppieren. Eines Morgens landete ein leuchtend blauer Schmetterling direkt auf ihrer Nase...', '/audio/Zara und der blaue Schmetterling.mp3'),
(9, 3, 'Zara lernt Geduld', 'Zara war ein junges Zebra, das immer die Schnellste sein wollte. Eines Tages meldete sie sich voller Stolz zu einem grossen Rennen an...', '/audio/zara_lernt_geduld.mp3');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_story_progress`
--

CREATE TABLE `user_story_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `story_id` int(11) NOT NULL,
  `play_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `animals`
--
ALTER TABLE `animals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indizes für die Tabelle `boxes`
--
ALTER TABLE `boxes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_id` (`serial_id`),
  ADD KEY `fk_boxes_user` (`user_id`);

--
-- Indizes für die Tabelle `figures`
--
ALTER TABLE `figures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_id` (`serial_id`),
  ADD KEY `fk_figures_animal` (`animal_id`);

--
-- Indizes für die Tabelle `sensordata`
--
ALTER TABLE `sensordata`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sensordata_figure` (`figure_id`),
  ADD KEY `fk_sensordata_device` (`device_id`);

--
-- Indizes für die Tabelle `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stories_animal` (`animal_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indizes für die Tabelle `user_story_progress`
--
ALTER TABLE `user_story_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_story` (`user_id`,`story_id`),
  ADD KEY `fk_user_story_progress_story` (`story_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `animals`
--
ALTER TABLE `animals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `boxes`
--
ALTER TABLE `boxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `figures`
--
ALTER TABLE `figures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `sensordata`
--
ALTER TABLE `sensordata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1088;

--
-- AUTO_INCREMENT für Tabelle `stories`
--
ALTER TABLE `stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `user_story_progress`
--
ALTER TABLE `user_story_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `boxes`
--
ALTER TABLE `boxes`
  ADD CONSTRAINT `fk_boxes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `figures`
--
ALTER TABLE `figures`
  ADD CONSTRAINT `fk_figures_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`);

--
-- Constraints der Tabelle `sensordata`
--
ALTER TABLE `sensordata`
  ADD CONSTRAINT `fk_sensordata_device` FOREIGN KEY (`device_id`) REFERENCES `boxes` (`serial_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sensordata_figure` FOREIGN KEY (`figure_id`) REFERENCES `figures` (`serial_id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `stories`
--
ALTER TABLE `stories`
  ADD CONSTRAINT `fk_stories_animal` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`id`);

--
-- Constraints der Tabelle `user_story_progress`
--
ALTER TABLE `user_story_progress`
  ADD CONSTRAINT `fk_user_story_progress_story` FOREIGN KEY (`story_id`) REFERENCES `stories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_story_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
