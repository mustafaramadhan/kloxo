<?php

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php";

// initProgram('admin');

$list = parse_opt($argv);

$select = (isset($list['select'])) ? $list['select'] : 'optimize';
$spare  = (isset($list['spare']))  ? (int)$list['spare'] : null;
$nolog  = (isset($list['nolog']))  ? $list['nolog'] : null;

setApacheOptimize($select, $spare, $nolog);

/* ****** BEGIN - setApacheOptimize ***** */

function setApacheOptimize($select, $spare = null, $nolog = null)
{
	log_cleanup("Apache optimize", $nolog);

	$factor = (isWebProxy()) ? 0.5 : 1;

	exec("/etc/init.d/httpd status", $out, $ret);

	$status = implode("\n", $out);

	if ($select === 'status') {
		log_cleanup("- Status: $status", $nolog);
	} else {
		//--- stristr for Case-insensitive
		if (stristr($status, 'running') !== FALSE) {
			log_cleanup("- Service stop", $nolog);

			$ret = lxshell_return("service", "httpd", "stop");

			if ($ret) { throw new lxexception('httpd_stop_failed', 'parent'); }
		}

		lxshell_return("sync; echo 3 > /proc/sys/vm/drop_caches");

		if (file_exists("/etc/httpd/conf.d/swtune.conf")) {
			//--- some vps include /etc/httpd/conf.d/swtune.conf
			log_cleanup("- Delete /etc/httpd/conf.d/swtune.conf if exist", $nolog);

			lunlink("/etc/httpd/conf.d/swtune.conf");
		}

		if ($select === 'optimize') {
			$m = array();

			// check memory -- $2=total, $3=used, $4=free, $5=shared, $6=buffers, $7=cached

			$m['total']   = (int)shell_exec("free -m | grep Mem: | awk '{print $2}'");
			$m['spare']   = ($spare) ? $spare : ($m['total'] * 0.25);

			$m['apps']    = (int)shell_exec("free -m | grep buffers/cache: | awk '{print $3}'");

		/*
			$m['used']    = (int)shell_exec("free -m | grep Mem: | awk '{print $3}'");
			$m['free']    = (int)shell_exec("free -m | grep Mem: | awk '{print $4}'");
			$m['shared']  = (int)shell_exec("free -m | grep Mem: | awk '{print $5}'");
			$m['buffers'] = (int)shell_exec("free -m | grep Mem: | awk '{print $6}'");
			$m['cached']  = (int)shell_exec("free -m | grep Mem: | awk '{print $7}'");

			$m['avail']   = $m['free'] + $m['shared'] + $m['buffers'] + $m['cached'] - $m['spare'];
		*/

			$m['avail'] = $m['total'] - $m['spare'] - $m['apps'];

			$maxpar_p = (int)($m['avail'] / 30 * $factor);
			$minpar_p = (int)($maxpar_p / 2);

			$maxpar_w = (int)($m['avail'] / 35 * $factor);
			$minpar_w = (int)($maxpar_w / 2);

			if ($maxpar_p < 4) {
				$maxpar_p = 4;
				$minpar_p = 2;
			}
		
			if ($maxpar_w < 4) {
				$maxpar_w = 4;
				$minpar_w = 2;
			}


		} elseif ($select === 'default') {
			$maxpar_p = 4;
			$minpar_p = 2;
			$maxpar_w = 4;
			$minpar_w = 2;
		}

		log_cleanup("- Service start", $nolog);

		$input = array('maxspareservers' => $maxpar_p, 'minspareservers' => $minpar_p,
				'maxsparethreads' => $maxpar_w, 'minsparethreads' => $minpar_w,
				'keepalive' => 'Off', 'maxrequestsperchild' => '2000');

		$tplsource = getLinkCustomfile("/home/apache/tpl", "~lxcenter.conf.tpl");

		$tpltarget = "/etc/httpd/conf.d/~lxcenter.conf";

		$tpl = file_get_contents($tplsource);

		$tplparse = getParseInlinePhp($tpl, $input);

		file_put_contents($tpltarget, $tplparse);

		log_cleanup("- Calculate: threads (min/max -> {$minpar_w}/{$maxpar_w}) and servers (min/max -> {$minpar_p}/{$maxpar_p})", $nolog);
		log_cleanup("- Write to '/etc/httpd/conf.d/~lxcenter.conf'", $nolog);

		$ret = lxshell_return("service", "httpd", "start");

		if ($ret) { throw new lxexception('httpd_start_failed', 'parent'); }

	}
}

/* ****** END - setApacheOptimize ***** */
