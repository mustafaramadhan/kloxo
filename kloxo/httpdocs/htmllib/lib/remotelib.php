<?php

function do_remote($rmt)
{
	if ($rmt->action === 'set') {
		$robject = $rmt->robject;

		if (is_array($robject->subaction)) {
			$sub = implode(",", $robject->subaction);
		} else {
			$sub = $robject->subaction;
		}

		log_message("Remote Object: {$robject->get__table()}:{$robject->nname}:{$robject->dbaction}:$sub\n");
		$driver = get_class($robject->driverApp);
		dprint("Remote Object: {$robject->get__table()}:$driver:{$robject->nname}:{$robject->dbaction}:$sub\n");
	} else {
		if (is_array($rmt->func)) {
			$f = implode("::", $rmt->func);
		} else {
			$f = $rmt->func;
		}

		dprint("Get Action: $f\n");
	}

	//	dprintr($rmt);

	$res = check_for_remote($rmt);
	dprint("\nDone....\n-------------\n");
	$res->message = "none";

	//	dprintr($res);
	return $res;
}


function update_from_master($rmt)
{
	global $gbl, $sgbl, $login, $ghtml;

	$ver = $rmt->version;
	//lxfile_rm("lib/gbl.php");
	//lxshell_return("cvs", "up");
	if (!lx_core_lock_check_only('update.php')) {
		exec_with_all_closed("$sgbl->__path_php_path ../bin/update.php --till-version=$ver");
		//sleep(1);
	}

}

function do_do_the_action($rmt)
{
	global $gbl, $sgbl, $login, $ghtml;

	return do_local_action($rmt);

	if ($rmt->action == "set" || $rmt->action == 'get') {
		if (isLocalhost($rmt->slaveserver)) {
		} else {
			//return rl_exec(null, $rmt->slaveserver, $rmt);
		}
	} else {
		if ($rmt->action == 'dowas') {
			$object = $rmt->robject;
			$object->__masterserver = null;
			dprint("in dowas\n");

			return $object->doWas();
		}
	}
}

function do_the_action($rmt, $res)
{
	try {
		$ddata = do_do_the_action($rmt);
		//fprint("in do the action");
		//fprint($ddata);
		$res->ddata = $ddata;
	} catch (Exception $e) {
		if ($e instanceof lxexception) {
			$res->exception = $e;
		} else {
			$code = null;

			if ($e instanceof com_exception) {
				$code = $e->getCode();
				dprint($e->getCode() . "\n");
			}

			dprint($e->getMessage());
			dprint($e->getTraceAsString());

			$res->exception = new lxException($e->getMessage(), $code);
		}

		$res->ddata = null;
	}

	return $res;
}

function check_for_remote($rmt)
{
	global $gbl, $sgbl, $login, $ghtml;

	$local = false;

	$res = new Remote();
	$res->exception = null;

	if (!$rmt->authenticated) {
		$local = true;

		if (!($rmt->password === getAdminDbPass())) {
			log_message("Failed Local access to $rmt->remote_login from localhost");
			$res->exception = new lxException("login_failed", $rmt->machine);

			return $res;
		}
	}

	if (!$local) {
		log_message("Successful Access access to $rmt->remote_login from $rmt->machine");
	}

	$vercmp = version_cmp($rmt->version, $sgbl->__ver_major_minor_release);

	if ($local) {
		if ($vercmp > 0) {
			$res->exception = new lxException("backend_server_restarting", $rmt->machine);
			os_restart_program();

			return $res;
		}
	}

	//	dprintr($rmt);

	/*
	 // Even if it is demo, versions must be updated, otherwise, results are unpredictable.
	 if (if_demo()) {
		 do_the_action($rmt, $res);

		 $res->state = 'success';

		 return $res;
	 }
 */

	if ($vercmp < 0) {
		$res->state = 'version_greater';
		print("Version Greater <br> \n");
		$res->exception = new lxException("slave_version_higher._please_update_master_to_the_latest_version", $rmt->machine);

		return $res;
	}

	if ($vercmp > 0) {
		update_from_master($rmt);
		//$res->state = 'upgrade';
		$res->exception = new lxException("slave_upgrading_please_try_after_a_few_minutes", 'machine', $rmt->machine);
		print("Version Lesser <br> \n");

		return $res;
	}

	do_the_action($rmt, $res);

	$res->state = 'success';

	return $res;
}


function do_remote_exec($machine, $rmt, $cmdtype, $nname, $dbaction)
{
	global $gbl, $sgbl, $login, $ghtml;

	$remotechar = $sgbl->__var_remote_char;

	if ($login && $login->isSuperClient()) {
		$table = 'node';
		$class = 'admin';
	} else {
		$table = 'pserver';
		$class = 'slave';
	}

	if (!isLocalhost($machine)) {

		$var = $table . "_password";

		if (isset($gbl->$var) && $gbl->$var) {
			$password = $gbl->$var;
		} else {
			$ssm = new Sqlite(null, $table);
			$res = $ssm->rawQuery("select realpass from $table where nname = '$machine'");

			if ($res) {
				$password = $res[0]['realpass'];
			} else {
				throw new lxException("machine_doesnt_exist_in_db", 'nname', $machine);
			}
		}
	} else {
		$password = getAdminDbPass();
		$machine = 'localhost';
	}

	$port = $sgbl->__var_prog_port;

	$rmt->version = $sgbl->__ver_major_minor_release;

	$rmt->machine = $machine;
	$rmt->remote_login = $class;
	$rmt->password = $password;
	$rmt->master_c = getOneIPForLocalhost($machine);

	$var = base64_encode(serialize($rmt));

	if (!isLocalhost($rmt->machine)) {
		$user = base64_encode('slave');
		$pass = base64_encode($rmt->password);
		$var = $remotechar . "\n" . $user . "\n" . $pass . "\n" . $var;
	}

	$totalout = send_to_some_server($machine, $var);

	$res = unserialize(base64_decode($totalout));

	if (!$res) {
		throw new lxException('could_not_connect_to_server', 'syncserver', $machine);
	}

	//	dprint($res->message);

	if ($res->exception) {
		throw $res->exception;
	}

	//	dprint($res->message);

	//	print_time('server', "remote<b> $raddress</b>: $size KB", 2);

	// We have only return values. The output of the command is discarded.
	// This leads to tremendous savings of bandwidth; makes the communication almost one way.
	// If you want to get the output, you have to use the lxshell_output function and give your command as the argument.
	// This function changes the output as a return value which is then returned back.
	// The whole concept is about function execution, and returning the ret value of the function.

	$err = $res ? 3 : 2;

	dprint("<br>  <table border=2> <tr> <td > Remote: $machine, $cmdtype, $nname, $dbaction<br> ", $err);
	if (!$res) {
		dprint("<b> <font color=red>Got Error: </b> </font> $res", $err);
		//$ser = base64_decode($res);
		//dprint($ser);
	} else {
		dprint("Message: " . $res->message . "<br> ", $err);
	}

	dprint("</td> </tr> </table>  ", $err);

	if (!$res) {
		dprint("Warning");
	}

	if ($res->__this_warning) {
		$gbl->__this_warning = $res->__this_warning;
	}

	return $res;
}

/**
 * @return void
 * @param
 * @param
 * @desc Remote takes two type of command. When called as "get", it executes a given function in the The other machine and returns the return value.  When called as "set", it clones the third argument (an lxclass object), clears all the children And sends the object to the other end, where the 'dosynctosystem' method in the object Is executed.
 */
function remote_exec($machine, $cmd)
{
	global $gbl, $sgbl, $login, $ghtml;

	$version = $sgbl->__ver_major_minor_release;

	if (isLocalhost($machine)) {
		if (is_secondary_master()) {
			throw new lxException('you_are_on_secondary_master', '');
		}
		if (os_isSelfSystemUser()) {
			return do_local_action($cmd);
		}
	}

	if ($cmd->action === "set" || $cmd->action === 'dowas') {
		$cmdtype = get_class($cmd->robject);
		$robject = $cmd->robject;
		$nname = $robject->nname;
		$dbaction = $robject->dbaction;
	} else {
		if ($cmd->action === 'get') {
			$cmdtype = $cmd->func;
			if (is_array($cmdtype)) {
				$cmdtype = implode("::", $cmdtype);
			}
			$nname = "";
			$dbaction = "";
		} else {
			$cmdtype = null;
			$nname = "";
			$dbaction = "";
		}
	}

	print_time('remote_exec');

	$rmt = do_remote_exec($machine, $cmd, $cmdtype, $nname, $dbaction);
	$class = 'get_action';
	$subaction = "get";

	if ($cmd->action === 'set') {
		$class = $cmd->robject->get__table();
		$subaction = $cmd->robject->subaction;
	}

	print_time('remote_exec', "<b> Remote Exec $machine: type: $cmdtype, class: $class name: $nname action: $dbaction; subaction $subaction </b>");

	if (!$rmt) {
		return null;
	}

	// If the slave server is upgrading try once more. Don't!!!!!!!!!!!!!!
	/*
	 if ($rmt->state === 'upgrade') {
		 print("Slave Server Upgraded. Trying Again....<br> ");
		 flush();
		 sleep(20);
		 $rmt = do_remote_exec($machine, $password, $cmd, $cmdtype, $nname, $dbaction);
	 }

	 if (!$rmt) {
		 return null;
	 }

	 if ($rmt->state === 'upgrade') {
		 dprint("Slave Server Upgrade. Has failed...<br> ");
		 throw new lxException("slave_server_upgrade_failed");
	 }
 */

	if ($rmt->exception) {
		//$exc = new Exception("syncserver:$machine <br> " . $rmt->exception->getMessage());
		$rmt->exception->syncserver = $machine;
		throw $rmt->exception;
		//throw $exc;
	}


	return $rmt->ddata;
}

function get_from_master($variable)
{
	return send_to_master($variable);
}

function send_to_master($object)
{
	global $gbl, $sgbl, $login, $ghtml;

	if ($sgbl->is_this_master()) {
		return master_get_data($object);
	}

	$res = new Remote();
	$res->var = $object;
	$var = base64_encode(serialize($res));
	$var = "__master::$var";
	$rmt = lfile_get_unserialize("__path_slave_db");
	$raddress = $rmt->master_c;
	$out = send_to_some_stream_server("cmd", null, $raddress, $var, null);
	$rmt = unserialize(base64_decode($out));

	return $rmt->ddata;
}


function send_to_some_server($raddress, $var)
{
	return send_to_some_stream_server("cmd", null, $raddress, $var, null);
	//	return send_to_some_http_server($raddress, $socket_type, "7777", $rmt);
}

function send_to_some_stream_server($type, $size, $raddress, $var, $fd)
{
	global $gbl, $sgbl, $login, $ghtml;

	$exitchar = $sgbl->__var_exit_char;
	$remotechar = $sgbl->__var_remote_char;
	$con = $sgbl->__var_connection_type;

	if ($raddress === "localhost") {
		$con = "tcp";
		$port = $sgbl->__var_local_port;
	} else {
		$con = "ssl";
		$port = $sgbl->__var_remote_port;
	}

	print_time('server');

	print_time('serverstart');
	$rraddress = $raddress;

	if (isLocalhost($raddress)) {
		$rraddress = "127.0.0.1";
	}
	$socket = stream_socket_client("$con://$rraddress:$port");

	//	$socket =  fsockopen("$con://$raddress", $port);
	print_time('serverstart', "Fsockopen");

	if ($socket <= 0) {
		if ($raddress === 'localhost' && !WindowsOs() && !$sgbl->isDebug()) {
			//	lxshell_background("/usr/sbin/lxrestart", $sgbl->__var_program_name);
			throw new lxException('no_socket_connect_to_server', '', $raddress);
			throw new lxException('restarting_backend', '', $raddress);
		} else {
			throw new lxException('no_socket_connect_to_server', '', $raddress);
		}
	}

	stream_set_timeout($socket, 30000000000);
	//	stream_context_set_option($socket, 'ssl', 'allow_self_signed', true);
	//	stream_context_set_option($socket, 'ssl', 'verify_peer', false);
	$in = $var;
	fwrite($socket, $in);
	$in = "\n";
	fwrite($socket, $in);
	$in = $exitchar;
	fwrite($socket, $in);
	$in = "\n";
	fwrite($socket, $in);

	$totalout = null;
	$totalsize = 0;

	while (true) {
		$out = fgets($socket, 8092);

		if (!$out) {
			if (!$totalout) {
				dprint("Got Nothing\n");
			}

			break;
		}

		if ($type === 'fileprint' || $type === 'file') {
			// The stream comes with a first and last character appended to it.
			if ($totalsize === 0 && $out[0] === 'f') {
				log_log("servfile", "Got failure from the servfile $out");

				break;
			}

			if ($totalsize === 0) {
				$out = substr($out, 1);
			}

			$totalsize += strlen($out);

			if ($totalsize >= $size + 1) {
				$out = substr($out, 0, strlen($out) - 1);
			}

			print_or_write($fd, $out);

			if ($totalsize >= $size + 1) {
				break;
			}
		} else {
			//$out = trim($out);
			$totalout .= $out;

			if (csa($totalout, $exitchar)) {
				break;
			}
		}
	}
	fclose($socket);

	if ($type === 'file' || $type === 'fileprint') {
		return $totalsize;
	}

	if (!$totalout) {
		return null;
	}

	dprint("Got this much:" . strlen($totalout));

	//	dprint($totalout);

	$totalout = trim($totalout);
	$size = round(strlen($totalout) / 1024, 4);

	//	dprint($totalout);
	//	$ee = unserialize(base64_decode($totalout));

	return $totalout;

}

function some_server_windows()
{
	global $gbl, $sgbl, $login, $ghtml;

	dprint("In Windows Server\n");

	$exitchar = $sgbl->__var_exit_char;
	$con = $sgbl->__var_connection_type;
	// Set the ip and port we will listen on

	// Array that will hold client information
	$clients = Array();

	// Create a TCP Stream socket
	//$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

	$sockr = stream_socket_server("ssl://0.0.0.0:7779");
	$sockl = stream_socket_server("tcp://127.0.0.1:7776");

	if (!$sockr) {
		die("Could not bind Remote address\n");
	}

	if (!$sockl) {
		die("Could not local bind address\n");
	}

	stream_context_set_option($sockr, 'ssl', 'verify_peer', false);
	stream_context_set_option($sockr, 'ssl', 'allow_self_signed', true);
	//stream_context_set_option($sock, 'ssl', 'cafile', "/etc/httpd/conf/ssl.crt/server.crt");
	stream_context_set_option($sockr, 'ssl', 'local_cert', "$sgbl->__path_program_root/file/program.key");
	$client = null;

	// Loop continuously

	$client = null;

	// Loop continuously
	while (true) {
		// Setup clients listen socket for reading
		$read = null;
		$read[0] = $sockr;
		$read[1] = $sockl;
		$writea = null;
		$excpta = null;
		/*
		 foreach((array) $client as $c) {
			 $read[] = $c['sock'];
		 }
	 */
		//	dprint("Before: ");
		//	dprintr($read);

		// Set up a blocking call to stream_select()
		$ready = stream_select($read, $writea, $excpta, 30);

		if ($ready === 0) {
			continue;
		}

		//	dprint("After: $ready");
		//	dprintr($read);

		// This means that sock - which is our main master socket - is ready for reading,
		// which in turn signifies that a NEW connection has arrived. The other members of the read array
		if (in_array($sockl, $read)) {
			//	dprint("Local:");
			//	dprintr($read);
			$c = stream_socket_accept($sockl);
		} else {
			if (in_array($sockr, $read)) {
				//	dprint("Remote:");
				//	dprintr($read);
				$c = stream_socket_accept($sockr);
			}
		}

		// If a client is trying to write - handle it now

		$total = null;

		while (true) {
			$input = fread($c, 8092);

			if ($input == null) {
				//	fclose($c);

				break;
			}

			//	dprintr($input . "\n");

			$total .= $input;

			if (csa($total, $exitchar)) {
				// requested disconnect
				//	dprint("got total $total\n");

				$out = process_server_input($total);
				fwrite($c, $out);
				fwrite($c, "\n");
				fwrite($c, $exitchar);
				fwrite($c, "\n");

				// Server shouldn't close the socket. It is the client's job to do so.
				//	fclose($c);

				break;
			}
		}
	} // end while

	// Close the master sockets
	fclose($sock);
}


function createSslStream()
{
	global $gbl, $sgbl, $login, $ghtml;

	$sockr = stream_socket_server("ssl://0.0.0.0:{$sgbl->__var_remote_port}");

	stream_context_set_option($sockr, 'ssl', 'verify_peer', false);
	//	stream_set_timeout($sockr, 30000000);
	stream_context_set_option($sockr, 'ssl', 'allow_self_signed', true);
	//	stream_context_set_option($sock, 'ssl', 'cafile', "/etc/httpd/conf/ssl.crt/server.crt");
	stream_context_set_option($sockr, 'ssl', 'local_cert', "$sgbl->__path_program_root/file/internal_program.key");

	if (!$sockr) {
		die("Could not bind Remote address\n");
	}

	return $sockr;
}

function createLocalStream()
{
	global $gbl, $sgbl, $login, $ghtml;
	$sockl = stream_socket_server("tcp://127.0.0.1:{$sgbl->__var_local_port}");

	//	stream_set_timeout($sockl, 30000000);

	if (!$sockl) {
		die("Could not local bind address\n");
	}

	return $sockl;
}

function do_ssl()
{
	global $gbl, $sgbl, $login, $ghtml;

	$uid = posix_getpwnam("lxlabs");

	if (!$uid) {
		print("No lxlabs User... Cannot run Slave...\n");

		exit;
	}

	posix_setuid($uid['uid']);
	$sockr = createSslStream();

	do_socket(array($sockr), "process_nonlocal_input");
}

function do_local()
{
	global $gbl, $sgbl, $login, $ghtml;

	$sockl = createLocalStream();
	do_socket(array($sockl), "process_server_input");
}

function some_server()
{
	/*
	 $pid = myPcntl_fork();

	 if (!$pid) {
		 do_ssl();
	 } else {
		 do_local();
	 }
 */
	$sockr = createSslStream();
	$sockl = createLocalStream();

	do_socket(array($sockr, $sockl), "process_server_input");
}

function do_socket($socklist, $processfunc)
{
	global $gbl, $sgbl, $login, $ghtml;

	$client = null;
	$i = 0;

	while (true) {
		$read = null;

		foreach ($socklist as $sock) {
			$read[] = $sock;
		}

		$writea = null;
		$excpta = null;

		// Set up a blocking call to stream_select()
		$ready = stream_select($read, $writea, $excpta, 30);

		// Server stuff must be executed not merely when it is timed out. But always.
		if (os_isSelfSystemUser()) {
			do_server_stuff();
		}
		if ($ready === 0) {
			myPcntl_reaper();

			continue;
		}
		myPcntl_reaper();

		// This means that sock - which is our main master socket -
		// is ready for reading, which in turn signifies that a NEW connection has arrived.
		// The other members of the read array
		foreach ($socklist as $sock) {
			if (in_array($sock, $read)) {
				//	dprintr($sock);
				$pid = pcntl_fork();

				if ($pid === 0) {
					$childsock = stream_socket_accept($sock);
					if (!$childsock) {
						exit;
					}
					stream_set_timeout($childsock, 30000000);
					foreach ($socklist as $msock) {
						fclose($msock);
					}
					process_single_command($childsock, $processfunc);

					exit;
				} else {
					usleep(100 * 1000);
					//	fclose($c);
					// Parent...
				}
			}
		}
	} // end while

	// Close the master sockets
	fclose($sock);
}

function process_single_command($mchildsock, $processfunc)
{
	global $gbl, $sgbl, $login, $ghtml;

	$exitchar = $sgbl->__var_exit_char;

	$total = null;

	while (true) {
		$input = fgets($mchildsock, 10);

		if ($input == null) {
			//	fclose($mchildsock);

			break;
		}

		//	dprintr($input . "\n");
		$total .= $input;

		if (csa($total, $exitchar)) {
			if (csa($total, "__file::")) {
				$ret = file_server($mchildsock, $total);
			} else {
				if (csa($total, "__master::")) {
					$out = process_in_master($total);
					fwrite($mchildsock, $out);
					fwrite($mchildsock, "\n");
					fwrite($mchildsock, $exitchar);
					fwrite($mchildsock, "\n");
					$ret = true;
				} else {
					$out = $processfunc($total);
					fwrite($mchildsock, $out);
					fwrite($mchildsock, "\n");
					fwrite($mchildsock, $exitchar);
					fwrite($mchildsock, "\n");
					$ret = true;
				}
			}
			// dummy read but only if there was something written to the socket.
			if ($ret) {
				stream_set_timeout($mchildsock, 15);
				fgets($mchildsock, 200);
			}

			//	fclose($mchildsock);

			break;
		}
	}
}

function process_nonlocal_input($total)
{
	return send_to_some_server("localhost", $total);
}

function save_slave_name($o)
{
	if (!lxfile_exists("__path_slave_db")) {
		return;
	}

	$rmt = lfile_get_unserialize("__path_slave_db");

	if (($o->machine && $rmt->myname !== $o->machine) || ($o->master_c && $rmt->master_c !== $o->master_c)) {
		dprint("Saving slave info..\n");
		$rmt->myname = $o->machine;
		$rmt->master_c = $o->master_c;
		lfile_put_serialize("__path_slave_db", $rmt);
	}
}

function process_in_master($total)
{
	global $gbl, $sgbl, $login, $ghtml;

	$remotechar = $sgbl->__var_remote_char;

	$total = strfrom($total, "__master::");
	$rmt = unserialize(base64_decode($total));
	$res = new Remote();
	$res->message = "Master info";
	$res->exception = null;
	$vv = master_get_data($rmt->var);
	$res->ddata = $vv;
	$out = base64_encode(serialize($res));

	return $out;

}

function master_get_data($rmt)
{
	dprint("Got master request for {$rmt->cmd}\n");

	if ($rmt->cmd === 'sendemail') {
		callInChild("send_mail_to_admin", array($rmt->subject, $rmt->message));
	}
}

function do_master_get_data($var)
{
	switch ($var) {
		case "contactemail":
			$sq = new Sqlite(null, 'client', true);
			$vv = $sq->getRowsWhere("nname = 'admin'", array("contactemail"));
			return $vv[0]['contactemail'];
	}

	return null;
}

function process_server_input($total)
{
	global $gbl, $sgbl, $login, $ghtml;

	$remotechar = $sgbl->__var_remote_char;


	if (csb($total, $remotechar)) {
		$list = explode("\n", $total);
		$remoteuser = base64_decode($list[1]);
		$remotepass = base64_decode($list[2]);
		//	dprintr("Remote Password: $remotepass\n");

		reload_slave_password();

		if (check_remote_pass($remoteuser, $remotepass)) {
			$rmt = unserialize(base64_decode($list[3]));
			$rmt->authenticated = true;
			save_slave_name($rmt);
		} else {
			$res = new Remote();
			$res->message = "Remote Authentication failed";
			$res->exception = new lxException("remote_authentication_failed", '');
			$res->ddata = null;
			$out = base64_encode(serialize($res));
			return $out;
		}
	} else {
		$rmt = unserialize(base64_decode($total));
		$rmt->authenticated = false;
	}

	if ($rmt) {
		$res = do_remote($rmt);
	} else {
		$res = new Remote();
		$res->message = "Got Error From Your End";
		$res->exception = new lxException("got_error_from_ur_end", $rmt->machine);
		$res->ddata = null;
	}

	$res->__this_warning = null;

	if ($gbl->__this_warning) {
		$res->__this_warning = $gbl->__this_warning;
	}

	$out = base64_encode(serialize($res));

	return $out;
}


function do_local_action($rmt)
{
	if ($rmt->action === "set") {
		$object = $rmt->robject;
		return $object->doSyncToSystem();
	} else {
		if ($rmt->action === "get") {
			// workaround for the following php bug:
			//   http://bugs.php.net/bug.php?id=47948
			//   http://bugs.php.net/bug.php?id=51329
			if (is_array($rmt->func) && count($rmt->func) > 0) {
				$class = $rmt->func[0];
				class_exists($class);
			}

			return call_user_func_array($rmt->func, $rmt->arglist);
		}
	}
}

function rl_exec_get($masterserver, $slaveserver, $func, $arglist = null)
{
	global $gbl, $sgbl, $login, $ghtml;

	$rmt = new Remote();
	$rmt->action = 'get';
	$rmt->func = $func;
	$rmt->arglist = $arglist;

	return rl_exec($masterserver, $slaveserver, $rmt);
}

function rl_exec_set($masterserver, $slaveserver, $object)
{
	$rmt = new Remote();
	$rmt->action = 'set';
	$rmt->robject = $object;

	return rl_exec($masterserver, $slaveserver, $rmt);
}

// CLone is fucking buggy. SO now just serializing and unserializing the object.

function myclone($object)
{
	return clone $object;

	if (!is_subclass_of($object, "lxclass")) {
		return clone $object;
	}
	$class = $object->get__table();

	$newobject = new $class($object->__masterserver, $object->__readserver, $object->nname);

	foreach ($object as $k => $v) {
		/*
		 if (is_object($v)) {
			 continue;
		 }
	 */
		$newobject->$k = $v;
	}

	return $newobject;
}

/**
 * @return void
 * @param
 * @param
 * @desc Remote or local exec. Either exectues it locally or calles remote exec depending on whether $syncserver is localhost or not.
 */
function rl_exec($masterserver, $slaveserver, $cmd)
{

	global $gbl, $sgbl, $login, $ghtml;

	// Convert to driverapp here. Only here do we have the full information (masterserver/syncserver) to
	// to get the syntosystem class properly.

	if ($cmd->action === "set" || $cmd->action === 'dowas') {
		$robject = $cmd->robject;
		$clo = myclone($robject);
		//dprint("Just before $robject {$robject->nname} " . $robject->domain_l . "<br> ");
		lxclass::clearChildrenAndParent($clo);
		$clo->syncserver = $slaveserver;
		$clo->createSyncClass();
		//dprint("Just after $robject {$robject->nname} " . $robject->domain_l . "<br> ");
		$cmd->robject = $clo;
	}

	if (!$masterserver || $masterserver === "localhost") {
		$cmd->slaveserver = null;

		if (!isset($gbl->pserver_password) && isset($cmd->slave_password)) {
			$gbl->pserver_password = $cmd->slave_password;
		}

		$result = remote_exec($slaveserver, $cmd);
	} else {
		$cmd->slaveserver = $slaveserver;
		$result = remote_exec($masterserver, $cmd);
	}

	return $result;
}

function reload_slave_password()
{
	global $gbl, $sgbl, $login, $ghtml;

	static $time;

	if (!lxfile_exists('__path_slave_db')) {
		return;
	}

	$stat = llstat("__path_slave_db");
	$cur = $stat['mtime'];

	if ($cur > $time) {
		$rmt = unserialize(lfile_get_contents("__path_slave_db"));
		$login->password = $rmt->password;
		$time = $cur;
	}

}

function remote_main()
{
	global $gbl, $sgbl, $login, $ghtml, $g_dbf;

	global $argv;

	ob_start();
	$args = parse_opt($argv);

	$gbl->is_master = false;
	$gbl->is_slave = false;

	if (isset($args['install-type']) && $args['install-type'] === 'master') {
		$login = new Client(null, null, 'master');
		$gbl->is_master = true;
		$login->get();
	} else {
		$login = new Client(null, null, 'slave');
		//$login->initThisDef();
		$gbl->is_slave = true;
		$rmt = unserialize(lfile_get_contents("__path_slave_db"));
		$login->password = $rmt->password;
	}

	$login->cttype = 'admin';

	// This is to prevent the socket already used error. If use a strict single interface, the socket operations happen through our own functions, and we can set the reuse option.

	$rmt = unserialize(base64_decode($ghtml->frm_rmt));

	$res = do_remote($rmt);
	print_time('full', 'timing');

	$res->message = ob_get_contents();
	$val = base64_encode(serialize($res));

	while (@ob_end_clean()) {
		;
	}

	print($val);

	exit;

}

