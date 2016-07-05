<?php 

class all_easyinstaller__linux extends LxDriverclass
{
	static function getListofApps()
	{
		global $gbl, $sgbl, $login, $ghtml; 

		if ($sgbl->dbg < 1) {
			$list = lfile("__path_kloxo_httpd_root/easyinstallerdata/description/base_linux.data");
		} else {
			$list = lfile("__path_kloxo_httpd_root/easyinstallerdata/description/base_linux.data");
		//	$list = lfile("__path_kloxo_httpd_root/easyinstallerdata/description/base_linux.data.debug");
		}

		$res = null;

		$res[] = array('nname' => 'easyinstaller', 'appname' => 'easyinstaller', 'description' => "easyinstaller");

		foreach((array) $list as $l) {
			$l = trim($l);

			if (!$l) {
				continue;
			}

			if ($l[0] === '#') {
				continue;
			}

			$v = explode(" ", $l);
			$r = null;
			$r['nname'] = array_shift($v);
			$r['appname'] = $r['nname'];
			$r['description'] = implode(" ", $v);

			$res[] = $r;
		}

		return $res;
	}
}
