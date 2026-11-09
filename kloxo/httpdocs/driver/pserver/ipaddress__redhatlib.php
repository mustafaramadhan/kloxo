<?php

class Ipaddress__Redhat extends LxDriverclass
{
	function IpaddressEdit($action)
	{
		global $gbl, $sgbl, $login;

		if ($this->main->devname === 'NAT') { return; }

		$this->checkForEthBase();

		if ($sgbl->dbg > 1 && $this->main->devname === 'eth0') {
			return 1;
		}

		$ipaddr = $this->main->ipaddr;
		$netmask = $this->main->netmask;
		$temp_ipaddr = explode(".", $ipaddr);
		$temp_netmask = explode(".", $netmask);

		$i = 0;

		foreach ($temp_ipaddr as $row) {
			$ipaddr_binary[$i] = str_pad(base_convert($row, 10, 2), 8, '0', STR_PAD_LEFT);
			$i++;
		}

		$i = 0;

		foreach ($temp_netmask as $row) {
			$netmask_binary[$i] = str_pad(base_convert($row, 10, 2), 8, '0', STR_PAD_LEFT);
			$networkip[$i] = ($netmask_binary[$i] & $ipaddr_binary[$i]);
			$converted[$i] = base_convert($networkip[$i], 2, 10);
			$i++;
		}

		$networkaddress = implode(".", $converted);
		$dev = explode("-", $this->main->devname);

		if (count($dev) >= 2) {
			$actualname = implode(":", $dev);
		} else {
			$actualname = $this->main->devname;
		}

		$ipaddrfile = "$sgbl->__path_real_etc_root/sysconfig/network-scripts/ifcfg-" . $actualname;

		$fdata = null;

		$fdata .= "DEVICE=" . $actualname . "\n";

		$status = "yes";

		$fdata .= "ONBOOT=$status \n";

		if (isset($this->main->bproto)) {
			$fdata .= "BOOTPROTO=" . $this->main->bproto . "\n";
		} else {
			$fdata .= "BOOTPROTO=" . "static" . "\n";
		}

		$fdata .= "IPADDR=" . $this->main->ipaddr . "\n";
		$fdata .= "NETMASK=" . $this->main->netmask . "\n";
		$fdata .= "NETWORK=" . $networkaddress . "\n";
		$fdata .= "GATEWAY=" . $this->main->gateway . "\n";

		if (isset($this->main->userctl)) {
			$fdata .= "USERCTL=" . $this->main->userctl . "\n";
		}

		if (isset($this->main->peerdns)) {
			$fdata .= "PEERDNS=" . $this->main->peerdns . "\n";
		}

		if (isset($this->main->itype)) {
			$fdata .= "TYPE=" . $this->main->itype . "\n";
		}

		if (isset($this->main->ipv6init)) {
			$fdata .= "IPV6INIT=" . $this->main->ipv6init . "\n";
		}

		lfile_put_contents($ipaddrfile, "$fdata");

		ipaddress::copyCertificate($this->main->devname, $this->main->getParentName());

		lxshell_return("ifdown", $actualname);
		lxshell_return("ifup", $actualname);
	}

	function checkForEthBase()
	{
		global $login;

		if (ipaddress::checkIfBaseAddress($this->main->devname)) {
			throw new lxException($login->getThrow("modifying_eth_not_permitted"), '', $this->main->devname);

			return;
		}
	}

	function dbactionAdd()
	{
		$this->IpaddressEdit('add');

	//	createRestartFile($this->main->__var_dnsdriver);
		createRestartFile("restart-dns");
	
		// MR -- not needed because Kloxo-MR use *:port instead existing ip for webconfig
	//	exec("sh /script/fixweb --target=defaults");
	}

	function dbactionUpdate($subaction)
	{
		global $login;

		throw new lxException($login->getThrow("modifying_not_permitted"), '', $subaction);
	}

	function dbactionDelete()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$this->checkForEthBase();
		$dev = explode("-", $this->main->devname);

		if (count($dev) >= 2) {
			$actualname = implode(":", $dev);
		} else {
			$actualname = $this->main->devname;
		}

		$ipaddrfile = "$sgbl->__path_real_etc_root/sysconfig/network-scripts/ifcfg-" . $actualname;
		lxshell_return("ifdown", $actualname);
		lxfile_rm($ipaddrfile);

	//	createRestartFile($this->main->__var_dnsdriver);
		createRestartFile("restart-dns");
	}

	static function getModernIps()
	{
		$result = array();
		$json = @shell_exec("ip -j addr 2>/dev/null");
		if ($json) {
			$data = @json_decode($json, true);
			if (is_array($data)) {
				foreach ($data as $iface) {
					$ifname = isset($iface['ifname']) ? $iface['ifname'] : '';
					if (!$ifname || $ifname === 'lo') continue;
					if (!empty($iface['addr_info']) && is_array($iface['addr_info'])) {
						foreach ($iface['addr_info'] as $info) {
							if (isset($info['family']) && $info['family'] === 'inet') {
								$ip = isset($info['local']) ? $info['local'] : '';
								if (!$ip || $ip === '127.0.0.1') continue;
								$prefix = isset($info['prefixlen']) ? (int)$info['prefixlen'] : 24;
								$netmask = long2ip(-1 << (32 - $prefix));
								$devname = str_replace(':', '-', $ifname);
								$result[] = array(
									'devname' => $devname,
									'ipaddr' => $ip,
									'netmask' => $netmask,
									'status' => 'yes',
									'gateway' => '',
									'bproto' => 'none',
									'itype' => 'Ethernet',
									'userctl' => 'no',
									'peerdns' => 'no',
									'ipv6init' => 'no'
								);
							}
						}
					}
				}
			}
		}

		if (empty($result)) {
			$out = @shell_exec("ip -o -4 addr show 2>/dev/null");
			if ($out) {
				$lines = explode("\n", trim($out));
				foreach ($lines as $line) {
					$parts = preg_split('/\s+/', trim($line));
					if (count($parts) >= 4) {
						$devname = $parts[1];
						if ($devname === 'lo') continue;
						$cidr = $parts[3];
						$cidr_parts = explode('/', $cidr);
						$ip = $cidr_parts[0];
						$prefix = isset($cidr_parts[1]) ? (int)$cidr_parts[1] : 24;
						$netmask = long2ip(-1 << (32 - $prefix));
						$devname = str_replace(':', '-', $devname);
						$result[] = array(
							'devname' => $devname,
							'ipaddr' => $ip,
							'netmask' => $netmask,
							'status' => 'yes',
							'gateway' => '',
							'bproto' => 'none',
							'itype' => 'Ethernet',
							'userctl' => 'no',
							'peerdns' => 'no',
							'ipv6init' => 'no'
						);
					}
				}
			}
		}

		return $result;
	}

	static function getCurrentIps()
	{
		global $gbl, $sgbl, $login, $ghtml;

		$path = $sgbl->__path_real_etc_root . "sysconfig/network-scripts";

		$result1 = array();
		if (is_dir($path)) {
			$flist = lscandir($path);

			foreach ((array)$flist as $file) {
				if (char_search_a($file, "ifcfg-")) {
					$result1[] = self::get_network_data(str_replace("{$path}/ifcfg-", "", "{$path}/{$file}"));
				}
			}
		}

		$result = array(); // Initialize as array (expected return result)

		foreach ($result1 as $res) {
			$temp = explode(":", $res['devname']);

			if (count($temp) === 2) {
				$res['devname'] = implode("-", $temp);
			}

			$result[] = $res;
		}

		if (empty($result)) {
			$result = self::getModernIps();
		}

		return ($result);
	}

	static function listSystemIps($machinename)
	{
		global $gbl, $sgbl, $login, $ghtml;
		$result = self::getCurrentIps();

	//	web__apache::createWebmailConfig($result);
	//	web__apache::createWebDefaultConfig($result);

		// MR -- not needed because Kloxo-MR use *:port instead existing ip for webconfig
	//	exec("sh /script/fixweb --target=defaults");

		$res = ipaddress::fixstatus($result);

		foreach ($res as $r) {
			if ($sgbl->isKloxo()) {
				ipaddress::copyCertificate($r['devname'], $machinename);
			}
		}

		return $res;
	}

	static function get_network_data($devname)
	{
		// MR -- use directly to get_ifconfig_parse because unstandard ifcfg make
		// trouble to reading

		$list = self::get_ifconfig_parse($devname);

		foreach ($list as $key => $value) {
			switch ($key) {
				case "DEVICE":
					$result['devname'] = $value;
					break;

				case "IPADDR":
					$result['ipaddr'] = $value;
					break;

				case "NETMASK":
					$result['netmask'] = $value;
					break;

				case "ONBOOT":
					$result['status'] = $value;
					break;

				case "GATEWAY":
					$result['gateway'] = $value;
					break;

				case "USERCTL":
					$result['userctl'] = $value;
					break;

				case "PEERDNS":
					$result['peerdns'] = $value;
					break;

				case "TYPE":
					$result['itype'] = $value;
					break;

				case "IPV6INIT":
					$result['ipv6init'] = $value;
					break;

				case "BOOTPROTO":
					$result['bproto'] = $value;
					break;
			}
		}

		if (!isset($result['devname'])) {
			$result['devname'] = $devname;
		}

		if (!isset($result['status'])) {
			$result['status'] = "yes";
		}

		if (!isset($result['gateway']))
			$result['gateway'] = null;

		if (!isset($result['userctl'])) {
			$result['userctl'] = null;
		}

		if (!isset($result['netmask'])) {
			$result['netmask'] = null;
		}

		if (!isset($result['peerdns'])) {
			$result['peerdns'] = null;
		}

		if (!isset($result['itype'])) {
			$result['itype'] = null;
		}

		if (!isset($result['ipv6init'])) {
			$result['ipv6init'] = null;
		}

		if (!isset($result['bproto'])) {
			$result['bproto'] = null;
		}

		return ($result);
	}

	static function get_ifconfig_parse($devname)
	{
		// MR - mod from http://www.plugged.in/linux/getting-network-information-in-bash-scripts.html
		// call ifconfig and ip must with full path!

		$t = explode(":", $devname);
		$pdevname = $t[0];

		exec("/sbin/ifconfig {$devname}", $out);
		$vifconfig = implode("\n", $out);
		$out = null;

		// MR -- use this trick to fix OpenVZ issue
		if (stripos($devname, ':') !== false) {
			exec("/sbin/ip addr show | grep '{$devname}'", $out);
		} else {
			exec("/sbin/ip addr show | grep '{$devname}'|grep -v '{$devname}:'", $out);
		}

		$vip = implode("\n", $out);
		$out = null;

		$list = array();

		$list['DEVICE']     =  $devname;
		exec("echo '{$vifconfig}' | grep -w encap | awk '{print $3}' | cut -d \":\" -f 2", $out);
		$list['TYPE']       =  $out[0];
		$out = null;
		// MR -- exception for OpenVZ
		if (strpos($vifconfig, 'P-t-P') !== false) {
			exec("echo '{$vifconfig}' | grep -w inet | awk '{print $5}' | cut -d \":\" -f 2", $out);
		} else {
			exec("echo '{$vifconfig}' | grep -w inet | awk '{print $4}' | cut -d \":\" -f 2", $out);
		}
		$list['NETMASK']    =  $out[0];
		$out = null;
		exec("echo '{$vip}' | grep 'inet' | grep 'scope global' | awk '{print $2}' | awk -F '/' '{print $1}'", $out);
		$list['IPADDR']  = $out[0];
		$out = null;
		exec("echo '{$vip}' | grep 'inet' | grep 'scope global' | awk '{print $2}' | awk -F '/' '{print $2}'", $out);
		$list['IPPREFIX']   =  $out[0];
		$out = null;
		exec("echo '{$vip}' | grep 'inet6' | grep 'scope global' | awk '{print $2}' | awk -F '/' '{print $1}'", $out);
		$list['IP6ADDR'] =  $out[0];
		$out = null;
		exec("echo '{$vip}' | grep 'inet6' | grep 'scope global' | awk '{print $2}' | awk -F '/' '{print $2}'", $out);
		$list['IP6PREFIX']  =  $out[0];
		$out = null;
		// MR -- exception for OpenVZ
	//	if ($list['NETMASK'] === '255.255.255.255') {
		if (strpos($vifconfig, 'P-t-P') !== false) {
			$list['GATEWAY']   = $list['IPADDR'];
		} else {
		//	exec("/sbin/ip route show | grep {$pdevname} | grep link | awk '{ print $1}'", $out);
			exec("/sbin/ip route show | grep {$pdevname} | grep default | awk '{ print $3}'", $out);
			$list['GATEWAY']   =  $out[0];
		}
		$out = null;
		exec("echo '{$vifconfig}' | grep HWaddr | awk '{ print $5 }'", $out);
		$list['MACADDRESS'] =  $out[0];
		$out = null;
		// MR -- exception for OpenVZ
	//	if ($list['NETMASK'] === '255.255.255.255') {
		if (strpos($vifconfig, 'P-t-P') !== false) {
			$list['BROADCAST']   = $list['IPADDR'];
		} else {
			exec("echo '{$vifconfig}' | grep -w inet | awk '{print $3}' | cut -d \":\" -f 2", $out);
			$list['BROADCAST']  =  $out[0];
		}

		$out = null;

		// MR -- need info from ifcfg-* file for bproto
		$list2 = self::get_ifcfgfile_parse($pdevname);
		$list['BOOTPROTO'] = $list2['BOOTPROTO'];

		return $list;
	}

	static function get_ifcfgfile_parse($devname)
	{
		// MR -- must with @ if not want notice message for '#' deprecated in php 5.3+
		$ret = @parse_ini_file("/etc/sysconfig/network-scripts/ifcfg-{$devname}");

		return $ret; 
	}
}
