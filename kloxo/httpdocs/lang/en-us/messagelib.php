<?php

/*
	REMARK:
	- [%_server_%] - translate to current server (example: localhost)
	- [%_loginas_%] - translate to current user login (example: admin)
	- [%_client_%] - translate to current client of user login (example: tester)
	- [%_mailaccount_%] - translate to current user login (example: admin@domain.com)
	- [%_program_%] - translate kloxo
	- [%_programname_%] - translate Kloxo-MR
	- [%_domain_%] - translate to current domain (example: domain.com)
*/

$__emessage['blocked'] = "Your address is blocked";
$__emessage['no_server'] = "Could not connect to the Server.";
$__emessage['set_emailid'] = "Please Set Your EmailId Properly ";
$__emessage['no_socket_connect_to_server'] = "Could not Connect to the server [%s]. This is most likely due to underlying network problem. Make sure that the server is accessible from this particular node by running <b>telnet slave-id 7779</b>   ";
$__emessage['restarting_backend'] = "Restarting the backend. Please try again after 30 seconds.";
$__emessage['quota_exceeded'] = "Quota Exceeded for [%s]";
$__emessage['license_no_ipaddress'] = "The public ipaddress [%s] of this server was not found in in the license repository. Please contact Lxlabs sales or your reseller to create a license for this server.";
$__emessage['ssh_root_password_access'] = "You have not disabled password based access to root on this server. Password based access to root is not necessary since you can manage your ssh authorized keys via Kloxo-MR itself. Click <url:k[class]=pserver&k[nname]=[%_server_%]&a=updateform&sa=update&o=sshconfig>[here]</url> to configure your ssh server.";
$__emessage['already_exists'] = "The resource of name [%s] already exists.";
$__emessage['lxguard_not_configured'] = "Lxguard for this server is not configured. Click <url:k[class]=pserver&k[nname]=[%_server_%]&a=show&o=lxguard>[here]</url> to configure Lxguard since it is very important that you understand what it does. Lxguard is critical for the security of your server, at the same time, it can block your own IPaddress from accessing the server, which can be frustrating if you don't know what's happening.";
$__emessage['root_cannot_extract_to_existing_dir'] = "Directory you provided already exists. Root user cannot extract archive into an existing directory. Please provide the name of a directory that doesn't exist in the system.";
$__emessage['no_imagemagick'] = "There is no imagemagick in the system. You can install imagemagick by running <b>yum -y install imagemagick</b>.";
$__emessage['warn_license_limit'] = "You are very close to your license limit for [%s]. If the system goes over limit for [%s] the interface will stop working and you won't be able to manage your system. Please increase your license at client.lxlabs.com and update by click <url:o=license&a=show>[here]</url>.";
$__emessage['file_already_exists'] = "The file [%s] already exists.";
$__emessage['contact_set_but_not_correct'] = "Your Contact Information doesn't appear to be a valid email address. Click <url:a=updateform&sa=information>[here]</url> to fix it.";
$__emessage['contact_not_set'] = "Your Contact Information is not set properly. Click <url:a=updateform&sa=information>[here]</url> to fix it.";
$__emessage['you_have_unread_message'] = "You have [%s] Unread Message(s). click <url:a=list&c=smessage>[here]</url> to read it.";
$__emessage['you_have_unread_ticket'] = "You have [%s] Unread Ticket(s). click <url:a=list&c=ticket>[here]</url> to read it.";
$__emessage['security_warning'] = "Your password is now set as a generic password which constitutes a grave security threat. Please change it immediately by click <url:a=updateform&sa=password>[here]</url>.";
$__emessage['ssh_port_not_configured'] = "The ssh port for this server is not set. It is a good idea to change it to something other than the default 22. Please click <url:a=show&o=sshconfig>[here]</url> to change. If you want to keep the ssh port to 22 and still avoid this message, go to the page, and set it forcibly to 22, and the warning will not be displayed again.";
$__emessage['system_is_updating_itself'] = "The system at this point is upgrading itself, and thus you won't be able to make any changes for a few minutes. All read actions work normally though.";
$__emessage['system_is_locked'] = "Someone has initiated system-modification-action on this particular object which is still going on. You wont be able to make any changes till it is finished. All read actions work normally though.";
$__emessage['system_is_locked_by_u'] = "You have initiated a system-modification-action which is still going on. You wont be able to make any changes till it is finished. All read actions work normally though.";
$__emessage['smtp_server_not_running'] = "Kloxo-MR could not connect to an smtp server on this server. That means that Kloxo-MR will not able to send out any mails. This is very critical since Kloxo-MR monitors the health of the entire cluster and sends email to the admin if there is any problem. You should make sure that the smtp service is running on this server. Once you restart the SMTP service, please wait 5 minutes for this error message to disappear, since Kloxo-MR checks for the service availability only once every 5 minutes.";
$__emessage['template_not_owner'] = "You are not the Owner of this Template";
$__emessage['ipaddress_changed_amidst_session'] = "IP Address Changed Amidst Session. Possible Session Hijacking.";
$__emessage['more_than_one_user'] = "More than one user is logged in this account. Click <url:a=list&c=ssession>[here]</url> to see list of logins. ";
$__emessage['login_error'] = "Login Unsuccessful";
$__emessage['file_exists'] = "file(s) [%s] Exists. Not Pasting...";
$__emessage['cannot_unzip_in_root'] = "You cannot unzip files into the root. Please specify a directory and unzip into that.";
$__emessage['nouser_email'] = "The Email doesn't match User's Contact Email Address";
$__emessage['session_expired'] = "Session Expired";
$__emessage['e_password'] = "Password Incorrect";
$__emessage['is_demo'] = "[%s] is Disabled in Demo Version.";
$__emessage['user_create'] = "User [%s] Could not be Created. Please try a different Name";
$__emessage['switch_done'] = "Switching the Servers has been run in the background. You will be sent a mail when the switch is complete.";
$__emessage['mis_changed'] = "Display Configuration successfully changed.";
$__emessage['password_sent'] = "Password was reset and sent successfully.";
$__emessage['added_successfully'] = "[%s] was added successfully.";
$__emessage['backup_has_been_scheduled'] = "The backup is now happening in the background. You will receive a mail at your contact email when it is done.";
$__emessage['update_scheduled'] = "Update is now running in the background. You can refresh this page to see if the update has completed properly.";
$__emessage['restore_has_been_scheduled'] = "The Restore is now happening in the background. You will receive a mail at your contact email when it is done.";
$__emessage['same_dns'] = "Master And Slave Cannot Be on the same Server.";
$__emessage['user_exists'] = "User [%s] already exists.";
$__emessage['mysql_error'] = "Mysql Error, Database Said: [%s]";
$__emessage['this_domain_does_not_resolve_to_this_ip'] = "To map an IP to a domain, the domain must ping to the same IP, otherwise, the domain will stop working. The domain you are trying to map this IP to, doesn't resolve back to the IP, and so it cannot be set as the default domain for the IP.";
$__emessage['dns_conflict'] = "The domain was not added due to an error in the dns settings. Please check your dns template and verify. The message from the dns server was [%s]";
$__emessage['add_without_www'] = "You should add only the main domain in the form of domain.com. The <b>www</b> subdomain will be automatically added to it. You shouldn't add <b>www</b> when creating a domain.";
$__emessage['could_not_connect_to_db'] = "Could Not Connect to Database: The error has been logged. Please contact the administrator.";
$__emessage['e_no_dbadmin_entries'] = "There are no Database administrator entries configured for this particular server. Please contact your admin to set them.";
$__emessage['please_add_one_domain_for_owner_mode'] = "You will need to have at least one domain if you want to switch to domain owner mode. You can add a domain by click <url:a=addform&c=domain>[here]</url>.";
$__emessage['e_no_dbadmin_entries_admin'] = "There are no Database administrator entries configured for this particular server. You have to go to the server section for this server, and click on the Dbadmin link, and add the database admin user and password for this particular machine and the type of database.";
$__emessage['mail_server_name_not_set'] = "The identification name for your mail server is not set. This means that many public mail servers like hotmail will reject mails from your server. Click <url:k[class]=pserver&k[nname]=[%_server_%]&a=updateform&sa=update&o=servermail>[here]</url> to set it.";
$__emessage['dns_template_inconsistency'] = "The Dns Template You have chosen is not consistent with your choice of the servers. For instance, it could be that the ipaddress in the dns is not at all found in the webserver. Please go back and create a dns template that has the parameters consistent with server setup.";
$__emessage['adding_cron_failed'] = "Adding crontab has failed due to [%s]. Please delete this cron and add it again.";
$__emessage['se_submit_running_background'] = "Search Engine Submission is running in the background. You will be sent a message to your contact email when it is done.";
$__emessage['err_no_dns_template'] = "There are no Dns Templates in the System. You have to have at least one Dns Template to add a domain/client. Click <url:a=list&c=dnstemplate>[here]</url> to add a dnstemplate.";
$__emessage['certificate_key_file_empty'] = "The certificate and the Key file you have chosen are empty. You have to first create or upload them before enabling ssl";
$__emessage['document_root_may_not_contain_spaces'] = "The document root may not contain any space at the end or before the slash. Please check and submit again.";

$__emessage['switch_program_not_set'] = "Need select services. Click <url:k[class]=pserver&k[nname]=[%_server_%]&a=updateform&sa=switchprogram>[here]</url> to set it.";
$__emessage['phptype_not_set'] = "Need select php-type. Click <url:k[class]=pserver&k[nname]=[%_server_%]&o=serverweb&a=show>[here]</url> to set it.";

$__emessage['phpini_not_set_pserver'] = "Need update php.ini to make sure the website able to process php files. Click <a href='/display.php?frm_action=show&frm_o_o[0][class]=pserver&frm_o_o[0][nname]=[%_server_%]&frm_o_o[1][class]=phpini'>[here]</a> to set it.";
$__emessage['phpini_not_set_client'] = "Need update php.ini to make sure the website able to process php files. Click <url:o=phpini&a=show>[here]</url> to set it.";


$__emessage['session_timeout'] = "Session timeout";

// ------------------------ //

$__information['ndskshortcut_list__pre'] = "<p>You can add a page in kloxo to the favorites, by clicking on the <b>add to favorites</b>link on <b>that particular page</b>.</p>".
	"<p>Each shortcut has a parameter called <b>Sort Id</b>.</p>".
	"<p>By setting a suitable <b>sort id</b> to each of the link and then sorting the entire list by <b>sort id</b>, you can arrange the list in any manner you want.</p>".
	"<p>The tool bar list on the top will reflect the exact way in which this particular list is sorted.<p>";

$__information['sshauthorizedkey_addform_lxlabs_pre'] = "<p>This will add lxlabs ssh key to your authorized keys, which will allow support personnel ".
	"to login to your server without password.</p>".
	"<p>It is recommended that you do this if you have opted for assistance from your provider.</p>";

$__information['lxguardhitdisplay_list__pre'] = "<p>This is the list of blocked/allowed connections.</p>";

$__information['rawlxguardhit_list__pre'] = "<p>This is the list of raw connections. ".
	"This is primarily useful to trace an IP if you know the user account.</p>".
	"<p>For instance, your own customers trying to login to the server, if they attempt too many times, will get blocked.</p>".
	"<p>This page has an <b>advanced search</b> where you can search the list by the <b>user login</b>, ".
	"and thus you will be able to find out the client's ipaddress, which you can remove from the main connections page.</p>";

$__information['lxguardwhitelist_addform__pre'] = "<p>It is recommended that you use the <b>whitelist</b> button on top of ".
	"the <url:a=list&c=lxguardhitdisplay>Connections Page</url>, to whitelist an Ipaddress.</p>".
	"<p>Manually entering the IP is bound to lead to spelling mistakes, and you will be left confused.</p>".
	"<p>If you are entering your IP here, please double check that it is correct.</p>";

$__information['login_pre'] = "<p>Welcome to Kloxo-MR</p>".
	"<p>Use a valid username and password to gain access to the console.</p>";

$__information['tickethistory_addform__pre'] = "<p>You can use &#91quote&#93 &#91/quote &#93 to quote some text, which will shown properly formatted.</p>".
	"<p>You can also use &#91code&#93 &#91/code&#93 for code snippets, and &#91b&#93 &#91/b&#93 for bold.</p>";

$__information['sshconfig_updateform_update_pre'] = "<p>It is recommended that you completely disable password based access to this server, ".
	"and instead use the <url:goback=1&a=list&c=sshauthorizedkey>ssh authorization key manager</url> to allow specific people to access without password.</p>".
	"<p>In any case, make sure you disable password based root access to this server.</p>";

$__information['all_dns_list__pre'] = "<p>This is the list of every dns created by your VPS owners.</p>".
	"<p>Click <url:o=general&a=updateform&sa=reversedns>[here]</url> to configure DNS servers so that all your customers can use it.</p>".
	"<p>Kloxo-MR's DNS manager allows a VPS owner to create DNS directly in Kloxo-MR itself, and the data will be saved on the VPS vendor's servers.</p>".
	"<p>In other words, it allows you to host your vps customer's DNS on your servers.</p>";

$__information['actionlog_list__pre'] = "<p>Action log records all the actions executed on a client or a vps. ".
	"The important thing you should note is that the auxiliary id of the person logged in is properly recorded here.</p>".
	"<p>So you should never give out the main account to anyone, instead create <url:a=list&c=auxiliary> auxiliary identities </url>for each of your staff.</p>";

$__information['sshauthorizedkey_list__pre'] = "<p>Please note, the ~/.ssh/authorized_keys2 file has been deprecated as of openssh version 3, ".
	"and so only the ~/.ssh/authorized_keys file is managed.</p>".
	"<p>These are the ssh keys from the machines which can login to this server without providing password. Make sure you keep this list trimmed.</p>";

$__information['updateform_forcedeletepserver_pre'] = "<p>Force delete Server just removes the server from Kloxo-MR's database.</p>".
	"<p>This is useful if the server has been completely removed is no longer accessible.</p>";

$__information['allowedip_addform__pre'] = "<p>This is only meant for blocking access to the control panel, and will not block access to the actual resource. ".
	"You can add an IP of the form <b>192.168.1.*</b>  denote a range.</p>".
	"<p>That is, instead of providing a number, you can use <b>*</b>  to represent the entire range. ".
	"You can also add individual IPs of the form 192.168.1.2.</p>".
	"<p>Please note that the dotted notation is necessary, and you have to provide all the 4 fields. ".
	"If you want to allow everyone, remove all the allowed IPs, or provide <b>*.*.*.*</b>.</p>".
	"<p>Other IP notations are not supported at this point.</p>";

$__information['blockedip_addform__pre'] = "<p>This is only meant for blocking access to the control panel, and will not block access to the actual resource.".
	"This is the list of blocked IPAddresses.</p>".
	"<p>The IP notation is the same as that of 'Allowed IP'.</p>".
	"<p>You should add blocked ips only if the allowed ip list is empty. ".
	"If allowed ip list is non-empty then, automatically all ips not listed are denied.</p>";

$__information['general_updateform_portconfig_pre'] = "<p>This page is primarily meant to configure the ports of Kloxo-MR.</p>".
	"<p>After making the changes here, you will need to restart Kloxo-MR service once for the changes to take effect.</p>".
	"<p>Run this command '<b>sh /script/defaultport</b>' and the ports will be reset to the default. You can then restart Kloxo-MR.</p>".
	"<p>Leave the fields blank to revert to default ports.</p>";

$__information['lxbackup_updateform_schedule_conf_pre'] = "<p>Please note that only the scheduled backups, that is, ".
	"backups that start with the name <b>kloxo-scheduled-</b>, will be rotated.</p>".
	"<p>If you create your own backup with your own name, they won't be rotated.</p>".
	"<p>So if you want a manually created backup to be rotated, provide the initial string as <b>kloxo-scheduled-</b>.</p>";

$__information['updateform_ssl_kloxo_pre'] = "<p>This will set the ssl certificate for Kloxo-MR as this particular certificate.</p>".
	"<p>Make sure you restart Kloxo-MR after you set it here.</p>";

$__information['updateform_ssl_authorized_keys_pre'] = "<p>These are the SSH keys from the machines which are authorized to login to ".
	"your account without supplying the password.</p>".
	"They are kept as 1 per line. You can add the keys to the machines you want to have password-less access to this machine.</p>".
	"<p>You should also keep this file trimmed so as to reduce the chances of unwanted people logging in.</p>";

$__information['general_updateform_selfbackupconfig_pre'] = "<p>This is primarily meant to configure the remote backup of ".
	"the master database and nothing else.</p>".
	"<p>The database dump is taken everyday and is saved in a local folder in this machine. ".
	"If an ftp account is configured here, the file will be uploaded to the machine.</p>".
	"<p>This is mainly useful in large cluster setup where the failure of master can have much larger impacts.</p>";

$__information['lxguard_updateform_update_pre'] = "<p>Lxguard protects you against brute force attacks by monitoring the ssh and ftp log messages, ".
	"and blocking ipaddresses that have too many failed attempts at logging into the server.</p>".
	"<p>Lxguard is <b>default turned on</b>, and will automatically block hosts, and <b>cannot be turned off</b>.</p>".
	"<p>You can configure Lxguard by specifying the <b>threshold of failed attempts</b> or by adding certain ipaddresses to the whitelist.</p>".
	"<p>If an IP is found in the whitelist, it won't be blocked, even if it has crossed the threshold of failed attempts.</p>".
	"<p>To remove the warnings you get about Lxguard please click on the agreement checkbox below. </p>";

$__information['general_updateform_generalsetting_pre'] = "<p>Add multiple paths for 'Extra basedir' with separated by ':'. Example: /var/log/:/var/qmail:/opt/configs</p>" .
	"<p>The 'HelpDesk URL' is a link to your HelpDesk, ".
	"which will be used in place of the default help desk built into the software.</p>".
	"<p>Community URL is the link the client will see on his left page, and in normal cases can point to your forum.</p>";

$__information['custombutton_addform__pre'] = "<p>In 'URL', you can use %nname%, which will be substituted with the name of the client.</p>".
	"<p>You can use %default_domain% for the default domain of the client.</p>";

$__information['updateform_download_config_pre'] = "<p>Normally, when downloading files, the master creates a temporary session, ".
	"and then redirects the browser directly to the slave.</p>".
	"<p>This is to save the bandwidth, since otherwise, the files are pointlessly have to go through the master.</p>".
	"<p>If you enable this, Kloxo-MR will instead route the file via the master itself.</p>".
	"<p>This is useful if you have given private ips for slaves, and the slaves are not accessible from the outside world.</p>";

$__information['updateform_login_options_pre'] = "<p><b>Note:</b> session timeout cannot be less than 100 and if less, will be automatically set to 100.</p>";

$__information['resourceplan_show__pre'] = "<p><b>Note:</b> ".
	"If you change the values here, every account that uses this plan will be updated with the new values.</p>".
	"<p>Click <url:a=updateForm&sa=description>[here]</url> to see the accounts configured on this plan.</p>";

$__information['resourceplan_addform__pre'] = "<p>You can set 'Resource Plan' here and then create client based on it.</p>";

$__information['lxbackup_updateform_backup_pre'] = "<p>The backup file will appear in the __backup directory of your client area. ".
	"You can access it by clicking on the 'File Manager' Tab.</p>".
	"<p>To restore a backup, you can first upload it to the server using the <b>upload</b> tab.</p>".
	"<p>Please note that Kloxo-MR backup is heirarchical. ".
	"If you take backup of a particular resource, everything under it is automatically included. ".
	"Thus if you take backup of admin, then you need not take backups of clients under you separately.</p>";

$__information['lxbackup_updateform_restore_from_file_pre'] =	"<p>You can upload directly or from an http url or an ftp server. ".
	"Then come here, and click on <b>directory</b> icon on the right in the <b>restore</b> form, ".
	"and it will allow you to select the particular file. Then click on <b>start restore process</b>.</p>";

$__information['phpini_updateform_edit_admin_pre'] = "<p>Installing PHP modules (like xcache/ioncube/zend/suhosin) rpm via yum. Installing these modules depend on php branch and version installed on system.</p>" .
	"<p>If using 'php-branch' in 'php used' and using php53u, install with 'yum install php53u-xcache' and then try 'php -m' for to make sure.</p>" .
	"<p>Ask to <a href='http://forum.mratwork.com' target='_blank'>forum</a> to know how to install php extension for 'multiple php'. Generally, using 'sh /script/phpm-extension-installer'.</p>" .
	"<p>Click <url:o=serverweb&a=show>[here]</url> to install another php for 'Multiple PHP'.</p>" .
	"<p><b>Note:</b></p>" .
	"<ul>" .
		"<li>for enable 'multiple php' need install php52m, php53m, php53m, php54m, php55m and php56m with 'sh /script/phpm-installer php53m' (example for php53m)</li>" .
		"<li>for shared-hosting, better install all phpXYm series</li>" .
		"<li>Format for 'multiple php ratio' is 'a:b:c:d' where 'a' for php52m, 'b' for php53m, 'c' for php54m and 'd' for php55m</li>" .
	"</ul>" .
	"</p>" .
	"<p>In domain-level, appear 'web selected' and 'php selected' (if enable 'multiple php').</p>" .
	"<p>For web proxy (example: nginx-proxy), select 'front-end' mean execute process in nginx (rewrite rule in .htaccess may not work).</p>";

$__information['client_updateform_wall_pre'] = "<p><b>Note:</b> The Message will only be sent to your direct children (one level, ".
	"including this account) who has a contact email set.</p>";

$__information['ffile_updateform_upload_pre'] = "<p>If you want to upload multiple files/directories, zip them up and upload; ".
	"you can unzip the archives from inside the file manager.</p>";

$__information['dskshortcut_a_list__pre'] = "<p>To add a page to the favorites, click on the <b>add to favorites</b> link that appears on the top right.</p>".
	"<p>You can click on a favorite in the list below and change its name to something more personally recognizable.</p>".
	"<p>You can click on the <b>description</b> header, and the list will be sorted by that field, and then refresh the entire frame.</p>".
	"<p>The actual favorite list on the left panel will exactly reflect the order that's visible here.</p>";

$__information['ticketconfig_updateform_ticketconfig_pre'] = "<p>The mailgate is an account from which Kloxo-MR will download mails at particular intervals, ".
	"and will be parsed and added to the helpdesk.</p>".
	"<p>When sending out mails, Kloxo-MR will send the mails as originating from the address you configure. ".
	"The address is of the form <b>account@domain.com</b>. Server is the pop server from which to download the mail.<b>server.com</b>.</p>".
	"<p>If you check the <b>use ssl</b> box, then the mail will be downloaded over pop3-ssl, at port 995. ".
	"It is always recommended that you use ssl, but you will need to make sure that the remote mail serer does support pop3-ssl service at port 995.</p>";

$__information['updateform_mysqlpasswordreset_pre'] = "<p>This should only be used if you have lost the MySQL database root password.</p>".
	"<p>In normal circumstances, you can change the password by clicking <url:a=list&c=dbadmin>[here]</url> (database admin).</p>".
	"<p>Please be patient as this will take a while to finish. This will reset the mysql root password by running it with the skip-grant-tables option.</p>".
	"<p>If this doesn't work, please login through ssh and run '<b>sh /script/reset-mysql-root-password</b>'.</p>";

$__information['updateform_pserver_s_pre'] = "<p>This is the Server Pool for this reseller.</p>".
	"<p>This shows the list of servers that this reseller can use when creating a client.</p>".
	"<p>That is, when creating a new customer, this reseller will be able to assign the servers in this list to him.</p>";

$__information['general_updateform_disableper_pre'] = "<p>This is the percentage of usage at which the account will be disabled.</p>".
	"<p>The normal value is 110%. You will be given warnings when the quota reaches 90,100,110%.</p>";

$__information['lxbackup_updateform_ftp_conf_pre'] = "<p>If you enable <b>dont keep local copy</b>, the local file be deleted. " .
	"You can use this if you want to save space in your account.</p>" .
	"<p>You can write full format like <b>ftps://1.2.3.4:21001</b> (for example) on 'FTP Server'</p>";

$__information['vv_updateform_skin_logo_pre'] = "<p>To enforce your logo on your children, just disable their 'can Manage logo' in the permission settings.</p>";

$__information['pserver_updateform_information_pre'] = "<p>FQDN is a very important field and it should be set to a domain name ".
	"that will properly resolve to this particular machine.</p>".
	"<p>Once you set the FQDN, Kloxo-MR will use that value for all further network communication, ".
	"and if the FQDN set here is wrong, then network communication between the master/slave would fail.</p>".
	"<p>If you leave it blank, Kloxo-MR will use the first ipaddress on this server for communication.</p>".
	"<p>Set FQDN to a hostname using which you can access this machine from everywhere.</p>";

$__information['pserver_addform__pre'] = "<p>If you have freshly installed a slave server, the password is admin.</p>".
	"<p>It is strongly recommended that you add servers by their name rather than by their IP.</p>".
	"<p>The Server should be accessible from the master using the particular name you supply here, for instance, server.domain.com.</p>".
	"<p>The verbose-identifier is a string that can be used to describe this server, and will be visible in chooser boxes.</p>";

$__information['sp_specialplay_updateform_upload_logo_pre'] = "<p><b>Note:</b>".
	"<ul>".
		"<li>Leave the fields blank to reset the logos to default images</li>".
		"<li>To enforce your logo on your children, just disable their 'can Manage logo' in the permission settings</li>".
 "<li>Upload logo image (example: appear on top-right cp page) should be in PNG format with optimal height 75 pixels, while width is up to user's choice</li>".	"</ul></p>";

$__information['web_updateform_extra_tag_pre'] = "<p><b><span style='color=red'>Warning!!!!!</span></b>  Whatever you enter here will be directly added to the VirtualHost.</p>".
	"<p>If there is a syntax error in this, it will prevent the webserver from restarting.</p>".
	"<p>This option is available only for the admin user. After Saving here, make sure that the server is running.</p>";

$__information['addondomain_list__pre'] = "<p><b>Note:</b>".
	"<ul>".
		"<li>If you want a parked domain with full DNS and mail management, create a full domain that has the same document root as the destination domain.</li>".
		"<li>If you want a redirected domain with full DNS and mail management, create a full domain, and then redirect its <b>/</b> to the destination domain.</li>".
	"</ul></p>";

$__information['redirect_a_list__pre'] = "<p>This will allow you to redirect a particular url in the domain to another.</p>".
	"<p>Click <url:a=updateform&sa=configure_misc>[here]</url> if you want to forcibly redirect non-www base <b>domain.com</b> to <b>www.domain.com</b>.</p>";

$__information['web_updateform_dirindex_pre'] = "<p>Enabling <b>directory index</b> will allow you to browse the directories of your domain via the webserver.</p>".
	"<p>If directory index is disabled, and if an index.xxx file is not found inside the directory, a forbidden error message will be raised.</p>";

$__information['updateform_editmx_pre'] = "<p>If you want to configure remote mail server, click <url:a=updateform&sa=remotelocalmail>[here]</url>.</p>".
	"<p>You can tell Kloxo-MR that the mail server is configured remotely, so that all local generated mails will be sent to that server.</p>".
	"<p>If you don't configure remote mail, then all mails to this domain will delivered locally itself, without doing any DNS lookup.</p>";

$__information['web_updateform_run_stats_pre'] = "<p>This will allow you to forcibly run the stats program, ".
	"so that you can see your latest statistics in the web statistics page.</p>".
	"<p>Use <b>update all</b> to run it on all the domains visible in the top pull down menu.</p>";

$__information['server_alias_a_addform__pre'] = "<p>You can add wildcards (*) as an alias so that all the subdomains are automatically directed to this domain. ".
	"Kloxo-MR will also automatically add a DNS entry for the alias.</p>".
	"<p>Once you configure the catchall subdomain with wildcards (*), you can add the proper logic in your script to detect the correct subdomain and do accordingly.</p>".
	"<p>Lighttpd may be confused with selected wildcards (*). Access to defaults (default, cp, disable and 'customize') pages will be lead to the domain page.</p>";

$__information['updateform_sesubmit_pre'] = "<p>Your domain will be submitted to all the searchengines listed below.</p>".
	"<p>The email should be an address that's not used often, since you are very highly likely to get Spammed on the email you enter here.</p>";

$__information['mmail_updateform_authentication_pre'] = "<p>Primary MX server is automatically included in the SPF, and need not add it separately.</p>" .
	"<p>DMARC is optional but recommended to enabled.</p>".
	"<p>Can use <b>update all</b> to impress these values on all the domains visible on the top pull down list.</p>";

$__information['updateform_preview_config_pre'] = "<p>Preview domain is a master domain, to which the site-preview button will be redirected to.</p>".
	"<p>You have to manually add a parked domain called domain.com.previewdomain.com to this domain, and then add the previewdomain.com here.</p>".
	"<p>Then the <b>DNSless preview</b> will be redirected to domain.com.previewdomain.com.</p>".
	"<p>If unsure, please leave this blank.</p>";

$__information['updateform_stats_protect_pre'] = "<p>Stats page protection is the password that's used to protect the statistics page for your domain.</p>".
	"<p>If set to null password, protection will be disabled, and you will be able to access the stats directly.</p>";

$__information['updateform_installatron_pre'] = "<p>You have to logout of your current user, and then specifically login as this user ".
	"to use Installatron for this particular account.</p>".
	"<p>That is, Installatron is only available at present for the account that is directly logged in.</p>";

$__information['ftpuser_admin'] = "<p>Use <b>--direct--</b> to add an ftpuser that does not contain domain name.</p>";

$__information['updateform_default_domain_pre'] = "<p>This will set the domain that's considered as the primary domain for this particular account.</p>".
	"<p>You can access this domain's document root by going to http://IP/~clientname.</p>".
	"<p>To map an ipaddress to a domain, you have to click <url:a=list&c=ipaddress>[here]</url>, go inside an ipaddress and click on 'configure domain' tab there.</p>".
	"<p>If you want to view a domain before the dns is setup, the best way is to create an entry for the domain in your local etc/hosts file.</p>".
	"<p>Just add an entry like this: <b>192.168.1.32 domain.com</b> on a separete line, ".
	"and then you will be able to access the domain by typing it in your browser's url box.</p>";

$__information['web_updateform_blockip_pre'] = "<p>Add one IP per line. If you want to add an IP range use the .*.* notation. For instance, 192.168.*.*.</p>".
	"<p>Please note, this is the only notation supported for ip ranges. The standard ip notation is not supported.</p>";

$__information['web_updateform_statsconfig_pre'] = "<p>Every day, if the log file's size is larger than 50MB, they are moved into the client's home directory.</p>".
	"<p>If you set the remove_processed_logs as true, then instead of moving, they will be deleted. ".
	"Your main statistics calculation will not be affected at all.</p>";

$__information['web_updateform_hotlink_protection_pre'] = "<p>Your domain and subdomains will automatically have access to the images, ".
	"and you don't have to add them specifically.</p>".
	"<p>A *.domain.com is automatically added to the list of allowed domains you supply here.</p>".
	"<p>The <b>redirect to image</b>  has to be a path to the image inside your domain, and NOT a full url. It should be of the form (/img/noaccess.gif).</p>".
	"<p>You have to enter domains as simple names without any wild-characters. For example, domain.com, mydomain.com, mysdomain.com</p>";

$__information['mailqueue_list__pre'] = "<p>It will take a little bit more time for the queue to actually disappear.</p>".
	"<p>So after clicking on delete, the mails will appear in the queue for some more time. Just refresh the page after around a minute to ".
	"verify if the mails are actually deleted or not.</p>";

$__information['mailqueue_updateform_update_pre'] = "<p>To see the log for the mail, please go back to the listing, and flush the mailqueue once, ".
	"since only the log for the past hour is parsed.</p>";

$__information['rubyrails_addform__pre'] = "<p>The application would be normally accessible at http://domain.com/applicationname.</p>".
	"<p>The path would be /home/client/ror/domain.com/applicationname. If you specify the <b>accessible directly</b> flag, ".
	"then the application would be accessible at http://domain.com itself.</p>";

$__information['installapp_addform__pre'] = "<p>To install an application in the document root, please leave the <b>Location</b> blank.</p>".
	"<p>To install the same application for another domain, please use the select box on the top, and change the domain to another, ".
	"and you will be able to get same form with the new domain as the parent.</p>".
	"<p>A message with login and url information will be sent to the contact email address you provide here.</p>".
	"<p><b><span style='color: red;'>WARNING:</span></b> InstallApp is deprecated. Use Installatron or SpectrApps instead.</p>";

$__information['mysqldb_updateform_restore_pre'] = "<p>You can use this only to restore the backups that were explicitly taken in Kloxo-MR ".
	"itself using the <b>Get Backup</b> tab.</p>".
	"<p>To restore normal mysql dump file, please use phpMyAdmin.</p>";

$__information['updateform_search_engine_pre'] = "<p>Some engines may require your e-mail confirmation for submission.</p>".
	"<p>Do not enter your main e-mail address, since you may recieve spam messages.</p>".
	"<p>For a better ranking repeat the operation every 3-4 weeks but not sooner, since you may get banned.</p>";

$__information['updateform_domainpserver_pre'] = "<p>These are the servers on which the domains under this client will be configured on.</p>".
	"<p>If you change the values here, automatically all the domains will be moved to the proper servers. ".
	"That is, if you change the <b>mail server</b> and update, then <b>all</b> the mailaccounts for the domains under this client ".
	"will be migrated from the old server to the new server.</p>".
	"<p>The <b>dnstemplate</b>  is the new dnstemplate that the dns of the all the domains will be switched to. ".
	"So you have to make sure that you first create a dnstemplate that reflects the new configuration, then provide that to kloxo here.</p>".
	"<p>See bottom for more help on server move. You can make mass DNS change later by going to <b>dns manager -> rebuild</b> and clicking <b>update all</b>, ".
	"which will impress the new dnstemplate on all the domains in the account.</p>";

$__information['updateform_exclusive_pre'] = "<p>IP address can be assigned to certain client exclusively.</p>";

$__information['domainipaddress_updateform_update_pre'] = "<p>This will allow you to map a particular ipaddress to a domain.</p>".
	"<p>That is, if someone accesses http://ip, then the document root of the domain configured here will be shown.</p>" .
	"<p><b>WARNING</b>:" .
	"<ul>" .
		"<li>Please always select '--Disabled--' if you ONLY have 1 IP</li>".
		"<li>Need 1 IP as shared-IP (no assign to domain) if you have more than 1 IPs and other IP able to assign to domain</li>".
	"</ul>" .
	"</p>";

$__information['sslipaddress_updateform_update_pre'] = "<p>To setup an ssl for an ipaddress, first upload/add an ssl certificate ".
	"from <url:goback=2&a=list&c=sslcert>[here]</url>.</p>";

$__information['sslcert_updateform_update_pre'] = "<p>Two option for SSL certificate:</p>" .
		"<ul>" .
			"<li><b>IP Address based</b>: must assign to certain IP address and possible access domain via IP address" .
				"<p>To assign IP address, click <url:goback=2&a=list&c=ipaddress>[here]</url> and then go into an IP address, ".
				"and click on <b>ssl certificate</b> tab and select an IP address.</p>" .
				"<p>The admin will need to have assigned you an exclusive ipaddress for you to access this feature.</p></li>" .
			"<li><b>Domain based</b>: possible every domains heve their owned ssl certifate without assign to IP address</li>" .
		"</ul>";
	/*
		"<p><b>Note</b>:" .
		"<ul>" .
			"<li><b>Common Name</b>: Set wildcards (*) domain (ex: *.domain.com)</li>" .
			"<li><b>Subject Alt Name</b>: Set primary domain (ex: domain.com) and other domains (separated by comma; including their wildcards if needed)</li>" .
		"</ul>";
	*/
$__information['domain_not_customer'] = "<p>To add a domain, create a customer first, and you can add domains under him.</p>".
	"<p>To add a customer, click <url:a=addform&c=client&dta[var]=cttype&dta[val]=customer>[here]</url>.</p>";

$__information['ipaddress_addform__pre'] = "<p>IP must assigned to detectable 'Device Name'.</p>";

$__information['ipaddress_updateform_update_pre'] = "<p>Exclusive IP will allow you to have dedicated control of a particular ipaddress. ".
	"This is useful for setting up SSL and also for setting up an ip for a domain.</p>".
	"<p>That is, if you want a particular IP to resolve to a domain.</p>".
	"<p>For you to have an exclusive IPaddress, the administrator will have to set the exclusive client of a particular ipaddress.</p>".
	"<p>To setup ssl or to map an IP to a domain, click on an IPaddress, and then click on <b>configure ssl</b> or <b>configure domain</b>.</p>";

$__information['clientmail_list__pre'] = "<p>This will list the number of mails sent out by your clients via ".
	"the webserver or smtp auth relay in the last 2 days.</p>".
	"<p>If it is a full mailaccount like <b>user@domain.com</b>, then it represents mail sent via relay.</p>".
	"<p>If it is a simple username, then it represents mail sent via a form in the web server.</p>";

$__information['servermail_updateform_update_pre'] = "<p>Set 'My Name' with domain name. Better use domain which taken from server's hostname.</p>".
	"<p>The max smtp instances specifies the maximum number of smtp processes that are allowed. You should set it to some number, " .
	"say 10, if you are getting spammed heavily.</p>".
	"<p>If you leave it blank, it will be set to UNLIMITED, which is the default.</p>" .
	"<p>You can choose port 25, 465 or 587 for SMTP.</p>" .
	"<p>Set <b>SMTP Relay</b> to use outside SMTP server instead internal." .
	"<ul>" .
		"<li>Full format (all domains): <b>:smtpserveraddress:port username password</b><br>" . 
		"- Example: <b>:mail.domain.com:25 admin@domain.com pass123</b></li>" .
		"<li>Open-relay format (example for certain domain): <b>domain2.com:smtpserveraddress</b><br>" . 
		"- Example: <b>domain2.com:mail.domain.com</b></li>" .
	"</ul></p>";


$__information['updateform_switchprogram_pre'] = "<p>Switching Programs will take a while, since it needs to remove the old program from the system, ". 
	"and install the new one using yum.</p>".
	"<p>The log for this will be available in the 'shell_exec' file. All your information will be transparently migrated.</p>".
	"<p>You will need to wait one minute before the new service properly restarts.</p>".
	"<p>Add '<b>&lt;?php header(\"X-Hiawatha-Cache: 10\"); ?&gt;</b>' in top of index.php to boosting Hiawatha performance. ".
	"Only Nginx and Hiawatha able to use 'microcache' at this moment.</p>".
	"<p>Enable '<b>No fix config</b>' will perform no action to fix configuration where important for huge amount for domains; ".
	"you must run '<b>sh /script/fix-all; sh /script/restart-all</b>' from ssh manually.</p>";

$__information['updateform_permalink_pre'] = "<p>Kloxo-MR comes with default permalink configuration for many apps.</p>".
	"<p>Please select the application and the directory where you have installed it, ".
	"and kloxo will add the corresponding rewrite rule into the lighty configuration.</p>".
	"<p>Please note that for some applications, permalinks are achieved via setting the 404 error handler, for instance wordpress.</p>";

$__information['weblastvisit_list__pre'] = "<p>This is the list of last 50 visitors or the number of visitors in ".
	"the last 20 hours, whichever is smaller.</p>".
	"<p>Realtime represents the time in unix time stamp, and is there so that you can sort accurately by time.</p>".
	"<p>The longer strings are truncated to fit the screen, and you can see their full values by moving the mouse over them.</p>";

$__information['subweb_a_addform__pre'] = "<p>This is a simple subdomain. A simple subdomain only has a web component, ".
	"and you cannot add mail or manage DNS for it.</p>".
	"<p>If you want a full subdomain, please use the <b>subdomain button</b> on the main <b>domains</b> page.</p>".
	"<p>The simple subdomain's path is /home/clientname/domain/domain.com/subdomains/subdomainname.</p>";

$__information['updateform_lighty_rewrite_pre'] = "<p>This is the custom lighttpd rewrite rule that will directly appended to the configuration file ".
	"without any change. It will be of the form <b>url.rewrite = ( ...</b>.</p>";

$__information['updateform_custom_error_pre'] = "<p><b>Note:</b></p>".
	"<p>The values you have to provide are the virtual paths to the files that will be shown in case of these errors.</p>".
	"<p>Example: /error_files/404.html.</p>";

$__information['domain_updateform_ipaddress_pre'] = "<p><b>Note:</b></p>".
	"<p>Make sure that you make the requisite changes to nameserver configuration too.</p>";

$__information['client_updateform_ipaddress_pre'] = "<p><b>Note:</b></p>".
	"<p>The available ip pool is selected from the machines in the web server pool.</p>";

$__information['domaintemplate_addform__pre'] = "<p><b>Note:</b></p>".
	"<p>The Max Value on the right shows your current quota limit.</p>".
	"<p>You can create a Template with values more than your quota, but you won't be able to use them to create Domains/Clients.</p>";

$__information['spam_updateform_update_pre'] = "<p>The 'score'--which can be 1-10--is the value at which a mail is marked as SPAM.</p>".
	"<p>So if you set it to lower values, more mail will be marked as spam.</p>".
	"<p>Too low values might lead to genuine mails getting classified as spam. ".
	"Too high values will lead to high amount of spam getting through the filter.</p>";

$__information['web_updateform_enable_frontpage_flag_pre'] = "<p>The front page password will be the same as that of the system user (main ftp user).</p>";

$__information['installappsnapshot_list__pre'] = "<p>Snapshots are the exact copy of the database and the files of your application at a particular time.</p>".
	"<p>You can restore your application to a particular snapshot by clicking on the <b>restore</b> button.</p>";

$__information['sshclient_updateform_disabled_pre'] = "<p>Your admin hasn't enabled shell access for you.</p>".
	"Please open a support ticket if you need ssh access.</p>";

$__information['sshclient_updateform_warning_pre'] = "<p>Please note that all your activity is logged and any attempt at accessing files ".
	"not belonging to you will lead to termination of your hosting account.</p>".
	"<p>So please act responsibly.</p>";

$__information['ffile_show___lx_error_log_pre'] = "<p>This is the error log for your domain.</p>".
	"<p>The contents of this will help you trouble shoot if you are having any problems regarding the domain.</p>";

$__information['ffile_show___lx_access_log_pre'] = "<p>This is the access log for your domain.</p>".
	"<p>You can download this by clicking on the <b>download</b> tab at the right. ".
	"This file contains information about every single hit that is made to your website.</p>";

$__information['updateform_dnstemplatelist_pre'] = "<p>Allocate only a single dns template to your customer.</p>".
	"<p>This would mean that kloxo will not show the dns template select box while adding a domain, ".
	"which will make it less confusing to your customer.</p>";

$__information['forward_a_addform__pre'] = "<p>The forward addresses are a list of email addresses to which the mail is forwarded to. ".
	"One copy of the mail gets saved to the actual mailaccount too.</p>".
	"<p>You can disable local storage by click <url:a=updateform&sa=configuration>[here]</url>.</p>";

$__information['webserver_config'] = 
	"<p><b>PHP Used</b>".
	"<ul>".
		"<li>The purpose for 'PHP Used' is using a different PHP for 'Single PHP' System. To enable 'Multiple PHP, click <url:o=phpini&a=show>[here]</url></li>".
      		"<li>You can choose 'standard php' (install with 'PHP Branch') or 'Multiple PHP' (install with 'Multiple PHP Install')</li>".
	"</ul></p>".
	"<p><b>PHP Branch</b>".
	"<ul>".
		"<li>Branch-based PHP version</li>".
		"<li>Php-fpm for 'php52' may not work; ".
		"test result with 'php -v' via ssh to find out error (usually incompatible modules)</li>".
		"<li>Better reboot after change, especially if using Lighttpd, Nginx or Apache with 'php-fpm' as 'php-type'</li>".
	"</ul></p>".
	"<p><b>Multiple PHP Install</b>".
	"<ul>".
		"<li>PHP for 'Multiple PHP' already installed will show in 'Multiple PHP Already Installed'</li>".
		"<li>List of 'Available' meaning all available for 'Multiple PHP'</li>".
		"<li>List of 'Selected' meaning PHP that want to install</li>".
		"<li>Choose the same name with 'Multiple PHP Already Installed' for need reinstall</li>".
		"<li><b>Note</b>:".
			"<ul>".
				"<li>Select for 'php53m' as the same way for running 'sh /script/set-php-fpm php53m' in ssh command-line</li>".
				"<li>Only work and optimize for php-fpm</li>".
				"<li>Install process running in background and don't install other(s) until current PHP installed appear in 'Multiple PHP Already Installed'</li>".
			"</ul>".
		"</li>".
	"</ul></p>".
	"<p><b>PHP Type</b>".
	"<ul>".
		"<li>Process-based: httpd-prefork, http-itk</li>".
		"<li>thread-based: httpd-worker, httpd-event</li>".
		"<li>secure environment: suphp, php-fpm, ruid2, itk, fcgid</li>".
	"</ul></p>".
	"<p><b>Apache Memory Optimize</b>".
	"<ul>".
		"<li>Optimize memory usage</li>".
		"<li>Most situations enough select 'default' or 'low'. Select 'medium' or 'high' if having many websites and or clients</li>".
		"<li>For proxy (like nginx-proxy), select 'low' or 'medium' only</li>".
		"<li>Run 'sh /script/apache-optimize --help' to know how calculation work</li>".
	"</ul></p>".
	"<p><b>MySQL Convert</b>".
	"<ul>".
		"<li>MyISAM (less memory usage; save about 100-200 MB)<br/>".
	       	"<b>Note:</b> adding 'skip-innodb' may cause trouble accessing the panel)</li>".
		"<li>InnoDB (higher performance)</li>".
		"<li>Aria (Alternative to MyISAM if select MariaDB)</li>".
	"</ul></p>".
	"<p><b>Fix 'Ownership' And 'Permissions'</b>".
	"<ul>".
		"<li>Prevent '500 Internal server error' on secure environment</li>".
	"</ul></p>".
	"<p><b>Notes:</b>".
	"<ul>".
		"<li>Add in .htaccess for using php 5.2 (change php52 to php53 if want using php 5.3 and so on):<br/><br/>".
		"<b>&nbsp;&nbsp;&nbsp;&nbsp;&lt;FilesMatch \.php$&gt;<br/>".
		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SetHandler x-httpd-php52<br/>".
		"&nbsp;&nbsp;&nbsp;&nbsp;&lt;/FileMatch&gt;</b><br/><br/>".
		"-OR-<br/><br/>".
		"<b>&nbsp;&nbsp;&nbsp;&nbsp;Options +ExecCGI<br/>".
		"&nbsp;&nbsp;&nbsp;&nbsp;&lt;FilesMatch \.php$&gt;<br/>".
		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SetHandler fcgid-script<br/>".
		"&nbsp;&nbsp;&nbsp;&nbsp;&lt;/FileMatch&gt;<br/>".
		"&nbsp;&nbsp;&nbsp;&nbsp;FCGIWrapper /home/kloxo/client/php52.fcgi .php</b><br/><br/>".
		"<li>If select <b>suphp/suphp_worker/suphp_event</b> and then select 'fix-ALL' to prevent '500 Internal server error'</li>".
	"</ul></p>";

// $__information['client_show__pre'] = "<p>No information...</p>";
// $__information['domain_show__pre'] = "<p>No information...</p>";
// $__information['pserver_show__pre'] = "<p>No information...</p>";

$__information['mailaccount_show__pre'] = "<p>Login in Kloxo-MR panel with <b>[%_mailaccount_%]</b> will be going to this page directly.</p>";

$__information['smessage_addform__pre'] = "<p>You can send a message your client.</p>";

$__information['ticket_addform__pre'] = "<p>You can make a ticket here. Previous tickets will be listing here.</p>";

$__information['helpdeskcategory_a_addform__pre'] = "<p>You can add/delete 'HelpDesk' category here.</p>";

// $__information['all_domain_list__pre'] = "<p>No information...</p>";
// $__information['all_addondomain_list__pre'] = "<p>No information...</p>";
// $__information['all_mailaccount_list__pre'] = "<p>No information...</p>";
// $__information['all_mailforward_list__pre'] = "<p>No information...</p>";
// $__information['all_mysqldb_list__pre'] = "<p>No information...</p>";
// $__information['all_cron_list__pre'] = "<p>No information...</p>";
// $__information['all_ftpuser_list__pre'] = "<p>No information...</p>";
// $__information['all_mailinglist_list__pre'] = "<p>No information...</p>";

$__information['reversedns_addform__pre'] = "<p>Reverse dns only work for Class C (256 ips) or more.</p>";

$__information['general_updateform_reversedns_pre'] = "<p>Setting up basic Reverse/Forward DNS here.</p>";

// $__information['all_reversedns_list__pre'] = "<p>No information...</p>";

$__information['vv_dns_blank_message'] = "<p>DNS Manager</p>";

$__information['dnstemplate_addform__pre'] = "<p><b>Primary and Secondary DNS</b></p>".
	"<p>If selecting 'default' in 'DNS Template Name', 'default.dnst' is 'symbolic' domain name.</p>".
	"<p><b>Example</b> - 'ns1.default.dnst' (on 'Primary DNS') will be converted to 'ns1.domain.com' for 'domain.com'.</p>";

$__information['dns_show__pre'] = "<p>Here you can manage DNS zone entries.</p>";

$__information['dns_updateform_rebuild_pre'] = "<p>Here you can rebuild your domain zone from a DNS Template.</p>";

$__information['dns_record_a_addform_ns_pre'] = "<p>Here you can manage the 'NS record'.</p>";

$__information['dns_record_a_addform_a_pre'] = "<p>Here you can manage the 'A record'.</p>".
	"<p>Enter '__base__', if you want to get the base domain.</p>";

$__information['dns_record_a_addform_cname_pre'] = "<p>Here you can manage the 'CNAME record'.</p>";

$__information['dns_record_a_addform_fcname_pre'] = "<p>Here you can manage the 'FCNAME record'. <b>FCNAME</b> stands for full CNAME and ".
	"will allow you to point a subdomain to an external domain.</p>";

$__information['dns_record_a_addform_mx_pre'] = "<p>Here you can manage the 'MX record'.</p>".
	"<p>Enter '__base__', if you want to get the base domain.</p>";

$__information['dns_record_a_addform_aaaa_pre'] = "<p>Here you can manage the 'AAAA record' (the same as 'A record' but for IPv6).</p>".
	"<p>Enter '__base__', if you want to get the base domain.</p>";

$__information['dns_record_a_addform_txt_pre'] = "<p>Use &lt;%domain%&gt; if you want the domain name inside a TXT record. ".
	"For instance, 'v=spf1 include: &lt;%domain%&gt;'.</p>".
	"<p>Automatically create 'SRV record' with the same contents.</p>";

$__information['dns_updateform_parameter_pre'] = "<p>Here you can manage the 'SOA record'.</p>";

// $__information['dns_record_a_updateform_edit_pre'] = "<p>No information...</p>";

$__information['domain_addform__pre'] = "<p>Better use lowercase for 'Domain Name' and 'Document Root' to minimize compatiblity issue.</p>".
	"<p>Subdomain like 'subdom.domain.com' consider as domain.</p>".
	"<p>Leave the document root blank and Kloxo-MR will automatically use domain from 'Domain Name' as the docroot.</p>".
	"<p>The document root may not contain any space at the end or before the slash. Please check and submit again.</p>";

$__information['subdomain_addform__pre'] = "<p>Better use lowercase for 'Domain Name' and 'Document Root' to minimize compatiblity issue.</p>".
	"<p>Leave the document root blank and Kloxo-MR will automatically use domain from 'Subdomain Name' + selected domain as the docroot.</p>".
	"<p>The document root may not contain any space at the end or before the slash. Please check and submit again.</p>";

$__information['mailaccount_addform__pre'] = "<p>Kloxo-MR automatically add <b>postmaster</b> account if you add domain/subdomain.</p>".
	"<p>You can add another mail accounts if you need them.</p>";

$__information['sp_specialplay_updateform_skin_pre'] = "<p>Base 'Appearance' is 'Skin'. Different skin have different features.</p>".
	"<p><b>Example:</b> 'Feather' skin doesn't have 'background image' feature.</p>".
	"<p><b>Note:</b> Simplicity skin may not work in old browser version. Found 'menu' problem in IE 8 or less version.</p>";

$__information['lxupdate_updateform_lxupdateinfo_pre'] = "<p>Click 'Update Now' for update. Update process will be run in background. The same way with <b>'sh /script/cleanup'</b>.</p>".
	"<p>If Kloxo-MR panel not able to access after update, need running 'sh /script/restart' from ssh.</p>";

$__information['releasenote_list__pre'] = "<p>Information about Kloxo-MR release since first release.</p>";

$__information['client_updateform_password_pre'] = "<p>Update password for <b>[%_client_%]</b>.</p>".
	"<p>Better use 8 character or more with a combination of uppercase, lowercase and numbers.</p>".
	"<p>Click 'Generate Password' if you want Kloxo-MR to generate password for you.</p>";

$__information['auxiliary_addform__pre'] = "<p>You can add/delete 'auxiliary login' (always with '.aux' postfix) as alternative id to login.</p>".
	"<p>Don't give main account to anyone, instead 'auxiliary login'.</p>";

$__information['all_auxiliary_list__pre'] = "<p>List all auxiliary login which you and your customer owned.</p>";

$__information['ffile_updateform_upload_s_pre'] = "<p>You can upload backup file from your 'local pc'.</p>".
	"<p>Maximum upload size is 2000MB (2GB - 48MB).</p>";

$__information['ffile_updateform_download_from_http_pre'] = "<p>You can upload backup file via FTP from other remote server.</p>";

$__information['ffile_updateform_download_from_ftp_pre'] = "<p>Setup for remote ftp server.</p>";

// $__information['ffile_show__pre'] = "<p>No information...</p>";

// MR -- it's for 'processed log'
$__information['domaindefault_updateform_update_pre'] = "<p>Click 'Update' to remove log in <b>'/home/[%_client_%]/__processed_stats/'</b>.</p>";

// $__information['utmp_list__pre'] = "<p>No information...</p>";

// $__information['ftpsession_list__pre'] = "<p>No information...</p>";

$__information['client_updateform_shell_access_pre'] = "<p>You can limiting SSH access for <b>[%_client_%]</b>.</p>";

$__information['general_updateform_scavengetime_pre'] = "<p>The purpose of this feature is 'book keeping jobs' for disk/traffic/etc.</p>";

$__information['general_updateform_maintenance_pre'] = "<p>Send message 'System Under Maintenance' to your client.</p>";

$__information['dirindexlist_a_addform__pre'] = "<p>By default, ".
	"'index.php, index.html, index.shtml, index.htm, default.htm, Default.aspx, Default.asp, index.pl' files are already setup.</p>".
	"<p>You can add other index files here.</p>";

$__information['client_updateform_disable_url_pre'] = "<p>All your children will automatically inherit your Disable url parameters.</p>".
	"<p>To enforce your 'Disable URL' on them, just disable their 'can Set Disable URL' in the permission settings.</p>".
	"<p>This is the url to which a domain will be redirected to when it is disabled.</p>";

$__information['client_updateform_skeleton_pre'] = "<p>This is the archive of the skeleton directory which will be copied to ".
	"your domain home directory when it is created. The archive will be unzipped into a newly created domain's home directory.</p>".
	"<p>You can use <b>&lt;%domainname%&gt; , &lt;%clientname%&gt;</b>  inside the index.html file, ".
	"and the variables will be replaced properly with the correct values.</p>";

$__information['notification_updateform_update_pre'] = "<p>You can configure 'notifications' here.</p>";

$__information['client_updateform_miscinfo_pre'] = "<p>Add 'miscellaneous information' for <b>[%_client_%]</b>.</p>";

$__information['sp_childspecialplay_updateform_skin_pre'] = $__information['sp_specialplay_updateform_skin_pre'];

$__information['psrole_a_addform__pre'] = "<p>No information...</p>";

$__information['pserver_updateform_reboot_pre'] = "<p>Click 'Reboot' to reboot your <b>[%_server_%]</b>.</p>";

$__information['pserver_updateform_poweroff_pre'] = "<p>Click 'Poweroff' to poweroff/shutdown your <b>[%_server_%]</b>.</p>";

$__information['service_list__pre'] = "<p>Click 'SB' for start/stop service when the server is rebooted. Click 'State' to start/stop service now.</p>".
	"<p>Kloxo-MR always monitor services with green 'SB' and try to start them periodically in case of red 'State' .</p>";

// $__information['process_list__pre'] = "<p>No information...</p>";

// $__information['component_list__pre'] = "<p>No information...</p>";

// $__information['llog_show__pre'] = "<p>No information...</p>";

// $__information['ffile_updateform_content_pre'] = "<p>No information...</p>";

// $__information['driver_updateform_update_pre'] = "<p>No information...</p>";

$__information['pserver_updateform_timezone_pre'] = "<p>Choose 'timezone' for your <b>[%_server_%]</b>. Default is 'Europe/London'.</p>";

$__information['pserver_updateform_commandcenter_pre'] = "<p>You can execute ssh command here. Example: 'sh /script/sysinfo'.</p>";

$__information['sshclient_show__pre'] = "<p>SSH access here (using java-based ssh emulator) for your <b>[%_server_%]</b>.</p>".
	"<p>Need java-enabled in your browser.</p>";

// $__information['traceroute_list__pre'] = "<p>No information...</p>";

$__information['phpini_updateform_edit_pre'] = $__information['phpini_updateform_edit_admin_pre'];

$__information['phpini_updateform_extraedit_pre'] = "<p>You can change 'advanced' PHP settings here.</p>";

$__information['mysqldb_addform__pre'] = "<p>Add your MySQL database here. 'User Name' is the same as 'DB Name' and will be cutoff to 16 chars.</p>" .
	"<p>Note:<br>" .
	"1. Database name:<br>" .
	"- Valid character: a-z 0-9 _<br>" .
	"- Minimum 2 chars; maximum: 64 chars; last char NOT _<br>" .
	"2. Password:<br>" .
	"- Valid character: a-z A-Z 0-9<br>" .
	"- Minimum 8 chars; maximum: 64 chars</p>";

$__information['dbadmin_addform__pre'] = "<p>". $__emessage['e_no_dbadmin_entries'] . "</p>";

$__information['sslcert_list__pre'] = "<p>List certificate which you able to use/modificition/delete.</p>";

$__information['sslcert_addform__pre'] = $__information['sslcert_updateform_update_pre'];

$__information['sslcert_addform_uploadfile_pre'] = "<p>You can upload your certificate here.</p>".
	"<p>As alternative, you can use 'Add SSL Text' to copy-paste certificate contents.</p>" .
	"<p><b>Note</b>:" .
		"<ul>" . 
			"<li>You can combine/merge all '(---CERTIFICATE---)' file/text and upload/insert to 'Certificate File'/'Certificate' or" .
			" insert Certificate Authority/Chain/Intermediate file/text to 'Certificate Authority/Intermediate' file/text</li>" .
		"</ul>";

$__information['sslcert_addform_uploadtext_pre'] = "<p>You can copy-paste your certificate contents here.</p>".
	"<p>As alternative, you can use 'Add SSL File' to upload certificate files.</p>" .
	"<p><b>Note</b>:" .
		"<ul>" . 
			"<li>You can combine/merge all '(---CERTIFICATE---)' file/text and upload/insert to 'Certificate File'/'Certificate' or" .
			" insert Certificate Authority/Chain/Intermediate file/text to 'Certificate Authority/Intermediate' file/text</li>" .
		"</ul>";

$__information['sslcert_addform_letsencrypt_pre'] = "<p>You can use <b>Let's Encrypt</b> free SSL here. </p>" . 
	"Subdomain must be part of domain SSL and always create for domain only and add subdomain in SAN entry. " .
	"</p>" .
	"<p><b>Note</b>:" .
		"<ul>" .
			"<li>Expire in 90 days and then need renew (update) before expire</li>" .
			"<li>Use 'Add SSL Link' to parent SSL (domain SSL) for activate subdomain SSL</li>" .
			"<li>Possible 100 SANs (Subject Alternative Names) for each domain</li>" .
			"<li>If using 'Remote Mail' for domain, remove 'webmail.domain.com' from 'Subject Alternative Name (SAN)'</li>" .			
		"</ul>";

$__information['sslcert_addform_link_pre'] = "<p>For wildcards ('*') or 'Let's Encrypt' SSL, SSL for subdomain just link to their parent SSL";

$__information['serverweb_updateform_edit_pre'] = $__information['webserver_config'];

$__information['ftpuser_addform__pre'] = $__information['ftpuser_admin'];

$__information['servermail_updateform_spamdyke_pre'] = "<p>No information...</p>";

$__information['mail_graylist_wlist_a_addform__pre'] = "<p>No information...</p>";

// $__information['client_list__pre'] = "<p>No information...</p>";

// $__information['all_client_list__pre'] = "<p>No information...</p>";

$__information['client_addform_wholesale_pre'] = "<p>Add 'Wholesale Reseller' if you want your Client able to resell their plan.<p>".
	"<p>Different between 'Wholesale' and 'regualar' Reseller is 'Wholesale' able to resell their plan in master/slave server.</p>";

$__information['client_addform_reseller_pre'] = "<p>Add 'Reseller' if you want your Client able to resell their plan.</p>" .
	"<p>Note:<br>" .
	"1. Username:<br>" .
	"- Valid character: a-z 0-9 _<br>" .
	"- Minimum: 2 chars; maximum: 31 chars; first char must a-z; last char NOT _<br>" .
	"2. Password:<br>" .
	"- Valid character: a-z A-Z 0-9<br>" .
	"- Minimum 8 chars; maximum: 64 chars</p>";

$__information['client_addform_customer_pre'] = "<p>Add 'Customer' if you don't want your Client not able to resell their plan.</p>" .
	"<p>Note:<br>" .
	"1. Username:<br>" .
	"- Valid character: a-z 0-9 _<br>" .
	"- Minimum: 2 chars; maximum: 31 chars; first char must a-z; last char NOT _<br>" .
	"2. Password:<br>" .
	"- Valid character: a-z A-Z 0-9<br>" .
	"- Minimum 8 chars; maximum: 64 chars</p>";
	
$__information['domain_updateform_limit_pre'] = "<p>You can setting up <b>[%_domain_%]</b> limiting here.</p>";

$__information['addondomain_addform_parked_pre'] = "<p>No information...</p>";

$__information['addondomain_addform_redirect_pre'] = "<p>No information...</p>";

$__information['dirprotect_addform__pre'] = "<p>No information...</p>";

$__information['web_updateform_docroot_pre'] = "<p>Default directory structure is '/home/[%_client_%]/[%_domain_%]'.</p>".
	"<p>You can change to other but better using default. Left blank if you want to use default.</p>";

$__information['web_updateform_configure_misc_pre'] = "<p>No information...</p>";

$__information['webhandler_addform__pre'] = "<p>No information...</p>";

$__information['webmimetype_addform__pre'] = "<p>No information...</p>";

$__information['redirect_a_addform_local_pre'] = "<p>No information...</p>";

$__information['redirect_a_addform_remote_pre'] = "<p>No information...</p>";

$__information['web_updateform_custom_error_pre'] = "<p>No information...</p>";

$__information['domain_updateform_changeowner_pre'] = "<p>No information...</p>";

$__information['mailforward_list__pre'] = "<p>No information...</p>";

$__information['mailforward_addform_forward_pre'] = "<p>No information...</p>";

$__information['mailforward_addform_alias_pre'] = "<p>No information...</p>";

$__information['mmail_updateform_catchall_pre'] = "<p>No information...</p>";

$__information['mmail_updateform_remotelocalmail_pre'] = "<p>Choose 'remote' if using remote mail server (like GoogleApps). " .
	"Without it, sendmail as 'local'.</p>" .
	"<p>Webmail URL is optional (left 'blank').</p>";

$__information['mailinglist_list__pre'] = "<p>No information...</p>";

$__information['mailinglist_addform__pre'] = "<p>No information...</p>";

$__information['mailinglist_show__pre'] = "<p>No information...</p>";

$__information['mailinglist_updateform_update_pre'] = "<p>No information...</p>";

$__information['mailinglist_mod_a_addform__pre'] = "<p>No information...</p>";

$__information['mailinglist_updateform_editfile_pre'] = "<p>No information...</p>";

$__information['listsubscribe_addform__pre'] = "<p>No information...</p>";

$__information['updateform_cron_mailto_pre'] = "<p>Cron task will reported to 'Mail To'.</p>" .
	"<p><b>Note</b>: add '/usr/local/lxlabs/kloxo/etc/flag/enablecronforall.flg' to enable Cron for all clients.</p>";

$__information['cron_addform_simple_pre'] = "<p>Add something like 'sh /script/restart-all' in 'Command'.</p>".
	"<p>You can see cron activity in 'Log Manager'.</p>";

$__information['cron_addform_complex_pre'] = $__information['cron_addform_simple_pre'];

$__information['serverftp_updateform_update_pre'] =  "<p>Basic setting for FTP server.</p>";

$__information['client_updateform_information_pre'] =  "<p>As admin, if you have <url:a=list&c=auxiliary>auxillary login</url> for admin, you can set 'Disable Admin Login' and use auxillary login for login purpose.</p>";

$__information['jailed_show__pre'] =  "<p>Enable 'Jailed' make 'chroot' client access and only permit to their document root ('/home/user').</p>";

$__information['general_delete_warning'] = 
	"<p>- <b>ATTENTION for deletion</b>:" .
	"<ul>" .
		"<li>CLIENT - delete clients will delete their domains and mysql databases</li>" .
		"<li>DOMAIN - delete domains will delete their websites, mail accounts, ftp users and stats</li>" .
		"<li>FTP User - delete ftpusers will delete their ftpusers</li>" .
		"<li>DATABASE - delete databases will delete their databases</li>" .
		"<li>and other deletions...</li>" .
	"</ul>" .
	"<p>- If many domains/sudomains are pointing to the same folder then data won't be removed until only one folder remains.</p>" .
	"<p>- No recovery mechanism except via restore from backup.</p>";

$__information['general_delete_warning_customer'] = 
	"<p>- <b>ATTENTION for deletion</b>:" .
	"<ul>" .
		"<li>DOMAIN - delete domains will delete their websites, mail accounts, ftp users and stats</li>" .
		"<li>FTP User - delete ftpusers will delete their ftpusers</li>" .
		"<li>DATABASE - delete databases will delete their databases</li>" .
		"<li>and other deletions...</li>" .
	"</ul>" .
	"<p>- If many domains/sudomains has the same document root then data won't be removed until only one domain/subdomain is pointing to this particular folder.</p>" .
	"<p>- No recovery mechanism except via restore from backup.</p>";

$__information['dnsslave_addform__pre'] = "<p>Add 'Slave Domain' for domain want to slaved and add their 'Master IP Address' where domain exists in primary location (must be 'allow-transfer' for this server IP address in their primary DNS server setting).</p>";

$__information['watchdog_addform__pre'] = "<p>Settings:" .
	"<ul>" .
		"<li>Servicename - service identity</li>" .
		"<li>Watchdog activated - enable or disable Watchdog service monitoring</li>" .
		"<li>Port - service port number</li>" .
		"<li>Action - command required to restart service</li>" .
	"</ul></p>" .
	"<p><b>Note</b>: - usually 'Action' to restart service is 'service XXX restart' or '/etc/init.d/XXX restart'.</p>";

$__information['updateform_webselector_pre'] =
	"<p>In web proxy (like Nginx-proxy), select 'back-end' in 'Web Selected' mean execute php in back side (Apache). " .
	"Otherwise, select 'front-end' mean execute php in front side (like Nginx).</p>" .
	"<p>If enable 'multiple php', possible select php for website under 'Php Selected'. " .
	"Select '--Default--' mean use php where declare in 'Php Used' under 'Webserver Configure'.</p>";