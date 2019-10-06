<?php

require_once("server/autoload.php");
textdomain("admin");
disableCache();
startSession();

if(!isLoggedIn()) {
	header("Location: login.php");
	exit(0);
}


$showForm = TRUE;
$name = "";
$type = "";
$url = "";
$confirmURL = FALSE;
$confirmURLValue = "";
$index = "";
$accessCount = "";

if($_POST["action"] == "slug") {
	$name = trim($_POST["name"]);
	$type = $_POST["type"];
	$url = trim($_POST["url"]);
	$confirmURLValue = $_POST["confirm_url"];
	$index = $_POST["index"];
	$accessCount = $_POST["access_count"];
	$file = $_FILES["upload_file"];
	if(!checkNonce($_POST["nonce"], "shorten")) {
		$error = gettext('Session expired. Try again.');
	} else if(empty($name)) {
		$error = gettext('Enter the name you want to use as friendly URL.');
	} else if(strlen($name) > 255) {
		$error = sprintf(gettext('The name you want to use as friendly URL is too long. Enter something smaller (maximum %d characters).'), 255);
	} else if(preg_match("/^[a-zA-Z0-9\-\_\.]+$/", $name) !== 1) {
		$error = gettext('The name you want to use as friendly URL is not valid. Only a-z, A-Z, 0-9, hyphen (-), underscore (_) characters are allowed.');
	} else if(strncmp(strtolower($name), strtolower("index"), 5) === 0 || strncmp(strtolower($name), strtolower("admin"), 5) === 0 || strncmp(strtolower($name), strtolower("."), 1) === 0 || strncmp(strtolower($name), strtolower("web."), 4) === 0) {
		$error = sprintf(gettext('The name you want to use as friendly URL is not valid. Cannot start with "%s", "%s", "%s" or "%s", because these are reserved for the system.'),
			"index", "admin", "web.", ".");
	} else if(!empty(CNS_SHORTENERPREFIX) && strncmp(strtolower($name), strtolower(CNS_SHORTENERPREFIX), strlen(CNS_SHORTENERPREFIX)) === 0) {
		$error = sprintf(gettext('The name you want to use as friendly URL is not valid. Cannot start with "%s", because this is reserved for the system.'),
			CNS_SHORTENERPREFIX);
	} else if($type != "redirect" && $type != "iframe" && $type != "file" && $type != "file-download") {
		$error = gettext('Select the type of the URL.');
	} else if(($type == "redirect" || $type == "iframe") && empty($url)) {
		$error = gettext("Enter the original URL.");
	} else if(($type == "redirect" || $type == "iframe") && strlen($url) > CNS_MAXURLLENGTH) {
		$error = sprintf(gettext("The URL you entered is too long. Enter something smaller (maximum %d characters)."), CNS_MAXURLLENGTH);
	} else if(($type == "redirect" || $type == "iframe") && filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
		$error = gettext("You haven't entered a valid URL. Don't forget the protocol (http, https, ...).");
	} else if(($type == "file" || $type == "file-download") && empty($file)) {
		$error = gettext("Select the file to upload.");
	} else if(($type == "file" || $type == "file-download") && $file["error"] !== UPLOAD_ERR_OK) {
		$error = gettext("Couldn't upload the file. Try again.");
	} else if(($type == "file" || $type == "file-download") && $file["size"] > getMaxUploadSize()) {
		$error = sprintf(gettext("The file you tried to upload is too big. Try uploading a file with maximum length of %s."), bytesToSize(getMaxUploadSize(),
			[0 => gettext("%.2f bytes"), 1 => gettext("%.2f byte"), 2 => gettext("%.2f bytes"), 1024 => gettext("%.2f KiB"), 1048576 => gettext("%.2f MiB"), 1073741824 => gettext("%.2f GiB")]));
	} else if(($type == "file" || $type == "file-download") && ($fileContent = file_get_contents($file["tmp_name"])) === FALSE) {
		$error = gettext("Couldn't upload the file. Try again.");
	} else if(!empty(dbFetch("SELECT id FROM " . DB_PREFIX . "slugs WHERE LOWER(name) = LOWER(?) LIMIT 1;", [ $name ]))) {
		$error = gettext("The name you want to use as friendly URL already exists. Enter something different.");
	} else if($type == "iframe" && $confirmURLValue != "true" && parse_url($url, PHP_URL_SCHEME) !== parse_url(getCurrentWebPath(1), PHP_URL_SCHEME)) {
		$confirmURL = TRUE;
		$error = gettext("The protocol from the URL you entered is not the same as the protocol we're running on right now. This means the Iframe Redirect URL may not work as expected. You need to confirm the use of this URL to continue.");
	} else {
		$index = ($index == "true") ? 1:0;
		$accessCount = ($accessCount == "true") ? 0:NULL;
		if($type == "redirect" || $type == "iframe") {
			dbQuery("INSERT INTO " . DB_PREFIX . "slugs(name, type, content, mime, extension, bot_index, access_count) SELECT ?, ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM " . DB_PREFIX . "slugs WHERE LOWER(name) = LOWER(?));",
				[ $name, $type, $url, NULL, NULL, $index, $accessCount, $name ]);
		} else {
			// TODO: more efficient solution to upload to DB.
			$extension = pathinfo($file["name"], PATHINFO_EXTENSION);
			if(empty($extension)) {
				$extension = NULL;
			} else {
				$extension = substr($extension, 0, 32);
			}
			$mime = mime_content_type($file["tmp_name"]);
			if(empty($mime) || strlen($mime) > 255) {
				$mime = "application/octet-stream";
			}
			dbQuery("INSERT INTO " . DB_PREFIX . "slugs(name, type, content, mime, extension, bot_index, access_count) SELECT ?, ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM " . DB_PREFIX . "slugs WHERE LOWER(name) = LOWER(?));",
				[ $name, $type, $fileContent, $mime, $extension, $index, $accessCount, $name ]);
		}
		$showForm = FALSE;
	}
}

$newNonce = getNonce("shorten");
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
		#header p a {
			display: inline-block;
			margin-right: 5px;
			font-size: 1.2em;
		}
		#error {
			margin-top: 50px;
			color: rgb(255, 0, 0);
		}
		#form {
			display: block;
			margin: 50px 0px;
		}
		#form .field {
			display: block;
			margin-top: 0px;
			margin-left: auto;
			margin-bottom: 25px;
			margin-right: auto;
			vertical-align: middle;
		}
		#form .field span {
			display: block;
		}
		#form .field.options span {
			margin-top: 15px;
		}
		#form .field.options p.small {
			padding-left: 25px;
			text-align: justify;
		}
		#form .field.text input, #form .field.strtext input, #form .field.file input {
			display: inline-block;
			border-style: solid;
			border-width: 0px 0px 2px 0px;
			border-color: rgba(0, 0, 0, 0.25);
			outline-width: 0px;
			margin: 0px auto;
			padding: 5px;
			box-sizing: border-box;
			width: 100%;
			transition: border-color 0.25s;
		}
		#form .field.text input:hover, #form .field.strtext input:hover, #form .field.file input:hover {
			border-color: rgba(0, 0, 0, 1);
		}
		#form .field.text input:focus, #form .field.strtext input:focus, #form .field.file input:focus {
			border-color: rgba(132, 0, 255, 1);
		}
		#form .field.strtext span > .smallest {
			display: inline-block;
			margin-right: 5px;
			font-size: 0.75em;
		}
		#form .field.strtext input {
			width: 50%;
		}
		#form .field.file input {
			width: 80%;
		}
		#form .field.text span.check label {
			color: rgb(255, 0, 0);
		}
		#form .field.text span.check input {
			width: auto;
		}
		#form .field.file input {
			border-width: 2px;
		}
		#form .field.file span a {
			margin-left: 5px;
			text-decoration: none;
		}
		#form .field.buttons button {
			display: inline-block;
			margin: 2px;
			padding: 10px;
			border-style: solid;
			border-width: 2px;
			border-color: rgb(0, 0, 0);
			cursor: pointer;
			color: rgb(255, 255, 255);
			background-color: rgb(132, 0, 255);
			transition: background-color,color 0.25s,0.25s;
		}
		#form .field.buttons button:hover {
			color: rgb(255, 255, 255);
			background-color: rgb(84, 38, 128);
		}
		#form .field.buttons button:first-child {
			margin-left: 0px;
		}
		#form .field.buttons button:last-child {
			margin-right: 0px;
		}
		#form .field .small {
			font-size: 0.75em;
		}
		#form .field a {
			display: inline-block;
			color: rgb(0, 0, 0);
			transition: color 0.25s;
		}
		#form .field a:hover {
			color: rgb(84, 38, 128);
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
	<script type="text/javascript">
		const MAX_FILE_UPLOAD_SIZE = <?php echo getMaxUploadSize(); ?>;
		const MAX_FILE_UPLOAD_STR = "<?php echo sprintf(gettext("The file you are trying to upload exceeds the maximum length of %s."), bytesToSize(getMaxUploadSize(), [0 => gettext("%.2f bytes"), 1 => gettext("%.2f byte"), 2 => gettext("%.2f bytes"), 1024 => gettext("%.2f KiB"), 1048576 => gettext("%.2f MiB"), 1073741824 => gettext("%.2f GiB")])); ?>";
		function windowLoaded() {
			let i, items;
			items = document.querySelectorAll(".jshidden");
			for(i = 0; i < items.length; i++) {
				items[i].style.display = "none";
			}
			typeChanged();
			fileChanged();
		}
		function checkFileSize() {
			let i, field, input, items;
			field = document.getElementById("form-file-field");
			input = field.querySelector("input");
			items = input.files;
			for(i = 0; i < items.length; i++) {
				if(items[i].size > MAX_FILE_UPLOAD_SIZE) {
					window.alert(MAX_FILE_UPLOAD_STR);
					return false;
				}
			}
			return true;
		}
		function typeChanged() {
			let i, url, file, items, value;
			url = document.getElementById("form-url-field");
			file = document.getElementById("form-file-field");
			items = document.getElementById("form-types-field").getElementsByTagName("input");
			value = "";
			for(i = 0; i < items.length; i++) {
				if(items[i].checked == true) {
					value = items[i].value;
					break;
				}
			}
			if(value == "redirect" || value == "iframe") {
				file.querySelector("input").required = false;
				file.style.display = "none";
				url.querySelector("input").required = true;
				url.style.display = "block";
				fileReset();
			} else if(value == "file" || value == "file-download") {
				url.querySelector("input").required = false;
				url.style.display = "none";
				file.querySelector("input").required = true;
				file.style.display = "block";
			} else {
				url.querySelector("input").required = false;
				url.style.display = "none";
				file.querySelector("input").required = false;
				file.style.display = "none";
			}
		}
		function fileChanged() {
			let field, input, link;
			field = document.getElementById("form-file-field");
			input = field.querySelector("input");
			link = field.querySelector("a");
			if(input.files.length < 1) {
				link.style.display = "none";
			} else {
				link.style.display = "inline-block";
			}
		}
		function fileReset() {
			let field, oldInput, newInput, link;
			field = document.getElementById("form-file-field");
			oldInput = field.querySelector("input");
			link = field.querySelector("a");
			newInput = document.createElement("input");
			newInput.id = oldInput.id;
			newInput.type = oldInput.type;
			newInput.name = oldInput.name;
			newInput.onchange = oldInput.onchange;
			link.parentNode.removeChild(oldInput);
			link.parentNode.insertBefore(newInput, link);
			link.style.display = "none";
		}
		window.onload = windowLoaded;
	</script>
</head>

<body>
	<div id="main">
		<div id="header">
			<a href="?">CAJUS</a>
			<p><a href="index.php" title="<?php echo gettext("Go back"); ?>">&#8592;</a><?php echo gettext("CAJUS Ain't Just a URL Shortener"); ?></p>
		</div>
		<?php
			if($error) {
				echo '<div id="error"><p>' . $error . '</p></div>';
			}
		?>
		<?php
			if($showForm) {
		?>
		<div id="form">
			<form action="?" method="post" enctype="multipart/form-data" onsubmit="return checkFileSize();" onreset="window.location.href = 'index.php';">
				<input type="hidden" name="action" value="slug">
				<input type="hidden" name="nonce" value="<?php echo $newNonce; ?>">
				<div class="field strtext">
					<label for="form-name"><?php echo gettext("Friendly URL:"); ?></label>
					<span><p class="smallest"><?php echo getCurrentWebPath(1); ?></p><input type="text" name="name" id="form-name" maxlength="255" placeholder="<?php echo gettext("example-homepage"); ?>" value="<?php echo parseForHTML($name); ?>" required="required" autofocus="autofocus"></span>
					<p class="small"><?php echo gettext("Only a-z, A-Z, 0-9, hyphen (-), underscore (_) characters are allowed."); ?></p>
				</div>
				<div id="form-types-field" class="field options">
					<label for="form-type-redirect"><?php echo gettext("Type of URL:"); ?></label>
					<span>
						<label><input type="radio" name="type" id="form-type-redirect" onchange="typeChanged();" value="redirect" required="required"<?php if($type === "redirect") { echo ' checked="checked"'; } ?>> <?php echo gettext("Redirect URL"); ?></label>
						<p class="small"><?php echo gettext("The friendly URL redirects to another web address."); ?></p>
					</span>
					<span>
						<label><input type="radio" name="type" id="form-type-iframe" onchange="typeChanged();" value="iframe" required="required"<?php if($type === "iframe") { echo ' checked="checked"'; } ?>> <?php echo gettext("Iframe Redirect URL"); ?></label>
						<p class="small"><?php echo gettext('The friendly URL shows a frame to another web address. This frame looks like "a webpage inside another webpage", meaning the address bar in the browser won\'t change. The framed website must allow this, and most of them don\'t.'); ?></p>
					</span>
					<span>
						<label><input type="radio" name="type" id="form-type-file" onchange="typeChanged();" value="file" required="required"<?php if($type === "file") { echo ' checked="checked"'; } ?>> <?php echo gettext("File"); ?></label>
						<p class="small"><?php echo gettext("The friendly URL redirects to a file that can be downloaded (eg.: *.zip or *.exe) or visualized in the browser (eg.: *.pdf or *.png)."); ?></p>
					</span>
					<span>
						<label><input type="radio" name="type" id="form-type-filedownload" onchange="typeChanged();" value="file-download" required="required"<?php if($type === "file-download") { echo ' checked="checked"'; } ?>> <?php echo gettext("File (force download)"); ?></label>
						<p class="small"><?php echo gettext("The friendly URL redirects to a file that can will be downloaded. Note that some browser won't respect this request, and can show the file in the browser instead of downloading it."); ?></p>
					</span>
				</div>
				<div id="form-url-field" class="field text">
					<label for="form-url"><?php echo gettext("Redirect URL:"); ?></label>
					<span><input type="text" name="url" id="form-url" maxlength="<?php echo CNS_MAXURLLENGTH; ?>" placeholder="<?php echo gettext("http://example.com/homepage"); ?>" value="<?php echo parseForHTML($url); ?>"></span>
					<p class="small"><?php echo gettext("The URL you are redirecting to. Don't forget the protocol (http, https, ...)."); ?></p>
					<p class="small jshidden"><?php echo gettext('You just need to fill this if you choose "Redirect URL" or "Iframe Redirect URL".'); ?></p>
					<?php if($confirmURL) { ?>
						<span class="check"><label><input type="checkbox" name="confirm_url" value="true" required="required"<?php if($confirmURLValue === "true") { echo ' checked="checked"'; } ?>> <?php echo gettext("I confirm I want to redirect to this URL."); ?></label></span>
					<?php } ?>
				</div>
				<div id="form-file-field" class="field file">
					<label for="form-file"><?php echo gettext("File to upload:"); ?></label>
					<span><input type="file" name="upload_file" onchange="fileChanged();" id="form-file"><a href="#" onclick="fileReset(); return false;" style="display: none;" title="<?php echo gettext("Remove file"); ?>">&times;</a></span>
					<p class="small"><?php echo sprintf(gettext("The file must have a maximum length of %s."), bytesToSize(getMaxUploadSize(), [0 => gettext("%.2f bytes"), 1 => gettext("%.2f byte"), 2 => gettext("%.2f bytes"), 1024 => gettext("%.2f KiB"), 1048576 => gettext("%.2f MiB"), 1073741824 => gettext("%.2f GiB")])); ?></p>
					<p class="small jshidden"><?php echo gettext('You just need to fill this if you choose "File" or "File (force download)".'); ?></p>
				</div>
				<div class="field checks">
					<span><label><input type="checkbox" name="index" value="true"<?php if($index === "true") { echo ' checked="checked"'; } ?>> <?php echo gettext("Allow search engine bots to index this."); ?></label></span>
					<span><label><input type="checkbox" name="access_count" value="true"<?php if($accessCount === "true") { echo ' checked="checked"'; } ?>> <?php echo gettext("Count accesses or clicks."); ?></label></span>
				</div>
				<div class="field buttons">
					<button type="submit"><?php echo gettext("Create this URL"); ?> &#187;</button>
					<button type="reset"><?php echo gettext("Cancel"); ?> &#215;</button>
				</div>
			</form>
		</div>
		<?php
			} else {
		?>
		<div id="form">
			<div class="field text">
				<input type="text" value="<?php echo getCurrentWebPath(1) . $name; ?>" style="cursor: pointer;" onmouseover="this.select();"  onclick="this.select(); this.setSelectionRange(0, 99999); document.execCommand('copy');" readonly="readonly">
				<p class="small"><?php echo gettext("You can now copy and use this link anywhere."); ?></p>
			</div>
			<div class="field buttons">
				<button type="button" onclick="window.location.href = '?';"><?php echo gettext("Create another URL"); ?> &#187;</button>
				<button type="button" onclick="window.location.href = 'index.php';"><?php echo gettext("Done"); ?> &#215;</button>
			</div>
		</div>
		<?php
			}
		?>
		<div id="footer">
			<p class="center"><?php echo gettext("Logged-in"); ?> (<a href="logout.php?nonce=<?php echo $logoutNonce; ?>"><?php echo gettext("Logout"); ?></a>)</p>
		</div>
	</div>
	<div id="main-noscreen">
		<p><?php echo gettext("Your screen is too small to show this webpage."); ?></p>
	</div>
</body>

</html>
