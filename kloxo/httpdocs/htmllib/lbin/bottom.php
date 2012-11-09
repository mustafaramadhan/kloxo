<?php 
chdir("../../");

include_once "htmllib/lib/displayinclude.php";
//initProgram();
?> 
	<link href="/htmllib/css/header_new.css" rel="stylesheet" type="text/css" />
	<link href="/htmllib/css/common.css" rel="stylesheet" type="text/css" />
	<?php 
$ghtml->print_jscript_source("/htmllib/js/lxa.js");
print("<body topmargin=\"0\" leftmargin=\"0\" bottommargin=\"0\" rightmargin=\"0\"> ");
print("<div id=statusbar  style='background:#f0f0ff;scroll:auto;height:100%;width:100%;border-top:1px solid #aaaacf;margin:0 0 0 0:vertical-align:top;text-align:top'></div> </body> ");
