<?php 

class cron__Linux extends lxDriverClass {


function dbactionAdd()
{
	$this->syncCreateConf();
	$ret = array("__syncv_jobid" => $this->main->nname);

	return $ret;

}

function dbactionDelete()
{
	$this->syncCreateConf();
}

function dbactionUpdate($subaction)
{
	$this->syncCreateConf();
}

function getCronString($list)
{
	if (!is_array($list)) {
		return $list;
	}
	if ($list[0] === '--all--') {
		return "*";
	} else {
		return implode(",", $list);
	}
}


function syncCreateConf()
{
	global $gbl, $sgbl, $login, $ghtml; 
	global $global_shell_error;
	
	if_demo_throw_exception('cron');
	$conf_file = "__path_cron_root/{$this->main->username}";
	$list = array('minute', 'hour', 'weekday', 'ddate', 'month');


	$tfile = lx_tmp_file($conf_file);

	$cmd = null;

	if ($this->main->__var_mailto) {
		$cmd .= "MAILTO={$this->main->__var_mailto}\n";
	} else {
		$cmd .= "MAILTO=\"\"\n";
	}

	$result = $this->main->__var_cron_list;

	foreach($result as &$__r) {
		foreach($list as $l) {
			$__r[$l] = unserialize(base64_decode($__r["ser_$l"]));
		}
	}

	$result = merge_array_object_not_deleted($result, $this->main);

	//dprintr($result);

	foreach((array) $result as $v) {

		if ($v['ttype'] === 'simple') {
			$v['weekday'] = array('--all--');
			$v['month'] = array('--all--');
			$v['ddate'] = array('--all--');
			if ($v['simple_cron'] === 'every-day') {
				$v['hour'] = $v['cron_day_hour'];
				$v['minute'] = 0;
			}
			if ($v['simple_cron'] === 'every-hour') {
				$v['hour'] = array('--all--');
				$v['minute'] = 0;
			}
			if ($v['simple_cron'] === 'every-minute') {
				$v['hour'] = array('--all--');
				$v['minute'] = array('--all--');
			}

		} else {
			foreach($v["weekday"] as &$___tq)  {
				if (is_numeric($___tq)) {
					$___tq-=1;
				}
			}
		}

		foreach($list as $l) {
			$v[$l] = $this->getCronString($v[$l]);
		}

		if (!$v['minute']) { $v['minute'] = 0; }

		$cmd .= implode("\t", array($v['minute'], $v['hour'], $v['ddate'], $v['month'],$v['weekday'], $v['command']));
		$cmd .= "\n";
	}

	lfile_put_contents($tfile, $cmd);

	if (!posix_getpwnam($this->main->username)) {
		lxfile_rm("/var/spool/cron/{$this->main->username}");
		return;
	}


	$ret = lxshell_return("crontab", "-u", $this->main->username, $tfile);

	if ($ret) {
		// Why exactly was a throw removed? backup/restore?
		//throw new lxException("adding_cron_failed", "", $global_shell_error);
		$gbl->setWarning('adding_cron_failed', '', $global_shell_error);
		$data = lfile_get_contents($tfile);
		log_log("cron_error", $data);
	}

	lunlink($tfile);
}
}
