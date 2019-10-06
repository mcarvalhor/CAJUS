<?php

/* == PROJECT CONSTS == */



/* ~ GENERAL ~
*/
const CNS_CHECKVERSION = "https://isc.mcarvalhor.com/CAJUS/version.php?v=%s"; // Project version check URL
const CNS_VERSION = "v1.0.1.1"; // Project version
const CNS_SESSIONNAME = 'CAJUS_SessionID'; // Session name
const CNS_SEMAPHORE = "mutex.sem"; // Mutex file

/* ~ URL ~
*/
const CNS_SHORTENERPREFIX = "~"; // Prefix for short links (can be empty string, though it's not recommended). Recommended: "-", "_" or "~"
const CNS_MAXURLLENGTH = 1024 * 2; // Maximum length of a long URL (in characters).
const CNS_MINSHORTENEDLEN = 4; // Minimum length of shortened URL
const CNS_MAXSHORTENEDLEN = 10; // Maximum length of shortened URL


/* ~ Indexing ~
*/
const CNS_INDEX = "all"; // X-Robot Tag to index the page
const CNS_NOINDEX = "noindex,nofollow,noarchive,noodp,noydir"; // X-Robot Tag to prevent indexing the page

