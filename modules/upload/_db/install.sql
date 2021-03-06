CREATE TABLE IF NOT EXISTS `media` (
    `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(42) NOT NULL,
    `original` VARCHAR(250) NOT NULL,
    `mime` VARCHAR(30) NOT NULL,
    `path` VARCHAR(60) NOT NULL,
    `form` VARCHAR(50) NOT NULL,
    `user` INTEGER,
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX `by_mime_name` ON `media` ( `mime`, `name` );
