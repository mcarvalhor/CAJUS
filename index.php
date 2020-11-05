<?php

require_once("admin/server/autoload.php");
textdomain("index");
disableCache();

$slugName = $_GET["s"];

if(empty($slugName) && preg_match("/([a-zA-Z0-9\-\_\.]+|" . preg_quote(CNS_SHORTENERPREFIX) . "[a-zA-Z0-9\-\_\.]+)(?:[\?\#].*)?$/", $_SERVER["REQUEST_URI"], $slugNameMatches)) {
	$slugName = $slugNameMatches[1];
}

if(empty($slugName) || strtolower($slugName) == "index.php") {
	$slugName = "index";
}

if($slugName == "admin") {
	header("Location: " . getCurrentWebPath() . "admin/");
	exit(0);
}

$slug = [ ];

$slugQuery = dbFetchLOB("SELECT id, name, type, content, LENGTH(content) AS length, bot_index, mime, extension, access_count FROM " . DB_PREFIX . "slugs WHERE LOWER(name) = LOWER(?) LIMIT 1;",
	[ $slugName ],
	[ 0 => &$slug["id"], 1 => &$slug["name"], 2 => &$slug["type"], 4 => &$slug["length"], 5 => &$slug["bot_index"], 6 => &$slug["mime"], 7 => &$slug["extension"], 8 => &$slug["access_count"] ],
	[ 3 => &$slug["content"] ]);

if($slugQuery === FALSE || empty($slug["id"]) || empty($slug["content"])) {
	http_response_code(404);
	header("X-Robots-Tag: " . CNS_NOINDEX);
	echo '<!DOCTYPE html>' . PHP_EOL;
	echo '<html><head>';
	echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,height=device-height,initial-scale=1,user-scalable=no"><meta name="robots" content="' . CNS_NOINDEX . '">';
	echo '<link rel="icon" type="image/png" sizes="16x16 32x32 64x64" href="favicon64.png"><link rel="icon" type="image/png" sizes="128x128 256x256 512x512" href="favicon.png">';
	// The title of the not found error
	echo '<title>' . htmlentities(gettext("404 Not Found"), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") . '</title>';
	echo '<style type="text/css">h2 { display: block; text-align: center; } p { display: block; text-align: center; }</style>';
	echo '</head><body>';
	// The title of the not found error
	echo '<h2>' . htmlentities(gettext("404 Not Found"), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") . '</h2>';
	// Description for the not found error
	echo '<p>' . htmlentities(gettext("The resource you are looking for could not be found."), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") . '</p>';
	echo '</body></html>';
	exit(0);
}

if($slugName != $slug["name"]) {
	header("Location: " . getCurrentWebPath() . $slug["name"]);
	exit(0);
}

if(empty($slug["bot_index"])) {
	$index = CNS_NOINDEX;
} else {
	$index = CNS_INDEX;
}

if($slug["type"] == "redirect" && ($slug["content"] = fgets($slug["content"])) !== FALSE) {
	header("Location: " . $slug["content"]);
} else if($slug["type"] == "iframe" && ($slug["content"] = fgets($slug["content"])) !== FALSE) {
	header("X-Robots-Tag: " . $index);
	echo '<!DOCTYPE html>' . PHP_EOL;
	echo '<html><head>';
	echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width,height=device-height,initial-scale=1,user-scalable=no"><meta name="robots" content="' . $index . '">';
	echo '<link rel="icon" type="image/png" sizes="16x16 32x32 64x64" href="favicon64.png"><link rel="icon" type="image/png" sizes="128x128 256x256 512x512" href="favicon.png">';
	echo '<title>' . htmlentities($slug["name"], ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") . '</title>';
	echo '<style type="text/css">body { overflow: hidden; } #page-frame { display: block; position: fixed; top: 0px; left: 0px; bottom: 0px; right: 0px; width: 100%; height: 100%; border: none; overflow: auto; }</style>';
	echo '</head><body>';
	echo '<iframe id="page-frame" src="' . htmlentities($slug["content"], ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") . '"></iframe>';
	echo '</body></html>';
} else if($slug["type"] == "file" && !empty($slug["length"]) && !empty($slug["mime"])) {
	header("X-Robots-Tag: " . $index);
	header("Content-Length: " . $slug["length"]);
	header("Content-Type: " . $slug["mime"]);
	fpassthru($slug["content"]);
} else if($slug["type"] == "file-download" && !empty($slug["length"])) {
	header("X-Robots-Tag: " . $index);
	header("Content-Length: " . $slug["length"]);
	header("Content-Type: application/octet-stream");
	if(empty($slug["extension"])) {
		$slug["extension"] = "";
	} else {
		$slug["extension"] = "." . $slug["extension"];
	}
	header("Content-Disposition: attachment; filename=" . preg_replace("/[^a-zA-Z0-9\-\_]+/", "", $slug["name"]) . $slug["extension"]);
	fpassthru($slug["content"]);
} else {
	header("Location: " . getCurrentWebPath() . "index.php");
	exit(0);
}

if($slug["access_count"] !== NULL) {
	dbQuery("UPDATE " . DB_PREFIX . "slugs SET access_count = access_count + 1 WHERE id = ? AND access_count IS NOT NULL;", [ $slug["id"] ]);
}


