<?php 

include_once "htmllib/lib/include.php"; 

initProgram('admin');


$login->loadAllObjects('client');
$list = $login->getList('client');

foreach($list as $c) {
	$dlist = $c->getList('domaina');
	$cdir = "__path_customer_root/{$c->getPathFromName('nname')}";
	lxfile_mkdir("$cdir/old");
	lxfile_mkdir("$cdir/cgi-bin");
	foreach($dlist as $l) {
		if (lxfile_exists("$cdir/$l->nname")) {
			lxfile_mv_rec("$cdir/$l->nname", "$cdir/old/");
		}
		if (!is_link("$cdir/domain/$l->nname/www/")) {
			lxfile_mv_rec("$cdir/domain/$l->nname/www/", "$cdir/$l->nname");
		}
		/*
		foreach($l->subweb_a as $k => $v) {
			lxfile_mv_rec("$cdir/domain/$l->nname/subdomains/$v->nname", "$cdir/$l->nname/$v->nname");
		}
	*/
		if (!is_link("$cdir/domain/$l->nname/cgi-bin/")) {
			lxfile_mv_rec("$cdir/domain/$l->nname/cgi-bin/", "$cdir/cgi-bin/$l->nname");
		}
		lunlink("$cdir/domain/$l->nname/www");
		lxfile_symlink("$cdir/$l->nname", "$cdir/domain/$l->nname/www");
		lxfile_symlink("$cdir/cgi-bin/$l->nname", "$cdir/domain/$l->nname/cgi-bin");
		$web = $l->getObject('web');
		$web->setUpdateSubaction('full_update');
		$dirlist = $web->getList('dirprotect');
		foreach($dirlist as $dir) {
			$dir->setUpdateSubaction('full_update');
			$dir->was();
		}
		$web->was();
	}
}

lxshell_return("__path_php_path", "../bin/misc/fixftpuserclient.phps", "--nolog");

