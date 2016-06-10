<?php 

$conf['path']['apache_path'] = '/etc/httpd/conf/';
$conf['path']['named_chroot'] = "";
// If you have a chroot, the named path should be relative to the chroot directory.
$conf['path']['named_path'] = "/var/named";
$conf['path']['named_conf'] = '/etc/kloxo.named.conf';
$conf['var_driver']['ipaddress'] = 'redhat';
$conf['var_driver']['service'] = 'redhat';
$conf['var_drivername']['web'] = 'httpd';
$conf['var_drivername']['dns'] = 'named';
$conf['var_drivername']['mysql'] = 'mysqld';
$conf['var_driveruser']['dns'] = 'named';

