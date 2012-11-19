<?
require_once "lib.php";
require_once "db.php";

define("DIR_TEMPLATES","templates/"); // do not use directly - use getTemplatesPath() instead
$aTBGExtensions = array("GIF","JPG","PNG");

class Template {
	public  $txt = "";
	public  $wordFirst = "";
	public  $wordSecond = "";
	public  $wordThird = "";
	public  $months = array();
	
	private $delegates = "";
	private $organizers = "";
	private $place = "";
	public  $day = 0;
	public  $month = "";
	public  $year = 0;

	function __construct($txt,$wordFirst,$wordSecond,$wordThird,$months)
	{
		if (count($months)!=12) die("Months array count is not 12");
		$this->txt = $txt;
		$this->wordFirst = $wordFirst;
		$this->wordSecond = $wordSecond;
		$this->wordThird = $wordThird;
		$this->months = $months;
	}

	private function replaceV1($fn,$v,$st)
	{	
		if (!$v)
			return $st;
		else
			return str_ireplace("<$fn>",$v,$st);
	}
	
	function extractName($txt)
	{
		$p = strpos($txt,"|");
		if ($p) $txt = substr($txt,0,$p);
		return $txt;
	}
	
	public function getData()
	{
		if (!$this->year)
		{
			$competition = strict_query("SELECT delegates, organizers, place, date_e FROM competitions WHERE id=?", array($_SESSION["c_id"]));
			$this->delegates = $this->extractName(cased_mysql_result($competition,0,"delegates"));
			$this->organizers = $this->extractName(cased_mysql_result($competition,0,"organizers"));
			$this->place = cased_mysql_result($competition,0,"place");
			$aDate = date_parse(cased_mysql_result($competition,0,"date_e"));
			$this->day = $aDate["day"];
			$this->month = $this->months[$aDate["month"]-1];
			$this->year = $aDate["year"];
		}
	}

	public function getV1()
	{
		$this->getData();
		$st = $this->txt;
		$st = $this->replaceV1("championship",$_SESSION["c_name"],$st);
		$st = $this->replaceV1("delegates",$this->delegates,$st);
		$st = $this->replaceV1("organizers",$this->organizers,$st);
		$st = $this->replaceV1("place",$this->place,$st);
		$st = $this->replaceV1("day",$this->day,$st);
		$st = $this->replaceV1("month",$this->month,$st);
		$st = $this->replaceV1("year",$this->year,$st);
		$st = str_ireplace("<","[",$st);
		$st = str_ireplace(">","]",$st);
		return $st;
	}
}

function defaultTemplateLanguage()
{
	switch($_SESSION["c_country"])
	{
	case "AR":
	case "BO":
	case "CL":
	case "CO":
	case "CR":
	case "CU":
	case "DO":
	case "EC":
	case "SV":
	case "GQ":
	case "GT":
	case "HN":
	case "MX":
	case "NI":
	case "PA":
	case "PY":
	case "PE":
	case "ES":
	case "UY":
	case "VE":
		return "Spanish";
		break;
	case "AU":
	case "CM":
	case "GM":
	case "GH":
	case "IN":
	case "IE":
	case "KE":
	case "MT":
	case "NA":
	case "NZ":
	case "NG":
	case "PK":
	case "PH":
	case "SL":
	case "SG":
	case "ZA":
	case "TZ":
	case "TH":
	case "UG":
	case "GB":
	case "ZM":
	case "ZW":
		return "English (UK)";
		break;
	case "AT":
	case "DE":
	case "LI":
	case "CH":
		return "German";
		break;
	case "IT":
	case "SM":
	case "VA":
		return "Italian";
		break;
	case "AO":
	case "BR":
	case "CV":
	case "TL":
	case "GW":
	case "MZ":
	case "PT":
	case "ST":
		return "Portuguese";
		break;
	case "BE":
	case "NL":
	case "SR":
		return "Dutch";
		break;
	default:
		return "English (US)";
	}
}

function getTemplatesPath()
{
	$path = DIR_TEMPLATES;
	if (substr($_SERVER["REQUEST_URI"],0,6)=="/beta/") 
		$path = "../$path";
	return $path;
}

function getTemplatesPathAndPrefix()
{
	$path = getTemplatesPath();
	$prefix = (preg_match("~^test\\.~i",$_SERVER["HTTP_HOST"]) ? "test_" : "");
	return array($path,$prefix);
}

function readTemplate($fname,&$language)
{
	$isDefault = (strpos($fname,"_")===false);
	list($path,$prefix) = getTemplatesPathAndPrefix();
	if (!$isDefault)
		$fname = $prefix.$fname;
	$fname = $path.$fname;
	$fname .= ($isDefault?".tpt":".txt");
	//echo $fname."<hr>";
	if (!file_exists($fname))
	{
		$language = null;
		return null;
	}
	else
	{
		$h = fopen($fname,"r");
		if (!$isDefault)
			$language = trim(fgets($h));
		$wFirst = trim(fgets($h));
		$wSecond = trim(fgets($h));
		$wThird = trim(fgets($h));
		$mons = array();
		for ($x=0;$x<12;$x++)
			$mons[$x] = trim(fgets($h));
		$txt = trim(fgets($h));
		fclose($h);
		//
		return new Template($txt,$wFirst,$wSecond,$wThird,$mons);
	}
}

function saveTemplate($language,$text)
{
	$template = readTemplate($language, $dummy);
	if (!$template)
		die("File does not exist ($language.tpt)");
	
	list($path,$prefix) = getTemplatesPathAndPrefix();
	$fname = $path.$prefix."tpt_".$_SESSION["c_id"].".txt";
	
	$h = fopen($fname,"w");
	fputs($h,$language."\n");
	fputs($h,$template->wordFirst."\n");
	fputs($h,$template->wordSecond."\n");
	fputs($h,$template->wordThird."\n");
	for ($x=0;$x<12;$x++)
		fputs($h,$template->months[$x]."\n");
	if ($text)
	{
		$text = str_replace("\\\\","\\",$text);
		$text = str_replace("\\'","'",$text);
		$text = str_replace("\\\"","\"",$text);
		fputs($h,$text);
	}
	else
		fputs($h,$template->getV1());	
	fclose($h);
}

function uploadTemplateBackground($tmpfname,$ext)
{
	global $aTBGExtensions;
	switch($ext)
	{
	case $aTBGExtensions[0]:
		$img = imagecreatefromgif($_FILES["file"]["tmp_name"]);
		break;
	case $aTBGExtensions[1]:
		$img = imagecreatefromjpeg($_FILES["file"]["tmp_name"]);
		break;
	case $aTBGExtensions[2]:
		$img = imagecreatefrompng($_FILES["file"]["tmp_name"]);
		break;
	}
	if (!$img) return "Error: not a valid $ext image!";
	//
	list($path,$prefix) = getTemplatesPathAndPrefix();
	$fname = $path.$prefix."tbg_" . $_SESSION["c_id"];
	if (!move_uploaded_file ($tmpfname, $fname.".".strtolower($ext)))
		return "Error: cannot copy the file!";
	else
	{
		foreach($aTBGExtensions as $e)
			if ($e != $ext)
			{
				$e = strtolower($e);
				if (file_exists($fname.".".$e))
					unlink($fname.".".$e);
			}
		//
		$w = imagesx($img);
		$h = imagesy($img);
		$nw = 287;
		$nh = 200;
		$thumb = imagecreatetruecolor($nw,$nh);
		$white = imagecolorallocate($thumb,255,255,255);
		imagefilledrectangle($thumb, 0, 0, $nw-1, $nh-1, $white);
		imagecopyresampled($thumb, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
		imagejpeg($thumb,$fname."_thumb.jpg",100);
		imagedestroy($img);
		imagedestroy($thumb);
		//
		return "";
	}
}

function getTemplateBackgroundFilename()
{
	global $aTBGExtensions;
	list($path,$prefix) = getTemplatesPathAndPrefix();
	$fname = $path.$prefix."tbg_".$_SESSION["c_id"];
	foreach($aTBGExtensions as $e)
	{
		$e = strtolower($e);
		if (file_exists($fname.".".$e))
			return $fname.".".$e;
	}
	return $path."Background.jpg";
}

function getTemplateBackgroundThumbFilename(&$exists)
{
	list($path,$prefix) = getTemplatesPathAndPrefix();
	$fname = $path.$prefix."tbg_".$_SESSION["c_id"]."_thumb.jpg";
	if (file_exists($fname))
	{
		$exists = true;
		return $fname;
	}
	else
	{
		$exists = false;
		return $path."Thumb.jpg";
	}
}

function deleteTemplateBackground()
{
	global $aTBGExtensions;
	list($path,$prefix) = getTemplatesPathAndPrefix();
	$fname = $path.$prefix."tbg_".$_SESSION["c_id"];
	if (file_exists($fname."_thumb.jpg"))
		unlink($fname."_thumb.jpg");
	foreach($aTBGExtensions as $e)
	{
		$e = strtolower($e);
		if (file_exists($fname.".".$e))
			unlink($fname.".".$e);
	}
}
?>
