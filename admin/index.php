<?php

require_once("server/autoload.php");
textdomain("admin");
disableCache();
startSession();
mutexLock();

if(!isLoggedIn()) {
	header("Location: login.php");
	exit(0);
}



if($_GET["action"] == "delete") {
	$id = $_GET["id"];
	if(checkNonce($_GET["nonce"], "delete") && !empty($id)) {
		dbQuery("DELETE FROM " . DB_PREFIX . "slugs WHERE id = ?;", [ $id ]);
	}
	header("Location: ?");
	exit(0);
}


$links = dbFetchAll("SELECT id, name, type, bot_index, access_count, SUBSTR(content, 0, ?) AS content FROM " . DB_PREFIX . "slugs WHERE LOWER(name) != LOWER(?) ORDER BY access_count DESC, name, id;", [ CNS_MAXURLLENGTH, "index" ]);
$deleteNonce = getNonce("delete", FALSE, max(1, min(32, count($links))));
$logoutNonce = getNonce("logout");

?><!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,height=device-height,initial-scale=1,user-scalable=no">
	<meta name="robots" content="noindex,nofollow,noarchive,noodp,noydir">
	<title>CAJUS</title>
	<style type="text/css">
		@media not all and (min-width: 360px) and (min-height: 360px){
			#main-noscreen {
				display: block;
			}
			#main {
				display: none;
			}
		}
		@media all and (min-width: 360px) and (min-height: 360px){
			#main-noscreen {
				display: none;
			}
			#main {
				display: block;
			}
		}
		body {
			display: block;
			background-color: rgb(0, 0, 0);
			color: rgb(0, 0, 0);
		}
		#main {
			font-size: 16px;
			margin: 100px auto;
			width: 315px;
			padding: 15px;
			border-radius: 10px;
			background-color: rgb(255, 255, 255);
			box-shadow: 0px 0px 20px 10px rgb(255, 255, 255);
			overflow: hidden;
		}
		#header {
			display: block;
			color: rgb(84, 38, 128);
			text-align: center;
			margin-bottom: 50px;
		}
		#header a {
			color: rgb(84, 38, 128);
			font-size: 2em;
			margin: 0px auto;
			text-align: center;
			text-decoration: none;
		}
		#nolist {
			display: block;
			color: rgb(100, 100, 100);
			text-align: center;
		}
		#list {
			display: block;
			padding: 0px;
			list-style-type: none;
		}
		#list li {
			display: grid;
			margin-top: 50px;
			width: 100%;
			grid-template-columns: 150px 30px 100px 30px;
			grid-template-rows: auto auto;
		}
		#list li:first-child {
			margin-top: 0px;
		}
		#list li .name {
			display: grid;
			align-self: end;
			justify-items: left;
			grid-column-start: 1;
			grid-column-end: span 1;
			grid-row-start: 1;
			grid-row-end: span 1;
			font-size: 0.9em;
			word-break: break-all;
			overflow: hidden;
		}
		#list li .arrow {
			display: grid;
			align-self: end;
			justify-items: center;
			font-size: 1.5em;
			grid-column-start: 2;
			grid-column-end: span 1;
			grid-row-start: 1;
			grid-row-end: span 1;
			overflow: hidden;
		}
		#list li .url {
			display: grid;
			align-self: end;
			justify-items: center;
			grid-column-start: 3;
			grid-column-end: span 1;
			grid-row-start: 1;
			grid-row-end: span 1;
			font-size: 0.9em;
			text-transform: lowercase;
			text-align: center;
			word-break: break-all;
			overflow: hidden;
		}
		#list li .access {
			display: grid;
			color: rgb(100, 100, 100);
			align-self: center;
			justify-items: left;
			padding-top: 5px;
			grid-column-start: 1;
			grid-column-end: span 1;
			grid-row-start: 2;
			grid-row-end: span 1;
			font-size: 0.9em;
			word-break: break-all;
			overflow: hidden;
		}
		#list li .index {
			display: grid;
			color: rgb(100, 100, 100);
			align-self: center;
			justify-items: center;
			padding-top: 5px;
			grid-column-start: 3;
			grid-column-end: span 1;
			grid-row-start: 2;
			grid-row-end: span 1;
			font-size: 0.9em;
			word-break: break-all;
			overflow: hidden;
		}
		#list li .delete {
			display: grid;
			align-self: center;
			justify-items: right;
			grid-column-start: 4;
			grid-column-end: span 1;
			grid-row-start: 1;
			grid-row-end: span 2;
		}
		#list li .delete a {
			min-width: 18px;
			border-radius: 12px;
			border-style: solid;
			border-width: 1px;
			border-color: rgba(0, 0, 0, 0);
			text-align: center;
			text-decoration: none;
		}
		#list li .delete a:hover {
			border-color: rgba(84, 38, 128, 1);
		}
		#list li a {
			color: rgb(0, 0, 0);
			transition: color,border-color 0.25s,0.25s;
		}
		#list li a:hover {
			color: rgb(84, 38, 128);
		}
		#buttons {
			display: grid;
			margin: 50px auto;
			grid-template-columns: repeat(2, 1fr);
		}
		#buttons a {
			display: grid;
			border-radius: 10px;
			text-align: center;
			align-self: center;
			padding: 10px 10px;
			font-size: 1.1em;
			font-weight: bold;
			text-decoration: none;
			color: rgb(132, 0, 255);
			background-color: transparent;
			text-align: center;
			transition: background-color,color 0.25s,0.25s;
		}
		#buttons a:hover {
			color: rgb(255, 255, 255);
			background-color: rgb(84, 38, 128);
		}
		#footer {
			display: block;
			font-size: 0.75em;
			text-align: justify;
		}
		#footer .center {
			text-align: center;
		}
		#footer a {
			color: rgb(0, 0, 0);
			transition: color 0.25s;
		}
		#footer a:hover {
			color: rgb(84, 38, 128);
		}
		#main-noscreen {
			margin: 3px;
			color: rgb(255, 255, 255);
			text-align: justify;
		}
	</style>
</head>

<body>
	<div id="main">
		<div id="header">
			<a href="?">CAJUS</a>
			<p><?php echo gettext("CAJUS Ain't Just a URL Shortener"); ?></p>
		</div>
		<?php
			if(empty($links)) {
				echo '<div id="nolist"><p>(' . gettext("You haven't created any short URL yet.") . ')</p></div>';
			} else {
				echo '<ul id="list">';
				foreach($links as $link) {
					if(strlen($link["content"]) > 30) {
						$link["content"] = substr_replace($link["content"], "[...]", 26);
					}
					echo '<li>';
					echo '<a href="' . getCurrentWebPath(1) . $link["name"] . '" class="name" target="_blank">' . $link["name"] . '</a>';
					if($link["type"] == "redirect") {
						echo '<span class="redirectlink arrow">&#8594;</span>';
						echo '<span class="url">' . parseForHTML($link["content"]) . '</span>';
					} else if($link["type"] == "iframe") {
						echo '<span class="iframelink arrow">&#8592;</span>';
						echo '<span class="url">' . parseForHTML($link["content"]) . '</span>';
					} else if($link["type"] == "file") {
						echo '<span class="filelink arrow">&#8594;</span>';
						echo '<span class="url">(' . gettext("File") . ')</span>';
					} else {
						echo '<span class="filedllink arrow">&#8594;</span>';
						echo '<span class="url">(' . gettext("Download") . ')</span>';
					}
					if($link["access_count"] !== NULL) {
						echo '<span class="access">' . sprintf(gettext("Access count: %d hit (s)."), $link["access_count"]) . '</span>';
					} else {
						echo '<span class="access">' . gettext("Access count: disabled.") . '</span>';
					}
					if(!empty($link["bot_index"])) {
						echo '<span class="index">' . gettext("Indexed.") . '</span>';
					} else {
						echo '<span class="index">' . gettext("Not indexed.") . '</span>';
					}
					echo '<span class="delete"><a href="?action=delete&id=' . $link["id"] . '&nonce=' . $deleteNonce . '" onclick="return window.confirm(\'' . gettext("Are you sure you want to remove this URL?") . '\');" title="' . gettext("Remove URL") . '">&times;</a></span>';
					echo '</li>';
				}
				echo '</ul>';
			}
		?>
		<div id="buttons">
			<a href="shorten.php"><?php echo gettext("Shorten URL"); ?></a>
			<a href="slug.php"><?php echo gettext("Create friendly URL"); ?></a>
		</div>
		<div id="footer">
			<p class="center"><?php echo gettext("Logged-in"); ?> (<a href="logout.php?nonce=<?php echo $logoutNonce; ?>"><?php echo gettext("Logout"); ?></a> - <a href="settings.php"><?php echo gettext("Settings"); ?></a>)</p>
		</div>
	</div>
	<div id="main-noscreen">
		<p><?php echo gettext("Your screen is too small to show this webpage."); ?></p>
	</div>
</body>

</html>
