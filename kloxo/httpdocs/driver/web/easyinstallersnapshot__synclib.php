<?php 

class easyinstallersnapshot__sync extends Lxdriverclass
{
	function dbactionDelete()
	{
		lxfile_rm_rec("{$this->main->__var_snapbase}/{$this->main->nname}");
	}

	static function getSnapList($path) 
	{
		$list = lscandir_without_dot($path);

		foreach($list as $l) {
			$res['nname'] = $l;
			$rmt = lfile_get_unserialize("$path/$l/metadata.data");
			list($res['appname'], $res['ddate']) = explode("-", $l);
			$res['app_real_nname'] = $rmt->data->nname;
			$res['app_real_date'] = $rmt->data->ddate;
			$ret[$l] = $res;
		}

		return $ret;
	}
}
