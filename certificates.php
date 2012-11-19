<?
require_once "lib_admin.php";
require_once "lib_tpt.php";

$color = "#6b7b71";
$light_color = "#b0c7b4";
$dark_color = "#0a1414";

$fname = "tpt_".$_SESSION["c_id"];
$template = readTemplate($fname,$language);
if ($template)
	$tptExists = true;
else
{
	$language = defaultTemplateLanguage();
	$template = readTemplate($language,$language);
	$tptExists = false;
}
if (!$template) die("Impossible to load a valid template");

?>
<!DOCTYPE HTML>
<html>
<head>
<TITLE><?=$_SESSION["c_name"]?></TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
	body {font-family:arial,sans-serif;font-size:14px;background-color:<?=$color?>;color:#2a3837;}
	td {font-size:14px;}
	a {color:black;font-weight:bold;text-decoration:none;}
	a:hover {color:#CCFF00;}
	.header {color:white;background-color:<?=$dark_color?>;font-size:14px;font-weight:bold;padding:4px 10px;margin-bottom:4px;}
	.topts td {background-color:<?=$light_color?>;}
</style>

<script>

var changed = 1;

function setChanged(v)
{
	if (changed != v)
	{
		changed = v;
		document.getElementById("savebtn").style.display = (v?"inline":"none");
	}
}

function gotoPage(page)
{
	if (changed && !confirm("Your last changes haven't been saved yet.\nDiscard them?")) return;
	setChanged (false);
	window.location = page; 
}

</script>

</head>
<body onunload='if(changed) alert("Warning!\r\n\nYour last changes will be discarded because you navigated off this page prior to save them.")'>
<?

require_once "lib_menu.php";
echoMenu(3,"gotoPage");

?>
<TABLE class=topts width=100% cellspacing=10 cellpadding=5 border=0 style='font-size:12px;'>
<TR valign=top>
<TD>
<div class=header>Language</div>
<?

echo "<span>Your template is in <b>$language</b> | Select if you want to start from a default template in <select onclick='changeLanguage(this.value);'><option value=''>other language...</option></span>" . PHP_EOL;
$path = getTemplatesPath();
foreach (glob($path."*.tpt") as $lang)
{
	$pinfo = pathinfo($lang);
	$lang = $pinfo["filename"];
	if ($tptExists || $lang != $language)
		echo "<option value='$lang'>$lang</option>" . PHP_EOL;
}
echo "</select><p>" . PHP_EOL;
?>
<div class=header>Text</div>
<table width=100%><tr valign=top><td>
compose your text<br>
<form>
<textarea id=text name=text autocomplete=off rows=8 cols=70 oninput='textChanged();' style=''>
<?=htmlspecialchars($template->getV1(), ENT_QUOTES)?>
</textarea><p>
<span id=savebtn><input type=button value="save changes" onclick="saveChanges();"></span>
</form>
<script>
var wtpthelp = 0;
</script>
<a href=certhelp.php target=w_tpthelp onclick='wtpthelp=window.open("", "w_tpthelp", "width=600, height=540, location=0, scrollbars=0, resizable=0"); wtpthelp.focus();' style='font-size:12px;'>need help?</a>
</td><td height=100% width=100%>
text sample<br>
<div style='background-color:white;width:100%;font-family:serif;font-size:16px;color:black;'>
<div id=sample style='padding:30px;'></div>
</div>
</td></tr>
</table>

<table width=100%><tr valign=top>
<td width=70%>
<div class=header>Background</div>
<img src='
<?

echo ($thumbName = getTemplateBackgroundThumbFilename($thumbExists));

?>
' style='float:left;margin-right:12px;'>
<b>Upload a background designed by you<?=($thumbExists?"...":"")?></b>
<p>
<?
require_once "lib_upload.php";
$maxSize = 1024; // Kb
echoUploadForm(
	"uploadtptbg.php", 
	array("image/gif","image/jpeg","image/pjpeg","image/png"),
	$maxSize,
	"image/*",
	"window.location.reload();");
?>
<p style='font-size:10px;'>Only JPEG, PNG and GIF images of <?=$maxSize?> Kb maximum. Regardless of the dimension and resolution of the uploaded file, the image will be stretched 287 mm width and 200 mm height in order to cover all the certificate background.<p>
<?
if ($thumbExists)
{
?>
<b>...or revert to the predefined background</b><br>
<form action="deletetptbg.php" method="post" onclick='if (confirm("Are you sure you want to delete your design from the server and revert to the default one?")) this.submit(); else return false;'>
<input type="submit" name="submit" value="use the default background">
</form>
<?
}
?>
</td><td>
<div class=header>Printing</div>
<input type=checkbox id=cbbackground checked onclick='clickPrintOption();' />background<br>
<input type=checkbox id=cbtext checked onclick='clickPrintOption();' />text<p>
<input type=button id=btnprint value='print' onclick='printAll();' />
</td></tr></table>
</TD></TR></TABLE>

<script>
function createXMLHttpRequest() 
{
	var xmlHttp=null;
	if (window.ActiveXObject) 
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	else 
		if (window.XMLHttpRequest) 
			xmlHttp = new XMLHttpRequest();
	return xmlHttp;
}

function saveChanges(otherLanguage)
{
	var req = createXMLHttpRequest();
	var URL = "save_template.php";
	if (otherLanguage)
		URL = URL + "?l="+otherLanguage;
	else
		URL = URL + "?l=<?=$language?>&t="+document.getElementById("text").value;
	req.open ("GET", encodeURI(URL), false);
	req.send (null);
	var response = req.responseText.replace(/[\s\r\n]+$/,"");
	if (response == "OK")
		setChanged (false);
	else
		alert(response);
}

function changeLanguage(value)
{
	if(value)
	{
<?
if ($tptExists)
{	
?>
		if (!confirm("This will overwrite the last saved version of the text of your template.\r\nAre you sure?")) return;
<?
}
?>
		saveChanges(value);
		document.location.reload();
	}
}

function sample()
{
	txt = document.getElementById("text").value.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
	
	while ((a = txt.indexOf("[size=")) >= 0)
	{
		if ((b = txt.indexOf("]",a+1)) >= 0)
		{
			num = txt.substring(a+6,b);
			txt = txt.replace("[size="+num+"]","<font style='font-size:"+Math.floor(num*(14/20))+"px;'>");
		}		
	}
	txt = txt.replace(/\[serif\]/g,"<font style='font-family:serif'>");
	txt = txt.replace(/\[sans\-serif\]/g,"<font style='font-family:sans-serif'>");
	txt = txt.replace(/\[b\]/g,"<b>");
	txt = txt.replace(/\[\/b\]/g,"</b>");
	txt = txt.replace(/\[br\]/g,"<br>");
	txt = txt.replace(/\[center\]/g,"<center>");
	txt = txt.replace(/\[\/center\]/g,"</center>");
	
	txt = txt.replace(/\[competitor\]/g,"John Smith");
	txt = txt.replace(/\[position\]/g,"<?=$template->wordSecond?>");
	txt = txt.replace(/\[event\]/g,"Rubik's Cube");
	txt = txt.replace(/\[score\]/g,"12.34");
	
	document.getElementById("sample").innerHTML = txt;
}

function textChanged()
{
	setChanged (true);
	sample();
}
	
function printAll()
{
	if (changed) 
		if (confirm("You must save the changes before printing\r\n\nSave now?"))
			saveChanges();
	if (!changed) 
	{
		var URI = "";
		if (document.getElementById("cbbackground").checked)
			URI += "b=1";
		if (document.getElementById("cbtext").checked)
		{
			if (URI) URI += "&";
			URI += "t=1";
		}
		URI = "produce_certificates.php?" + URI;
		window.open(URI);
	}
}

function clickPrintOption()
{
	var b1 = document.getElementById("cbbackground").checked;
	var b2 = document.getElementById("cbtext").checked;
	document.getElementById("btnprint").disabled = (!b1 && !b2);
}

setChanged (false);
sample();

</script>

</body>
</html>
<?
sql_close();
?>
