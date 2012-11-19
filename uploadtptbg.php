<?
/*
 * See comments at lib_upload.php
 */
define("MAX_FILE_SIZE",1024); // Kb

if(!isset($_SESSION)) session_start();
require_once "lib_ref_admin.php";
require_once "lib_tpt.php";

$IE = (preg_match("/msie/i",$_SERVER["HTTP_USER_AGENT"]) || preg_match("/internet explorer/i",$_SERVER["HTTP_USER_AGENT"]));

if ($IE)
{
?>
<HTML>
<HEAD>
<style type="text/css">
	body {background-color:#6b7b71;font-family:arial,sans-serif;font-size:12px;color:#FFFFCC;}
</style>
</HEAD>
<BODY>
<?
}

function _error($msg)
{
	global $IE;
	if ($IE)
	{
		echo $msg."\r\n";
		echo "<script>self.focus();</script>\r\n";
		echo "</BODY></HTML>";
	}
	else
		echo $msg;
	die();
}

if ($_FILES["file"]["error"] > 0) _error ("Error: " . $_FILES["file"]["error"]);

$ext = "";
if ($_FILES["file"]["type"] == "image/gif") $ext = $aTBGExtensions[0];
elseif ($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/pjpeg") $ext = $aTBGExtensions[1];
elseif ($_FILES["file"]["type"] == "image/png") $ext = $aTBGExtensions[2];
if (!$ext) _error ("Error: not a JPEG, PNG or GIF image (".$_FILES["file"]["type"].")");

$size = $_FILES["file"]["size"] / 1024;
if ($size > MAX_FILE_SIZE) _error ("Error: file size exceeds ".MAX_FILE_SIZE." Kb");
//
if ($error = uploadTemplateBackground($_FILES["file"]["tmp_name"], $ext))
	_error($error);
else if (!$IE)
	die("OK");
else
{
?>

<script>
opener.location.reload();
window.close();
</script>

<?
}
?>
</BODY>
</HTML>
