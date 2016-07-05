<?php 

class lxguard__sync extends lxDriverClass
{
	function dbactionUpdate($subaction)
	{
		$rmt = new Remote();
		$rmt->data['disablehit'] = $this->main->disablehit;
		
		lfile_put_serialize("__path_home_root/lxguard/config.info", $rmt);
		lxshell_return("__path_php_path", "../bin/common/lxguard.php");
	}
}

