<?PHP

    // HN_URL_rewrite :: example 1

    $root = (strrpos($_SERVER['DOCUMENT_ROOT'],'/')==strlen($_SERVER['DOCUMENT_ROOT'])-1) ? substr($_SERVER['DOCUMENT_ROOT'], 0, strlen($_SERVER['DOCUMENT_ROOT'])-1) : $_SERVER['DOCUMENT_ROOT'];
    require_once($root.'/hn_urlrewrite.class.php');


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
<TITLE>EXAMPLE 1</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
<META NAME="Description" CONTENT="Beschreibung soll max 150 Zeichen sein">
<META NAME="Keywords" CONTENT="Schlagworte, sollen insgesamt max. 874 Zeichen lang sein, und, Kommasepariert, wie, hier,">
</head>

<body>
<h1>PHP-Class hn_urlrewrite :: EXAMPLE 1</h1>
<h3>no specific scripts are registered</h3>
<h3>this example uses the buffer methods</h3>
<hr>
<?PHP


    // Build class instance
    $rewrite  = new hn_urlrewrite();

    // start buffer
    $rewrite->buffer_start();

?>
<p><b>example 1</b><br>
  <a href="/hn_urlrewrite_example/example1.php?param1=value1&param2=value2&param3=value3" title="test">
           /hn_urlrewrite_example/example1.php?param1=value1&param2=value2&param3=value3</a>
</p>
<p><b>example 2</b><br>
  <a href="/hn_urlrewrite_example/example2.php?param1=value1&param2=value2&param3=value3">
           /hn_urlrewrite_example/example2.php?param1=value1&param2=value2&param3=value3</a>
</p>
<p><b>example 3</b><br>
  <a href="/hn_urlrewrite_example/sub1/example3.php?name=JohnDoe&mail=john@somehost.com&id=1234567890abcXYZ">
           /hn_urlrewrite_example/sub1/example3.php?name=JohnDoe&mail=john@somehost.com&id=1234567890abcXYZ</a>
</p>
<?PHP


    // stop buffering and rewrite pagecontent
    $rewrite->buffer_end();

    // retrieve rewritten page as string
    $new_page = $rewrite->page;


    // do something with the string
    $CONTENT = $new_page."<br>\n<hr>\n<br>\n<pre>".htmlentities($new_page)."</pre>\n<br>\n<hr>\n<h3>retrieved values:</h3>\n";
    foreach($_GET as $k=>$v)
    {
        $CONTENT .= "<p>".htmlentities("$k=>$v")."</p>\n";
    }
    $CONTENT .= "<hr><p>QueryString: ".htmlentities($_SERVER['QUERY_STRING'])."</p>\n";


    // Output
    echo $CONTENT;

?>
</body>
</html>
