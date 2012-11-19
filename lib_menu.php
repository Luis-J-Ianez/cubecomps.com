<?
function echoMenu($selectedItem,$functionGoTo = null,$onlyTDs = false)
{
	global $light_color;
	echo "<script>
var timerKA = setTimeout('keepAlive();',600000);

function keepAlive()
{
	var req = createXMLHttpRequest();
	req.open ('GET', 'keepalive.php');
	req.send (null);
	//
	timerKA = setTimeout('keepAlive();',600000);
}

</script>

<style>
	table.t_tabs {border-style:none;color:#CCFF00;}
	table.t_tabs td {font-size:20px;font-weight:bold;padding:0 20px}
	td.t_sel {text-shadow:1px 1px $light_color;}
	td.t_sel:hover {background-color:$light_color;}
</style>
";
	if (@$_SESSION["c_admin"])
	{
		$aPages = array("events","competitors","results","certificates","misc");
		if (!$onlyTDs)
			echo "<table class=t_tabs><tr>" . PHP_EOL;
		for($x=0;$x<count($aPages);$x++)
			if ($x == $selectedItem)
				echo "<td>".strtoupper($aPages[$x])."</td>" . PHP_EOL;
			elseif ($functionGoTo) 
				echo "<td class=t_sel><a style='cursor:pointer;' onclick='$functionGoTo(\"".$aPages[$x].".php\");'>".strtoupper($aPages[$x])."</a></td>" . PHP_EOL;
			else
				echo "<td class=t_sel><a href='".$aPages[$x].".php'>".strtoupper($aPages[$x])."</a></td>" . PHP_EOL;
		if (!$onlyTDs)
			echo "</tr></table>" . PHP_EOL;
	}
}
?>
