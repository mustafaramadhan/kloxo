<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

initProgram('admin');

$list = parse_opt($argv);

$server = (isset($list['server'])) ? $list['server'] : 'localhost';
$select = (isset($list['select'])) ? $list['select'] : 'all';
$nolog  = (isset($list['nolog']))  ? $list['nolog'] : null;

log_cleanup("Fixing Chown and Chmod", $nolog);

$login->loadAllObjects('client');
$clist = $login->getList('client');

$prevsyncserver = '';
$currsyncserver = '';

foreach($clist as $c) {
	$cinfo = posix_getpwnam($c->nname);

	if (!$cinfo) { continue; }

	if ($server !== 'all') {
		$sa = explode(",", $server);
		if (!in_array($c->syncserver, $sa)) { continue; }
	}

	$dlist = $c->getList('domaina');

	foreach((array) $dlist as $l) {
		$web = $l->getObject('web');

		$currsyncserver = $web->syncserver;

		if ($prevsyncserver !== $currsyncserver) {
			$prevsyncserver = $currsyncserver;

			if (!$nolog) {
				if ($select === 'all') {
					$web->setUpdateSubaction('fix_chownchmod_all');
				} elseif ($select === 'chown') {
					$web->setUpdateSubaction('fix_chownchmod_own');
				} elseif ($select === 'chmod') {
					$web->setUpdateSubaction('fix_chownchmod_mod');
				}
			} else {
				if ($select === 'all') {
					$web->setUpdateSubaction('fix_chownchmod_all_nolog');
				} elseif ($select === 'chown') {
					$web->setUpdateSubaction('fix_chownchmod_own_nolog');
				} elseif ($select === 'chmod') {
					$web->setUpdateSubaction('fix_chownchmod_mod_nolog');
				}
			}

			$web->was();
		}
	}
}


