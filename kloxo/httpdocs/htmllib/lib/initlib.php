<?php 

function create_mysql_db($type, $opt, $admin_pass)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$progname = $sgbl->__var_program_name;
	if (!isset($opt['db-rootuser']) || !isset($opt['db-rootpassword'])) {
		print("Need db Root User and password --db-rootuser, --db-rootpassword \n");
		exit;
	}
	if ($sgbl->__var_database_type === 'mysql') {
		$req = mysql_connect('localhost', $opt['db-rootuser'], $opt['db-rootpassword']);
	} else if ($sgbl->__var_database_type === 'mssql') {
		$req = mssql_connect("localhost,$sgbl->__var_mssqlport");
	} else {
		$req = new PDO("sqlite:$sgbl->__var_dbf");
	}


	if (!$req) {
		print("Could not Connect to Database on localhost using root user\n");
	}
	//$sqlcm = lfile_get_contents("__path_program_root/httpdocs/sql/init/$type.sql");

	$dp = randomString(9);
	$dbadminpass = client::createDbPass($dp);


	$dbname = $sgbl->__var_dbf;
	$pguser = $sgbl->__var_admin_user;
	if ($sgbl->__var_database_type === 'mysql') {
		@ mysql_query("create database $dbname");
		mysql_query("grant all on $dbname.* to '$pguser'@'localhost' identified by '$dbadminpass';");
	} else if ($sgbl->__var_database_type === 'mssql') {
		 mssql_query("create database $dbname;");
		 mssql_query("use master ");
		 mssql_query("sp_addlogin '$pguser', '$dbadminpass', '$dbname';");
		 mssql_query("use $dbname ");
		 mssql_query("grant all to $pguser");
	} else {
	}


	lfile_put_contents("__path_admin_pass", $dbadminpass);
	lxfile_generic_chown("__path_admin_pass", "lxlabs");

}


function add_admin($pass)
{
		
	global $gbl, $sgbl, $login, $ghtml; 

	$client = new Client(null, null, 'admin');
	$login = $client;
	$client->initThisDef();
	$client->priv->pserver_num = 10;
	$client->priv->maindomain_num = 'Unlimited';
	$client->priv->vps_num = '5';
	$client->priv->client_num = 'Unlimited';
	$client->ddate = time();

	$ddb = new Sqlite(null, "client");
	if (!$ddb->existInTable("nname", 'admin')) {
		if ($sgbl->dbg > 0) {
			$pass = 'lxlabs';
			$res['contacemail'] = 'admin@lxlabs.com';
		}
		$res['password'] = crypt($pass);
		$res['cttype'] = 'admin';
		$res['cpstatus'] = 'on';
		if(if_demo()){
			$res['email'] = "admin@lxlabs.com";
		}
		$client->create($res);
		$client->driverApp = new client__sync(null, null, 'admin');
		$client->was();
		lxfile_mkdir("__path_client_root/$client->nname");
		lxfile_generic_chown("__path_client_root/$client->nname", "lxlabs");
	}

	$notif = new Notification(null, null, $client->getClName());
	$notif->initThisDef();
	$notif->dbaction = 'add';
	$notif->text_newaccountmessage = lfile_get_contents("__path_program_root/file/welcome.txt");
	$notif->parent_clname = $client->getClName();
	$notif->write();

	$display = new sp_SpecialPlay(null, null, $client->getClName());
	$display->initThisDef();
	$display->parent_clname = $client->getClName();
	$display->dbaction = 'add';
	$display->write();

}


function create_general()
{
	global $sgbl, $login, $ghtml; 
	$gen = new General(null, null, 'admin');
	$gen->initThisDef();
	$g = $gen->generalmisc_b;

	$g->npercentage = 90;
	$g->dpercentage = 110;
	$g->attempts = 3;
	$g->loginhistory_time = 2;
	$g->traffichistory_time = 6;

	$list = array("Billing", "Complaint", "Other");
	$h = null;
	foreach($list as $l) {
		$cat = new helpdeskcategory_a(null, null, $l);
		$h[$l] = $cat;
	}
	$gen->helpdeskcategory_a = $h;

	$gen->dbaction = 'add';
	$gen->write();

}

function create_servername()
{

	$pserver = new pserver(null, 'localhost', "localhost");
	$pserver->initThisDef();
	$pserver->rolelist = array("web", "dns");
	if (if_demo()) {
		$pserver->realpass = 'admin';
		$pserver->password = crypt("admin");
		$pserver->cpstatus = 'on';
	}
	$pserver->dbaction = "add";
	$pserver->postAdd();
	$pserver->superPostAdd();
	$pserver->was();
	//dprintr($pserver);
	return;
}





