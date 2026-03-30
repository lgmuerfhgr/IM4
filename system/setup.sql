-- Core users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`email`)
);

-- Physical babyphone devices (device_code is printed on the device)
CREATE TABLE IF NOT EXISTS `devices` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `device_code` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_devices_code` (`device_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Junction table: which user is connected to which device (many-to-many)
CREATE TABLE IF NOT EXISTS `user_has_device` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `device_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_device` (`user_id`, `device_id`),
  CONSTRAINT `fk_user_has_device_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_has_device_device`
    FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Available tracks for babyphone soothing playlist
CREATE TABLE IF NOT EXISTS `tracks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Junction table: which tracks are selected on which device (many-to-many)
CREATE TABLE IF NOT EXISTS `device_tracks` (
  `device_id` INT NOT NULL,
  `track_id` INT NOT NULL,
  PRIMARY KEY (`device_id`, `track_id`),
  CONSTRAINT `fk_device_tracks_device`
    FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_device_tracks_track`
    FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Babyphone crying history linked to a device (only the device writes these)
CREATE TABLE IF NOT EXISTS `sensordata` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `device_id` INT NOT NULL,
  `starttime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endtime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sensordata_device` (`device_id`),
  CONSTRAINT `fk_sensordata_device`
    FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed tracks once (safe on reruns)
INSERT INTO `tracks` (`title`) VALUES 
('Another brick in the wall'),
('Back in black'),
('Bohemian rhapsody'),
('Clocks'),
('Creep'),
('Don`t fear the reaper'),
('Enter sandman'),
('Hotel california'),
('I love rock`n`roll'),
('Smells like teen spirit'),
('Stairway to heaven'),
('Sympathy for the devil'),
('Under the bridge'),
('Where is my mind'),
('Wonderwall');

