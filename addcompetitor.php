<?
include "lib_com.php";
include "lib_ref_admin.php";

if (isset($_GET["wcaid"]) && $_GET["name"] && $_GET["birthday"] && $_GET["country"] && $_GET["gender"])
{
	include "db.php";
	$res = addCom ($_GET["wcaid"],$_GET["name"],$_GET["birthday"],$_GET["country"],$_GET["gender"]);
	echo (is_int($res) ? "" : $res); 
	mysql_close();
}
else
	echo "Incorrect parameters calling \"addcompetitor\"";
?>
