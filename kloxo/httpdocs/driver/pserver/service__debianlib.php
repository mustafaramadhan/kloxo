<?php 

include_once "driver/pserver/service__linuxlib.php";

class Service__Debian extends lxDriverClass {


	/// We need to properly port this system to debian. I tried using the chkconfig directly on debian, but it seems the individual scripts themselves have to support chkconfig if it has to work, and thus chkconfig fails to run. Now the only way is to use update-rc.d program on debain.

function dbactionAdd()
{
	if (command_exists('systemctl')) {
		lxshell_return("systemctl", "enable", $this->main->servicename);
	} else {
		lxshell_return("update-rc.d", $this->main->servicename, 'defaults');
	}
}


function dbactionUpdate($subaction)
{
	switch($subaction) 
	{

		case "toggle_status":
			{

				if ($this->main->isOn('status')) {
					if (command_exists('systemctl')) {
						lxshell_return("systemctl", "enable", $this->main->servicename);
					} elseif (command_exists('update-rc.d')) {
						lxshell_return("update-rc.d", $this->main->servicename, 'defaults');
					} else {
						lxshell_return("chkconfig", $this->main->servicename, 'on');
					}
				} else {
					if (command_exists('systemctl')) {
						lxshell_return("systemctl", "disable", $this->main->servicename);
					} elseif (command_exists('update-rc.d')) {
						lxshell_return("update-rc.d", "-f", $this->main->servicename, 'remove');
					} else {
						lxshell_return("chkconfig", $this->main->servicename, 'off');
					}
				}

				break;
			}

		case "toggle_state":
			{
				$action = ($this->main->isOn('state')) ? "start" : "stop";
				if (command_exists('systemctl')) {
					lxshell_return("systemctl", $action, $this->main->servicename);
				} else {
					lxshell_return("service", "{$this->main->servicename}", $action);
				}
				break;
			}
	}

}


static function getServiceList()
{
	return Service__Linux::getServiceList();
}


static function checkService($name)
{
	return Service__Linux::checkService($name);
}

}
