<?php
/**
 * Extension for easy inclusion of Tiny MCE rich text editor
 *
 * @package Extensions
 * @author Peter Epp
 * @copyright Copyright (c) 2009 Peter Epp (http://teknocat.org)
 * @license GNU Lesser General Public License (http://www.gnu.org/licenses/lgpl.html)
 * @version 2.1
 */
class TinyMce extends AbstractExtension {
	public function run() {
		// Ensure instantiation
	}
	/**
	 * Register the Tiny MCE js components. A module that wishes to use it must call this method within the action method that requires it.
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public function register_components() {
		$this->register_js('header','tiny_mce/jscripts/tiny_mce/tiny_mce.js',true);
	}
	/**
	 * Provide upload directories for directory setup
	 *
	 * @return void
	 * @author Peter Epp
	 */
	protected function act_on_directory_setup() {
		$this->Biscuit->add_to_directory_list(SITE_ROOT.'/uploads/images');
		$this->Biscuit->add_to_directory_list(SITE_ROOT.'/uploads/media');
		$this->Biscuit->add_to_directory_list(SITE_ROOT.'/uploads/files');
	}
	/**
	 * Return a script tag for the tiny mce tb browser. This method should be called by the module that needs it when compiling the footer.
	 *
	 * @return string HTML code snippet
	 * @author Peter Epp
	 */
	public function render_tinymce_tb_browser_script() {
		return '<script type="text/javascript" charset="utf-8" src="/extensions/tiny_mce/js/tiny_mce/jscripts/tiny_mce/plugins/tinybrowser/tb_tinymce.js.php"></script>';
	}
	/**
	 * Return a script tag for the standalone tb browser. This method should be called by the module that needs it when compiling the footer.
	 *
	 * @return string HTML code snippet
	 * @author Peter Epp
	 */
	public function render_standalone_tb_browser_script() {
		return '<script type="text/javascript" charset="utf-8" src="/extensions/tiny_mce/js/tiny_mce/jscripts/tiny_mce/plugins/tinybrowser/tb_standalone.js.php"></script>';
	}
	/**
	 * Return the TinyMce configuration setting that defines the CSS file to use in the RTE if a Tiny MCE stylesheet exists in the page's theme
	 *
	 * @param Page $page Instance of a page model
	 * @return string Javascript code snippet
	 * @author Peter Epp
	 */
	public function theme_css_setting(Page $page) {
		if (file_exists($page->full_theme_path().'/css/styles_tinymce.css')) { 
			return "content_css: '".$page->full_theme_path(true)."/css/styles_tinymce.css',\n";
		}
		return '';
	}
	/**
	 * Render a text field with a browse button beside it that pops up the standalone TinyBrowser to select a file or image to populate the field with.
	 * Aside from the required first argument, this function takes all the same arguments as the Form::text() method.  This method supports either jQuery
	 * or Prototype, preferring jQuery.
	 *
	 * @param $type The type of file to browse for. 'file', 'image' or 'media'
	 * @return string HTML code snippet
	 * @author Peter Epp
	 */
	public function render_standalone_file_browser_field($type) {
		$text_field_args = func_get_args();
		array_shift($text_field_args);
		$field_id = 'attr_'.$text_field_args[0];
		$field_base = call_user_func_array(array('Form','text'),$text_field_args);
		$button_id = 'file-browse-button-'.$text_field_args[0];
		$field = <<<HTML
<span class="file-browse-field">$field_base<a href="#file-browser" id="$button_id" class="file-browse-button">Browse/Upload</a></span>
HTML;
		if ($this->Biscuit->extension_exists('Jquery')) {
			$field .= <<<HTML
<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function() {
		jQuery('#$button_id').click(function() {
			tinyBrowserPopUp('$type','$field_id');
			return false;
		});
	});
</script>
HTML;
		} else if ($this->Biscuit->extension_exists('PrototypeJs')) {
			$field .= <<<HTML
<script type="text/javascript" charset="utf-8">
	document.observe('dom:loaded',function() {
		$('$button_id').observe('click',function(event) {
			Event.stop(event);
			tinyBrowserPopUp('$type','$field_id');
		});
	});
</script>
HTML;
		}
		return $field;
	}
	/**
	 * Add file manager permissions to the system settings table. Works with Biscuit 2.1 only
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public static function install_migration() {
		DB::query("REPLACE INTO `system_settings` (`constant_name`, `friendly_name`, `description`, `value`) VALUES ('TINYBROWSER_ACCESS_LEVEL','File Manager browsing access level','The level of access required to browse files using the file manager','99'), ('TINYBROWSER_UPLOAD_LEVEL','File Manager upload access level','The level of access required to upload files with the file manager','99'), ('TINYBROWSER_EDIT_LEVEL','File Manager edit access level','The level of access required to perform edit operations on files using the file manager (move, rename, resize images etc)','99'), ('TINYBROWSER_DELETE_LEVEL','File Manager delete access level','The level of access required to delete files using the file manager','99'), ('TINYBROWSER_FOLDER_MODIFY_LEVEL','File Manager folder management access level','The level of access required to manage folders using the file manager','99')");
	}
	/**
	 * Delete file manager permissions from the system settings table. Works with Biscuit 2.1 only
	 *
	 * @return void
	 * @author Peter Epp
	 */
	public static function uninstall_migration() {
		DB::query("DELETE FROM `system_settings` WHERE `constant_name` LIKE 'TINYBROWSER_%'");
	}
}
?>