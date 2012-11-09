<?php 

class dirprotect__ extends lxDriverClass
{

	function dbactionAdd()
	{
		$this->createDiruserfile();
	}

	function dbactionUpdate($subaction)
	{
		$this->createDiruserfile();
	}

	function dbactionDelete()
	{
		$dir = '__path_httpd_root/' . $this->main->getParentName() . '/__dirprotect';
		$dirfile = $dir . '/' . $this->main->getFileName();

		lxfile_rm($dirfile);
	}

	function createDiruserfile()
	{
		$dir = '__path_httpd_root/' . $this->main->getParentName() . '/__dirprotect';
		$dirfile = $dir . '/' . $this->main->getFileName();

		$chownug = $this->main->__var_username . ':apache';

		if (!lxfile_exists($dir)) {
			lxuser_mkdir($chownug, $dir);
			lxfile_generic_chmod($dir, '0750');
		}

		lxfile_rm($dirfile);

		if ($this->main->status == 'on') {
			$fstr = '';
			foreach ($this->main->diruser_a as $v) {
				$fstr .= $v->nname . ':' . crypt($v->param) . "\n";
			}

			lxuser_put_contents($chownug, $dirfile, $fstr);
			lxfile_generic_chmod($dirfile, '0750');
		}
	}

}
