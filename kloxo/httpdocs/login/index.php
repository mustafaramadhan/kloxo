<?php
	ini_set("display_errors","1");

//	if(!isset($_SESSION)) {
	if (session_id() == "") {
		session_start();
	}

	if (file_exists("./custom-index.php")) {
		include_once "./custom-index.php";
	} elseif (file_exists("./custom.index.php")) {
		include_once "./custom.index.php";
	} else {

		if (file_exists("./custom-inc.php")) {
			$incfile = "./custom-inc.php";
		} elseif (file_exists("./custom.inc.php")) {
			$incfile = "./custom.inc.php";
		} else {
			if (file_exists("./inc.php")) {
				$incfile = "./inc.php";
			}
		}

		if (file_exists("./custom-inc2.php")) {
			$incfile2 = "./custom-inc2.php";
		} elseif (file_exists("./custom.inc2.php")) {
			$incfile = "./custom.inc2.php";
		} else {
			if (file_exists("./inc2.php")) {
				$incfile2 = "./inc2.php";
			}
		}

		if (file_exists("./images/user-logo.png")) {
			$logo_url = "./images/user-logo.png";
		} else {
			$logo_url = "./images/logo.png";
		}
?>
<html>

<head>
	<meta http-equiv="Content-Language" content="en-us" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php

		if(isset($incfile2)) { include_once $incfile2 ; }

		if (isset($page)) {
			$title = "Kloxo-MR {$page} page";
		} else {
			$title = "Kloxo-MR Page";
		}

		if (basename(getcwd()) === 'login') {

			$selimg = './images/abstract.jpg';

			$path = "../theme/background";

			// MR -- trick to make random background for login
			if ((file_exists($path)) && (!file_exists("./.norandomimage"))) {
				try {
					$dirs = glob("{$path}/*", GLOB_MARK);

					if ($dirs) {
						$count = count($dirs);
						$selnum = rand(0, ($count - 1));

						if ((isset($_SESSION['last_login_time'])) && (isset($_SESSION['num_login_fail']))) {
							if ($_SESSION['num_login_fail'] == 5) {
								$selimg = "{$path}/abstract_003.jpg";
							} else {
								$selimg = $dirs[$selnum];
							}
						} else {
							$selimg = $dirs[$selnum];
						}
					}
				} catch (Exception $e) { }
			} else {
				$c = trim(file_get_contents("./.norandomimage"));
					
				if ($c !== '') {
					$selimg = "{$path}/{$c}";
				}
			}

			$bckgrnd = "\tbackground-image: url({$selimg});\n".
				"\tbackground-size: cover;\n".
				"\tbackground-attachment: fixed;";
		} else {
			$bckgrnd = "\tbackground-image: url(./images/abstract.jpg);";
		}
?>
	<title><?= $title; ?></title>
<style>
body {
	font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
	font-size: 8pt;
	font-weight: 100;
<?= $bckgrnd; ?>

	background-color:#cccccc;
	margin: 0;
}
a {
	text-decoration: none;
}
img {
	border: 0;
}

img.logo {
	margin: 5px;
	padding: 0;
}

table.header {
	border-collapse: collapse;
	border-spacing: 0;
	width: 100%;
	font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
	font-size: 8pt;
	font-weight: 100;
}

table.content {
	border-collapse: collapse;
	border-spacing: 0;
	width: 100%;
	/* height: 100%; */
	font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
	font-size: 8pt;
	font-weight: 100%;
}

table.content_body td {
	border-collapse: collapse;
	border: 1px dashed #cccccc;
	font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
	font-size: 10pt;
	color: #444444;
	padding:10px;
	spacing:0;
}

table.content_title td {
	border-collapse: collapse;
	border: 0;
	font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
	font-size: 12pt;
	color: #336699;
}

</style>

</head>

<body>

<table class="header">
	<tr>
		<td width="100%"><img style="margin:5px; padding:5px; height:50px" class="logo" src="<?php echo $logo_url; ?>" alt="hosting-logo"></td>
		<td><a href="//mratwork.com/work/" title="Go to Kloxo-MR website"><img style="margin:5px; padding:5px; height:50px" class="logo" src="./images/kloxo-mr.png" alt="kloxo-mr-logo"></a></td>
	</tr>
</table>
<table class="content">
	<tr>
		<td width="50">&nbsp;</td>
		<td valign="middle" width="100%"><?php include_once $incfile; ?></td>
		<td width="50">&nbsp;</td>
	</tr>
</table>
</body>

</html>
<?php
		}
?>
