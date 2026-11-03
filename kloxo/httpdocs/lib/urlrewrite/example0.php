<?PHP

    // HN_URL_rewrite :: example 0

    $root = (strrpos($_SERVER['DOCUMENT_ROOT'],'/')==strlen($_SERVER['DOCUMENT_ROOT'])-1) ? substr($_SERVER['DOCUMENT_ROOT'], 0, strlen($_SERVER['DOCUMENT_ROOT'])-1) : $_SERVER['DOCUMENT_ROOT'];
    require_once($root.'/hn_urlrewrite.class.php');


    // Build class instance
    $rewrite  = new hn_urlrewrite();

    // start buffer
    $rewrite->buffer_start();   // optionally it can send headers against caching by passing a TRUE to the method


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
<TITLE>EXAMPLE 0</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
<META NAME="Description" CONTENT="Beschreibung soll max 150 Zeichen sein">
<META NAME="Keywords" CONTENT="Schlagworte, sollen insgesamt max. 874 Zeichen lang sein, und, Kommasepariert, wie, hier,">
</head>

<body>
<h1>PHP-Class hn_urlrewrite :: EXAMPLE 0</h1>
<h3>no specific scripts are registered</h3>
<h3>this example uses the buffer methods<br>and also gzip compression, if your browser support this</h3>
<hr>
<p><a href="/hn_urlrewrite_example/example1.php?param1=value1&param2=value2&param3=value3" title="test">example1.php?param1=value1&param2=value2&param3=value3</a></p>
<p><a href="/hn_urlrewrite_example/example2.php?param1=value1&param2=value2&param3=value3">example2.php?param1=value1&param2=value2&param3=value3</a></p>
<p><a href="/hn_urlrewrite_example/sub1/example3.php?name=JohnDoe&mail=john@somehost.com&id=1234567890abcXYZ">example3.php?name=JohnDoe&mail=john@somehost.com&id=1234567890abcXYZ</a></p>
</body>
</html>
<?PHP


    // stop buffering and rewrite pagecontent
    $rewrite->buffer_end();         // optionally it can rewrite img-tags too, when passing TRUE to the method!


    // output rewritten page
    $rewrite->send_page(TRUE);      // optionally it can compress content if browser supports this


    // or: $string = $rewrite->page; //if you don't want direct output

?>
