<?php

if (strpos($_SERVER["SCRIPT_NAME"], "/display.php") !== false) {
	// no action
} else {
	print("No permit access directly");
	return;
}

$syncserver = $login->syncserver;

$loginas = $login->nname;
$clientid = $loginas;

$consumedlogin = "";

if (isset($ghtml->frm_consumedlogin)) {
	if ($ghtml->frm_consumedlogin === 'true') {
		$consumedlogin = "frm_consumedlogin=true&";
	}
}

$loginquery = "frm_o_o[0][class]=client&frm_o_o[0][nname]={$loginas}&";

if (isset($ghtml->frm_o_o[0]['class']) && ($ghtml->frm_o_o[0]['class'] === 'client')) {
	$clientid = $ghtml->frm_o_o[0]['nname'];

	$clientquery = "frm_o_o[0][class]=client&frm_o_o[0][nname]={$clientid}&";
} else {
	$clientquery = "";
}

if (isset($ghtml->frm_o_o[1]['class']) && ($ghtml->frm_o_o[1]['class'] === 'client')) {
	$clientid2 = $ghtml->frm_o_o[1]['nname'];

	$clientquery2 = "frm_o_o[0][class]=client&frm_o_o[1][nname]={$clientid}&";
} else {
	$clientquery2 = "";
}

$serverquery = "frm_o_o[0][class]=pserver&frm_o_o[0][nname]={$syncserver}&";
$localhostquery = "frm_o_o[0][class]=pserver&frm_o_o[0][nname]=localhost&";

if (isset($ghtml->frm_o_o[0]['class']) && ($ghtml->frm_o_o[0]['class'] === 'domain')) {
	$domainid = $ghtml->frm_o_o[0]['nname'];

	$domainquery = "frm_o_o[0][class]=client&frm_o_o[0][nname]={$clientid}&";
} else {
	$domainquery = "";
}

if (isset($ghtml->frm_o_o[1]['class']) && ($ghtml->frm_o_o[1]['class'] === 'domain')) {
	$domainid2 = $ghtml->frm_o_o[1]['nname'];

	$domainquery2 = "frm_o_o[0][class]=client&frm_o_o[0][nname]={$clientid}&";
} else {
	$domainquery2 = "";
}

if (($clientquery !== '') || ($syncserver !== 'localhost')) {
	$genwidth = "640";
} else {
	$genwidth = "480";
}

?>
<!-- 'BEGIN: Simplicity menu' -->
<?php
	if (file_exists(getcwd() . "/theme/skin/simplicity/default/css/custom.menu.css")) {
?>

<link rel="stylesheet" type="text/css" href="/theme/skin/simplicity/default/css/custom.menu.css"/>
<?php
	} else {
?>

<link rel="stylesheet" type="text/css" href="/theme/skin/simplicity/default/css/menu.css"/>
<?php
	}
?>

<div id="menu_div" class="div_menu shadow_all">
<?php
// MR -- prevent for mailaccount login
if (strpos($loginas, "@") !== false) {
	// no action
} else {
?>

<div style="float:left">
	<ul class="menuTemplate2 decor2_1">
		<li><a href="/display.php?<?= $consumedlogin ?>frm_action=show"><?= $login->getKeywordUc('home') ?></a>
<?php
	if (!$login->isAdmin()) {
?>
			<div class="drop2 decor2_2" style="width:320px">
				<div class='left'>
<?php
		if ($clientquery !== "") {
			if ($clientquery2 === "") {
?>
					&#x00bb;&nbsp;<?= $login->getKeywordUc('home') ?>

						&#x00bb;&nbsp;<a href="/display.php?frm_action=show&<?= $clientquery ?>"><?= $clientid ?></a><br/>
<?php
			} else {
?>
					&#x00bb;&nbsp;<?= $login->getKeywordUc('home') ?>

						&#x00bb;&nbsp;<a href="/display.php?frm_action=show&<?= $clientquery ?>"><?= $clientid ?></a>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=show&<?= $clientquery ?><?= $clientquery2 ?>"><?= $clientid2 ?></a><br/>
<?php
			}
		}

		if ($consumedlogin !== '') {
		// MR -- don't include $consumedlogin here!
?>
					&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=update&sa=dologin&o=client") ?>

						&#x00bb;&nbsp;<a href="/display.php?frm_action=show&<?= $loginquery ?>"><?= $loginas ?> (<?= $login->getKeywordUc('cancel') ?>)</a><br/>
<?php
		} else {
?>
					&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=update&sa=dologin&o=client") ?>

						&#x00bb;&nbsp;<a href="/display.php?frm_action=update&frm_subaction=dologin&<?= $clientquery ?>"><?= $clientid ?></a><br/>
<?php
		}
?>
				</div>
			</div>
<?php
	}
?>
		</li>
		<li class="separator"></li>
		<li><a href="javascript://" class="arrow"><?= $login->getKeywordUc('administration') ?></a>
			<div class="drop2 decor2_2" style="width:<?= $genwidth ?>px">
<?php
	if ($login->isCustomer()) {
?>
				<div class='left'>
<?php
	} else {
?>
				<div class='left rightborder'>
					<b><?= $login->getKeywordUc('alllist') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_client"><?= $ghtml->getTitleOnly("a=list&c=all_client") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_domain"><?= $ghtml->getTitleOnly("a=list&c=all_domain") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_addondomain"><?= $ghtml->getTitleOnly("a=list&c=all_addondomain") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_mailaccount"><?= $ghtml->getTitleOnly("a=list&c=all_mailaccount") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_mailforward"><?= $ghtml->getTitleOnly("a=list&c=all_mailforward") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_mailinglist"><?= $ghtml->getTitleOnly("a=list&c=all_mailinglist") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_mysqldb"><?= $ghtml->getTitleOnly("a=list&c=all_mysqldb") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_cron"><?= $ghtml->getTitleOnly("a=list&c=all_cron") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_ftpuser"><?= $ghtml->getTitleOnly("a=list&c=all_ftpuser") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_sslcert"><?= $ghtml->getTitleOnly("a=list&c=all_sslcert") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_client") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_client"><?= $loginas ?></a><br/>

						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_domain") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_domain"><?= $loginas ?></a><br/>

						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_addondomain") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_addondomain"><?= $loginas ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_mailaccount") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_mailaccount"><?= $loginas ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_mailinglist") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_mailinglist"><?= $loginas ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_mailforward") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_mailforward"><?= $loginas ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_mysqldb") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_mysqldb"><?= $loginas ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_cron") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_cron"><?= $loginas ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_ftpuser") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_ftpuser"><?= $loginas ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_sslcert") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_sslcert"><?= $loginas ?></a><br/>
<?php
	}
?>					</div>
					<b><?= $login->getKeywordUc('resourceplan') ?></b>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=resourceplan"><?= $ghtml->getTitleOnly("a=list&c=resourceplan") ?></a><br/>
<?php
		if ($clientquery !== '') {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=update&sa=change_plan") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=change_plan&<?= $clientquery ?>"><?= $clientid ?></a><br/>
<?php
		}
?>
					</div>
<?php
		if (!$login->isAdmin()) {
?>
				</div>
				<div class='left'>
<?php
		}
?>
					<b><?= $login->getKeywordUc('support') ?></b>
					<div class="dropmenu">
<?php
		if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=ticket"><?= $ghtml->getTitleOnly("a=list&c=ticket") ?></a><br/>
<?php
		} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=ticket") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=ticket"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=ticket"><?= $clientid ?></a><br/>
<?php
		}

		if ($login->isAdmin()) {
?>
							&#x00bb;&nbsp;<a href="/display.php?frm_action=updateform&frm_subaction=ticketconfig&frm_o_o[0][class]=ticketconfig"><?= $ghtml->getTitleOnly("a=updateform&sa=ticketconfig&o=ticketconfig") ?></a><br/>
							&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_o[0][class]=general&frm_o_cname=helpdeskcategory_a"><?= $ghtml->getTitleOnly("a=list&o=general&c=helpdeskcategory_a") ?></a><br/>
<?php
		}
?>
					</div>
<?php
	}
?>
					<b><?= $login->getKeywordUc('message') ?></b>
					<div class="dropmenu">
<?php
		if ($clientquery === '') {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=smessage"><?= $ghtml->getTitleOnly("a=list&c=smessage") ?></a><br/>
<?php
		} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=smessage") ?> &#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=smessage"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=smessage"><?= $clientid ?></a><br/>
<?php
		}
?>
					</div>
<?php

	if ($login->isAdmin()) {
?>
				</div>
				<div class='left'>
<?php
	}
?>

<?php
	if ($login->isAdmin()) {
?>
					<b><?= $login->getKeywordUc('custombutton') ?></b>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_cname=custombutton"><?= $ghtml->getTitleOnly("a=list&c=custombutton") ?></a><br/>
					</div>
<!--
					<b><?= $login->getKeywordUc('reversedns') ?></b>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_cname=reversedns"><?= $ghtml->getTitleOnly("a=list&c=reversedns") ?></a><br/>
					</div>
-->
<?php
	}

	if ($login->isAdmin()) {
?>
					<b><?= $login->getKeywordUc('update') ?></b>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?frm_action=show&frm_o_o[0][class]=lxupdate"><?= $ghtml->getTitleOnly("a=show&o=lxupdate") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_o[0][class]=lxupdate&frm_o_cname=releasenote"><?= $ghtml->getTitleOnly("a=list&o=lxupdate&c=releasenote") ?></a><br/>
					</div>
<?php
	}

?>
					<b><?= $login->getKeywordUc('other') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === '') {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=information"><?= $ghtml->getTitleOnly("a=updateform&sa=information&o=client") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=actionlog"><?= $ghtml->getTitleOnly("a=list&c=actionlog") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=password"><?= $ghtml->getTitleOnly("a=updateform&sa=password&o=client") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=skin&frm_o_o[0][class]=sp_specialplay"><?= $ghtml->getTitleOnly("a=updateform&sa=skin&o=sp_specialplay") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=information&o=client") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=information"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=information&<?= $clientquery ?>"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=actionlog") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=actionlog"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=actionlog"><?= $clientid ?></a><br/>

						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=password&o=client") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=password"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=password&<?= $clientquery ?>"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=skin&o=sp_specialplay") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_o_o[0][class]=sp_specialplay"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&<?= $clientquery ?>frm_o_o[1][class]=sp_specialplay"><?= $clientid ?></a><br/>
<?php
	}
?>
					</div>
				</div>
				<div style='clear: both;'></div>
			</div>
		</li>
		<li class="separator"></li>
		<li><a href="javascript://" class="arrow"><?= $login->getKeywordUc('resource') ?></a>
			<div class="drop2 decor2_2" style="width:<?= $genwidth ?>px">
				<div class='left rightborder'>
					<b><?= $login->getKeywordUc('auxiliary') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=auxiliary"><?= $ghtml->getTitleOnly("a=list&c=auxiliary") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=auxiliary") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=auxiliary"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=auxiliary"><?= $clientid ?></a><br/>
<?php
	}

	if ($login->isAdmin()) {
		if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=all_auxiliary"><?= $ghtml->getTitleOnly("a=list&c=all_auxiliary") ?></a><br/>
<?php
		} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=all_auxiliary") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_auxiliary"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=all_auxiliary"><?= $clientid ?></a><br/>
<?php
		}
	}
?>
					</div>

					<b><?= $login->getKeywordUc('ssl') ?></b>
<?php
	if ($clientquery === '') {
		if ($domainquery === '') {
?>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_cname=sslcert"><?= $ghtml->getTitleOnly("a=list&c=sslcert") ?></a><br/>
					</div>
<?php
		} else {
?>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_o[0][class]=domain&frm_o_o[0][nname]=<?= $domainid ?>&frm_o_o[1][class]=web&frm_o_cname=sslcert"><?= $ghtml->getTitleOnly("a=list&c=sslcert") ?></a><br/>
					</div>
<?php
		}
	} else {
		if ($domainquery === '') {
?>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_o[0][class]=client&frm_o_o[0][nname]=<?= $clientid ?>&frm_o_cname=sslcert"><?= $ghtml->getTitleOnly("a=list&c=sslcert") ?></a><br/>
					</div>
<?php
		} else {
?>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_o[0][class]=client&frm_o_o[0][nname]=<?= $clientid ?>&frm_o_o[1][class]=domain&frm_o_o[1][nname]=<?= $domainid ?>&frm_o_o[1][class]=domain&frm_o_o[1][nname]=<?= $domainid2 ?>&frm_o_o[2][class]=web&frm_o_cname=sslcert"><?= $ghtml->getTitleOnly("a=list&c=sslcert") ?></a><br/>
					</div>
<?php
		}
	}
?>
					<b><?= $ghtml->getTitleOnly("a=list&c=ipaddress") ?></b>
					<div class="dropmenu">
<?php

	if ($login->isAdmin()) {
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=ipaddress") ?>

							&#x00bb;&nbsp;<a href="/display.php?frm_action=list&<?= $localhostquery ?>frm_o_cname=ipaddress"> &#x00bb;&nbsp; localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?frm_action=list&<?= $serverquery ?>frm_o_cname=ipaddress"><?= $syncserver ?></a><br/>

						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=update&sa=readipaddress&o=pserver") ?>

							&#x00bb;&nbsp;<a href="/display.php?frm_action=update&frm_subaction=readipaddress&<?= $localhostquery ?>"> &#x00bb;&nbsp; localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?frm_action=update&frm_subaction=readipaddress&<?= $serverquery ?>"><?= $syncserver ?></a><br/>

<?php
		} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=ipaddress") ?>&nbsp;&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_cname=ipaddress"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=update&sa=readipaddress&o=pserver") ?>&nbsp;&#x00bb;&nbsp;<a href="/display.php?frm_action=update&frm_subaction=readipaddress&<?= $serverquery ?>"><?= $syncserver ?></a><br/>
<?php
		}
	}
?>
					</div>
<?php

	if ($login->isAdmin()) {
?>
					<b><?= $login->getKeywordUc('dnstemplate') ?></b>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_cname=dnstemplate"><?= $ghtml->getTitleOnly("a=list&c=dnstemplate") ?></a><br/>

<?php
		if ($clientquery !== "") {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=dnstemplatelist") ?>

							&#x00bb;&nbsp;<a href="/display.php?frm_action=updateform&frm_subaction=dnstemplatelist&<?= $clientquery ?>"><?= $clientid ?></a><br/>
<?php
		}
?>
					</div>
<?php
	}

	if ($login->isAdmin()) {
?>

				</div>
				<div class='left'>
<?php
	}
?>
					<b><?= $login->getKeywordUc('backuprestore') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&frm_o_o[0][class]=lxbackup"><?= $ghtml->getTitleOnly("a=show&o=lxbackup") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=ftp_conf&frm_o_o[0][class]=lxbackup"><?= $ghtml->getTitleOnly("a=updateform&sa=ftp_conf&o=lxbackup") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=schedule_conf&frm_o_o[0][class]=lxbackup"><?= $ghtml->getTitleOnly("a=updateform&sa=schedule_conf&o=lxbackup") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&frm_o_o[0][class]=lxbackup&frm_o_o[1][class]=ffile&frm_o_o[1][nname]=/"><?= $ghtml->getTitleOnly("a=show&o=lxbackup&l[class]=ffile&l[nname]=/") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=upload&frm_o_o[0][class]=lxbackup&frm_o_o[1][class]=ffile&frm_o_o[1][nname]=/"><?= $ghtml->getTitleOnly("a=updateform&sa=upload&o=lxbackup&l[class]=ffile&l[nname]=/") ?></a><br>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=lxbackup") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&frm_o_o[0][class]=lxbackup"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $clientquery ?>frm_o_o[1][class]=lxbackup"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=ftp_conf&o=lxbackup") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=ftp_conf&frm_o_o[0][class]=lxbackup"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=ftp_conf&<?= $clientquery ?>frm_o_o[1][class]=lxbackup"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=schedule_conf&o=lxbackup") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=schedule_conf&frm_o_o[0][class]=lxbackup"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=schedule_conf&<?= $clientquery ?>frm_o_o[1][class]=lxbackup"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=lxbackup&l[class]=ffile&l[nname]=/") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&frm_o_o[0][class]=lxbackup&frm_o_o[1][class]=ffile&frm_o_o[1][nname]=/"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $clientquery ?>frm_o_o[1][class]=lxbackup&frm_o_o[2][class]=ffile&frm_o_o[2][nname]=/"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=upload&o=lxbackup&l[class]=ffile&l[nname]=/") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=upload&frm_o_o[0][class]=lxbackup&frm_o_o[1][class]=ffile&frm_o_o[1][nname]=/"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=upload&<?= $clientquery ?>frm_o_o[1][class]=lxbackup&frm_o_o[2][class]=ffile&frm_o_o[2][nname]=/"><?= $clientid ?></a><br/>

<?php
	}
?>
					</div>
<?php

	if (!$login->isAdmin()) {
?>
				</div>
				<div class='left'>
<?php
	}
?>
					<b><?= $login->getKeywordUc('other') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&frm_o_o[0][class]=domaindefault"><?= $ghtml->getTitleOnly("a=updateform&sa=update&o=domaindefault") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=ftpsession"><?= $ghtml->getTitleOnly("a=list&c=ftpsession") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=shell_access"><?= $ghtml->getTitleOnly("a=updateform&sa=shell_access&o=client") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=update&o=domaindefault") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&frm_o_o[0][class]=domaindefault"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&<?= $clientquery ?>frm_o_o[1][class]=domaindefault"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=ftpsession") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=ftpsession"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=ftpsession"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=shell_access&o=client") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=shell_access"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=shell_access&<?= $clientquery ?>"><?= $clientid ?></a><br/>

<?php
	}
?>
					</div>
				</div>
				<div style='clear: both;'></div>
			</div>
		</li>
		<li class="separator"></li>
		<li><a href="javascript://"><?= $login->getKeywordUc('advanced') ?></a>
			<div class="drop2 decor2_2" style="width:<?= $genwidth ?>px">
				<div class='left rightborder'>
					<b><?= $login->getKeywordUc('general') ?></b>
					<div class="dropmenu">
<?php
	if ($login->isAdmin()) {
?>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=updateform&frm_subaction=scavengetime&frm_o_o[0][class]=general"><?= $ghtml->getTitleOnly("a=updateform&sa=scavengetime&o=general") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=updateform&frm_subaction=generalsetting&frm_o_o[0][class]=general"><?= $ghtml->getTitleOnly("a=updateform&sa=generalsetting&o=general") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=updateform&frm_subaction=selfbackupconfig&frm_o_o[0][class]=general"><?= $ghtml->getTitleOnly("a=updateform&sa=selfbackupconfig&o=general") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=list&frm_o_o[0][class]=genlist&frm_o_cname=dirindexlist_a"><?= $ghtml->getTitleOnly("a=list&o=genlist&c=dirindexlist_a") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=updateform&frm_subaction=portconfig&frm_o_o[0][class]=general"><?= $ghtml->getTitleOnly("a=updateform&sa=portconfig&o=general") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=updateform&frm_subaction=disable_skeleton"><?= $ghtml->getTitleOnly("a=updateform&sa=disable_skeleton&c=client") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?frm_action=updateform&frm_subaction=upload_logo&frm_o_o[0][class]=sp_specialplay"><?= $ghtml->getTitleOnly("a=updateform&sa=upload_logo&o=sp_specialplay") ?></a><br/>
<?php
	}
?>
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&frm_o_o[0][class]=notification"><?= $ghtml->getTitleOnly("a=show&o=notification") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=login_options&frm_o_o[0][class]=sp_specialplay"><?= $ghtml->getTitleOnly("a=updateform&sa=login_options&o=sp_specialplay") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=notification") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&frm_o_o[0][class]=notification"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $clientquery ?>frm_o_o[1][class]=notification"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=login_options&o=sp_specialplay") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_o_o[0][class]=sp_specialplay"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&<?= $clientquery ?>frm_o_o[1][class]=sp_specialplay"><?= $clientid ?></a><br/>
<?php
	}
?>
					</div>
<?php
	if (!$login->isCustomer()) {
?>
				</div>
				<div class='left'>
<?php
	}
?>
					<b><?= $login->getKeywordUc('blockedallowedips') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=allowedip"><?= $ghtml->getTitleOnly("a=list&c=allowedip") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=blockedip"><?= $ghtml->getTitleOnly("a=list&c=blockedip") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=allowedip") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=allowedip"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientid ?>frm_o_cname=allowedip"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=blockedip") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=blockedip"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientid ?>frm_o_cname=blockedip"><?= $clientid ?></a><br/>

<?php
	}
?>
					</div>
<?php
	if ($login->isAdmin()) {
?>
					<b><?= $login->getKeywordUc('maintenance') ?></b>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=maintenance&frm_o_o[0][class]=general"><?= $ghtml->getTitleOnly("a=updateform&sa=maintenance&o=general") ?></a><br/>

					</div>
<?php
	}

	if ($login->isCustomer()) {
?>
				</div>
				<div class='left'>
<?php
	}
?>
					<b><?= $login->getKeywordUc('other') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=miscinfo"><?= $ghtml->getTitleOnly("a=updateform&sa=miscinfo&o=pserver") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=miscinfo&o=pserver") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=miscinfo"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&<?= $clientquery ?>frm_subaction=miscinfo"><?= $clientid ?></a><br/>
<?php
	}

	if ($login->isAdmin()) {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=skin&frm_o_o[0][class]=sp_childspecialplay"><?= $ghtml->getTitleOnly("a=updateform&sa=skin&o=sp_childspecialplay") ?></a><br/>
<?php
	}
?>
					</div>
				</div>
				<div style='clear: both;'></div>
			</div>
		</li>
<?php
	if ($login->isAdmin()) {
?>
		<li class="separator"></li>
		<li><a href="javascript://"><?= $login->getKeywordUc('server') ?></a>
			<div class="drop decor2_2" style="width:<?= $genwidth ?>px">
				<div class='left rightborder'>
					<b><?= $login->getKeywordUc('server') ?></b>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=pserver"><?= $ghtml->getTitleOnly("a=list&c=pserver") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=forcedeletepserver&frm_o_cname=client"><?= $ghtml->getTitleOnly("a=updateform&sa=forcedeletepserver&c=client") ?></a><br/>
<?php
		if (check_if_many_server()) {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=psrole_a"><?= $ghtml->getTitleOnly("a=list&c=psrole_a") ?></a><br/>
<?php
		}

		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=reboot&l[class]=pserver&l[nname]={$syncserver}") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=reboot&<?= $localhostquery ?>">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=reboot&<?= $serverquery ?>"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=reboot&l[class]=pserver&l[nname]={$syncserver}") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=poweroff&<?= $localhostquery ?>">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=poweroff&<?= $serverquery ?>"><?= $syncserver ?></a><br/>
<?php
		} else {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=reboot&<?= $serverquery ?>"><?= $ghtml->getTitleOnly("a=updateform&sa=reboot&l[class]=pserver&l[nname]={$syncserver}") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=poweroff&<?= $serverquery ?>"><?= $ghtml->getTitleOnly("a=updateform&sa=poweroff&l[class]=pserver&l[nname]={$syncserver}") ?></a><br/>
<?php
		}
?>
					</div>
					<b><?= $login->getKeywordUc('show') ?></b>
					<div class="dropmenu">
<?php
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=service") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_cname=service">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=service"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=process") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_cname=process">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=process"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=component") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_cname=component">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=component"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=llog") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=llog">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=llog"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=update&o=driver") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&<?= $localhostquery ?>frm_o_o[1][class]=driver">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&<?= $serverquery ?>frm_o_o[1][class]=driver"><?= $syncserver ?></a><br/>


<?php
		} else {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=service"><?= $ghtml->getTitleOnly("a=list&c=service") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=process"><?= $ghtml->getTitleOnly("a=list&c=process") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=component"><?= $ghtml->getTitleOnly("a=list&c=component") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=llog"><?= $ghtml->getTitleOnly("a=show&o=llog") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&<?= $serverquery ?>frm_o_o[1][class]=driver"><?= $ghtml->getTitleOnly("a=updateform&sa=update&o=driver") ?></a><br/>
<?php
		}
?>
					</div>
				</div>
				<div class='left'>
					<b><?= $login->getKeywordUc('other') ?></b>
					<div class="dropmenu">
<?php
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=switchprogram&l[class]=pserver&l[nname]={$syncserver}") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=switchprogram&<?= $localhostquery ?>">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=switchprogram&<?= $serverquery ?>"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=timezone&l[class]=pserver&l[nname]={$syncserver}") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=timezone&<?= $localhostquery ?>">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=timezone&<?= $serverquery ?>"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=commandcenter") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=commandcenter&<?= $localhostquery ?>">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=commandcenter&<?= $serverquery ?>"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=sshclient") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=sshclient">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=sshclient"><?= $syncserver ?></a><br/>

<?php
		} else {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=switchprogram&<?= $serverquery ?>"><?= $ghtml->getTitleOnly("a=updateform&sa=switchprogram&l[class]=pserver&l[nname]={$syncserver}") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=timezone&<?= $serverquery ?>"><?= $ghtml->getTitleOnly("a=updateform&sa=timezone&l[class]=pserver&l[nname]={$syncserver}") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=commandcenter&<?= $serverquery ?>"><?= $ghtml->getTitleOnly("a=updateform&sa=commandcenter") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=sshclient"><?= $ghtml->getTitleOnly("a=show&o=sshclient") ?></a><br/>
<?php
		}

		if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=traceroute"><?= $ghtml->getTitleOnly("a=list&c=traceroute") ?></a><br/>
<?php
		} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=traceroute") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=traceroute"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=traceroute"><?= $clientid ?></a><br/>
<?php
		}
?>					</div>
				</div>
				<div style='clear: both;'></div>
			</div>
		</li>
		<li class="separator"></li>
		<li><a href="javascript://"><?= $login->getKeywordUc('security') ?></a>
			<div class="drop decor2_2" style="width:<?= $genwidth ?>px">
				<div class='left rightborder'>
					<b><?= $login->getKeywordUc('lxguard') ?></b>
					<div class="dropmenu">
<?php
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=lxguard") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=lxguard">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=lxguard"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&o=lxguard&c=lxguardhitdisplay") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=lxguardhitdisplay">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=lxguardhitdisplay"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&o=lxguard&c=rawlxguardhit") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=rawlxguardhit">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=rawlxguardhit"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&o=lxguard&c=lxguardwhitelist") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=lxguardwhitelist">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=lxguardwhitelist"><?= $syncserver ?></a><br/>

<?php
		} else {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=lxguard"><?= $ghtml->getTitleOnly("a=show&o=lxguard") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=lxguardhitdisplay"><?= $ghtml->getTitleOnly("a=list&o=lxguard&c=lxguardhitdisplay") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=rawlxguardhit"><?= $ghtml->getTitleOnly("a=list&o=lxguard&c=rawlxguardhit") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_o[1][class]=lxguard&frm_o_cname=lxguardwhitelist"><?= $ghtml->getTitleOnly("a=list&o=lxguard&c=lxguardwhitelist") ?></a><br/>
<?php
		}
?>					</div>
				</div>
				<div class='left'>
					<b><?= $login->getKeywordUc('other') ?></b>
					<div class="dropmenu">
<?php
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=sshconfig") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=sshconfig">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=sshconfig"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=watchdog") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_cname=watchdog">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=watchdog"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=hostdeny") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_cname=hostdeny">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=hostdeny"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=sshauthorizedkey") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_cname=sshauthorizedkey">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=sshauthorizedkey"><?= $syncserver ?></a><br/>
						<!-- &#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=jailed") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=jailed">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=jailed"><?= $syncserver ?></a><br/> -->
<?php
		} else {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=sshconfig"><?= $ghtml->getTitleOnly("a=show&o=sshconfig") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=watchdog"><?= $ghtml->getTitleOnly("a=list&c=watchdog") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=hostdeny"><?= $ghtml->getTitleOnly("a=list&c=hostdeny") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=sshauthorizedkey"><?= $ghtml->getTitleOnly("a=list&c=sshauthorizedkey") ?></a><br/>
						<!-- &#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=jailed"><?= $ghtml->getTitleOnly("a=show&o=jailed") ?></a><br/> -->
<?php
		}
?>
					</div>
				</div>
				<div style='clear: both;'></div>
			</div>
		</li>
<?php
	}

	if ($login->isAdmin()) {
?>
		<li class="separator"></li>
		<li><a href="javascript://"><?= $login->getKeywordUc('webmailanddb') ?></a>
			<div class="drop decor2_2" style="width:<?= $genwidth ?>px">
				<div class='left rightborder'>
					<b><?= $login->getKeywordUc('configure') ?></b>
					<div class="dropmenu">
<?php
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=phpini") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=phpini">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=phpini"><?= $syncserver ?></a>
						<!-- &#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=phpini"><?= $clientid ?></a>--><br/>
						<!-- &#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=extraedit&o=phpini") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=extraedit&<?= $localhostquery ?>frm_o_o[1][class]=phpini">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=extraedit&<?= $serverquery ?>frm_o_o[1][class]=phpini"><?= $syncserver ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=extraedit&<?= $serverquery ?>&<?= $clientquery ?>frm_o_o[1][class]=phpini"><?= $clientid ?></a><br/> -->
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=serverweb") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=serverweb">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=serverweb"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=serverftp") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=serverftp">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=serverftp"><?= $syncserver ?></a><br/>

<?php
		} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=phpini") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=phpini">localhost</a>
						<!-- &#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>&<?= $clientquery ?>frm_o_o[1][class]=phpini"><?= $clientid ?></a>--><br/>
						<!-- &#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=extraedit&o=phpini") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=extraedit&<?= $localhostquery ?>frm_o_o[1][class]=phpini">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=extraedit&<?= $serverquery ?>&<?= $clientquery ?>frm_o_o[1][class]=phpini"><?= $clientid ?></a><br/> -->

						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=serverweb"><?= $ghtml->getTitleOnly("a=show&o=serverweb") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=serverftp"><?= $ghtml->getTitleOnly("a=show&o=serverftp") ?></a><br/>
<?php
		}
?>
					</div>
					<b><?= $login->getKeywordUc('database') ?></b>
					<div class="dropmenu">
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=dbadmin"><?= $ghtml->getTitleOnly("a=list&c=dbadmin") ?></a><br/>
<?php
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=mysqlpasswordreset&o=pserver") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=mysqlpasswordreset&<?= $localhostquery ?>">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=mysqlpasswordreset&<?= $serverquery ?>"><?= $syncserver ?></a><br/>

<?php
		} else {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=mysqlpasswordreset&<?= $serverquery ?>"><?= $ghtml->getTitleOnly("a=updateform&sa=mysqlpasswordreset&o=pserver") ?></a><br/>
<?php
		}
?>
					</div>
				</div>
				<div class='left'>
					<b><?= $login->getKeywordUc('mail') ?></b>
					<div class="dropmenu">
<?php
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=update&o=servermail") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&<?= $localhostquery ?>frm_o_o[1][class]=servermail">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&<?= $serverquery ?>frm_o_o[1][class]=servermail"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=update&o=spamdyke") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=spamdyke&<?= $localhostquery ?>frm_o_o[1][class]=servermail">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=spamdyke&<?= $serverquery ?>frm_o_o[1][class]=servermail"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=mail_graylist_wlist_a") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_o[1][class]=servermail&frm_o_cname=mail_graylist_wlist_a">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_o[1][class]=servermail&frm_o_cname=mail_graylist_wlist_a"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=mailqueue") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_cname=mailqueue">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=mailqueue"><?= $syncserver ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=clientmail") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $localhostquery ?>frm_o_cname=clientmail">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=clientmail"><?= $syncserver ?></a><br/>

<?php
		} else {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=update&<?= $serverquery ?>frm_o_o[1][class]=servermail"><?= $ghtml->getTitleOnly("a=updateform&sa=update&o=servermail") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=spamdyke&<?= $serverquery ?>frm_o_o[1][class]=servermail"><?= $ghtml->getTitleOnly("a=updateform&sa=spamdyke&o=servermail") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_o[1][class]=servermail&frm_o_cname=mail_graylist_wlist_a"><?= $ghtml->getTitleOnly("a=list&c=mail_graylist_wlist_a") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=mailqueue"><?= $ghtml->getTitleOnly("a=list&c=mailqueue") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $serverquery ?>frm_o_cname=clientmail"><?= $ghtml->getTitleOnly("a=list&c=clientmail") ?></a><br/>
<?php
		}
?>
					</div>
				</div>
				<div style='clear: both;'></div>
			</div>
		</li>
<?php
	}
?>
		<li class="separator"></li>
		<li><a href="javascript://"><?= $login->getKeywordUc('task') ?></a>
			<div class="drop decor2_2" style="width:<?= $genwidth ?>px">
				<div class='left rightborder'>
					<b><?= $login->getKeywordUc('domain') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=domain"><?= $ghtml->getTitleOnly("a=list&c=domain") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=subdomain"><?= $ghtml->getTitleOnly("a=list&c=subdomain") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=addondomain"><?= $ghtml->getTitleOnly("a=list&c=addondomain") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=default_domain&<?= $clientquery ?>"><?= $ghtml->getTitleOnly("a=updateform&sa=default_domain&o=client") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=domain") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=domain"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=domain"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=subdomain") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=subdomain"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=subdomain"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=addondomain") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=addondomain"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=addondomain"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=updateform&sa=default_domain&o=client") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=default_domain"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=updateform&frm_subaction=default_domain&<?= $clientquery ?>"><?= $clientid ?></a><br/>

<?php
	}
?>					</div>
					<b><?= $login->getKeywordUc('ftpmaildatabase') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=ftpuser"><?= $ghtml->getTitleOnly("a=list&c=ftpuser") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=mailaccount"><?= $ghtml->getTitleOnly("a=list&c=mailaccount") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=mysqldb"><?= $ghtml->getTitleOnly("a=list&c=mysqldb") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=ftpuser") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=ftpuser"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=ftpuser"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=mailaccount") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=mailaccount"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=mailaccount"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=mysqldb") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=mysqldb"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=mysqldb"><?= $clientid ?></a><br/>
<?php
	}
?>					</div>
					<b><?= $login->getKeywordUc('filemanager') ?></b>
					<div class="dropmenu">
<?php
	if ($login->isAdmin()) {
		if ((check_if_many_server()) && ($syncserver !== 'localhost')) {	
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=ffile") ?>

							&#x00bb;&nbsp;<a href="/display.php?frm_action=show&<?= $localhostquery ?>frm_o_o[1][class]=ffile&frm_o_o[1][nname]=">localhost</a>
							&#x00bb;&nbsp;<a href="/display.php?frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=ffile&frm_o_o[1][nname]="><?= $syncserver ?></a><br/>

<?php
		} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=ffile") ?>&nbsp;&#x00bb;&nbsp;<a href="/display.php?frm_action=show&<?= $serverquery ?>frm_o_o[1][class]=ffile&frm_o_o[1][nname]="><?= $syncserver ?></a><br/>
<?php
		}
	}
?>

<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=ffile") ?>&nbsp;&#x00bb;&nbsp;<a href="/display.php?frm_action=show&frm_o_o[0][class]=ffile&frm_o_o[0][nname]=/"><?= $clientid ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=show&o=ffile") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&frm_o_o[0][class]=ffile&frm_o_o[1][nname]=/"><?= $loginas ?></a></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=show&<?= $clientquery ?>frm_o_o[1][class]=ffile&frm_o_o[1][nname]=/"><?= $clientid ?></a></a>
<?php
	}
?>
					</div>
				</div>
				<div class='left'>
					<b><?= $login->getKeywordUc('crontask') ?></b>
					<div class="dropmenu">
<?php
	if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=cron"><?= $ghtml->getTitleOnly("a=list&c=cron") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&frm_dttype[var]=ttype&frm_dttype[val]=simple&frm_o_cname=cron"><?= $ghtml->getTitleOnly("a=addform&c=cron&dta[var]=ttype&dta[val]=simple") ?></a><br/>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&frm_dttype[var]=ttype&frm_dttype[val]=complex&frm_o_cname=cron"><?= $ghtml->getTitleOnly("a=addform&c=cron&dta[var]=ttype&dta[val]=complex") ?></a><br/>
<?php
	} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=cron") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=cron"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=cron"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=addform&c=cron&dta[var]=ttype&dta[val]=simple") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&frm_dttype[var]=ttype&frm_dttype[val]=simple&frm_o_cname=cron"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&<?= $clientquery ?>frm_dttype[var]=ttype&frm_dttype[val]=simple&frm_o_cname=cron"><?= $clientid ?></a><br/>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=addform&c=cron&dta[var]=ttype&dta[val]=complex") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&frm_dttype[var]=ttype&frm_dttype[val]=complex&frm_o_cname=cron"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&<?= $clientquery ?>frm_dttype[var]=ttype&frm_dttype[val]=complex&frm_o_cname=cron"><?= $clientid ?></a><br/>

<?php
	}
?>
					</div>
<?php
	if (!$login->isCustomer()) {
?>
					<b><?= $login->getKeywordUc('client') ?></b>
					<div class="dropmenu">
<?php
		if ($login->isAdmin()) {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=all_client"><?= $ghtml->getTitleOnly("a=list&c=all_client") ?></a><br/>
<?php
		}

		if ($clientquery === "") {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=client"><?= $ghtml->getTitleOnly("a=list&c=client") ?></a><br/>
<?php
		} else {
?>
						&#x00bb;&nbsp;<?= $ghtml->getTitleOnly("a=list&c=client") ?>

							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&frm_o_cname=client"><?= $loginas ?></a>
							&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=list&<?= $clientquery ?>frm_o_cname=client"><?= $clientid ?></a><br/>

<?php
		}

		if ($login->isAdmin()) {
			if (check_if_many_server()) {
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&frm_dttype[var]=cttype&frm_dttype[val]=wholesale&frm_o_cname=client"><?= $ghtml->getTitleOnly("a=addform&dta[var]=cttype&dta[val]=wholesale&c=client") ?></a><br/>
<?php
			}
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&frm_dttype[var]=cttype&frm_dttype[val]=reseller&frm_o_cname=client"><?= $ghtml->getTitleOnly("a=addform&dta[var]=cttype&dta[val]=reseller&c=client") ?></a><br/>
<?php
		}
?>
						&#x00bb;&nbsp;<a href="/display.php?<?= $consumedlogin ?>frm_action=addform&frm_dttype[var]=cttype&frm_dttype[val]=customer&frm_o_cname=client"><?= $ghtml->getTitleOnly("a=addform&dta[var]=cttype&dta[val]=customer&c=client") ?></a><br/>
					</div>
				</div>
<?php
	}
?>
				<div style='clear: both;'></div>
			</div>
		</li>
	</ul>
</div>
<?php
}
?>
<div style="float:right">
	<ul class="menuTemplate2 decor2_1">
		<li><a title="<?= $login->getKeywordUc('click_here_for') ?> <?= $login->getKeywordUc('help') ?>" href="javascript://" onClick="toggleVisibilityById('infomsg');"><?= $login->getKeywordUc('help') ?></a></li>
		<li class="separator"></li>
		<li><a title="<?= $login->getKeywordUc('click_here_for') ?> <?= $login->getKeywordUc('logout') ?>"  href="javascript:if (confirm('<?= $login->getKeywordUc('is_want_logout') ?>')) { location = '/lib/php/logout.php'; }"><?= $login->getKeywordUc('logout') ?></a></li>
	</ul>
</div>
</div>
<!-- 'END - Simplicity menu' -->


