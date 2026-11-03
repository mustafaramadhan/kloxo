<?php

chdir("..");
include_once "lib/html/displayinclude.php";

$kloxo_mr_version = $sgbl->__ver_full;

init_language();

$cgi_clientname = $ghtml->frm_clientname;
$cgi_class = $ghtml->frm_class;
$cgi_password = $ghtml->frm_password;
$cgi_forgotpwd = $ghtml->frm_forgotpwd;
$cgi_email = $ghtml->frm_email;

$cgi_token = $ghtml->frm_token;

$cgi_classname = 'client';

if ($cgi_class) {
	$cgi_classname = $cgi_classname;
}

$accountlist = array('client' => "Kloxo Account", 'domain' => 'Domain Owner', 'mailaccount' => "Mail Account");
$progname = $sgbl->__var_program_name;

if ($sgbl->is_this_slave()) {
	print("Slave Server\n");

	exit;
}

$logfo = db_get_value("general", "admin", "login_pre");
$logfo = str_replace("<%programname%>", $sgbl->__var_program_name, $logfo);

if (!$cgi_forgotpwd) {
//	if (session_status() == PHP_SESSION_NONE) {
	if(!isset($_SESSION)) {
		session_start();
	}

	$ghtml->print_message();

	$_SESSION['frm_token'] = mt_rand();
?>
<!--- include start --->

<?php

	if ((isset($_SESSION['last_login_time'])) && (isset($_SESSION['num_login_fail']))) {
		$t = time();
		$s = $_SESSION['last_login_time'];
		$d = $t - $s;
		$n = $_SESSION['num_login_fail'];

		$m = $g_language_mes->__emessage['blocked'];
		$r = $g_language_mes->__emessage['blocked_remaining'];

		if ($n == 5) {
			if (intval($t - $s) < intval(10*60)) {
				$msg = '
<div style="margin: 4px auto; width: 450px; padding: 4px; color: #000; background-color: #fdb; border: 1px solid #ccc">
<div id="countdown" align="center"></div>
<script>
	var countdown = document.getElementById("countdown");
	//var totalTime = 600;
	var totalTime = ' . intval(600 - $d) . ';
	function pad(n) {
		return n > 9 ? "" + n : "0" + n;
	}
	var original = totalTime;
	function padMinute(n) {
		return original >= 600 && n <= 9 ? "0" + n : "" + n;
	}
	var interval = setInterval(function() {
		updateTime();
		if(totalTime == -1) {
			clearInterval(interval);
		//	return;
		//	self.location = self.location.href;
			self.location = "/login/";
		}
	}, 1000);

	function displayTime() {
		var minutes = Math.floor(totalTime / 60);
		var seconds = totalTime % 60;
		minutes = "<span>" + padMinute(minutes).split("").join("</span><span>") + "</span>";
		seconds = "<span>" + pad(seconds).split("").join("</span><span>") + "</span>";
	//	countdown.innerHTML = "Blocked remaining: " + minutes + ":" + seconds;
		countdown.innerHTML = "' . $m . ' ' . $r . ': " + minutes + ":" + seconds;
	}
	function updateTime() {
		displayTime();
		totalTime--;
	}
	updateTime();
</script>
</div>';
			} else {
				$_SESSION['num_login_fail'] = 0 ;
			}
		} else {
			$_SESSION['last_login_time'] = time();
		}
	} else {
		$msg="";
	}
?>

<div align="center">
	<div class="login">
		<div class="login-form">
		 	 <div align="center"><font size="5" color="red"><b> Login </b></font></div>
		 	 <br/>

		 	 <form name="loginform" action="/lib/php/" onsubmit="ctrim(this.frm_clientname.value) ; ctrim(this.frm_password.value) ; encode_url(loginform) ; return fieldcheck(this)" method="post">
		 		 <div class="form-block">
 		 		<div class="inputlabel">Username</div>
 		 		<input name="frm_clientname" type="text" class="inputbox" size="30"/>

 		 		<div class="inputlabel">Password</div>
	 		 		<input name="frm_password" type="password" class="passbox" size="30"/>
 			 		<br/>
	 		 		<input type="hidden" name="frm_token" value="<?php echo $_SESSION['frm_token']; ?>"/>
	 		 		<div align="left"><input type="submit" class="button" name="login" value="Login"/></div>
				</div>
		 	 </form>
		</div>
		<div class="login-text">
			<div class="ctr"><img src="/theme/login/icon.gif" width="64" height="64" alt="security"/></div>
			<?=$logfo?>
			<a class="forgotpwd" href="javascript:document.forgotpassword.submit()">Forgot Password?</a>
			<form name="forgotpassword" method="post" action="/login/">
			<input type="hidden" name="frm_forgotpwd" value="1"/>
			</form>

			<script>
				document.loginform.frm_clientname.focus();
			</script>
		</div>
		<div class="clr"></div>
	</div>
	<div style="margin: 4px auto; width: 200px; padding: 4px; color: #fff; background-color: #000">Kloxo-MR <?php echo $kloxo_mr_version ?></div>
<?php echo $msg;?>

</div>

<div id="break"></div>

<?php
	if (if_demo()) {
		print("<div align='center'>");
		include_once "lib/demologins.php";
		print("</div>");
	}
} elseif ($cgi_forgotpwd == 1) {
?>

<div align="center">
	<div class="login">
		<div class="login-form">
			<div align="center"><font name=Verdana size=5 color=red><b> Forgot Password </b></font></div>
			<br/>

			<form name="sendmail" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
				<div class="form-block">
				<div class="inputlabel">Username</div>
 		 		<input name="frm_clientname" type="text" class="inputbox" size="30"/>

 		 		<div class="inputlabel">Email Id</div>
	 		 		<input name="frm_email" type="text" class="passbox" size="30"/>
 			 		<br/>
 			 		<input type="hidden" name="frm_forgotpwd" value="2"/>
	 		 		<div align="left"><input type="submit" class="button" name="forgot" value="Send"/></div>
				</div>
			</form>
		</div>
		<div class="login-text">
		<div class="ctr"><img src="/theme/login/icon1.gif" width="64" height="64" alt="security"/></div>
			<p>Welcome to <?php echo $sgbl->__var_program_name; ?></p>
			<p>Use a valid username and email-id to get password.</p>
			<br/>
			<a class=forgotpwd href="javascript:history.go(-1);"><font color="black"><u>Back to login</u></a>
		</div>

		<script>
			document.sendmail.frm_clientname.focus();
		</script>

		<div class="clr"></div>
	</div>
	<div style="margin: 4px auto; width: 200px; padding: 4px; color: #fff; background-color: #000">Kloxo-MR <?php echo $kloxo_mr_version ?></div>
</div>

<div id="break"></div>

<?php
} elseif ($cgi_forgotpwd == 2) {
	$progname = $sgbl->__var_program_name;
	$cprogname = ucfirst($progname);

	$cgi_clientname = $ghtml->frm_clientname;
	$cgi_email = $ghtml->frm_email;


	htmllib::checkForScript($cgi_clientname);
	$classname = $ghtml->frm_class;

	if (!$classname) {
		$classname = getClassFromName($cgi_clientname);
	}

	if ($cgi_clientname != "" && $cgi_email != "") {
		$tablename = $classname;
		$rawdb = new Sqlite(null, $tablename);
		$email = $rawdb->rawQuery("select contactemail from $tablename where nname = '$cgi_clientname';");


		if ($email && $cgi_email == $email[0]['contactemail']) {
			$rndstring = randomString(8);
			$pass = crypt($rndstring, '$1$'.randomString(8).'$');

			$rawdb->rawQuery("update $tablename set password = '$pass' where nname = '$cgi_clientname'");
			$mailto = $email[0]['contactemail'];
			$name = "$cprogname";
			$email = "Admin";

			$cc = "";
			$subject = "$cprogname Password Reset Request";
			$message = "\n\n\nYour password has been reset to the one below for your $cprogname login.\n";
			$message .= "The Client IP address which requested the Reset: {$_SERVER['REMOTE_ADDR']}\n";
			$message .= 'Username: ' . $cgi_clientname . "\n";
			$message .= 'New Password: ' . $rndstring . '';

			//$message = nl2br($message);

			lx_mail(null, $mailto, $subject, $message);

			$ghtml->print_redirect("/login/?frm_smessage=password_sent");

		} else {
			$ghtml->print_redirect("/login/?frm_emessage=nouser_email");
		}
	}
}
?><!--- include end --->
