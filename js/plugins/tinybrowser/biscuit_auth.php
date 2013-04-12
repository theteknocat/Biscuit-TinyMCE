<?php
// Bootstrap the framework so we can check user access and configure TinyBrowser access accordingly.
// Include this file at the top of the TinyBrowser config file and use the variables it sets for the access settings in the
// TinyBrowser config.  Tell TinyBrowser to use 
require_once(realpath(dirname(__FILE__).'/../../../../../framework/bootstrap.php'));
// Stuff the query string with page_slug set to "index" so it doesn't load the 404 page every time.  Doesn't really matter which page it loads since we're
// not actually viewing a Biscuit page, but it goes through some extra logic when a page isn't found so might as well avoid that.
Request::set_query('page_slug','index');
// Set the session ID from GET params. Session::set_id() will take care of only setting it if the value isn't empty. This is for the Flash session
// bug workaround, which must be done here not in the TB config file as it needs to be in place prior to Bootstrapping the framework.
Session::set_id(Request::query_string('sessidpass'));
// Tell the framework to ignore request tokens so we don't have problems when post request is sent by the Flash file uploader.
define('IGNORE_REQUEST_TOKEN',true);
// Do a full bootstrap of the framework so the shared models and Authenticator module get loaded.
$Biscuit = Bootstrap::load(Bootstrap::FULL);
$Biscuit->init();
$biscuit_user_can_upload         = false;
$biscuit_user_can_edit           = false;
$biscuit_user_can_delete         = false;
$biscuit_user_can_modify_folders = false;
$Authenticator = $Biscuit->ModuleAuthenticator();
$Authenticator->define_access_levels();
if ($Authenticator->user_is_logged_in()) {
	$current_user_level = $Authenticator->active_user()->user_level();
	if (method_exists($Authenticator,'file_manager_permission')) {
		$user_can_access_tb = $Authenticator->file_manager_permission('access');
	} else if (defined('TINYBROWSER_ACCESS_LEVEL')) {
		$user_can_access_tb = ($current_user_level >= TINYBROWSER_ACCESS_LEVEL);
	}
	if ($user_can_access_tb) {
		Session::set('tiny_browser_access_enabled',true);
		if (method_exists($Authenticator,'file_manager_permission')) {
			// Hook to allow setting custom file manager permissions for the current user
			$biscuit_user_can_upload         = $Authenticator->file_manager_permission('upload');
			$biscuit_user_can_edit           = $Authenticator->file_manager_permission('edit');
			$biscuit_user_can_delete         = $Authenticator->file_manager_permission('delete');
			$biscuit_user_can_modify_folders = $Authenticator->file_manager_permission('modify_folders');
		} else {
			$biscuit_user_can_upload         = (defined('TINYBROWSER_UPLOAD_LEVEL') && $current_user_level >= TINYBROWSER_UPLOAD_LEVEL);
			$biscuit_user_can_edit           = (defined('TINYBROWSER_EDIT_LEVEL') && $current_user_level >= TINYBROWSER_EDIT_LEVEL);
			$biscuit_user_can_delete         = (defined('TINYBROWSER_DELETE_LEVEL') && $current_user_level >= TINYBROWSER_DELETE_LEVEL);
			$biscuit_user_can_modify_folders = (defined('TINYBROWSER_FOLDER_MODIFY_LEVEL') && $current_user_level >= TINYBROWSER_FOLDER_MODIFY_LEVEL);
		}
	} else {
		Session::unset_var('tiny_browser_access_enabled');
	}
} else {
	Session::unset_var('tiny_browser_access_enabled');
}
