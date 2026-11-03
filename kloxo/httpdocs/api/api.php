<?php 

include "lib/html/apilib.php";

api_main();

function api_main()
{

	list($server, $port) = explode(":", $_SERVER['SERVER_NAME']);
?>
Add an Kloxo Client: <br>

<form method="get" action="http://<?php echo $_SERVER['SERVER_NAME']; ?>/bin/webcommand.php">

<input type='hidden' name='login-class' value='client'>
<input type='hidden' name='login-name' value='admin'>
<input type='hidden' name='login-password' value='lxlabs'>
<input type='hidden' name='action' value='add'>
<input type='hidden' name='class' value='client'>
<input type='hidden' name='v-type' value='customer'>
Client name:<br> 
<input type='text' name='name' value=''> <br> 
Contact Email:<br> 
<input type='text' name='v-contactemail' value=''><br> 
Password:<br> 
<input type='text' name='v-password' value=''><br> 
<?php 

	get_and_print_a_select_variable("Plan", "resourceplan", "v-plan_name");
?> 

<input type='submit' name='submit' value='submit'><br>
</form>
<?php
}


