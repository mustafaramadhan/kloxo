<?php

class FormVar
{
	function __get($key)
	{
		return null;
	}
}

class HtmlLib
{
	public $__message;

	static function checkForScript($value)
	{
		// [FIXME] replace this function with a sanitization method applied on request data
		// we can use the htmlpurifier project if we need to display html or htmlspecialchars to display text
		// for now we limit to log the attempt and stop the script

		if (csa($value, "<") || csa($value, ">") || csa($value, "(") || csa($value, ")")) {
			log_security("XSS attempt: $value");
			exit;
		}

		if (csa($value, "'")) {
			log_security("SQL injection attempt: $value");
			exit;
		}
	}

	function __construct()
	{
		global $gbl, $sgbl;
		$tmp = array_merge($_GET, $_POST); # [FIXME] We should use $tmp = $_REQUEST;

		if (isset($tmp['frm_o_o']) && $tmp['frm_o_o']) {
			ksort($tmp['frm_o_o']); # [FIXME] Why order? useless?

			foreach ($tmp['frm_o_o'] as $k => $v) {
				if (isset($k)) {
					self::checkForScript($k);
				}
				if (isset($v['class'])) {
					self::checkForScript($v['class']);
				}
				if (isset($v['nname'])) {
					self::checkForScript($v['nname']);
				}
			}
		}

		if (isset($tmp['frm_dttype']) && $tmp['frm_dttype']) {
			foreach ($tmp['frm_dttype'] as $k => $v) if (isset($v)) {
				self::checkForScript($v);
			}
		}

		if (isset($tmp['frm_accountselect'])) {
			self::checkForScript($tmp['frm_accountselect']);
		}

		if (isset($tmp['frm_hpfilter'])) {
			foreach ($tmp['frm_hpfilter'] as $k => $v) if (is_array($v)) {
				foreach ($v as $kk => $vv) self::checkForScript($vv);
			}
		}

		if (isset($tmp['frm_action'])) {
			self::checkForScript($tmp['frm_action']);
		}
		if (isset($tmp['frm_subaction'])) {
			self::checkForScript($tmp['frm_subaction']);
		}
		if (isset($tmp['frm_o_cname'])) {
			self::checkForScript($tmp['frm_o_cname']);
		}

		$hvar = array();

		$this->nname = 'html';

		$gbl->frm_ev_list = null;
		if (isset($tmp['frm_ev_list'])) {
			$gbl->frm_ev_list = $tmp['frm_ev_list'];
			unset($tmp['frm_ev_list']);
		}

		foreach ($tmp as $key => $value) {
			if (char_search_a($key, "_aaa_")) {
				$arvar = substr($key, 0, strpos($key, "_aaa_"));
				$arkey = substr($key, strpos($key, "_aaa_") + 5);
				$arval = $value;
				if (!csa($arvar, "password") && !csa($arvar, "text")) {
					$hvar[$arvar][$arkey] = $arval;
				} else {
					$hvar[$arvar][$arkey] = $arval;
				} #[FIXME] Same behaviour?
			} else {
				if (!is_array($value)) {
					if (!csa($key, "password") && !csa($key, "text")) {
						$hvar[$key] = $value;
					} else {
						$hvar[$key] = $value;
					} #[FIXME] Same behaviour?

				} else {
					$hvar[$key] = $value;
				}
			}
		}

		//FIXME: HACK.. fixing the quota variables from arrays to strings. Moving teh unlimited to the value itself.
		foreach ($hvar as $key => $val) {
			if (csa($key, '_c_priv_s_')) {
				if (!is_array($val)) {
					continue;
				}

				if (cse($key, "_flag")) {
					if (isset($val['checked'])) {
						$hvar[$key] = $val['checked'];
					} else {
						$hvar[$key] = $val['checkname'];
					}
					continue;
				}

				if (isset($val['unlimited'])) {
					$hvar[$key] = "Unlimited";
				} else {
					if (cse($key, "_time")) {
						$hvar[$key] = mktime(0, 0, 0, $val['month'], $val['day'], $val['year']);
					} else {
						if ($val['quotaname'] !== "") {
							$hvar[$key] = $val['quotaname'];
						} else {
							$hvar[$key] = $val['quotamax'];
						}
					}
				}
			}
		}
		foreach ($hvar as $key => $val) {
			if (is_array($val)) {

				if (isset($val['selectandvaluecheckname']) || isset($val['selectandvaluecheckhidden'])) {
					if (isset($val['selectandvaluecheckname'])) {
						$hvar[$key] = $val['selectandvaluecheckname'];
					} else {
						$hvar[$key] = $val['selectandvaluecheckhidden'];
					}
				}


				if (isset($val['checked']) || isset($val['checkname'])) {
					if (isset($val['checked'])) {
						$hvar[$key] = $val['checked'];
					} else {
						$hvar[$key] = $val['checkname'];
					}
				}
			}
		}


		$this->__http_vars = $hvar;
	}

	function do_url_decode(&$hvar)
	{
		foreach ($hvar as $key => &$value) {
			if (is_array($value)) {
				foreach ($value as $k => &$v) {
					if (is_array($v)) {
						foreach ($v as $nk => &$nv) $nv = urldecode($nv);
					} else {
						$v = urldecode($v);
					}
				}
			} else {
				$value = urldecode($value);
			}
		}
	}

	function getpath($key)
	{
		return $this->__path[$key];
	}

	function gfrm($key)
	{
		return (isset($this->__http_vars[$key])) ? $this->__http_vars[$key] : NULL;
	}

	function cgi($key)
	{
		return (isset($this->__http_vars[$key])) ? $this->__http_vars[$key] : NULL;
	}

	function isSelectShow()
	{
		return (strtolower($this->frm_action) === 'selectshow');
	}

	function get_htmlvar_details($key, &$class, &$variable, &$extra, &$value)
	{
		$string = $key;
		if (char_search_a($string, "_v_")) {
			$value = substr($string, strpos($string, "_v_") + 3);
			$string = substr($string, 0, strpos($string, "_v_"));
		}

		if (char_search_a($string, "_t_")) {
			$extra = substr($string, strpos($string, "_t_") + 3);
			$string = substr($string, 0, strpos($string, "_t_"));
		}

		if (char_search_a($string, "_c_")) {
			$variable = substr($string, strpos($string, "_c_") + 3);
			$string = substr($string, 0, strpos($string, "_c_"));
		}

		$class = substr($string, 4);

	}

	function getcgikey($key)
	{
		$nkey = substr($key, 4);
		$nkey = "_cgi_" . $nkey;
		return $nkey;
	}

	function getformkey($key)
	{
		$nkey = substr($key, 5);
		$nkey = "frm_" . $nkey;
		return $nkey;
	}

	function __get($key)
	{

		if (char_search_beg($key, "__path")) {
			dprint("Trying to access Path Variable in html $key");
		}

		if (char_search_beg($key, "__var")) {
			dprint("Trying to access Var Variable in html $key");
		}

		if (char_search_beg($key, "__c")) {
			dprint("Trying to access __c Variable in html $key");
		}

		$newkey = $key;

		if (!isset($this->__http_vars[$newkey])) {
			return null;
		}

		$v = $this->__http_vars[$newkey];

		if (is_array($v)) {
			foreach ($v as $kk => $vv) $nv[$kk] = $vv;
		} else {
			$nv = $v;
		}

		return $v;
	}

	function get_server_string($object)
	{
		if (!$object->isLocalhost() && $object->syncserver != $object->nname) {
			return "(on $object->syncserver)";
		}

		return null;
	}

	function print_info_block($obj, $ilist)
	{
		?>
	<table class="tableheader" width="95%">
		<tr align="right">
			<td>
				<b> <?= get_description($obj) . " Info for " . $obj->getId() ?> </b>
			</td>
		</tr>
	</table>

	<table cellpadding="0" cellspacing="0" border="0" width="70%" align="center">
		<tr height="20">
			<?php
				$class = get_class($obj);
			foreach ($ilist as $i) {
				$desc = "__desc_$i";
				$descr = get_classvar_description($class, $desc);
				$descr[2] = getNthToken($descr[2], 1);
				?>
				<td width="16%" align="left">
					<span style="color: bb3333; ">
						<b><?=$descr[2] ?>: <?= $obj->display($i) ?></b>
					</span>
				</td>
				<?php

			}
			?>
		</tr>
	</table>
			<?php

	}

	function whichTabSelect($alist)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$psuedourl = NULL;
		$target = NULL;
		$img_path = $login->getSkinDir();
		$imgtop = $img_path . '/top_line.gif';
		$buttonpath = get_image_path() . 'button/';
		foreach ($alist as $key => $url) {
			$this->resolve_int_ext($url, $psuedourl, $target);
			$check = $this->compare_urls("display.php?{$this->get_get_from_current_post(null)}", $url);
			$ret[$key] = $check;
		}
		return $ret;
	}

	function print_tab_block($alist)
	{
		global $gbl, $sgbl, $login, $ghtml;
		if ($login->isDefaultSkin()) {
			$this->print_tab_block_old($alist);
		} else {
			include_once "lib/print_tab.phps";
			print_tab_for_feather($alist);
		}
	}

	function print_tab_block_old($alist)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$img_path = $login->getSkinDir();
		$imgtop = $img_path . "/top_line.gif";

		foreach ($alist as $k => $a) $alist[$k] = $ghtml->getFullUrl($a);

		if ($login->getSpecialObject('sp_specialplay')->isOn('enable_ajax')) {
			$this->print_dialog($alist, $gbl->__c_object);
		}

		echo "<br>
        <table cellspacing=0 cellpadding=0 width=100% border=0>
            <tr align=left valign=bottom> <td width=10>
            <img src=$imgtop width=10 height=2></td> <td >
            <table cellspacing=0 cellspacing=0> <tr valign=bottom> ";
		foreach ($alist as $k => $a) $sel = $this->printTabButtonOld($k, $a);

		echo " </tr> </table> </td> <td width=100%>  <img src=$imgtop width=100% height=2></td></tr> </table> <br> <br> ";

	}

	function printTabButtonOld($key, $url)
	{

		global $gbl, $sgbl, $login, $ghtml;

		$cobject = $gbl->__c_object;
		static $after_sel = false;
		$psuedourl = NULL;
		$target = NULL;
		$img_path = $login->getSkinDir();
		$imgtop = $img_path . '/top_line.gif';

		$buttonpath = get_image_path() . '/button/';
		$bpath = $login->getSkinDir();
		$bdpath = $login->getSkinColor();
		$button = $bpath . '/top_line_medium.gif';

		$this->resolve_int_ext($url, $psuedourl, $target);

		$descr = $this->getActionDetails($url, $psuedourl, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);


		$form_name = $this->createEncForm_name($file . "_" . $name);

		$borderbottom = "style =\"border-bottom:2px solid #$bdpath;\"";
		$borderbot = "style =\"background:url($bpath/tab_select_bg2.gif) 0 0 repeat-x;\"";
		if ($check = $this->compare_urls("display.php?{$this->get_get_from_current_post(null)}", $url)) {
			$bgcolorstring = "bgcolor=#99aaff";
			$sel = "_select";
			$borderbottom = $borderbot;
		} else {
			$sel = "_select";
			$bgcolorstring = "bgcolor=#99aaff";
		}

		$imageheight = 24;
		$height = 34;
		$imgp = $login->getSkinDir();
		$imglt = $imgp . "/tab{$sel}_lt.gif";
		$imgbg = $imgp . "/tab{$sel}_bg.gif";
		$imgrt = $imgp . "/tab{$sel}_rt.gif";

		$linkflag = true;
		if (csa($key, "__var_")) {
			$privar = strfrom($key, "__var_");
			if (!$cobject->checkButton($privar)) {
				$linkflag = false;
			}
		}

		$idstring = null;
		if ($login->getSpecialObject('sp_specialplay')->isOn('enable_ajax') && csb($key, "__v_dialog")) {
			$idstring = "id=$key-comment";
		}

		?>
	<td>
		<table cellspacing=0 cellpadding=0  <?=$idstring?> <?=$borderbottom ?> valign=bottom>
			<tr valign=bottom>
				<?php
		if ($check) {
				print("<td valign=middle wrap><img src=$imglt height=38 width=2></td>");
			} else {
				print("<td valign=middle wrap><img src=$imglt height=$height width=3></td>");
			}
				?>
				<form method=get name=form_<?=$form_name ?> action=<?=$path?> <?=$target ?>>
				<?php
		$this->print_input_vars($post);
					print('</form>');
					$this->printTabForTabButton($key, $linkflag, $height + 2, $imageheight, $sel, $imgbg, $form_name, $name, $image, $descr, $check);

					if ($check) {
						print("<td ><img src=$imgrt width=2 height=38></td>");
					} else {
						print("<td ><img src=$imgrt width=3 height=$height></td>");
					}

					?>
			</tr>
		</table>
	</td>

				<?php
		return $sel;
	}


	function compare_urls($a, $b)
	{

		$rvar = array("frm_o_o", "frm_dttype", "frm_o_nname", "frm_o_parent", "frm_action", "frm_o_cname", "frm_subaction");

		$this->get_post_from_get($a, $path, $pa);
		$this->get_post_from_get($b, $path, $pb);

		if (isset($pb["frm_o_cname"])) {
			if (exec_class_method($pb['frm_o_cname'], "consumeUnderParent")) {
				if ($pb['frm_action'] === 'list') {
					$pb["frm_o_cname"] = null;
					$pb['frm_action'] = 'show';
				}
			}
		}

		foreach ($rvar as $k) {
			if (!isset($pa[$k])) {
				$pa[$k] = null;
			}
			if (!isset($pb[$k])) {
				$pb[$k] = null;
			}
		}

		foreach ($rvar as $k) if ($pa[$k] != $pb[$k]) {
			return false;
		}
		return true;
	}

	function printTabForTabButton($key, $linkflag, $height, $imageheight, $sel, $imgbg, $formname, $name, $imagesrc, $descr, $check)
	{
		global $gbl, $sgbl, $login;

		$help = $descr['help'];
		$imgstr = null;
		if ($imagesrc) {
			$imgstr = "<img width=$imageheight imageheight=$imageheight src=$imagesrc>";
		}

		if ($linkflag) {
			if ($login->getSpecialObject('sp_specialplay')->isOn('enable_ajax') && csb($key, "__v_dialog")) {
				$displaystring = "<span title='$help'>  $descr[2] </span>";
			} else {
				$displaystring = "<span title='$help'> <a href=\"Javascript:document.form_$formname.submit()\"> $descr[2]</a> </span>";
			}

		} else {
			$displaystring = "<span title=\"You don't have permission\">$descr[2] </span>";
		}

		if ($check) {
			?>
		<td height="34" wrap class="alink"
			style='cursor:pointer;padding:3 0 0 0;vertical-align:middle'><?=$imgstr ?> </td>
		<td height="height" nowrap class="alink" style='cursor:pointer;padding:3 0 0 0;vertical-align:middle'><font
				size=-1><?=$displaystring ?></td>
		<?
		} else {
			?>
		<td height=34 wrap class=alink
			style='cursor:pointer;background:url(<?=$imgbg ?>);padding:3 0 0 0; vertical-align:middle'><?=$imgstr ?> </td>
		<td height=height nowrap class=alink
			style='cursor:pointer;background:url(<?=$imgbg ?>);padding:3 0 0 0;vertical-align:middle'><font
				size=-1><?=$displaystring ?></td><?php

		}
	}

	function print_object_action_block($obj, $alist, $num)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$syncserver = $this->get_server_string($obj);
		$buttonpath = get_image_path() . "/button/";
		$image = $ghtml->get_image($buttonpath, '', 'resource', '.gif');
		$this->print_action_block($obj, $obj->get__table(), $alist, $num);
	}

	function print_action_block_old($title, $alist)
	{
		$i = 0;
		/* This is a mighty hack... The first element of $alist is
        supposed to be the main title. You use it as the first title and
        unset the variable. This is a hack from the previous code where
        the first title was preset here itself. */

		if (!$title) {
			$title = $alist['__title_main'];
			unset($alist['__title_main']);
		}

		?>
	<table cellpadding="0" width="100%" cellspacing="0" border="1">
	<tr>
	<td>
	<table cellpadding="2" cellspacing="7" border="0" width="25%">
	<tr align="left">
	<td align="left">
		<?php
								$t = 2;
		foreach ($alist as $k => $u) {
			$i++;
			if (csb($k, "__title")) {
				$i = 0;
				$t++;
				?>
                                        </td> </tr> </table> </td> <td>
                                        <table cellpadding=0 border=0 cellspacing=0> <tr> <td>
                                        <?php
										continue;
			}


			if ($t % 4 === 1) {
				?>
                                        </td> </tr> </table> </tr> <tr> <table cellpadding=0 cellspacing=0 border=2> <tr> <td>
                                        <?php

			}

			if ($i % 2) {
				?>
                                        </td> </tr> <tr> <td>
                                        <?php

			}
			$this->print_div_button(null, "block", false, $k, $u);
		}
		if ($i <= 7) {
			for (; $i <= 7; $i++) {
				print("</td><td width=40>&nbsp;");
			}
		}




		?>
	</td>
	</tr>
	</table>
		</td>
	</tr>
	</table>
	</fieldset>
	<br/>
	<br/>
		<?php

	}

	function print_action_block_dumb($title, $alist)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$getskin = $login->getSkinDir();
		$i = 0;

		/// This is a mighty hack... The first element of $alist is supposed to be the main title.
		// You use it as the first title and unset the variable. This is a hack from the previous code
		// where the first title was preset here itself.


		if (!$title) {
			$title = $alist['__title_main'];
			unset($alist['__title_main']);
		}

		?>
	<table valign=top cellpadding=3 cellspacing=3>
		<tr>
		<td valign=top width=33%>
		<table cellpadding=0 cellspacing=0 border=1>
		<tr>
		<td>
			<table cellpadding=0 cellspacing=0 border=0 height=13 width=98% style="background:url('<?php echo
			$getskin?>/bar.gif')">
				<tr>
					<td> <?=$title ?>  </td>
			</table>
		<table cellpadding="2" cellspacing="7" border="0" height=100% width="90%">
		<tr align=left>
		<td align=left>
			<?php
		$n = 1;
			foreach ($alist as $k => $u) {
				$i++;
				if ($i % 3 === 1) {
					print("</td> </tr> <tr align=left> <td align=left>");
				}

				if (csb($k, "__title")) {
					$i = 0;
					$n++;

					$tr = null;
					if ($n % 3 == 1) {
						$tr = "</tr> <tr> ";
					}
					?>
                </td> </tr> </table>

                </td> </tr> </table> </td> <?= $tr ?>  <td width=33% valign=top> <table cellpadding=0 cellspacing=0
																						border=1> <tr> <td>
        <table cellpadding=2 cellspacing=2 border=0 height=13 width=98% style="background:url('<?=$getskin?>/bar.gif')">
			<tr>
				<td> <?=$u ?>  </td>
		</table>

                <table cellspacing=7 width=90% border=0> <tr align=left> <td align=left>
                <?php
				continue;
				}
				$this->print_div_button(null, "block", true, $k, $u);
			}
			if ($i <= 7) {

				for (; $i <= 7; $i++) {
					print("</td><td width=40>&nbsp;");
				}
			}


			?>
		</td> </tr> </table>
		</td> </tr> </table>
		</td>
		</tr>
	</table>
	</fieldset>
	<br> <br>
			<?php

	}

	function create_action_block($class, $alist)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$alist) {
			return null;
		}

		$title = "main";
		$i = 0;
		$n = 0;
		foreach ($alist as $k => $a) {
			if (csb($k, "__title")) {
				$title = $k;
				$ret[$k][$k] = $a;
			}
			$ret[$title][$k] = $a;
			$ret[$title]['open'] = true;
		}

		if (isset($login->boxpos["{$class}_show"])) {
			foreach ($login->boxpos["{$class}_show"] as $k => $v) {
				if (!isset($ret[$k])) {
					continue;
				}
				$nret[$k] = $ret[$k];
				$nret[$k]['open'] = $v;
			}
			foreach ($ret as $k => $v) if (!isset($nret[$k])) {
				$nret[$k] = $ret[$k];
			}
		} else  {
			$nret = $ret;
		}

		return $nret;

	}

	function print_style_desktop()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$skindir = $login->getSkinDir();
		$col = $login->getSkinColor();
		#[FIXME] Put this css code on a file, NOT inline
		?>
	<style type="text/css">
		.expanded a:hover
		{
			cursor: pointer;
		}

		.trigger a:hover
		{
			cursor: poiner;
		}

		.trigger
		{
			cursor: pointer;
			background: url(<?=$skindir?>/expand.gif);
			border: 1px solid #<?=$col?>;
		}

		.expanded
		{
			cursor: pointer;
			background: url(<?=$skindir?>/expand.gif);
			border: 1px solid #<?=$col?>;
		}

		.show
		{
			position: static;
			display: table;
		}

		.hide
		{
			position: absolute;
			left: -999em;
			height: 1px;
			width: 100px;
			overflow: hidden;
		}

		body
		{
			font-family: arial, sans-serif;
			color: #333;
		}

		#boundary
		{
			border-left: 1px solid #<?=$col?>;
			border-right: 1px solid #<?=$col?>;
			border-bottom: 1px solid #<?=$col?>;
		}

		a
		{
			color: #369;
		}

		h1
		{
			font-family: "trebuchet ms", verdana, sans-serif;
			font-size: 130%;
			border-bottom: 1px solid #999;
		}

		h2
		{
			font-family: "trebuchet ms", verdana, sans-serif;
			font-size: 130%;
			color: #003360;
			background: url(<?=$skindir?>/expand.gif);
			margin-bottom: 0
		}

		h3
		{
			font-family: "trebuchet ms", verdana, sans-serif;
			font-size: 100%;
		}

		p code
		{
			font-size: 110%;
			color: #666;
			font-weight: bold;
		}

		pre
		{
			background: #eee;
			padding: .5em 1em;
			border: 1px solid #<?=$col?>;
		}

		h1 code, h2 code, h3 code
		{
			font-family: "trebuchet ms", verdana, sans-serif;
		}

		h1 code
		{
			font-family: "Trebuchet MS", Arial, Sans-serif;
		}

		#header
		{
			background: #69c;
			border-top: 1px solid #9cf;
			border-bottom: 1px solid #369;
		}

		#content
		{
			font-size: 90%;
		}

		#download
		{
			position: absolute;
			top: 9em;
			width: 15em;
			right: 4em;
		}

		#download ul
		{
			background: #ccf;
			padding: .5em 0 .5em 1.5em;
		}

		#download h2
		{
			background: #369;
			color: #fff;
			font-size: 90%;
			padding: 0.5em;
			margin: .5em 0 0 0;
			border-bottom: 1px solid #036;
			border-right: 1px solid #036;
			border-top: 1px solid #69c;
			border-left: 1px solid #<?=$col?>;
		}

		#download li
		{
			list-style-type: square;
		}

		#header a img
		{
			padding: 5px 1em;
		}
		
		img
		{
			border: 0;
		}
	</style>
	<?
	}

	function print_style_home()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$skindir = $login->getSkinDir();
		$col = $login->getSkinColor();
		#[FIXME] Put this css code on a file, NOT inline
		?>
	<style type="text/css">
		.expanded a:hover
		{
			cursor: pointer;
		}

		.trigger a:hover
		{
			cursor: pointer;
		}

		.trigger
		{
			cursor: pointer;
			background: url(<?=$skindir?>/expand.gif);
			border: 1px solid #<?=$col?>;
			height: 25px;
		}

		.expanded
		{
			cursor: pointer;
			background: url(<?=$skindir?>/expand.gif);
			border: 1px solid #<?=$col?>;
			height: 25px;
		}

		.show
		{
			position: static;
			display: table;
		}

		.hide
		{
			position: absolute;
			left: -999em;
			height: 1px;
			width: 100px;
			overflow: hidden;
		}

		body
		{
			font-family: arial, sans-serif;
			color: #333;
			margin: 0;
			padding: 0;
		}

		#boundary
		{
			margin-left: 20px;
			margin-right: 100px;
			border-left: 1px solid #<?=$col?>;
			border-right: 1px solid #<?=$col?>;
			border-bottom: 1px solid #<?=$col?>;
		}

		a
		{
			color: #369;
		}

		h1
		{
			font-family: "trebuchet ms", verdana, sans-serif;
			font-size: 130%;
			border-bottom: 1px solid #999;
		}

		h2
		{
			font-family: "trebuchet ms", verdana, sans-serif;
			font-size: 130%;
			color: #003370;
			background: url(<?=$skindir?>/expand.gif);
			margin-bottom: 10px;
			margin-top: 10px
		}

		h3
		{
			font-family: "trebuchet ms", verdana, sans-serif;
			font-size: 100%;
		}

		p code
		{
			font-size: 110%;
			color: #666;
			font-weight: bold;
		}

		pre
		{
			background: #eee;
			padding: .5em 1em;
			border: 1px solid #<?=$col?>;
		}

		h1 code, h2 code, h3 code
		{
			font-family: "trebuchet ms", verdana, sans-serif;
		}

		h1 code
		{
			font-family: "Trebuchet MS", Arial, Sans-serif;
		}

		#header
		{
			padding: 0;
			left: 0;
			top: 0;
			background: #69c;
			margin: 0;
			border-top: 1px solid #9cf;
			border-bottom: 1px solid #369;
		}

		#content
		{
			font-size: 90%;
			margin-top: 0;
		}

		#download
		{
			position: absolute;
			top: 9em;
			width: 15em;
			right: 4em;
		}

		#download ul
		{
			background: #ccf;
			margin: 0;
			padding: .5em 0 .5em 1.5em;
		}

		#download h2
		{
			background: #369;
			color: #fff;
			font-size: 90%;
			padding: 0 .5em;
			margin: .5em 0 0 0;
			border-bottom: 1px solid #036;
			border-right: 1px solid #036;
			border-top: 1px solid #69c;
			border-left: 1px solid #<?=$col?>;
		}

		#download li
		{
			list-style-type: square;
		}

		#header a img
		{
			border: 0;
			padding: 5px 1em;
		}
	</style>
	<?
	}

	function drag_drop()
	{
		global $gbl, $sgbl, $login, $ghtml;
		?>
	<script type="text/javascript" src="wz_dragdrop.js"></script>
        <div id="name" style="position:absolute;...">
    <?
	}

	function print_domcollapse($sel)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$skinget = $login->getSkinDir();
		if ($sel == "des") {
			$style = $ghtml->print_style_desktop();
		}
		if ($sel == "hom") {
			$style = $ghtml->print_style_home();
		}
		?>
	<script type="text/javascript">
		dc = {
			triggerElements:'*',	// elements to trigger the effect
			parentElementId:null,   // ID of the parent element (keep null if none)
			uniqueCollapse:false,   // is set to true only one element can be open at a time

			// CSS class names
			trigger:'trigger',
			triggeropen:'expanded',
			hideClass:'hide',
			showClass:'show',
			// pictures and text alternatives
			closedPic:'<?=$skinget?>/plus.gif',
			closedAlt:'expand section',
			openPic:'<?=$skinget?>/minus.gif',
			openAlt:'collapse section',
			right:'right',
			center:'center',
			/* Doesn't work with Safari
            hoverClass:'hover',
        */
			init:function(e)
			{
				var temp;
				if (!document.getElementById || !document.createTextNode) {
					return;
				}
				if (!dc.parentElementId) {
					temp = document.getElementsByTagName(dc.triggerElements);
				} else if (document.getElementById(dc.parentElementId)) {
					temp = document.getElementById(dc.parentElementId).getElementsByTagName(dc.triggerElements);
				} else {
					return;
				}
				dc.tempLink = document.createElement('a');
				dc.tempLink.setAttribute('href', '#');
				dc.tempLink.appendChild(document.createElement('img'));
				for (var i = 0; i < temp.length; i++) {
					if (dc.cssjs('check', temp[i], dc.trigger) || dc.cssjs('check', temp[i], dc.triggeropen)) {
						dc.makeTrigger(temp[i], e);
					}
				}
			},
			makeTrigger:function(o, e)
			{
				var tl = dc.tempLink.cloneNode(true);
				var tohide = o.nextSibling;
				while (tohide.nodeType != 1) {
					tohide = tohide.nextSibling;
				}
				o.tohide = tohide;
				if (!dc.cssjs('check', o, dc.triggeropen)) {
					dc.cssjs('add', tohide, dc.hideClass);
					tl.getElementsByTagName('img')[0].setAttribute('align', dc.right);
					tl.getElementsByTagName('img')[0].setAttribute('src', dc.closedPic);
					tl.getElementsByTagName('img')[0].setAttribute('alt', dc.closedAlt);
					tl.getElementsByTagName('img')[0].setAttribute('title', dc.closedAlt);
					//o.setAttribute('title',dc.closedAlt);
				} else {
					dc.cssjs('add', tohide, dc.showClass);
					tl.getElementsByTagName('img')[0].setAttribute('align', dc.right);
					tl.getElementsByTagName('img')[0].setAttribute('src', dc.openPic);
					tl.getElementsByTagName('img')[0].setAttribute('alt', dc.openAlt);
					tl.getElementsByTagName('img')[0].setAttribute('title', dc.openAlt);
					//o.setAttribute('title',dc.openAlt);
					dc.currentOpen = o;
				}
				//  dc.addEvent(o,'click',dc.addCollapse,false);
				/* Doesn't work with Safari
            dc.addEvent(o,'mouseover',dc.hover,false);
            dc.addEvent(o,'mouseout',dc.hover,false);
            */
				o.insertBefore(tl, o.firstChild);
				dc.addEvent(tl, 'click', dc.addCollapse, false);
				// Safari hacks
				tl.onclick = function()
				{
					return false;
				};
				o.onclick = function()
				{
					return false;
				}
			},
			/* Doesn't work with Safari
        hover:function(e){
            var o=dc.getTarget(e);
            var action=dc.cssjs('check',o,dc.hoverClass)?'remove':'add';
            dc.cssjs(action,o,dc.hoverClass)
        },
        */
			addCollapse:function(e)
			{
				var action,pic;
				// hack to fix safari's redraw bug
				// as mentioned on http://en.wikipedia.org/wiki/Wikipedia:Browser_notes#Mac_OS_X
				if (self.screenTop && self.screenX) {
					window.resizeTo(self.outerWidth + 1, self.outerHeight);
					window.resizeTo(self.outerWidth - 1, self.outerHeight);
				}
				if (dc.uniqueCollapse && dc.currentOpen) {
					dc.currentOpen.getElementsByTagName('img')[0].setAttribute('align', dc.right);
					dc.currentOpen.getElementsByTagName('img')[0].setAttribute('src', dc.closedPic);
					dc.currentOpen.getElementsByTagName('img')[0].setAttribute('alt', dc.closedAlt);
					dc.currentOpen.setAttribute('img', dc.closedAlt);
					dc.cssjs('swap', dc.currentOpen.tohide, dc.showClass, dc.hideClass);
					dc.cssjs('remove', dc.currentOpen, dc.triggeropen);
					dc.cssjs('add', dc.currentOpen, dc.trigger);
				}
				var o = dc.getTarget(e);
				if (o.tohide) {
					if (dc.cssjs('check', o.tohide, dc.hideClass)) {
						o.getElementsByTagName('img')[0].setAttribute('align', dc.right);
						o.getElementsByTagName('img')[0].setAttribute('src', dc.openPic);
						o.getElementsByTagName('img')[0].setAttribute('alt', dc.openAlt);
						o.getElementsByTagName('img')[0].setAttribute('title', dc.openAlt);
						//o.setAttribute('title',dc.openAlt);
						dc.cssjs('swap', o.tohide, dc.hideClass, dc.showClass);
						dc.cssjs('add', o, dc.triggeropen);
						dc.cssjs('remove', o, dc.trigger);
					} else {
						o.getElementsByTagName('img')[0].setAttribute('align', dc.right);
						o.getElementsByTagName('img')[0].setAttribute('src', dc.closedPic);
						o.getElementsByTagName('img')[0].setAttribute('alt', dc.closedAlt);
						o.getElementsByTagName('img')[0].setAttribute('title', dc.closedAlt);
						//o.setAttribute('title',dc.closedAlt);
						dc.cssjs('swap', o.tohide, dc.showClass, dc.hideClass);
						dc.cssjs('remove', o, dc.triggeropen);
						dc.cssjs('add', o, dc.trigger);
					}
					dc.currentOpen = o;
					dc.cancelClick(e);
					//document.getElementById('debug').innerHTML=o.tohide.className;
				} else {
					dc.cancelClick(e);
				}
			},
			/* helper methods */
			getTarget:function(e)
			{
				var target = window.event ? window.event.srcElement : e ? e.target : null;
				if (!target) {
					return false;
				}
				while (!target.tohide && target.nodeName.toLowerCase() != 'body') {
					target = target.parentNode;
				}
				// if(target.nodeName.toLowerCase() != 'a'){target = target.parentNode;} Safari fix not needed here
				return target;
			},
			cancelClick:function(e)
			{
				if (window.event) {
					window.event.cancelBubble = true;
					window.event.returnValue = false;
					return;
				}
				if (e) {
					e.stopPropagation();
					e.preventDefault();
				}
			},
			addEvent: function(elm, evType, fn, useCapture)
			{
				if (elm.addEventListener) {
					elm.addEventListener(evType, fn, useCapture);
					return true;
				} else if (elm.attachEvent) {
					var r = elm.attachEvent('on' + evType, fn);
					return r;
				} else {
					elm['on' + evType] = fn;
				}
			},
			cssjs:function(a, o, c1, c2)
			{
				switch (a) {
					case 'swap':
						o.className = !dc.cssjs('check', o, c1) ? o.className.replace(c2, c1) : o.className.replace(c1, c2);
						break;
					case 'add':
						if (!dc.cssjs('check', o, c1)) {
							o.className += o.className ? ' ' + c1 : c1;
						}
						break;
					case 'remove':
						var rep = o.className.match(' ' + c1) ? ' ' + c1 : c1;
						o.className = o.className.replace(rep, '');
						break;
					case 'check':
						return new RegExp("(^|\\s)" + c1 + "(\\s|$)").test(o.className)
						break;
				}
			}
		};
		dc.addEvent(window, 'load', dc.init, false);
	</script>
		<?
	}

	function print_dialog($alist, $obj)
	{

		global $gbl, $sgbl, $login, $ghtml;
		$buttonpath = get_image_path("/button");
		$lclass = $login->get__table();
		$talist = null;

		$dwidth = "600";
		$dheight = "400";
		if ($login->dialogsize) {
			list($dwidth, $dheight) = explode("x", $login->dialogsize);
		}

		foreach ($alist as $k => $a) {
			if (!csb($k, "__v_dialog")) {
				continue;
			}
			$talist[$k] = $a;
		}
		if (!$talist) {
			return;
		}
		$buttonpath = get_image_path("/button");
		?>

	<div id="comments-dlg" style="visibility:hidden;">
		<div class="x-dlg-hd"><?=$obj->getId()?></div>
		<div class="x-dlg-bd">

			<?php
		$count = 0;
			$first_tab = null;
			foreach ($talist as $k => $a) {
				$descr = $this->getActionDetails($a, null, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
				if ($count === 0) {
					$first_tab = $k;
				}
				$count++;
				?>
				<div id="<?=$k?>-tab" class="x-dlg-tab" title="<?=$descr[2]?>">
					<div id="<?=$k?>-list" class="inner-tab"></div>
				</div>
				<?php } ?>
		</div>
		<div class="x-dlg-ft">
			<div id="dlg-msg">
				<span id="post-error" class="posting-msg"><img src="/img/extjs/warning.gif" width="16" height="16"
															   align="absmiddle"/>&nbsp;<span
						id="post-error-msg"></span></span>
				<span id="post-wait" class="posting-msg"><img src="/img/extjs/default/grid/loading.gif" width="16"
															  height="16" align="absmiddle"/>&nbsp;Updating...</span>
			</div>
		</div>
	</div>



	<link rel="stylesheet" type="text/css" href="/htmllib/extjs/examples/dialog/post.css"/>

	<script>
	var global_formname;
	var Comments = function()
	{
		var dialog, postLink, viewLink, txtComment;
		var tabs, commentsList,  renderer;
		var wait, error, errorMsg;
		var posting = false;

		var global_tabid = '<?=$first_tab?>-tab';

		return {

			init : function()
			{
				// cache some elements for quick access
				// txtComment = Ext.get('comment');
				wait = Ext.get('post-wait');
				error = Ext.get('post-error');
				errorMsg = Ext.get('post-error-msg');

				this.createDialog();

				<?php foreach ($talist as $k => $a) {
				$na = str_replace("display.php", "ajax.php", $a);
				?>
					<?=$k?>Link = Ext.get('<?=$k?>-comment');
					<?=$k?>Link.on('click', function(e)
				{
					e.stopEvent();
					var tabname = global_tabid.substr(0, global_tabid.length - 4);

					if (tabname == '<?=$k?>') {
						var tList = Ext.get('<?=$k?>-list');
						// set up the comment renderer, all ajax requests for commentsList
						// go through this render
						var tum = tList.getUpdateManager();
						//tum.update('/ajax.php?frm_action=updateform&frm_subaction=password');
						tum.update('<?=$na?>&r=' + Math.random());
					}
					tabs.activate('<?=$k?>-tab');
					dialog.show(<?=$k?>Link);
				});
				<?php } ?>

			},

			okComment : function()
			{
				this.submitComment('ok');
			},

			allComment : function()
			{
				if (confirm("Do you really want to apply the above settings to all the objects visible in the top right selectbox?")) {
					this.submitComment('all');
				} else {
					return;
				}
			},

			// submit the comment to the server
			submitComment : function(x)
			{

				if (!check_for_needed_variables(global_formname)) {
					return;
				}
				g_postBtn.disable();
				g_okBtn.disable();
				//g_allBtn.disable();
				wait.radioClass('active-msg');

				var commentSuccess = function(o)
				{
					g_postBtn.enable();
					g_okBtn.enable();
					//g_allBtn.enable();

					var data = renderer.parse(o.responseText);
					//alert(o.responseText);
					data = eval('(' + o.responseText + ')');
					// if we got a comment back
					if (data) {
						if (data.returnvalue == 'success') {
							if (data.refresh) {
								top.mainframe.window.location.reload();
							}
							if (x == 'ok' || x == 'all') {
								dialog.hide();
							}
						} else {
							var tabname = global_tabid.substr(0, global_tabid.length - 4);
							var tList = Ext.get(tabname + '-list');
							// set up the comment renderer, all ajax requests for commentsList
							// go through this render
							var tum = tList.getUpdateManager();
							//tum.update('/ajax.php?frm_action=updateform&frm_subaction=password');
							tum.update('/ajax.php?r=' + Math.random() + "&" + data.url);
						}
						wait.removeClass('active-msg');
						renderer.append(data.message);
						return data.returnvalue;
					} else {
						error.radioClass('active-msg');
						errorMsg.update(o.responseText);
						//eval(tabname + "um.update('/ajax.php?frm_action=updateform&frm_subaction=password');");

					}
				};

				var commentFailure = function(o)
				{
					g_postBtn.enable();
					g_allBtn.enable();
					g_okBtn.enable();
					error.radioClass('active-msg');
					errorMsg.update('Unable to connect.');
				};

				if (x == 'all') {
					var ur = '/ajax.php?frm_change=updateall'
				} else {
					var ur = '/ajax.php'
				}

				Ext.lib.Ajax.formRequest(global_formname, ur, {success: commentSuccess, failure: commentFailure});
			},

			createDialog : function()
			{
				dialog = new Ext.BasicDialog("comments-dlg", {
					autoTabs:true,
					width:<?=$dwidth?>,
					height:<?=$dheight?>,
					shadow:true,
					minWidth:300,
					minHeight:300
				});
				dialog.addKeyListener(27, dialog.hide, dialog);
				g_okBtn = dialog.addButton('OK', this.okComment, this);
				dialog.addButton('Cancel', dialog.hide, dialog);
				g_postBtn = dialog.addButton('Apply', this.submitComment, this);
				g_allBtn = dialog.addButton('All Update', this.allComment, this);


				// clear any messages and indicators when the dialog is closed
				dialog.on('hide', function()
				{
					wait.removeClass('active-msg');
					error.removeClass('active-msg');
					//txtComment.dom.value = '';
				});

				// stoe a refeence to the tabs
				tabs = dialog.getTabs();

				// auto fit the comment box to the dialog size
				var sizeTextBox = function(x)
				{
					//txtComment.setSize(dialog.size.width-44, dialog.size.height-264);
					if (x != 'init') {
						Ext.lib.Ajax.request('post', '/ajax.php', {success: null, failure: null }, 'frm_action=update&frm_subaction=dialogsize&frm_<?=$lclass?>_c_dialogsize=' + dialog.size.width + 'x' + dialog.size.height);
					}
				};
				sizeTextBox('init');
				dialog.on('resize', sizeTextBox);

				// hide the post button if not on Post tab
				tabs.on('tabchange', function(panel, tab)
				{
					// postBtn.setVisible(tab.id == 'post-tab');
					global_tabid = tab.id;
				});

				<?php foreach ($talist as $k => $a) {
				if (!csb($k, "__v_dialog")) {
					continue;
				}
				$na = str_replace("display.php", "ajax.php", $a);
				?>
					<?=$k?>List = Ext.get('<?=$k?>-list');
				// set up the comment renderer, all ajax requests for commentsList
				// go through this render
				renderer = new CommentRenderer(<?=$k?>List);
				var <?=$k?>um = <?=$k?>List.getUpdateManager();
					<?=$k?>um.setRenderer(renderer);

				// lazy load the comments when the view tab is activated
				tabs.getTab('<?=$k?>-tab').on('activate', function()
				{
						<?=$k?>um.update('<?=$na?>&r=' + Math.random());
				});
				<?php

			}
				?>

			}
		};
	}();

	// This class handles rendering JSON into comments
	var CommentRenderer = function(list)
	{
		// create a template for each JSON object
		var tpl = new Ext.DomHelper.Template('{lx__form}');

		this.parse = function(json)
		{
			try {
				return eval('(' + json + ')');
			} catch(e) {
			}
			return null;
		};

		// public render function for use with UpdateManager
		this.render = function(el, response)
		{
			var data = this.parse(response.responseText);
			if (!data || !data.lx__form || data.lx__form.length < 1) {
				el.update('the_server_didnt_return_a_form: error:' + response.responseText);
				return;
			}
			// clear loading
			el.update('');

			if (data.allbutton) {
				g_allBtn.enable();
			} else {
				g_allBtn.disable();
			}

			if (data.ajax_dismiss) {
				g_allBtn.setVisible(false);
				g_okBtn.setVisible(false);
				g_postBtn.setVisible(false);
			} else {
				g_allBtn.setVisible(true);
				g_okBtn.setVisible(true);
				g_postBtn.setVisible(true);
			}

			global_need_list = new Array();
			global_match_list = new Array();
			for (v in data.ajax_need_var) {
				global_need_list[v] = data.ajax_need_var[v];
			}
			for (v in data.ajax_match_var) {
				global_match_list[v] = data.ajax_match_var[v];
			}
			global_formname = data.ajax_form_name;

			this.append(data);
		};

		// appends a comment
		this.append = function(data)
		{
			tpl.append(list.dom, data);
		};
	};

	Ext.EventManager.onDocumentReady(Comments.init, Comments, true);
	</script>
			<?php

	}

	function print_drag_drop($obj, $ret, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$lclass = $login->get__table();
		$skindir = $login->getSkinDir();
		$col = $login->getSkinColor();
		$plus = "$skindir/plus.gif";
		$minus = "$skindir/minus.gif";
		$buttonpath = get_image_path() . "/button/";

		?>

	<script>
		(function()
		{

			var Dom = YAHOO.util.Dom;
			var Event = YAHOO.util.Event;
			var DDM = YAHOO.util.DragDropMgr;

			//////////////////////////////////////////////////////////////////////////////
			// example app
			//////////////////////////////////////////////////////////////////////////////
			YAHOO.example.DDApp = {
				init: function()
				{

					var dd;

					dd = new YAHOO.util.DDTarget("mainbody");

					<?php foreach ($ret as $title => $a) {
					$nametitle = strfrom($title, "__title_");
					print("dd = new YAHOO.example.DDList('item_$nametitle');\n");
					print("dd.setXConstraint(0, 0, 0);\n");
					print("dd.setHandleElId('handle_$nametitle' );");
				}
					?>

					//Event.on("showButton", "click", this.showOrder);
					//Event.on("switchButton", "click", this.switchStyles);
				},

				showOrder: function()
				{
				},

				switchStyles: function()
				{
				}
			};

			//////////////////////////////////////////////////////////////////////////////
			// custom drag and drop implementation
			//////////////////////////////////////////////////////////////////////////////

			YAHOO.example.DDList = function(id, sGroup, config)
			{

				YAHOO.example.DDList.superclass.constructor.call(this, id, sGroup, config);

				this.logger = this.logger || YAHOO;
				var el = this.getDragEl();
				Dom.setStyle(el, "opacity", 0.67); // The proxy is slightly transparent

				this.goingUp = false;
				this.lastY = 0;
			};

			YAHOO.extend(YAHOO.example.DDList, YAHOO.util.DDProxy, {

				startDrag: function(x, y)
				{
					this.logger.log(this.id + " startDrag");

					// make the proxy look like the source element
					var dragEl = this.getDragEl();
					var clickEl = this.getEl();
					Dom.setStyle(clickEl, "visibility", "hidden");

					//dragEl.innerHTML = clickEl.innerHTML;
					Dom.setStyle(dragEl, "color", Dom.getStyle(clickEl, "color"));
					Dom.setStyle(dragEl, "backgroundColor", Dom.getStyle(clickEl, "backgroundColor"));
					Dom.setStyle(dragEl, "border", "1px solid #<?echo $col?>");
				},

				endDrag: function(e)
				{

					var srcEl = this.getEl();
					var proxy = this.getDragEl();

					// Show the proxy element and animate it to the src element's location
					Dom.setStyle(proxy, "visibility", "");
					var a = new YAHOO.util.Motion(proxy, {
						points: {
							to: Dom.getXY(srcEl)
						}
					}, 0.2, YAHOO.util.Easing.easeOut);
					var proxyid = proxy.id;
					var thisid = this.id;

					// Hide the proxy and show the source element when finished with the animation
					a.onComplete.subscribe(function()
					{
						Dom.setStyle(proxyid, "visibility", "hidden");
						Dom.setStyle(thisid, "visibility", "");
					});
					a.animate();
				},

				onDragDrop: function(e, id)
				{

					// If there is one drop interaction, the li was dropped either on the list,
					// or it was dropped on the current location of the source element.


					var page = document.getElementById('show_page');
					var out = parseList(page, "List 1");
					var url = 'frm_<?=$lclass?>_c_title_class=<?=$class ?>\&frm_action=update\&frm_subaction=boxpos\&frm_<?=$lclass?>_c_page=' + out;
					var request = YAHOO.util.Connect.asyncRequest('post', "/ajax.php", callback, url);

				},

				onDrag: function(e)
				{

					// Keep track of the direction of the drag for use during onDragOver
					var y = Event.getPageY(e);

					if (y < this.lastY) {
						this.goingUp = true;
					} else if (y > this.lastY) {
						this.goingUp = false;
					}

					this.lastY = y;
				},

				onDragOver: function(e, id)
				{

					var srcEl = this.getEl();
					var destEl = Dom.get(id);

					// We are only concerned with list items, we ignore the dragover
					// notifications for the list.
					if (destEl.id == 'mainbody') {
						return;
					}
					if (destEl.nodeName.toLowerCase() == "div") {
						var orig_p = srcEl.parentNode;
						var p = destEl.parentNode;

						if (this.goingUp) {
							p.insertBefore(srcEl, destEl); // insert above
						} else {
							p.insertBefore(srcEl, destEl.nextSibling); // insert below
						}

						DDM.refreshCache();
					}
				}
			});

			Ext.EventManager.onDocumentReady(YAHOO.example.DDApp.init, YAHOO.example.DDApp, true);

		})();

	</script>
		<?php

	}

	function print_div_button($actionlist, $type, $imgflag, $key, $url, $ddate = null)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$obj = $gbl->__c_object;
		$psuedourl = NULL;
		$target = NULL;

		$buttonpath = get_image_path() . "/button/";

		$linkflag = true;
		if (csa($key, "__var_")) {
			$privar = strfrom($key, "__var_");
			if (!$obj->checkButton($privar)) {
				$linkflag = false;
			}
		}

		$complete = $this->resolve_int_ext($url, $psuedourl, $target);

		if ($complete) {
			$this->get_post_from_get($url, $path, $post);
			$descr = $this->getActionDescr($path, $post, $class, $name, $identity);
			$complete['name'] = str_replace("<", "&lt;", $complete['name']);
			$complete['name'] = str_replace(">", "&gt;", $complete['name']);
			$name = $complete['name'];
			$bname = $complete['bname'];
			$descr[1] = $complete['name'];
			$descr[2] = $complete['name'];
			$descr['desc'] = $complete['name'];
			$file = $class;
			if (lxfile_exists("img/custom/$bname.gif")) {
				$image = "/img/custom/$bname.gif";
			} else {
				$image = "/img/image/collage/button/custom_button.gif";
			}
			$__t_identity = $identity;
		} else {
			$url = str_replace("[%s]", $obj->nname, $url);

			$descr = $this->getActionDetails($url, $psuedourl, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
		}

		$this->save_non_existant_image($image);

		$str = randomString(8);
		$form_name = $this->createEncForm_name("{$file}_{$name}_$str");
		$form_name = fix_nname_to_be_variable($form_name);

		if (csb($url, "http:/")) {
			$formmethod = "get";
		} else {
			$formmethod = $sgbl->method;
		}
		// Use get always. Only in forms should post be used.
		$formmethod = 'get';

		$dividentity = "searchdiv_{$descr['desc']}";

		$dividentity = str_replace(" ", "_", $dividentity);
		$dividentity = strtolower($dividentity);
		for ($i = 0; $i < 10; $i++) {
			if (!isset($actionlist[$dividentity])) {
				break;
			}
			$dividentity = "{$dividentity}$i";
		}
		?>
	<td valign="middle" align="left" width=5>
		<div id="<?php echo $dividentity ?>" style="visibility:visible;display:block">
			<form method=<?=$formmethod ?> name=form_<?=$form_name ?> action=<?=$path?> <?=$target ?>>
				<?php
		$this->print_input_vars($post);
				?>
				<?
				$this->print_div_for_divbutton($key, $imgflag, $linkflag, $form_name, $name, $image, $descr);

				?>
			</form>
		</div>
	</td>
				<?php

		return $dividentity;
	}

	function print_action_block($obj, $class, $alist, $num)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$lclass = $login->get__table();
		$skindir = $login->getSkinDir();
		$talist = $alist;
		$ret = $this->create_action_block($class, $alist);
		$col = $login->getSkinColor();
		$plus = "$skindir/plus.gif";
		$minus = "$skindir/minus.gif";
		$buttonpath = get_image_path() . "/button/";

		if ($sgbl->isDebug()) {
			$outputdisplay = 'inline';
		} else {
			$outputdisplay = 'none';
		}

		if ($login->getSpecialObject('sp_specialplay')->isOn('enable_ajax')) {
			$this->print_dialog($talist, $obj);
			$this->print_drag_drop($obj, $ret, $class);
		}

		if ($sgbl->isBlackBackground()) {
			$backgimage = "$skindir/black.gif";
			$minus = "$skindir/black.gif";
			$plus = "$skindir/black.gif";
			$col = "333";
		} else {
			$backgimage = "$skindir/expand.gif";
		}
		?>

	<style>

		div.section, div#createNew
		{
			border: 1px solid #<?echo $col?>;
			margin: 9px 5px;
			padding: 0px 0px 10px 0px;
			width: 520;
		}

		div#createNew input
		{
			margin-left: 5px;
		}

		div#createNew h3, div.section h3
		{
			font-size: 12px;
			padding: 2px 5px;
			margin: 0 0 10px 0;
			display: block;
			font-family: "trebuchet ms", verdana, sans-serif;
			color: #003360;
			background: url(<?=$skindir?>/expand.gif);
			border-bottom: 1px solid #<?echo $col?>;
		}

		div.section h3
		{
			cursor: move;
		}

		div.demo div.example span
		{
			margin: 0px;
			margin-bottom: 0px;
			padding: 0px;
			font-size: 1.0em;
			text-align: center;
			display: block;
		}

		div.demo
		{
			margin: 0px;
			overflow: visible;
			position: relative;
			width: 100%;
		}

		h1
		{
			margin-bottom: 0;
			font-size: 18px
		}
	</style>
	<div id="show_page">
		<?php

		if (!$login->getSpecialObject('sp_specialplay')->isOn('enable_ajax')) {
			$dragstring = "Enable Ajax to Drag";
		} else {
			$dragstring = "Drag";
		}

		$div_id_list = null;
		$completedivlist = null;

		$count = 1;

		foreach ($ret as $title => $a) {
			$count++;
			if (!isset($a[$title])) {
				continue;
			}
			$dispstring = "display:none";
			if ($a['open']) {
				$dispstring = "";
			}
			unset($a['open']);
			$nametitle = strfrom($title, "__title_");
			?>

			<div id="item_<?="$nametitle" ?>" class="section">
				<table cellpadding=0 cellspacing=0>
					<tr class=handle id="handle_<?=$nametitle ?>" style="background:url(<?=$backgimage?>)"
						onMouseover="document.getElementById('font_<?=$nametitle ?>').style.visibility='visible'; this.style.background='url<?=$backgimage?>()'"
						onMouseout="document.getElementById('font_<?=$nametitle?>').style.visibility='hidden'; this.style.background='url(<?=$backgimage?>)'">
						<td nowrap style='cursor: move'><font id=font_<?=$nametitle?> style='visibility:hidden'>
							&nbsp;<?=$dragstring?> </font></td>
						<td width=100% style="cursor: move; " align=center><font
								style='font-weight: bold'><?= $a[$title]?></font></td>
						<td nowrap style='cursor: move'><font id=font_<?=$nametitle?> style='visibility:hidden'>
							&nbsp;<?=$dragstring?> </font></td>
						<td class=handle style='cursor: pointer'
							onclick="blindUpOrDown('<?=$lclass?>', '<?=$class ?>', '<?=$skindir?>', '<?=$nametitle ?>')">
							<img id=img_<?=$nametitle?> name=img_<?=$nametitle ?> src=<?=$minus?>></td>
					</tr>
				</table>
				<div style="<?=$dispstring?>;" id="internal_<?=$nametitle?>">
					<table cellpadding="10" cellspacing="4" style="padding:1 2 1 1">
						<tr>
							<?
							array_shift($a);
							$n = 0;
							foreach ($a as $k => $u) {
								$n++;
								if ($n === $num) {
									print("</tr><tr>");
									$n = 1;
								}
								print("<td>");
								$ret = $this->print_div_button($completedivlist, "block", true, $k, $u);
								$completedivlist[$ret] = $ret;
								$div_id_list[$nametitle][$ret] = $ret;
								print("</td>");
							}
							?>
							</td></tr>
					</table>
				</div>
			</div>
			<?
		}
		print("</td></tr></table>");
		?>
	</div>

		<?php

		print("<script>");
		$count = 0;
		print("global_action_box = new Array()\n");
		foreach ($div_id_list as $k => $v) {
			print("global_action_box[$count] = new Array();\n");
			print("global_action_box[$count][0] = '$k';\n");
			$j = 1;
			foreach ($v as $kk => $vv) {
				print("global_action_box[$count][$j] = '$vv';\n");
				$j++;
			}
			$count++;
		}
		print("</script>");
	}

	function print_action_blockold($title, $alist, $num)
	{
		$i = 0;
		$total = $num;

		/// This is a mighty hack... The first element of $alist is supposed to be the main title. You use it as the first title and unset the variable. This is a hack from the previous code where the first title was preset here itself.

		foreach ($alist as $k => $a) {
			if (csb($k, "__title")) {
				$title = $a;
				unset($alist[$k]);
				break;
			}
		}

		?>
        <fieldset width=100% style='border: 0px; border-top: 1px solid #d0d0d0'><legend
			style='font-weight:normal;border:0px'><b> <font color=#303030
															style='font-weight:normal'><?=$title ?> </font></b></legend>
            <table cellpadding="2" cellspacing="7" border="0" width="95%">
            <tr align=left> <td align=left>
            <?php
		foreach ($alist as $k => $u) {
		$i++;
		if ($i % $total === 1) {
			print("</td> </tr> <tr align=left> <td align=left>");
		}

		if (csb($k, "__title")) {
			if ($i <= $total) {
				for (; $i <= $total; $i++) {
					print("</td><td width=60>&nbsp;");
				}
			}
			$i = 0;
			?>
                </td> </tr> </table>
                </fieldset><fieldset style='font-weight:normal;border: 0px; border-top: 1px solid #d0d0d0'><legend
					style='font-weight:normal'><b> <font color=#303030 style='font-weight:normal'><?=$u ?> </font> </b>
			</legend>
                <table cellspacing=7 width=95% border=0> <tr align=left> <td align=left>
                <?php
				continue;
		}
		$this->print_div_button(null, "block", true, $k, $u);
	}
		if ($i <= $total) {

			for (; $i <= $total; $i++) {
				print("</td><td width=40>&nbsp;");
			}
		}


		?>
            </td> </tr>
            </table>
                </fieldset>
            <br> <br>
			<?php

	}

	function cginum($key)
	{
		return (isset($this->__http_vars[$key])) ? $this->__http_vars[$key] : 0;
	}

	function cgiset($key, $value)
	{
		// Needs to be Fixed.
		$this->__http_vars[$key] = $value;
	}

	function frmiset($key)
	{
		return (isset($this->__http_vars[$key])) ? 1 : 0;
	}

	function iset($key)
	{
		return (isset($this->__http_vars[$key])) ? 1 : 0;
	}


	function get_image($path, $class, $variable, $extension)
	{
		return add_http_host($this->get_image_without_host($path, $class, $variable, $extension));
	}

	function createMissingName($name)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$val = 0;
		if ($sgbl->isKloxo()) {
			return '/img/general/default/default.gif';
		}

		for ($i = 0; $i < strlen($name); $i++) {
			$val += ord($name[$i]);
		}

		$val = $val % 10;

		return "/img/general/default/default_$val.gif";
	}

	function get_image_without_host($path, $class, $variable, $extension)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$variable = strtolower($variable);
		$class = strtolower($class);

		$realv = $variable;

		//hack hack...
		if ($class === 'installapp') {
			if (strstr($variable, "addform")) {
				$variable = strfrom($variable, "_");
			}
		}

		if (csa($variable, "_nn_")) {
			$variable = substr($variable, 0, strpos($variable, '_nn_'));
		}

		$name = ($class) ? $class . "_" . $variable : $variable;

		$fpath = $path . "/" . $name . $extension;


		if (lfile_exists(getreal($fpath))) {
			return $fpath;
		}

		$name = $variable;

		$fnpath = $path . "/" . $name . $extension;

		if (lfile_exists(getreal($fnpath))) {
			return $fnpath;
		}

		$name = substr($variable, 0, strpos($variable, "_"));
		$fnpath = $path . "/" . $name . $extension;

		if (lfile_exists(getreal($fnpath))) {
			return $fnpath;
		}

		$name = substr($variable, strrpos($variable, "_") + 1);
		$fnpath = $path . "/" . $name . $extension;

		if (lfile_exists(getreal($fnpath))) {
			return $fnpath;
		}

		if ($realv === 'show') {
			return $this->get_image_without_host($path, $class, "list", $extension);
		}

		if (csb($realv, "update_")) {
			$qname = strfrom($realv, "update_");
			$qname = "updateform_$qname";
			return $this->get_image_without_host($path, $class, $qname, $extension);
		}

		$name = strfrom("{$class}_$variable", "all_");
		$fnpath = "$path/$name$extension";

		if (lfile_exists(getreal($fnpath))) {
			return $fnpath;
		}

		if ($sgbl->dbg < 0) {
			$imgname = $this->createMissingName($fpath);
			return $imgname;
		}

		return $fpath;

	}

	function save_non_existant_image($path)
	{
		global $gbl, $sgbl, $login, $ghtml;

		return; #[FIXME]

		// We need only the form images, and the normal non form action images need not be saved.
		if (!csa($path, "list") && !csa($path, "form")) {
			return;
		}

		if ($sgbl->dbg <= 1) {
			return;
		}

		if (lfile_exists(getreal($path))) {
			return;
		}

		$cont = null;
		$icon = $login->getSpecialObject('sp_specialplay')->icon_name;

		$file = "__path_program_htmlbase/$icon.missing_image.txt";

		if (lfile_exists($file)) {
			$cont = lfile($file);
			foreach ($cont as $k => &$__c) {
				$__c = trim($__c);
				if (!$__c) {
					unset($cont[$k]);
				}
			}
		}
		$cont = array_push_unique($cont, $path);
		$cont = implode("\n", $cont);
		$cont .= "\n";
		lfile_put_contents($file, $cont);
	}

	function get_date()
	{
		return array(date('d'), date('m'), date('Y'));
	}

	function get_post_from_get($url, &$path, &$post)
	{
		$post = NULL;
		$array = parse_url($url);
		$path = '';
		if (isset($array['host'])) {
			$path .= $array['scheme'] . '://' . $array['host'];
		}
		if (isset($array['port'])) {
			$path .= ':' . $array['port'];
		}
		if (isset($array['path'])) {
			$path .= $array['path'];
		}
		if (isset($array['query'])) {
			parse_str($array['query'], $post);
		}

		return $post;
	}

	function createCurrentParam($class)
	{
		$param = null;

		foreach ($this->__http_vars as $key => $val) {
			if (csb($key, "__m_")) {
				$param[$key] = $val;
				continue;
			}
			if (!csa($key, "_c_")) {
				continue;
			}

			$realname = substr($key, strlen('frm_'));
			$this->get_htmlvar_details($key, $newclass, $variable, $extra, $htmlvalue);

			$param[$variable] = $val;
		}
		check_for_select_one($param);
		return $param;
	}

	function get_form_variable_name($descr)
	{
		return getNthToken($descr, 1);
	}

	function fix_stuff_or_class($stuff, $variable, &$class, &$value)
	{
		$value = null;
		if (is_object($stuff)) {
			$class = lget_class($stuff);
			lxclass::resolve_class_differences($class, $variable, $dclass, $dvariable);
			if ($dclass != $class && cse($dclass, "_b")) {
				$value = $stuff->$dclass->$dvariable;
			} else {
				if ($stuff->isQuotaVariable($variable)) {
					$value = $stuff->priv->$variable;
				} elseif ($stuff->isListQuotaVariable($variable)) {
					$value = $stuff->listpriv->$variable;
				} elseif (!cse($variable, "_f")) {
					$value = $stuff->getVariable($variable);
				}
			}
		} else {
			$class = $stuff;
		}

		if (!is_array($value)) {
			$value = htmlspecialchars($value);
		}
	}

	function print_file_permissions($ffile)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$ffile->getPermissions($perm);

		if ($perm[0] === '') {
			$user = 0;
		} else {
			$user = $perm[0];
		}
		if ($perm[1] === '') {
			$group = 0;
		} else {
			$group = $perm[1];
		}
		if ($perm[2] === '') {
			$other = 0;
		} else {
			$other = $perm[2];
		}

		$imgheadleft = $login->getSkinDir() . '/top_lt.gif';
		$imgheadright = $login->getSkinDir() . '/top_rt.gif';
		$imgheadbg = $login->getSkinDir() . 'top_bg.gif';
		$imgtopline = $login->getSkinDir() . '/top_line.gif';
		$tablerow_head = $login->getSkinDir() . '/tablerow_head.gif';

		?>
	<br/>
	<br/>
	<script>
		function sendchmode(a, b)
		{
			b.frm_ffile_c_file_permission_f.value = a.user.value + a.group.value + a.other.value;
			if (a.frm_ffile_c_recursive_f.checked) {
				if (confirm("Do You Really want to set this permission Recursively?")) /* [FIXME] Harcode string translate */
				{
					b.frm_ffile_c_recursive_f.value = 'on';
				} else {
					b.frm_ffile_c_recursive_f.value = 'off';
				}
			} else {
				b.frm_ffile_c_recursive_f.value = 'off';
			}
			b.submit();
		}
	</script>

	<form name="frmsendchmod" action="display.php">
		<input type="hidden" name="frm_ffile_c_file_permission_f">
		<?php
				$post['frm_o_o'] = $this->__http_vars['frm_o_o'];
		$ghtml->print_input_vars($post);
		?>
		<input type="hidden" name="frm_ffile_c_recursive_f" value="Off">
		<input type="hidden" name="frm_action" value="update">
		<input type="hidden" name="frm_subaction" value="perm">
	</form>


	<table cellpadding="0" cellspacing="0" border="0" width="325">
		<tr>
			<td width="60%" valign="bottom">
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td width="100%" height="2" background="<?=$imgtopline; ?>"></td>
					</tr>
				</table>
			</td>
			<td align="right">
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td>
							<img src="<?=$imgheadleft; ?>">
						</td>
						<td nowrap width="100%" background="<?=$imgheadbg; ?>">
							<b><font color="#ffffff">Change
								Permissions</font></b> <? #[FIXME] Harcode translation string ?>
						</td>
						<td>
							<img src="<?=$imgheadright; ?>">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

        <form name="chmod" method=<?=$sgbl->method ?> action="">
            <table cellpadding="0" cellspacing="0" border="0" width="325">
				<tr style='background:url(<?=$tablerow_head ?>)'>
					<td width="100" class="col"></td>
					<td width=75 align=center>User</td><? #[FIXME] Harcode translation string ?>
					<td width=75 align=center>Group</td><? #[FIXME] Harcode translation string ?>
					<td align=center width=75>Others</td><? #[FIXME] Harcode translation string ?>
				</tr>
				<tr style='background:url(<?=$tablerow_head ?>)'>
					<td width=100 class="col"></td>
					<td align="center">
						<input type="checkbox" name="userall" onclick="allrights(document.chmod,this,'user');">
					</td>
					<td align="center">
						<input type="checkbox" name="groupall" onclick="allrights(document.chmod,this,'group');">
					</td>
					<td align="center">
						<input type="checkbox" name="otherall" onclick="allrights(document.chmod,this,'other');">
					</td>
				</tr>
			</table>
            <table cellpadding="0" cellspacing="0" border="0" width="325">
				<tr class="tablerow0">
					<td class="col" width="100">Write</td><? #[FIXME] Harcode translation string ?>
					<td align="center">
						<input type="checkbox" name="wu" onclick="changerights(document.chmod,this,'user',2);">
					</td>
					<td align="center">
						<input type="checkbox" name="wg" onclick="changerights(document.chmod,this,'group',2);">
					</td>
					<td align="center">
						<input type="checkbox" name="wo" onclick="changerights(document.chmod,this,'other',2);">
					</td>
				</tr>
				<tr class="tablerow1">
					<td class="col" width="100">Execute</td><? #[FIXME] Harcode translation string ?>
					<td width="75" align="center">
						<input type="checkbox" name="eu" onclick="changerights(document.chmod,this,'user',1);">
					</td>
					<td width="75" align="center">
						<input type="checkbox" name="eg" onclick="changerights(document.chmod,this,'group',1);">
					</td>
					<td width="75" align="center">
						<input type="checkbox" name="eo" onclick="changerights(document.chmod,this,'other',1);">
					</td>
				</tr>
				<tr class="tablerow0">
					<td class="col" width="100">Read</td><? #[FIXME] Harcode translation string ?>
					<td align="center">
						<input type="checkbox" name="ru" onclick="changerights(document.chmod,this,'user',4);">
					</td>
					<td align="center">
						<input type="checkbox" name="rg" onclick="changerights(document.chmod,this,'group',4);">
					</td>
					<td align="center">
						<input type="checkbox" name="ro" onclick="changerights(document.chmod,this,'other',4);">
					</td>
				</tr>
			</table>
            <table cellpadding="0" cellspacing="0" border="0" width="325">
				<!--<tr><td colspan=4 bgcolor="#ffffff" height=2></td></tr>
                <tr><td colspan=4 bgcolor="#a5c7e7" height=1></td></tr>-->
				<tr>
					<td colspan="4" bgcolor="#ffffff" height="2"></td>
				</tr>
				<tr class="tablerow1">
					<td class="tableheadtext" width="100">&nbsp;&nbsp;Total
					</td> <? #[FIXME] Harcode translation string ?>
					<td align="center" width="75">
						<input type="text" size="1" name="user" class="textchmoddisable" value="<?=$user; ?>">
					</td>
					<td width="75" align="center">
						<input type="text" size="1" name="group" class="textchmoddisable" value="<?=$group; ?>">
					</td>
					<td width="75" align="center">
						<input type="text" size="1" name="other" class="textchmoddisable" value="<?=$other; ?>">
					</td>
				</tr>
				<!--<tr><td colspan=4 bgcolor="#ffffff" height=2></td></tr>
                <tr><td colspan=4 bgcolor="#a5c7e7" height=1></td></tr>-->

				<tr>
					<td colspan="3">
						&nbsp; <b> Change Permssion Recursively <b> <? #[FIXME] Harcode translation string ?>
					</td>
					<td>
						<input type="checkbox" name="frm_ffile_c_recursive_f">
					</td>
				</tr>

				<tr>
					<td colspan="4" bgcolor="#ffffff" height="4"></td>
				</tr>
				<tr>
					<td colspan="4" align="right">
						<input type="button" onclick="sendchmode(document.chmod,document.frmsendchmod)"
							   class="submitbutton" name="change" value="Change">
					</td>
				</tr>
				<tr>
					<td colspan="2" bgcolor="#ffffff" height="4"></td>
				</tr>
				<tr>
					<td colspan="4" style='background:url(<?=$imgtopline?>)' height="1"></td>
				</tr>
			</table>
    </form>

    <script>
		document.chmod.user.disabled = true;
		document.chmod.group.disabled = true;
		document.chmod.other.disabled = true;

		setpermission(document.chmod, 'user',<?=$user;?>);
		setpermission(document.chmod, 'group',<?=$group; ?>);
		setpermission(document.chmod, 'other',<?=$other; ?>);
	</script>

    <?php

    }

	function object_variable_file($stuff, $variable)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$sgbl->method = 'post';

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);
		$descr = $this->get_classvar_description_after_overload($class, $variable);

		$rv = new FormVar();
		$rv->name = $variable;
		$rv->desc = $descr[2];
		$rv->type = 'file';
		return $rv;
	}

	function object_variable_fileselect($stuff, $variable, $opt = null)
	{
		$valstring = null;

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);

		$rvr = new formVar();

		if ($value) {
			$rvr->value = $value;
		}

		$needstr = null;
		$descr = $this->get_classvar_description_after_overload($class, $variable);

		$desc = getNthToken($descr[2], 1);
		if (char_search_a($descr[0], 'n')) {
			$rvr->need = 'yes';
		}

		$estring = null;
		if ($opt) {
			foreach ($opt as $key => $val) $rvr->$key = $val;
		}

		$rvr->name = "frm_{$class}_c_$variable";
		$rvr->desc = $desc;
		$rvr->type = 'fileselect';
		return $rvr;
	}

	function object_variable_image($stuff, $variable, $opt = null)
	{
		$valstring = null;

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);

		$needstr = null;
		$descr = $this->get_classvar_description_after_overload($class, $variable);

		$desc = getNthToken($descr[2], 1);

		$rvr = new formVar();
		$estring = null;
		if ($opt) {
			foreach ($opt as $key => $val) $rvr->$key = $val;
		}

		$rvr->name = "frm_{$class}_c_$variable";
		$rvr->desc = $desc;
		$rvr->type = 'image';

		return $rvr;
	}

	function url_encode($value) #[FIXME] Remove this useless function and rewrite the code
	{
		return urlencode($value);
	}

	function object_variable_modify($stuff, $variable, $opt = null)
	{
		$valstring = null;

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);

		$rvr = new FormVar();

		if ($value) {
			$rvr->value = $value;
		}

		$needstr = null;
		$descr = $this->get_classvar_description_after_overload($class, $variable);

		$desc = getNthToken($descr[2], 1);
		if (char_search_a($descr[0], 'n')) {
			$rvr->need = 'yes';
		}

		$estring = null;
		if ($opt) {
			foreach ($opt as $key => $value) if ($key === 'postvar') {
				$postvar = new FormVar();
				$postvar->option = $value['val'][1];
				$postvar->name = "frm_{$class}_c_{$value['var']}";
				$postvar->type = 'select';
				$rvr->postvar = $postvar;
			} else {
				$rvr->$key = $value;
			}
		}

		$rvr->name = "frm_{$class}_c_$variable";
		$rvr->desc = $desc;
		$rvr->type = 'modify';
		return $rvr;
	}

	function object_variable_show_select($stuff, $variable, $list)
	{
		$value = null;


		$rvr = new FormVar();
		$rvr->name = $variable;
		$rvr->desc = 'Show';
		$rvr->type = 'select';

		$rvr->option = $this->object_variable_option(false, $list, $value, true);

		return $rvr;
	}

	function is_special_url($stuff)
	{
		return is_object($stuff);
	}

	function is_special_variable($stuff)
	{
		return is_object($stuff);
	}

	function object_variable_select($stuff, $variable, $list, $assoc = false)
	{
		$value = null;

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);

		if (!is_object($stuff)) {
			$flist = $assoc ? array_keys($list) : $list;
			$value = getFirstFromList($flist);
		}

		if ($this->is_special_variable($list)) {
			$descr = $list->descr;
			$list = $list->list;
		} else {
			$descr = $this->get_classvar_description_after_overload($class, $variable);
		}

		$desc = $this->get_form_variable_name($descr[2]);
		$string = $this->do_object_variable_select($class, $variable, $desc, $list, $value, $assoc);
		return $string;
	}

	function do_object_variable_select($class, $variable, $desc, $list, $value, $assoc = false)
	{
		$rvr = new FormVar();
		$rvr->name = "frm_{$class}_c_$variable";
		$rvr->desc = $desc;
		$rvr->type = 'select';

		$rvr->option = $this->object_variable_option(false, $list, $value, $assoc);

		return $rvr;
	}

	function object_variable_multiselect($stuff, $variable, $list)
	{
		$value = null;

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);


		$descr = $this->get_classvar_description_after_overload($class, $variable);

		$desc = $this->get_form_variable_name($descr[2]);

		$string = $this->do_object_variable_multiselect($class, $variable, $desc, $list, $value);
		return $string;
	}

	function do_object_variable_multiselect($class, $variable, $desc, $list, $value)
	{
		$ret = new FormVar();
		$ret->name = "frm_{$class}_c_$variable";
		$ret->desc = $desc;
		$ret->type = 'multiselect';

		$rvr = new FormVar();
		$rvr->name = "frm_{$class}_a_$variable";
		$rvr->option = $this->object_variable_option(true, $list);
		$ret->variable1 = $rvr;

		$rvr = new FormVar();
		$rvr->name = "frm_{$class}_b_$variable";
		$rvr->option = $this->object_variable_option(true, $value);
		$ret->variable2 = $rvr;

		return $ret;
	}

	function object_variable_nomodify($stuff, $variable, $value = null)
	{
		$this->fix_stuff_or_class($stuff, $variable, $class, $svalue);
		if ($value === null) {
			$value = $svalue;
		}

		if ($this->is_special_variable($value)) {
			$descr = $value->descr;
			$value = $value->value;
		} else {
			$descr = $this->get_classvar_description_after_overload($class, $variable);
		}

		$desc = $descr[2];

		if (is_array($value)) {
			$value = implode('\n', $value);
		}

		$rvr = new FormVar();
		$rvr->name = "frm_{$class}_c_$variable";
		$rvr->desc = $desc;
		$rvr->type = 'nomodify';
		$rvr->value = $value;

		return $rvr;
	}

	function xml_variable_endblock()
	{
		return ' </block> </start>';
	}

	function object_variable_button($name)
	{
		$name = ucfirst($name);
		$rvr = new FormVar();
		$rvr->type = 'button';
		$rvr->name = 'frm_change';
		$rvr->value = $name;
		return $rvr;
	}

	function object_variable_check($stuff, $variable, $def = null)
	{
		$value = null;

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);


		//Hack Hack... Handling used separately...
		if (csb($variable, 'used_s_')) {
			$nclass = $class;
			$nvariable = strfrom($variable, 'used_s_');
			$value = $stuff->used->$nvariable;
		} else {
			$nvariable = $variable;
			$nclass = $class;
		}

		if ($value === 'on') {
			$value = 'yes';
		}

		$descr = $this->get_classvar_description_after_overload($nclass, $nvariable);

		$rvr = new FormVar();
		$rvr->name = "frm_{$class}_c_{$variable}_aaa_checkname";
		$rvr->desc = $descr[2];
		$rvr->type = 'hidden';
		$rvr->value = 'off';
		$ret[] = $rvr;

		$rvr = new FormVar();
		$rvr->name = "frm_{$class}_c_{$variable}_aaa_checked";
		$rvr->desc = $descr[2];
		$rvr->type = 'checkbox';
		$rvr->checked = $value;
		$rvr->value = 'on';
		$ret[] = $rvr;
		return $ret;
	}

	function object_variable_hidden($key, $value)
	{
		$string = null;

		if (is_array($value)) {
			foreach ($value as $k => $v) {
				$str = "{$key}" . "[$k]";
				$rvr = new FormVar();
				$rvr->name = $str;
				$rvr->value = $v;
				$rvr->type = 'hidden';
				$ret[] = $rvr;
			}
		} else {
			$rvr = new FormVar();
			$rvr->name = $key;
			$rvr->value = $value;
			$rvr->type = 'hidden';
			$ret[] = $rvr;
		}
		return $ret;
	}

	function object_variable_hiddenlist($hlist)
	{
		foreach ($hlist as $key => $val) $a[] = $this->object_variable_hidden($key, $val);

		return lx_array_merge($a);
	}

	function object_variable_htmltextarea($stuff, $variable, $value = null, $nonameflag = false)
	{
		$this->fix_stuff_or_class($stuff, $variable, $class, $nvalue);
		$name = "frm_{$class}_c_{$variable}";

		if (!$value) {
			$value = $nvalue;
		}

		if ($nonameflag) {
			$name = null;
		}

		$descr = $this->get_classvar_description_after_overload($class, $variable);
		$val = exec_class_method($class, 'getTextAreaProperties', $variable);

		$rvr->name = $name;
		$rvr->desc = $descr[2];
		$rvr->height = $val['height'];
		$rvr->width = $val['width'];
		$rvr->value = $value;
		$rvr->type = 'htmltextarea';

		return $rvr;
	}

	function object_variable_textarea($stuff, $variable, $value = null, $nonameflag = false)
	{
		$this->fix_stuff_or_class($stuff, $variable, $class, $nvalue);
		$name = "frm_{$class}_c_{$variable}";

		if (!$value) {
			$value = $nvalue;
		}
		if ($nonameflag) {
			$name = null;
		}

		$descr = $this->get_classvar_description_after_overload($class, $variable);
		$val = exec_class_method($class, 'getTextAreaProperties', $variable);

		$rvr = new FormVar();
		$rvr->name = $name;
		$rvr->desc = $descr[2];
		$rvr->height = $val['height'];
		$rvr->width = $val['width'];
		$rvr->value = $value;
		$rvr->type = 'textarea';

		return $rvr;
	}

	function object_variable_command($type, $desc)
	{
		$rvr = new FormVar();
		$rvr->type = $type;
		$rvr->desc = $desc;
		return $rvr;
	}

	function object_inherit_filter()
	{
		return null; // Don't inherit hpfilter.
	}

	function object_inherit_accountselect()
	{
		return $this->object_variable_inherit('frm_accountselect');
	}

	function object_inherit_classpath()
	{
		$a1 = $this->object_variable_inherit('frm_o_o');
		$a2 = $this->object_variable_inherit('frm_consumedlogin');
		return lx_merge_good($a1, $a2);
	}

	function html_variable_inherit($var = null)
	{
		$string = null;
		foreach ($this->__http_vars as $key => $value) {
			if ($var && $var != $key) {
				continue;
			} elseif (!char_search_a($key, "_c_")) {
				continue;
			}

			if (is_array($value)) {
				foreach ($value as $k => $v) if (is_array($v)) {
					foreach ($v as $nk => $nv) {
						$str = "{$key}" . "[$k][$nk]";
						print("<input type=hidden name=$str value=$nv>");
					}
				} else {
					$str = "{$key}" . "[$k]";
					print("<input type=hidden name=$str value=$v>");
				}
			} else {
				if (!$value) {
					continue;
				}
				print("<input type=hidden name=$str value=$value>");
			}
		}
		return $string;
	}

	function object_variable_inherit($var = null)
	{
		$ret = null;

		foreach ($this->__http_vars as $key => $value) {
			if ($var) {
				if ($var != $key) {
					continue;
				}
			} else {
				if (!char_search_a($key, "_c_")) {
					continue;
				}
			}
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $nk => $nv) {
							$rvr = new FormVar();
							$str = "{$key}" . "[$k][$nk]";
							$rvr->name = $str;
							$rvr->value = $nv;
							$rvr->type = "hidden";
							$ret[] = $rvr;
						}

					} else {
						$rvr = new FormVar();
						$str = "{$key}" . "[$k]";
						$rvr->name = $str;
						$rvr->value = $v;
						$rvr->type = "hidden";
						$ret[] = $rvr;
					}
				}
			} else {

				if (!$value) {
					continue;
				}
				$rvr = new FormVar();
				$rvr->name = $key;
				$rvr->value = $value;
				$rvr->type = "hidden";
				$ret[] = $rvr;
			}

		}
		return $ret;

	}


	function object_variable_listquota($parent, $stuff, $variable, $list = null)
	{

		global $gbl, $sgbl, $ghtml;

		$value = null;

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);


		$descr = $this->get_classvar_description_after_overload($class, $variable);
		$desc = $this->get_form_variable_name($descr[2]);

		$cvar = $variable;

		$listvariable = "listpriv_s_" . $variable;

		if (cse($cvar, "_sing")) {
			$realvar = strtil($cvar, "_sing");
			$listvar = $realvar . "_list";
			if (!$list) {
				$list = $parent->listpriv->$listvar;
			}
			$string = $this->do_object_variable_select($class, $listvariable, $desc, $list, $value);
		} else {
			$listvar = $cvar;
			if (!$list) {
				$list = $parent->listpriv->$listvar;
			}
			$string = $this->do_object_variable_multiselect($class, $listvariable, $desc, $list, $value);
		}


		return $string;

	}


	function object_variable_quota($parent, $stuff, $variable)
	{

		global $gbl, $sgbl, $ghtml;

		$parent = $parent->getClientParentO();

		$value = null;

		$this->fix_stuff_or_class($stuff, $variable, $class, $value);


		$descr = $this->get_classvar_description_after_overload($class, $variable);

		$cvar = $variable;

		if ($value === 'Unlimited') {
			$value = null;
		}


		$check = (trim($value) !== "") ? 'no' : 'yes';


		if (is_object($stuff)) {
			if (isOn($value)) {
				$chval = 'yes';
			} else {
				$chval = 'no';
			}
		} else {
			$cl = get_name_without_template($stuff);
			if (isOn(exec_class_method($cl, "getDefaultValue", $variable))) {
				$chval = 'yes';
			} else {
				$chval = "no";
			}
		}

		if (cse($variable, "_flag")) {
			$rvr = new FormVar();
			$rvr->name = "frm_{$class}_c_priv_s_{$variable}_aaa_checkname";
			$rvr->desc = $descr[2];
			$rvr->type = "hidden";
			$rvr->value = "off";
			$ret[] = $rvr;

			if ($parent->priv->isOn($variable)) {
				$rvr = new FormVar();
				$rvr->name = "frm_{$class}_c_priv_s_{$variable}_aaa_checked";
				$rvr->desc = $descr[2];
				$rvr->type = "checkbox";
				$rvr->checked = $chval;
				$rvr->value = "on";
				$ret[] = $rvr;
			} else {
				$rvr = new FormVar();
				$rvr->name = "frm_{$class}_c_priv_s_{$variable}_aaa_checked";
				$rvr->desc = $descr[2];
				$rvr->type = "checkbox";
				$rvr->checked = "disabled";
				$rvr->value = "off";
				$ret[] = $rvr;
			}
			return $ret;
		}

		if (is_unlimited($parent->priv->$cvar)) {
			$rvr = new FormVar();
			$rvr->name = "frm_{$class}_c_$variable";
			$rvr->type = "checkboxwithtext";
			$rvr->desc = $descr[2];
			$rvr->mode = "or";

			$text = new FormVar();
			$text->name = "frm_{$class}_c_priv_s_{$variable}_aaa_quotaname";
			$text->value = $value;
			$rvr->text = $text;

			$checkbox = new FormVar();
			$checkbox->desc = "Unlimited";
			$checkbox->name = "frm_{$class}_c_priv_s_{$variable}_aaa_unlimited";
			$checkbox->checked = $check;
			$checkbox->value = "yes";
			$rvr->checkbox = $checkbox;
			$ret[] = $rvr;

			$rvr = new FormVar();
			$rvr->type = "hidden";
			$rvr->value = "Unlimited";
			$rvr->name = "frm_{$class}_c_priv_s_{$variable}_aaa_quotamax";
			$ret[] = $rvr;
		} else {
			$quotaleft = $parent->getEffectivePriv($cvar, $class);

			if (isHardQuotaVariableInClass($class, $cvar)) {
				$quotaleft += $value;
			}

			$totalstring = null;
			$totalstring = "Total: " . $parent->priv->$cvar;

			if (cse($class, "template")) {
				$totalstring = null;
				$quotaleft = $parent->priv->$cvar;
			}

			if ($value === "") {
				$value = $quotaleft;
			}

			$rvr = new FormVar();
			$rvr->type = "modify";
			$rvr->texttype = "text";
			$rvr->value = $value;
			$rvr->desc = $descr[2];
			$rvr->name = "frm_{$class}_c_priv_s_{$variable}_aaa_quotaname";
			$rvr->posttext = "Max $quotaleft $totalstring";
			$rvr->format = "integer";
			$ret[] = $rvr;

			$rvr = new FormVar();
			$rvr->type = "hidden";
			$rvr->value = $quotaleft;
			$rvr->name = "frm_{$class}_c_priv_s_{$variable}_aaa_quotamax";
			$ret[] = $rvr;

		}
		return $ret;
	}


	function object_variable_startblock($obj, $class, $title, $url = null)
	{
		if (!$url) {
			$url = $_SERVER['PHP_SELF'];
		}
		if (!$class) {
			$class = get_class($obj);
		}
		$domdesc = get_classvar_description($class);
		$server = $this->print_machine($obj);

		$formname = fix_nname_to_be_variable("$title{$obj->getId()}");

		$header = new FormVar();
		$header->form = $formname;
		$header->formtype = "enctype=\"multipart/form-data\"";

		if ($title) {
			$header->title = "$title for {$obj->getId()} $server";
		} else {
			$header->title = null;
		}

		$header->url = $url;
		return $header;
	}

	function object_variable_oldpassword($class, $var, $descr)
	{
		$rvr = new FormVar();
		$rvr->name = "frm_{$class}_c_{$var}";
		$rvr->desc = $descr[2];
		$rvr->texttype = "password";
		$rvr->valid = "yes";
		$rvr->type = "modify";
		$rvr->need = "yes";
		return $rvr;
	}

	function printbr($val = 2)
	{
		for ($i = 0; $i < $val; $i++) {
			print("<br> ");
		}
	}

	function object_variable_password($class, $var)
	{
		$desc = get_classvar_description($class, $var);

		$rvr = new FormVar();
		$rvr->name = "frm_{$class}_c_{$var}";
		$rvr->desc = $desc[2];
		$rvr->texttype = "password";
		$rvr->confirm_password = true;
		$rvr->valid = "yes";
		$rvr->type = "modify";
		$rvr->need = "yes";
		$ret[] = $rvr;

		$rvr = new FormVar();
		$rvr->name = "frm_confirm_password";
		$rvr->desc = "Confirm Password";
		$rvr->texttype = "password";
		$rvr->type = "modify";
		$rvr->valid = "yes";
		$rvr->need = "yes";
		$rvr->match = "frm_{$class}_c_{$var}";
		$rvr->matchdesc = $desc[2];
		$ret[] = $rvr;

		return $ret;
	}


	function object_variable_option($multi, $list, $select = null, $assoc = null)
	{
		$string = null;
		$sel = null;
		if (!$list) {
			return null;
		}

		$match = false;
		foreach ((array) $list as $k => $l) {
			$value = ($assoc) ? $k : $l;

			if ($l === '--Disabled--') {
				$match = true;
			}

			if ($select !== "" && "$value" === "$select") {
				$match = true;
				$option["__v_selected_$value"] = $l;
			} else {
				$option[$value] = $l;
			}
		}

		// IF the select is nonnull and the the damn thing doesn't match, then there is some problem. That is the current value isn't in the list of acceptable values.
		if (!$match && !$multi) {
			if ($select) {
				$sel['--Select One--'] = "--Select One ($select not in List)--";
			} else {
				$sel['--Select One--'] = '--Select One--';
			}
		}

		if ($sel) {
			$option = $sel + $option;
		}

		return $option;
	}


	function isSelectOne($var)
	{
		return ($var === '--Select One--');
	}

	function print_current_input_vars($ignore)
	{
		$this->print_input_vars($this->__http_vars, $ignore);
	}

	function print_current_input_var_unset_filter($key1, $arr)
	{
		if (!isset($this->__http_vars['frm_hpfilter'])) {
			return;
		}
		$post['frm_hpfilter'] = $this->__http_vars['frm_hpfilter'];
		foreach ($arr as $key2) {
			if (isset($post['frm_hpfilter'][$key1][$key2])) {
				unset($post['frm_hpfilter'][$key1][$key2]);
			}
		}
		$this->print_input_vars($post);
	}

	function print_input_vars($post, $ignore = array())
	{
		foreach ((array) $post as $key => $value) {
			if (array_search_bool($key, $ignore)) {
				continue;
			}


			if (is_array($value)) {
				foreach ($value as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $nk => $nv) {
							print("<input type=hidden name=\"" . $key . "[" . $k . "]" . "[" . $nk . "]\" value =\"$nv\"> \n");
						}

					} else {
						print("<input type=hidden name=\"" . $key . "[" . $k . "]\" value =\"$v\"> \n");
					}
				}
			} else {
				print("<input type=hidden name=\"$key\" value =\"$value\"> \n");
			}
		}
	}

	function get_get_from_post($ignore, $list)
	{
		$string = "";
		if (!$list) {
			return $string;
		}

		foreach ($list as $key => $value) {
			if ($ignore && array_search_bool($key, $ignore)) {
				continue;
			}
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $nk => $nv) {
							$string .= $key . "[" . $k . "]" . "[" . $nk . "]=" . $nv . "&";
						}
					} else {
						$string .= $key . "[" . $k . "]=" . $v . "&";
					}
				}
			} else {
				$string .= "$key=$value&";
			}

		}
		$string = preg_replace("/&$/", "", $string);
		return $string;
	}

	function get_get_from_current_post($ignore)
	{
		return $this->get_get_from_post($ignore, $this->__http_vars);
	}


	function get_classvar_description_after_overload($class, $property)
	{
		global $gbl, $sgbl, $login;

		lxclass::resolve_class_differences($class, $property, $dclass, $dproperty);

		$classdesc = get_classvar_description($dclass);
		$prop_descr = get_classvar_description($dclass, $dproperty);
		$this->fix_variable_overload($prop_descr, $classdesc[2]);
		$prop_descr[2] = getNthToken($prop_descr[2], 1);
		return $prop_descr;
	}

	function fix_variable_overload(&$descr, $classdesc)
	{
		foreach ($descr as &$d) if (strstr($d, "[%v]") !== false) {
			$d = str_replace("[%v]", $classdesc, $d);
		}
	}

	function generateParentListUrl($nobject)
	{
		$object = clone $nobject;
		$object->__parent_o = null;
		$parent = $object->getParentO();

		if (!$parent) {
			log_log("parent_state", "object {$object->getClName()} has no parent...");
			return null;
		}

		$plist[] = $parent;
		while (is_object($parent) && !$parent->isAdmin()) {
			$parent = $parent->getParentO();
			$plist[] = $parent;
		}
		$plist = array_reverse($plist);
		$i = 0;
		$string = null;
		$str = null;
		foreach ($plist as $p) {
			if (!$p || $p->isAdmin()) {
				continue;
			}
			if ($p->getParentO() && $p->getParentO()->isSingleObject($p->getClass())) {
				$string[] = "frm_o_o[$i][class]={$p->get__table()}";
			} else {
				$string[] = "frm_o_o[$i][class]={$p->get__table()}&frm_o_o[$i][nname]={$p->nname}";
			}

			$i++;
		}
		$class = strfrom($object->getClass(), "all_");
		if ($string) {
			$str = implode("&", $string);
		}
		return "?frm_action=list&frm_o_cname=$class&$str";

	}

	function generateEntireUrl($nobject, $top)
	{
		$object = clone $nobject;
		$object->__parent_o = null;
		$parent = $object->getParentO();

		if (!$parent) {
			log_log("parent_state", "object {$object->getClName()} has no parent...");
			return null;
		}

		$plist[] = $parent;
		while (!$parent->hasSameId($top)) {
			$parent = $parent->getParentO();

			if (!$parent) {
				log_log("parent_state", "object {$object->getClName()} has no parent...");
				return null;
			}
			$plist[] = $parent;
		}
		$plist = array_reverse($plist);
		$i = 0;
		foreach ($plist as $p) {
			if ($p->hasSameId($top)) {
				continue;
			}

			if ($p->getParentO() && $p->getParentO()->isSingleObject($p->getClass())) {
				$string[] = "frm_o_o[$i][class]={$p->get__table()}";
			} else {
				$string[] = "frm_o_o[$i][class]={$p->get__table()}&frm_o_o[$i][nname]={$p->nname}";
			}
			$i++;
		}
		$class = strfrom($object->getClass(), "all_");
		$string[] = "frm_o_o[$i][class]=$class&frm_o_o[$i][nname]={$object->nname}";

		$str = implode("&", $string);
		return "?frm_action=show&$str";

	}

	function getFullUrl($url, $p = "default")
	{

		if (is_array($url) || $this->is_special_url($url) || csb($url, "?") || csa($url, "display.php")) {
			return $url;
		}

		if ($p === "default") {
			$p = $this->frm_o_o;
		}

		$np = array();

		$url = "display.php?" . $url;
		$this->get_post_from_get($url, $path, $post);

		$k = 0;
		$k = count($p);
		if (isset($post['goback'])) {
			for ($i = 0; $i < $post['goback']; $i++) {
				unset($p[--$k]);
			}
		}

		if (isset($post['j'])) {
			$desc = get_classvar_description($post['j']['class']);
			if (csa($desc[0], "N")) {
				if ($p[$k - 1]['class'] === $post['j']['class']) {
					$k--;
				}
			}
			$p[$k]['class'] = $post['j']['class'];
			$p[$k]['nname'] = $post['j']['nname'];
			$k++;
		}

		if (isset($post['n'])) {
			$obj = $post['n'];
			if (csa($obj, "_s_")) {
				$l = explode("_s_", $obj);
				foreach ($l as $o) {
					$p[$k++]['class'] = $o;
				}
			} else {
				$p[$k++]['class'] = $post['n'];
			}
		}

		// Ka has to come AFTER n. Otherwise it won't work in the getshowalist, especially for web/installapp combo.
		if (isset($post['k'])) {
			$desc = get_classvar_description($post['k']['class']);
			if (csa($desc[0], "N")) {
				if ($p[$k - 1]['class'] === $post['k']['class']) {
					$k--;
				}
			}
			$p[$k]['class'] = $post['k']['class'];
			$p[$k]['nname'] = $post['k']['nname'];
			$k++;
		}
		if (isset($post['o'])) {
			$obj = $post['o'];
			if (csa($obj, "_s_")) {
				$l = explode("_s_", $obj);
				foreach ($l as $o) {
					$p[$k++]['class'] = $o;
				}
			} else {
				$p[$k++]['class'] = $post['o'];
			}
		}

		if (isset($post['l'])) {
			$desc = get_classvar_description($post['l']['class']);
			if (csa($desc[0], "N")) {
				if ($p[$k - 1]['class'] === $post['l']['class']) {
					$k--;
				}
			}
			$p[$k]['class'] = $post['l']['class'];
			$p[$k]['nname'] = $post['l']['nname'];
		}

		$npost['frm_action'] = $post['a'];

		if (isset($post['sa'])) {
			$npost['frm_subaction'] = $post['sa'];
		}

		if (isset($post['dta'])) {
			$npost['frm_dttype']['var'] = $post['dta']['var'];
			$npost['frm_dttype']['val'] = $post['dta']['val'];
		}

		foreach ((array) $post as $k => $v) {
			if (csa($k, "_c_")) {
				$npost[$k] = $v;
			}
		}
		if ($p) {
			$npost['frm_o_o'] = $p;
		}

		if (isset($post['c'])) {
			$npost['frm_o_cname'] = $post['c'];
		}


		if (isset($post['frm_filter'])) {
			$npost['frm_filter'] = $post['frm_filter'];
		}

		if ($this->frm_consumedlogin) {
			$npost['frm_consumedlogin'] = 'true';
		}

		$url = "/display.php?" . $this->get_get_from_post(null, $npost);

		return $url;
	}

	function printObjectElement($parent, $class, $classdesc, $obj, $name, $width, $descr, $colcount)
	{
		global $gbl, $sgbl, $login;

		$rclass = $class;

		list($graphtype, $graphwidth) = exec_class_method($rclass, "getGraphType");

		if ($name === 'syncserver') {
			$serverdiscr = pserver::createServerInfo(array($obj->syncserver), $class);
		}

		$__external = 0;
		$iconpath = get_image_path() . "/button/";
		if (isset($descr[$name]) && (csa($descr[$name][0], 'q') || csa($descr[$name][0], "D"))) {
			// For hard quota you need priv. For soft quota, you use used.
			if (csa($descr[$name][0], 'h')) {
				$pname = $obj->priv->display($name);
			} else {
				$pname = $obj->used->display($name);
			}
		} else {
			if (isset($descr[$name]) && csa($descr[$name][0], 'p')) {
				if (cse($name, "_per_f")) {
					$qrname = strtil($name, "_per_f");
					$pname = array($obj->priv->$qrname, $obj->used->$qrname, null);
				} else {
					$pname = $obj->perDisplay($name);
				}
			} else {
				$pname = $obj->display($name);
				$pname = Htmllib::fix_lt_gt($pname);
				if (csa($pname, "_lximg:")) {
					$pname = preg_replace("/_lximg:([^:]*):([^:]*):([^:]*):/", "<img src=$1 width=$2 height=$3>", $pname);
				}
				if (csa($pname, "_lxspan:")) {
					$pname = preg_replace("/_lxspan:([^:]*):([^:]*):/", "<span title='$2'>$1 </span> ", $pname);
				}
				if (csa($pname, "_lxurl:")) {
					$pname = preg_replace("/_lxurl:([^:]*):([^:]*):/", "<a class=insidelist target=_blank href=http://$1> $2 </a>", $pname);
				}
				if (csa($pname, "_lxinurl:")) {
					$url = preg_replace("/_lxinurl:([^:]*):([^:]*):/", "$1", $pname);
					$url = $this->getFullUrl($url);
					$url = "\"$url\"";
					$pname = preg_replace("/_lxinurl:([^:]*):([^:]*):/", "<a class=insidelist href=$url> $2 </a>", $pname);
				}
				if ($name === 'syncserver') {
					$pname = "<span title='$serverdiscr'>  $pname </span> ";
				}
			}
		}

		$wrapstr = ($width === "100%") ? "wrap" : "nowrap";

		$target = NULL;
		$purl = NULL;

		$url = null;

		$__full_url = false;
		if ($name === 'parent_name_f' && csb($class, "all_")) {
			$url = $this->generateParentListUrl($obj);
			$ac_descr = $this->getActionDetails($url, $purl, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
			$__full_url = true;
			$__full_url_t_identity = $__t_identity;
		}

		if (isset($descr[$name][3]) || csa($name, "abutton_")) {
			if (csa($name, "abutton_")) {
				$urlname = $obj->nname;
				$str = strfrom($name, "abutton_");
				$_tv = explode("_s_", $str);
				if ($_tv[0] === 'list') {
					$url = "a=list&c={$_tv[1]}";
				} else {
					if ($_tv[0] === 'show') {
						$url = "a=show&o={$_tv[1]}";
					} else {
						$url = "a=$_tv[0]&sa={$_tv[1]}";
					}
				}
				$url = "&k[class]=$class&k[nname]=$urlname&$url";

			} else {
				if ($this->is_special_url($descr[$name][3])) {
					$url = $descr[$name][3];
				} else {
					if (csb($descr[$name][3], "__stub")) {
						$url = $obj->getStubUrl($descr[$name][3]);
					} else {
						if (csb($class, "all_")) {
							$url = $this->generateEntireUrl($obj, $login);
							if (!$url) {
								/// That means that the object is dangling and has no parent.
								throw new lxException("object_found_without_proper_parent");
							}
						} else {
							$urlname = $obj->nname;
							$url = $descr[$name][3] . "&k[class]=$class&k[nname]=$urlname";
						}
					}
				}
			}
			if ($this->is_special_url($url)) {
				$purl = $url->purl;
				$target = $url->target;
				$url = $url->url;
				$purl = $this->getFullUrl($purl);
				$url = str_replace("[%s]", $obj->nname, $url);

				if (strpos($url, "http:/") !== false) {
					$__external = 1;
				}
			} else {
				$url = $this->getFullUrl($url);
			}
			$ac_descr = $this->getActionDetails($url, $purl, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);

		}

		$align = 'left';
		$valign = 'middle';
		$image = 0;
		if (csa($descr[$name][0], "e")) {
			$pname = strtolower($pname);
			$property = "{$name}_v_$pname";
			$prop_descr = get_classvar_description($rclass, $property);

			if (!$prop_descr) {
				dprint("Property Description for $rclass $property not Found <br> \n");
			}

			$this->fix_variable_overload($prop_descr, $classdesc[2]);
			$image = $this->get_image($iconpath, $class, $property, ".gif");
			$help = $this->get_full_help($prop_descr['help'], $obj->getId());
			$alt = lx_strip_tags($help);
			$help = $this->get_action_or_display_help($help, "notice");

			$align = "center onmouseover=\"changeContent('help',' $help')\" onmouseout=\"changeContent('help','helparea')\"";

			if (!$sgbl->isBlackBackground()) {
				$pname = " <span title='$alt'><img src=$image width=16 height=16 >";
			}
			$this->save_non_existant_image($image);
			$image = 1;
		}

		if (!$obj->isAction($name) && char_search_a($descr[$name][0], "b")) {
			$pname = "";
		}

		$bgcolorstring = null;
		$forecolorstring = null;
		if ($sgbl->isBlackBackground()) {
			$bgcolorstring = "bgcolor=#000";
			$forecolorstring = "color=#999999";
		}

		if ($url && $obj->isAction($name)) {

			$urlhelp = "";
			if (!$image) {
				$this->fix_variable_overload($ac_descr, $classdesc[2]);
				// When it is showing the parent name, it is showing the resource under that parent, nad not under this object.
				if ($__full_url) {
					$help = $this->get_full_help($ac_descr[2], $__full_url_t_identity);
				} else {
					$help = $this->get_full_help($ac_descr[2], $obj->getId());
				}
				$alt = lx_strip_tags($help);
				$help = $this->get_action_or_display_help($help, "action");
				$urlhelp = "onmouseover=\"changeContent('help',' $help')\" onmouseout=\"changeContent('help','helparea')\"";
				if (strstr($descr[$name][0], "b") != NULL || csb($name, "abutton")) {
					if ($obj->isButton($name)) {
						if ($sgbl->isBlackBackground()) {
							$pname = "b";
						} else {
							$pname = " <span title='$alt'><img src='$_t_image'  height=15 width=15>";
						}

						$align = "center";
					} else {
						$pname = "";
					}
				}
			}
			print("<td $bgcolorstring class=collist $wrapstr align=$align > <span title='$alt'>");

			$method = ($__external) ? "get" : $sgbl->method;

			?>
		<form name=form<?=$colcount ?>  method=<?=$method?> action=<?=$path ?> <?=$target ?> >
			<?php
		if ($this->frm_action === 'selectshow') {
			$post['frm_action'] = 'selectshow';
			$post['frm_selectshowbase'] = $this->frm_selectshowbase;
		}
			$this->print_input_vars($post);
			?>
		</form>
		<a class=insidelist
		   href="javascript:document.form<?=$colcount ?>.submit()" <?=$urlhelp ?> > <?=$pname ?>   </a>  </span> </td>
			<?php

		} else {
			if (char_search_a($descr[$name][0], "p")) {
				print("<td $bgcolorstring class=collist $wrapstr align=$align > ");
				$arr = $pname;
				$this->show_graph($arr[0], $arr[1], null, $graphwidth, $arr[2], $graphtype, $obj->getId(), $name);
				print("</td>  ");
			} else {

				if (csa($descr[$name][0], "W")) {
					$pname = str_replace("\n", "<br>\n", $pname);
					$pname = str_replace("[code]", "<div style='padding: 10 10 10 10; margin: 10 10 10 10; border: 1px solid #43a1a1'>", $pname);
					$pname = str_replace("[quote]", "<div style='background:#eee; padding: 10 10 10 10; margin: 10 10 10 10; border: 1px solid #aaa'> [b] QUOTE [/b]", $pname);
					$pname = str_replace("[b]", "<font style='font-weight:bold'>", $pname);
					$pname = str_replace("[/b]", "</font>", $pname);
					$pname = str_replace("[/code]", "</div>", $pname);
					$pname = str_replace("[/quote]", "</div>", $pname);
					$pname = "<table width=100% style='background:white;padding:20 20 20 20; margin: 8 8 8 8 ;border: 1px solid grey;' cellpadding=0 cellspacing=0> <tr> <td  > $pname </td> </tr> </table>  ";
				}
				print("<td $bgcolorstring class=collist $wrapstr align=$align >  $pname   </td>  ");
				//print("<td class=collist $wrapstr align=$align onClick=\"javascript:getElementById('input$oname$name').type='text'; getElementById('font$oname$name').style.display='none'\"> <input \"style=width:60\" id=input$oname$name type=hidden name=hello value=$pname> <font id=font$oname$name> $pname </font>  </td>  ");
			}
		}
	}

	function print_input($type, $name, $value, $extra = null)
	{
		echo '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" ' . $extra . ' />';
	}

	function print_next_previous_link($object, $class, $place, $iconpath, $name, $page_value)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$filtername = $object->getFilterVariableForThis($class);
		print("<form name=form{$name}_page_$place method=$sgbl->method  action={$_SERVER["PHP_SELF"]}>");
		$this->print_current_input_var_unset_filter($filtername, array('pagenum'));
		$this->print_current_input_vars(array('frm_hpfilter'));
		$this->print_input("hidden", "frm_hpfilter[$filtername][pagenum]", $page_value);
		print("</form>");
		$help = "<font class=bold> Action: </font> <br> <br> ";
		if ($name === "forward" || $name === "rewind") {
			$help .= ucfirst($name) . "  a few Pages.";
		} else {
			$help .= "Go To " . ucfirst($name) . " Page.";
		}

		$link = "<a  onmouseover=\"javascript:changeContent('help','$help')\" onmouseout=\"changeContent('help','helparea')\" href=javascript:document.form{$name}_page_$place.submit()><img src=$iconpath/{$name}_page.gif align=absbottom ></a> ";
		return $link;
	}

	function print_next_previous($object, $class, $place, $cgi_pagenum, $total_num, $pagesize)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$iconpath = get_general_image_path() . "/icon";

		$search_brack_o = "<b> ";
		$search_brack_c = " </b> ";

		$prev_link = NULL;
		$first_link = NULL;
		$rewind_link = NULL;

		print("<table width=10 > <tr> <td bgcolor=#ffffff nowrap>");
		$first_link = $this->print_next_previous_link($object, $class, $place, $iconpath, "first", 1);

		$rewind_link = $this->print_next_previous_link($object, $class, $place, $iconpath, "rewind", ($cgi_pagenum + $cgi_pagenum % 2) / 2);
		$prev_link = $this->print_next_previous_link($object, $class, $place, $iconpath, "prev", max(1, ($cgi_pagenum - 1)));

		$next_link = NULL;
		$forward_link = NULL;
		$last_link = NULL;
		if ($total_num > $pagesize) {
			$page = $total_num / $pagesize;
			$page = explode('.', $page);
			$page = (isset($page[1])) ? $page[0] + 1 : $page[0];

			$left = $page - $cgi_pagenum;
			$next_link = $this->print_next_previous_link($object, $class, $place, $iconpath, "next", min(($cgi_pagenum + 1), $page));
			$forward_link = $this->print_next_previous_link($object, $class, $place, $iconpath, "forward", min($cgi_pagenum + ($left + ($left % 2)) / 2, $page));
			$last_link = $this->print_next_previous_link($object, $class, $place, $iconpath, "last", $page);

			print(" $first_link &nbsp; $rewind_link &nbsp; $prev_link <b> <font class=pagetext>&nbsp;Page $cgi_pagenum (of $page) </b> </font> $next_link $forward_link $last_link");
			$search_brack_o = "  &nbsp;  (";
			$search_brack_c = ") ";
		}

		print("</td> </tr> </table> ");
	}


	function print_machine($object)
	{
		if (!$object->isClass('client') && !$object->isLocalhost() && $object->syncserver != $object->nname) {
			return "(on $object->syncserver)";
		}
		return '';
	}

	function printSearchTable($name_list, $parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$this->print_real_search($name_list, $parent, $class);

	}

	function get_class_description($class, $display = null)
	{
		$classdesc = get_classvar_description($class);

		if (!$classdesc) {
			print("Cannot access $class::\$__desc");
			exit(0);
		}
		return $classdesc;
	}

	function display_count(&$obj_list, $disp)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$n = 0;

		if (!$obj_list) {
			return $n;
		}

		$filter = $this->frm_filter;

		if (!$filter && !$ghtml->frm_searchstring) {
			return count($obj_list);
		}

		foreach ($obj_list as $o) {
			if (if_search_continue($o) || !$o->isDisplay($filter)) {
				$obj_list[$o->nname] = null;
				unset($obj_list[$o->nname]);
				continue;
			}
			$n++;
		}

		return $n;
	}

	function print_crappy_header()
	{
		if (0 && !$sellist && $class !== 'permission' && $class !== 'resource' && $class !== 'information') {
			?>

		<table cellpadding=0 width=100% cellspacing=0 border=0 height=27>

			<tr width=20% nowrap valign=top>
				<td><img src="<?=$imgheadleft; ?>"></td>
				<td nowrap valign=middle background="<?=$imgheadbg; ?>"><b><font color="#234355"
																				 style="font-weight: bold"><?=get_plural($classdesc[2])?> <?=$showvar ?> <?=$login->getKeyword('under')?> <?="{$parent->getId()} $filterundermes" ?>
				</b> <?=$this->print_machine($parent) ?> <b> (<?=$total_num ?>)</b></font></td>
				<td><img src="<?=$imgheadright; ?>"></td>

				<td align=right
					width=100%> <?php $this->print_next_previous($parent, $class, "top", $cgi_pagenum, $total_num, $pagesize); ?> </td>

			</tr>
		</table>
		</td>
		</tr>

            <tr><td colspan=3> <table cellpadding=0 cellspacing=0 border=0 width=100% height=35
									  background="<?=$imgbtnbg; ?>">
			<tr>
				<td width=80% align=left>
					<table width=100% cellpadding=0 cellspacing=0 border=0>
						<tr>
							<td valign=bottom><?php  ?></td>
						</tr>
					</table>
				</td>
				<td width=15% align=right><b><font color="#ffffff"><?php $this->print_search($parent, $class); ?></font></b>
				</td>
				<td valign=bottom><img src=<?=$imgbtncrv ?>></td>
			</tr>
		</table>

			<?php

		} else {
			if (0 && $class !== 'resource' && $class !== 'permission' && $class !== 'information') {

				$descr = $this->getActionDescr($_SERVER['PHP_SELF'], $this->__http_vars, $class, $var, $identity);
				?>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td align=left>
						<table cellpadding=0 cellspacing=0 border=0 width=100%>
							<tr>
								<td><img src="<?=$imgheadleft; ?>"></td>
								<td nowrap width=100% background="<?=$imgheadbg; ?>"><b><font color="#000000">
									Confirm <?=$descr[1] ?>: </b><?=get_plural($classdesc[2])?>
									from <?=$parent->display("nname"); ?></font></td>
								<td><img src="<?=$imgheadright; ?>"></td>
							</tr>
						</table>
					</td>
					<td width=100%> &nbsp; </td>
				</tr>
			</table>

				<?php

			}
		}
	}

	function printListAddFormBad($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$col = $login->getSkinColor();
		$rclass = $class;
		$vlist = exec_class_method($rclass, "addListForm", $parent, $class);
		if (!$vlist) {
			return;
		}

		$buttonpath = get_image_path() . "/button/";
		?>

    <table cellpadding=0 width=90% cellspacing=1 style='border: 1px solid #<?=$col?>;  background:#fffafa;'>
    <?php
	print("<tr> <td height=10 colspan=10> &nbsp;</td> </tr> <tr> <td width=20 color=#fffafa> &nbsp; </td> <td > </td> ");
		print("<form name=addlist method=get action=/display.php >");
		foreach ($vlist as $k => $v) {
			if (isset($v[0]) && $v[0] === 'h') {
				continue;
			}
			$k = get_classvar_description($rclass, $k);
			print("<td nowrap> $k[2] </td>");
		}

		print("<td > </td> <td width=10> &nbsp; </td> </tr> <tr> <td width=10> </td> <td > <img src=$buttonpath/{$class}_list.gif height=20 width=20> </td> ");

		foreach ($vlist as $k => $v) {
			print("<td >");
			if (isset($v[0]) && $v[0] === 's') {
				print("<select name=frm_{$class}_c_{$k} style='border:1px solid #b0c0f0; font-family:arial; color:#000000; font-size:10px; font-weight:normal; padding-left:2; background-color:#ffffff;' value=>\n");

				foreach ($v[1] as $kk => $vv) {
					echo '<option value="' . $vv . '">' . $vv . '</option>';
				}
				print("</select>");
			} else {
				if (isset($v[0]) && $v[0] === 'M') {
					print("$v[1]");
				} else {
					if (isset($v[0]) && $v[0] === 'h') {
						print("<input type=hidden name=frm_{$class}_c_{$k} value=$v[1]>");
					} else {

						print("<input type=text name=frm_{$class}_c_{$k} style='border:1px solid #b0c0f0; font-family:arial; color:#000000; font-size:10px; font-weight:normal; padding-left:2; background-color:#ffffff; MARGIN:1px;  background-height:10; background-width:10; BACKGROUND-POSITION:1% 1%; BACKGROUND-COLOR:#FFFFFF; PADDING-LEFT:1px; VERTICAL-ALIGN:middle;' value=>");

					}
				}
			}
			print("</td>\n");
		}
		print("<input type=hidden name=frm_action  value=add>\n");

		$this->print_current_input_vars(array('frm_action'));
		$desc = $this->get_class_description($rclass);
		$desc = $desc[2];
		?>
	</td>
	<td><input type=submit class=submitbutton name=Search value="Quick Add <?=$desc ?>"></td>
	</form>
	<?php
	print("</td> <td width=100%> </td> </tr> <tr> <td height=10 colspan=10> </td> </tr>  </table>  ");
	}

	function printListAddForm($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$vlist = exec_class_method($class, "addListForm", $parent, $class);
		if (!$vlist) {
			return;
		}

		$unique_name = "{$parent->getClName()}_$class";
		$showstring = "Show/Hide";
		$show_all_string = null;
		if ($login->getSpecialObject('sp_specialplay')->isOn('close_add_form')) {
			$visiblity = "visibility:hidden;display:none";
		} else {
			$visiblity = "visibility:visible;display:block";
		}

		$cdesc = get_description($class);
		$cdesc .= " for $parent->nname";

		$backgroundstring = "background:#fff;";
		$fontcolor = "black";
		$bordertop = "#d0d0d0";
		if ($sgbl->isBlackBackground()) {
			$backgroundstring = "background:#000;";
			$fontcolor = "#333333";
			$bordertop = "#444444";
		}

		?>

	<table cellpadding="0" cellspacing="0" background="img/skin/kloxo/default/default/expand.gif">
		<tr>
			<td>
				<font align=left style='color:<?=$fontcolor;?>;font-weight:bold'>
					<a style='color:<?=$fontcolor; ?> ;font-weight:bold'
					   href="javascript:toggleVisibility('listaddform_<?=$unique_name?>');"> &nbsp; &nbsp; Click Here to
						Add <?=$cdesc?> (<?=$showstring?>) </a> <?=$show_all_string?>
				</font> &nbsp; &nbsp; &nbsp;
			</td>
		</tr>
	</table>

	<div id="listaddform_<?=$unique_name?>" style="<?=$visiblity?>">
		<table width="100%" border="0" cellpadding=0 style=' border: 0px solid '>
			<tr>
				<td width="10"></td>
				<td>
					<table cellpadding=0 align=center cellspacing=0 width=90%>
						<tr>
							<td>
								<?php do_addform($parent, $class, null, true); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
		<?php

	}

	function print_real_search($name_list, $parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$col = $login->getSkinColor();

		$rclass = $class;
		$filtername = $parent->getFilterVariableForThis($class);
		$url = $_SERVER['PHP_SELF'];
		$gen_image_path = get_general_image_path();
		$btnpath = $gen_image_path . "/icon/";
		$imgpath = $gen_image_path . "/button/";

		$classdesc = $this->get_class_description($rclass);

		$unique_name = trim($parent->nname) . trim($class) . trim($classdesc[2]);
		$unique_name = fix_nname_to_be_variable($unique_name);

		$imgpath = $login->getSkinDir();
		$buttonpath = get_image_path() . "/button/";
		$img = $this->get_image($buttonpath, $rclass, "list", ".gif");

		$global_visible = false;
		$value = null;
		foreach ($name_list as $name => $width) {
			if (isset($this->frm_hpfilter[$filtername]["{$name}_o_cont"])) {
				$value = $this->frm_hpfilter[$filtername]["{$name}_o_cont"];
			} else {
				if ($login->issetHpFilter($filtername, "{$name}_o_cont")) {
					$value = $login->getHPFilter($filtername, "{$name}_o_cont");
				}
			}

			if ($value && $value !== '--any--') {
				$global_visible = true;
				break;
			}
		}

		if ($global_visible) {
			$visiblity = "visibility:visible;display:block";
			$showstring = null;
			$show_all_string = "(Click on show-all to hide)";
		} else {
			$showstring = "Show/Hide";
			$show_all_string = null;
			$visiblity = "visibility:hidden;display:none";
		}

		$backgroundstring = "background:#fffafa;";
		$backgroundnullstring = null;
		$bordertop = "#d0d0d0";
		if ($sgbl->isBlackBackground()) {
			$backgroundstring = "background:gray;";
			$backgroundnullstring = "background:gray;";
			$bordertop = "#333";
		}
		?>
	<!--
<table cellspacing=0 cellpadding=0>
<tr>
    <td><img src="lside.gif"></td>

    <td background="center.gif">
        <table>
            <tr>
                <td>Domain Name</td>
                <td>CPS</td>
                <td>Status</td>
                <td>Type of Hosting</td>
                <td colspan=2>Date</td>
            </tr>
            <tr>
                <td><input value="" name=""></td>
                <td><input value="" name=""></td>
                <td><input value="" name=""></td>
                <td><input value="" name=""></td>
                <td><input value="" name=""></td>
                <td><button>Search</button></td>
            </tr>
        </table>
    </td>

    <td><img src="rside.gif"></td>
</tr>
</table>
-->


	<fieldset
			style='<?=$backgroundnullstring?> padding: 0 ; text-align: middle ; margin: 0; border: 0px; border-top: 1px solid <?= $bordertop ?> '>
		<legend><font style='font-weight:bold'>Advanced Search <a
				href="javascript:toggleVisibility('search_<?=$unique_name?>');"><?=$showstring ?> </a> <?=$show_all_string?>
		</font></legend>
	</fieldset>
	<table width=90% border=0 cellspacing=0 cellpadding=0>
		<tr>
			<td><font style='font-weight:bold'>


				<div id=search_<?=$unique_name?> style='<?=$visiblity?>'>
					<table width=100% border=0 align=left cellpadding=0
						   style='<?=$backgroundstring?> border: 1px solid #<?=$col?>'>
						<tr>
							<td><img width=26 height=26 src=<?=$img?>></td>
						</tr>
						<tr>
							<td width=10> &nbsp; </td>
							<td>
								<table width=90% height=90% cellpadding=0 cellspacing=0>
									<form name=lpfform_rsearch method=<?=$sgbl->method ?>  action=<?=$url ?>
										  onsubmit="return true;">


										<?php

										$filarr[] = 'pagenum';
										$count = 0;

										foreach ($name_list as $name => $width) {

											$count++;
											$desc = "__desc_{$name}";

											$descr[$name] = get_classvar_description($rclass, $desc);

											if (!$descr[$name]) {
												print("Cannot access static variable $rclass::$desc");
												exit(0);
											}

											if (csa($descr[$name][2], ':')) {
												$_tlist = explode(':', $descr[$name][2]);
												$descr[$name][2] = $_tlist[1];
											}

											foreach ($descr[$name] as &$d) {
												if ($this->is_special_url($d)) {
													continue;
												}
												if (strstr($d, "%v") !== false) {
													$d = str_replace("[%v]", $classdesc[2], $d);
												}
											}

											print("<td nowrap align=right> <font style='font-weight: bold'>{$descr[$name][2]} </font> &nbsp; </td> <td>");
											$filarr[] = "{$name}_o_cont";
											$value = null;
											if (isset($this->frm_hpfilter[$filtername]["{$name}_o_cont"])) {
												$value = $this->frm_hpfilter[$filtername]["{$name}_o_cont"];
											} else {
												if ($login->issetHpFilter($filtername, "{$name}_o_cont")) {
													$value = $login->getHPFilter($filtername, "{$name}_o_cont");
												}
											}

											if ($width) {
												if ($width[0] === 's') {
													print("<select name=frm_hpfilter[$filtername][{$name}_o_cont]  class=searchbox size=1 width=10 maxlength=30>");
													foreach ($width[1] as $v) {
														$sel = null;
														if ($v === $value) {
															$sel = "SELECTED";
														}
														echo '<option value="' . $v . '" ' . $sel . '>' . $v . '</option>';
													}
													print("</select>");
												}
											} else {
												print("<input type=text name=frm_hpfilter[$filtername][{$name}_o_cont] value='$value'  class=searchbox size=11 maxlength=30>");
											}


											print("</td> ");

											if ($count === 3) {
												$count = 0;
												print("</tr> <tr> ");
											}
										}

										$this->print_current_input_var_unset_filter($filtername, $filarr);
										$this->print_current_input_vars(array('frm_hpfilter'));
										?>

										</td> </tr>
								</table>

							</td>
							<td>
								<input type=submit class=submitbutton name=Search value=Search>
								</form>
							</td>
						</tr>
						<tr>
							<td width=10> &nbsp; </td>
						</tr>
					</table>
				</div></td>
		</tr>
	</table>

										<?php

	}

	function isResourceClass($class)
	{
		return ($class === 'permission' || $class === 'resource' || $class === 'information');
	}

	function checkIfFilter($filter)
	{

		foreach ($filter as $k => $f) {
			if ($k !== 'view' && $k !== 'pagesize' && $k !== 'pagenum' && $k !== 'sortby' && $k !== 'sortdir' && $f && $f !== '--any--') {
				return true;
			}
		}
		return false;
	}

	function printObjectTable($name_list, $parent, $class, $blist = array(), $display = NULL)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$col = $login->getSkinColor();

		$view = null;

		if (exec_class_method($class, "hasViews")) {
			$blist[] = array($ghtml->getFullUrl("a=list&c=$class&frm_filter[view]=quota"), 1);
			$blist[] = array($ghtml->getFullUrl("a=list&c=$class&frm_filter[view]=normal"), 1);
		}

		print_time("$class.objecttable");

		$rclass = $class;

		if ($this->frm_accountselect !== null) {
			$sellist = explode(',', $this->frm_accountselect);
		} else {
			$sellist = null;
		}

		$filtername = $parent->getFilterVariableForThis($class);
		$sortdir = null;
		$sortby = null;
		$fil = $login->getHPFilter();
		if (isset($fil[$filtername]['sortby'])) {
			$sortby = $fil[$filtername]['sortby'];
		}
		if (isset($fil[$filtername]['sortdir'])) {
			$sortdir = $fil[$filtername]['sortdir'];
		}

		$pagesize = (int) $login->issetHpFilter($filtername, 'pagesize') ? $login->getHPFilter($filtername, 'pagesize') : exec_class_method($rclass, "perPage");

		if (!(int) $pagesize) {
			$pagesize = 10;
		}

		$view = null;
		if (isset($fil[$filtername]['view'])) {
			$view = $fil[$filtername]['view'];
			dprintr($view);
		}

		if (!$name_list) {
			if (csa($class, "all_")) {
				$__tcl = strfrom($class, "all_");
				$name_list = exec_class_method($__tcl, "createListNlist", $parent, $view);
				foreach ($name_list as $k => $v) {
					if (csa($k, "abutton")) {
						unset($name_list[$k]);
					}
				}
				$name_list = lx_merge_good(array('parent_name_f' => '10%'), $name_list);
			} else {
				$name_list = exec_class_method($class, "createListNlist", $parent, $view);
			}
		}

		$iconpath = get_image_path() . "/button";

		$buttonpath = get_image_path() . "/button/";
		$nlcount = count($name_list) + 1;
		$imgheadleft = $login->getSkinDir() . "/top_lt.gif";
		$imgheadleft = $login->getSkinDir() . "/top_lt.gif";
		$imgheadleft2 = $login->getSkinDir() . "/top_lt.gif";
		$imgheadright = $login->getSkinDir() . "/top_slope_rt.gif";
		$imgheadbg = $login->getSkinDir() . "/top_bg.gif";
		$imgbtnbg = $login->getSkinDir() . "/btn_bg.gif";
		$imgtablerowhead = $login->getSkinDir() . "/tablerow_head.gif";
		$imgtablerowheadselect = $login->getSkinDir() . "/top_line_medium.gif";
		$imgbtncrv = $login->getSkinDir() . "/btn_crv_right.gif";
		$imgtopline = $login->getSkinDir() . "/top_line.gif";
		$skindir = $login->getSkinDir();

		$classdesc = $this->get_class_description($rclass, $display);

		$unique_name = trim($parent->nname) . trim($class) . trim($display) . trim($classdesc[2]);

		$unique_name = fix_nname_to_be_variable($unique_name);


		?>
	<br>
	<script> var ckcount<?=$unique_name; ?> ; </script>
		<?php
	if (!$sortby) {
		$sortby = exec_class_method($rclass, "defaultSort");
	}
		if (!$sortdir) {
			$sortdir = exec_class_method($rclass, "defaultSortDir");
		}

		$obj_list = $parent->getVirtualList($class, $total_num, $sortby, $sortdir);


		if (exec_class_method($rclass, "isdefaultHardRefresh")) {
			exec_class_method($rclass, "getExtraParameters", $parent, $obj_list);

		} else {
			if ($this->frm_hardrefresh === 'yes') {
				exec_class_method($rclass, "getExtraParameters", $parent, $obj_list);
			}
		}

		$pluraldesc = get_plural($classdesc[2]);

		if ($login->issetHpFilter($filtername, 'pagenum')) {
			$cgi_pagenum = $login->getHPFilter($filtername, 'pagenum');
		} else {
			$cgi_pagenum = 1;
		}

		$showvar = null;
		if ($login->issetHpFilter($filtername, 'show')) {
			$showvar = $login->getHPFilter($filtername, 'show');
		}

		if ($showvar) {
			$showvar = "(" . ucfirst($showvar) . ")";
		}

		$filterundermes = null;
		if ($login->issetHpFilter($filtername) && $this->checkIfFilter($login->getHPFilter($filtername))) {
			$filterundermes = "({$login->getKeywordUc('search_on')}";
			if ($total_num == 0) {
				$filterundermes .= ". Click on show all to see all the objects";
			}
			$filterundermes .= ")";
		}

		$perpageof = null;
		$lower = $pagesize * ($cgi_pagenum - 1) + 1;
		if ($lower > $total_num) {
			$lower = $total_num;
		}
		if ($pagesize * $cgi_pagenum > $total_num) {
			$upper = $total_num;
		} else {
			$upper = $pagesize * $cgi_pagenum;
		}
		$total_page = strtil($total_num / $pagesize, ".") + 1;
		$perpageof = "$lower to $upper of ";

		if ($sgbl->isBlackBackground()) {
			$backgroundstring = "background:#222222;";
			$stylebackgroundstring = "style='background-color:#000000; background:#000000;'";
			$filteropacitystringspan = "<span style='background:black' > ";
			$filteropacitystring = "style='FILTER:progid;-moz-opacity:0.5'";
			$filteropacitystringspanend = "</span>";

			$backgroundcolorstring = "#000000";
			$imgtopline = $login->getSkinDir() . "/black.gif";
			$blackstyle = "style='background:black;color:gray;border:1px solid gray;'";
			$imgtablerowhead = null;
			$col = "333";
			$bordertop = "#444444";

		} else {
			$blackstyle = null;
			$backgroundstring = "background:#fffafa;";
			$stylebackgroundstring = null;
			$filteropacitystring = null;
			$filteropacitystringspan = null;
			$filteropacitystringspanend = null;
			$backgroundcolorstring = "#ffffff";
			$bordertop = "#d0d0d0";
		}

		if (!$sellist && !$this->isResourceClass($class)) {
			print(" <fieldset style='$backgroundstring padding: 0 ; text-align: middle ; margin: 0; border: 0px; border-top: 1px solid $bordertop'><legend><font style='font-weight:bold'>$pluraldesc $showvar  {$login->getKeyword('under')} {$parent->getId()} <font color=red>$filterundermes </font> {$this->print_machine($parent)} ({$perpageof}$total_num)</font> </legend> </fieldset> ");
		}

		if (!$sellist && !$this->isResourceClass($class) && !$gbl->__inside_ajax) {
			?>
		<table width=90% cellpadding=0 cellspacing=0 border=0
			   style=' <?=$backgroundstring ?>  border: 1px solid #<?=$col?>; '>
			<tr>
				<td valign=bottom height=10> &nbsp;  </td>
			<tr>
				<td width=10> &nbsp;  </td>
				<td><?php $this->print_list_submit($class, $blist, $unique_name); ?></td>
				<td> <?php $this->print_search($parent, $class); ?> </td width=10>
				&nbsp;  </td> </tr>
			<tr>
				<td height=10> &nbsp; </td>
			</tr>
		</table>
			<?php

		}


		if (!$sellist && !$this->isResourceClass($class) && !$gbl->__inside_ajax) {
			$imgshow = get_general_image_path() . "/button/btn_show.gif";

			print("<table cellpadding=0 cellspacing=0 width=95% border=0  valign=middle> <tr> <td colspan=100 height=6></td> </tr> <tr valign=middle >");

			$imgbtm1 = get_general_image_path() . "/button/btm_01.gif";
			$imgbtm2 = get_general_image_path() . "/button/btm_02.gif";
			$imgbtm3 = get_general_image_path() . "/button/btm_03.gif";
			$imgshow = get_general_image_path() . "/button/btn_show.gif";

			$rpagesize = exec_class_method($rclass, "perPage");
			if ($rpagesize > 1000) {
				$width = 50;
			} else {
				$width = 70;
			}

			print("<td nowrap> &nbsp;&nbsp;</td>");
			print("<td ><b>Page</b>&nbsp;</td> ");

			$last = false;
			foreach (range(1, $total_page) as $i) {
				if ($i > 6) {
					$last = true;
				}
				if ($sgbl->isBlackBackground()) {
					$col = "333";
				}
				if ($i == $cgi_pagenum) {
					$bgcolorstring = "background: #$col";
				} else {
					$bgcolorstring = "";
				}
				print("<form name=page$unique_name$i method=$sgbl->method action=''>");
				print("<td width=6 style='border: 1px solid #$col; $bgcolorstring'>");
				$this->print_current_input_var_unset_filter($filtername, array('pagenum'));
				$this->print_current_input_vars(array('frm_hpfilter'));
				if ($last) {
					print(" <input type=hidden name=\"frm_hpfilter[$filtername][pagenum]\" type=text value=$total_page class=small>");
					print("<a  href=javascript:page$unique_name$i.submit()>...Last&nbsp; </a>");
				} else {
					print(" <input type=hidden name=\"frm_hpfilter[$filtername][pagenum]\" type=text value=$i class=small>");
					print("<a  href=javascript:page$unique_name$i.submit()> &nbsp;$i&nbsp; </a>");
				}
				print("</form> </td>");

				if ($last) {
					break;
				}
			}

			print("<td width=100%> </td>");

			print("<td nowrap> <b>Show</b> &nbsp;</td>\n");
			$f_page = (int) $login->issetHpFilter($filtername, 'pagesize') ? $login->getHPFilter($filtername, 'pagesize') : $pagesize;
			if ($rpagesize < 1000) {
				$list = array($rpagesize / 2, $rpagesize, $rpagesize * 2, $rpagesize * 4, $rpagesize * 8, $rpagesize * 16);
				$i = 0;
				foreach ($list as $l) {
					$i++;
					if ($l == $f_page) {
						$bgcolorstring = "background: #$col";
					} else {
						$bgcolorstring = "";
					}
					print("<td width=6 style='border: 1px solid #$col; $bgcolorstring'>");
					print("<form name=perpage_$i$unique_name method=$sgbl->method action=/display.php>");
					$this->print_current_input_var_unset_filter($filtername, array('pagesize', 'pagenum'));
					$this->print_current_input_vars(array('frm_hpfilter'));
					print("<input type=hidden name=frm_hpfilter[$filtername][pagesize] value=$l>");
					print("</form> ");
					print("<a href=javascript:perpage_$i$unique_name.submit() >&nbsp;$l&nbsp;</a> ");
					print('</td> ');
				}
			}
			echo '</tr></table>';
		}
		?>

    <table width="100%"> <tr> <td align="center" style='border:0px solid black'>
    <table cellspacing="2" cellpadding="2" width="97%" align="center">
    <tr>
		<td class="rowpoint"></td>
		<td colspan="<?=$nlcount; ?>">

			<!--    </td></tr><tr><td height=2 colspan=2></td></tr></table> -->
    <tr height="25" valign="middle">

    <?php
		if (!$sgbl->isBlackBackground()) {
		print("<td bgcolor=$backgroundcolorstring> </td> ");
	}
		?>

	<?php

		if (!$this->isResourceClass($class) && !$gbl->__inside_ajax) {
			?>
			<td width=10 background=<?=$imgtablerowhead ?>>
				<form name="formselectall<?=$unique_name; ?>" value=hello> <?=$filteropacitystringspan ?>
					<input <?=$filteropacitystring ?>   type=checkbox name="selectall<?=$unique_name; ?>"
														value=on <?php if ($sellist) {
						echo "checked disabled";
					}  ?>
														onclick="javascript:calljselectall<?=$unique_name; ?> ()"> <?=$filteropacitystringspanend ?>
				</form>
			</td>
			<?php

		}
		$imguparrow = get_general_image_path() . '/button/uparrow.gif';
		$imgdownarrow = get_general_image_path() . '/button/downarrow.gif';

		foreach ($name_list as $name => $width) {
			$desc = "__desc_{$name}";

			if (csa($name, "abutton")) {
				$descr[$name] = array("b", "", "", "", 'help' => "");
			} else {
				$descr[$name] = get_classvar_description($rclass, $desc);
			}

			if (!$descr[$name]) {
				print("Cannot access static variable $rclass::$desc");
				exit(0);
			}

			if (csa($descr[$name][2], ':')) {
				$_tlist = explode(':', $descr[$name][2]);
				$descr[$name][2] = $_tlist[0];
			}

			foreach ($descr[$name] as &$d) {
				if ($this->is_special_url($d)) {
					continue;
				}
				if (strstr($d, "%v") !== false) {
					$d = str_replace("[%v]", $classdesc[2], $d);
				}
			}

			if ($width === "100%") {
				$wrapstr = "wrap";
			} else {
				$wrapstr = "nowrap";
			}

			if ($sortby && $sortby === $name) {
				if ($sgbl->isBlackBackground()) {
					$wrapstr .= " style='background:gray'";
				} else {
					$wrapstr .= " style='background:url($skindir/listsort.gif)'";
				}

				print("<td width=$width $wrapstr ><table cellspacing=0 cellpadding=2  border=0> <tr> <td class=collist rowspan=2 $wrapstr>");
			} else {
				if ($sgbl->isBlackBackground()) {
					$wrapstr .= " style='background:gray'";
				} else {
					$wrapstr .= " style='background:url($skindir/expand.gif)'";
				}
				print("<td width=$width $wrapstr class=collist>");
			}
			?>
			<b><?php $this->print_sortby($parent, $class, $unique_name, $name, $descr[$name])?> </b></font>

			<?php
		$imgarrow = ($sortdir === "desc") ? $imgdownarrow : $imguparrow;

			if ($sortby && $sortby === $name) {
				print("</td> <td width=15><img src=" . $imgarrow . " ></td><td ></td></tr></table>");
			}

			?>

			</td>

			<?php

		}

		$count = 0;
		$rowcount = 0;
		echo '</tr>';
		print_time('loop');

		$n = 1;
		foreach ((array) $obj_list as $okey => $obj) {
			if (!$obj) {
				continue;
			}

			// Admin object should not be listed ever.
			if ($obj->isAdmin() && $obj->isClient()) {
				continue;
			}

			$checked = $obj->isSelect() ? "" : "disabled";

			// Fix This.
			if ($sellist) {
				$checked = "checked disabled";
				if (!array_search_bool($obj->nname, $sellist)) {
					continue;
				}
			}

			$imgpointer = get_general_image_path() . "/button/pointer.gif";
			$imgblank = get_general_image_path() . "/button/blank.gif";

			$rowuniqueid = "tr$unique_name$rowcount";

			?>
			<script> loadImage('<?=$imgpointer?>') </script>
			<script> loadImage('<?=$imgblank?>') </script>

            <tr height=22 id=<?=$rowuniqueid ?>  class=tablerow<?=$count; ?>
				onmouseover=" swapImage('imgpoint<?=$rowcount; ?>','','<?=$imgpointer; ?>',1);document.getElementById('<?=$rowuniqueid ?>').className='tablerowhilite';"
				onmouseout="swapImgRestore();restoreListOnMouseOver('<?=$rowuniqueid ?>', 'tablerow<?=$count ?>','ckbox<?=$unique_name . $rowcount ?>')">
        <?php

			if (!$sgbl->isBlackBackground()) {
				print("<td $stylebackgroundstring id=td$unique_name.$rowcount width=5 class=rowpoint><img name=imgpoint$rowcount src=\"$imgblank\"></td>");
			}

			if (!$this->isResourceClass($class) && !$gbl->__inside_ajax) {
				?>
				<td width=10 style='<?=$backgroundstring ?>'> <?=$filteropacitystringspan ?>
					<input <?=$filteropacitystring ?> id=ckbox<?=$unique_name . $rowcount; ?>  class=ch1
													  type=checkbox <?=$checked ?> name=frm_accountselect
													  onclick="hiliteRowColor('tr<?=$unique_name . $rowcount; ?>','tablerow<?=$count; ?>',document.formselectall<?=$unique_name; ?>.selectall<?=$unique_name; ?>)";
					value="<?=$obj->nname ?>"> <?=$filteropacitystringspanend ?> </td>
				<?php

			}

			$colcount = 1;
			foreach ($name_list as $name => $width) {
				try {
					$this->printObjectElement($parent, $class, $classdesc, $obj, $name, $width, $descr, $colcount . "_" . $rowcount);
				} catch (exception $e) {
					break;
				}
				$colcount++;
			}

			print("</tr> ");
			if ($count === 0) {
				$count = 1;
			} else {
				$count = 0;
			}
			$rowcount++;

			if (!$sellist) {
				if ($n === ($pagesize * $cgi_pagenum)) {
					break;
				}
			}
			$n++;
		}

		print("<tr><td></td><td colspan=$nlcount>");
		if (!$rowcount) {
			if ($login->issetHpFilter($filtername, 'searchstring') && $login->getHPFilter($filtername, 'searchstring')) {
				?>
			<table width=95%>
				<tr align=center>
					<td width=100%><b> <?= $login->getKeyword('no_matches_found') ?>  </b></td>
				</tr>
			</table>
				<?php

			} else {
				$filtermessagstring = null;
				if ($login->issetHpFilter($filtername)) {
					$filtermessagstring = $login->getKeyword('search_note');

					?>
				<table width=95%>
					<tr align=center>
						<td width=100%><b> <?=$filtermessagstring?>   </b></td>
					</tr>
				</table>
					<?php

				} else {
					?>
				<table width=95%>
					<tr align=center>
						<td width=100%>
							<b>  <?=$login->getKeyword('no') ?> <?=get_plural($classdesc[2]) ?>   <?=$login->getKeyword('under')?> <?="{$parent->getId()}" ?>   </b>
						</td>
					</tr>
				</table>
					<?php

				}
			}
		}
		print("</td></tr>");
		print("<tr><td class=rowpoint></td><td colspan=" . $nlcount . " > <table cellpadding=0 cellspacing=0 border=0 width=100%> <tr height=1 style='background:url($imgtopline)'><td></td></tr> <tr><td>");

		if ($this->frm_action === 'selectshow') {
			return;
		}
		?>
	<script>ckcount<?=$unique_name;?> = <?=$rowcount . ";  ";?>
			function calljselectall<?=$unique_name; ?>()
			{
				jselectall(document.formselectall<?=$unique_name; ?>.selectall<?=$unique_name; ?>, ckcount<?=$unique_name; ?>, '<?=$unique_name;?>')
			}
	</script>
	<?php
	if ($sellist) {
		print("<table $blackstyle> <tr> <td >");
		print("<form method=$sgbl->method action={$_SERVER["PHP_SELF"]}>");

		$ghtml->print_current_input_vars(array("frm_confirmed"));
		$ghtml->print_input("hidden", "frm_confirmed", "yes");
		$ghtml->print_input("submit", "Confrm", "Confirm", "class=submitbutton");
		print("</form> ");

		print("</td> <td  width=30> &nbsp; </td> <td >");
		print("<form method=$sgbl->method action=\"/display.php\">");
		$this->print_current_input_vars(array("frm_action", "frm_accountselect"));
		$ghtml->print_input("hidden", "frm_action", "list");
		$ghtml->print_input("submit", "Cancel", "Cancel", "class=submitbutton");
		print("</form> ");

		print("</td> </tr> </table> ");

	}
		if ($sgbl->isBlackBackground()) {
			print("</td></tr></table>");
			print("</td></tr></table>");
			print("</td></tr></table>");
			return;
		}

		if (!$sellist && !$this->isResourceClass($class) && !$gbl->__inside_ajax) {
			$imgbtm1 = get_general_image_path() . "/button/btm_01.gif";
			$imgbtm2 = get_general_image_path() . "/button/btm_02.gif";
			$imgbtm3 = get_general_image_path() . "/button/btm_03.gif";
			$imgshow = get_general_image_path() . "/button/btn_show.gif";

			print("<table cellpadding=0 cellspacing=0 border=0 width=100%><tr><td >");
			print("<table cellpadding=0 cellspacing=0 border=0>");
			print("<form name=perpage_{$unique_name} method=$sgbl->method action=''>");
			print("<tr><td><img src='" . $imgbtm1 . "'></td><td background='" . $imgbtm2 . "'>");
			$rpagesize = exec_class_method($rclass, "perPage");
			if ($rpagesize > 1000) {
				$width = 50;
			} else {
				$width = 70;
			}

			print("<table width=90% cellpadding=0 cellspacing=0><tr><td width=40><b>Show</b></td><td width=$width>");
			$this->print_current_input_var_unset_filter($filtername, array('pagesize', 'pagenum'));
			$this->print_current_input_vars(array('frm_hpfilter'));
			$f_page = (int) $login->issetHpFilter($filtername, 'pagesize') ? $login->getHPFilter($filtername, 'pagesize') : $pagesize;
			if ($rpagesize < 1000) {

				print("<select class=textbox onchange='document.perpage_{$unique_name}.submit()' style='width:40' name=frm_hpfilter[$filtername][pagesize]>");
				$list = array($rpagesize / 2, $rpagesize, $rpagesize * 2, $rpagesize * 4, $rpagesize * 8, $rpagesize * 16);
				foreach ($list as $l) {
					$sel = null;
					if ($l == $f_page) {
						$sel = "SELECTED";
					}
					echo '<option value="' . $l . '" ' . $sel . '>' . $l . '</option>';
				}
				print("</select>");
			} else {
				print("<input type=text class=textbox style='width:25' name=frm_hpfilter[$filtername][pagesize] value=$f_page>");
			}

			print("<input type=image src='" . $imgshow . "'>");
			print("</td></tr></table>");
			print("</td><td><img src='" . $imgbtm3 . "'></td></tr></form></table>");
			print("</td><td align=right >");

			if ($rpagesize < 1000) {
				print("<form method=$sgbl->method action=''>");
				print("<table cellpadding=0 cellspacing=0  border=0 valign=middle><tr valign=middle><td><b> Page </b>");
				$this->print_current_input_var_unset_filter($filtername, array('pagenum'));
				$this->print_current_input_vars(array('frm_hpfilter'));
				print("<input class=textbox style='width:25px' name=\"frm_hpfilter[$filtername][pagenum]\" type=text value=$cgi_pagenum class=small></td><td >");
				print("<input type=image src='$imgshow'>");
				print("</form> </td></tr></table> ");
			}


		}

		print("</td></tr></table>");
		print("</td></tr></table>");
		print("</td> </tr></table>");
		//else {
		//
		//  $this->print_list_submit($blist);
		//  }


		//print_time("$class.objecttable", "$class.objecttable");
	}

	function getInheritVar()
	{
		$v = array("frm_o_o", "frm_o_cname", 'frm_action', 'frm_sortby', 'frm_sortdir', 'frm_searchstring', "frm_selectshowbase", "frm_consumedlogin");
		return $v;
	}

	function getCurrentInheritVar()
	{
		$inherit_var = $this->getInheritVar();

		foreach ($inherit_var as $v) {
			if (isset($this->__http_vars[$v])) {
				$refreshpost[$v] = $this->__http_vars[$v];
			}
		}
		return $refreshpost;
	}

	function print_list_submit($class, $blist, $uniquename)
	{
		$rclass = $class;
		$this->print_list_submit_start();

		foreach ((array) $blist as $b) {
			$this->print_list_submit_middle($b, $uniquename);
		}
		$refreshpost = $this->getCurrentInheritVar();
		$refreshpost['frm_list_refresh'] = 'yes';
		$url = $this->get_get_from_post(null, $refreshpost);

		$refresh = create_simpleObject(array('url' => "display.php?$url", 'purl' => "/display.php?frm_action=refresh&frm_o_cname=ffile", 'target' => ''));

		$this->print_list_submit_middle(array($refresh, 1), $uniquename);

		if (exec_class_method($rclass, "isHardRefresh")) {
			$hardrefresh = create_simpleObject(array('url' => "display.php?$url&frm_hardrefresh=yes", 'purl' => "/display.php?frm_action=hardrefresh&frm_o_cname=ffile", 'target' => ''));
			$this->print_list_submit_middle(array($hardrefresh, 1), $uniquename);
		}

		$this->print_list_submit_end();
	}

	function print_list_submit_start()
	{
		?>
    <table height=100% cellpadding=0 cellspacing=0 border=0 style="border-collapse: collapse;">
    <tr align=left>
<?php

	}

	function print_list_submit_middle($button, $uniquename)
	{


		global $gbl, $sgbl, $login, $ghtml;
		$iconpath = get_image_path() . "/button";

		$url = $button[0];
		$purl = NULL;
		if ($this->is_special_url($url)) {
			$purl = $url->purl;
			$target = $url->target;
			$url = $url->url;
		}
		$ac_descr = $this->getActionDetails($url, $purl, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);

		$descr = $ac_descr;
		$var = $_t_name;
		$file = $_t_file;

		$help = $this->get_full_help($descr['help']);

		$name = $descr[2];
		$form_name = str_replace(" ", "_", $file . "_" . $var);

		$form_name = $this->createEncForm_name($form_name);


		$image = $_t_image;
		$this->save_non_existant_image($image);

		$noselect = (isset($button[1]) && $button[1]) ? 1 : 0;
		$doconfirm = (isset($button[3]) && $button[3]) ? 1 : 0;
		$imgbtnsep = $login->getSkinDir() . "/btn_sep.gif";


		print(" <form name=form" . $form_name . " action=" . $path . ">");


		$this->print_input_vars($post);

		if (!$noselect) {
			print("<input id=accountsel name=frm_accountselect type=hidden>");
		}
		print("</form>");

		?>
	<td width=10></td>
	<td align=center valign=bottom>

		<?php
	if (!isset($button[2])) {
		$button[2] = NULL;
	}
		if (!$button[2]) {
			?>
    <span title='<?=$help ?>'> <a class=button
								  href="javascript:storevalue(document.form<?=$form_name; ?>,'accountsel','ckbox<?=$uniquename; ?>',ckcount<?=$uniquename; ?>, <?=$noselect ?>, <?=$doconfirm ?>)">

<?php

		}

		if (!$sgbl->isBlackBackground()) {
			print("<img height=15 width=15 src=$image>\n");
			$colorstring = null;
		} else {
			$colorstring = "color=#999999";
		}
		?>

		<?="<br> <font $colorstring class=lightandthin>" . $name . "</font> "  ?>

		<?php if (!$button[2]) ?> </a> </span> <?php

		?>

	</td>
	<td width=10></td>

		<?php

	}

	function print_list_submit_end()
	{
		print("</tr></table>");
	}

	function get_filter_var()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$post['frm_hpfilter'] = $login->getHPFilter();
		$string = $this->get_get_from_post(null, $post);
		return $string;
	}

	function get_help_url()
	{
		// TODO: Remove not used function
	}

	function get_session_vars()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$this->__http_vars = $login->c_session->http_vars;
	}

	function print_defintion($value)
	{
		// TODO: Remove not used function
	}

	function get_action_or_display_help($help, $flag)
	{
		// TODO: Remove not used function
	}

	function print_jscript_source($jsource)
	{
		print("<script language=Javascript src=$jsource> </script>\n");

	}

	function print_css_source($csource)
	{
		print("<link href='$csource' rel=stylesheet type=text/css>\n");
	}

	function get_lpanel_file()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$fpath = get_image_path() . "/";
		$path = getreal($fpath);
		$val = lscandir($path);
		return $val;
	}


	function printNavHistMenu()
	{
		global $gbl, $sgbl, $login, $ghtml;

		print("<script>");
		if ($login->getSpecialObject('sp_specialplay')->isOn('ultra_navig')) {
			foreach ((array) $gbl->__navigmenu as $n => $v) {
				create_navmenu($n, $v[0], $v[1]);
			}
		}
		print("window.histlist = new Menu('histlist',210);");
		if (isset($gbl->__histlist)) {
			end($gbl->__histlist);
			$ghtml->print_pmenu('histlist', key($gbl->__histlist), null, null, true);
			while (($val = prev($gbl->__histlist))) {
				$ghtml->print_pmenu('histlist', key($gbl->__histlist), null, null, true);
			}
			reset($gbl->__histlist);
		} else {
			$ghtml->print_pmenu('histlist', '__blank|No History');
		}
		print("</script>");
	}

	function print_refresh_key()
	{
		global $gbl, $sgbl, $login, $ghtml;


		?>
	<script>
		document.onkeydown = function(e)
		{
			e = e || window.event;
			if (e.keyCode == 27) {
				var b = document.getElementById('showimage');
				if (b) {
					b.style.visibility = 'hidden';
				}
				var b = document.getElementById('esmessage');
				if (b) {
					b.style.visibility = 'hidden';
				}
			}
			return true;
		}


	</script>
		<?php

		if ($sgbl->dbg <= 0) {
			return;
		}
		?>

	<script>
		document.onkeyup = function(e)
		{
			e = e || window.event;
			if (e.keyCode == 86 && e.ctrlKey) {
				top.mainframe.window.location.reload();
			}
			return true;
		}
	</script>
		<?php

	}

	function print_include_jscript($header = NULL)
	{
		global $gbl, $sgbl, $login;

		?>
	<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT">

		<?php
	$this->print_refresh_key();
		$this->print_jscript_source("/htmllib/js/lxa.js");
		$this->print_jscript_source("/htmllib/js/helptext.js");
		$this->print_jscript_source("/htmllib/js/preop.js");


		if (!$login->getSpecialObject('sp_specialplay')->isOn('enable_ajax') && ($header !== 'left_panel')) {
		} else {
			$this->print_jscript_source("/htmllib/extjs/adapter/yui/yui-utilities.js");
			$this->print_jscript_source("/htmllib/extjs/adapter/yui/ext-yui-adapter.js");
			$this->print_jscript_source("/htmllib/extjs/ext-all.js");
			$this->print_jscript_source("/htmllib/yui-dragdrop/dragdrop.js");
		}
		$this->print_jscript_source("/htmllib/js/drag.js");

		$func = null;
		if (!$header) {
			$func = "onLoad='lxLoadBody();'";
		}

		if (!$header) {
			$descr = $this->getActionDescr($_SERVER['PHP_SELF'], $this->__http_vars, $class, $var, $identity);
			$help = $this->get_full_help($descr[2]);
			$help = $this->get_action_or_display_help($help, "display");
			$this->print_defintion($help);
		}

		$skin = $login->getSkinDir();
		$css = "$skin/css.css";
		if (!lfile_exists(getreal($css))) {
			$css = "/htmllib/css/skin/base.css";
		}
		$this->print_css_source("/htmllib/css/common.css");
		$this->print_css_source($css);
		$this->print_css_source("/htmllib/css/ext-all.css");

		$l = @ getdate();
		$hours = $l['hours'];
		$minutes = $l['minutes'];

		if ($header === 'left_panel') {
			?>
		<script type="text/javascript">
			var gl_helpUrl;
			gl_tDate = new Date();
			var clockTimeZoneMinutes = <?=$l['minutes'] ?> - gl_tDate.getMinutes();
			var clockTimeZoneHours =   <?=$l['hours'] ?> - gl_tDate.getHours();

			function program_help()
			{
				window.open(top.mainframe.jsFindHelpUrl());
			}
			function lxCallEnd()
			{
			}

		</script>
		</head>
			<?php

		}
		?>
	<script>
		function jsFindFilterVar()
		{
			gl_filtervar = '<?=$this->get_filter_var() ?>';
			return gl_filtervar;
		}

		function jsFindHelpUrl()
		{
			if (document.all || document.getElementById) {
				gl_helpUrl = '<?=$this->get_help_url() ?>';
				return gl_helpUrl;
			}
		}
		function lxLoadBody()
		{
			if (top.topframe && typeof top.topframe.changeLogo == 'function') {
				top.topframe.changeLogo(0);
			}
			changeContent('help', 'helparea');
		}
	</script>
		<?php
	?>
	<script>
		var gl_skin_directory = '<?=$login->getSkinDir();?>';
	</script>
		<?php
	if ($header === 'left_panel') {
		echo "<script>lxCallEnd();</script>";
	} #[FIXME] This call a lxCallEnd a empty function
	}

	function print_refresh()
	{
		print("<script> top.mainframe.window.location.reload(); </script>");
	}

	function print_redirect_back($message, $variable, $value = null)
	{
		global $gbl, $sgbl, $login;

		$vstring = null;
		if ($value) {
			$value = htmlspecialchars($value);
			$vstring = "&frm_m_emessage_data=$value";
		}
		$parm = "frm_emessage=$message$vstring";
		if ($variable) {
			$parm .= "&frm_ev_list=$variable";
		}

		$last_page = $gbl->getSessionV("lx_http_referer");

		if (!$last_page) {
			$last_page = "/display.php?frm_action=show";
		}

		$current_url = $this->get_get_from_current_post(null);
		if ($last_page === $current_url) {
			log_log("redirect_error", "$last_page is same as the current url...\n");
			$last_page = "/display.php?frm_action=show";
		}

		$this->get_post_from_get($last_page, $path, $post);

		$get = $this->get_get_from_post(array("frm_ev_list"), $post);

		$this->print_redirect("$path?$get&$parm");

	}

	function print_redirect_back_success($message, $variable, $value = null)
	{
		global $gbl, $sgbl, $login;

		$vstring = null;
		if ($value) {
			$value = htmlspecialchars($value);
			$vstring = "&frm_m_smessage_data=$value";
		}
		$parm = "frm_smessage=$message$vstring";
		if ($variable) {
			$parm .= "&frm_ev_list=$variable";
		}

		$last_page = $gbl->getSessionV("lx_http_referer");

		if (!$last_page) {
			$last_page = "/display.php?frm_action=show";
		}

		$this->get_post_from_get($last_page, $path, $post);

		$get = $this->get_get_from_post(array("frm_ev_list"), $post);

		$this->print_redirect("$path?$get&$parm");
	}


	function print_redirect_to($red)
	{

		global $gbl, $sgbl, $login, $ghtml;

		if ($gbl->isetSessionV("redirect_to")) {
			$this->print_redirect($gbl->getSessionV("redirect_to"));
			$gbl->unsetSessionV("redirect_to");
			return;
		}

		$this->print_redirect($red);
	}


	function print_redirect($redirect_url, $windowurl = null)
	{
		global $gbl, $sgbl;


		$current_url = $this->get_get_from_current_post(null);

		if (ifSplashScreen() || $windowurl) {
			dprint("<br> <br> Redirect called with splash <br> ");
			dprint(" <b> <br> <br>  Click <a href=\"$redirect_url\"> <b>  xhere to go Continue. </a> </b> \n");

			if ($sgbl->dbg < 0 || (isset($gbl->__no_debug_redirect) && $gbl->__no_debug_redirect)) {
				?>
            <head>
            <meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
				<META HTTP-EQUIV="Refresh" CONTENT="0;URL=<?=$redirect_url ?>">
				<?php if ($windowurl) {
					?>
					<script>
						window.open('<?=$windowurl?>');
					</script>
                </head>
                <?php } ?>
				<?php

			} else {
				if ($windowurl) {
					?>
				<script>
					window.open('<?=$windowurl?>');
				</script>

					<?php

				}
			}

			exit(0);


		}


		if (($sgbl->dbg > 0) && !(isset($gbl->__no_debug_redirect) && $gbl->__no_debug_redirect)) {
			$cont = ob_get_contents();
			if ($gbl->__fvar_dont_redirect || csa($cont, "Notice") || csa($cont, "Warning") || csa($cont, "Parse error")) {
				print_time('full', "Page Generation Took: ");
				print(" <b> <br> <br>  Looks Like there are some errors... Or Been asked not to redirect Not redirecting... <br> Click <a href=\"$redirect_url\"> xHere to go there Anyways . </b> \n");
			} else {
				print_time('full', "Page Generation Took: ");
				print(" <b> <br> <br>  Looks Like there are some errors... Or Been asked not to redirect Not redirecting... <br> Click <a href=\"$redirect_url\"> xHere to go there Anyways . </b> \n");
			}
		} else {
			header("Location:$redirect_url");
			?>
		<head>
			<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
			<META HTTP-EQUIV="Refresh" CONTENT="0;URL=<?=$redirect_url ?>">
		</head>
			<?php

		}
		exit(0);
	}

	function print_redirect_left_panel($redirect_url)
	{
		echo '<script> top.leftframe.location="' . $redirect_url . '"; </script>';
		exit(1);
	}

	function print_redirect_self($redirect_url)
	{
		echo '<script> top.location="' . $redirect_url . '"; </script>';
		exit(1);
	}

	function print_table_header($heading)
	{
		global $gbl, $sgbl, $login;
		?>  <br><br>
	<table cellpadding="0" cellspacing="0" border="0" width="20%">
		<tr>
			<td bgcolor=<?=$login->skin->table_title_color?>><b><?=$heading?></b>
			</td>
		</tr>
		<tr>
			<td bgcolor="#A5C7E7"></td>
		</tr>
	</table>

		<?php

	}

	function get_full_help($help, $name = NULL)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$classdesc = NULL;
		if (!$help) {
			return null;
		}


		if ($name) {
			$val = " <font color=blue> " . $name . "</font>";
			if (preg_match("/\[%s\]/", $help)) {
				$help = str_replace("[%s]", $val, $help);
			} else {
				if ($help[strlen($help) - 1] != '.') {
					$help = "$help for $val.";
				}
			}
			if ($classdesc) {
				$tmp = array(&$help);
				$this->fix_variable_overload($tmp, $classdesc[1]);

			}

			return $help;
		}
		return $help;
	}


	function printGraphSelect($list)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$list) {
			return;
		}

		$cgi_o_o = $this->frm_o_o;


		$oldname = $ghtml->frm_c_graph_time;


		$subactionstr = null;

		if ($ghtml->frm_subaction) {
			$subactionstr = "<input type=hidden name=frm_subaction value={$ghtml->frm_subaction}>";
		}

		$cnamestr = null;
		if ($ghtml->frm_o_cname) {
			$cnamestr = "<input type=hidden name=frm_o_cname value={$ghtml->frm_o_cname}>";
		}
		$dttypestr = null;
		// This needs to be an array.
		if ($ghtml->frm_dttype) {
			$dttypestr = "<input type=hidden name=frm_dttype[val] value={$ghtml->frm_dttype['val']}>";
			$dttypestr = "<input type=hidden name=frm_dttype[var] value={$ghtml->frm_dttype['var']}>";
		}

		$frm_action = $ghtml->frm_action;
		$filter = null;
		$hpfilter = $login->getHPFilter();
		if ($hpfilter) {
			$filter['frm_hpfilter'] = $hpfilter;
		}

		?>
	<table width=100%>
		<tr>
			<td width=10></td>
			<td align=left>
				<form name="graphselectjump" method="<?=$sgbl->method ;?>" action="display.php">

					<?php
	foreach ($cgi_o_o as $k => $v) {
					?>
					<input type=hidden name='frm_o_o[<?=$k ?>][class]' value=<?=$v['class']?>>
					<?php if (isset($v['nname'])) { ?>
						<input type=hidden name='frm_o_o[<?=$k ?>][nname]' value=<?=$v['nname']?>>
						<?php

					}
				}
					?>

					<input type=hidden name=frm_action value=<?=$frm_action ?>>
					<?=$subactionstr ?>
					<?=$cnamestr ?>
					<?=$dttypestr ?>
					<?php $this->print_input_vars($filter) ?>
					Period <select class=textbox onChange='document.graphselectjump.submit()' name='frm_c_graph_time'>

					<?php
	foreach ($list as $k => $l) {
						$sssl = null;
						if ($k == $oldname) {
							$sssl = " SELECTED ";
						}
						echo '<option value="' . $k . '" ' . $sssl . '>' . $l . '</option>';
					}
						?>

				</select>


				</form>
			</td>
		</tr>
	</table>

					<?php

	}

	function printShowSelectBox($list)
	{
		global $gbl, $sgbl, $login, $ghtml;

		if (!$list) {
			return;
		}

		$cgi_o_o = $this->frm_o_o;

		$filteropacitystringspan = null;
		$filteropacitystringspanend = null;
		$filteropacitystring = null;
		if ($sgbl->isBlackBackground()) {
			$filteropacitystringspanend = "</span>";
			$filteropacitystringspan = "<span style='background:black' > ";
			$filteropacitystring = "style='background:black;color:#999999;FILTER:progid;-moz-opacity:0.5'";
		}


		$num = count($cgi_o_o) - 1;
		while ($num >= 0) {
			$class = $cgi_o_o[$num]['class'];
			$desc = $ghtml->get_class_description($class);
			if (isset($cgi_o_o[$num]['nname']) && !csa($desc[0], 'P')) {
				break;
			}
			$num--;
		}
		if ($num < 0) {
			return;
		}

		$oldname = $cgi_o_o[$num]['nname'];


		$subactionstr = null;
		if ($ghtml->frm_subaction) {
			$subactionstr = "<input type=hidden name=frm_subaction value={$ghtml->frm_subaction}>\n";
		}

		if ($ghtml->frm_consumedlogin) {
			$subactionstr .= "<input type=hidden name=frm_consumedlogin value={$ghtml->frm_consumedlogin}>";
		}

		$cnamestr = null;
		if ($ghtml->frm_o_cname) {
			$cnamestr = "<input type=hidden name=frm_o_cname value={$ghtml->frm_o_cname}>";
		}
		$dttypestr = null;
		// This needs to be an array.
		if ($ghtml->frm_dttype) {
			$dttypestr = "<input type=hidden name=frm_dttype[val] value={$ghtml->frm_dttype['val']}>";
			$dttypestr .= "<input type=hidden name=frm_dttype[var] value={$ghtml->frm_dttype['var']}>";
		}

		$frm_action = $ghtml->frm_action;
		$filter = null;

		$hpfilter = $login->getHPFilter();
		if ($hpfilter) {
			$filter['frm_hpfilter'] = $hpfilter;
		}

		$skindir = $login->getSkinDir();
		$forecolorstring = null;
		if ($sgbl->isBlackBackground()) {
			$forecolorstring = "color=gray";
		}

		$ststring = null;
		if ($sgbl->isBlackBackground()) {
			$ststring = "style='background:black;color:gray'";
		}
		if ($sgbl->isBlackBackground()) {
		} else {
			$col = "$skindir/expand.gif";
		}
		?>
	<table cellspacing=0 cellpadding=0 width=100%>
		<tr style="background:url('<?=$col?>')">
			<td nowrap><font <?php echo $forecolorstring ?> style='font-weight:bold'> Switch To Another </font></td>
			</td>
			<td align=center>


				<form name=topjumpselect method=<?=$sgbl->method ?> action=
				'display.php'>


				<?php
	foreach ($cgi_o_o as $k => $v) {
				?>
				<input type=hidden name='frm_o_o[<?=$k ?>][class]' value=<?=$v['class']?>>
				<?php
		if ($k != $num && isset($v['nname'])) {
					?>
					<input type=hidden name='frm_o_o[<?=$k ?>][nname]' value=<?=$v['nname']?>>
					<?php

				}
			}
				?>

				<input type=hidden name=frm_action value=<?=$frm_action ?>>
				<?=$subactionstr ?>
				<?=$cnamestr ?>
				<?=$dttypestr ?>
				<?php $this->print_input_vars($filter) ?>

				<?=$filteropacitystringspan ?>
				<select <?= $filteropacitystring ?> <?= $ststring ?>  class=textbox
																	  onChange='document.topjumpselect.submit()'
																	  name='frm_o_o[<?=$num ?>][nname]'>

				<?php
	foreach ($list as $k => $l) {
					$tdisp = $l->getId();
					if ($sgbl->isDebug()) {
						$tdisp = $l->getClName();
					}
					?>
					<option <?php if ($k == $oldname) {
						echo ' SELECTED ';
					} ?> value="<?=$k ?>"><?=$tdisp ?></option>
					<?php

				}
					?>

				</select> <?= $filteropacitystringspan ?>



				</form>
			</td>
			<td width=100%></td>
		</tr>
		<tr height=10>
			<td></td>
			<td></td>
	</table>

				<?php

	}

	function getActionDescr($path, $post, &$class, &$var, &$nname)
	{

		global $gbl, $sgbl, $login, $ghtml;

		$laclass = $suclass = null;

		if (isset($post['frm_o_cname'])) {
			$laclass = $post['frm_o_cname'];
		}
		if (isset($post['frm_o_o']) && $post['frm_o_o']) {
			$p = $post['frm_o_o'];
			$suclass = $p[count($p) - 1]['class'];

			$p = $post['frm_o_o'];
			for ($i = count($p) - 1; $i >= 0; $i--) {
				if (isset($p[$i]['nname'])) {
					$nname = exec_class_method($suclass, 'getClassId', $p[$i]['nname']);
					break;
				}
			}
		} else {
			$nname = $login->nname;
		}

		$name = "<font color=blue> $nname</font>";
		if (!$laclass && !$suclass) {
			$laclass = lget_class($login);
			$suclass = lget_class($login);
		}

		$var = null;
		if (isset($post['frm_action'])) {
			$var = strtolower($post["frm_action"]);
		}


		if ($var === "delete") {
			$class = $laclass;
			return array("", "", "Delete", "", 'desc' => "Delete", 'help' => $login->getKeywordUc('delete'), "{$login->getKeywordUc('delete')} $laclass");
		}

		if ($var === "list") {

			$class = $laclass;
			$desc = get_classvar_description($class, "__acdesc_list");

			if (!$desc) {

				$desc = get_classvar_description($laclass);
				$descr = $desc[2];
				$descri = get_plural($desc[2]);
				$help = "{$login->getKeywordUc('list')} $descri";
			} else {
				$descri = $desc[2];
				$help = $desc[2];
			}


			if (isset($post['frm_filter']['show'])) {
				$dvar = 'filter_show_' . $post['frm_filter']['show'];
				$desc = get_classvar_description($laclass, $dvar);
				$descri = $desc[2];
				$var = "list_" . '_filter_show_' . $post['frm_filter']['show'];
			}

			if (isset($post['frm_filter']['view'])) {
				$dvar = 'filter_view_' . $post['frm_filter']['view'];
				$desc = get_classvar_description($laclass, $dvar);
				$descri = $desc[2];
				$var = "list_" . '_filter_view_' . $post['frm_filter']['view'];
			}
			return array("", "", $descri, 'desc' => $descri, $help, 'help' => $desc['help']);
		}
		if ($var === "searchform") {
			$class = $laclass;
			$desc = get_classvar_description($laclass);
			$descr = $desc[2];
			$descri = get_plural($desc[2]);

			if (isset($post['frm_hpfilter'])) {
				$dvar = 'filter_show_' . $post['frm_hpfilter']['show'];
				$desc = get_classvar_description($laclass, $dvar);
				$descri = $desc[2];
				$var = "list_" . '_filter_show_' . $post['frm_hpfilter']['show'];
			}


			return array("", "", "$descr Search", 'desc' => "$descr Search", 'help' => "$descr Search", "Search $descr");
		}

		if ($var === "addform") {
			$class = $laclass;

			if (isset($post['frm_dttype'])) {
				$subvar = $post['frm_dttype']['var'];
				$sub = $post['frm_dttype']['val'];
			} else {
				$sub = null;
			}
			if ($sub) {
				$desc = get_classvar_description($laclass, "{$subvar}_v_$sub");
			} else {
				$desc = get_classvar_description($laclass);
			}
			if ($sub) {
				$var = $sub . "_" . $var;
			}
			$descr = $desc[2];
			return array("", "", "Add $descr", 'desc' => "Add $descr", 'help' => "{$login->getKeywordUc('add')} $descr", "{$login->getKeywordUc('add')} $descr");
		}

		if ($var === "updateform" || $var === "update") {
			if (isset($laclass)) {
				$class = $laclass;
			} else {
				$class = $suclass;
			}
			if (isset($post['frm_subaction'])) {
				$sub = "_" . $post['frm_subaction'];
			} else {
				$sub = null;
			}
			$var = $var . $sub;
			$desc = get_classvar_description($class, "__acdesc_update" . $sub);
			if ($desc) {
				if (csa($desc[2], "[%s]")) {
					$desc[2] = str_replace("[%s]", $name, $desc[3]);
				} else {
					$desc[2] .= "";
				}
				$desc['desc'] = $desc[2];
				return $desc;
			} else {
				$descr = "Update $sub";
			}
			return array("", '', $descr, 'desc' => $descr, 'help' => $desc['help']);
		}


		if ($var === "show" || $var === 'graph') {

			$realvar = $var;
			$class = $suclass;

			if (isset($post['frm_subaction'])) {
				$sub = "_" . $post['frm_subaction'];
			} else {
				$sub = null;
			}
			$var = $var . $sub;
			$desc = get_classvar_description($suclass, "__acdesc_$realvar" . $sub);

			if (!$desc) {
				$desc = get_classvar_description($suclass);

				if (csa($desc[0], "N")) {
					$count = count($post['frm_o_o']) - 1;
					$var .= "_nn_" . fix_nname_to_be_variable($post['frm_o_o'][$count]['nname']);
				}
				$descr = "{$desc[2]} {$login->getKeywordUc('home')} ";
				$help = "{$login->getKeywordUc('show')} {$desc[2]} details";
			} else {
				$descr = $desc[2];
				$help = $desc[2];
			}

			$desc = get_classvar_description($suclass);

			if (csa($desc[0], "N")) {
				$count = count($post['frm_o_o']) - 1;
			}
			return array("", '', $descr, 'desc' => $descr, 'help' => $help);
		}


		$descvar = "__ac_desc_" . $class . "_" . $var;

		$dvar = ucfirst($var);

		return array("", '', $dvar, 'desc' => $dvar, 'help' => $dvar);
	}


	function getActionDetails($url, $psuedourl, $buttonpath, &$path, &$post, &$class, &$name, &$image, &$identity)
	{

		global $gbl, $sgbl, $login, $ghtml;

		if (!$psuedourl) {
			$psuedourl = $url;
		}

		$this->get_post_from_get($psuedourl, $path, $post);

		$descr = $this->getActionDescr($path, $post, $class, $name, $identity);
		$descr['desc'] = $descr[2];
		$image = $this->get_image($buttonpath, $class, $name, ".gif");

		$this->get_post_from_get($url, $path, $post);


		return $descr;
	}


	function print_div_for_divbutton($key, $imgflag, $linkflag, $formname, $name, $imagesrc, $descr)
	{

		global $gbl, $sgbl, $login;

		$skincolor = $login->getSkinColor();
		$shevron = "{$login->getSkinDir()}/shevron_line.gif";

		$help = $this->get_full_help($descr[2]);
		$help = $this->get_action_or_display_help($help, "action");

		$dummyimg = get_image_path() . "/button/untitled.gif";

		$help = $descr['help'];

		$selectcolor = '#edf6fd';
		$blackbordercolor = 'white';
		$bgcolorstring = null;
		$forecolorstring = "color:#002244";
		if ($sgbl->isBlackBackground()) {
			$bgcolorstring = "bgcolor=#000";
			$forecolorstring = "color:#999999";
			$selectcolor = '#444444';
			$skincolor = '#000000';
			$blackbordercolor = '#000000';
			$imgflag = false;
		}


		if ($linkflag) {
			$displayvar = "<font style='$forecolorstring' class=icontextlink id=aaid_$formname href=\"javascript:document.form_$formname.submit()\" onmouseover=\" style.textDecoration='underline';\" onmouseout=\"style.textDecoration='none'\"> $descr[2] </font> </span>";
			$onclickvar = "onClick=\"javascript:document.form_$formname.submit()\"";
			$alt = $help;
		} else {
			$displayvar = "<span title=\"You don't have permission\" class=icontextlink>{$descr[2]} (disabled)</span>";
			$alt = "You dont have permission";
			$onclickvar = null;
		}

		$idvar = null;
		if ($login->getSpecialObject('sp_specialplay')->isOn('enable_ajax') && csb($key, "__v_dialog")) {
			$onclickvar = null;
			$idvar = "id=$key-comment";
		}

		if ($imgflag) {
			if ($linkflag) {
				$imgvar = "<img width=32 height=32   class=icontextlink onMouseOver=\"getElementById('aaid_$formname').style.textDecoration='underline'; \" onMouseOut=\"getElementById('aaid_$formname').style.textDecoration='none'; \" src=\"$imagesrc\" >";
			} else {
				$imgvar = "<img width=32 height=32 class=icontextlink   src=\"$imagesrc\" >";
			}

		} else {
			$imgvar = null;
		}

		?>

	<!--    <span style="background: url(<?=$imagesrc; ?>) no-repeat;" >
-->
	<table <?=$idvar?> style='border: 1px solid <?= $blackbordercolor ?> ; cursor: pointer' <?=$onclickvar ?>
					   onmouseover=" getElementById('aaid_<?=$formname?>').style.textDecoration='none' ; this.style.backgroundColor='<?=$selectcolor?>' ; this.style.border='1px solid #<?=$skincolor ?>';"
					   onmouseout="this.style.border='1px solid <?= $blackbordercolor ?>'; this.style.backgroundColor=''; getElementById('aaid_<?=$formname?>').style.textDecoration='none';"
					   cellpadding=3 cellspacing=3 height=80 width=60 valign=top>
		<tr>
			<td valign=top align=center><span title='<?=$alt ?>'> <?=$imgvar ?></td>
		</tr>
		<tr valign=top height=100%>
			<td width=60 align=center> <span title='<?=$alt ?>'> <?=$displayvar ?>
			</td>
		</tr>
	</table>
		<?php

	}

	function createEncForm_name($name)
	{
		global $gbl, $sgbl;

		return $name;
		if ($sgbl->dbg > 0) {
			return $name;
		}

		$name = str_replace("_", "", $name);
		$name = str_replace("php", "", $name);
		$name = str_replace("a", "z", $name);
		$name = str_replace("e", "r", $name);
		$name = str_replace("i", "x", $name);
		$name = str_replace("s", "q", $name);
		$name = str_replace("o", "p", $name);
		$name = str_replace("r", "j", $name);

		return $name;
	}

	function resolve_int_ext(&$url, &$psuedourl, &$target)
	{
		if ($this->is_special_url($url)) {

			if (isset($url->custom) && $url->custom) {
				$complete['url'] = $url->url;
				$complete['name'] = $url->name;
				$complete['bname'] = $url->bname;
				$target = $url->target;
				$url = $url->url;
				return $complete;
			} else {
				$nurl = $url->url;
				$psuedourl = $url->purl;
				$target = $url->target;
				$psuedourl = $this->getFullUrl($psuedourl);
				if (isset($url->__internal)) {
					$nurl = $this->getFullUrl($nurl);
				}
				$url = $nurl;
			}
		}
	}

	function print_toolbar()
	{
		$list = get_favorite("ndskshortcut");
		foreach ((array) $list as $l) {
			if ($l['ttype'] === 'separator') {
				print("<td nowrap width=20> </td>");
				continue;
			}
			print("<td  valign='middle'  align='left' width=5>");
			print('<form>');
			$l['ac_descr']['desc'] = "{$l['fullstr']} {$l['tag']}";
			$this->print_div_for_divbutton_on_header($l['url'], $l['target'], null, true, true, $l['url'], $l['__t_identity'], $l['_t_image'], $l['ac_descr']);
			print('</form>');
			print("</td >");
		}

	}

	function print_div_button_on_header($type, $imgflag, $key, $url, $ddate = null)
	{

		global $gbl, $sgbl, $login, $ghtml;
		$obj = $gbl->__c_object;
		$psuedourl = NULL;
		$target = NULL;

		$buttonpath = get_image_path() . "/button/";

		$linkflag = true;
		if (csa($key, "__var_")) {
			$privar = strfrom($key, "__var_");
			if (!$obj->checkButton($privar)) {
				$linkflag = false;
			}
		}

		$complete = $this->resolve_int_ext($url, $psuedourl, $target);

		if (!$target) {
			$target = "mainframe";
		}

		if ($complete) {
			$this->get_post_from_get($url, $path, $post);
			$descr = $this->getActionDescr($path, $post, $class, $name, $identity);
			$complete['name'] = str_replace($complete['name'], "<", "&lt;");
			$complete['name'] = str_replace($complete['name'], ">", "&gt;");
			$name = $complete['name'];
			$bname = $complete['bname'];
			$descr[1] = $complete['name'];
			$descr[2] = $complete['name'];
			$file = $class;
			if (lxfile_exists("img/custom/$bname.gif")) {
				$image = "/img/custom/$bname.gif";
			} else {
				$image = "/img/image/collage/button/custom_button.gif";
			}
			$__t_identity = $identity;
		} else {
			$url = str_replace("[%s]", $obj->nname, $url);

			$descr = $this->getActionDetails($url, $psuedourl, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
		}


		//$this->save_non_existant_image($image);

		$str = randomString(8);
		$form_name = $this->createEncForm_name("{$file}_{$name}_$str");
		$form_name = fix_nname_to_be_variable($form_name);

		if (csb($url, "http:/")) {
			$formmethod = "get";
		} else {
			$formmethod = $sgbl->method;
		}
		// Use get always. Only in forms should post be used.
		$formmethod = 'get';


		?>

	<td valign="middle" align="left" width=5>

		<form>
			<?php
	$this->print_input_vars($post);
			if (csa($url, "javascript")) {
				$form_name = $url;
			}
			$this->print_div_for_divbutton_on_header($url, $target, $key, $imgflag, $linkflag, $form_name, $name, $image, $descr);

			?>
		</form>
	</td>
			<?php

	}


	function print_div_for_divbutton_on_header($url, $target, $key, $imgflag, $linkflag, $formname, $name, $imagesrc, $descr)
	{

		global $gbl, $sgbl, $login;

		$skincolor = $login->getSkinColor();
		$shevron = "{$login->getSkinDir()}/shevron_line.gif";

		$help = $this->get_full_help($descr[2]);
		$help = $this->get_action_or_display_help($help, "action");

		$dummyimg = get_image_path() . "/button/untitled.gif";

		$help = $descr['desc'];

		if ($linkflag) {
			$displayvar = "<font style='color:#002244' class=icontextlink id=aaid_$formname href=\"javascript:document.form_$formname.submit()\" onmouseover=\" style.textDecoration='underline';\" onmouseout=\"style.textDecoration='none'\"> </font> </span>";
			if (csa($formname, "javascript")) {
				$onclickvar = "onClick=\"$formname\"";
			} else {
				if ($target == 'mainframe') {
					$onclickvar = "onClick=\"javascript:top.mainframe.window.location='$url'\"";
				} else {
					$onclickvar = "onClick=\"javascript:top.window.open('$url')\"";
				}
			}
			$alt = $help;
		} else {
			$displayvar = "<span title=\"You don't have permission\" class=icontextlink>{$descr[2]} (disabled)</span>";
			$alt = "You dont have permission";
			$onclickvar = null;
		}

		$idvar = null;
		if ($imgflag) {
			if ($linkflag) {
				$imgvar = "<img width=15 height=15   class=icontextlink onMouseOver=\"getElementById('aaid_$formname').style.textDecoration='underline'; \" onMouseOut=\"getElementById('aaid_$formname').style.textDecoration='none'; \" src=\"$imagesrc\" >";
			} else {
				$imgvar = "<img width=15 height=15 class=icontextlink   src=\"$imagesrc\" >";
			}

		} else {
			$imgvar = null;
		}

		?>

	<!--    <span style="background: url(<?=$imagesrc; ?>) no-repeat;" >
-->
<span title='<?=$alt ?>'>
<table <?=$idvar?> style='border: 1px solid #<?=$skincolor?>; cursor: pointer' <?=$onclickvar ?>
				   onmouseover=" getElementById('aaid_<?=$formname?>').style.textDecoration='none' ; this.style.backgroundColor='#fff' ; this.style.border='1px solid #<?=$skincolor ?>';"
				   onmouseout="this.style.border='1px solid #<?=$skincolor?>'; this.style.backgroundColor=''; getElementById('aaid_<?=$formname?>').style.textDecoration='none';"
				   cellpadding=3 cellspacing=3 height=10 width=10 valign=top>
	<tr>
		<td valign=top align=center> <?=$imgvar ?> </td>
	</tr>
	<tr valign=top height=100%>
		<td width=10 align=center> <span title='<?=$alt ?>'><?=$displayvar ?>
		</td>
	</tr>
</table>
		<?php

	}

	function getUrlInfo($url)
	{
		$buttonpath = get_image_path() . "/button/";
		if ($this->is_special_url($url)) {
			$psuedourl = $url->purl;
			$url = $url->url;
		} else {
			$psuedourl = $url;
		}
		$descr = $this->getActionDetails($url, $psuedourl, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);
		$ret['description'] = $descr;
		$ret['image'] = $image;
		return $ret;
	}


	function show_graph($maxval, $val, $info, $tabwidth = null, $unit = "MB", $type = "normal", $name = null, $varname = null)
	{

		global $gbl, $sgbl, $login, $ghtml;

		if ($sgbl->isBlackBackground()) {
			return;
		}

		if (!is_unlimited($maxval) && $maxval == 0) {
			return;
		}
		if ($tabwidth > 0 && $tabwidth != null) {
			$width = $tabwidth;
		} else {
			$width = 100;
		}

		$path = get_general_image_path() . "/icon/";

		$gwhite = $path . "g_white.gif";
		$gorange = $path . "g_orange.gif";
		$gyellow = $path . "g_yellow.gif";
		$ggreen = $path . "g_green.gif";
		$gred = $path . "g_red.gif";

		$percentage_val = 0;
		if (is_unlimited($maxval) || $maxval === 'Na') {
			$percentage_val = 0;
		} else {
			if ($maxval) {
				$percentage_val = $val / $maxval;
			}
		}
		$usedval = round($percentage_val * 100);

		$realval = $usedval;

		$usedval = min(110, $usedval);

		$quotaimg = null;
		if ($usedval > 90) {
			$quotaimg = $gred;
		}
		if ($usedval > 75 && $usedval <= 90) {
			$quotaimg = $gorange;
		}
		if ($usedval > 50 && $usedval <= 75) {
			$quotaimg = $gyellow;
		}
		if ($usedval >= 0 && $usedval <= 50) {
			$quotaimg = $ggreen;
		}


		$text = "<span class=last><font size=1 face=arial></font></span>";
		$help = null;
		$alt = null;
		$maxval = Resource::privdisplay($varname, null, $maxval);
		$val = Resource::privdisplay($varname, null, $val);
		if ($type === "small") {
			$help = "<br> <br> <font color=blue>$name </font> uses $val $unit ($realval%) of $maxval";
			$alt = lx_strip_tags($help);
			$help = "<b> Message: </b>  " . $help;
			$help = "onmouseover=\"changeContent('help',' $help')\" onmouseout=\"changeContent('help','helparea')\"";
		} else {
			$text = "<span class=last> <font size=1 face=arial>$realval%</font> </span>";
		}

		if ($info != null) {
			?>
    <table cellpadding=0 cellspacing=0 width=<?=$width + 50; ?>>
    <tr>
    <td class=collist width=50><b> <?=$info; ?> </b></td>
    <td>
<?php } ?>

		<table cellpadding=0 cellspacing=0 border=0 width=<?=$width; ?>>
			<tr>
				<td <?=$help ?>>
					<div id="quotameter" class="smallroundedmodule lowquota">
						<div class="first">
							<span class="first"></span>
							<span class="last"></span>
						</div>
						<div>
    <span id="quotausagebar" title='<?=$alt ?>'>

    <span class="first" style="background-image: url('<?=$quotaimg; ?>');  width:<?=$usedval; ?>%;"></span>
		<?=$text;?>
    </span>
						</div>
						<div class="last">
							<span class="first"></span>
							<span class="last"></span>
						</div>
					</div>
				</td>
			</tr>
		</table>
		<?php
	if ($info != null) {
		?>  </td></tr></table>


<?php

	}
	}


	function form_header($title)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$imgheadleft = $login->getSkinDir() . "/top_lt.gif";
		$imgheadright = $login->getSkinDir() . "/top_rt.gif";
		$imgheadbg = $login->getSkinDir() . "/top_bg.gif";
		$imgtopline = $login->getSkinDir() . "/top_line.gif";

		?>

	<table cellpadding=0 cellspacing=0 border=0 width=100%>
		<tr>
			<td width=60% valign=bottom>
				<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<tr>
						<td width=100% height=2 background="<?=$imgtopline; ?>"></td>
					</tr>
				</table>
			</td>
			<td align=right width=1%>
				<table cellpadding=0 cellspacing=0 border=0 width=100%>
					<tr>
						<td><img src="<?=$imgheadleft; ?>"></td>
						<td nowrap width=100% background="<?=$imgheadbg; ?>"><b><font
								color="#ffffff"><?=$title; ?></font></b></td>
						<td><img src="<?=$imgheadright; ?>"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>


		<?php

	}

	function form_footer()
	{
		?>
	<table cellpadding=0 cellspacing=0 border=0 width=100%>
		<tr>
			<td height=2 bgcolor="#a5c7e7"></td>
		</tr>
	</table>
		<?php

	}


	function getgroupvarselect($_multivarname)
	{
		global $gbl, $sgbl, $login, $ghtml;
		if (isset($gbl->__group_mode) && $gbl->__group_mode) {
			return "
		<td>
			<select class=\"textbox\" name=\"$_multivarname\">
				<option value=\"nochange\"> Dont Change </option>
				<option value=\"change\"> Change </option>
			</select>\n
			<br />
		</td> ";
		} else {
			return null;
		}
	}


	function print_fancy_select($class, $src, $dst)
	{


		$variablename = "frm_interface_template_c_{$class}_show_list";
		$ts_name = "ts_$variablename";
		$ts_name2 = "ts_{$variablename}2";
		$variable_description = "";
		$dstname = "destination";

		$form = "fancy_select";

		$stylestring = "style='width: 300;' size=20";
		$iconpath = get_image_path() . "/button";

		?>

	<table cellpadding=0 cellspacing=0>
		<tr>
			<td></td>
			<td>  <?=$variable_description ?>   </td>
			<td>
				<table width=100% cellspacing=0 cellpadding=0>
					<tr align=center>
						<td><b> Available </b></td>
						<td></td>
						<td><b> Selected </b></td>
					</tr>
					<tr height=20 valign=middle>

						<form name=<?=$form ?> action=/display.php>
							<input type=hidden name=<?=trim($variablename) ?>>
							<input type=hidden name=frm_action value=update>
							<input type=hidden name=frm_subaction value=update>
							<?php $this->html_variable_inherit("frm_o_o") ?>


							<td class=col width=100% align=center valign=middle><select class=textbox <?=$stylestring ?>
																						id=<?=$ts_name ?>  multiple
																						class=textbox
																						name=<?=trim($srcname) ?>>
								<?php
	foreach ($src as $k => $s) {
								if (csb($k, "__title")) {
									$desc = "----$k-----";
									$_t_image = null;
									$key = $k;
								} else {
									$key = base64_encode($s);
									$s = "j[class]=$class&$j[nname]=name&$s";
									$s = $this->getFullUrl($s, null);
									$ac_descr = $this->getActionDetails($s, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
									$desc = $ac_descr[2];
									$_t_image = dirname($_t_image) . "/small/" . basename($_t_image);
								}
								echo '<option
			value="' . $key . '"
			style="valign:middle;padding:0 0 0 25;
			width:300;height:20;
			background:url(' . $_t_image . ') no-repeat;">' . $desc . '</option>';
							}
								?>
							</select>

							</td>
							<td class=col width=15% align=center>
								<table align=center>
									<tr>
										<td><INPUT TYPE=button class=submitbutton
												   onClick="multiSelectPopulate('<?=$form ?>', '<?=trim($variablename) ?>',  '<?=$ts_name ?>', '<?=$ts_name2 ?>')"
												   VALUE=">>">

										</td>
									</tr>
									<tr>
										<td>
											<INPUT TYPE=button class=submitbutton
												   onClick="multiSelectRemove('<?=$form ?>', '<?=trim($variablename) ?>', '<?=$ts_name2 ?>')"
												   VALUE="<<">

										</td>
									</tr>
								</table>


							</td>

							<td class=col align=center width=30%>
								<select id=<?=$ts_name2 ?> <?=$stylestring ?> class=textbox multiple
										name=<?=trim($dstname) ?>>
								<?php

									foreach ($dst as $k => $d) {
										if (csb($d, "__title")) {
											$desc = $d;
											$_t_image = null;
										} else {

											$s = "j[class]=$class&$j[nname]=name&$d";
											$s = $this->getFullUrl($s, null);
											$ac_descr = $this->getActionDetails($s, null, $iconpath, $path, $post, $_t_file, $_t_name, $_t_image, $__t_identity);
											$d = base64_encode($d);
											$_t_image = dirname($_t_image) . "/small/" . basename($_t_image);
											$desc = $ac_descr[2];
										}
										echo '<option
			value="' . $d . '"
			style="valign:middle;padding:0 0 0 25;
			width:300;height:20;
			background:url(' . $_t_image . ') no-repeat;">' . $desc . '</option>';
									}
									?>
								</select>
								<script>
									createFormVariable('<?=$form ?>', '<?=trim($variablename) ?>', '<?=$ts_name2 ?>');
								</script>

							</td>
							<td><input type="button" class=submitbutton value="Up"
									   onclick="shiftOptionUp('<?=$form ?>', '<?=$variablename ?>', <?=$dstname ?>)"/><br/><br/>
								<input type="button" class=submitbutton value="Down"
									   onclick="shiftOptionDown('<?=$form ?>', '<?=$variablename ?>', <?=$dstname ?>)"/><br/><br/>
							</td>
					</tr>

				</table>
			</td>
		</tr>

		<tr>
			<td colspan=100 align=right><input type=submit class=submitbutton value=Update></td>
		</tr>
	</table>
	</form>
								<?php

	}

	static function fix_lt_gt($value)
	{
		$value = str_replace(array("<", ">"), array("&lt;", "&gt;"), $value);
		return $value;
	}


	function print_find($object)
	{

		global $gbl, $sgbl, $login, $ghtml;
		if ($sgbl->isBlackBackground()) {
			return;
		}
		$rows = 100;
		$cols = 240;
		$skindir = $login->getSkinDir();
		$value = $object->text_comment;
		$rclass = "frmtextarea";
		$variable = "frm_{$object->getClass()}_c_text_comment";
		print("<table cellpadding=0 cellspacing=3 align=center width=100%> <tr align=center> <td width=100%><table  align=center cellpadding=0 cellspacing=0 > <tr height=23 width=100%> <td align=center style='background:url(\"$skindir/expand.gif\")'> <font style='font-weight:bold'>&nbsp;Find </td> </tr> <tr> <td >");
		print("<input type=text name=find onKeyUp=\"searchpage(this)\"> ");
		print("</td> </tr> </table> </td> </tr> </table>  ");
	}

	function print_note($object)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$rows = 100;
		$cols = 240;
		$blackstyle = null;
		if ($sgbl->isBlackBackground()) {
			$blackstyle = "style='background:black;color:gray;border:1px solid gray;'";
			print("<font color=gray> note area </font> ");
			return;
		}
		$skindir = $login->getSkinDir();
		$value = $object->text_comment;
		$rclass = "frmtextarea";
		$url = $ghtml->getFullUrl("a=updateform&sa=information");
		$variable = "frm_{$object->getClass()}_c_text_comment";
		print("<table $blackstyle cellpadding=0 cellspacing=3 align=center> <tr align=center> <td width=14> </td> <td ><table  align=center cellpadding=0 cellspacing=0> <tr height=23> <td style='background:url(\"$skindir/expand.gif\")'> <font style='font-weight:bold'>&nbsp;Comments<a href=$url> [edit] </a> </td> </tr> <tr> <td >");
		print("<textarea nowrap id=textarea class=$rclass rows=$rows style='margin:0 0 0 0;width:$cols;height:100px;' name=\"$variable\" size=30>$value</textarea>\n");
		print("</td> </tr> </table> </td> </tr> </table>  ");
	}

	function print_multiselect($form, $variable, $rowuniqueid, $rowclass, $rowcount)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$_t_name = $this->getcgikey($variable->name);
		$this->checkForScript($this->$_t_name);
		$m_value = $this->$_t_name;
		$ts_name = "ts_$variable->name";
		$ts_name2 = "ts_{$variable->name}2";
		$size = $variable->count;
		$variable1 = $variable->variable1;
		$variable2 = $variable->variable2;


		$prevvar = $gbl->getSessionV('__tmp_redirect_var');
		if (isset($prevvar[$variable->name])) {
			$v2 = $prevvar[$variable->name];
			$v2 = explode(",", $v2);
		}
		print($variable->desc);
		?>


	<table width=100% cellpadding=0 cellspacing=0>
		<tr>
			<td><b> Available </b></td>
			<td colspan=1></td>
			<td><b> Selected </b></td>
			<td colspan=1></td>
		</tr>

		<tr>
			<td>
				<input type=hidden name=<?=$variable->name?>>


				<select class=textbox id=<?=$ts_name ?>  multiple size=5 class=textbox name=<?=$variable1->name ?>>
					<?php
		foreach ($variable1->option as $k => $option) echo '<option value="' . $k . '" >' . $option . '</option>';
					?>
				</select>

			</td>

			<td>

				<INPUT TYPE=button class=submitbutton
					   onClick="multiSelectPopulate('<?=$form ?>', '<?=trim($variable->name) ?>',  '<?=$ts_name ?>', '<?=$ts_name2 ?>')"
					   VALUE=">>">

				<INPUT TYPE=button class=submitbutton
					   onClick="multiSelectRemove('<?=$form ?>', '<?=trim($variable->name) ?>', '<?=$ts_name2 ?>')"
					   VALUE="<<">
			</td>


			<td>

				<select id=<?=$ts_name2?> class=textbox size=5 multiple name=<?=trim($variable2->name)?>>
					<?php
	$v2count = 0;
						foreach ($v2 as $k => $option) {
							$v2count++;
							echo '<option value="' . $option . '" >' . $option . '</option>';
						}
						?>
					<?php
	if (!$v2count) {
					foreach ((array) $variable2->option as $k => $option) echo '<option value="' . $option . '" >' . $option . '</option>';
				}
						?>
				</select>
				<script>
					createFormVariable('<?=$form?>', '<?=$variable->name?>', '<?=$ts_name2?>');
				</script>

			</td>

			<td>

				<input type="button" name=upbotton class=submitbutton value="Up"
					   onclick="shiftOptionUp('<?=$form?>', '<?=$variable->name?>', <?=$variable2->name?>)"/>
				<input type="button" name=downbutton class=submitbutton value="Down"
					   onclick="shiftOptionDown('<?= $form ?>', '<?=$variable->name?>', <?=$variable2->name?>)"/>

			</td>
		</tr>
	</table>


					<?php

	}


	function print_checkboxwithtext($form, $variable, $rowuniqueid, $rowclass, $rowcount)
	{
		if ($variable->mode === "or") {
			$txtval = "true";
			$txtval1 = "false";
			$txtcn = "'textdisable'";
			$txtcn1 = "'textenable'";
			$ckclass = "ckbox1";
			$tdash1 = "-";
			$tdash2 = "";
		}
		$blockcount = "bdd";
		$variable_description = "$variable->desc";

		if ($variable->mode === "depend") {
			$txtval = "false";
			$txtval1 = "true";
			$txtcn = "'textenable'";
			$txtcn1 = "'textdisable'";
			$ckclass = "ckbox2";
			$tdash1 = "";
			$tdash2 = "-";
		}

		if ($variable->checkbox->checked === "yes") {
			$tclass = "textdisable";
			$tdisabled = "disabled";
		} else {
			$tclass = "textenable";
			$tdisabled = "";
		}

		if ($variable->text->value != "" && $variable->text->value != "-") {
			$tval = $variable->text->value;
		} else {
			$tval = "";
		}

		print("$variable_description <br> ");


		?>
                        <input class=<?=$tclass?> <?=$tdisabled?> type=text
							   name=<?=$variable->text->name?>  value="<?=$variable->text->value?>"size=20 > <font
			class=small><?=$variable->text->text?></font><?=$variable->checkbox->desc?> <input class="<?=$ckclass?>"
																							   type=checkbox
																							   name="<?=$variable->checkbox->name; ?>"
																							   value="<?= trim($variable->checkbox->value); ?>" <?php if ($variable->checkbox->checked === "yes") {
			echo " CHECKED  ";
		} ?>
																							   onclick="<?="checkBoxTextToggle('$form', '{$variable->checkbox->name}', '{$variable->text->name}',  '{$variable->checkbox->value}', '{$variable->text->value}');" ?>">



     <?php

}


	function xml_print_page($full)
	{

		global $gbl, $sgbl, $ghtml, $login;
		$frmvalidcount = -1;

		$skincolor = $login->getSkinColor();

		$backgroundcolor = '#fff';
		$bordertop = "#d0d0d0";
		if ($sgbl->isBlackBackground()) {
			$skincolor = '333';
			$backgroundcolor = '#000';
			$bordertop = "#333";
		}
		$rowcount = -1;
		$rowclass = 1;
		$frmvalidcount++;
		$blockcount = "count";
		$width = "90%";

		$block = array_shift($full);


		if ($gbl->__inside_ajax) {
			$onsubmit = "onsubmit='return false;'";
			$gbl->__ajax_form_name = $block->form;
		} else {
			$onsubmit = "onsubmit=\"return check_for_needed_variables('$block->form');\"";
			print("<script> global_need_list = new Array(); </script>");
			print("<script> global_match_list = new Array(); </script>");
			print("<script> global_desc_list = new Array(); </script>");
		}

		print("<form name=$block->form id=$block->form action=$block->url  $block->formtype method=$sgbl->method $onsubmit>\n");
		dprint($block->form);

		$full = array_flatten($full);
		//dprintr($full);

		$totalwidth = '500';
		foreach ($full as $variable) {
			if ($variable->type === 'textarea' && $variable->width === '90%') {
				$totalwidth = '100%';
				break;
			}
		}

		if ($block->title) {
			print("<fieldset width=90% style='background-color:$backgroundcolor; border: 0px; padding: 10 10 10 10;border-top: 1px solid #$bordertop'><legend style='font-weight:normal;border:0px'><font color=#303030 style='font-weight:bold'>$block->title  </font> </legend></fieldset>   ");
		}
		print("<div align=left style='background-color:$backgroundcolor; width:90%'>");
		print("<div align=left style='width:$totalwidth ; border: 1px solid #$skincolor'>");
		$total = count($full);
		$count = 0;


		foreach ($full as $variable) {

			if ($variable->type == "subtitle") {
				print("</div>");
				print("<div style='padding: 10 10 10 10'> <font style='font-weight:bold'>$variable->desc</font> </div>  ");
				print("<div align=left style='display:hidden; width:$totalwidth ; border: 1px solid #$skincolor'>");
				$count = 0;
				continue;
			}

			if ($variable->type === 'hidden') {
				print("<input type=hidden name=\"$variable->name\" value=\"$variable->value\" > \n ");
				continue;
			}

			if ($variable->need === 'yes') {
				if ($gbl->__inside_ajax) {
					if (!isset($gbl->__ajax_need_var)) {
						$gbl->__ajax_need_var = array();
					}
					$gbl->__ajax_need_var[$variable->name] = $variable->desc;
				} else {
					print("<script> global_need_list['$variable->name'] = '$variable->desc'; </script>");
				}

			}

			if (isset($variable->match)) {
				if ($gbl->__inside_ajax) {
					if (!isset($gbl->__ajax_match_var)) {
						$gbl->__ajax_match_var = array();
					}
					if (!isset($gbl->__ajax_desc_var)) {
						$gbl->__ajax_desc_var = array();
					}
					$gbl->__ajax_match_var[$variable->name] = $variable->match;
					$gbl->__ajax_desc_var[$variable->name] = $variable->desc;
					$gbl->__ajax_desc_var[$variable->match] = $variable->matchdesc;
				} else {
					print("<script> global_match_list['$variable->name'] = '$variable->match';\n");
					print("global_desc_list['$variable->name'] = '$variable->desc';\n");
					print("global_desc_list['$variable->match'] =   '$variable->matchdesc';  </script>");
				}
			}

			$this->print_variable($block, $variable, $count);
			$count++;


		}

		print("</div>");
		print("</div>");
		print("</form> ");
	}


	function print_modify($form, $variable, $rowuniqueid, $rowclass, $rowcount)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$prevvar = $gbl->getSessionV('__tmp_redirect_var');

		$myneedstring = null;
		if ($variable->need === "yes") {
			$myneedstring = "<font color=red><sup>*</sup></font>";
		}

		$variable_description = "$variable->desc";
		$blackstyle = null;
		if ($sgbl->isBlackBackground()) {
			$variable_description = "<font color=#999999> $variable_description </font> ";
			$blackstyle = "style='background:black;color:gray;border:1px solid gray;'";
		}

		if ($variable->type === 'fileselect') {
			$fvalue = trim($variable->fvalue);
			$url = $this->getFullUrl("a=selectshow&l[class]=ffile&l[nname]=$fvalue");
			$url .= "&frm_selectshowbase=$fvalue";
		}
		$m_value = "";
		$realname = trim($variable->name);
		$realname = substr($realname, strlen('frm_'));
		// Don't Create extra variables 'pre-$var' and 'post-$var' if there are extra texts.


		if ($variable->value != "") {
			$this->checkForScript($variable->value);
			$m_value = $variable->value;
		} else {
			if (trim($variable->texttype) != "password") {
				$m_value = null;
				$index = trim($variable->name);
				if (isset($prevvar[$index])) {
					$this->checkForScript($prevvar[$index]);
					$m_value = $prevvar[$index];
				}
			}
		}

		if (trim($variable->text) != "") {
			$tbsize = 18;
		} else {
			$tbsize = 30;
		}
		if (trim($variable->texttype) == "") {
			$texttype = "text";
		} else {
			$texttype = $variable->texttype;
		}

		print("$variable_description $myneedstring <br>  ");
		print("$variable->pretext\n");
		print("<input $blackstyle class=\"$variable->name textbox\" type=\"$texttype\"  width=60%  name=$variable->name value=\"$m_value\"  size=\"$tbsize\"> $variable->posttext");


		if ($variable->type === 'fileselect') {
			?>
		<?php /*--- issue #609 - "'<?=$url ?>';);"><img" to "'<?=$url ?>');"><img;" ---*/ ?>
		<a href="javascript:void(0);"
		   onclick="javascript:selectFolder(<?=trim($form) ?>.<?=trim($variable->name)?>, '', '<?=$url ?>');"><img
				width=15 height=15 src="img/image/collage/button/ffile_ttype_v_directory.gif" border="0"
				alt="Select Folder" align="absmiddle"></a>
			<?php

		}

		if (isset($variable->confirm_password) && $variable->confirm_password) {

			?>
		<script language=Javascript src=/htmllib/js/divpop.js></script>
		<div id="showimage" style="visibility:hidden;position:absolute;width:250px;left:250px;top:250px">

			<table border="1" width="250" bgcolor="#000080" cellspacing="0" cellpadding="2">
				<tr>
					<td width="100%">
						<table border="0" width="100%" cellspacing="0" cellpadding="0"
							   height="36px">
							<tr>
								<td id="dragbar" style="cursor:hand; cursor:pointer" width="100%"
									onMousedown="password_initializedrag(event)">
									<ilayer width="100%" onSelectStart="return false">
										<layer width="100%" onMouseover="dragswitch=1;" onMouseout="dragswitch=0"><font
												face="Verdana"
												color="#FFFFFF"><strong>
											<small>Password Box</small>
										</strong></font></layer>
									</ilayer>
								</td>
								<td style="cursor:hand"><a href="#"
														   onClick="password_hidebox('showimage');return false"><img
										src="/img/image/collage/button/close.gif" width="16px"
										height="14px" border=0></a></td>
							</tr>
							<tr>
								<td width="100%" bgcolor="#FFFFFF" style="padding:4px" colspan="2">

									<!-- PUT YOUR CONTENT BETWEEN HERE -->

									<div id=password_container>
									</div>

									<!-- END YOUR CONTENT HERE -->

								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>

			<?php

			print("<input class=textbox type=button value=\"Generate Password\" onclick=\"javascript:generatePass('$form', '{$variable->name}');\" width=10>");
		}


		$postvar = $variable->postvar;
		if ($postvar) {
			print("<select width=60%  name=$postvar->name value=\"\"  size=\"1\">");
			foreach ($postvar->option as $vv) {
				echo '<option value="' . $vv . '" >' . $vv . '</option>';
			}
			print("</select>");
		}
	}

	function print_radio($form, $variable, $list, $rowuniqueid, $rowclass, $rowcount)
	{
		foreach ($list as $k => $l) {
			print("<input type=radio name=radio_$variable value=$k> $l <br>");
		}
		print("<input type=radio name=radio_$variable value=__provide__> Provide ");
		print("<input type=textbox name=$variable value=>");
	}


	function print_variable($block, $variable, $count)
	{

		global $gbl, $sgbl, $login, $ghtml;
		static $rowclass, $rowcount;

		if ($gbl->__inside_ajax && $variable->type === 'button') {
			if (strtolower($variable->value) === 'updateall') {
				$gbl->__ajax_allbutton = true;
			}
			return;
		}


		$skincolor = $login->getSkinColor();

		$skindir = $login->getSkinDir();

		$imgheadleft = "{$login->getSkinDir()}top_lt.gif";
		$imgheadright = "{$login->getSkinDir()}top_rt.gif";
		$imgheadleft = "{$login->getSkinDir()}top_lt.gif";
		$imgtablerowhead = "{$login->getSkinDir()}tablerow_head.gif";
		$imgheadbg = "{$login->getSkinDir()}top_bg.gif";
		$imgtopline = "{$login->getSkinDir()}top_line.gif";
		$imgsubtitle1 = "{$login->getSkinDir()}subtitle1.gif";
		$imgsubtitle2 = "{$login->getSkinDir()}subtitle2.gif";
		$imgsubtitle3 = "{$login->getSkinDir()}subtitle3.gif";
		$imgpointer = get_general_image_path("/button/pointer.gif");
		$imgblank = get_general_image_path("/button/blank.gif");

		$prevvar = $gbl->getSessionV('__tmp_redirect_var');

		$_error_list = array();
		if (isset($gbl->frm_ev_list)) {
			$_error_list = explode(",", $gbl->frm_ev_list);
		}

		$myneedstring = null;
		if ($variable->need === "yes") {
			$myneedstring = "<font color=red><sup>*</sup></font>";
		}

		$variable_description = "$variable->desc";

		$vname = $variable->name;
		if (csa($vname, "_aaa_")) {
			$vname = strtil($vname, "_aaa_");
		}


		$blackstyle = null;
		$filteropacitystringspan = null;
		$filteropacitystringspanend = null;
		$filteropacitystring = null;
		if ($sgbl->isBlackBackground()) {
			$variable_description = "<font color=#999999> $variable_description </font> ";
			$blackstyle = "style='background:black;color:gray;border:1px solid gray;'";
			$filteropacitystringspanend = "</span>";
			$filteropacitystringspan = "<span style='background:black' > ";
			$filteropacitystring = "style='background:black;color:#999999;FILTER:progid;-moz-opacity:0.5'";
		}

		if (preg_match("/frm_.*_c_/", $vname)) {
			$vname = preg_replace("/frm_.*_c_/i", "", $vname);
		}
		if ($vname && array_search_bool($vname, $_error_list)) {
			$divstyle = 'background-color:#ffd7d7';
		} else {
			$borb = null;
			if ($count) {
				$borb = "border-top:1px solid #aaaaaa;";

				if ($sgbl->isBlackBackground()) {
					$borb = "border-top:1px solid #333;";
				}
			}

			if ($rowclass) {
				$divstyle = "$borb background-color:#ffffff";
			} else {
				$divstyle = "$borb background-color:#faf8f8";
			}
			if ($sgbl->isBlackBackground()) {
				$divstyle = "$borb background-color:#000";
			}
			if ($variable->type === 'button') {
				if ($sgbl->isBlackBackground()) {
					$divstyle = "text-align:right;";
				} else {
					$divstyle = "text-align:right;$borb background:url($skindir/expand.gif)";
				}
			}

			$rowclass = $rowclass ? 0 : 1;
		}

		$rowuniqueid = "id$vname";
		$rowcount++;


		print("<div align=left style='padding:10 10 10 10 ;$divstyle;display:block' > ");

		$variable_description = ucwords($variable_description);


		switch ($variable->type) {

			case "checkbox":
				$m_value = null;
				if (isset($prevvar[trim($variable->name)])) {
					$this->checkForScript($prevvar[trim($variable->name)]);
					$m_value = $prevvar[trim($variable->name)];
				}
				$checkedvalue = trim($variable->checked);
				$checkv = null;
				if ($checkedvalue === "yes") {
					$checkv = " CHECKED ";
				} else {
					if ($checkedvalue === 'disabled') {
						$checkv = " DISABLED";
					}
				}

				print(" $filteropacitystringspan <input $filteropacitystring $blackstyle type=checkbox name=\"$variable->name\" $checkv  value=\"$variable->value\"> $variable_description $filteropacitystringspanend");
				break;


			case "select":
				$m_value = null;
				if (isset($prevvar[trim($variable->name)])) {
					$this->checkForScript($prevvar[trim($variable->name)]);
					$m_value = $prevvar[trim($variable->name)];
				}
				print("$variable_description <br> ");
				$v = $variable->name;
				print("$filteropacitystringspan <select $filteropacitystring class=textbox  name=\"$v\">\n");
				foreach ($variable->option as $k => $option) {
					$issel = false;
					if (csb($k, "__v_selected_")) {
						$k = strfrom($k, "__v_selected_");
						$issel = true;
					}
					$sel = null;
					if ($issel && !$m_value) {
						$sel = "SELECTED";
					}

					if ($k === $m_value) {
						$sel = "SELECTED";
					}

					echo '<option value="' . $k . '" ' . $sel . '>' . $option . '</option>';
				}
				print("</select> $filteropacitystringspanend");

				break;


			case "multiselect":
				$this->print_multiselect($block->form, $variable, $rowuniqueid, $rowclass, $rowcount);
				break;


			case "checkboxwithtext":
				$this->print_checkboxwithtext($block->form, $variable, $rowuniqueid, $rowclass, $rowcount);
				break;


			default:
			case "nomodify" :
				{
				$value = $variable->value;
				$value = self::fix_lt_gt($value);
				if ($sgbl->isLxlabsClient()) {
					$value = preg_replace("+(https://[^ \n]*)+", "<a href=$1 target=_blank style='text-decoration:underline'> Click Here </a>", $value);
				}
				$value = str_replace("\n", "\n<br> ", $value);
				$ttname = $variable->name;
				// Don't ever make this hidden. It is absolutely not necessary. The value is available directly itself.
				print("$variable_description: &nbsp; ");
				print("$value");
				break;
				}


			case "image" :
				$width = trim($variable->width);
				$height = trim($variable->height);
				print("$variable_description <br> ");
				print("<img src=$variable->value width=$width height=$height>");
				break;

			case "fileselect":
			case "modify":
				$this->print_modify($block->form, $variable, $rowuniqueid, $rowclass, $rowcount);
				break;

			case "file":

				print("$variable_description $myneedstring <br>");
				print("<input class=filebox type=file name=$variable->name  size=30 >");
				break;


			case "htmltextarea":
				{
				print("<tr> <td colspan=1000 >\n");
				if ($variable->height != "") {
					$rows = $variable->height;
				} else {
					$rows = "5";
				}
				if ($variable->width != "") {
					$cols = $variable->width;
				} else {
					$cols = "90%";
				}
				if (trim($variable->readonly) === "yes") {
					$readonly = " readonly ";
					$rclass = "frmtextareadisable";
				} else {
					$readonly = " ";
					$rclass = "frmtextarea";
				}
				$value = "$variable->value";


				if (!$value) {
					if (isset($prevvar[trim($variable->name)])) {
						$value = $prevvar[trim($variable->name)];
					}
				}


				include("htmllib/fckeditor/fckeditor_php5.php");
				$oFCKeditor = new FCKeditor($variable->name);
				$oFCKeditor->BasePath = '/htmllib/fckeditor/';
				$oFCKeditor->Value = $value;
				$oFCKeditor->Create();


				print("</td> </tr> \n");
				break;
				}


			case "textarea":
				print("$variable_description $myneedstring <br> ");
				if ($variable->height != "") {
					$rows = trim($variable->height);
				} else {
					$rows = "5";
				}
				if ($variable->width != "") {
					$cols = trim($variable->width);
				} else {
					$cols = "90%";
				}
				if (trim($variable->readonly) === "yes") {
					$readonly = " readonly ";
					$rclass = "frmtextareadisable";
				} else {
					$readonly = " ";
					$rclass = "frmtextarea";
				}
				$value = "$variable->value";


				if (!$value) {
					if (isset($prevvar[$variable->name])) {
						$value = $prevvar[$variable->name];
					}
				}

				print("<textarea nowrap  id=textarea_{$variable->name} class=$rclass rows=$rows style='margin:0 0 0 50;width:$cols;height:200px;' name=\"$variable->name\" size=30 $readonly>$value</textarea>\n");

				print("<script type=\"text/javascript\">createTextAreaWithLines('textarea_$variable->name');</script>\n");

				?>
				<style>
					.textAreaWithLines
					{
						display: block;
						margin: 0;
						font-style: Helvetica;
						border: 1px solid #666;
						border-right: none;
						background: #<?=$skincolor?> ;
					}
				</style>

					<?php
			break;


			case "button":
				$string = null;
				$bgcolor = null;
				$onclick = null;
				if (strtolower($variable->value) === 'updateall') {
					$string = "Click Here to Update all the objects that appear in the top selectbox with the above values";
					$bgcolor = "bgcolor=$skincolor";
					$onclick = "onclick='return updateallWarning();'";
				}
				print("$string");
				print("<input $blackstyle class=submitbutton type=submit $onclick name=$variable->name value=\"$variable->value\">");

				break;

		}

		print("</div> ");
	}


	function print_information($place, $type, $class, $extr, $vlist = null)
	{
		global $gbl, $sgbl, $login, $ghtml;
		global $g_language_mes;

		$pinfo = null;
		if ($vlist) {
			$info = $vlist;
		} else {
			$info = implode("_", array($class, $type, $extr, $place));
		}


		if (isset($g_language_mes->__commonhelp[$info])) {
			$info = $g_language_mes->__commonhelp[$info];
		}

		if ($place !== 'post') {
			if (isset($g_language_mes->__information[$info])) {
				$pinfo = $g_language_mes->__information[$info];
			}
		} else {
			dprint($info);
			print("<table cellpadding=0 cellspacing=0> <tr height=10> <td > </td> </tr> </table> ");
			if (lxfile_exists("__path_program_htmlbase/help/$info.dart")) {
				$pinfo = lfile_get_contents("__path_program_htmlbase/help/$info.dart");
			}
		}

		if (!$pinfo) {
			$info = implode("_", array($type, $extr, $place));
			if ($place !== 'post') {
				if (isset($g_language_mes->__information[$info])) {
					$pinfo = $g_language_mes->__information[$info];
				}
			} else {
				dprint($info);
				if (lxfile_exists("__path_program_htmlbase/help/$info.dart")) {
					$pinfo = lfile_get_contents("__path_program_htmlbase/help/$info.dart");
				}
			}
		}

		if (!$pinfo) {
			return;
		}

		$pinfo = str_replace("<%program%>", $sgbl->__var_program_name, $pinfo);

		$pinfo = explode("\n", $pinfo);
		$skip = false;
		foreach ($pinfo as $p) {
			$p = trim($p);
			if (csb($p, "<%ifblock:")) {
				$name = strfrom($p, "<%ifblock:");
				$name = strtil($name, "%>");

				$forward = true;
				if ($name[0] === '!') {
					$forward = false;
					$name = strfrom($name, "!");
				}

				if (method_exists($login, $name)) {
					if ($forward) {
						if (!$login->$name()) {
							$skip = true;
						}
					} else {
						if ($login->$name()) {
							$skip = true;
						}
					}
				} else {
					$skip = true;
				}
				continue;

			}
			if ($p === "</%ifblock%>") {
				$skip = false;
				continue;
			}

			if ($skip) {
				continue;
			}

			$out[] = $p;
		}

		$pinfo = implode("\n", $out);

		$fontcolor = "#000000";
		if ($sgbl->isBlackBackground()) {
			$fontcolor = "#999999";
		}


		$this->print_curvy_table_start();

		$pinfo = str_replace("\n", "<br>", $pinfo);
		$pinfo = str_replace("[b]", "<font style='font-weight: bold'>", $pinfo);
		$pinfo = str_replace("[/b]", "</font>", $pinfo);

		$ret = preg_match("/<url:([^>]*)>([^<]*)<\/url>/", $pinfo, $matches);

		if ($ret) {
			$fullurl = $this->getFullUrl(trim($matches[1]));
			$pinfo = preg_replace("/<url:([^>]*)>([^<]*)<\/url>/", "<a class=insidelist href=$fullurl> $matches[2] </a>", $pinfo);
		}

		if ($sgbl->isBlackBackground()) {
			print("<font color=#999999>");
		}
		print($pinfo);
		if ($sgbl->isBlackBackground()) {
			print("</font> ");
		}

		$this->print_curvy_table_end();

	}

	function print_curvy_table_start($width = "100")
	{
		global $gbl, $sgbl, $login;
		$a = $login->getSkinDir();
		if ($sgbl->isBlackBackground()) {
			return;
		}
		print("<table cellpadding=0  align=center cellspacing=0 width=\"100%\"><tr><td width=$width align=right><img src='$a/tl.gif' align=center></td ><td style='background: url($a/dot.gif) 0 0 repeat-x'></td ><td width=$width align=left><img src='$a/tr.gif' align=center></td > </tr><tr><td height=50px style='background: url($a/dot.gif) 90% 0 repeat-y;'></td><td align=left>");
	}

	function print_curvy_table_end($width = "100")
	{
		global $gbl, $sgbl, $login;
		$a = $login->getSkinDir();
		if ($sgbl->isBlackBackground()) {
			return;
		}
		print("<td style='background: url($a/dot.gif) 10% 0 repeat-y'></td></tr><tr><td width=$width align=right><img src='$a/bl.gif' align= center></td ><td style='background: url($a/dot.gif) 0 95% repeat-x'></td><td width=$width align=left><img src='$a/br.gif' align=center></td ></tr></table>");
	}

	function print_on_status_bar($message)
	{
		print("<script> top.bottomframe.updateStatusBar(\"$message\") </script>");
	}

	function print_message()
	{
		global $gbl, $sgbl, $login;

		global $g_language_mes;


		$img_path = get_general_image_path();


		$cgi_message = $this->cgi("frm_emessage");
		$this->checkForScript($cgi_message);


		$cgi_frm_smessage = $this->frm_smessage;

		if ($cgi_frm_smessage) {
			$value = $this->frm_m_smessage_data;
			if (isset($g_language_mes->__emessage[$cgi_frm_smessage])) {
				$mess = $g_language_mes->__emessage[$cgi_frm_smessage];
			} else {
				$mess = $cgi_frm_smessage;
			}
			$imgfile = $img_path . "/button/okpic.gif";

			unset($this->__http_vars['frm_smessage']);
			unset($this->__http_vars['frm_m_smessage_data']);
			$color = 'green';
			$message = "<font color=green> <b> Information: </b>  </font> ";
			$style = 'border: 1px solid green; background:#fff;';
			$fontstyle = 'color: #000';
			$mess = $this->format_message($mess, $value, true);
			$this->print_on_status_bar("$message $mess");
		}


		if ($cgi_message) {
			$value = $this->frm_m_emessage_data;
			if (isset($g_language_mes->__emessage[$cgi_message])) {
				$mess = $g_language_mes->__emessage[$cgi_message];
			} else {
				$mess = $cgi_message;
				if ($value) {
					$mess .= " [$value]";
				}
			}

			unset($this->__http_vars['frm_emessage']);
			unset($this->__http_vars['frm_m_emessage_data']);
			$imgfile = $img_path . "/button/warningpic.gif";
			$color = 'brown';
			$message = "<font color=red> <b> Alert: </b>  </font> ";
			$style = 'border: 1px solid red; background:#ffd7d7;';
			$fontstyle = 'color: #000';
			// In the status bar, you should print with mainframe. But in the main page, it should be simple url.
			$pmess = $this->format_message($mess, $value, false);
			$this->show_error_message($pmess, $message, $imgfile, $color, $style, $fontstyle);
			$pmess = $this->format_message($mess, $value, true);
			$pmess = substr($pmess, 0, 270);
			$this->print_on_status_bar("$message $pmess...");
		}


	}


	function show_error_message($mess, $message = null, $imgfile = null, $color = null, $style = null, $fontstyle = null)
	{
		if (!$imgfile) {
			$img_path = get_general_image_path();
			$imgfile = $img_path . "/button/warningpic.gif";
			$color = 'brown';
			$message = "<font color=red> <b> Error: </b>  </font> ";
			$style = 'border: 1px solid red; background:#ffd7d7;';
			$fontstyle = 'color: #000';
		}

		print("<div id=esmessage style='visibility:visible;position:absolute;width:95%;top:21%;left:2%'>");
		print(" <table width=100%   style='$style'  cellpadding=4 cellspacing=5 > <tr height=10> <td nowrap>  <a href=javascript:hide_a_div_box('esmessage')><img src=/img/image/collage/button/close.gif> <font style=small>Press Esc to close </font> </a> </td> <td > </td>   </tr> <tr> <td ><img src=$imgfile><font style='$fontstyle'> $message $mess </font></td></tr> <tr height=10> <td > </td> </tr> </table><br>");
		print("</div> ");
	}


	function replace_url($mess, $mainframeflag)
	{
		$tstring = null;
		if ($mainframeflag) {
			$tstring = "target=mainframe";
		}
		$ret = preg_match("/<url:([^>]*)>([^<]*)<\/url>/", $mess, $matches);
		if ($ret) {
			$fullurl = $this->getFullUrl(trim($matches[1]));
			$mess = preg_replace("/<url:([^>]*)>([^<]*)<\/url>/", "<a class=insidelist $tstring href=$fullurl> $matches[2] </a>", $mess);
		} else {
			$ret = preg_match("/<burl:([^>]*)>([^<]*)<\/burl>/", $mess, $matches);
			if ($ret) {
				$fullurl = $this->getFullUrl(trim($matches[1]), null);
				$mess = preg_replace("/<burl:([^>]*)>([^<]*)<\/burl>/", "<a class=insidelist $tstring href=$fullurl> $matches[2] </a>", $mess);
			}
		}
		return $mess;
	}

	function format_message($mess, $value, $mainframeflag)
	{
		$mess = str_replace("[b]", "<font style='font-weight:bold'>", $mess);
		$mess = str_replace("[/b]", "</font>", $mess);
		$mess = str_replace("[%s]", "<font style='font-weight:bold' color=black>$value</font>", $mess);
		$mess = str_replace("[%cs]", $value, $mess);
		$mess = $this->replace_url($mess, $mainframeflag);
		return $mess;
	}

	function print_lpanel_start_separator()
	{
		global $gbl, $sgbl, $login, $ghtml;
		?>
	<tr>
		<td colspan=3 height=2></td>
	</tr>
	<tr style='background:url(<?=$imgdark ?>)' height=1>
		<td colspan=3 height=1></td>
	</tr>
	<tr>
		<td colspan=3 height=2></td>
	</tr>
		<?php

	}

	function lpanel_beginning()
	{

		global $gbl, $sgbl, $login;
		$img_path = $login->getSkinDir();
		$tbg = $img_path . "/lp_bg.gif";
		$hpic = $img_path . "/lp_head.gif";
		$buttonpath = get_image_path() . "/button/";
		$hpic = null;
		$hpic = $img_path . "/gradient_bg.gif";
		$refresh = '/img/general/button/refresh.gif';
		$refreshover = '/img/general/button/refreshover.gif';
		$close = '/img/general/button/close.gif';
		$closeover = '/img/general/button/closeover.gif';

		$fullscreen = '/img/general/button/fullscreen.gif';
		$fullscreenover = '/img/general/button/fullscreenover.gif';

		if ($gbl->isOn('show_help')) {
			$altpanel = 'TreeMenu';
		} else {
			$altpanel = 'Xp Like Panel';
		}
		$widthstring = "width=$sgbl->__var_lpanelwidth";

		?>
	<script>
		___timglpanel_close_over = new Image();
		___timglpanel_refresh_over = new Image();
		___timglpanel_close_over.src = '<?=$closeover ?>';
		___timglpanel_refresh_over.src = '<?=$refreshover ?>';
		function js_reload_lpanel_with_filter()
		{
			window.open('/htmllib/lbin/lpanel.php', 'leftframe');
		}

	</script>
    <table cellpadding="0" <?=$widthstring ?>  height=100% cellspacing="0" border="0" valign=top align=middle>
    <tr> <td width=100% height=100% valign=top> <table cellpadding=0 cellspacing=0 width=100% height=100% valign=top>

    <tr height=14 style="background:url('')" align=right>
		<td valign=middle align=right>
			<?php print("<a href=javascript:js_reload_lpanel_with_filter()><img width=14 alt=refresh height=14 src=$refresh onMouseover=\"this.src='$refreshover'\" onMouseOut=\"src='$refresh'\" ></a>");
			print("&nbsp; <a href=/display.php?frm_action=resource target=mainframe><img width=14 height=14 src=$fullscreen onMouseOut=\"src='$fullscreen'\" alt='Expand TreeMenu' onMouseover=\"src='$fullscreenover'\"></a>");
			print("&nbsp; <a href=/display.php?frm_action=update&frm_subaction=switchhelp><img width=14 alt='Show $altpanel'height=14 src=$close onMouseOut=\"src='$close'\" onMouseover=\"src='$closeover'\"></a>");
			?> &nbsp; &nbsp;
		</td>
	</tr>
		<?php

	}

	function print_xpsingle($treename, $url, $psuedourl = NULL, $target = NULL, $nameflag = false)
	{

		if ($url === 'a=show') {
			$home = true;
		}

		$this->resolve_int_ext($url, $psuedourl, $target);

		$img_path = get_image_path();
		$buttonpath = $img_path . "/button";
		$iconpath = $img_path . "/button";


		$descr = $this->getActionDetails($url, $psuedourl, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);

		$desc = $descr[2];
		if ($nameflag) {
			$desc = "$__t_identity";
		}

		$help = $descr[3];

		$help = $this->get_action_or_display_help($help, "action");

		$desc = trim($desc);
		$open = 'false';
		$name = $file . "_" . $name;
		print("createSubMenu($treename, '<u>$desc</u>', '$url', '', '', '$image', 'mainframe');");

	}

	function tab_vheight()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$skincolor = $login->getSkinColor();


		$this->print_css_source("/htmllib/css/examples.css");
		print_ext_tree($login);
		print("<script type='text/javascript' src='/htmllib/js/tabs-example.js'></script>");

		?>
	<div style='background-color:#ffffff' id="tabs1">
		<div id="script"
			 style="overflow:no; height:100%;width:218px;border-bottom:1px solid #c3daf9; border-right:1px solid #c3daf9;"
			 class="tab-content">
			<br>
			<?$ghtml->xp_panel($login);?>
		</div>

		<div id="markup" class="tab-content">

			<div id="tree-div"
				 style="overflow:auto; height:100%;width:218px;;border-bottom:1px solid #c3daf9; border-right:1px solid #c3daf9;"></div>


		</div>
	</div>


		<?

	}

	function xp_panel($object)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$skincolor = $login->getSkinColor();
		$skin_name = basename($login->getSkinDir());
		if (csa($skin_name, "_")) {
			$skin_name = substr($skin_name, 0, strrpos($skin_name, "_"));
		}
		$skin_name = str_replace("_", " ", $skin_name);
		$icon_name = $login->getSpecialObject('sp_specialplay')->icon_name;
		$cl = $login->getResourceChildList();
		$qlist = $object->getList('resource');
		$skinget = $login->getSkinDir();
		?>
	<script language="javascript" type="text/javascript" src="/htmllib/js/xpmenu/ua.js"></script>
	<script language="javascript" type="text/javascript" src="/htmllib/js/xpmenu/PanelBarOrig.js"></script>
	<script language="javascript" type="text/javascript">
		function drawMenu()
		{
			var iCntr = 0;
			var objMenu;
			var strId, strLbl;
			if (this.open) {
				visib = 'visibile';
				disp = 'block';
				menuclass = "menuHeaderExpanded";
				image = '<?=$skinget?>/minus.gif';
			} else {
				visib = 'hidden';
				disp = 'none';
				menuclass = "menuHeaderCollapsed";
				image = '<?=$skinget?>/plus.gif';
			}

			document.write("<table  border=\"0\" cellspacing=\"0\"" + " cellpadding=\"0\" style=\"padding:0 0 0 0;\" width=\"100%\">");
			document.write("<tr style=\"background:url('<?=$skinget?>/expand.gif')\" onMouseover=\"this.style.background='url(<?=$skinget?>/onexpand.gif)'\" onMouseout=\"this.style.background='url(<?=$skinget?>/expand.gif)'\"><td style=\"width:180px;vertical-align: center; \"><font style='font-weight:bold'>&nbsp;" + this.label + "</font></td><td class=" + menuclass + " id=\"" + this.id + "\"" + "onclick=\"toggle(this)\">");
			document.write("&nbsp;<img id=" + this.id + "_image src=" + image + "></td></tr>");
			document.write("</table>");
			document.write("<div style=\"display: " + disp + "; visibility: " + visib + ";\"" + " class=\"menuItems\" id=\"" + this.id + "_child" + "\">");
			document.write("<table border=0 style='background:white' border=0 cellspacing=1 cellpadding=0 width=100%>");
			for (iCntr = 0; iCntr < this.smcount; iCntr++) {
				this.submenu[iCntr].render();
			}
			document.write("</table></div>");
		}
		function toggle(pobjSrc)
		{
			var strCls = pobjSrc.className;
			var strId = pobjSrc.id;
			var objTmp, child;

			if (pobjSrc.id != _currMenu) {
				objTmp = document.getElementById(_currMenu);
			}

			child = document.getElementById(strId + "_child");
			ichild = document.getElementById(strId + "_image");
			if (child.style.visibility == "hidden") {
				pobjSrc.className = "menuHeaderExpanded";
				child.style.visibility = "visible";
				child.style.display = "block";
				ichild.src = "<?=$skinget?>/minus.gif";
			} else {
				pobjSrc.className = "menuHeaderCollapsed";
				child.style.visibility = "hidden";
				child.style.display = "none";
				ichild.src = "<?=$skinget?>/plus.gif";
			}
			_currMenu = pobjSrc.id;
		}

	</script>

	<script language="javascript">
		var objTmp;
			<?php

			if (!$login->getSpecialObject('sp_specialplay')->isOn('disable_quickaction')) {
				$class = $login->getQuickClass();
				if ($class) {
					print("xpreso = createMenu('Quick Actions', '', true);");
					$rdesc = print_quick_action($class);
					print("createSubMenu(xpreso, '$rdesc', '', '', '', '', '');\n");
				}
			}

			$url = $this->getFullUrl("a=list&c=ndskshortcut");
			print("xxpFav = createMenu('<font color=#003360>Favorites<a href=\"$url\" target=mainframe>  [edit] </a>', '', true);");
			$rdesc = print_favorites();
			print("createSubMenu(xxpFav, '$rdesc', '', '', '', '', '');\n");


			if ($login->isLte('reseller')) {
				print("xxpDescr = createMenu('<font color=#003360>Usage', '', true);");
				$rdesc = null;
				foreach ((array) $qlist as $or) {
					if (!cse($or->vv, "usage") && !cse($or->vv, "_num")) {
						continue;
					}

					if (cse($or->vv, "last_usage")) {
						continue;
					}

					if (is_unlimited($or->resourcepriv)) {
						$limit = "&#8734;";
					} else {
						$limit = $or->display('resourcepriv');
					}

					$array = array("traffic_usage", "totaldisk_usage", "client_num", "maindomain_num", "vps_num");

					if (!array_search_bool($or->vv, $array)) {
						continue;
					}
					$rdesc .= "<tr align=left style=\"border-width:1 ;background:url($skinget/a.gif)\" > <td > <img width=15 height=15 src=/img/image/collage/button/state_v_{$or->display('state')}.gif> {$or->shortdescr} </td> <td nowrap> {$or->display('resourceused')} </td> <td align=left> $limit&nbsp;</td> </tr>";
				}
				print("createSubMenu(xxpDescr, '$rdesc', '', '', '', '', '');\n");
			}



			$forumurl = "http://forum.lxcenter.org";
			if (!$login->isAdmin() && isset($login->getObject('general')->generalmisc_b->forumurl)) {
				$forumurl = $login->getObject('general')->generalmisc_b->forumurl;
			}


			?>

		setTheme("XPClassic.css", null, null);
		initialize(<?=($sgbl->__var_lpanelwidth - 20) ?>);

	</script>


			<?php

	}

	function lpanel_start($help = null)
	{
		global $gbl, $sgbl, $login;
		$img_path = $login->getSkinDir();
		$tbg = $img_path . "/lp_bg.gif";
		$hpic = $img_path . "/lp_head.gif";
		$imgleftpoint = "$img_path/left_point.gif";
		$imgrightpoint = "$img_path/right_point.gif";
		$navtxt = "Navigation";
		$histxt = "History";
		$imgpoint = $imgleftpoint;
		$width = "198";
		$width = "100%";
		$xpos = 196;

		$l = getdate($gbl->c_session->logintime);
		$login_time = $l['hours'] . ":" . $l['minutes'] . ":" . $l['seconds'];
		$skin_name = $login->getSkinDir();
		if (csa($skin_name, "_")) {
			$skin_name = substr($skin_name, 0, strrpos($skin_name, "_"));
		}
		$skin_name = str_replace("_", " ", $skin_name);
		$name = substr($login->nname, 0, 12);

		?>
	<table>
		<tr>
			<td height=215></td>
		</tr>
	</table>

		<?php

		$this->lpanel_help();
	}

	function do_full_resource($object, $depth, $alistflag)
	{
		$treename = fix_nname_to_be_variable($object->nname);
		$this->do_resource(null, $object, $depth, $alistflag, "getResourceChildList", true, true);
	}


	function do_resource($tree, $object, $depth, $alistflag, $func, $complex = true, $showurlflag = true)
	{
		global $gbl, $sgbl, $login, $ghtml;
		static $scriptdone;

		if (!$scriptdone && $complex) {
			print("<link href=/htmllib/js/tree/dtree.css rel=stylesheet type=text/css>\n");
			$ghtml->print_jscript_source("/htmllib/js/tree/dtree.js");
			$scriptdone = true;
		}

		$treename = "_" . fix_nname_to_be_variable($object->nname);

		?>

	<table width=90% cellpadding=0 cellspacing=0 valign=top>
		<tr>
			<td valign=top align=left>


				<?php

				if ($complex) {
					print("<div class='dtree'>");
					print("<script>");
					print("$treename = new  dTree('$treename');");
					print("</script>");
				}

				$val = -1;

				if (!$tree) {
					$tree = $this->print_resource(null, $object, $ghtml->frm_o_o, $object, $depth, $alistflag, $func, false, $showurlflag);
				}

				if ($complex) {
					print("<script>");
					if (isset($gbl->__tmp_checkbox_value)) {
						print("var __treecheckboxcount = $gbl->__tmp_checkbox_value;");
					}
				}
				$total = -1;
				print_time('tree');
				$this->print_tree($treename, $tree, $total, $val, $complex);
				if ($complex) {
					print("document.write($treename);");
					print("</script>");
					print("</div>");
				}
				print_time('tree', "Tree", 2);

				?>


			</td>
		</tr>
	</table>

    <form name=__treeForm id=__treeForm method=<?="get" ?> action="/display.php">

    <input type=hidden name=frm_accountselect value="">
    <?php

    $this->print_current_input_vars(array('frm_action', 'frm_subaction'));

    if (cse($ghtml->frm_subaction, "confirm_confirm")) {
		$this->print_input("hidden", "frm_action", "update");
		$sub = $this->frm_subaction;
		$actionimg = "finish.gif";
	} else {
		$this->print_input("hidden", "frm_action", "updateform");
		$sub = $this->frm_subaction . "_confirm";
		$actionimg = "next.gif";
	}


    $this->print_input("hidden", "frm_subaction", "$sub");
    if (isset($gbl->__tmp_checkbox_value)) {
		print("<a href=javascript:treeStoreValue()> <img src=/img/general/button/$actionimg> </a>");
	}

    print("</form>");


}

	function print_tree($treename, $tree, &$total, $level, $complex = true)
	{


		$tlist = $tree->getList('tree');
		$open = $tree->open ? $tree->open : 'false';
		$open = 'false';
		if ($tree->imgstr) {
			$total++;
			if ($complex) {
				print("$treename.add($total, $level, '$tree->imgstr', '$tree->url', '', 'mainframe', '$tree->img', '$tree->img', $open, '$tree->help', '$tree->alt');\n");
			} else {
				for ($i = 0; $i < $level; $i++) {
					dprint("Hello\n");
					print("&nbsp;");
					print($imgstr . '<br>');
				}
			}
		}
		$level = $total;
		if ($tlist) {
			foreach ($tlist as $t) {
				$this->print_tree($treename, $t, $total, $level, $complex);
			}
		}
	}


	function print_resource($tree, $object, $cgi_o_o, $toplevelobject, $depth, $alistflag, $func, $childobjectflag = false, $showurlflag = true)
	{

		global $gbl, $sgbl, $login, $ghtml;


		$bgcolor = null;
		$path = get_image_path() . "/button/";
		$bpath = get_image_path() . "/button/";
		$class = $object->getClass();

		if (!$tree) {
			$tree = createTreeObject('name', null, null, null, null, null, null);
			$level = -1;
		} else {
			$level = 1;
		}

		$cnl = $object->$func();

		$alist = null;


		if ($level != -1) {
			if ($childobjectflag) {
				$url = $this->getFullUrl("a=show&o=$class", $cgi_o_o);
				$num = count($cgi_o_o);
				$cgi_o_o[$num]['class'] = $class;
			} else {
				$urlname = $object->nname;
				$url = $this->getFullUrl("a=show&l[class]=$class&l[nname]=$urlname", $cgi_o_o);
				$num = count($cgi_o_o);
				$cgi_o_o[$num]['class'] = $class;
				$cgi_o_o[$num]['nname'] = $object->nname;
			}
			$open = 'false';
			$alist = null;

		} else {
			$url = $this->getFullUrl("a=show", $cgi_o_o);
			$alist = $object->createShowAlist($alist);
			$open = 'true';
		}


		$list = $object->createShowTypeList();
		foreach ($list as $k => $v) {
			$type = $object->$k;
			$vtype = $k;
		}

		if ($childobjectflag) {
			$img = $this->get_image($path, $class, "show", ".gif");
		} else {
			$img = $this->get_image($path, $class, "{$vtype}_v_$type", ".gif");
		}

		if (isset($object->status) && $object->status) {
			if ($object->isOn('status')) {
				$hstr = "and is Enabled";
				$status = 'on';
			} else {
				$hstr = "and is Disabled";
				$status = 'off';
			}
			$stimg = $this->get_image($path, $class, "status_v_" . $status, ".gif");
			$imgstr = "<img height=8 width=8 src=$stimg>";
		} else {
			$imgstr = null;
			$hstr = null;
		}
		$homeimg = $this->get_image($path, $class, "show", ".gif");
		if ($childobjectflag) {
			$name = $ghtml->get_class_description($class);
			$name = $name[2];
		} else {
			$name = $object->getId();
		}
		$help = "$class <font color=blue> $name </font> is of Type $type $hstr";
		$alt = lx_strip_tags($help);
		$inputstr = null;

		if (!$showurlflag) {
			$url = null;
		}
		$imgstr = "$inputstr <img src=$img width=14 height=14>   $imgstr $name";
		if (isset($object->__v_message)) {
			$imgstr .= " " . $object->__v_message;
		}
		$pttr = createTreeObject($name, $img, $imgstr, $url, $open, $help, $alt);
		$tree->addToList('tree', $pttr);

		$childdepth = 1;
		$ppp = $object;
		//dprintr($depth);
		if ($object !== $toplevelobject) {
			while ($ppp = $ppp->getParentO()) {
				if ($ppp === $toplevelobject) {
					break;
				}
				$childdepth++;
			}
			if ($depth && ($childdepth >= $depth)) {
				return;
			}
		}


		if ($alist && $alistflag) {
			$open = 'false';
			$imgstr = "<img src=$homeimg width=14 height=14> <font color=#5958aa> <b> Functions </b></font> ";
			$ttr = createTreeObject($name, '', $imgstr, $url, $open, $help, $alt);
			$pttr->addToList('tree', $ttr);
			$this->print_resourcelist($ttr, $alist, null);
			$open = 'true';
		}


		foreach ((array) $cnl as $v) {
			$name = $object->getChildNameFromDes($v);

			if (cse($v, "_o")) {
				$c = null;
				if ($object->isRealChild($name)) {
					$c = $object->getObject($name);
				}
				if ($c) {
					$this->print_resource($pttr, $c, $cgi_o_o, $toplevelobject, $depth, $alistflag, $func, true, $showurlflag);
				}
				continue;
			}

			$img = $this->get_image($path, $name, "list", ".gif");
			$url = $this->getFullUrl("a=list&c=$name");
			$desc = $this->get_class_description($name);
			$printname = get_plural($desc[2]);
			$help = "Click to Show $printname";
			$alt = $help;
			$npttr = $pttr;
			if ($object === $toplevelobject) {

				$open = 'true';
				$gbl->__navigmenu[$level + 2] = array('show', $object);
				$gbl->__navig[$level + 2] = $this->get_post_from_get($url, $__tpath, $__tpost);
				$imgstr = "<img src=$img width=20 height=20>$printname";

				if (!$showurlflag) {
					$url = null;
				}
				$npttr = createTreeObject($name, $homeimg, $imgstr, $url, $open, $help, $alt);
				$pttr->addToList('tree', $npttr);


				if ($alistflag) {
					$open = 'false';
					$imgstr = 'Functions';
					$nttr = createTreeObject($name, $homeimg, $imgstr, $url, $open, $help, $alt);
					$npttr->addToList('tree', $nttr);
					$lalist = exec_class_method($name, 'createListAlist', $object, $name);
					$this->print_resourcelist($nttr, $lalist, null);
				}


			}
			$open = 'true';

			$filtername = $object->getFilterVariableForThis($name);

			$pagesize = (int) $login->issetHpFilter($filtername, 'pagesize') ? $login->gethpfilter($filtername, 'pagesize') : exec_class_method($class, "perPage");

			if (isset($sgbl->__var_main_resource) && $sgbl->__var_main_resource) {
				$cl = $object->getList($name);
				$count = count($cl);

				$halfflag = false;

			} else {
				$halfflag = true;
				$cl = $object->getVirtualList($name, $count);
			}
			if ($object->isVirtual($name)) {
				continue;
			}
			if ($cl) {
				//Setting $prev to -ll; this is done to initialize prev.
				if ($object === $toplevelobject && $login->getSpecialObject('sp_specialplay')->isOn('lpanel_group_resource') && $alistflag) {
					$prev = "-ll";
					foreach ($cl as $c) {
						if ($c->nname[0] != $prev[0]) {
							$imgstr = "<b>{$c->nname[0]} ....</b> ";
							$ttr = createTreeObject($name, $homeimg, $imgstr, $url, $open, $help, $alt);
							$npttr->addToList('tree', $ttr);
						}
						$prev = $c->nname;
						$this->print_resource($ttr, $c, $cgi_o_o, $toplevelobject, $depth, $alistflag, $func, false, $showurlflag);
					}

				} else {
					foreach ($cl as $c) {
						$this->print_resource($npttr, $c, $cgi_o_o, $toplevelobject, $depth, $alistflag, $func, false, $showurlflag);
					}
				}

				if ($halfflag && $count > $pagesize) {
					$url = $ghtml->getFullUrl("a=list&c=$name", $cgi_o_o);
					$ttr = createTreeObject($name, $homeimg, "More (Showing $pagesize of $count)", $url, $open, $help, $alt);
					$npttr->addToList('tree', $ttr);
				}
			}
		}

		// At the top client Make all the children virtual.. This assures that after viewing resources, the cache doesn't hogg the system.
		return $tree;

	}


	function getMenuDescrString($img, $descr, $endimg = null)
	{
		$endstr = null;
		$imgstr = null;
		if ($endimg) {
			$endstr = "<td > <img src=$endimg> </td> <td width=4> &nbsp; </td> ";
		}
		if ($img) {
			$imgstr = "<img width=14 height=14 src=$img> ";
		}

		// hack hack using the hilite class ( the lighter one for images.. and the image class is used for hilite)
		$string = "<table width=100% cellpadding=0 cellspacing=0 > <tr> <td valign=middle align=center style='padding:0 0 0 0' height=25 nowrap width=30 class=menuhilite> $imgstr </td> <td style='size:7pt' width=100%>&nbsp;&nbsp;<font style='size:7pt'>$descr</font> </td> $endstr </tr></table> ";
		return $string;
	}

	function print_resourcelist($tree, $alist, $base)
	{
		global $gbl, $sgbl, $login, $ghtml;

		$image = null;
		$open = 'false';
		$help = null;
		$alt = null;


		foreach ((array) $alist as $k => $a) {
			if (is_array($a)) {
				if ($k === 'home') {
					continue;
				}

				if (csb($k, "__title")) {
					$open = 'false';
					$a = strtil($a, "(");
					$a = strtil($a, "[");
					$ttr = createTreeObject($name, $image, $a, '', $open, $help, $alt);
					$tree->addToList($ttr);
					continue;
				}

				$desc = get_plural($k);
				$image = "/img/image/" . $login->getSpecialObject('sp_specialplay')->icon_name . "/button/browse.gif";
				$endimg = "/img/right_point.gif";
				$desc = "$desc";
				$open = 'false';
				$help = $desc;
				$alt = lx_strip_tags($help);


				$ttr = createTreeObject('name', $image, $desc, null, $open, $help, $alt);
				$tree->addToList('tree', $ttr);
				foreach ($a as $nk => $nv) {
					$nv = $this->getFullUrl($nv, $base);
					$this->print_ressingle($ttr, $nv);
				}
			}
		}

		foreach ((array) $alist as $k => $a) {
			if ($k === 'home') {
				continue;
			}

			if (csb($k, "__title")) {
				$open = 'false';
				$a = strtil($a, "(");
				$a = strtil($a, "[");
				$a = strtil($a, ":");
				$a = strtil($a, ":");
				$ttr = createTreeObject('name', $image, $a, null, $open, $help, $alt);
				$tree->addToList('tree', $ttr);
				continue;
			}

			if (is_array($a)) {
			} else {
				if (!csb($k, "__v_")) {
					$a = $this->getFullUrl($a, $base);
					if (isset($ttr)) {
						$this->print_ressingle($ttr, $a);
					} else {
						$this->print_ressingle($tree, $a);
					}
				}
			}
		}

	}


	function print_ressingle($tree, $url, $psuedourl = NULL, $target = NULL, $nameflag = false)
	{


		$this->resolve_int_ext($url, $psuedourl, $target);

		$img_path = get_image_path();
		$buttonpath = $img_path . "/button";
		$iconpath = $img_path . "/button";


		$descr = $this->getActionDetails($url, $psuedourl, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);


		$desc = $descr[2];
		if ($nameflag) {
			$desc = $descr[2] . "  ($__t_identity)";
		}

		$help = $descr[2];

		$alt = lx_strip_tags($help);
		$help = $this->get_action_or_display_help($help, "action");

		$open = 'false';
		$name = $file . "_" . $name;
		/// Hack hack... Just not setting frame for navigation menus. THis is not the way to do it. a flag should be passed... is the correct way...
		$imgstr = "<img src=$image width=14 height=14>$descr[2]";
		$ttr = createTreeObject($name, $image, $imgstr, $url, $open, $help, $alt);
		$tree->addToList('tree', $ttr);

	}


	function print_menulist($name, $alist, $base, $type)
	{
		global $gbl, $sgbl, $login, $ghtml;

		foreach ((array) $alist as $k => $a) {
			if (is_array($a)) {
				continue;
				if ($k === 'home') {
					continue;
				}
				if ($this->is_special_url($a)) {
					continue;
				}


				if (csb($k, "__title")) {
					continue;
				}
				$desc = get_plural($k);
				$menuimg = "/img/image/" . $login->getSpecialObject('sp_specialplay')->icon_name . "/button/browse.gif";
				$endimg = "/img/right_point.gif";
				$desc = "<font style=font-weight:bold>$desc</font>";
				$mnu = $this->getMenuDescrString($menuimg, $desc, $endimg);

				print("window.$name$k = new Menu(\"$mnu\", 100);\n");
				foreach ($a as $nk => $nv) {
					$nv = $this->getFullUrl($nv, $base);
					$this->print_pmenu("$name$k", $nv);
				}
			}
		}

		print("window.$name = new Menu('$name',130);\n");
		if ($type === 'slist') {
			$aa = $this->getFullUrl('a=show', $base);
			$this->print_pmenu($name, $aa);
		}
		foreach ((array) $alist as $k => $a) {
			if (!strcmp($k, 'home')) {
				continue;
			}

			if ($this->is_special_variable($a)) {
				continue;
			}

			if (csb($k, "__title")) {
				continue;
			}
			if (is_array($a)) {
				// Dont print property etc...
				continue;
				$aa = $this->getFullUrl($a[0], $base);
				print("$name.addMenuItem($name$k, frame1+\"$aa\", 'Properties', 'mainframe');\n");
			} else {
				// Hack hack...  NOt showing addforms in the top Menu... Also not showing in the tree view..
				if (csa(strtolower($a), "addform") || (csa(strtolower($a), 'update') && !csa(strtolower($a), 'updateform'))) {
					continue;
				}
				if (!csb($k, "__v_")) {
					$a = $this->getFullUrl($a, $base);
					$this->print_pmenu($name, $a);
				}
			}
		}
	}

	function print_pmenu($menu, $url, $psuedourl = NULL, $target = NULL, $nameflag = false)
	{

		global $gbl, $sgbl, $login;

		$this->resolve_int_ext($url, $psuedourl, $target);

		$img_path = get_image_path();
		$buttonpath = $img_path . "/button";
		$iconpath = $img_path . "/button";

		if (csb($url, "__blank|")) {
			$url = substr($url, 8);
			$image = $buttonpath . "/delete.gif";
			$string = $this->getMenuDescrString($image, $url);
			print("$menu.addMenuItem(\"$string\", \"\", \"0\",\"There is No History at this Point.\", \"0\");\n");
			return;
		}


		$descr = $this->getActionDetails($url, $psuedourl, $buttonpath, $path, $post, $file, $name, $image, $__t_identity);


		$desc = $descr[2];
		if ($nameflag) {
			$desc = $descr[2] . "  ($__t_identity)";
		}

		$help = $descr[3];

		$help = $this->get_action_or_display_help($help, "action");

		$name = $file . "_" . $name;
		/// Hack hack... Just not setting frame for navigation menus. THis is not the way to do it. a flag should be passed... is the correct way...
		$frame = null;
		$string = $this->getMenuDescrString($image, $desc);
		if (csb($menu, 'navig') || csb($menu, 'hist')) {
			print("$menu.addMenuItem(\"$string\", \"window.location='$url';\", \"0\",\"$help\", \"0\");\n");
		} else {
			$frame = "frame1+";
			print("$menu.addMenuItem(\"$string\", $frame\"'$url';\", \"0\",\"$help\", \"0\");\n");
		}
	}


	function print_real_beginning()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$lightskincolor = $login->getSkinColor();
		$lightskincolor = "818fb0";
		$func = "onLoad='lxLoadBody();'";
		$bodycolor = "ffffff";
		if ($sgbl->isBlackBackground()) {
			$bodycolor = "000";
		}
		if ($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) {
			$bodycolor = $lightskincolor;
		}

		print("<body leftmargin=0 rightmargin=0 $func align=center topmargin=0 bottommargin=0  bgcolor=#$bodycolor >");

		if ($login->getSpecialObject('sp_specialplay')->isOn('simple_skin')) {
			print("<div id=mmm leftmargin=0 rightmargin=0 $func height=100% align=center style='background:#$lightskincolor' >");
			print("<table cellpadding=0 cellspacing=0 height=10> <tr> <td > </td> </tr> </table> ");
			print("<div id=mainbodyd style='padding:4 4 4 4;background:#fff;width:861;border:1px solid #000'>");
			print("<div id=mainbodynext style='background:#fff;border:1px solid #000'>");
		}
		return;
		?>
<body topmargin=0 leftmargin=0 width=1000>
<div id=mainbody>
<?php

	}

	function print_splash_js_function()
	{


		?>
	<script>

		function coverScreen(flag)
		{

			var coverob = document.getElementById('coverscreen');
			if (!coverob) {
				return;
			}

			var x,y;
			if (self.innerHeight) {
				x = self.innerWidth;
				y = self.innerHeight;
			} else if (document.documentElement && document.documentElement.clientHeight) {
				x = document.documentElement.clientWidth;
				y = document.documentElement.clientHeight;
			} else if (document.body) {
				x = document.body.clientWidth;
				y = document.body.clientHeight;
			}


			x = x - 20;
			coverob.style.zIndex = 2;
			coverob.style.position = 'absolute';
			coverob.style.left = 0;
			coverob.style.top = 0;
			coverob.style.width = x;
			coverob.style.height = y;

			if (!flag) {
				coverob.style.display = 'none';
				coverob.style.visibility = 'hidden';
			} else {
				coverob.style.display = 'block';
				coverob.style.visibility = 'visible';
			}

		}

		function splashScreen(flag)
		{

			var splashob = document.getElementById('splashscreen');

			if (!splashob) {
				return;
			}

			var x,y;
			if (self.innerHeight) {
				x = self.innerWidth;
				y = self.innerHeight;
			} else if (document.documentElement && document.documentElement.clientHeight) {
				x = document.documentElement.clientWidth;
				y = document.documentElement.clientHeight;
			} else if (document.body) {
				x = document.body.clientWidth;
				y = document.body.clientHeight;
			}


			var top = 0;
			var left = x - 215;
			if (left <= 0) {
				left = 5;
			}


			if (flag) {
				splashob.style.visibility = 'visible';
				splashob.style.display = 'block';
			} else {
				splashob.style.visibility = 'hidden';
				splashob.style.display = 'none';
			}

			splashob.style.zIndex = 5;
			splashob.style.left = left + "px";
			splashob.style.top = top + "px";
			splashob.style.position = 'absolute';

		}

	</script>

		<?php

	}

	function print_splash()
	{
		?>
	<script>if (top.topframe && typeof top.topframe.changeLogo == 'function') {
		top.topframe.changeLogo(1);
	}</script>
		<?php

	}


	function print_start()
	{
		global $gbl, $sgbl, $login;
		$img_path = $login->getSkinDir();
		$tbg = $img_path . "/lp_bg.gif";
		$imgbordermain = $login->getSkinDir() . "/top_line_medium.gif";

		$this->print_include_jscript();
		?>

     <table id=tblmain cellpadding=0 cellspacing=0 border=0 width=100% height=100%>
     <tr>
    </td> <td width=100% align=center valign=top>
 <?php

	}


	function print_middle_start($help = NULL)
	{
		global $gbl, $sgbl, $login, $ghtml;
		// TODO: Remove Empty Function

	}


	function print_alternate_main_header()
	{
		global $gbl, $sgbl, $login, $ghtml;
		if ($sgbl->dbg > 0) {
			?>


			<table bgcolor=cccccc color=ffffff width=100% cellpadding=0 cellspacing=0 height=1>
				<tr>
					<td><a href=javascript:top.mainframe.window.location.reload()> zRefresh </a></td>
					<td width=10> &nbsp; </td>
					<td><a href="/display.php?frm_action=show"> Home </a></td>
					<td><a href=/display.php?frm_action=list&frm_o_cname=domain> Domain </a></td>
					<td><a href=/display.php?frm_action=show&frm_o_o[0][class]=pserver&frm_o_o[0][nname]=localhost>
						System </a></td>
					<td><a href=/display.php?frm_action=list&frm_o_cname=client> Client </a></td>
					</td>
					<td><a href=/display.php?frm_action=list&frm_o_cname=pserver> Server </a></td>
					<td><a href=/display.php?frm_action=list&frm_o_cname=ticket> Tickets </a></td>
					<td><a href=/display.php?frm_action=list&frm_o_cname=ssession> Session</a></td>
					<td><a href=/htmllib/phplib/logout.php> Logout </a></td>
					<td width=5%></td>
				</tr>
			</table>

			<?php

		}
	}


	function fix_post_pre_stuff($key)
	{
		$val = $this->__http_vars[$key];
		$realname = substr($key, strlen('frm_'));
		$prevar = 'frm_pre_' . $realname;
		if ($this->frmiset($prevar)) {
			$val = $this->gfrm($prevar) . $val;
		}
		$prevar = 'frm_post_' . $realname;
		if ($this->frmiset($prevar)) {
			$val .= $this->gfrm($prevar);
		}
		return $val;

	}

	function do_modify_obj($cobj)
	{

		$class = strtolower(get_class($cobj));
	}


	function modify_object($cobj)
	{

		$this->do_modify_obj($cobj);
		$cobj->dbaction = "modify";

	}


	function print_about()
	{
		global $gbl, $sgbl, $login, $ghtml;
		$img_path = $login->getSkinDir();
		$tbg = $img_path . "/lp_bg.gif";

		$imgbordermain = $login->getSkinDir() . "/top_line.gif";



		?>

		<table cellpadding=0 cellspacing=0>
			<tr>
				<td><img src=/img/aboutus.jpg></td>
			</tr>
		</table>
		<?php

	}

	function print_end()
	{
		global $gbl, $sgbl, $login;
		$img_path = $login->getSkinDir();
		$tbg = $img_path . "/lp_bg.gif";

		$imgbordermain = $login->getSkinDir() . "/top_line.gif";

		?>
</td> </tr> </table>
</td> </tr> </table>
<?php
	return;
	}

	function print_sortby($parent, $class, $unique_name, $sortby, $descr)
	{

		global $gbl, $sgbl, $login, $ghtml;

		$filtername = $parent->getFilterVariableForThis($class);

		$desc = $descr[2];
		$help = $descr['help'];
		$alt = lx_strip_tags($help);
		$url = $_SERVER['PHP_SELF'];
		if (!$desc) {
			$desc = ucfirst($sortby);
		}

		if (char_search_a($descr[0], "b") || char_search_a($descr[0], "S")) {
			// hack...
			if (!$alt) {
				$d = $alt;
			} else {
				$d = $desc;
			}
			print("<font class=tableheadtext onmouseover=\"changeContent('help','<b> Message </b>: <br> <br> $help')\" onmouseout=\"changeContent('help','helparea')\"> $d </font>");
			return;
		}

		$fil = $login->getHPFilter();
		$sortdir = null;
		$nsortby = null;
		if (isset($fil[$filtername]['sortby'])) {
			$nsortby = $fil[$filtername]['sortby'];
		}
		if (isset($fil[$filtername]['sortdir'])) {
			$sortdir = $fil[$filtername]['sortdir'];
		}
		if ($nsortby === $sortby) {
			$sortdir = ($sortdir === "desc") ? "asc" : "desc";
		}
		$formname = 'lpform_' . $unique_name . $sortby;
		?>

	<form name=<?=$formname; ?> method=<?=$sgbl->method ?> action=<?=$url; ?>>
		<?php $this->print_current_input_vars(array('frm_hpfilter')); ?>
		<input name=frm_hpfilter[<?=$filtername ?>][sortby] type=hidden value="<?=$sortby; ?>">
		<input name=frm_hpfilter[<?=$filtername ?>][sortdir] type=hidden value="<?=$sortdir; ?>">

	</form>
	<span title='<?=$alt ?>'><a class=tableheadtext
								href="javascript:document.<?=$formname; ?>.submit()"><?=$desc; ?> </a>  </span>
		<?php

	}


	function print_search($parent, $class)
	{
		global $gbl, $sgbl, $login;

		$url = $_SERVER['PHP_SELF'];
		$gen_image_path = get_general_image_path();
		$btnpath = $gen_image_path . "/icon/";

		$filtername = $parent->getFilterVariableForThis($class);
		$blackstyle = null;
		if ($sgbl->isBlackBackground()) {
			$blackstyle = "style='background:black;color:gray;border:1px solid gray;'";
		}

		$value = null;
		if ($login->issetHpFilter($filtername, 'searchstring')) {
			$value = $login->getHPFilter($filtername, 'searchstring');
		}
		$showallimg = "$btnpath/showall_b.gif";
		$searchimg = "$btnpath/search_b.gif";
		if ($sgbl->isBlackBackground()) {
			$showallimg = null;
			$searchimg = null;
		}
		?>
	<table width=90% border=0 cellpadding=0>
		<tr>
			<td>
				<table width=100% cellpadding=0 cellspacing=0 border=0>
					<tr>
						<td width=60%>
						</td>
						<td height=22 width=40% align=right>
							<table cellpadding=0 cellspacing=0 border=0 width=200>
								<tr>
									<td width=10 height=22></td>
									<td height=22>
										<form name=lpform_search method=<?=$sgbl->method ?>  action=<?=$url ?>
											  onsubmit="return checksearch(this,1);">

											<?php $this->print_current_input_var_unset_filter($filtername, array('sortby', 'sortdir', 'pagenum')) ?>
											<?php $this->print_current_input_vars(array("frm_hpfilter")) ?>

											<input <?=$blackstyle?> type="text"
																	name='frm_hpfilter[<?=$filtername ?>][searchstring]'
																	value="<?=$value ?>" class=searchbox size="18">
									</td>
									<td width=10 height=22></td>
									</form>
									<td height=22 width=20><a href='javascript:document.lpform_search.submit()'><img
											border=0 alt="Search" title="Search" name=search
											src=<?=$searchimg?> height=15 width=15
											onMouseOver="changeContent('help','search');"
											onMouseOut="changeContent('help','helparea');"></a></form></td>
									<td width=10 height=22></td>
									<td height=22 width=70>
										<form name=lpform_showall method=<?=$sgbl->method ?>  action=<?=$url ?>>

											<?php $this->print_current_input_vars(array("frm_hpfilter")) ?>
											<input type=hidden name=frm_clear_filter value=true>

											<table cellpadding=0 cellspacing=0 border=0 width=100% height=22>
												<tr>
													<td height=22 width=31% align=center nowrap><a
															href="javascript:document.lpform_showall.submit();"><img
															alt="Show All" title="Show all" name=showall
															src="<?=$showallimg?>"
															onMouseOver="changeContent('help','showall');"
															onMouseOut="changeContent('help','helparea');"></a></td>
													<td width=69% height=22 nowrap><a
															href="javascript:document.lpform_showall.submit();"
															onMouseOver="changeContent('help','showall');"
															onMouseOut="changeContent('help','helparea');"><font
															class=small>Show All</font></a></td>
												</tr>
											</table>
									</td>
									</form></tr>
							</table>
						</td>
					</tr>
				</table>

			</td>
		</tr>
	</table>

		<?php

	}


	function getClass()
	{
		return 'Html';

	}

	function oldlpanel_help()
	{
		global $gbl, $sgbl, $login;
		$img_path = $login->getSkinDir();
		$tbg = $img_path . "/lp_bg.gif";

		$helpimg = $img_path . "/";

		?>
<table cellpadding="0" width=100% cellspacing="0" border="0" align=center>

        <tr>
			<td align=center><br>
				<table cellpadding=0 cellspacing=0 border=0 align=center>
					<tr align=center>
						<td><img id=helppic name=namepic src="<?=$helpimg; ?>/help_head.gif" style="cursor:pointer"
								 onclick="javascript:window.open('<?=$this->get_help_url() ?>')"></td>
					</tr>
				</table>
			</td>
			</td></tr>

		<tr align=center>
			<td align=center>
				<table cellpadding=0 cellspacing=0 border=0 align=center>
					<tr>
						<td><img src="<?=$helpimg; ?>/help_edge.gif"></td>
						<td background="<?=$helpimg; ?>/help_bg.gif" width=170>
							<table cellpadding=0 cellspacing=0 border=0>
								<tr>
									<td width=10></td>
									<td>
										<div id=help class=helparea>
											<script> document.write(help_data['helparea']) </script>
										</div>
									</td>
								</tr>
							</table>
						</td>
						<td><img src="<?=$helpimg; ?>/help_edge.gif"></td>
					</tr>
				</table>
			</td>
		</tr>



		<?php if (if_demo()) { ?>
		<tr>
			<td align=center>
				<table>
					<tr align=center>
						<td align=center nowrap><a href=/live target=_blank class=tableheadtext>Click Here for Live
							Support.</a></td>
					</tr>
				</table>
			</td>
		</tr>
        </table>
        <?php 
	}


		return;

	}


}



