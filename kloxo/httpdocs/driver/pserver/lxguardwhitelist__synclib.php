<?php 
class lxguardwhitelist__sync extends lxDriverClass {

	function dbactionAdd()
	{
		$this->updateWht();
	}

	function dbactionDelete()
	{
		$this->updateWht();
	}

	function dbactionUpdate($subaction)
	{
		$this->updateWht();
	}

	function updateWht()
	{
		$res = $this->main->__var_whitelist;
		$res = merge_array_object_not_deleted($res, $this->main);

		$list = get_namelist_from_arraylist($res, 'ipaddress');

		$rmt = new Remote();
		$rmt->data = $list;

		lfile_put_serialize("__path_home_root/lxguard/whitelist.info", $rmt);
		lxshell_return("__path_php_path", "../bin/common/lxguard.php");
	}
}
