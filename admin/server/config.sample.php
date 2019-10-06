<?php

/* == SERVER CONFIG FILE == */



/* ~ ADMIN ACCESS PASSWORD ~
* If you ever forget the password, modify this setting.
*/
const CNF_DEFAULTPASSWORD = '[...]';



/* ~ GENERAL ~
*/
const CNF_MAXFILEUPLOADSIZE = -1; // Maximum file upload size (bytes), or -1 to unlimited.
const CNF_MAXSESSIONTIME = 30 * 24 * 60 * 60; // Maximum duration of a login session (seconds).
const CNF_AUTOCHECKVERSION = FALSE; // Auto check if project is up-to-date when acessing system settings?



/* ~ Database ~
* In the case user or password is not required (eg.: SQLite), leave it as a empty string.
* The username and password fields must be plain text. However, there's no security breach, since this file is hidden from the internet.
* Officially compatible only to SQLite3 and MySQL.
*
* DNS EXAMPLES:
* 	'sqlite:/var/www-hidden-data/cajus.sqlite3'
*	'sqlite::memory:' (data is lost when script shuts down! Not useful.)
* 	'mysql:host=localhost;dbname=cajus'
* 	'mysql:host=externalserver.example.com;port=3307;dbname=cajus'
*
* Notes:
* 	- if you are using SQLite3, note that both the database file and its folder must be write and read enabled for PHP (rw for www-data).
*/
const DB_DSN = 'sqlite:' . __DIR__ . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'cajus.sqlite3';
const DB_USERNAME = '';
const DB_PASSWORD = '';
const DB_PREFIX = 'cajus_'; // This prefix will be added to the tables.


