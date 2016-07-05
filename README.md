![Kloxo-MR logo](https://github.com/mustafaramadhan/kloxo/blob/dev/kloxo-mr_big.png)

### Kloxo fork by Mustafa Ramadhan


===================

Branch:
- master - original code from lxcenter
- dev - branch for next release
- bugfix-6.5.0 - branch for bugfix for 6.5.0.f
- release - branch for release in zip format
- rpms - branch for rpms

===================

# Kloxo-MR

This is special edition (fork) of Kloxo with many features not existing on Kloxo official release (6.1.12+).

This fork named as Kloxo-MR (meaning 'Kloxo fork by Mustafa Ramadhan').

### URL

1. More information about Kloxo (Official from LxCenter) - http://lxcenter.org/ and http://forum.lxcenter.org/

2. More information about Kloxo-MR - http://mratwork.com/ and http://forum.mratwork.com/

### Kloxo-MR Features

* OS: Redhat/CentOS 5 and 6 (32bit and 64bit) or their variants
* Billing: AWBS, WHMCS, HostBill, TheHostingTool, AccountLab Plus, Blesta and BoxBilling (note: claim by billing's author)
* Web server: Nginx, Nginx-Proxy and Lighttpd-proxy, Hiawatha, Hiawatha-proxy and Httpd 24, beside Httpd and Lighttpd; also Dual and Multiple Web server *)
* Webcache server: Squid, Varnish, Hiawatha and ATS *)
* Php: Dual-php with php 5.3/5.4 as primary and php 5.2 as secondary; multiple-php *)
* PHP-type for Apache: php-fpm_worker/_event and fcgid_worker/_event; beside mod_php/_ruid2/_itk and suphp/_worker/_event
* Mail server: qmail-toaster instead special qmail (in progress: change from courier-imap to dovecot as imap/pop3) *)
* Database: MySQL or MariaDB
* Database Manager: PHPMyAdmin; Adminer, MyWebSql and SqlBuddy as additional **)
* Webmail: Afterlogic Webmail Lite, Telaen, Squirrelmail, Roundcube and Rainloop; Horde and T-Dah dropped
* FTP server: Pure-ftpd
* DNS Server: Bind and Djbdns; add Powerdns, ~~MaraDNS~~, NSD, myDNS and Yadifa *)
* Addons: ClamAV, Spamassassin/Bogofilter/Spamdyke, RKHunter and MalDetect
* Free SSL: Let's Encrypt (via letsencrypt/certbot-auto and acme.sh) and StartAPI *)
* Fixed many bugs of Kloxo Official (including security issues)
* And many more!

### Contributing

* Always invite for devs and testers. Go to http://mratwork.com/ and http://forum.mratwork.com/

### Licensing - AGPLv3

* Like Kloxo Official, Kloxo-MR adopt AGPLv3 too.

### How to install

* Read https://github.com/mustafaramadhan/kloxo/blob/release/how-to-install.txt


### Notes
*) New features in Kloxo-MR 7.0.0 (aka Kloxo-MR 7)

- Version 6.5.1 change to 7.0.0 since 20 Aug 2014 (beta step)
- Web server: Hiawatha (since 28 Sep 2013) and Httpd 2.4 (since 20 Jun 2015); Dual (since 19 Jan 2016) and Multiple Web server (in progress)
- Webcache server: Squid, Varnish and ATS (Apache Traffic Server) (since 3 Oct 2013)
- DNS server: Powerdns, NSD, MyDNS and Yadifa (since 16 Sep 2013)
- Mail server: Dovecot (since 19 Jun 2016)
- Php: multiple Php versions
  * suphp base since 27 Jun 2014
  * fcgid base since 5 Jul 2015
  * php-fpm/spawning base since 24 May 2016
- Free SSL:
  * Let's Encrypt (since 4 May 2016)
  * StartAPI (since 29 Jun 2016)

**) New features in Kloxo-MR 6.5.0 after released
- Panel: Adminer, MyWebSql and SqlBuddy as alternative for Database management
- Core: change to use Hiawatha + php52s from lxphp + lxlighttpd for running panel

