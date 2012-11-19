<?
require_once "inc_private.php";
require_once "lib.php";
require_once "lib_ref_admin.php";
require_once "db.php";
require_once "lib_tpt.php";

define("SERIF","dejavuserif");
define("SANS","dejavu");

function parseText($text, $competitor,$position,$event,$score)
{
	global $pdf, $template;
	//
	$text = preg_replace("/\[competitor\]/",$competitor,$text);
	$position = $position+0;
	switch($position)
	{
	case 1:
		$position = $template->wordFirst;
		break;
	case 2:
		$position = $template->wordSecond;
		break;
	case 3:
		$position = $template->wordThird;
		break;
	}
	$text = preg_replace("/\[position\]/",$position,$text);
	$text = preg_replace("/\[event\]/",$event,$text);
	$text = preg_replace("/\[score\]/",$score,$text);
	//
	$lineH = 8;
	$pdf->SetFont(SERIF,"",20);
	while ($text)
	{
		$p = strpos($text,"[");
		if ($p === false)
		{
			$pdf->Write($lineH,$text);
			$text = "";
		}
		else
		{
			if ($p)
			{
				// avoid certain orphans
				$ch = substr($text,0,1);
				if ($ch == "." || $ch == ",")
				{
					$lastMargin = $pdf->rMargin;
					$pdf->SetRightMargin(0);
					$pdf->Write($lineH,$ch);
					$pdf->SetRightMargin($lastMargin);
					$text = substr($text,1);
					$p--;
				}
				$pdf->Write($lineH,substr($text,0,$p));
				$text = substr($text,$p);
			}
			$p = strpos($text,"]");
			if ($p === false)
			{
				$pdf->Write($lineH,$text);
				$text = "";
			}
			else
			{
				$command = substr($text,0,$p+1);
				$text = substr($text,$p+1);
				if (substr($command,0,6) == "[size=")
					$pdf->SetFont("","",substr($command,6,strlen($command)-6));
				else switch($command)
				{
				case "[sans-serif]":
					$pdf->SetFont(SANS);
					break;
				case "[serif]":
					$pdf->SetFont(SERIF);
					break;
				case "[b]":
					$pdf->SetFont("","B");
					break;
				case "[/b]":
					$pdf->SetFont("","");
					break;
				case "[br]":
					$pdf->Ln($lineH);
					break;
				case "[center]":
					$p = strpos($text,"[/center]");
					if ($p === false)
					{
						$part = $text;
						$text = "";
					}
					else
					{
						$part = substr($text,0,$p);
						$text = substr($text,$p+9);
					}
					$part = preg_replace("/\[.*\]/","",$part);
					$pdf->Cell(0,$lineH,$part,0,1,"C");
					break;
				case "[/center]":
					break;
				/*
				case "[competitor]":
					$pdf->Write($lineH,$competitor);
					break;
				case "[position]":
					$position = $position+0;
					switch($position)
					{
					case 1:
						$pdf->Write($lineH,$template->wordFirst);
						break;
					case 2:
						$pdf->Write($lineH,$template->wordSecond);
						break;
					case 3:
						$pdf->Write($lineH,$template->wordThird);
						break;
					default:
						$pdf->Write($lineH,$position);
					}
					break;
				case "[event]":
					$pdf->Write($lineH,$event);
					break;
				case "[score]":
					$pdf->Write($lineH,$score);
					break;
				*/
				default:
					$pdf->Write($lineH,$command);
				}
			}
		}
	}
}

function printCertificate($text, $competitor,$position,$event,$score)
{
	global $pdf, $fbgname;
	$pdf->AddPage();
	if (isset($_GET["b"]) && $fbgname)
		$pdf->Image($fbgname,5,5,287,200);
	if (isset($_GET["t"]))
		parseText($text, $competitor,$position,$event,$score);
}

if (substr($_SERVER["REQUEST_URI"],0,6)=="/beta/")
	require_once "../".DIR_FPDF;
else
	require_once DIR_FPDF;

$pdf = new tFPDF("L");
$pdf->SetMargins(50,50,50);
$pdf->AddFont(SERIF,"","DejaVuSerif.ttf",true);
$pdf->AddFont(SERIF,"B","DejaVuSerif-Bold.ttf",true);
$pdf->AddFont(SANS,"","DejaVuSans.ttf", true);
$pdf->AddFont(SANS,"B","DejaVuSans-Bold.ttf", true);

$fname = "tpt_".$_SESSION["c_id"];
$template = readTemplate($fname,$dummy);
if ($template)
	$text = $template->txt;
else
{
	$fname = defaultTemplateLanguage();
	$template = readTemplate($fname,$dummy);
	if (!$template) 
		die("Impossible to load a valid template");
	else
		$text = $template->getV1();
}

$fbgname = getTemplateBackgroundFilename();
if (!file_exists($fbgname))
	$fbgname = "";

if (!isset($_GET["t"]))
	printCertificate(null,null,null,null,null);
else
{
	$CERTIFICATES = true;        // flag
	require_once "extrarep.php"; // does all the work over db
}

sql_close();

$pdf->SetDisplayMode("fullpage","single");
$pdf->Output(preg_replace("/\W/","",$_SESSION["c_name"])." - Certificates.pdf", "I");
?>
