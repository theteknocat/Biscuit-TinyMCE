<?php
/**
 * This file customized to accommodate files uploaded from the standard upload form rather than the Flash uploader.
 *
 * @author Peter Epp
 */
require_once('config_tinybrowser.php');
require_once('fns_tinybrowser.php');

// delay script if set
if($tinybrowser['delayprocess']>0) sleep($tinybrowser['delayprocess']);

// Initialise files array and error vars
$files = array();
$good = 0;
$bad = 0;
$dup = 0;
$cannotresize = 0;
$total = (isset($_GET['filetotal']) ? $_GET['filetotal'] : 0);


// Assign get variables
$folder = $tinybrowser['docroot'].urldecode($_GET['folder']);
$foldernow = urlencode(str_replace($tinybrowser['path'][$_GET['type']],'',urldecode($_GET['folder'])));
$passfeid = (isset($_GET['feid']) ? '&feid='.$_GET['feid'] : '');

if ($handle = opendir($folder))
	{
	while (false !== ($file = readdir($handle)))
		{
		if ($file != "." && $file != ".." && substr($file,-1)=='_')
			{
			//-- File Naming
			$tmp_filename = $folder.$file;
			$dest_filename	 = $folder.rtrim($file,'_');
        
			//-- Duplicate Files
			if(file_exists($dest_filename)) { unlink($tmp_filename); $dup++; continue; }

			//-- Bad extensions
			$nameparts = explode('.',$dest_filename);
			$ext = end($nameparts);
			
			if(!validateExtension($ext, $tinybrowser['prohibited'])) { unlink($tmp_filename); continue; }
        
			//-- Rename temp file to dest file
			rename($tmp_filename, $dest_filename);
			$good++;
			
			//-- if image, perform additional processing
			if($_GET['type']=='image')
				{
				//-- Good mime-types
				$imginfo = getimagesize($dest_filename);
	   			if($imginfo === false) {
					unlink($dest_filename); continue;
				}
				if (memory_check($dest_filename)) {
					Console::log("Image passed the memory check...");
					$mime = $imginfo['mime'];

					if (!file_exists($folder.'_originals')) {
						createfolder($folder.'_originals/',$tinybrowser['unixpermissions']);
					}

					$origimg = $folder.'_originals/'.rtrim($file,'_');

					if (file_exists($origimg)) {
						@unlink($origimg);
					}

					$image = new Image($dest_filename);
					$image->auto_rotate()->write($origimg);

					// resize image to maximum height and width, if set
					if($tinybrowser['imageresize']['width'] > 0 || $tinybrowser['imageresize']['height'] > 0) {
						// assign new width and height values, only if they are less than existing image size
						$widthnew  = ($tinybrowser['imageresize']['width'] > 0 && $tinybrowser['imageresize']['width'] < $imginfo[0] ? $tinybrowser['imageresize']['width'] : $imginfo[0]);
						$heightnew = ($tinybrowser['imageresize']['height'] > 0 && $tinybrowser['imageresize']['height'] < $imginfo[1] ? $tinybrowser['imageresize']['height'] :  $imginfo[1]);

						// only resize if width or height values are different
						if($widthnew != $imginfo[0] || $heightnew != $imginfo[1]) {
							$image->resize($widthnew,$heightnew,Image::RESIZE_ONLY,$dest_filename);
						}
					}

					// generate thumbnail
					$thumbimg = $folder.'_thumbs/_'.rtrim($file,'_');
					if (!file_exists($thumbimg)) {
						$image->resize($tinybrowser['thumbwidth'],$tinybrowser['thumbheight'],Image::RESIZE_AND_CROP,$thumbimg);
					}
					$image->destroy();
				} else {
					Console::log("File failed memory check on upload");
					@unlink($dest_filename);
					$cannotresize++;
					$good--;
					continue;
				}
			}
      	}
		}
	closedir($handle);
	}
if (isset($_GET['standard_upload_form']) && $_GET['standard_upload_form'] == 1 && $_GET['file_errors'] == 1) {
	// Handle file upload errors if they occur when uploading from the standard (non-Flash) upload form
	$bad = 1;
} else {
	$bad = $total-($good+$dup);
}

// Check for problem during upload
if($total>0 && $bad==$total && $cannotresize == 0) Header('Location: ./upload.php?type='.$_GET['type'].$passfeid.'&permerror=1&total='.$total);
else Header('Location: ./upload.php?type='.$_GET['type'].$passfeid.'&folder='.$foldernow.'&badfiles='.$bad.'&goodfiles='.$good.'&dupfiles='.$dup.'&cannotresize='.$cannotresize);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Pragma" content="no-cache" />
		<title>TinyBrowser :: Process Upload</title>
	</head>
	<body>
		<p>Sorry, there was an error processing file uploads.</p>
	</body>
</html>
