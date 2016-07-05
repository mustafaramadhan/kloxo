<?php

class all_easyinstaller extends Lxclass
{
	static $__desc  = array("P","",  "easyinstaller"); 
	static $__desc_nname  = array("","", "application");
	static $__desc_install_flag  = array("e","", "Installed");
	static $__desc_install_flag_v_on  = array("e","", "Installed");
	static $__desc_install_flag_v_dull  = array("e","", "Not Installed");
	static $__desc_appname  = array("","", "application");

	static $__acdesc_update_update  = array("","", "easyinstaller");
	static $__acdesc_show  = array("","", "easyinstaller");
	static $__acdesc_list  = array("","", "_");

	function get() {}
	function write() {}

	static function createListNlist($parent, $view)
	{
		$nlist['install_flag'] = '5%';
		$nlist['appname'] = '100%';
		return $nlist;
	}

	static function perPage()
	{
		return 50;
	}

	static function initThisListRule($parent, $class)
	{
		return null;
	}

	function isSelect()
	{
		return false;
	}

	function createShowPropertyList(&$alist)
	{
	//	$alist['property'][] = 'goback=1&a=list&c=all_easyinstaller';
		$alist['property'][] = 'a=show';
		$alist['property'][] = 'goback=1&a=list&c=easyinstaller';
		$alist['property'][] = 'goback=1&a=list&c=easyinstallersnapshot';

	}

	function display($var)
	{
		if ($var === 'install_flag') {
			return $this->checkIfInstalled();
		}

		return $this->$var;
	}

	function checkIfInstalled()
	{
		$sq = new Sqlite($this->__masterserver, 'installsoft');
		$res = $sq->getRowsWhere("appname = '$this->appname' AND parent_clname = '{$this->getParentO()->getClName()}'");

		if ($res) {
			return 'on';
		}
		return "dull";
	}

	static function getAllInformation($name)
	{
		$name = strtolower($name);
		$darr = lfile("__path_kloxo_httpd_root/easyinstallerdata/description/$name.info");

		$pvar = "NA";
		$plink = "NA";
		$pimg = "NA";
		$pdesc = "Not available";
		$pversion = "-";
		$pdetail = null;
		$preq = "NA" ; 
		$adminarea = null;

		foreach ($darr as $info) {
			if($info==null) { continue; }

			$info = trimSpaces($info);
			$sinfo = explode(" ", $info);

			switch ($sinfo[0]){
				case "variables":
				case "variable":
					$pvar = substr($info, 8);
					break;
				case "url":
					$plink = $sinfo[1];
					break;
				case "img":
					$pimg = $sinfo[1];
					break;
				case "adminarea":
					$adminarea = $sinfo[1];
					break;
				case "description":
					$pdesc = substr($info ,11);
					break;
				case "detail":
					$pdetail = substr($info, 6);
					break;
				case "version":
					$pversion = $sinfo[1];
					break;
				case "requirement" :
					$preq = substr($info, 11);
					break;
			}
		}

		return array('pdesc' => $pdesc, 'padminarea' => $adminarea, 
			'pversion' => $pversion, 'pimg' => $pimg, 'plink' => $plink, 
			'pvar' => $pvar, 'pdetail' => $pdetail, 'preq' => $preq);
	}

	static function showDescription($object, $name)
	{
		global $gbl, $sgbl, $login, $ghtml; 

		$col = $login->getSkinColor();

		$icondir = get_image_path();

		$list = $object->getParentO()->getList('all_easyinstaller');

		$list = get_namelist_from_objectlist($list);

		$searchstring = $ghtml->frm_searchstring;

		$web = $object->getParentO();
		$selflist = $object->getParentO()->getList('easyinstaller');
		$installed = "No";
		$installedpath = null;

		foreach((array) $selflist as $s) {
			if ($s->appname === $name) {
				$installed = "Yes";
				$installedpath[] = "<a target=_blank href=http://$web->nname/$s->installdir> $s->installdir </a> ";
			}
		}
	
		$installagain = null;

		if ($installedpath) {
			$installedpath = implode(", ", $installedpath);
			$installedpath = "($installedpath)";
			$installagain = " in another Location";
		}

		$array = self::getAllInformation($name);

		$lowername = strtolower($name);

		foreach($array as $k => $v) {
			$$k = $v;
		}

		if (!$pdetail) {
			$pdetail = $pdesc;
		}

		$paddurl = $ghtml->getFullUrl("goback=1&a=addform&c=easyinstaller&dta[var]=appname&dta[val]=$name");

		$color = "$col";
		$string = null;
		$count = 0;
		$numapp = 0;
		$total = count($list);

		foreach($list as $l) {
			$small = strtolower($l);

			if ($searchstring && !csa($small, $searchstring)) {
				continue;
			}

			$icon = "/theme/easyinstaller/icon_$small.gif";
			$count++;
			$bgcolorvar = null;

			if ($small === $lowername) {
				$pointericon = "<img width='5' height='5' src='$icondir/on.gif'>";
				$bgcolorvar = "bgcolor=$color";
			} 

			if (csa($l, "__title")) {
				$reall = strfrom($l, "__title_");
				$reall = str_replace("_", " . ", $reall);
				$reall = ucwords($reall);
				$urlstring = null;
				$spanvar =  "<span title=\"$reall\">";
				$l = "<b> $reall </b> ";
				$bgcolorvar = "bgcolor=#efefef";
				$iconstring = null;
				$textpropery = "font-weight:bold;";
			} else {
				$url = $ghtml->getFullUrl("goback=1&a=show&k[class]=all_easyinstaller&k[nname]=$l");
				$urlstring = "<a href=\"$url\">";
				$list = self::getAllInformation($l);
				$__descr = $list['pdesc'];
				$__descr = trim($__descr);
				$spanvar = "<span title=\"$__descr\">";
				$iconstring = "<img width=14 height=14 src=$icon>";
				$textpropery = null;

				if ($l !== $lowername) {
					$numapp++;
				}
			}

			$bdtop = "padding: 3 3 3 3;  border-left:0px solid black ; border-bottom:1px solid black; $textpropery" ;
			$pointericon = null;

			$ucfirstl = ucfirst($l);

			if ($count <= $total/2 + 1) {
				$stringvar = "stringleft";
				$stringleft[] = "<tr > <td $bgcolorvar valign=top align=left style='$bdtop'>$spanvar   $urlstring $pointericon $ucfirstl </a> $iconstring &nbsp; &nbsp; </span> </td> </tr> ";

				if ($l === $lowername) {
					$selfpostion = $count;
				}
			} else {
				$stringright[] = "<tr > <td $bgcolorvar valign=top align=left style='$bdtop'>$spanvar &nbsp; &nbsp; $urlstring $iconstring $ucfirstl $pointericon</a>   </span> </td> </tr> ";

				if ($l === $lowername) {
					$selfpostion = $count - $total/2;
				}
			}
		}

		$count = $total/2;

		if ($selfpostion < $count/3) {
			$scrollposition = $selfpostion * 11 - 40;
		} else if ($selfpostion > $count * 2/3) {
			$scrollposition = $selfpostion * 11 + 40;
		} else {
			$scrollposition = $selfpostion * 10;
		}

		dprint($scrollposition);
		$stringleft = implode("\n", $stringleft);
		$stringright = implode("\n", $stringright);

		$stringleft = "<table cellpadding=1 cellspacing=0 width=100% border=0>$stringleft </table>\n";
		$stringright = "<table cellpadding=1 cellspacing=0 width=100% border=0>$stringright </table>\n";
		$divheight = 700;
?> 
<table cellpadding=0 cellspacing=0 width=95% border=0 height=600 bordercolor=black><tr> 

<td valign=top width=130> 
<div dir=rtl id=mydivleft style='overflow:auto;height:<?php echo $divheight ?> '>
<?php echo $stringleft ?> 
</div>
</td> 

<td valign=top>
<table cellpadding=0 valign=top width=100% cellspacing=0 border=0>
<tr valign=top ><td height=10 style='border-top: 1px solid black;' colspan=10 bgcolor=<?php echo $color ?> ></td> </tr> 

<tr valign=top> 
<td colspan=3 height=<?php echo $divheight?> width=10 valign=top style='border-bottom: 0px solid black; ' bgcolor=<?php echo $color ?> >
</td> <td style='border-bottom: 0px solid black; border-top: 0px solid black'>
<table cellpadding=0 width=100% cellspacing=15> <tr> <td colspan=3 valign=top>
<?php	
	$pimg = "$lowername.gif";
	print "<img src=/theme/easyinstaller/$pimg>";
?> 
	</td>
	</tr> 
	<?php
		if ($lowername === 'easyinstaller') {
?> 
	<tr>
	<td >Search</td>
	<td rowspan=5 width=1 bgcolor=black></td> 
	<td > <form method=post action=/display.php >
	<?php $ghtml->print_current_input_vars(array('frm_searchstring')); ?> 
	<input name=frm_searchstring type=text value=<?php echo $searchstring ?> >
	<input type=submit name=search value=Search class=submitbutton>
	</form> 
	</td>
	</tr>
<?php
		}
?> 
	<tr>
	<td >Url</td>
<?php
		if ($lowername !== 'easyinstaller') {
?> 
	<td rowspan=6 width=1 bgcolor=black></td> 
<?php
		}
?> 
	<td ><?php print ("<a target=_blank href=\"$plink\">$plink</a>"); ?> </td>
	</tr>
	<tr> 
	<td >Version</td>
	<td ><?php echo $pversion?></td> 
	</tr> 
<?php
		if ($lowername !== 'easyinstaller') {
?> 
	<tr> 
	<td valign=top> Already Installed </td>
	<td width=400> <?php echo "$installed $installedpath"?>  </td>
	</tr> 
	<tr> <td > Install </td> 
	<td width=400> <a href='<?php echo $paddurl ?>'> Install This Application <?php echo $installagain ?>  </a>  </td>  </tr> 
<?php
		} else {
?> 
	<tr> <td > Total Applications </td> 
	<td width=400>  <?php echo $numapp ?>  </td>  </tr> 
<?php
		}

		if ($preq != 'NA') {
?>	
	  <tr> <td valign=top> Requirement </td> 
	  <td width=400 >  <?php echo $preq ?>  </td>  </tr> 
	  <tr> 
<?php
		}
?> 
	<td valign=top>Description </td> 
	<td width=400><?php echo $pdetail?></td> 
	</tr> 
	</table>
	</td> </tr>  <td bgcolor=<?php echo $color ?>  height=10 colspan=4> </td> </tr></table> 
	</td> <td width=10 bgcolor=<?php echo $color ?> > </td> 
<td valign=top width=130> 
<div dir=ltr id=mydivright style='overflow:auto;height:<?php echo $divheight ?> '>
<?php echo $stringright ?> 
</div>
</td> 
	</tr> <tr>  </table>

</td> </tr> </table>
<script>
var myobj = document.getElementById('mydivleft');
myobj.scrollTop = <?php echo $scrollposition ?> ;

var myobj = document.getElementById('mydivright');
myobj.scrollTop = <?php echo $scrollposition ?> ;
//alert(myobj.scrollTop);
</script>
<?php 

	}

	function isParentList() { return true; }

	static function print_div_botton($position)
	{
		return;
?> 
<script>
var myobj = document.getElementById('mydivbottom');
myobj.scrollTop = 100;
alert(myobj.scrollTop);
</script>
<?php 
	}

	function showRawPrint($subaction = null)
	{
		self::showDescription($this, $this->appname);
	}

	function createShowAlist(&$alist, $subaction = null)
	{
		if ($this->checkIfInstalled() === 'dull') { }

		return $alist;
	}

	static function createListAlist($parent, $class)
	{
		return easyinstaller::createListAlist($parent, $class);
	}

	static function initThisList($parent, $class)
	{
		global $gbl, $sgbl, $login, $ghtml; 

		$driverapp = $gbl->getSyncClass($parent->__masterserver, $parent->__readserver, 'all_easyinstaller');
		$res = exec_class_method("all_easyinstaller__$driverapp", "getListofApps");

		return $res;
	}
}

