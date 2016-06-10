<?php

include_once "theme/htmllib.php";

class Html extends Htmllib
{
	function __construct()
	{
		parent::__construct();

	}

	function login_lpanel($object)
	{
		$this->do_login_lpanel("", $object);
	}

	function ifRemote()
	{
		if ($gbl->__var_remote == "yes") {
			return true;
		}

		return false;
	}


	function mymenus($header)
	{
		global $gbl, $login, $ghtml;

?>
	<script>
		// menu objects
		function Menu(label, msize) {
			this.type = "Menu";
			this.fontSize = 12;
			this.fontWeight = "normal";
			this.fontFamily = "Arial, Verdana";
			this.fontColor = "#003366";
			this.fontColorHilite = "#000000";
			this.bgColor = "#555555";
			this.size = msize;
			this.menuBorder = 1;
			this.menuItemBorder = 1;
			this.menuItemBgColor = "#aaaaaa";
			this.menuLiteBgColor = "#ffffff";
			this.menuBorderBgColor = "#777777";
			this.menuHiliteBgColor = "#e6eaed";
			this.menuContainerBgColor = "#dbeefd";

			this.items = new Array();
			this.actions = new Array();
			this.colors = new Array();
			this.mouseovers = new Array();
			this.mouseouts = new Array();
			this.childMenus = new Array();

			this.addMenuItem = addMenuItem;
			this.addMenuSeparator = addMenuSeparator;
			this.writeMenus = writeMenus;
			this.showMenu = showMenu;
			this.onMenuItemOver = onMenuItemOver;
			this.onMenuItemOut = onMenuItemOut;
			this.onMenuItemDown = onMenuItemDown;
			this.onMenuItemAction = onMenuItemAction;
			this.hideMenu = hideMenu;
			this.hideChildMenu = hideChildMenu;
			this.mouseTracker = mouseTracker;
			this.setMouseTracker = setMouseTracker;

			if (!window.menus) window.menus = new Array();
			this.label = label || "menuLabel" + window.menus.length;
			window.menus[this.label] = this;
			window.menus[window.menus.length] = this;
			if (!window.activeMenus) window.activeMenus = new Array();
			if (!window.menuContainers) window.menuContainers = new Array();
			if (!window.mDrag) {
				window.mDrag = new Object();
				mDrag.startMenuDrag = startMenuDrag;
				mDrag.doMenuDrag = doMenuDrag;
				this.setMouseTracker();
			}
			if (window.MenuAPI) MenuAPI(this);
		}

		function loadMenus() {
			var frame1 = "top.mainframe.window.location=";

<?php
		$alist = $login->createShowAlist($alist);
		$this->print_menulist("home", $alist, null, 'slist');
		
		if ($login->isAdmin()) {
			$pserver = $login->getFromList("pserver", "localhost");
			$alist = $pserver->createShowAlist();
		
			$frm_o_o[0]['class'] = 'pserver';
			$frm_o_o[0]['nname'] = 'localhost';
		
			$this->print_menulist('system', $alist, $frm_o_o, 'slist');
		}
		
?>
			window.help = new Menu("help", 100);

			help.addMenuItem("Help", "window.open(gl_helpUrl, 'Help')", "0", "helparea", "0");
<?php
		if ($login->isAdmin()) {
		/*
?>
			help.addMenuItem("Live","window.open('/live/', 'Live', 'status=no')","0","Live Help","0");
			help.addMenuItem("Live Transcript","window.open('/live/transcript.php')","0","Live Transcript","0");
			help.addMenuItem("Help Desk","window.open('http://www.lxlabs.com/lxa/hdesk/')","0","helparea","0");
<?php
		*/
		}
?>
			help.addMenuItem("Forum", "window.open('http://www.lxcenter.org/forum')", "0", "helparea", "0");

		}
</script>
<?php
	}

	function printSelectObjectTable($name_list, $parent, $class, $blist = array(), $display = null)
	{
		global $gbl, $sgbl, $login, $ghtml;

		print_time("$class.objecttable");

		$skin_name = $login->getSpecialObject('sp_specialplay')->skin_name;
		$skin_color = $login->getSkinColor();

		if ($this->frm_accountselect !== null) {
			$sellist = explode(',', $this->frm_accountselect);
		} else {
			$sellist = null;
		}

		$classdesc = $this->get_class_description($class, $display);
		$unique_name = trim($parent->nname) . trim($class) . trim($display) . trim($classdesc[2]);

		$unique_name = fix_nname_to_be_variable($unique_name);

		$filtername = $parent->getFilterVariableForThis($class);
		$fil = $this->frm_hpfilter;
		$sortdir = null;
		$sortby = null;
		
		if (isset($fil[$filtername]['sortby'])) {
			$sortby = $fil[$filtername]['sortby'];
		}
		
		if (isset($fil[$filtername]['sortdir'])) {
			$sortdir = $fil[$filtername]['sortdir'];
		}

		$pagesize = '99999';

		$iconpath = get_image_path() . "/button";

		$imagedir = $login->getSkinDir() . "/images";

		$nlcount = count($name_list) + 1;
		$imgheadleft = $imagedir . "/top_lt.gif";
		$imgheadleft2 = $imagedir . "/top_lt.gif";
		$imgheadright = $imagedir . "/top_rt.gif";
		$imgheadbg = $imagedir . "/top_bg.gif";
		$imgbtnbg = $imagedir . "/btn_bg.gif";
		$imgtablerowhead = $imagedir . "/tablerow_head.gif";
		$imgtablerowheadselect = $imagedir . "/top_line_medium.gif";
		$imgbtncrv = $imagedir . "/btn_crv.gif";
		$imgtopline = $imagedir . "/top_line.gif";

		$classdesc = $this->get_class_description($class);

		$unique_name = trim($parent->nname) . trim($class) . trim($classdesc[2]);

		$unique_name = fix_nname_to_be_variable($unique_name);

?>
		
		<br />

		<script>
			var ckcount<?= $unique_name; ?>;
		</script>
<?php
		$tsortby = $sortby;

		if (!$sortby) {
			$tsortby = exec_class_method($class, "defaultSort");
		}

		if (!$sortdir) {
			$sortdir = exec_class_method($class, "defaultSortDir");
		}

		$obj_list = $parent->getVirtualList($class, $total_num, $tsortby, $sortdir);

		if (!$sellist) {
		//	$total_num = $this->display_count($obj_list, $display);
		}
?>

		<div style="background-color: #fff; border-top: 1px solid #ddd">
			<br />
			<div style="width:910px; margin: 0 20px">
				<table cellspacing='2' cellpadding='2' width='100%' align='center'>
					<tr>
						<td colspan="<?= $nlcount; ?>">
							<table style="padding: 0; margin: 0; border: 0; border-collapse: collapse; width:100%">
								<tr>
									<td valign='bottom'></td>
									<td>
<?php
		if (isset($ghtml->__http_vars['frm_hpfilter'][$filtername]['pagenum'])) {
			$cgi_pagenum = $ghtml->__http_vars['frm_hpfilter'][$filtername]['pagenum'];
		} else {
			$cgi_pagenum = 1;
		}

		if (!$sellist) {
			$this->print_next_previous($parent, $class, "top", $cgi_pagenum, $total_num, $pagesize);
		}
?>
									</td>
									<td align='right' valign='bottom'>
										<table style="border: 0; padding: 0; margin: 0; border-collapse: collapse; height: 27px">
											<tr>
<?php
		if (!$sellist) {
			if ($skin_name !== 'simplicity') {
?>

												<td><img src="<?= $imgheadleft; ?>"></td>
												<td nowrap valign='middle' background="<?= $imgheadbg; ?>"><b><span color="#ffffff"><?= get_plural($classdesc[2]) ?>&nbsp;under&nbsp;<?= $parent->display("nname") ?></b>&nbsp;<?= $this->print_machine($parent) ?><b>&nbsp;(<?= $total_num ?>)&nbsp;</b></span></td>
												<td><img src="<?= $imgheadright; ?>"></td>
<?php
			} else {
?>

												<td></td>
												<td nowrap valign='middle' style="border: 0; padding: 4px; margin: 0; background-color: <?=$skin_color?>"><b>&nbsp;<span color="#ffffff"><?= get_plural($classdesc[2]) ?>&nbsp;under&nbsp;<?= $parent->display("nname") ?></b>&nbsp;<?= $this->print_machine($parent) ?><b>&nbsp;(<?= $total_num ?>)&nbsp;</b></span></td>
												<td></td>
<?php
			}
?>
											</tr>
										</table>
									</td>
								</tr>

								<tr>
									<td colspan='3'>
<?php
			if ($skin_name !== 'simplicity') {
?>

										<table cellpadding='0' cellspacing='0' border='0' width='100%' height='35' background="<?= $imgbtnbg; ?>">
											<tr>
												<td><img src="<?= $imgbtncrv; ?>"></td>
												<td width='80%' align='left'>
													<table width='100%' cellpadding='0' cellspacing='0' border='0'>
														<tr>
															<td valign='bottom'><?php $this->print_list_submit($class, $blist, $unique_name); ?></td>
														</tr>
													</table>
												</td>
												<td style="text-align: right; padding: 0 5px"><span color="#ffffff"><b><?php $this->print_search($parent, $class); ?></b></span></td>
											</tr>
										</table>

<?php
			} else {
?>

										<table style="border: 0; padding: 4px; margin: 0; background-color: <?=$skin_color?>">
											<tr>
												<td></td>
												<td width='80%' align='left'>
													<table width='100%' cellpadding='0' cellspacing='0' border='0'>
														<tr>
															<td valign='bottom'><?php $this->print_list_submit($class, $blist, $unique_name); ?></td>
														</tr>
													</table>
												</td>
												<td style="text-align: right; padding: 0 5px"><span color="#ffffff"><b><?php $this->print_search($parent, $class); ?></b></span></td>
											</tr>
										</table>

<?php
			}
?>

									</td>
								</tr>
								<tr>
									<td height='2' colspan='2'></td>
								</tr>
							</table>

<?php
		} else {
							$descr = $this->getActionDescr($_SERVER['PHP_SELF'], $this->__http_vars, $class, $var, $identity);
?>
							<table cellpadding='0' cellspacing='0' border='0' width='100%'>
								<tr>
									<td width='70%' valign='bottom'>
										<table cellpadding='0' cellspacing='0' border='0' width='100%'>
											<tr>
												<td width='100%' height='2' background="<?= $imgtopline; ?>"></td>
											</tr>
										</table>
									</td>
									<td align=right>
										<table cellpadding='0' cellspacing='0' border='0' width='100%'>
											<tr>
												<td><img src="<?= $imgheadleft; ?>"></td>
												<td nowrap width='100%' background="<?= $imgheadbg; ?>"><span color="#ffffff"><b>Confirm <?= $descr[1] ?>:</b><?= get_plural($classdesc[2]) ?> from <?= $parent->display("nname"); ?></span></td>
												<td><img src="<?= $imgheadright; ?>"></td>
											</tr>
										</table>
									</td>
								</tr>
							</table>

						</td>
					</tr>
					<tr>
						<td height='0' colspan='2'></td>
					</tr>
				</table>

<?php
		}
?>

		<tr>
<?php

		$imguparrow = get_general_image_path() . "/button/uparrow.gif";
		$imgdownarrow = get_general_image_path() . "/button/downarrow.gif";

		foreach ($name_list as $name => $width) {
			$desc = "__desc_{$name}";

			$descr[$name] = get_classvar_description($class, $desc);

			if (!$descr[$name]) {
?>

					Cannot access static variable <?= $class ?>::<?= $desc ?>

<?php
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
				$wrapstr = "";
			} else {
				$wrapstr = "nowrap";
			}

			if ($sortby && $sortby === $name) {
				$wrapstr .= " background='$imgtablerowheadselect'";
?>
					<td <?= $wrapstr ?> width='<?= $width ?>'><table cellpadding='0' cellspacing='0' border='0'> <tr> <td <?= $wrapstr ?> rowspan='2'>
<?php
			} else {
					$wrapstr .= " background='$imgtablerowhead'";
?>
					<td width='<?= $width ?>' <?= $wrapstr ?> class='col'>
<?php
			}

?>
				<b><?php $this->print_sortby($parent, $class, $unique_name, $name, $descr[$name]) ?></b>

<?php

				$imgarrow = ($sortdir === "desc") ? $imgdownarrow : $imguparrow;

			if ($sortby && $sortby === $name) {
?>
				</td>
				<td width='15'><img src="<?= $imgarrow ?>"></td>
				<td></td>
			</tr>
		</table>
<?php
			} else {
?>
				</td>

<?php
			}
		}

			$count = 0;
			$rowcount = 0;

			if ($sellist) {
				$checked = "checked disabled";
			} else {
				$checked = "";
			}

?>
			<td background='<?= $imgtablerowhead ?>' style="width: 10px; text-align: center">
				<form name="formselectall<?= $unique_name; ?>" value='hello'>
					<input type='checkbox' name="selectall<?= $unique_name; ?>" value='on' <?= $checked; ?> onclick="javascript:calljselectall<?= $unique_name; ?>()">
				</form>
			</td>
		</tr>
<?php

		print_time('loop');

		$n = 1;
		
		foreach ((array)$obj_list as $okey => $obj) {
			$checked = '';
			
			// Fix This.
			if ($sellist) {
				$checked = "checked disabled";

				if (!array_search_bool($obj->nname, $sellist)) {
					continue;
				}
			}

			$imgpointer = get_general_image_path() . "/button/pointer.gif";
			$imgblank = get_general_image_path() . "/button/blank.gif";
?>

			<script>
				loadImage('<?= $imgpointer?>');
				loadImage('<?= $imgblank?>');
			</script>

			<tr id='tr<?= $unique_name . $rowcount; ?>' class='tablerow<?= $count; ?>' onmouseover="swapImage('imgpoint<?= $rowcount; ?>','','<?= $imgpointer; ?>',1);" onmouseout="swapImgRestore();">
<?php
			$colcount = 1;

			foreach ($name_list as $name => $width) {
				$this->printObjectElement($parent, $class, $classdesc, $obj, $name, $width, $descr, $colcount . "_" . $rowcount);
				$colcount++;
			}

			$basename = basename($obj->nname);
			$selectshowbase = $this->frm_selectshowbase;
			$ret = strfrom($parent->nname, $selectshowbase);
			// issue #609
		//	$ret = str_replace("///", "/", $ret);
		//	$ret = str_replace("//", "/", $ret);
			// MR -- change to
		//	$ret = "/$ret/$basename";
			$ret = preg_replace('/(\/){1,3}(.*)/', '/$2', $ret);

?>

				<td width='10'>&nbsp;<a class='button' href="javascript:callSetSelectFolder('<?= $ret ?>')">Select</a>&nbsp;</td>
			</tr>
<?php
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

		print_time('loop', "loop$n");
?>
		<tr>
			<td></td>
			<td colspan='<?= $nlcount ?>'>
<?php
		if (!$rowcount) {
			if ($ghtml->frm_searchstring) {
?>
						<table width='95%'>
							<tr align='center'>
								<td width='100%'>&nbsp;<b>&nbsp;No Matches Found&nbsp;</b></td>
							</tr>
						</table>
<?php
			} else {
?>
						<table width='95%'>
							<tr align='center'>
								<td width='100%'>&nbsp;<b>No <?= get_plural($classdesc[2]) ?>&nbsp;under&nbsp;<?= $parent->nname ?></b>&nbsp;</td>
							</tr>
						</table>
<?php
			}
		}
?>
			</td>
		</tr>
		<tr>
			<td colspan="<?=$nlcount ?>">
				<table cellpadding='0' cellspacing='0' border=0 width='100%'>
					<tr height='1' style='background:url(<?=$imgtopline ?>)'>
						<td></td>
					</tr>
					<tr>
						<td>

							<script>
								ckcount<?=$unique_name?> = <?=$rowcount?>;

								function calljselectall<?=$unique_name?>() {
									jselectall(document.formselectall<?=$unique_name?>.selectall<?=$unique_name?>, ckcount<?=$unique_name?>, '<?=$unique_name?>');
								}
							</script>

							<table>
								<tr>
									<td>
										<a class='button' href="javascript:window.close()">&nbsp;Cancel&nbsp;</a>
									</td>
									<td width='30'>&nbsp;</td>
									<td></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</tr>
		</table>
	</div>
	<br />
</div>
<?php

		exit;
	}
}
