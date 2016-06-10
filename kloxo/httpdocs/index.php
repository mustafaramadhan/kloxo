<?php

include_once "lib/html/displayinclude.php";

include_once "lib/redirect.php";

main_main();

function domainshow()
{
	global $gbl, $sgbl, $login, $ghtml;

	if ($login->isAdmin()) {
		$doctype = "admin";
		$domainclass = "all_domaina";
	} else {
		$doctype = "client";
		$domainclass = "domaina";
	}

	$url = "a=show";
	$url = $ghtml->getFullUrl($url);

	if (lxfile_exists("theme/frame_top_vendor.php")) {
		$file = "/theme/frame_top_vendor.php";
	} else {
		$file = "/theme/frame_top.php";
	}


	if ($login->isAdmin()) {
		//$url = '/display.php?frm_action=list&frm_o_cname=client';
	}

	$sp = $login->getSpecialObject('sp_specialplay');

	if ($sp->isOn('lpanel_scrollbar')) {
		$lpscroll = 'auto';
	} else {
		$lpscroll = 'no';
	}

	if ($gbl->isOn('show_help')) {
		$scrollstring = 'scrolling=no';
		$width = $sgbl->__var_lpanelwidth;
	} else {
		$scrollstring = "scrolling=$lpscroll";
		$width = $sgbl->__var_lpanelwidth;
	}
?>
	<head>
		<title> <?=get_title()?> </title>
		<meta http-equiv="Content-Language" content="en-us">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

<?php
		$ghtml->print_refresh_key();
?>
	</head>
<?php

	if ($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) {
?>
		<FRAMESET frameborder="0" rows="*,16"  border="0">
		<FRAME name="mainframe" src="<?= $url ?>">
		<FRAME name="bottomframe" src="/theme/frame_bottom.php">
<?php

		return;
	}

//	if ($login->isDefaultSkin()) {
//		$headerheight = 93;
//	} else {
		if ($login->getSpecialObject('sp_specialplay')->isOn('show_thin_header')) {
			$headerheight = 29;
		} else {
			$headerheight = 132;
			$headerheight = 29;
		}
//	}

?>
<FRAMESET frameborder="0" rows="<?= $headerheight ?>,*" border="0">
	<FRAME name="topframe" src="<?= $file ?>" scrolling="no">
<?php

if (!$sp->isOn('split_frame')) {
?>
	<FRAMESET frameborder="0" cols="<?= $width ?>,*" border="0">
	<FRAME name=leftframe src="/theme/frame_left.php?lpanel_type=tree" <?= $scrollstring ?> border="0">
<?php
}

if ($sp->isOn('split_frame')) {
?>
	<FRAMESET frameborder="0" cols="50%,*" border="0">
<?php
}
?>
	<FRAMESET frameborder="0" rows="*,16" border="0">
		<FRAME name="mainframe" src="<?= $url ?>">
		<FRAME name="bottomframe" src="/theme/frame_bottom.php">
<?php
		if ($sp->isOn('split_frame')) {
?>
			<FRAME name="rightframe" src="<?= $url ?>">
<?php
		}
?>
	</FRAMESET>
	</FRAMESET>
<?php
}

function main_main()
{
	global $gbl, $login, $ghtml;

	initProgram();

	if ($login->getSpecialObject('sp_specialplay')->skin_name === 'default') {
		set_login_skin_to_simplicity();
	}

	if (($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) || 
			($login->getSpecialObject('sp_specialplay')->skin_name === 'simplicity')) {
	//	include_once "./display.php";
		header( 'Location: /display.php?frm_action=show' ) ;
	} else {
		domainshow();
	}
}
