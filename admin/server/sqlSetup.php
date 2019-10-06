<?php

/* == SQL SETUP QUERIES == */



const SQL_BACKUPVERSION = "v1.0.1.1";
const SQL_PREFIX = "{prefix}";

const SQL_DROPTABLES = [
	"DROP TABLE `{prefix}auth_sessions`;",
	"DROP TABLE `{prefix}slugs`;"
];

const SQL_TRUNCATETABLES = [
	"DELETE FROM `{prefix}auth_sessions`;",
	"DELETE FROM `{prefix}slugs`;"
];

const SQL_CREATETABLES = [

	// Create the default all-SGBD compatible tables:

	"CREATE TABLE `{prefix}auth_sessions` (
		`id` INTEGER NOT NULL PRIMARY KEY, /* `id` BIGINT NOT NULL PRIMARY KEY, */
		`token` VARCHAR(255) NOT NULL UNIQUE,
		`user` BIGINT DEFAULT NULL,
		`expires` BIGINT NOT NULL
		);",

	"CREATE TABLE `{prefix}slugs` (
		`id` INTEGER NOT NULL PRIMARY KEY, /* `id` BIGINT NOT NULL PRIMARY KEY */
		`name` VARCHAR(255) NOT NULL UNIQUE,
		`type` VARCHAR(16) NOT NULL,
		`mime` VARCHAR(128) DEFAULT NULL,
		`extension` VARCHAR(32) DEFAULT NULL,
		`bot_index` TINYINT NOT NULL DEFAULT '0',
		`access_count` BIGINT DEFAULT NULL,
		`content` LONGBLOB NOT NULL
		);",

	// Create indexes and adjust auto increment:

	"CREATE INDEX `{prefix}auth_sessions_expires_key` ON `{prefix}auth_sessions`(`expires`);",

	// Primary Keys Auto Increment:

	"ALTER TABLE `{prefix}auth_sessions` MODIFY `id` BIGINT NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;", // MySQL

	"ALTER TABLE `{prefix}slugs` MODIFY `id` BIGINT NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;", // MySQL

	"ALTER TABLE `{prefix}auth_sessions` MODIFY `id` BIGINT NOT NULL IDENTITY(1,1) PRIMARY KEY;", // SQLServer

	"ALTER TABLE `{prefix}slugs` MODIFY `id` BIGINT NOT NULL IDENTITY(1,1) PRIMARY KEY;", // SQLServer

	// Specific SGBD improvements:

	"ALTER TABLE `{prefix}slugs` MODIFY `bot_index` TINYINT(1) NOT NULL DEFAULT '0';",

	"ALTER TABLE `{prefix}slugs` MODIFY `type` ENUM('redirect', 'iframe', 'file', 'file-download') NOT NULL;"

];


