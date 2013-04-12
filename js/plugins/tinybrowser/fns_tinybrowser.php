<?php
// *************************CREATE FOLDER**********************************
function createfolder($dir,$perm) {
is_dir(dirname($dir)) || createfolder(dirname($dir), $perm);
    return is_dir($dir) || @mkdir($dir, $perm);
}

// *************************VALIDATE FILE EXTENSIONS**********************************
function validateExtension($extension, $types) {
if(in_array($extension,$types)) return false; else return true;
}

//*************************************Display Alert Notifications*********************************
function alert(&$notify){
$alert_num = count($notify['type']);
for($i=0;$i<$alert_num;$i++)
	{
	?><div class="alert<?php echo $notify['type'][$i]; ?>"><?php echo $notify['message'][$i]; ?></div><br /><?php
	}
}

// *************************SORT FILE ARRAY BY SELECTED ORDER**********************************
function sortfileorder(&$sortbynow,&$sortorder,&$file) {

switch($sortbynow) 
	{
	case 'name':
		array_multisort($file['sortname'], $sortorder, $file['name'], $sortorder, $file['type'], $sortorder, $file['modified'], $sortorder, $file['size'], $sortorder, $file['dimensions'], $sortorder, $file['width'], $sortorder, $file['height'], $sortorder);
		break;
	case 'size':
		array_multisort($file['size'], $sortorder, $file['sortname'], SORT_ASC, $file['name'], SORT_ASC, $file['type'], $sortorder, $file['modified'], $sortorder, $file['dimensions'], $sortorder, $file['width'], $sortorder, $file['height'], $sortorder);
		break;
	case 'type':
		array_multisort($file['type'], $sortorder, $file['sortname'], SORT_ASC, $file['name'], SORT_ASC, $file['size'], $sortorder, $file['modified'], $sortorder, $file['dimensions'], $sortorder, $file['width'], $sortorder, $file['height'], $sortorder);
		break;
	case 'modified':
		array_multisort($file['modified'], $sortorder, $file['name'], $sortorder, $file['name'], $sortorder, $file['type'], $sortorder, $file['size'], $sortorder, $file['dimensions'], $sortorder, $file['width'], $sortorder, $file['height'], $sortorder);
		break;
	case 'dimensions':
		array_multisort($file['dimensions'], $sortorder, $file['width'], $sortorder, $file['sortname'], SORT_ASC, $file['name'], SORT_ASC, $file['modified'], $sortorder, $file['type'], $sortorder, $file['size'], $sortorder, $file['height'], $sortorder);
		break;
	default:
		// do nothing
	}
}

// **************************CHECK MEMORY NEEDED TO CREATE GD RESOURCE FROM IMAGE AGAINST ALLOCATED MEMORY*****************************
function memory_check($filepath) {
	// How much memory is currently available:
	$memory_allowed = ini_get('memory_limit');
	if (strtolower(substr($memory_allowed,-1)) == 'm') {
		$memory_available = ((int)$memory_allowed)*1024*1024;
	} else if (strtolower(substr($memory_allowed,-1)) == 'k') {
		$memory_available = ((int)$memory_allowed)*1024;
	} else {
		$memory_available = (int)$memory_allowed;
	}
	// Estimate memory needed to create GD image resource from file:
	$imgInfo = getimagesize($filepath);
	if (!$imgInfo) {
		return false;
	}
	$width = $imgInfo[0];
	$height = $imgInfo[1];
	if (!empty($imgInfo['bits'])) {
		$bits = (int)$imgInfo['bits'];
	} else {
		$bits = 8;	// Assume 8-bit if not defined in imageinfo, as that's a safe bet
	}
	$memory_estimate = $width * $height * $bits;
	return ($memory_estimate <= $memory_available);
}

// **************************GENERATE FORM OPEN*****************************
function form_open($name,$class,$url,$parameters){
?><form name="<?php echo $name; ?>" class="<?php echo $class; ?>" method="post" action="<?php echo $url.$parameters; ?>">
<?php
}

// **************************GENERATE FORM SELECT ELEMENT*****************************
function form_select($options,$name,$label,$current,$auto){
if ($label) {?><label for="<?php echo $name; ?>"><?php echo $label; ?></label><?php } 
?><select name="<?php echo $name; ?>" <?php if ($auto) {?>onchange="this.form.submit();"<?php }?>>
<?php
$loopnum = count($options); 
for($i=0;$i<$loopnum;$i++)
	{
	$selected = ($options[$i][0] == $current ? ' selected' : ''); 
	echo '<option value="'.$options[$i][0].'"'.$selected.'>'.$options[$i][1].'</option>';
	}
?></select><?php
}

// **************************GENERATE FORM HIDDEN ELEMENT*****************************
function form_hidden_input($name,$value) {
?><input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>" />
<?php
}

// **************************GENERATE FORM TEXT ELEMENT*****************************
function form_text_input($name,$label,$value,$size,$maxlength) {
if ($label) {?><label for="<?php echo $name; ?>"><?php echo $label; ?></label><?php } ?>
<input type="text" name="<?php echo $name; ?>" size="<?php echo $size; ?>" maxlength="<?php echo $maxlength; ?>" value="<?php echo $value; ?>" /><?php
}

// **************************GENERATE FORM SUBMIT BUTTON*****************************
function form_submit_button($name,$label,$class) {
?><button <?php if ($class) {?>class="<?php echo $class; ?>"<?php } ?>type="submit" name="<?php echo $name; ?>"><?php echo $label; ?></button>
</form>
<?php
}

//********************************Returns True if Number is Odd**************************************
function IsOdd($num)
{
return (1 - ($num & 1));
}

//********************************Truncate Text to Given Length If Required***************************
function truncate_text($textstring,$length){
	if (strlen($textstring) > $length)
		{
		$textstring = substr($textstring,0,$length).'...';
		}
	return $textstring;
}

/**
 * Present a size (in bytes) as a human-readable value
 * 
 * @param int    $size        size (in bytes)
 * @param int    $precision    number of digits after the decimal point
 * @return string
 */
function bytestostring($size, $precision = 0) {
    $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'KB', 'B');
    $total = count($sizes);

    while($total-- && $size > 1024) $size /= 1024;
    return round($size, $precision).' '.$sizes[$total];
}

//function to clean a filename string so it is a valid filename
function clean_filename($filename){
    $filename = preg_replace('/^\W+|\W+$/', '', $filename); // remove all non-alphanumeric chars at begin & end of string
    $filename = preg_replace('/\s+/', '_', $filename); // compress internal whitespace and replace with _
    return strtolower(preg_replace('/\W-/', '', $filename)); // remove all non-alphanumeric chars except _ and -

}

//********************************Return File MIME Type***************************
function returnMIMEType($filename)
    {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);

        switch(strtolower($fileSuffix[1]))
        {
            case 'js' :
                return 'application/x-javascript';

            case 'json' :
                return 'application/json';

            case 'jpg' :
            case 'jpeg' :
            case 'jpe' :
                return 'image/jpg';

            case 'png' :
            case 'gif' :
            case 'bmp' :
            case 'tiff' :
                return 'image/'.strtolower($fileSuffix[1]);

            case 'css' :
                return 'text/css';

            case 'xml' :
                return 'application/xml';

            case 'doc' :
            case 'docx' :
                return 'application/msword';

            case 'xls' :
            case 'xlt' :
            case 'xlm' :
            case 'xld' :
            case 'xla' :
            case 'xlc' :
            case 'xlw' :
            case 'xll' :
                return 'application/vnd.ms-excel';

            case 'ppt' :
            case 'pps' :
                return 'application/vnd.ms-powerpoint';

            case 'rtf' :
                return 'application/rtf';

            case 'pdf' :
                return 'application/pdf';

            case 'html' :
            case 'htm' :
            case 'php' :
                return 'text/html';

            case 'txt' :
                return 'text/plain';

            case 'mpeg' :
            case 'mpg' :
            case 'mpe' :
                return 'video/mpeg';

            case 'mp3' :
                return 'audio/mpeg3';

            case 'wav' :
                return 'audio/wav';

            case 'aiff' :
            case 'aif' :
                return 'audio/aiff';

            case 'avi' :
                return 'video/msvideo';

            case 'wmv' :
                return 'video/x-ms-wmv';

            case 'mov' :
                return 'video/quicktime';

            case 'zip' :
                return 'application/zip';

            case 'tar' :
                return 'application/x-tar';

            case 'swf' :
                return 'application/x-shockwave-flash';

            default :
            if(function_exists('mime_content_type'))
            {
                $fileSuffix = mime_content_type($filename);
            }

            return 'unknown/' . trim($fileSuffix[0], '.');
        }
    }

//************************Return Array of Directory Structure***************************
function dirtree(&$alldirs,$types='*.*',$root='',$tree='',$branch='',$level=0) {

// filter file types according to type
$filetypes = explode(',',preg_replace('{[ \t]+}', '',$types));

if($level==0 && is_dir($root.$tree.$branch))
	{
	$filenum=0;
	foreach($filetypes as $filetype)
	   {
   	$filenum = $filenum + count(glob($root.$tree.$branch.sql_regcase($filetype),GLOB_NOSORT));
   	}
   $treeparts = explode('/',rtrim($tree,'/'));
	$topname = end($treeparts);
	$alldirs[] = array($branch,rtrim($topname,'/').' ('.$filenum.')',rtrim($topname,'/'),rtrim($topname,'/'),$filenum,filemtime($root.$tree.$branch));
	}
$level++;

$dh = opendir($root.$tree.$branch);
while (($dirname = readdir($dh)) !== false)
	{
	if($dirname != '.' && $dirname != '..' && is_dir($root.$tree.$branch.$dirname) && $dirname != '_thumbs' && $dirname != '_originals')
		{
		$filenum=0;
		foreach($filetypes as $filetype)
		   {
			$filenum = $filenum + count(glob($root.$tree.$branch.$dirname.'/'.sql_regcase($filetype),GLOB_NOSORT));
			}
		$indent = '';
		for($i=0;$i<$level;$i++) { $indent .= ' &nbsp; '; }
      if(strlen($indent)>0) $indent .= '&rarr; ';
		$alldirs[] = array(urlencode($branch.$dirname.'/'),$indent.$dirname.' ('.$filenum.')',$indent.$dirname,$dirname,$filenum,filemtime($root.$tree.$branch.$dirname));
		dirtree($alldirs,$types,$root,$tree,$branch.$dirname.'/',$level);
		}
	}
closedir($dh);
$level--;
}

/* user defined error handling function. */
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    // timestamp for the error entry.
    $dt = date('Y-m-d H:i:s (T)');

    // define an assoc array of error string
    // in reality the only entries we should
    // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
    // E_USER_WARNING and E_USER_NOTICE.
    $errortype = array (
                E_ERROR => 'Error',
                E_WARNING => 'Warning',
                E_PARSE => 'Parsing Error',
                E_NOTICE => 'Notice',
                E_CORE_ERROR => 'Core Error',
                E_CORE_WARNING => 'Core Warning',
                E_COMPILE_ERROR => 'Compile Error',
                E_COMPILE_WARNING => 'Compile Warning',
                E_USER_ERROR => 'User Error',
                E_USER_WARNING => 'User Warning',
                E_USER_NOTICE => 'User Notice',
                E_STRICT => 'Runtime Notice'
                );
    // set of errors for which a var trace will be saved.
    $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

	 if($errno != E_STRICT) // exclude Runtime Notices
		{
	 	$err  = $dt. "\t";
    	$err .= $errno.' '.$errortype[$errno]. "\t";
    	$err .= $errmsg. "\t";
    	$err .= 'File: '.basename($filename). "\t";
    	$err .= 'Line: '.$linenum. "\t";

    	if (in_array($errno, $user_errors))
			{
        	$err .= 'Trace: '.wddx_serialize_value($vars, 'Variables'). "\t";
    		}
    	$err .= "\n";

	   // save to the error log file, and e-mail me if there is a critical user error.
	   error_log($err, 3, 'error.log');
	   }
}
// $old_error_handler = set_error_handler('userErrorHandler');

?>
