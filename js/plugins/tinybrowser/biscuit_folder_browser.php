<?php
// Generate a hierarchical folder browser panel using the Biscuit FindFiles library. Include this file within any of the TinyBrowser pages on which you want
// or need folder browsing.  It assumes all required variables and libs are present - it won't work on it's own.
$typenow = ((isset($_GET['type']) && in_array($_GET['type'],$validtypes)) ? $_GET['type'] : 'image');
$path = rtrim($tinybrowser['path'][$typenow],'/');
$base_path = SITE_ROOT.$path;
$path_bits = explode('/',$path);
$top_level_folder = end($path_bits);
$folder_list = FindFiles::ls($path,array('include_directories' => true, 'include_files' => false, 'excludes' => array('_thumbs', '_originals')),false,true);
if (!empty($folder_list)) {
	foreach ($folder_list as $folder) {
		$folders_by_filename[(string)$folder] = $folder;
	}
	if (!empty($folders_by_filename)) {
		ksort($folders_by_filename);
	}
}
$params = $_GET;
foreach ($params as $index => $param) {
	if ($index != 'folder') {
		$use_params[$index] = $param;
	}
}
$request_uri = Request::uri();
if (strstr($request_uri,'?')) {
	$request_uri = substr($request_uri,0,strpos($request_uri,'?'));
}
$base_query_string = http_build_query($use_params);
$base_query_string = preg_replace('/\&/','&amp;',$base_query_string);
$first_class = '';
if (empty($foldernow)) {
	$Biscuit->ExtensionNavigation()->tiger_stripe('tb_folder_list');
	$first_class = 'selected';
} else {
	$first_class = $Biscuit->ExtensionNavigation()->tiger_stripe('tb_folder_list');
}
?>
<div id="biscuit-folder-list-container" class="panel currentmod">
	<fieldset>
		<legend>Current Folder</legend>
		<div id="biscuit-folder-list">
			<a class="<?php echo $first_class ?>" href="<?php echo $request_uri.'?'.$base_query_string ?>&amp;folder=" style="padding: 4px 10px 4px 4px" title="/images"><span class="link-text"><?php echo $top_level_folder ?></span></a><?php
if (!empty($folders_by_filename)) {
	$curr_parent = $top_level_folder;
	$curr_indent = 1;
	foreach ($folders_by_filename as $full_path => $folder) {
		$relative_path = substr($full_path,strlen($base_path)+1);
		$path_bits = explode(DIRECTORY_SEPARATOR,$relative_path);
		$my_indent = count($path_bits);
		if ($my_indent > 1) {
			$my_parent = $path_bits[count($path_bits)-2];
		} else {
			$my_parent = $top_level_folder;
		}
		$link_url = $request_uri.'?'.$base_query_string.'&amp;folder='.urlencode($relative_path.'/');
		if ($foldernow == $relative_path.'/') {
			$Biscuit->ExtensionNavigation()->tiger_stripe('tb_folder_list');
			$my_class = 'selected';
		} else {
			$my_class = $Biscuit->ExtensionNavigation()->tiger_stripe('tb_folder_list');
		}
		$padding_left = (10*$my_indent)+4;
		$folder_name = $folder->getFilename();
		$full_folder_name = '/'.$top_level_folder.'/'.$relative_path;
		if (strlen($folder_name) > 20) {
			$folder_name = substr($folder_name,0,20).'...';
		}
		?><a class="<?php echo $my_class ?>" href="<?php echo $link_url ?>" style="padding: 4px 10px 4px <?php echo $padding_left ?>px" title="<?php echo $full_folder_name ?>">&rarr; <span class="link-text"><?php echo $folder_name ?></span></a><?php
		$curr_indent = $my_indent;
		$curr_parent = $my_parent;
		$just_started = false;
	}
}
?></div></fieldset></div>