<?php 

class Ipaddress__Redhat extends LxDriverclass { 

function OldUpdate()
{
	if ($action === 'update') {
		$res = self::getcontentsof($ipaddrfile);

		$fdata = null;
		$fdata .= "DEVICE=". $actualname . "\n";

		if ($this->main->isOn('status')) {
			$status = "yes";
		} else {
			$status = "no";
		}

		$fdata .= "ONBOOT=$status\n";

		if($this->main->bproto !=null ) {
			$fdata .= "BOOTPROTO=" . $this->main->bproto . "\n";
		} else  if($res['BOOTPROTO'] != "" ) {
			$fdata .= "BOOTPROTO=" . $res['bootproto'] . "\n"; 
		} else {
			$fdata .= "BOOTPROTO=" . "static" . "\n"; 
		}

		$fdata .= "IPADDR=" . $this->main->ipaddr . "\n";

		$fdata .= "NETMASK=" . $this->main->netmask . "\n";

		$fdata .= "NETWORK=" . $networkaddress . "\n";

		if ($this->main->gateway) {
			$fdata .= "GATEWAY=" . $this->main->gateway . "\n";
		}

		if($this->main->userctl != null) {
			$fdata .= "USERCTL=" . $this->main->userctl . "\n";
		} else  if($res['userctl'] != "" ) {

			$fdata .= "USERCTL=" . $res['userctl'] . "\n";
		}

		if($this->main->peerdns !=null ) {
			$fdata .= "PEERDNS=" . $this->main->peerdns . "\n";
		} else  if($res['peerdns'] != "" ) {
			$fdata .= "PEERDNS=" . $res['peerdns'] . "\n";
		}

		if($this->main->itype !=null ){ 
			$fdata .= "TYPE=" . $this->main->itype . "\n";
		} else  if($res['itype'] != "" ){ 
			$fdata .= "TYPE=" . $res['itype']  . "\n";
		}

		if($this->main->ipv6init !=null ){
			$fdata .= "IPV6INIT=" . $this->main->ipv6init . "\n";
		} else  if($res['ipv6init'] != null ){
			$fdata .= "IPV6INIT=" . $res['ipv6init'] . "\n";
		}

		lfile_put_contents($ipaddrfile, "$fdata");

		lxshell_return("ifdown", $actualname);
		if ($this->main->status === "on") {
			lxshell_return("ifup", $actualname);
		} 
	}
}



function IpaddressEdit($action)
{
	global $gbl, $sgbl, $login;


	$this->checkForEthBase();

	if ($sgbl->dbg > 1 && $this->main->devname === 'eth0') {
		return 1;
	}


	$ipaddr=$this->main->ipaddr;
	$netmask=$this->main->netmask;
	$temp_ipaddr=explode(".", $ipaddr);
	$temp_netmask=explode(".",$netmask);
	$i=0;
	foreach($temp_ipaddr as $row)  { 
		$ipaddr_binary[$i]=str_pad(base_convert($row,10,2),8,'0',STR_PAD_LEFT);
		$i++;
	}
	$i=0;

	foreach($temp_netmask as $row) {
		$netmask_binary[$i]=str_pad(base_convert($row,10,2),8,'0',STR_PAD_LEFT);
		$networkip[$i]=($netmask_binary[$i] & $ipaddr_binary[$i]);
		$converted[$i]=base_convert($networkip[$i],2,10);
		$i++;
	}

	$networkaddress=implode(".",$converted);
	$dev  = explode("-" , $this->main->devname);

	if(count($dev) >= 2) {
		$actualname  = implode( ":", $dev);
	} else  {
		$actualname = $this->main->devname;
	}

	$ipaddrfile = "$sgbl->__path_real_etc_root/sysconfig/network-scripts/ifcfg-". $actualname;


	$fdata = null;

	$fdata .= "DEVICE=". $actualname . "\n";

	$status = "yes";

	$fdata .= "ONBOOT=$status \n";

	if(isset($this->main->bproto)) {
		$fdata .= "BOOTPROTO=" . $this->main->bproto . "\n";
	} else 
		$fdata .= "BOOTPROTO=" . "static". "\n"; 

	$fdata .= "IPADDR=" . $this->main->ipaddr . "\n";
	$fdata .= "NETMASK=" . $this->main->netmask . "\n";
	$fdata .= "NETWORK=" . $networkaddress . "\n";
	$fdata .= "GATEWAY=" . $this->main->gateway . "\n";

	if(isset($this->main->userctl)) {
		$fdata .= "USERCTL=" . $this->main->userctl . "\n";
	} 
	if(isset($this->main->peerdns)) {
		$fdata .= "PEERDNS=" . $this->main->peerdns . "\n";
	}
	if(isset($this->main->itype)){ 
		$fdata .= "TYPE=" . $this->main->itype . "\n";
	}
	if(isset($this->main->ipv6init)){
		$fdata .= "IPV6INIT=" . $this->main->ipv6init . "\n";
	}

	lfile_put_contents($ipaddrfile, "$fdata");

	ipaddress::copyCertificate($this->main->devname, $this->main->getParentName());

	lxshell_return("ifdown", $actualname);
	lxshell_return("ifup", $actualname);
}

function checkForEthBase()
{
	if (ipaddress::checkIfBaseAddress($this->main->devname)) {
		throw new lxException("modifying_eth0_eth1_not_permitted", '');
		return;
	}
}

function dbactionAdd()
{
	$this->IpaddressEdit('add');
	createRestartFile($this->main->__var_dnsdriver);
	$result = self::getCurrentIps();
//	web__apache::createWebmailConfig($result);
//	web__apache::createWebDefaultConfig($result);

	exec("sh /script/fixweb --target=defaults --nolog");
}

function dbactionUpdate($subaction)
{
	throw new lxException("modifying_not_permitted", '');
}

function dbactionDelete()
{
	global $gbl, $sgbl, $login, $ghtml; 

	$this->checkForEthBase();
	$dev  = explode("-" , $this->main->devname);
	if(count($dev) >= 2) {
		$actualname  = implode(":", $dev);
	} else  {
		$actualname = $this->main->devname;
	}


	$ipaddrfile = "$sgbl->__path_real_etc_root/sysconfig/network-scripts/ifcfg-". $actualname;
	lxshell_return("ifdown", $actualname);
	lxfile_rm($ipaddrfile); 
	createRestartFile($this->main->__var_dnsdriver);
}

static function getCurrentIps()
{

	global $gbl, $sgbl, $login, $ghtml; 
 
	$path= $sgbl->__path_real_etc_root . "sysconfig/network-scripts/";

	$flist = lscandir($path);
	foreach($flist as $file) {
		if (char_search_a($file, "ifcfg-")) {
			$result1[] = self::getcontentsof($path .  $file);
		}
	}

	$result = array(); // Initialize as array (expected return result)

	foreach($result1 as $res) {

		$temp = explode(":" ,$res['devname']);

		if(count($temp) ===  2 ) { 
			$res['devname'] = implode("-" , $temp);
		} 
		$result[] = $res;
	
	}

	//dprintr($result);
	return($result);
}  

static function listSystemIps($machinename)
{
	global $gbl, $sgbl, $login, $ghtml; 
	$result = self::getCurrentIps();
	
//	web__apache::createWebmailConfig($result);
//	web__apache::createWebDefaultConfig($result);

	exec("sh /script/fixweb --target=defaults --nolog");
	
	$res =  ipaddress::fixstatus($result);
	foreach($res as $r) {
		if ($sgbl->isKloxo()) {
			ipaddress::copyCertificate($r['devname'], $machinename);
		}
	}
	return $res;
}


static function getcontentsof($file)
{

	$fileName ="";
	$fileName = explode('-', basename($file));

	//dprint("$file");
	$contents = lfile($file);
	//dprint_r($contents);
	$result1 = Array();
	
	$i=0;   
	foreach($contents as $row) {
		if (!csa($row, "=")) {
			continue;
		}
		$value = explode("=", trim($row));

		$value[1] = trim($value[1], "\"");
		$value[1] = trim($value[1], "'");

		switch($value[0])
		{   
			case "DEVICE":
				{
					$result['devname'] = $value[1];
					break;
				}
		    case "IPADDR":
				{
					$result['ipaddr']=$value[1];
					break;

		       }
			case "NETMASK":
				{
					$result['netmask']=$value[1];
					break;
				}

			case "ONBOOT":
				{
					$result['status']=$value[1];
					break;
				}

			case "GATEWAY":
				{
					$result['gateway'] =$value[1];
					break;

				}

			case "USERCTL":
				{ 
					$result['userctl'] =$value[1];
					break;
				}

			case "PEERDNS":
				{
					$result['peerdns'] =$value[1];
					break;
				}

			case "TYPE":
				{
					$result['itype'] =$value[1];
					break;
				}
			case "IPV6INIT":
				{
					$result['ipv6init'] =$value[1];
					break;

				}

			case "BOOTPROTO":
				{  
					$result['bproto'] =$value[1];
					break;
				}
		}  
	} 


	if(!isset($result['devname'])) { 
		$result['devname']  = $fileName[1];
    }

	if(!isset($result['status'])) {
		$result['status'] = "yes";
	}

	if(!isset($result['gateway'])) 
		$result['gateway'] = null;

	if(!isset($result['userctl'])) 
		$result['userctl'] =null;

	if(!isset($result['netmask'])) 
		$result['netmask'] = null;

	if(!isset($result['peerdns'])) 
		$result['peerdns'] =null;

	if(!isset($result['itype'] )) 
		$result['itype'] = null ;

	if(!isset($result['ipv6init'])) 
		$result['ipv6init'] = null;

	if(!isset($result['bproto']))
		$result['bproto'] = null;

	return($result);
}   

}

