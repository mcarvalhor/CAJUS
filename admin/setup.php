<?php

if(file_exists(__DIR__ . DIRECTORY_SEPARATOR . "server" . DIRECTORY_SEPARATOR . "config.php")) {
	header("Location: index.php");
	exit(0);
}

echo "You need to setup CAJUS first.<br><br>" . PHP_EOL . PHP_EOL;
echo "Take a look at the file <em>admin/server/config.sample.php</em>. Modify it and rename to <em>admin/server/config.php</em>.<br>" . PHP_EOL;
echo "If you need to generate a secure access password to the config file, <a href='forgotPassword.php' style='color: black;'>click here</a>.<br>" . PHP_EOL;
echo "Run all the 'CREATE TABLE' queries available at <em>admin/server/sqlSetup.php</em>. Don't forget to use the same <em>{prefix}</em> you configured in the variable <em>DB_PREFIX</em> from configuration file.<br>" . PHP_EOL;
exit(0);

// TODO: Setup page.

$show = "requirements";

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
			<p><?php echo ("CAJUS Ain't Just a URL Shortener"); ?></p>
		</div>
		<?php
			if($show == "requirements") {
		?>
		<div id="info">
			<p><?php echo ("You need to setup CAJUS and its database server first."); ?></p>
			<p><?php echo ("If you are not a computer advanced user, ask someone to help you in this setup."); ?></p>
		</div>
		<div id="requirements">
			<p><?php echo ("These are the requirements for the CAJUS to work properly."); ?></p>
			<ul>
				<li><span>Apache2 or IIS web server</span><span>Ok</span></li>
				<li><span>PHP 7.0+</span><span>Ok</span></li>
				<li><span>Image manipulation extension for PHP (Imagick)</span><span>Ok</span></li>
				<li><span>Read/Write permission</span><span>Ok</span></li>
				<li><span>Hidden directory</span><span>Ok</span></li>
			</ul>
		</div>
		<div id="buttons">
			<a href="?action=db"></a>
		</div>
		<?php
			} else {
		?>
		<?php
			}
		?>
		<div id="footer">
			<p class="center"></p>
		</div>
	</div>
	<div id="main-noscreen">
		<p><?php echo ("Your screen is too small to show this webpage."); ?></p>
	</div>
</body>

</html>
