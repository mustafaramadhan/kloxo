<?php 

class pserver__Linux extends lxDriverClass
{
	function poweroff()
	{
		system("poweroff");
	}

	function reboot()
	{
		system("reboot");
	}

	static function mysqlPasswordReset($pass)
	{
		lxshell_return("lphp.exe", "../bin/common/misc/reset-mysql-root-password.phps", $pass);
		sleep(20);
		exec_with_all_closed("service mysqld restart");
		sleep(50);
		$a['mysql']['dbpassword'] = $pass;
		slave_save_db("dbadmin", $a);

	}

	function setTimeZone()
	{
		lxfile_cp("/usr/share/zoneinfo/{$this->main->timezone}", "/etc/localtime");
	}

	static function execCommand($iid, $command)
	{
		if (if_demo()) {
			throw new lxException ("not_allowed_in_demo");
		}
		
		global $global_shell_error, $global_shell_ret;
		$global_shell_error = null;
		$out = shell_exec("$command 2>&1");

		return array('output' => $out, 'error' => $global_shell_error);
	}

	function information()
	{
		$rmt = new Remote();
		$rmt->load_threshold = $this->main->load_threshold;
		lxfile_mkdir("../etc/data");
		lfile_put_serialize("../etc/data/loadmonitor", $rmt);
	}

	function dbactionUpdate($subaction)
	{
		switch ($subaction) {
			case "mysqlpasswordreset":
				$this->mysqlPasswordReset();
				break;

			case "reboot":
				$this->reboot();
				break;

			case "poweroff":
				$this->poweroff();
				break;
			case "information":
				$this->information();
				break;

			case "password":
				$this->main->syncPasswordCommon();
				break;

			case "timezone":
				$this->setTimeZone();
				break;

			case "importvps":
				return $this->importVps();
				break;

			case "importhypervmvps":
				return $this->importHypervmVPS();
				break;

			case "savevpsdata":
				$this->savevpsdata();
				break;

			case "graph_vpstraffic":
				return rrd_graph_server("traffic", $this->main->__var_graph_list, $this->main->rrdtime);
				break;

			case "graph_vpscpuusage":
				return rrd_graph_server("cpu", $this->main->__var_graph_list, $this->main->rrdtime);
				break;

			case "graph_vpsmemoryusage":
				return rrd_graph_server("memory", $this->main->__var_graph_list, $this->main->rrdtime);
				break;
		}
	}

	function saveVpsData()
	{
		lxfile_mkdir("/home/hypervm/data/");
		lfile_put_serialize("/home/hypervm/data/complete", $this->main->__var_vpsdata);
	}

	function importHypervmVPS()
	{
		$vpsdata = lfile_get_unserialize("/home/hypervm/data/complete");
		
		foreach ($vpsdata as $k => $v) {
			$v->syncserver = $this->main->nname;
		}
		
		return $vpsdata;
	}


	function createShowAlist(&$alist, $subaction = null)
	{

	}

	static function parse_data($data)
	{
		foreach ($data as $d) {
			$d = trimSpaces($d);
			$d = str_replace(":", "", $d);
			$rval = explode(" ", $d);
			$res[strtolower($rval[0])] = $rval[1];
		}
		
		return $res;
	}

	static function getTotalMemory()
	{
		$path = "/proc/meminfo";
		$data = lfile($path);
		$res = self::parse_data($data);
		
		return $res['memtotal'] / (1024);
	}


	static function pserverInfo()
	{
		global $gbl, $sgbl, $login, $ghtml;
		
		$osdet = findOperatingSystem();

		$path = "/proc/meminfo";

		$data = lfile($path);

		$res = self::parse_data($data);

		$unit = 1024;

		$ret['priv_s_memory'] = $res['memtotal'] / $unit;
		$ret['used_s_memory'] = ($res['memtotal'] - $res['memfree']) / $unit;
		$ret['priv_s_swap'] = $res['swaptotal'] / $unit;
		$ret['used_s_swap'] = ($res['swaptotal'] - $res['swapfree']) / $unit;

		$ret['used_s_membuffers'] = $res['buffers'] / $unit;

		$ret['used_s_memcached'] = $res['cached'] / $unit;

		// This is a hack to show the actual non-kloxo memory on openvz.
		if ($sgbl->isKloxo()) {
			if (lxfile_exists("/proc/user_beancounters")) {
				$ret['used_s_memory'] -= 20;
			}
		}

		foreach ($ret as &$vvv) {
			$vvv = round($vvv);
		}

		$path = "/proc/cpuinfo";

		$data = lfile($path);

		$processornum = 0;

		foreach ($data as $v) {
			if (!trim($v)) {
				continue;
			}
			
			$d = explode(':', $v);
			$d[0] = trim($d[0]);
			$d[1] = trim($d[1]);
			
			if ($d[0] === 'processor') {
				$processornum = $d[1];
				continue;
			}

			if ($d[0] === 'model name') {
				$cpu[$processornum]['used_s_cpumodel'] = $d[1];
			}
			
			if ($d[0] === 'cpu MHz') {
				$cpu[$processornum]['used_s_cpuspeed'] = round($d[1] / 100) / 10 . "GHz";
			}
			
			if ($d[0] === 'cache size') {
				$cpu[$processornum]['used_s_cpucache'] = $d[1];
			}
		}

		$ret['disk'] = diskusage__linux::getDiskUsage();
		$ret['lvm'] = vg_complete();
		$ret['cpu'] = $cpu;
		$ret['osdet'] = $osdet;
		
		return $ret;
	}


	function importVps()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$objlist = null;
		
		if ($this->main->__var_vps_driver === 'openvz') {
			if (!lxfile_exists("/etc/vz/conf")) {
				throw new lxException ("no_vz_conf_directory", '');
			}

			$list = lscandir_without_dot_or_underscore("/etc/vz/conf");
			
			foreach ($list as $l) {
				if (!cse($l, ".conf")) {
					continue;
				}
				
				if ($l === '0.conf') {
					continue;
				}
				
				$object = vps__openvz::createVpsObject($this->main->nname, $l);
				$objlist[$object->nname] = $object;
			}
		} else {

			lxshell_return("chkconfig", "xendomains", "on");
			//lxshell_return("service", "xendomains", "restart");
			$imdriver = $this->main->__var_xenimportdriver;
			$importdriverfile = "{$sgbl->__path_program_htmlbase}/lib/xenimport/xenimport__$imdriver.php";
			
			if (!lxfile_exists($importdriverfile)) {
				throw new lxException ("could_not_find_xen_import_driver_file", '');
			}

			include_once $importdriverfile;

			if (!function_exists("__xenimport_get_data")) {
				throw new lxException ("no_xenimport_function", '');
			}

			$data = __xenimport_get_data();

			foreach ((array)$data as $input) {
				$object = vps__xen::createVpsObject($this->main->nname, $input);
				$objlist[$object->nname] = $object;
			}
		}
		
		return $objlist;
	}


}
