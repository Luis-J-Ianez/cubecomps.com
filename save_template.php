<?
require_once "lib_ref_admin.php";
require_once "lib_tpt.php";

$language = $_GET["l"];
$text = (isset($_GET["t"]) ? $_GET["t"] : null);
if (!$language) die("Invalid calling");
saveTemplate($language,$text);
echo "OK";
?>
