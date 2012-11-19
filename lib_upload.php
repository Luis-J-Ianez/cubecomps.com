<?
/* $uploadURL
 * 	IE:
 *		The $uploadURL page must output a visible, normal page.
 *		On success, it must refresh the opener and close itself.
 *		It must show an error message otherwise.
 *	non IE:
 *		The $uploadURL page must output plain text.
 *		Must return "OK" on success.
 *		Or an error text otherwise.
 *
 * ----- The rest of parameters are ignored in IE -----
 *
 * $aMIME is an array of MIME types -- example: array("image/gif","image/jpeg")
 *
 * $maxSize is in Kb
 *
 * $accept is a string of comma separated MIME types -- examples: "image/*" or "image/gif,image/jpeg"
 *
 * $onUploaded is a string with JS instructions to be executed on suceessful upload.
 * examples:
 *	"window.location.reload();"
 *	"uploaded();" // function uploaded() must exist in your code
 */
function echoUploadForm($uploadURL,$aMIME,$maxSize,$accept,$onUploaded)
{
	$IE = (preg_match("/msie/i",$_SERVER["HTTP_USER_AGENT"]) || preg_match("/internet explorer/i",$_SERVER["HTTP_USER_AGENT"]));
	if (!$IE)
	{
		$jsaMIME = "";
		foreach($aMIME as $m)
		{
			if ($jsaMIME) $jsaMIME .= "','";
			$jsaMIME .= $m;
		}
		$jsaMIME = "['$jsaMIME']";
		echo "<script>

var xhr;

function uploadFile()
{
	var MIMEtypes = $jsaMIME;
	
	var fileInput = document.getElementById('upldfile');
	if (fileInput.files.length == 0)
		alert('No file selected');
	else if (fileInput.files.length > 1)
		alert('Uploading more than one file is not permitted');
	else
	{
		var file = fileInput.files[0];
		for (var x=0;x<MIMEtypes.length;x++)
			if (file.type == MIMEtypes[x])
				break;
		if (x == MIMEtypes.length)
		{
			var msg = 'Type of file not supported';
			if (file.type)
				msg += ' ('+file.type+')';
			msg += '.';
			alert(msg);
		}
		else if (file.size > $maxSize*1024)
			alert('Size of file exceeds maximum allowed ($maxSize Kb).');
		else
		{
			progressbarReset();
			xhr = new XMLHttpRequest();
			xhr.onreadystatechange = progressbarReadyStateChange;
			xhr.upload.addEventListener('progress', onprogressHandler, false);
			xhr.open('POST', '$uploadURL', true);
			var formData = new FormData();
			formData.append('file', file);
			xhr.send(formData);
		}
	}
}

function progressbarPosition(pct)
{
	document.getElementById('progressbarIn').style.marginLeft = (pct*2)+'px';
}

function progressbarReset()
{
	progressbarPosition(0);
	document.getElementById('submit').disabled = true;
	document.getElementById('upldfile').disabled = true;
	document.getElementById('progressbarOut').style.display = 'block';
}

function progressbarHide()
{
	document.getElementById('progressbarOut').style.display = 'none';
	document.getElementById('submit').disabled = false;
	document.getElementById('upldfile').disabled = false;
}

function onprogressHandler(evt) 
{
	progressbarPosition (Math.round(evt.loaded/evt.total*100));	
}

function progressbarReadyStateChange()
{
	if (xhr.readyState == 4)
	{
		var response = xhr.responseText.replace(/[\\s\\r\\n]+$/,'');
		if (response == 'OK')
		{
			$onUploaded
		}
		else
		{
			progressbarHide();
			alert(response);
		}
	}
}

</script>

<input type=file id=upldfile name=file accept='$accept'/>
<input type=button id=submit onclick='uploadFile();' value=submit />
<p>
<span id=progressbarOut style='display:none;width:200px;height:5px;background-color:red;overflow-x:hidden;'>
<span id=progressbarIn style='width:200px;height:5px;display:block;background-color:#eee;'></span>
</span>";
	}
	else
	{
		echo "<script>
var wupload = 0;
</script>
<form action='$uploadURL' target='w_upload' onsubmit='if(wupload && !wupload.closed) wupload.close(); wupload=window.open(\"\", \"w_upload\", \"width=200, height=20, location=0, scrollbars=0, resizable=0\"); wupload.moveTo((screen.availWidth-200)/2,(screen.availHeight-20)/2);' method='post' enctype='multipart/form-data'>
<input type='file' name='file' />
<input type='submit' name='submit' value='submit' />
</form>";
	}
}
?>
