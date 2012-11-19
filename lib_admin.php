<?
if(!isset($_SESSION)) session_start();
if (!array_key_exists("c_admin",$_SESSION) || !$_SESSION["c_admin"])
{
	$txt = <<<TEXT
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
   <head>
      <title>Session expired!</title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta http-equiv="cache-control" content="no-cache" />
   </head>
   <body style="font-family:arial;">
  	<h1 style="color:#0a328c;font-size:1.0em;">SESSION EXPIRED</h1>

	<p style="font-size:0.8em;">Please <a href="/">re-login</a>.</p>
   </body>
  </html>
TEXT;
	exit($txt);
}
?>
