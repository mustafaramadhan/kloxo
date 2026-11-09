<?php 

include_once "lib/html/include.php";

$global_dontlogshell = true;


//collectdata_main();

function collectdata_main()
{
	if (lxfile_exists("/proc/xen")) {
		interfacetraffic_main();
		find_cpuusage();
	}

	if (lxfile_exists("/proc/vz")) {
		iptraffic_main();
		find_loadavg();
	}
}

function find_loadavg()
{
	$list = lfile("/proc/vz/vestat");
	foreach($list as $l) {
		if (csa($l, "Version")) {
			continue;
		}

		if (csa($l, "VEID")) {
			continue;
		}

		$l = trimSpaces($l);
		$load = explode(" ", $l);
		$cpu = $load[1] + $load[2] + $load[3];
		execRrdCpuusage("openvz-$load[0]", $cpu);
	}
}

function find_cpuusage()
{
	$out = lxshell_output("xm", "list");
	$list = explode("\n", $out);

	foreach($list as $l) {
		$l = trimSpaces($l);
		$val = explode(" ", $l);

		if (!cse($val[0], ".vm")) {
			continue;
		}
		execRrdCpuusage($val[0], $val[5]);
	}
}

function interfacetraffic_main()
{
	global $gbl, $sgbl, $login, $ghtml; 

	if (!lxfile_exists("{$sgbl->__path_program_etc}/xeninterface.list")) {
		return;
	}

	$list = lfile_trim("{$sgbl->__path_program_etc}/xeninterface.list");

	if (!lxfile_exists("{$sgbl->__path_program_etc}/newxeninterfacebw.data")) {
		foreach($list as $k) {
			$total[$k] = get_bytes_for_interface($k);
		}

		dprintr($total);
		lfile_put_contents("{$sgbl->__path_program_etc}/newxeninterfacebw.data", serialize($total));
		return;
	}

	$data = unserialize(lfile_get_contents("{$sgbl->__path_program_etc}/newxeninterfacebw.data"));

	$total = null;


	foreach($list as $k) {
		$total[$k] = get_bytes_for_interface($k);

		if (isset($data[$k])) {
			if ($total[$k]['total'] < $data[$k]['total']) {
				$v = $total[$k]['total'];
				$vinc = $total[$k]['incoming'];
				$vout = $total[$k]['outgoing'];
			} else {
				$v =   $total[$k]['total'] - $data[$k]['total'];
				$vinc = $total[$k]['incoming'] - $data[$k]['incoming'];
				$vout = $total[$k]['outgoing'] - $data[$k]['outgoing'];
			}
		} else {
			$v = $total[$k]['total'];
			$vinc = $total[$k]['incoming'];
			$vout = $total[$k]['outgoing'];
		}

		execRrdTraffic("xen-$k", $v, "-$vinc", $vout);
		$stringa[] = time() . " " . date("d-M-Y:H:i") . " $k $v $vinc $vout";
	}

	dprintr($total);

	$string = implode("\n{$stringa}");
	lfile_put_contents("/var/log/lxinterfacetraffic.log", "{$string}\n", FILE_APPEND);
	lfile_put_contents("{$sgbl->__path_program_etc}/newxeninterfacebw.data", serialize($total));
}

function get_bytes_for_interface($l)
{
	global $gbl, $sgbl, $login, $ghtml; 

	static $net;

	if (!$net) {
		$net = lfile_get_contents("/proc/net/dev");
		$net = explode("\n", $net);
	}

	foreach($net as $n) {
		$n = trimSpaces($n);

		if (!csb($n, "vif-$l:")) {
			continue;
		}

		$n = strfrom($n, "vif-$l:");
		$n = trimSpaces($n);
		$b = explode(" ", $n);
		$total = $b[0] + $b[8];

		// It seems for xen it is the reverse. The input for the vif is the output for the virtual machine.
		return array('total' => $total, 'incoming' => $b[8], 'outgoing' => $b[0]);
	}

	return 0;
}

function iptraffic_main()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_dontlogshell;

	$res = lxshell_output("iptables", "-nv", "-L", "FORWARD");

	$res = explode("\n", $res);

	$outgoing = null;

	foreach($res as $r) {
		$r = trimSpaces($r);

		$list = explode(' ', $r);

		if (!isset($list[7])) {
			continue;
		}

		if (csb($list[7], "0.0.0")) {
			// Just make sure that we don't calculate this goddamn thing twice,
			// which would happen if there are multiple copies of the same rule.
			// So mark that we have already read it in the sourcelist.
			if (!isset($sourcelist[$list[6]])) {
				$outgoing[$list[6]][] = $list[1];
				$sourcelist[$list[6]] = true;
			}
		} else if(csb($list[6], "0.0.0")) {
			if (!isset($dstlist[$list[7]])) {
				$incoming[$list[7]][] = $list[1];
				$dstlist[$list[7]] = true;
			}
		}
	}

	if (!$outgoing) {
		return;
	}

	if (!isset($incoming)) {
		return;
	}

	$realtotalincoming = calculateRealTotal($incoming);
	$realtotaloutgoing = calculateRealTotal($outgoing);

	foreach($realtotaloutgoing as $k => $v) {

		$vpsid = get_vpsid_from_ipaddress($k);

		if ($vpsid === 0) {
			continue;
		}

		if (!isset($vpsoutgoing[$vpsid])) { $vpsoutgoing[$vpsid] = 0; }
		if (!isset($vpsincoming[$vpsid])) { $vpsincoming[$vpsid] = 0; }

		$vpsoutgoing[$vpsid] += $realtotaloutgoing[$k];
		$vpsincoming[$vpsid] += $realtotalincoming[$k];
	}

	foreach($vpsincoming as $k => $v) {
		$tot = $vpsincoming[$k] + $vpsoutgoing[$k];
		execRrdTraffic("openvz-$k", $tot, "-$vpsincoming[$k]", $vpsoutgoing[$k]);
		$stringa[] = time() . " " . date("d-M-Y:H:i") . " openvz-$k $tot $vpsincoming[$k] $vpsoutgoing[$k]";
	}

	if ($stringa) {
		$string = implode("\n", $stringa);
		lfile_put_contents($sgbl->__path_iptraffic_file, "{$string}\n", FILE_APPEND);
	}

	lxshell_return("iptables", "-Z");
}

function execRrdTraffic($filename, $tot, $inc, $out)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$file = "{$sgbl->__path_program_root}/data/traffic/{$filename}.rrd";
	lxfile_mkdir("{$sgbl->__path_program_root}/data/traffic");

	if (!lxfile_exists($file)) {
		lxshell_return("rrdtool", 'create', $file, 'DS:cpu:ABSOLUTE:800:-1125000000:1125000000',
			'DS:incoming:ABSOLUTE:800:-1125000000:1125000000',
			'DS:outgoing:ABSOLUTE:800:-1125000000:1125000000', 'RRA:AVERAGE:0.5:1:600',
			'RRA:AVERAGE:0.5:6:700', 'RRA:AVERAGE:0.5:24:775',
			'RRA:AVERAGE:0.5:288:797');
	}

	lxshell_return("rrdtool", "update", $file, "N:$tot:$inc:$out");
}

function execRrdLoadAvg($filename, $tot)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$file = "{$sgbl->__path_program_root}/data/cpu/$filename.rrd";
	lxfile_mkdir("{$sgbl->__path_program_root}/data/cpu");
	
	if (!lxfile_exists($file)) {
		lxshell_return("rrdtool", 'create', $file, 'DS:load:GAUGE:800:0:11250',
			'RRA:AVERAGE:0.5:1:600', 'RRA:AVERAGE:0.5:6:700', 'RRA:AVERAGE:0.5:24:775',
			'RRA:AVERAGE:0.5:288:797');
	}
	
	lxshell_return("rrdtool", "update", $file, "N:$tot");
}

function execRrdCpuusage($filename, $tot)
{
	global $gbl, $sgbl, $login, $ghtml; 

	$tot = round($tot);
	$file = "{$sgbl->__path_program_root}/data/cpu/{$filename}.rrd";
	lxfile_mkdir("{$sgbl->$sgbl->__path_program_root}/data/cpu");
	
	if (!lxfile_exists($file)) {
		lxshell_return("rrdtool", 'create', $file, 'DS:cpu:DERIVE:800:0:112500',
			'RRA:AVERAGE:0.5:1:600', 'RRA:AVERAGE:0.5:6:700', 'RRA:AVERAGE:0.5:24:775',
			'RRA:AVERAGE:0.5:288:797');
	}
	
	lxshell_return("rrdtool", "update", $file, "N:$tot");
}

function get_vpsid_from_ipaddress($ip)
{
	global $gbl, $sgbl, $login, $ghtml; 

	static $res;

	if (!$res) {
		$res = lxshell_output('vzlist', '-H', '-o', 'vpsid,ip');
	}

	$list = explode("\n", $res);
	
	foreach($list as $l) {
		$l = trimSpaces($l);
		if (csa($l, $ip)) {
			list($vpsid) = explode(" ", $l);
			return $vpsid;
		}
	}

	return 0;
}
