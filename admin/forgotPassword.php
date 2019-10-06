<?php

bindtextdomain("admin", "server/locale");
textdomain("admin");

header("Cache-Control: private, no-store, max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");


$showForm = TRUE;

if($_POST["action"] == "password") {
	$password = $_POST["password"];
	if(!empty($password)) {
		$password = password_hash($password, PASSWORD_DEFAULT);
		$showForm = FALSE;
	}
}

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
			width: 90%;
			min-width: 315px;
			max-width: 650px;
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
		#info {
			display: block;
			font-size: 1em;
			text-align: justify;
		}
		#info ol li {
			margin-top: 10px;
		}
		#info ol li:first-child {
			margin-top: 0px;
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
		#form .field.text input {
			display: block;
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
		#form .field.text input:hover {
			border-color: rgba(0, 0, 0, 1);
		}
		#form .field.text input:focus {
			border-color: rgba(132, 0, 255, 1);
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
		#form .field .small.break {
			word-break: break-all;
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
		<div id="info">
			<p><?php echo gettext("If you forgot the admin access password, you need to generate a new password and modify the server configuration file manually."); ?></p>
			<p><?php echo gettext("If you are not a computer advanced user, ask someone to help you in this process."); ?></p>
		</div>
		<div id="form">
			<form action="?" method="post" onreset="window.location.href = 'index.php';">
				<input type="hidden" name="action" value="password">
				<div class="field text">
					<input type="password" name="password" maxlength="20" minlength="4" placeholder="(<?php echo gettext("New password"); ?>)" required="required" autofocus="autofocus">
				</div>
				<div class="field buttons">
					<button type="submit"><?php echo gettext("Generate new password"); ?> &#187;</button>
					<button type="reset"><?php echo gettext("Cancel"); ?> &#215;</button>
				</div>
			</form>
		</div>
		<?php
			} else {
		?>
		<div id="info">
			<p><?php echo gettext("Now, copy the generated secure password and modify it in the configuration file."); ?></p>
			<p>
				<ol>
					<li><?php echo gettext("Connect to the server in which this instance of CAJUS is hosted. This is usually made by using SSH software."); ?></li>
					<li><?php echo sprintf(gettext("Access the CAJUS server configuration file. It is located at %s."), "<strong>[...]/admin/server/config.php</strong>"); ?></li>
					<li><?php echo sprintf(gettext("Locate the line that looks like this: %s."), "<strong>const CNF_DEFAULTPASSWORD = '[...]';</strong>"); ?></li>
					<li><?php echo gettext("Modify only the portion of this line inside the apostrophes (') and make it equal to the value in the box below. Don't forget to keep the apostrophes (') and the semicolon (;)."); ?></li>
					<li><?php echo gettext("Save the file and try to log-in again using the new password."); ?></li>
				</ol>
			</p>
			<p><?php echo gettext("If you are not a computer advanced user, ask someone to help you in this process."); ?></p>
		</div>
		<div id="form">
			<div class="field text">
				<label for="form-password">Generated secure password:</label>
				<input type="text" value="<?php echo $password; ?>" id="form-password" style="cursor: pointer;" onmouseover="this.select();"  onclick="this.select(); this.setSelectionRange(0, 99999); document.execCommand('copy');" readonly="readonly">
				<p class="small"><?php echo gettext("Put the text in this box inside the the apostrophes (') of the located line."); ?></p>
				<p class="small break"><?php echo sprintf(gettext("Expected resulting line: %s"), "<br><strong>const CNF_DEFAULTPASSWORD = '" . $password . "';</strong>"); ?></p>
			</div>
			<div class="field buttons">
				<button type="button" onclick="window.location.href = '?';"><?php echo gettext("Generate a different password"); ?> &#187;</button>
				<button type="button" onclick="window.location.href = 'index.php';"><?php echo gettext("Done"); ?> &#215;</button>
			</div>
		</div>
		<?php
			}
		?>
		<div id="footer">
			<p class="center"></p>
		</div>
	</div>
	<div id="main-noscreen">
		<p><?php echo gettext("Your screen is too small to show this webpage."); ?></p>
	</div>
</body>

</html>
