<?php

ignore_user_abort(true);

include_once "lib/html/displayinclude.php";
// include_once "lib/html/include.php";

function __ac_desc_desktop($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$skindir = $login->getSkinDir();
	$col = $login->getSkinColor();

	$imgpath = get_image_path();

	$sel = "des";
	$a = $ghtml->print_domcollapse($sel);
	$history = $login->dskhistory;
	$history = array_reverse($history);
	$shortcut = $login->dskshortcut_a;
	$shediturl = $ghtml->getFullUrl('a=list&c=dskshortcut_a');
	$ghtml->print_message();

?>

	<table cellpadding="0" width="90%" cellspacing="1" style='border:1px solid #<?=$col?>; background:#fffafa;'>
		<tr></tr>
		<tr height="50">
			<td align="center">
				<form name="desktopsearch" method="get" action="/display.php" accept-charset="utf-8">
<?php

					$ghtml->print_input("hidden", "frm_action", "desktop");

?>

					<table cellpadding="0" cellspacing="0">
						<tr>
							<td>
<?php
								$ghtml->print_input("text", "frm_desksearch", $ghtml->frm_desksearch);
?>

				</form>
			</td>
			<td><a href="javascript:document.desktopsearch.submit()"> <img src="theme/general/icon/search_b.gif">
					Search </a> </td>
		</tr>
	</table>

	<table width="90%" cellpadding="0" valign="top" cellspacing="20" height="200">
		<tr>
			<td valign=top>
<?php
				$iconpath = get_image_path();
?>

				<div id="debug"></div>
				<div id="boundary">
					<div id="content">
						<h2 class="expanded" align="center" onMouseover="this.style.background='url(<?=$skindir?>/onexpand.gif)'" onMouseout="this.style.background='url(<?=$skindir?>/expand.gif)'"><code>History</code>
						</h2>
						<table cellpadding="0" cellspacing="0" style="background:#f0f0f0;">
<?php
							$count = 0;

							foreach ($history as $k => $h) {
								if ($h == 1) {
									$h = null;
								} else {
									$h = lxgettimewithoutyear($h);
								}

								$count++;
								$ac_descr = $ghtml->getActionDetails($k, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
								$des = "$ac_descr[2] for $__t_identity ($h)";
?>

								<tr valign="center" style="background:#f0f0f0;height:25px" onMouseover="this.style.background='url(<?=$skindir?>/a.gif)'" onMouseout="this.style.background='url(<?=$skindir?>/b.gif)'">
									<td style="padding:0 0 0 20px;" nowrap><a href="<?=$k?>"><img src="<?=$_t_image?>" width="20" height="20"></td>
									<td width="100%" nowrap><a href="<?=$k?>">&nbsp; <?=$des?></td>
								</tr>
								<tr style="background:#ffffff;">
									<td></td>
									<td></td>
								</tr>
<?php
							}
?>

						</table>
			</td>
			</div></div>
		</tr>
	</table>
<?php
}

function __ac_desc_logout($object)
{
	include "lib/php/logout.php";
}

function __ac_desc_updateshow($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$subaction = $ghtml->frm_subaction;
	$post = $ghtml->__http_vars;

	$class = $object->getClass();

	$param = $ghtml->createCurrentParam($class);
	$post['frm_action'] = 'updateform';

	$url = $ghtml->get_get_from_post(null, $post);
	$url = "/display.php?$url";

	$ret = $object->updateShow($subaction, $param);

	$out = $ret['out'];

	if (isset($ret['url'])) {
		$url = $ret['url'];
	}

?>

	<table cellpadding="0" cellspacing="0" width="90%">
		<tr>
			<td> <?=$out?> </td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<td> [<a href="<?=$url?>"> Go Back </a>]</td>
		</tr>
	</table>
<?php
}

function print_customer_mode($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$skin_color = $login->getSkinColor();

	$url = $ghtml->getFullUrl('a=update&sa=customermode');

	if ($object->isDomainOwnerMode()) {
		$mode = ucfirst($object->cttype);
	} else {
		$mode = $login->getKeywordUc('domainowner');
	}

?>

	<div style="background: #<?=$skin_color?> url(<?=$col?>); padding: 4px; margin: 0 25px; text-align: center">
		<a href="<?=$url?>">&nbsp;>>>> <?= $login->getKeywordUc('switchto') ?> <?=$mode?> <?= $login->getKeywordUc('mode') ?> <<<<&nbsp;</a>
	</div>
<?php
}

function __ac_desc_show($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	print_time("show_select");

	if (!$object) {
		return;
	}

	$selflist = $object->getSelfList();

	$class = lget_class($object);
	$subaction = $ghtml->frm_subaction;

	if (!$login->isAdmin()) {
	//	check_for_license();
	}

	$object->getAnyErrorMessage();

	$object->createShowPropertyList($prlist);

	if (!$prlist['property']) {
		$object->getParentO()->createShowPropertyList($prlist);

		foreach ($prlist['property'] as $k => $v) {
			$prlist['property'][$k] = "goback=1&$v";
		}
	}

	$ghtml->print_tab_block($prlist['property']);

	$ghtml->print_content_begin();

	if ($sgbl->isKloxo() && $object->isLogin() && $object->isLte('reseller')) {
		// MR -- TODO: disable switch from domain to admin mode; make confuse for dev
	//	print_customer_mode($object);

		// MR -- for temporary until switch to 'user' mode
		if ($object->isDomainOwnerMode()) {
			print_customer_mode($object);
		}
	}

	if ($selflist) {
		$ghtml->printShowSelectBox($selflist);
	}

	$printed_message = false;

	$clist = $object->createShowClist($subaction);

	$sclist = $object->createShowSclist();

	if ($sclist) {
		do_select_list($object, $sclist);
	}

	$ghtml->print_message();

	$cname = $object->getClass();
	$extra = $object->getExtraId();

	$ghtml->print_information('pre', 'show', $cname, $extra, "");

	$ilist = $object->createShowInfolist($subaction);
	$rlist = $object->createShowRlist($subaction);
	$plist = $object->createShowPlist($subaction);

	$progname = $sgbl->__var_program_name;

	if (!$subaction || $subaction === 'config') {
		$class = $object->get__table();
		$var = "{$class}_show_list";
		$interf = null;
		$object->createShowAlist($showalist);
		$object->createShowAlistConfig($advanced);

		if (isset($showalist['__v_message'])) {
?>

			<table cellpadding="16" width="100%" cellspacing="0" border="0">
				<tr>
					<td align="center">
						<table cellpadding="4" width="80%" cellspacing="13" border="1" height="80">
							<tr>
								<td>&nbsp;&nbsp;<img src="theme/general/button/warningpic.gif">&nbsp;<?=$showalist['__v_message']?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
<?php
			unset($showalist['__v_message']);
			$printed_message = true;

			if (isset($showalist['__v_refresh'])) {
				unset($showalist['__v_refresh']);
			}
		}

		$total = lx_merge_good($showalist, $advanced);

		if ($interf) {
			foreach ($interf as $k => $v) {
				if (csb($v, "__title")) {
					$interf[$k] = $v;
				} else {
					$interf[$k] = base64_decode($v);
				}
			}
		}

		if (!$subaction) {
			if ($interf) {
				foreach ($interf as $k => $i) {
					if (csb($i, "__title")) {
						$nnalist[$i] = $total[$i];
						continue;
					}

					$res = search_url_in_array($i, $total);

					if ($res !== false) {
						$nnalist[$res] = $total[$res];
					}
				}
			} else {
				$nnalist = $showalist;
			}
		} else if ($subaction === 'config') {
			if ($interf) {
				foreach ($total as $k => $a) {
					if ($ghtml->is_special_variable($a)) {
						$u = $a->purl;
					} else {
						$u = $a;
					}

					if (csb($k, "__title")) {
						continue;
					}

					$res = search_url_in_array($u, $interf);

					if ($res === false) {
						$nnalist[$k] = $total[$k];
					}
				}

				$nnalist = lx_merge_good(array('__title_advanced' => 'advanced'), $nnalist);
			} else {
				$nnalist = $advanced;
			}
		}

		$aalist = $nnalist;
	}

	if (!$subaction && !$printed_message) {
		$object->createShowActionList($acalist);

		if ($acalist) {
			array_splice($aalist, 1, 0, $acalist);
		}
	}

	$nalist = null;

	if ($aalist) {
		// We need the title to be the first one. Or else the insides wont' work.
		$gottitle = false;

		foreach ($aalist as $k => $a) {
			if (csb($k, "__title")) {
				$gottitle = true;
				$nalist[$k] = $a;

				break;
			}
		}

		if (!$gottitle) {
			$nalist['__title_resource'] = 'General_Get__Title_Error';
		}

		foreach ((array)$aalist as $k => $a) {
			if (csb($k, "__title")) {
				if (!$a) {
					$a = 'General_get_title_error';
				}

				$nalist[$k] = $a;
				continue;
			}

			if (!is_array($a)) {
				$nalist[$k] = $ghtml->getFullUrl($a);
			}
		}
	}

	if (($rlist || $plist || $ilist) && !$printed_message) {
?>

	<div>
		<div style="background-color: #fff; margin: 10px auto; height: 100%; width: 900px">
			<div style="float:left; height: 100%">
<?php
			$ghtml->print_find($object);

			if ($ilist) {
				$ghtml->printObjectTable(null, $object, 'information');
			}

			if ($object->createShowNote()) {
				$ghtml->print_note($object);
			}

			if ($rlist) {
				$ghtml->printObjectTable(null, $object, 'resource');
			}

			if ($plist) {
				$ghtml->printObjectTable(null, $object, 'permission');
			}
?>

		</div>

<?php
		if (isset($nalist)) {
			$ghtml->print_object_action_block($object, $nalist, 8);
		}

	} else {
		if (isset($nalist)) {
			$ghtml->print_object_action_block($object, $nalist, 8);
?>

			<br/>
<?php
		}
	}

	$object->showRawPrint($subaction);

	$aflist = $object->createShowAddform();
	$uflist = $object->createShowUpdateform();

	foreach ((array)$uflist as $k => $v) {
		do_updateform($object, $k);
	}

	foreach ((array)$aflist as $k => $v) {
		do_addform($object, $k);
	}

	foreach ((array)$clist as $k => $v) {
		make_show_all($object, $k);
		do_list_class($object, $k);
	}

	$ghtml->print_information('post', 'show', $cname, $subaction, "");

	if (($rlist || $plist || $ilist) && !$printed_message) {
?>
	
			</div>
		</div>
	</div>
<?php
	}

	$ghtml->print_content_end();
}

function search_url_in_array($url, $alist)
{
	global $gbl, $sgbl, $login, $ghtml;

	foreach ($alist as $k => $a) {
		if ($ghtml->is_special_variable($a)) {
			$u = $a->purl;
		} else {
			$u = $a;
		}

		if (csb($u, "__title")) {
			continue;
		}

		if (compare_action_urls($url, $u)) {
			return $k;
		}
	}

	return false;
}

function compare_action_urls($src, $dst)
{
	global $gbl, $sgbl, $login, $ghtml;

	$src = strtolower("display.php?$src");
	$dst = strtolower("display.php?$dst");
	$ghtml->get_post_from_get($src, $path, $srcpost);
	$ghtml->get_post_from_get($dst, $path, $dstpost);

	$arvar = array('a', 'o', 'n', 'c', 'l', 'sa');

	foreach ($arvar as $a) {
		if (!isset($srcpost[$a])) {
			$srcpost[$a] = null;
		}

		if (!isset($dstpost[$a])) {
			$dstpost[$a] = null;
		}
	}

	foreach ($arvar as $a) {
		if ($srcpost[$a] !== $dstpost[$a]) {
			return false;
		}
	}

	return true;
}

function __ac_desc_graph($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$subaction = $ghtml->frm_subaction;
	$selflist = $object->getSelfList();

	if (!$ghtml->frm_c_graph_time) {
		$ghtml->frm_c_graph_time = '1d';
	}

	$object->createShowPropertyList($alist);
	$object->createShowAlist($alist, $subaction);

	$nalist = null;

	if (!isset($alist['property'])) {
		$alist['property'] = array();
	}

	$nalist = lx_merge_good($nalist, $alist['property']);

	remove_if_older_than_a_minute_dir("__path_program_htmlbase/tmp/");

	$ghtml->print_tab_block($nalist);

	$ghtml->print_content_begin();

	if ($selflist) {
		$ghtml->printShowSelectBox($selflist);
	}
	if (cse($ghtml->frm_subaction, 'base')) {
		$core = strtil($ghtml->frm_subaction, "base");
		$ghtml->__http_vars['frm_subaction'] = "{$core}traffic";
		$subaction = "{$core}traffic";
	}

	$galist = $object->createGraphList();

	$ghtml->print_tab_block($galist);

	$graphtlist = array('1h' => '1h', '12h' => '12h', '1d' => '1d', '2d' => '2d', '1week' => '1week', '1month' => '1month');
	$gtlistsec = array('1h' => 3600, '12h' => 12 * 3600, '1d' => 24 * 3600, '2d' => 2 * 24 * 3600, '1week' => 7 * 24 * 3600, '1month' => 30 * 24 * 3600, '1year' => 365 * 24 * 3600);
	$ghtml->printGraphSelect($graphtlist);
	lxfile_mkdir("__path_program_htmlbase/tmp");
	$tmpgraph = ltempnam("__path_program_htmlbase/tmp/", "graph");

	$object->setUpdateSubaction("graph_$subaction");
	$time = $ghtml->frm_c_graph_time;

	$object->rrdtime = $gtlistsec[$time];

	try {
		$object->createExtraVariables();
		$file = rl_exec_set(null, $object->syncserver, $object);
	} catch (lxException $e) {
		$ghtml->print_curvy_table_start();
?>

		Graph Failed due to <?=$e->getMessage()?> <?=$e->value?>
<?php
		$ghtml->print_curvy_table_end();
		$object->dbaction = 'clean';

		return;
	}

	$object->dbaction = 'clean';

	lfile_put_contents($tmpgraph, $file);

	$tmpgraph = basename($tmpgraph);

?>

	<img src="/tmp/<?=$tmpgraph?>">
<?php
	$ghtml->print_content_end();
}

function showParentProperty($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$nalist[] = "a=show";
	$object->createShowPropertyList($nalist);
	$ghtml->print_tab_block($nalist);
}

function do_select_list($object, $sclist)
{
	global $gbl, $sgbl, $login, $ghtml;

	$class = lget_class($object);
	$desc = get_classvar_description($class);

	foreach ($sclist as $k => $s) {
		$cg = $ghtml->frm_o_o;
		$n = count($cg);
		$cg[$n]['frm_o_o']['class'] = $k;
		$string[] = $ghtml->object_variable_startblock($object, $class, "{$desc[2]}");
		$string[] = $ghtml->object_inherit_classpath();
		$string[] = $ghtml->object_variable_hidden("frm_o_o[$n][class]", $k);
		$string[] = $ghtml->object_variable_hidden("frm_action", 'show');
		$string[] = $ghtml->object_variable_show_select($object, "frm_o_o[$n][nname]", $s);
		$string[] = $ghtml->object_variable_button('Show');
		$ghtml->xml_print_page($string);
	}
}

function __ac_desc_delete($object)
{
	global $gbl, $sgbl, $login, $ghtml;
	global $g_language_mes;

	$cname = $ghtml->frm_o_cname;

	$ghtml->print_message();

	$ll = explode(',', $ghtml->frm_accountselect);

	if ($ghtml->frm_confirmed === "yes") {
		do_desc_delete($object, $cname, $ll);
		$desc = get_classvar_description($cname);
		$gbl->__this_redirect = $gbl->getSessionV("lx_delete_return_url") . "&frm_smessage=$desc[2]+successfully+deleted.";

		if ($cname === 'domain' || $cname === 'client' || $cname === 'vps') {
			$gbl->setSessionV('__refresh_lpanel', 'true');
		}
	} else {
		$gbl->setSessionV("lx_delete_return_url", $gbl->getHttpReferer());

		if ($login->isCustomer()) {
			$warning = $g_language_mes->__information['general_delete_warning_customer'];
		} else {
			$warning = $g_language_mes->__information['general_delete_warning'];
		}
?>

		<div class="div_mmm_inside">
			<div class="div_mmm_warning">
				<?=$warning?>
			</div>

<?php
	
		/*

			if (exec_class_method($cname, 'isTreeForDelete')) {
?>

				<div>These Objects Under these <?=get_plural($object->getClass())?> will also be Deleted.<br/> <br/></div>

<?php
				foreach ($ll as $l) {
					$o = $object->getFromList($cname, $l);
					$ghtml->do_resource(null, $o, 6, false, "getResourceChildList", true, false);
				}
?>
				<br/>
<?php
			}
		*/
			do_list_class($object, $cname);
?>
		</div>
<?php
	}
}

function __ac_desc_backup($object)
{
	global $gbl, $sgbl, $login, $ghtml;
}

function do_list_class($object, $cname)
{
	global $gbl, $sgbl, $login, $ghtml;

	$rclass = $cname;
	$blist = exec_class_method($rclass, "createListBlist", $object, $rclass);

	if ($blist) {
		foreach ($blist as $k => &$a) {
			if (is_numeric($k)) {
				$a[0] = $ghtml->getFullUrl($a[0]);
			}
		}
	}

	$ghtml->printObjectTable(null, $object, $cname, $blist);
	$ghtml->print_information('post', 'list', $cname, "", "");
}

function check_for_license()
{
	global $gbl, $sgbl, $login, $ghtml;

	$lic = $login->getObject('license')->licensecom_b;

	$prgm = $sgbl->__var_program_name;

	if ($prgm === 'lxlabsclient') {
		return;
	}

	$list = get_admin_license_var();

	foreach ($list as $k => $l) {
		$res = strfrom($k, "used_q_");
		$licv = "lic_$res";

		if ($licv === "lic_maindomain_num" && !isset($lic->$licv)) {
			$lic->$licv = $lic->lic_domain_num;
		}

		if ($l > $lic->$licv) {
			if ($login->isAdmin()) {
				$mess = $ghtml->show_error_message("The system is not at present working because there is not enough license for $res. " .
					"Please go to [b]  admin home -> advanced -> license update [/b]  and click on [b] get license from lxlabs [/b]. " .
					"You will have to first create a valid license at client.lxlabs.com.");
			} else {
				$mess = $ghtml->show_error_message("The system is not at present working because there is not enough license for $res. " .
					"Please contact your administrator.");
			}

			exit;
		}
	}
}

function __ac_desc_list($object, $cname = null)
{
	global $gbl, $sgbl, $login, $ghtml;

	if (!$cname) {
		$cname = $ghtml->frm_o_cname;
	}

	$rclass = $cname;

	$selflist = $object->getSelfList();

//	check_for_license();
	$refresh = $ghtml->frm_list_refresh;

	if ($refresh === 'yes') {
		$object->clearList($cname);
	}

	$alist = exec_class_method($rclass, "createListAlist", $object, $cname);

	if ($alist) {
		$ghtml->print_tab_block($alist);
	}

	$ghtml->print_content_begin();

	if ($selflist) {
		$ghtml->printShowSelectBox($selflist);
	}

	$ghtml->print_message();

	$updatelist = exec_class_method($rclass, "createListUpdateForm", $object, $cname);

	if ($updatelist) {
		foreach ($updatelist as $u) {
			do_updateform($object, $u);
		}
	}

	if (exec_class_method($rclass, "createListAddForm", $object, $cname)) {
		do_addform($object, $cname);
	}

	make_show_all($object, $cname);

	$pre = $post = null;

	if (isset($vlist['__m_message_pre'])) {
		$pre = $vlist['__m_message_pre'];
	}

	if (isset($vlist['__m_message_post'])) {
		$post = $vlist['__m_message_post'];
	}

	$ghtml->print_information('pre', 'list', $cname, "", $pre);

	if (exec_class_method($cname, "addListForm", $object, $cname)) {
		$ghtml->printListAddForm($object, $cname);
	}

	do_search($object, $cname);

	do_list_class($object, $cname);

	$ghtml->print_content_end();
}

function make_show_all($object, $cname)
{
	global $gbl, $sgbl, $login, $ghtml;

	dprint($ghtml->frm_clear_filter);

	if ($ghtml->frm_clear_filter === 'true') {
		$name = $object->getFilterVariableForThis($cname);
		unset($ghtml->__http_vars['frm_clear_filter']);
		$login->hpfilterUnset($name);
		$login->setUpdateSubaction();
	}
}

function __ac_desc_selectShow($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$cnamelist = $object->createShowClist("");
	$cname = "ffile";

	foreach ($cnamelist as $k => $v) {
		$cname = $k;

		break;
	}

?>

	<br/>
	<div style="background-color:#eee">
		<br/>
<?php

	$object->showRawPrint();
	$nlist = exec_class_method($cname, 'createSelectListNlist', $object);
	$ghtml->printSelectObjectTable($nlist, $object, $cname, null);
?>
	</div>
<?php
}

function get_return_url($action)
{
	global $gbl, $sgbl, $login, $ghtml;

	$var = "lx_{$action}_return_url";

	$url = $gbl->getSessionV("lx_http_referer");

	return $url;
}

function __ac_desc_showform($object)
{
}

function __ac_desc_Update($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$subaction = $ghtml->frm_subaction;
	$class = $ghtml->frm_o_cname;

	$list = null;

	if ($ghtml->frm_accountselect) {
		$list = explode(",", $ghtml->frm_accountselect);
	}

	if (strtolower($ghtml->frm_change) === 'updateall') {
		$selflist = $object->getSelfList();

		foreach ($selflist as $l) {
			$ret = do_update($l, $subaction, null);
		}
	}

	if (!$class) {
		$ret = do_update($object, $subaction, $list);
	} else {
		$desc = get_classvar_description($class);

		if (csa($desc[0], "P")) {
			// Special object... UPdation Happens only to the parent and not to the select ed children. 
			// Example is the ffile class...

			$subaction = "{$class}_$subaction";

			$ret = do_update($object, $subaction, $list);
		} else {
			if (!$list) {
				print_die("List not set for Multiple Update");
			}

			foreach ($list as $l) {
				$ob = $object->getFromList($class, $l);
				$ret = do_update($ob, $subaction, null);
			}
		}
	}

	if (!isset($gbl->__this_redirect)) {
		if ($ret) {
			$gbl->__this_redirect = get_return_url("update") . 
				"&frm_smessage={$subaction}+successfully+updated+for+{$object->nname}";
		} else {
			$gbl->__this_redirect = get_return_url("update");
		}
	}

	return $ret;
}

function security_check($oldvlist, $param)
{
	global $login;

	foreach ((array)$oldvlist as $k => $v) {
		if (csb($k, "__v")) {
			continue;
		}

		if ($v && $v[0] === 'M') {
			continue;
		}

		// Php treats null variables as unset. So we need to forcibly create a value for the variables 
		// that has been defined in the updateform. Stupdi php.
		$tmpvlist[$k] = 'newval';
	}

	unset($param['_accountselect']);

	foreach ((array)$param as $k => $v) {
		if (csb($k, "priv_s_")) {
			$k = strfrom($k, "priv_s_");
		}

		if (csb($k, "listpriv_s_")) {
			$k = strfrom($k, "listpriv_s_");
		}

		if (!isset($tmpvlist[$k])) {
			throw new lxException($login->getThrow("trying_to_access_an_unsettable_variable"), '', $k);
		}
	}
}

function do_update($object, $subaction, $list)
{
	global $gbl, $sgbl, $login, $ghtml;

	$class = lget_class($object);
	$param = $ghtml->createCurrentParam($class);

	if ($list) {
		$param['_accountselect'] = $list;
	}

	$oldvlist = $object->updateform($subaction, $param);

	if ($class !== 'lxbackup') {
		security_check($oldvlist, $param);
	}

	return do_desc_update($object, $subaction, $param);
}

function do_search($object, $cname)
{
	global $gbl, $sgbl, $login, $ghtml;

	$rclass = $cname;
	$nlist = exec_class_method($rclass, "createListSlist", $object);

	if ($nlist) {
		$ghtml->printSearchTable($nlist, $object, $cname);
	}
}

function __ac_desc_UpdateForm($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	if (!$object) {
		return;
	}

	$selflist = $object->getSelfList();
	$subaction = $ghtml->frm_subaction;

/*
	$object->createShowPropertyList($alist);

	if (isset($alist['property']) && count($alist['property']) > 1) {
		$nalist = null;
		$nalist = lx_merge_good($nalist, $alist['property']);
	} elseif ($object->getParentO()) {
		$alist['property'] = null;
		$object->getParentO()->createShowPropertyList($alist);
		$nalist = null;
	
		// MR -- need if here because error in debug mode
		if (isset($alist['property'])) {
			foreach ($alist['property'] as &$a) {
				$a .= '&goback=1';
			}
		}

		$nalist = lx_merge_good($nalist, $alist['property']);
	}

	$ghtml->print_tab_block($nalist);
*/
	// MR -- use logic from show
	$object->createShowPropertyList($prlist);

	if (!$prlist['property']) {
		$object->getParentO()->createShowPropertyList($prlist);

		foreach ($prlist['property'] as $k => $v) {
			$prlist['property'][$k] = "goback=1&$v";
		}
	}

	$ghtml->print_tab_block($prlist['property']);

	$ghtml->print_content_begin();

	$object->showRawPrint();

	$ghtml->print_message();

	if ($selflist) {
		$ghtml->printShowSelectBox($selflist);
	}

	$sublist = $object->getMultiUpload($subaction);

	if (is_array($sublist)) {
		foreach ($sublist as $subaction) {
			do_updateform($object, $subaction);
?>

			<br/> <br/> <br/>
<?php
		}
	} else {
		do_updateform($object, $sublist);
	}

	$ghtml->print_content_end();
}

function do_updateform($object, $subaction)
{
	global $gbl, $sgbl, $login, $ghtml;

	$class = lget_class($object);
	$parent = $object->getParentO();
	$qparent = $parent;
	$_tsubaction = null;

	if ($subaction) {
		$_tsubaction = "_" . $subaction;

		$udesc = get_classvar_description($class, "__acdesc_update" . $_tsubaction);
		$title = null;

		if ($udesc) {
			$title = $udesc[2];
		}

		$gbl->setSessionV("lx_update_return_url", "/display.php?" . $ghtml->get_get_from_current_post(null));

		$param = $ghtml->createCurrentParam($class);

		if ($ghtml->frm_accountselect) {
			$list = explode(",", $ghtml->frm_accountselect);
			$param['_accountselect'] = $list;
		}

		$vlist = $object->updateform($subaction, $param);

		$tparam = null;

		if (isset($vlist['__v_param'])) {
			$tparam = $vlist['__v_param'];
		}

		if (isset($vlist['__v_childheir'])) {
			if ($vlist['__v_childheir']) {
				$var = $vlist['__v_childheir'];
				$o = $object->$var;
			} else {
				$o = $object;
			}

			/// Hack mega hack.. Adding tparam to the http_vars variable so that do_resource will get them.
			if ($tparam) {
				foreach ($tparam as $k => $v) {
					$param["frm_" . $class . "_c_" . $k] = $v;
					$ghtml->__http_vars["frm_{$class}_c_{$k}"] = $v;
				}
			}

			$ghtml->do_resource($gbl->__var_restore_tree, $o, 0, false, $vlist['__v_resourcefunc'], true, false);

			if ($vlist['__v_showcheckboxflag']) {
				print_time('full', "Page Generation Took");

				return;
			}
		}

		// Hack Hack Hack... Cannot handle file permissions neatly now... Just calling the whole thing..
		if (isset($vlist['file_permission_f'])) {
			$ghtml->print_file_permissions($object);

			return;
		}

		if (isset($vlist['file_ownership_f'])) {
			$ghtml->print_file_ownership($object);

			return;
		}

		$string[] = $ghtml->object_variable_startblock($object, null, $title);
		$string[] = $ghtml->object_inherit_classpath();
		$ret['variable'] = $vlist;

		if (isset($vlist['__v_next'])) {
			$ret['action'] = 'updateform';
			$ret['subaction'] = $vlist['__v_next'];
		} else {
			$ret['action'] = "update";
			$ret['subaction'] = $subaction;
		}

		$param = null;

		if ($tparam) {
			foreach ($tparam as $k => $v) {
				$param["frm_{$class}_c_$k"] = $v;
			}

			$string[] = $ghtml->object_variable_hiddenlist($param);
		}

		$string[] = create_xml($qparent, $object, $ret);

		$pre = $post = null;

		if (isset($vlist['__m_message_pre'])) {
			$pre = $vlist['__m_message_pre'];
		}

		if (isset($vlist['__m_message_post'])) {
			$post = $vlist['__m_message_post'];
		}

		$ghtml->print_information('pre', 'updateform', $class, $subaction, $pre);

		$ghtml->xml_print_page($string);

		$ghtml->print_information('post', 'updateform', $class, $subaction, $post);
	}
}

function __ac_desc_add($object, $param = null)
{
	global $gbl, $sgbl, $login, $ghtml;

	$class = $ghtml->frm_o_cname;

	if (!$param) {
		$param = $ghtml->createCurrentParam($class);
	}

	do_desc_add($object, $class, $param);

	if (!isset($gbl->__this_redirect)) {
		if (exec_class_method($class, "createListAlist", $object, $class)) {
			$gbl->__this_redirect = $ghtml->getFullUrl("a=list&c=$class");
		} else {
			$gbl->__this_redirect = $ghtml->getFullUrl("a=show");
		}
	}

	$descr = get_description($class);
	$gbl->__this_redirect .= "&frm_smessage=added_successfully&frm_m_smessage_data=$descr";

	if ($class === 'domain' || $class === 'client' || $class === 'vps') {
		$gbl->setSessionV('__refresh_lpanel', 'true');
	}
}

function check_for_select_one($param)
{
	global $gbl, $sgbl, $login, $ghtml;

	foreach ((array)$param as $k => $v) {
		if ($ghtml->isSelectOne($v)) {
			throw new lxException($login->getThrow("please_select_value"), '', $k);
		}
	}
}

function __ac_desc_continue($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$cname = $ghtml->frm_o_cname;

	$numvar = $cname . "_num";

	if ($object->isQuotaVariable($numvar)) {
		if (isQuotaGreaterThan($object->used->$numvar, $object->priv->$numvar)) {
			throw new lxException($login->getThrow("quota_exceeded"), '', "{$cname} - {$numvar}");
		}
	}

	$param = $ghtml->createCurrentParam($cname);

	$continueaction = $ghtml->frm_continueaction;
	$ret = exec_class_method($cname, 'continueForm', $object, $cname, $param, $continueaction);

	if ($ret['action'] === 'addnow') {
		__ac_desc_add($object, $ret['param']);

		return;
	}

	$alist = exec_class_method($cname, "createListAlist", $object, $cname);

	if ($alist) {
		$ghtml->print_tab_block($alist);
	}

	$ghtml->print_content_begin();

	$ghtml->print_message();

	$string[] = $ghtml->object_variable_startblock($object, $cname, "Continue Add $cname");

	$string[] = $ghtml->object_inherit_classpath();
	$string[] = $ghtml->object_variable_hidden("frm_o_cname", $cname);

	$tparam = $ret['param'];
	$vlist = $ret['variable'];

	if (isset($tparam['nname']) && exists_in_db($object->__masterserver, $cname, $tparam['nname'])) {
		throw new lxException($login->getThrow("already_exists"), '', "{$tparam['nname']} in {$cname}");
	}

	$param = null;

	foreach ($tparam as $k => $v) {
		$param["frm_" . $cname . "_c_" . $k] = $v;
	}

	$string[] = $ghtml->object_variable_hiddenlist($param);

	$string[] = create_xml($object, $cname, $ret);

	$pre = $post = null;

	if (isset($vlist['__m_message_pre'])) {
		$pre = $vlist['__m_message_pre'];
	}
	if (isset($vlist['__m_message_post'])) {
		$post = $vlist['__m_message_post'];
	}

	$ghtml->print_information('pre', 'continueform', "", $continueaction, $pre);
	$ghtml->xml_print_page($string);
	$ghtml->print_information('post', 'continueform', "", $continueaction, $post);

	$ghtml->print_content_end();
}

function __ac_desc_addform($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$cname = $ghtml->frm_o_cname;
	$dttype = $ghtml->frm_dttype;

	if (exec_class_method($cname, "consumeUnderParent")) {
		showParentProperty($object);
	}

	$selflist = $object->getSelfList();

	$alist = exec_class_method($cname, "createAddformAlist", $object, $cname, $dttype);

	if ($alist) {
		$ghtml->print_tab_block($alist);
	} else {
		$object->createShowPropertyList($alist);

		$nalist = $alist['property'];
		$ghtml->print_tab_block($nalist);
	}

	$ghtml->print_content_begin();

	if ($selflist) {
		$ghtml->printShowSelectBox($selflist);
	}

	$ghtml->print_message();

	do_addform($object, $cname, $dttype);

	if (exec_class_method($cname, "createAddformList", $object, $cname)) {
		do_list_class($object, $cname);
	}

	$ghtml->print_content_end();
}

function do_addform($object, $class, $dttype = null, $notitleflag = false)
{
	global $gbl, $sgbl, $login, $ghtml;

	$gbl->setSessionV("lx_add_return_url", "/display.php?" . $ghtml->get_get_from_current_post(null));

	$cdesc = get_description($class);
	$cdesc = ($dttype) ? $dttype['val'] : $cdesc;

	if ($notitleflag) {
		$title = null;
	} else {
		$title = "Add $cdesc";
	}
	$string[] = $ghtml->object_variable_startblock($object, $class, $title);

	$string[] = $ghtml->object_inherit_classpath();
	$string[] = $ghtml->object_variable_hidden("frm_o_cname", $class);
	$string[] = $ghtml->object_variable_hidden("frm_dttype", $dttype);

	$ret = exec_class_method($class, 'addform', $object, $class, $dttype);

	if ($dttype) {
		$ret['variable'][$dttype['var']] = array('h', $dttype['val']);
	}

	$string[] = create_xml($object, $class, $ret);

	$vlist = $ret['variable'];

	$pre = $post = null;

	if (isset($vlist['__m_message_pre'])) {
		$pre = $vlist['__m_message_pre'];
	}
	if (isset($vlist['__m_message_post'])) {
		$post = $vlist['__m_message_post'];
	}

	$ghtml->print_information('pre', 'addform', $class, $dttype['val'], $pre);
	$ghtml->xml_print_page($string);
	$ghtml->print_information('post', 'addform', $class, $dttype['val'], $post);
}

function create_xml($object, $stuff, $ret)
{
	global $gbl, $sgbl, $login, $ghtml;

	if (is_object($stuff)) {
		$class = lget_class($stuff);
	} else {
		$class = $stuff;
	}

	$vlist = $ret['variable'];
	$action = $ret['action'];

	$string = null;

	foreach ($vlist as $k => $v) {
		if (csb($k, "__c_")) {
			$cmd = substr($k, 4);
			$cmd = substr($cmd, 0, strpos($cmd, '_'));
			$string[] = $ghtml->object_variable_command($cmd, $v);

			continue;
		}

		if (csb($k, "__v_") || csb($k, "__m_")) {
			continue;
		}

		// Hack hack:: used_s is handled separately.. There is no other way, since without any other way to recognize it, 
		// quota variables  defaults to priv...
		if (!csb($k, "used_s")) {
			if ($v && $ghtml->is_special_variable($v[1])) {
				//
			} else {
				$descr = $ghtml->get_classvar_description_after_overload($class, $k);

				if (count($descr) < 3) {
					dprint("Variable $k in $class Not Defined... <br/> \n");
					$descr = array($class, $k, "Not Defined");
				}
			}

			lxclass::resolve_class_differences($class, $k, $dclass, $dk);
		}

		if ($k === "old_password_f") {
			$string[] = $ghtml->object_variable_oldpassword($dclass, "old_password_f", $descr);

			continue;
		}

		if ($k === "password" || $k === "dbpassword") {
			$string[] = $ghtml->object_variable_password($class, $k);

			continue;
		}

		if ($v) {
			if (csa($v[0], 'I')) {
				if ($v[1] && is_array($v[1])) {
					$opt = $v[1];
				} else {
					$opt['value'] = $v[1];
				}

				$string[] = $ghtml->object_variable_image($stuff, $k, $opt);
				$opt = null;

				continue;
			}

			if (csa($v[0], 'L')) {
				if ($v[1] && is_array($v[1])) {
					$opt = $v[1];
				} else {
					$opt['fvalue'] = $v[1];
				}

				$string[] = $ghtml->object_variable_fileselect($stuff, $k, $opt);
				$opt = null;

				continue;
			}

			if (csa($v[0], 'm')) {
				if ($v[1] && is_array($v[1])) {
					$opt = $v[1];
				} else {
					$opt['value'] = $v[1];
				}

				$string[] = $ghtml->object_variable_modify($stuff, $k, $opt);
				$opt = null;

				continue;
			}

			if (csa($v[0], 'E')) {
				if (!$v[1]) {
					$list = exec_class_method($dclass, "getSelectList", $object, $dk);
				} else {
					$list = $v[1];
				}

				$string[] = $ghtml->object_variable_selectradio($class, $k, $list);
				$list = null;

				continue;
			}

			if (csa($v[0], 's')) {
				if (!$v[1]) {
					$list = exec_class_method($dclass, "getSelectList", $object, $dk);
				} else {
					$list = $v[1];
				}

				$string[] = $ghtml->object_variable_select($stuff, $k, $list);
				$list = null;

				continue;
			}

			if (csa($v[0], 'A')) {
				if (!$v[1]) {
					$list = exec_class_method($dclass, "getSelectList", $object, $dk);
				} else {
					$list = $v[1];
				}

				$string[] = $ghtml->object_variable_select($stuff, $k, $list, true);
				$list = null;

				continue;
			}

			if (csa($v[0], 'Q')) {
				$string[] = $ghtml->object_variable_listquota($object, $stuff, $k, $v[1]);

				continue;
			}

			if (csa($v[0], 'U')) {
				if (!$v[1]) {
					$list = exec_class_method($dclass, "getSelectList", $object, $dk);
				} else {
					$list = $v[1];
				}

				$string[] = $ghtml->object_variable_multiselect($stuff, $k, $list);
			//	$list = null;

				continue;
			}

			if (csa($v[0], 'V')) {
				$string[] = $ghtml->object_variable_htmltextarea($stuff, $k, $v[1]);

				continue;
			}

			if (csa($v[0], 't')) {
				$string[] = $ghtml->object_variable_textarea($stuff, $k, $v[1]);

				continue;
			}

			if (csa($v[0], 'T')) {
				$string[] = $ghtml->object_variable_textarea($stuff, $k, $v[1], true);

				continue;
			}

			if (csa($v[0], 'h')) {
				$string[] = $ghtml->object_variable_hidden("frm_" . $class . "_c_" . $k, $v[1]);

				continue;
			}

			if (csa($v[0], 'f')) {
				$string[] = $ghtml->object_variable_check($stuff, $k, $v[1]);

				continue;
			}

			if (csa($v[0], 'M')) {
				$string[] = $ghtml->object_variable_nomodify($stuff, $k, $v[1]);

				continue;
			}

			if (csa($v[0], 'W')) {
				$string[] = $ghtml->object_variable_warning($stuff, $k, $v[1]);

				continue;
			}
		}

		if (csa($descr[0], 'F')) {
			$string[] = $ghtml->object_variable_file($stuff, $k);

			continue;
		}

		if (csa($descr[0], 'E')) {
			$list = exec_class_method($dclass, "getSelectList", $object, $dk);
			$string[] = $ghtml->object_variable_selectradio($stuff, $k, $list);

			continue;
		}

		if (csa($descr[0], 'U')) {
			$list = exec_class_method($dclass, "getSelectList", $object, $dk);
			$string[] = $ghtml->object_variable_multiselect($stuff, $k, $list);

			continue;
		}

		if (csa($descr[0], 'f')) {
			$string[] = $ghtml->object_variable_check($stuff, $k);

			continue;
		}

		if (csa($descr[0], 'e') || csa($descr[0], 's')) {
			$list = exec_class_method($dclass, "getSelectList", $object, $dk);
			$string[] = $ghtml->object_variable_select($stuff, $k, $list, false);

			continue;
		}

		if (csa($descr[0], 'A')) {
			$list = exec_class_method($dclass, "getSelectList", $object, $dk);
			$string[] = $ghtml->object_variable_select($stuff, $k, $list, true);

			continue;
		}

		if (csa($descr[0], 't')) {
			$string[] = $ghtml->object_variable_textarea($stuff, $k);

			continue;
		}

		if (csa($descr[0], 'T')) {
			$string[] = $ghtml->object_variable_textarea($stuff, $k, null, true);

			continue;
		}

		if (csa($descr[0], 'q')) {
			$string[] = $ghtml->object_variable_quota($object, $stuff, $k);

			continue;
		}

		if (csa($descr[0], 'Q')) {
			$string[] = $ghtml->object_variable_listquota($object, $stuff, $k);

			continue;
		}

		$string[] = $ghtml->object_variable_modify($stuff, $k);
	}

/*
	if (isset($gbl->c_session->ssession_vars['__tmp_csrf_token'])) {
		$token = $gbl->c_session->ssession_vars['__tmp_csrf_token'];
	} else {
		$token = randomString(64);

		$gbl->setSessionV('__tmp_csrf_token', $token);
		$gbl->c_session->write();
	}
*/
//	$token = getCSRFToken();

//	$string[] = $ghtml->object_variable_hidden("frm_token", $token);

	$string[] = $ghtml->object_variable_hidden("frm_action", $action);

	if (isset($ret['subaction'])) {
		$string[] = $ghtml->object_variable_hidden("frm_subaction", $ret['subaction']);
	}

	if (isset($ret['continueaction'])) {
		$string[] = $ghtml->object_variable_hidden("frm_continueaction", $ret['continueaction']);
	}

	$button = null;

	if (isset($vlist['__v_button'])) {
		if ($vlist['__v_button']) {
			$button = $vlist['__v_button'];
		}
	} else {
		$button = $action;
	}

	// MR -- change frm_change to hidden and button with frm_submit/frm_submit_all
	// for fix updateall issue

	$string[] = $ghtml->object_variable_hidden("frm_change", $button);

	if ($button) {
		$string[] = $ghtml->object_variable_button($button);
	}

	if (isset($vlist['__v_updateall_button'])) {
		$string[] = $ghtml->object_variable_button('updateall');
	}

	return $string;
}

function resolve_single_child($object, $class, $nname)
{
	if (char_search_a($class, "_s_") || char_search_a($class, "-")) {
		if (csa($class, "_s_")) {
			$olist = explode("_s_", $class);
		} else if (csa($class, "-")) {
			$olist = explode('-', $class);
		}

		$object = $object->getFromList($olist[0], $nname);
		$olist[0] = null;
		unset($olist[0]);

		foreach ($olist as $o) {
			$object = $object->getObject($o);
		}
	} else {
		$object = $object->getFromList($class, $nname);
	}

	return $object;
}

function print_navigation($navig)
{
	global $gbl, $sgbl, $login, $ghtml;

	$skin_name = $login->getSpecialObject('sp_specialplay')->skin_name;
	$button_type = $login->getSpecialObject('sp_specialplay')->button_type;

	if ($ghtml->isSelectShow()) {
		return;
	}

	$img_path = $login->getSkinDir();
	$imgleftpoint = "$img_path/images/left_point.gif";
	$imgrightpoint = "$img_path/images/right_point.gif";
	$xpos = 0;
	$navigmenu = $gbl->__navigmenu;

	$icondir = get_image_path();

	if ($login->getSpecialObject('sp_specialplay')->isOn('show_navig')) {
		$vis = 'visible';
		$imgpoint = $imgleftpoint;
	} else {
		$vis = 'hidden';
		$imgpoint = $imgrightpoint;
	}

	if ($gbl->isOn('show_lpanel')) {
		$imgleftpanel = $imgleftpoint;
	} else {
		$imgleftpanel = $imgrightpoint;
	}

	$imgpoint = null;

	$navtxt = null;
	$navigpoint = null;

	$buttonpath = get_image_path();

	$url = $ghtml->get_get_from_current_post(null);

	$url = '/display.php?' . $url;
	$description = $ghtml->getActionDetails($url, null, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);

	$ghtml->save_non_existant_image($image);
	$demoimg = null;
	$ob = $gbl->__c_object;

	// Hack to fix a bug reported by samuel.

	if (!$ob) {
		return;
	}

	$imgstr = null;

	if ($ghtml->frm_action === 'show') {
		$list = $ob->createShowMainImageList();

		foreach ((array)$list as $k => $v) {
			if ($v) {
				if (isset($ob->$k)) {
					$img = $ghtml->get_image($buttonpath, $ob->getClass(), "{$k}_v_" . $ob->$k, ".gif");
					$imgstr[] = "<span title='$k is {$ob->$k}'><img src='$img' width='20' height='20'></span>";
				}
			} else {
				$v = $ob->display($k);
				$img = $ghtml->get_image($buttonpath, $ob->getClass(), "{$k}_v_" . $ob->display($k), ".gif");
				$imgstr[] = "<span title='$k is " . $ob->display($k) . "'><img src='$img' width='20' height='20'></span>";
			}
		}
	}

	if ($imgstr) {
		$imgstr = implode(" ", $imgstr);
	}

	if ($ob->isLxclient() && $ob->getSpecialObject('sp_specialplay') && $ob->getSpecialObject('sp_specialplay')->isOn('demo_status')) {
		$_timg = $ghtml->get_image($buttonpath, $ob->getClass(), "updateform_demo_status", ".gif");
		$demoimg = "<span title='Account is Demo'><img src='$_timg'></span>";
	}

	if ($sgbl->isBlackBackground()) {
		$imgstr = null;
		$image = "$icondir/black.gif";
	}

	// MR -- need add table around div to resolv div height!
	$padd = "2px";
?>
	<script>
		var gl_imgrightpoint = '<?=$imgleftpoint?>';
		var gl_imgleftpoint = '<?=$imgrightpoint?>';
	</script>

<table class="tbl_navigation"><tr><td nowrap='nowarp'>
<div style="padding: 2px">
<?php
		if ($button_type !== 'image') {
			$class = 'if16';
?>
			<div style="float: left;margin-top: 0">&nbsp;<span class="<?= $class ?>" style="color: #c22">&#xf009;</span>&nbsp;</div>
<?php
		} else {
			$img_height = '20';
?>
			<div style="float: left"><?="$imgstr $demoimg"?><img height="<?= $img_height ?>" src="<?=$image?>"></div>
<?php
		}

		$forecolorstring = null;

		if ($sgbl->isBlackBackground()) {
			$forecolorstring = "style='color:gray'";
		}

		foreach ((array)$navig as $k => $h) {
			//You have to actually get only the filters of the parents of this object. But let us just print all the filters anyway.
			$url = $ghtml->get_get_from_post(null, $h);
			$url = "/display.php?$url";
			$desc = $ghtml->getActionDescr('', $h, $class, $var, $name);
			$image = $ghtml->get_image($buttonpath, $class, $var, ".gif");
			$desc['help'] = $ghtml->get_action_or_display_help($desc['help'], 'action');
		//	$nname = substr($name, 0, 19);
			$nname = $name;

			$bracketedname = null;

			if ($navigmenu[$k][0] != 'list') {
			//	$bracketedname = "&nbsp;($nname)";
				$bracketedname = '';
				$desc['desc'] = "$nname";
			}

			$menustring = null;
?>

			<div style="float:left; padding-top: <?= $padd ?>;">&nbsp;<a href='<?=$url?>'><b><?=$desc['desc']?><?=$bracketedname?></b></a>&nbsp;&#x0097;</div>
<?php
		}


		$ob = $gbl->__c_object;
		$name = $ob->getId();
		$imgstr = array();

		if ($ghtml->frm_action === 'show') {

			$list = $ob->createShowImageList();

			foreach ((array)$list as $k => $v) {
				if ($v) {
					if (isset($ob->$k)) {
						$img = $ghtml->get_image($buttonpath, $ob->getClass(), "{$k}_v_" . $ob->$k, ".gif");
						$imgstr[] = "<span title='$k is {$ob->$k}'><img src='$img' width='9' height='9'></span>";
					}
				} else {
					$v = $ob->display($k);
					$img = $ghtml->get_image($buttonpath, $ob->getClass(), "{$k}_v_{$ob->display($k)}", ".gif");
					$imgstr[] = "<span title='$k is " . $ob->display($k) . "'><img src='$img' width='9' height='9'></span>";
				}
			}
		}

		if ($sgbl->isKloxo() && $gbl->c_session->ssl_param['backbase']) {
			$s = $gbl->c_session->ssl_param;
			$v = $s['backbase'];
			$pcl = $s['parent_clname'];
			$selfip = $_SERVER['SERVER_NAME'];
			$curl = "/display.php?{$ghtml->get_get_from_current_post(array('frm_emessage', 'frm_ssl'))}";
			$kloxourl = "&frm_ndskshortcut_c_vpsparent_clname=$pcl";

		} else {
			$v = "/display.php";
			$curl = "/display.php?{$ghtml->get_get_from_current_post(null)}";
			$kloxourl = null;
		}

		$iconpath = get_image_path();
		$ac_descr = $ghtml->getActionDetails($curl, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
		$curl = base64_encode($curl);
		$desc = "{$ac_descr['desc']} $__t_identity";
		$desc = urlencode($desc);

		$shurl = "$v?frm_o_cname=ndskshortcut&frm_ndskshortcut_c_ttype=favorite&frm_ndskshortcut_c_url=$curl&frm_action=add&frm_ndskshortcut_c_description=$desc$kloxourl";

		$clienttype = null;

		if ($ob->isClient() && $ghtml->frm_action === 'show') {
			$clienttype = ucfirst($ob->cttype);
			$clienttype = "$clienttype ";
		}

		$fullimgstr = implode(" ", $imgstr);
?>

		<div style="float:left; padding-top: <?= $padd ?>;" id="tnavig<?=$k?>" onMouseOut="changeContent('help', 'helparea');">&nbsp;{<?=$clienttype?><?=trim($description['desc'])?>}<?=$fullimgstr?></div>
<?php

		$hypervm = null;

		if ($sgbl->isKloxo() && $gbl->c_session->ssl_param) {
			$hypervm = "HyperVM";
		}

		if (($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) && ($login->getSpecialObject('sp_specialplay')->skin_name !== 'simplicity')) {
			if ($login->getSpecialObject('sp_specialplay')->isOn('show_thin_header')) {
				//	$v = create_simpleObject(array('url' => "javascript:top.mainframe.logOut()", 'purl' => '&a=updateform&sa=logout', 'target' => null));
				//	$ghtml->print_div_button_on_header(null, true, $k, '');
?>

				<div style="float:right; padding: 2px;"><input type="button" value="Help" onClick="toggleVisibilityById('infomsg');"/><input type="button" value="Logout" onClick="if (confirm('<?= $login->getKeywordUc('is_want_logout') ?>')) {top.location = '/lib/php/logout.php';}" /></div>
<?php
			}

		} elseif ($login->getSpecialObject('sp_specialplay')->skin_name !== 'simplicity') {
			$imgstring = "<img width='18' height='18' src='/theme/general/button/star.gif'>";

			if ($sgbl->isBlackBackground()) {
				$imgstring = null;
			}

?>

			<div style="float: right; padding-top: <?= $padd ?>;">&nbsp;<a href="javascript://" onclick="toggleVisibilityById('infomsg');"><?= $login->getKeywordUc('help') ?></a>&nbsp;-&nbsp;<a href="<?=$shurl?>"><?= $login->getKeywordUc('add_to_favorites') ?></a>&nbsp;</div>
<?php
		}

?>
	</div>
</td>
<td align="right"><div style="padding: 2px"><span style='font-weight: bold; color: #3498db'><?php echo $login->getKeywordUc('login_as'); ?>&nbsp;'<?php echo $login->nname; ?>'&nbsp;&mdash;&nbsp;<?php echo $login->getKeywordUc('click_help'); ?><span>&nbsp;</div></td>
</tr></table>

<?php
}

function create_navmenu($n, $action, $stuff)
{
	global $gbl, $sgbl, $login, $ghtml;

	if (is_object($stuff)) {
		$class = lget_class($stuff);
	} else {
		$class = $stuff;
	}

	if (is_object($stuff)) {
		$stuff->createShowAlist($alist);
		$type = 'slist';
	} else {
		$type = 'llist';
		$alist = exec_class_method($stuff, "createListAlist", $login, $stuff);
	}

	$f = null;

	if (isset($gbl->__navig[$n]['frm_o_o'])) {
		$f = $gbl->__navig[$n]['frm_o_o'];
	}

	$ghtml->print_menulist("navig$n", $alist, $f, $type);
}

function __ac_desc_resource($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	$sgbl->__var_main_resource = true;

	$treename = fix_nname_to_be_variable($object->nname);
?>

	<table valign=top>
		<tr align=left>
			<td width='10'><input class='submitbutton' onClick='<?=$treename?>.closeAll();' type='button' value="Close">
			</td>
			<td align='left' width='10'><input class='submitbutton' onClick='<?=$treename?>.openAll();' type='button' value="Open"></td>
			<td width='100%'></td>
		</tr>
	</table>
<?php
	$ghtml->do_full_resource($object, 0, false);
}

function print_warning()
{
	global $gbl, $sgbl, $login, $ghtml;

	if ($gbl->getSessionV('__v_not_first_time')) {
		return;
	}

	$sesss = $login->getList('ssession');

	if (count($sesss) > 1) {
		$ghtml->__http_vars['frm_emessage'] = "more_than_one_user";
	}

	$gbl->setSessionV('__v_not_first_time', 1);
	$gbl->__v_first_time = 1;
}

function license_check()
{
	global $gbl, $sgbl, $login, $ghtml;

	// Don't check for license if you are currently doing license management.
	if (csb($ghtml->frm_action, 'update') && $ghtml->frm_subaction === 'license') {
		return;
	}

	if ($gbl->getSessionV('__v_not_first_time')) {
		return;
	}

	// First time;
	dprint("First time");
	$time = getLicense('lic_expiry_date');
	$iip = getLicense('lic_ipaddress');
	$ipdb = new Sqlite(null, 'ipaddress');
	$iplist = $ipdb->getRowsWhere("syncserver = 'localhost'", array('ipaddr'));
	$match = false;

	// Lack of ip should give a warning. Or allow people to reread the ip address.

	foreach ((array)$iplist as $ip) {
		if ($ip['ipaddr'] === $iip) {
			$match = true;
		}
	}

	$time = intval($time);

	if ($time < time()) {
		$mess = "License Expired";
?>

		<br/> <br/>
		<?=$mess?>
<?php

		if ($login->isAdmin()) {
			do_updateform($login, "license");
		}

		exit;
	}

	if ($login->isAdmin()) {
		if (($time - time()) < 24 * 3600 * 27) {
			// Putting it into http error messaeg. Should actually move this to gbl.
			$expire = ($time - time()) / (24 * 3600);
			$ghtml->__http_vars['frm_emessage'] = "license_will_expire";
			$ghtml->__http_vars['frm_m_emessage_data'] = round($expire);
		}

		if (!$match) {
			$ghtml->__http_vars['frm_emessage'] = "license_doesnt_match_ip";
		}
	}

	$gbl->setSessionV('__v_not_first_time', 1);
	$gbl->__v_first_time = 1;
}

function password_contact_check()
{
	global $gbl, $sgbl, $login, $ghtml;

	if (!$login->isAdmin()) {
		return;
	}

	if (csb($ghtml->frm_action, 'update') && $ghtml->frm_subaction === 'password') {
		return;
	}

	if (check_raw_password('client', 'admin', 'admin')) {
/*
?>

		<br/>
		<div style="width: 640px; background-color: #fff; margin: 0 auto; border: 1px solid #ddd;">
		<br/>
<?php

			if (!isset($ghtml->__http_vars['frm_emessage'])) {
				$ghtml->__http_vars['frm_emessage'] = 'security_warning';
			}

			$ghtml->print_message();
			$gbl->frm_ev_list = "old_password_f";
			do_updateform($login, 'password');
?>
		</div>
		<br/>
<?php
		exit;
*/
		// MR -- change to redirect to 'password' page
		$ghtml->print_redirect($ghtml->getFullUrl("a=updateform&sa=password"), $gbl->__this_window_url);
	}
}

function do_display_init()
{
	global $gbl, $sgbl, $login, $ghtml;

	$skindir = $login->getSkinDir();
	$col = $login->getSkinColor();
	check_if_disabled_and_exit();

	$buttontype = $login->getSpecialObject('sp_specialplay')->button_type;

	if (!ifSplashScreen()) {
		ob_start();
	}

	if ($gbl->getSessionV('__refresh_lpanel') == 'true') {
		print("<script> top.leftframe.window.location.reload() ; </script>");
		print("<script> top.topframe.window.location.reload() ; </script>");
		$gbl->unsetSessionV('__refresh_lpanel');
	}

	if ($ghtml->frm_refresh_lpanel === 'true') {
		unset($ghtml->__http_vars['frm_refresh_lpanel']);
		print("<script> top.leftframe.window.location.reload(); </script>");
		print("<script> top.topframe.window.location.reload() ; </script>");
	}

	createPrincipleObject();
?>
	<html>
	<head>
		<title> Kloxo-MR Display Page </title>
<?php
		print_meta_lan();
?>

		<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
<?php
	if ($buttontype !== 'image') {
?>
	 <link rel="stylesheet" type="text/css" media="all" href="/theme/fonts/font.css" />
<?php
	}

	$ghtml->print_include_jscript();
?>
	</head>
<?php
	$ghtml->print_real_beginning();

	if (!$login->isDefaultSkin()) {
		print_head_image();
	}

	if ($sgbl->isKloxo() && $gbl->c_session->ssl_param) {
		$url = $gbl->c_session->ssl_param['backurl'];
		$parent = $gbl->c_session->ssl_param['parent_clname'];
?>

		<table cellpadding='0' height='26' cellspacing='0' background="<?=$skindir?>/expand.gif">
			<tr>
				<td nowrap><a href="<?=$url?>"> Back to HyperVM ($parent) </a></td>
				<td width='10'>&nbsp;|&nbsp;</td>
				<td> Kloxo</td>
				<td width='10'>&nbsp;|&nbsp;</td>
				<td><a href="/display.php?frm_action=show">Home</a></td>
				<td width='10'>&nbsp;|&nbsp;</td>
				<td><a href="/display.php?frm_action=list&frm_o_cname=all_domain">All </a></td>
				<td width='10'>&nbsp;|&nbsp;</td>
				<td><a href="/display.php?frm_action=list&frm_o_cname=client">Clients</a></td>
				<td width="100%"></td>
				<td><a href="/lib/php/logout.php"> Logout </a></td>
			</tr>
		</table>
<?php
	}

	if ($gbl->c_session->consuming_parent) {
	/*
?>

		<table cellpadding=0 cellspacing=0 bgcolor="<?=$col?>">
			<tr>
				<td nowrap> Consumed Login</td>
				<td><a href="/display.php?frm_consumedlogin=true&frm_action=desktop">Desktop </a></td>
				<td width='100%'></td>
				<td><a href="/lib/php/logout.php?frm_consumedlogin=true"> Logout </a></td>
			</tr>
		</table>
<?php
	*/
	}

	$ghtml->print_splash();

	if (ifSplashScreen()) {
		flush();
		ob_start();
	}

	$ghtml->print_start();

	$gbl->__this_redirect = null;

	try {
		main_system_lock();

		if ($login->getSpecialObject('sp_specialplay')->skin_name !== 'simplicity') {
			print_navigation($gbl->__navig);
		}

		print_warning();
		password_contact_check();
	} catch (Exception $e) {
		log_log("redirect_error", "exception");
		$gbl->setSessionV('__tmp_redirect_var', $ghtml->__http_vars);
		$gbl->c_session->write();

		if (is_array($e->variable)) {
			$evlist = implode(",", $e->variable);
		} else {
			$evlist = $e->variable;
		}

		$ghtml->print_redirect_back($e->getMessage(), $evlist, $e->value);

		exit;
	}
}

function do_display_exec()
{
	display_exec();
?>

	</div>
	</body>
	</html>
<?php
}

function __ac_desc_about()
{
	global $gbl, $sgbl, $login, $ghtml;

	$ghtml->print_about();
}

function main_system_lock()
{
	global $gbl, $sgbl, $login, $ghtml;

	// Not needed for hyperVM. HyperVM has more intelligent per vps locking.
	if ($sgbl->isHyperVm()) {
		return;
	}

	return;

	$lname = null;
	$nlname = $login->getClName();

	if ($nlname !== $lname && isModifyAction() && lx_core_lock($nlname)) {
		$ghtml->print_redirect_back('system_is_locked_by_u', '');

		exit;
	}
}

function display_init()
{
	global $gbl, $sgbl, $login, $ghtml;

	initProgram();
	init_language();

	if ($sgbl->is_this_slave()) {
		display_die("Slave Server");
	}

	// The only thing that gets modified when the dbaction is not a modify action, is the ssession table. 
	// Other tables should get modified only inside non-form actions.
	if (isModifyAction() && isUpdating()) {
		$ghtml->print_redirect_back('system_is_updating_itself', '');

		exit;
	}

	do_display_init();

	if ($ghtml->frm_filter) {
		$filtername = $gbl->__c_object->getFilterVariableForThis($ghtml->frm_o_cname);
		$list[$filtername] = $ghtml->frm_filter;
		$login->setupHpFilter($list);
		$login->setUpdateSubaction();
	}

	if ($ghtml->frm_hpfilter) {
		$login->setupHpFilter($ghtml->frm_hpfilter);
		$login->setUpdateSubaction();
	}

	do_display_exec();
}

function lx_frm_inc()
{
	global $gbl, $sgbl, $login, $ghtml;

	if (!$ghtml->iset("frm_action")) {
		display_die('Action Not set');
	}

//	if (isRemotePost()) {
//		display_die('Remote post not permit');
//	}

	if(!file_exists("{$sgbl->__path_program_etc}/flag/not_use_token.flg")) {
		if (!isCSRFTokenMatch()) {
			display_die('Post token not match or using get instead post');
		}
	}

	$caction = $ghtml->frm_action;
//	$cgi_action = "__ac_desc_{$ghtml->frm_action}";
	$cgi_action = "__ac_desc_{$caction}";

	$cobject = $gbl->__c_object;

	if (!function_exists($cgi_action)) {
		display_die('Action not supported');
	}

	try {
		switch ($caction) {
			case "add":
				__ac_desc_add($cobject);
				break;
			case "addform":
				__ac_desc_addform($cobject);
				break;
			case "update":
				__ac_desc_update($cobject);
				break;
			case "delete":
				__ac_desc_delete($cobject);
				break;
			default:
				$cgi_action($cobject);
				break;
		}

		$login->was();

		if ($login->isAuxiliary()) {
			$login->__auxiliary_object->setUpdateSubaction();
			$login->__auxiliary_object->write();
		}

		$gbl->unsetSessionV('__tmp_redirect_var');
	} catch (Exception $e) {
		log_log("redirect_error", "exception");
		$gbl->setSessionV('__tmp_redirect_var', $ghtml->__http_vars);
		$gbl->c_session->write();

		if (is_array($e->variable)) {
			$evlist = implode(",", $e->variable);
		} else {
			$evlist = $e->variable;
		}

		$ghtml->print_redirect_back($e->getMessage(), $evlist, $e->value);

		exit;
	}

	// If redirecting, too, ssession wont be saved....

	if ($gbl->__this_redirect) {
		save_login();

		if ($gbl->__this_warning) {
			$m = $gbl->__this_warning['message'];
			$gbl->__this_redirect .= "&frm_emessage=$m";
		}

		$windowurl = null;

		if (isset($gbl->__this_window_url)) {
			$windowurl = $gbl->__this_window_url;
		}

		$ghtml->print_redirect($gbl->__this_redirect, $windowurl);
	}

	// This is a misnomer.. It just saves the lx_http_refer, ssession variables... And also saves the login, if it exists.
	exit_program();

	if (function_exists("after_exit_program")) {
		after_exit_program();
	}

	if (isset($gbl->__this_function)) {
		dprint("Calling $gbl->__this_function <br/> <br/> ");

		// workaround for the following php bug:
		//   http://bugs.php.net/bug.php?id=47948
		//   http://bugs.php.net/bug.php?id=51329

		if (is_array($gbl->__this_function) && count($gbl->__this_function) > 0) {
			$class = $gbl->__this_function[0];
			class_exists($class);
		}

		// ---
		call_user_func_array($gbl->__this_function, $gbl->__this_functionargs);
	}
}

function exit_if_under_maintenance()
{
	global $gbl, $sgbl, $login, $ghtml;

	if ($login->isAdmin()) {
		return;
	}

	$g = $login->getObject('general');
	$gen = $login->getObject('general')->generalmisc_b;

	if ($gen->isOn("maintenance_flag")) {
		display_die($g->text_maintenance_message);
	}
}

function display_exec()
{
	global $gbl, $sgbl, $login, $ghtml;

	exit_if_under_maintenance();

	try {
		lx_frm_inc();
	} catch (Exception $e) {
		log_redirect("Caught except");
?>
		The resource you requested could not be retrieved... <?= $e->getMessage() ?>

<?php
	}
}

function print_head_image()
{
	global $gbl, $sgbl, $login, $ghtml;

	if ($sgbl->isBlackBackground()) {
		return;
	}

	if ($sgbl->isKloxo() && $gbl->c_session->ssl_param) {
		return;
	}

	if ($login->getSpecialObject('sp_specialplay')->isOn('show_thin_header')) {
		return;
	}

	$skin_col_dir = $login->getSpecialObject('sp_specialplay')->skin_color;

	$skin_name = $login->getSpecialObject('sp_specialplay')->skin_name;

	$skindir = $login->getSkinDir();

	// MR -- to minimize space; use from default
	$skindir_default = str_replace($skin_col_dir, 'default', $skindir);

	if ($skin_name !== 'feather') { return; }
?>
	<link href="<?=$skindir?>/css/style.css" rel="stylesheet" type="text/css" />
	<table class='bgtop3' style="width:100%; border-spacing:0; border-collapse:collapse; padding:0; margin:0; border:0; background:url(<?=$skindir_default?>/images/completefeather.jpg)">
		<tr>
			<td width='100%' id='td1'></td>
<?php
			if ($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) {
?>

				<td nowrap valign='top'><input type="button" value="Help" onClick="toggleVisibilityById('infomsg');" /><input type="button" value="Logout" onClick="if (confirm('Do You Really Want To Logout?')){top.location = '/lib/php/logout.php';}"></td>
<?php
			}
?>
		</tr>
		<tr>
			<td colspan='3' class='bg2'></td>
		</tr>
	</table>
<?php
}

function print_favorites()
{
	global $gbl, $sgbl, $login, $ghtml;

	$back = $login->getSkinDir();
	$list = $ghtml->get_favorite("ndskshortcut");

	$vvar_list = array('ttype', '_t_image', 'url', 'target', '__t_identity', 'ac_descr', 'str', 'tag');

	$res = null;

	foreach ((array)$list as $l) {
		foreach ($vvar_list as $vvar) {
			$$vvar = isset($l[$vvar]) ? $l[$vvar] : '';
		}

		if ($ttype == 'separator') {
			$res .= "<tr valign='top' style=\"border-width:1px; background:url($back/a.gif);\"> <td ></td> </tr>";
		} else {
			$res .= "<tr valign='top' style=\"border-width:1px; background:url($back/a.gif);\"> <td > <span title=\"$ac_descr[2] for $__t_identity\"> <img width=16 height=16 src=$_t_image> <a href=$url target=$target>  $str $tag</a></span></td> </tr>";
		}
	}

	return $res;
}

function print_quick_action($class)
{
	global $gbl, $sgbl, $login, $ghtml;

	$iconpath = get_image_path();

	if ($class === 'self') {
		$object = $login;
		$class = $login->getClass();
	} else {
		$list = $login->getVirtualList($class, $count);
		$object = getFirstFromList($list);
	}

	if (!$object) {
		return "No Object";
	}

	$namelist = get_namelist_from_objectlist($list);

	$alist = get_quick_action_list($object);

	foreach ($alist as $a) {
		$ac_descr = $ghtml->getActionDetails($a, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
	}

	$stylestr = "style=\"font-size: 10px\"";

	$res = null;
	$res .= " <tr style=\"background:#d6dff7\"> <td >";
	$res .= "<form name='quickaction' method='{$sgbl->method}' target='mainframe' action=\"/theme/lbin/redirect.php\">";
//	$res .= "<form name='quickaction' method='get' target='mainframe' action=\"/theme/lbin/redirect.php\">";

	$desc = $ghtml->get_class_description($class);

	if (!$object->isLogin()) {
		$res .= "<select $stylestr name=frm_redirectname>";

		foreach ($namelist as $l) {
			$pl = substr($l, 0, 26);
			$res .= '<option ' . $stylestr . ' value="' . $l . '" >' . $pl . '</option>';
		}

		$res .= "</select> </td> </tr>  ";
	}

	$res .= " <tr style=\"background:#d6dff7\"> <td ><select $stylestr name='frm_redirectaction'>";

	foreach ($alist as $k => $a) {
		if (csb($k, "__title")) {
			$res .= '<option value="" >------' . $a . '----</option>';

			continue;
		}

		$ac_descr = $ghtml->getActionDetails($a, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);

		$a = base64_encode($a);

		$desc = substr($ac_descr[2], 0, 20);
		$res .= '<option ' . $stylestr . ' value="' . $a . '" >' . $desc . '</option>';
	}

	$res .= "</select> </td> </tr> ";
	$res .= "</form> <tr> <td align='right'> <a href='javascript:quickaction.submit()'> Go </a> </td> </tr> ";

	return $res;
}

function display_die($string)
{
	die("<div class='div_display_die'>" . $string ."</div>");	
}

