<?
$color = "#6b7b71";
$light_color = "#b0c7b4";
$dark_color = "#0a1414";
?>
<!DOCTYPE HTML>
<html>
<head>
<TITLE>Help for certificate templates</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
	body {font-family:arial,sans-serif;font-size:14px;background-color:<?=$color?>;color:#FFFFCC;}
	b {color:#ca0;background-color:black;padding:0px 2px 2px 2px;}
	.header {color:white;background-color:<?=$dark_color?>;font-size:14px;font-weight:bold;padding:4px 10px;margin-bottom:4px;
}
</style>
</head>
<body>
<div class=header>functional tags</div>
<br>
<div style='margin-left:50px;'>
<b>[serif]</b> selects a serif font<p>
<b>[sans-serif]</b> selects a sans-serif font<p>
<b>[size=#]</b> selects a font size of #, being # any integer number<p>
<b>[b]</b> activates bold font and <b>[/b]</b> cancels it<p>
<b>[br]</b> breaks the line into a new one<p>
Text between <b>[center]</b> and <b>[/center]</b> is centered<br>
Any <i>functional</i> tag within a centered section is ignored<p>
</div>

<div class=header>content tags</div>
<br>
<div style='margin-left:50px;'>
<b>[competitor]</b> is replaced by the competitor's name<p>
<b>[position]</b> is replaced by an ordinal number in letters<p>
<b>[event]</b> is replaced by the official event name (in English)<p>
<b>[score]</b> is replaced by the score,<br>
that can be seconds, minutes, moves or a multi-blindfolded score<p>
</div>

<br><br><br><center><input type=button value=close onclick='window.close();' /></center>

</body>
</html>
