<?php
// Bootstrap the framework so we can check user access and configure TinyBrowser access accordingly.
// Include this file at the top of the TinyBrowser config file and use the variables it sets for the access settings in the
// TinyBrowser config.  Tell TinyBrowser to use 
require_once(realpath(dirname(__FILE__).'/../../../../../../../../framework/bootstrap.php'));
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
$biscuit_user_can_upload         = false;
$biscuit_user_can_edit           = false;
$biscuit_user_can_delete         = false;
$biscuit_user_can_modify_folders = false;
if ($Biscuit->ModuleAuthenticator()->user_is_logged_in()) {
	$current_user_level = $Biscuit->ModuleAuthenticator()->active_user()->user_level();
	if (defined('TINYBROWSER_ACCESS_LEVEL')) {
		if ($current_user_level >= TINYBROWSER_ACCESS_LEVEL) {
			Session::set('tiny_browser_access_enabled',true);
		} else {
			Session::unset_var('tiny_browser_access_enabled');
		}
		$biscuit_user_can_upload         = (defined('TINYBROWSER_UPLOAD_LEVEL') && $current_user_level >= TINYBROWSER_UPLOAD_LEVEL);
		$biscuit_user_can_edit           = (defined('TINYBROWSER_EDIT_LEVEL') && $current_user_level >= TINYBROWSER_EDIT_LEVEL);
		$biscuit_user_can_delete         = (defined('TINYBROWSER_DELETE_LEVEL') && $current_user_level >= TINYBROWSER_DELETE_LEVEL);
		$biscuit_user_can_modify_folders = (defined('TINYBROWSER_FOLDER_MODIFY_LEVEL') && $current_user_level >= TINYBROWSER_FOLDER_MODIFY_LEVEL);
	} else {
		Session::unset_var('tiny_browser_access_enabled');
	}
} else {
	Session::unset_var('tiny_browser_access_enabled');
}
?>