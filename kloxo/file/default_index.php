<?php
	ini_set("display_errors","1");

	// use default index.php
	if (!file_exists("./custom-index.php")) {
?>

<?php
	if (file_exists("./custom-inc.php")) {
		// use user-define inc.php -- no override when kloxo update
		$incfile = "./custom-inc.php";
		if (file_exists("./custom-inc2.php")) {
			$incfile2 = "./custom-inc2.php";
		}
	}
	else {
		// use default inc.php
		$incfile = "./inc.php";
		if (file_exists("./inc2.php")) {
			$incfile2 = "./inc2.php";
		}
	}
?>
<html>

<head>
	<title>Kloxo Control Panel</title>

	<meta http-equiv="Content-Language" content="en-us">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">

<?php if(isset($incfile2)) { include_once $incfile2 ; } ?>

	<style type="text/css">
		body {
			font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
			font-size: 8pt; font-weight: 100; background: #ddeeff; margin: 0;
		}

		a { text-decoration: none; }

		img { border: 0; }

		img.logo { margin: 5px; padding: 0; }

		table.header {
			border-collapse: collapse; border-spacing: 0;
			background-color: #66aadd; width: 100%;
			font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
			font-size: 8pt; font-weight: 100;
		}

		table.content {
			border-collapse: collapse; border-spacing: 0; width: 100%;
			font-family: Tahoma, Verdana, Arial, Helvertica, sans-serif;
			font-size: 8pt; font-weight: 100;
		}
	</style>
</head>

<body>

	<table class="header">
		<tr>
			<td valign="top" width="100%"><img class="logo" 
				src="./images/logo.png" height="75" alt="hosting-logo"></td>
			<td>
				<table class="content">
					<tr>
						<td><a href="http://lxcenter.org/" 
							title="Go to LxCenter website"><img class="logo" 
							src="./images/lxcenter.png" alt="lxcenter-logo" 
							width="120" height="35"></a></td>
					</tr>
					<tr>
						<td><a href="http://lxcenter.org/software/kloxo/" 
							title="Go to Kloxo website"><img class="logo" 
							src="./images/kloxo.png" alt="kloxo-logo" 
							width="120" height="27"></a></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" bgcolor="#000000">&nbsp;</td>
		</tr>
	</table>
	<table class="content">
		<tr>
			<td width="50">&nbsp;</td>
			<td valign="top"><?php include_once $incfile; ?></td>
			<td width="280" valign="center"><img src="./images/disableskeletonbg.gif"></td>
		</tr>
	</table>

</body>

</html>

<?php
	}
	else {
		// use user-define index.php -- no override when kloxo update
		include_once "./custom-index.php";
	}
?>

