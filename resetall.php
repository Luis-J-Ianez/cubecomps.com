<?
session_start();
require_once "lib_ref_admin.php";
require_once "db.php";

$result = strict_query("SELECT admin_pw FROM competitions WHERE id=".$_SESSION["c_id"]);
if ($result && sql_num_rows($result)==1)
{
        if (cased_mysql_result($result,0,"admin_pw")==$_POST["pw"])
        {
                strict_query("DROP TABLE $eventstable, $compstable, $regstable, $timestable");
                sql_close();
                $_SESSION["c_pw"]=$_POST["pw"];
                header("Location: identification.php\r\n");
        }
        else
        {
                $color = "#6b7b71";
                $light_color = "#b0c7b4";
                $dark_color = "#0a1414";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Erroneous password</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
        body {font-family:verdana,sans-serif;font-size:11px;margin:10px 10px;background-color:<?=$color?>;color:#FFFFCC;}
        .header {background-color:<?=$dark_color?>;font-size:12px;padding:4px 6px;margin-bottom:4px;}
        .errors {margin-left:14px;}
</style>
</HEAD>
<BODY>
<div class=header><b>Erroneous password</b></div>
<div class=errors>
Your database HASN'T BEEN deleted!<br>
<a href='misc.php'>[back]</a>
<div>
</BODY>
</HTML>
<?
        }
}
sql_close();
?>
