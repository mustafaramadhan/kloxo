<?php 
include_once "htmllib/lib/include.php"; 
exit_if_secondary_master();

// MR -- disable this process from kloxo start becuase make /home/<webserver>/conf/domains removed!
// lxshell_return("__path_php_path", "../bin/misc/newInstallFixIpaddress.php");

$flg = "__path_program_start_vps_flag";
if (lxfile_exists($flg)) { exit; }
dprint("Executing fix IPADDRESS\n");
lxshell_return("__path_php_path", "../bin/update.php");


