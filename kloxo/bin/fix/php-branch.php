<?php
// release by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php";

$list = parse_opt($argv);

$select = (isset($list['select'])) ? $list['select'] : '';
$nolog  = (isset($list['nolog']))  ? $list['nolog'] : null;

setPhpBranch($select, $nolog);

function setPhpBranch($select, $nolog = null)
{
	$phpbranch = getPhpBranch();

	if ($select === $phpbranch) {
		print("\nIt's the same branch ({$select}); no changed.\n");
		return null;
	} elseif ($select === '') {
		print("\nIt's no select entry.\n");
		return null;
	} else {
		// check 'yum-plugin-replace' installed or not

		$yumreplace = 'yum-plugin-replace';

		if (!isRpmInstalled($yumreplace)) {
			$ret = lxshell_return("yum", "-y", "install", $yumreplace);

			if ($ret) {
				throw new lxException("{$yumreplace}_install_failed", '', 'parent');
			}
		}

		// MR -- php54-mysqlnd may conflict with php54-mysql
		if ($select === 'php54') {
			$ret = lxshell_return("yum", "-y", "remove", "{$phpbranch}-mysql");

			if ($ret) {
				throw new lxException("remove_{$phpbranch}-mysql_failed", '', 'parent');
			}
			
		}

		$ret = lxshell_return("yum", "-y", "replace", $phpbranch, "--replace-with={$select}");

		if ($ret) {
			throw new lxException("change_to_{$select}_branch_failed", '', 'parent');
		}

		if ($select === 'php54') {
			$ret = lxshell_return("yum", "-y", "remove", "{$select}-mysqlnd");

			if ($ret) {
				throw new lxException("remove_{$select}-mysqlnd_failed", '', 'parent');
			}

			$ret = lxshell_return("yum", "-y", "install", "{$select}-mysql");

			if ($ret) {
				throw new lxException("install_{$select}-mysql_failed", '', 'parent');
			}		
		}

	}

	exec("sh /script/fixphp");
	exec("sh /script/fixweb");

	// createRestartFile('phpfpm');
	// MR -- better using:
	exec("service php-fpm force-reload");
}
