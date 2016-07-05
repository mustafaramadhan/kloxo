<?php 

// by mustafa@bigraf.com for Kloxo-MR

include_once "lib/html/include.php"; 

$mysqlbranch = getRpmBranchInstalled('mysql');

echo "*** Change MySQL to MariaDB - begin ***\n";

system("yum clean all");
system("sh /script/fix-service-list");
echo "\n";

if (strpos($mysqlbranch, "MariaDB") !== false) {
	echo "* Already '{$mysqlbranch}' installed\n";
} elseif (strpos($mysqlbranch, "mariadb") !== false) {
	echo "* Already '{$mysqlbranch}' installed\n";
} else {
	exec("yum list|grep MariaDB", $out, $ret);
	
//	if ($ret) {
//		echo "- No repo for MariaDB.\n";
//		echo "  Open '/etc/yum.repos.d/mratwork.repo and change 'enable=0' to 'enable=1'\n";
//		echo "  under [mratwork-mariadb32] for 32bit OS or [mratwork-mariadb64] for 64bit OS\n";
//		exit;
//	} else {
		system("yum clean all");

		// MR -- also issue on Centos 5.9 - prevent for update!
		if (php_uname('m') === 'x86_64') {
			system("yum remove mysql*.i386 -y");

			system("yum remove mysql*.i686 -y");
		}

		
		$out2 = shell_exec("rpm -qa|grep {$mysqlbranch}");

		$arr = explode("\n", $out2);

		echo "- Remove MySQL packages\n";
		system("'cp' -f /etc/my.cnf /etc/my.cnf._bck_");
		
		foreach ($arr as &$o) {
			if (strpos($o, "-mysql") !== false) { continue; }
		//	if (strpos($o, "mysqlclient") !== false) { continue; }
			system("rpm -e {$o} --nodeps");
		}
		
		// MR -- may trouble if remove for mysqli extension
	//	system("yum install mysqlclient* -y");

		if (!file_exists("/var/lib/mysqltmp")) {
			mkdir("/var/lib/mysqltmp");			
		}

		chown("/var/lib/mysqltmp", "mysql:mysql");

		echo "- Install MariaDB\n";
		system("yum install MariaDB MariaDB-shared -y");

		system("'cp' -f /etc/my.cnf._bck_ /etc/my.cnf.d/my.cnf");

		system("chmod 777 /var/lib/mysqltmp");

		echo "- Restart MariaDB\n";
		system("chkconfig mysql on");
		system("service mysql restart");
//	}
}

echo "\n";
//echo " - Note: remove 'skip-innodb' from '/etc/my.cnf' and '/etc/my.cnf.d/server.cnf'.\n";
//echo "   Need reboot!.\n\n";

echo "*** Change MySQL to MariaDB - end ***\n";



