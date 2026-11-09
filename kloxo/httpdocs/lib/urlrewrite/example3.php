<?PHP

    // HN_URL_rewrite :: example 3

    $root = (strrpos($_SERVER['DOCUMENT_ROOT'],'/')==strlen($_SERVER['DOCUMENT_ROOT'])-1) ? substr($_SERVER['DOCUMENT_ROOT'], 0, strlen($_SERVER['DOCUMENT_ROOT'])-1) : $_SERVER['DOCUMENT_ROOT'];
    require_once($root.'/hn_urlrewrite.class.php');



    // If we do not want all links to be rewritten,
    // we can pass an array with registered scriptnames to class instance.
    // But this only works, if configuration settings for registered scripts in class file
    // is set to FALSE, or if the array  there contains all scriptnames we want provide here.
    // The scripts must be defined as absolute URIs:
    $registered_scripts = array('/hn_urlrewrite_example/example1.php','/hn_urlrewrite_example/sub1/example3.php');



    // Build class instance and pass array with registered scripts as first param
    $rewrite  = new hn_urlRewrite($registered_scripts);




// we have html code as string $my_code and don't want/can use the buffer methods
$my_code = <<< CODE_2
<p><b>example 1</b><br>
  <a href="../example1.php?param1=value1&param2=value2&param3=value3" title="test">
           ../example1.php?param1=value1&param2=value2&param3=value3</a>
</p>
<p><b>example 2</b><br>
  <a href="../example2.php?param1=value1&param2=value2&param3=value3">
           ../example2.php?param1=value1&param2=value2&param3=value3</a>
</p>
<p><b>example 3</b><br>
  <a href="example3.php?name=JohnDoe&mail=john@somehost.com&id=1234567890abcXYZ">
           example3.php?name=JohnDoe&mail=john@somehost.com&id=1234567890abcXYZ</a>
</p>
CODE_2;



    // we rewrite our code
    $my_code = $rewrite->string_rewrite($my_code);


    // this is all, if we don't need to rewrite anything, we can free the class
    unset($rewrite);



    $CONTENT = $my_code."<br>\n<hr>\n<br>\n<pre>".htmlentities($my_code)."</pre>\n<br>\n<hr>\n<h3>retrieved values:</h3>\n";
    foreach($_GET as $k=>$v)
    {
        $CONTENT .= "<p>".htmlentities("$k=>$v")."</p>\n";
    }
    $CONTENT .= "<hr><p>QueryString: ".htmlentities($_SERVER['QUERY_STRING'])."</p>\n";



$heredoc_1 = <<< CODE_1
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
<TITLE>EXAMPLE 3</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
<META NAME="Description" CONTENT="Beschreibung soll max 150 Zeichen sein">
<META NAME="Keywords" CONTENT="Schlagworte, sollen insgesamt max. 874 Zeichen lang sein, und, Kommasepariert, wie, hier,">
</head>

<body>
<h1>PHP-Class hn_urlrewrite :: EXAMPLE 3</h1>
<h3>Scripts example1.php and example3.php are registered,<br>example2.php is not registered</h3>
<h3>this example doesn't use buffering, it uses one of the three string-rewrite-methods</h3>
<hr>
{$CONTENT}
</body>
</html>

CODE_1;


    // Output
    echo $heredoc_1;

?>
