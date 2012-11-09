<?php 

// release on Kloxo 6.1.7
// by mustafa.ramadhan@lxcenter.org

include_once "htmllib/lib/include.php"; 

// initProgram('admin');

$list = parse_opt($argv);

$engine = ($list['engine']) ? $list['engine'] : 'MyISAM';
$database = (isset($list['database'])) ? $list['database'] : null;
$table = (isset($list['table'])) ? $list['table'] : null;
$config = (isset($list['config'])) ? $list['config'] : null;

setMysqlConvert($engine, $database, $table, $config);

/* ****** BEGIN - setMysqlConvert ***** */

/* move from mysql-convert.php */

function setMysqlConvert($engine, $database, $table, $config)
{
	global $gbl, $sgbl, $login, $ghtml;

	log_cleanup("Convert of MySQL engine");

	$engine = strtolower($engine);

	$database = ($database) ? $database : '_all_';
	$table = ($table) ? $table : '_all_';
	$config = ($config) ? $config : 'yes';

	$pass = slave_get_db_pass();

	//--- the first - to 'disable' skip- and restart mysql
	system("sed -i 's/skip/\;###123###skip/g' /etc/my.cnf");
	$ret = lxshell_return("service", "mysqld", "restart");
	if ($ret) { throw new lxexception('mysqld_restart_failed', 'parent'); }

	mysql_connect('localhost', 'root', $pass);

	log_cleanup("- Converting to ".$engine." engine");

	if ($database === '_all_') {
		$dbs = mysql_query('SHOW databases');

		while ($db = mysql_fetch_array($dbs)) {
			log_cleanup("-- ".$db[0]." database converted");

			if ($db[0] === 'mysql') {
			}
			else if ($db[0] === 'information_schema') {
			}
			else {
				mysql_select_db($db[0]);

				if ($table === '_all_') {
					$tbls = mysql_query('SHOW tables');

					while ($tbl = mysql_fetch_array($tbls)) {
						log_cleanup("--- ".$tbl[0]." table converted");
						mysql_query("ALTER TABLE {$tbl[0]} ENGINE={$engine}");
					}
				}
				else {
					mysql_query("ALTER TABLE {$table} ENGINE={$engine}");
					log_cleanup("--- ".$table." table converted");
				}
			}
		}
	}
	else {
		mysql_select_db($database);

		log_cleanup("-- ".$database." database converted");

		if ($table === '_all_') {
			$tbls = mysql_query('SHOW tables');

			while ($tbl = mysql_fetch_array($tbls)) {
				log_cleanup("--- ".$tbl[0]." table converted");
				mysql_query("ALTER TABLE {$tbl[0]} ENGINE={$engine}");
			}
		}
		else {
			mysql_query("ALTER TABLE {$table} ENGINE={$engine}");
			log_cleanup("--- ".$table." table");
		}
	}

	//--- the second - back to 'original' config and restart mysql
	system("sed -i 's/\;###123###skip/skip/g' /etc/my.cnf");
	$ret = lxshell_return("service", "mysqld", "restart");
	if ($ret) { throw new lxexception('mysqld_restart_failed', 'parent'); }

	if ($config === 'yes') {
		if ($database === '_all_') {
			$file = "/etc/my.cnf";

			$string = file_get_contents($file);

			$string_array = explode("\n", $string);

			$string_collect = null;

			foreach($string_array as $sa) {
				if (stristr($sa, 'skip-innodb') !== FALSE) {
					$string_collect .= "";
					continue;
				}
				if (stristr($sa, 'default-storage-engine') !== FALSE) {
					$string_collect .= "";
					continue;
				}
				$string_collect .= $sa."\n";
			}
		
			if ($engine === 'myisam') {
				$string_source = "[mysqld]\n";
				$string_replace = "[mysqld]\nskip-innodb\ndefault-storage-engine=myisam\n";
				log_cleanup("- Added \"skip-innodb and default-storage-engine=".$engine."\" in /etc/my.cnf");
			}
			else {
				$string_source = "[mysqld]\n";
				$string_replace = "[mysqld]\ndefault-storage-engine={$engine}\n";
				log_cleanup("- Added \"default-storage-engine=".$engine."\" in /etc/my.cnf");
			}

			$string_collect = str_replace($string_source, $string_replace, $string_collect);

			file_put_contents($file, $string_collect);
		}
	}

	log_cleanup("- Convert of MySQL to ".$engine." engine finished");

	log_cleanup("- MySQL Service restarted");
	$ret = lxshell_return("service", "mysqld", "restart");
	if ($ret) { throw new lxexception('mysqld_restart_failed', 'parent'); }
}

/* ****** END - setMysqlConvert ***** */