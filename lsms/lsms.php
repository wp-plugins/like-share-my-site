<?php
/*
Plugin Name: Like & Share My Site
Plugin URI: http://www.scottflack.com
Description: This plugin allows you to easily set up buttons for 'likes' and 'shares' from multiple social networks.
Author: Scott Flack
Version: 0.1
Author URI: http://www.scottflack.com
License: GPL2
*/

/*  
Copyright 2013  Scott Flack  (email : hello@scottflack.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
* Admin Block
*/

//Create menu link
add_action('admin_menu', 'lsms_admin_menu');
function lsms_admin_menu() {
	// - directly add to settings menu -- add_options_page('Like & Share My Site', 'Like & Share My Site', 'manage_options', 'lsms_admin', 'lsms_admin_options_page');
	add_submenu_page('plugins.php', 'Like & Share My Site Options', 'Like & Share My Site Options', 'manage_options', 'lsms_admin', 'lsms_admin_options_page');
}

//Create the page
function lsms_admin_options_page() {
	if (current_user_can('manage_options')) {
		//User is allowed to mess with this plugin
		
		//Include style
		wp_enqueue_style('lsms_admin_style' ,plugins_url('style/admin.css', __FILE__));
		
		//Create array of 'buttons' for looping and saving
		$opt = array(
			'Facebook' => false,
			'Google_Plus' => false,
			'Twitter' => false,
			'StumbleUpon' => false,
			'Tumblr' => false
		);
		
		//Save options
		if ($_POST['submit_lsms']) {
			if ($_POST['lsms_Facebook'] == true) { $opt['Facebook'] = true; }
			if ($_POST['lsms_Google_Plus'] == true) { $opt['Google_Plus'] = true; }
			if ($_POST['lsms_Twitter'] == true) { $opt['Twitter'] = true; }
			if ($_POST['lsms_StumbleUpon'] == true) { $opt['StumbleUpon'] = true; }
			if ($_POST['lsms_Tumblr'] == true) { $opt['Tumblr'] = true; }
			
			foreach ($opt as $k => $v) {
				update_option('lsms_' . $k, $v);
			}
			
			//General Options
			if ($_POST['lsms_mouseover'] == 1) { 
				update_option('lsms_mouseover', true);
			} else {
				update_option('lsms_mouseover', false);
			}
			
			if ($_POST['lsms_float'] == 1) {
				update_option('lsms_float', true);
			} else {
				update_option('lsms_float', false);
			}
			
			update_option('lsms_label', $_POST['lsms_label']);
		}
		
		/*
			Options:
			on/off per button
			mouseover display only
			float left/right
			order them
		*/
		echo screen_icon();
		?>
		
		<h2>Like & Share My Site Options</h2>
		<div class="wrap">
			<form method="post" action="">
				<table class="lsms_options">
					<tr>
						<td></td>
						<?php
							foreach($opt as $k => $v) {
								echo '<th>' . str_replace('_', ' ', $k) . '</th>';
							}
						?>
					</tr>
					<tr>
						<th>Display</th>
						<?php 
							foreach ($opt as $k => $v) {
								echo '<td><input type="checkbox" name="lsms_'.$k.'" value="1" '. (get_option('lsms_' . $k) ? 'checked="checked"' : '') .'/></td>';
							}
						?>
					</tr>
				</table>
				
				<h3>General Options</h3>
					<table class="lsms_options">
						<tr>
							<th>Label</th>
							<td><input type="text" name="lsms_label" value="<?php echo get_option('lsms_label'); ?>" /></td>
						</tr>
						<tr>
							<th>Show on Mouseover</th>
							<td><input type="checkbox" name="lsms_mouseover" value="1" <?php echo (get_option('lsms_mouseover') ? 'checked="checked"' : ''); ?> /></td>
						</tr>
						<tr>
							<th>Float</th>
							<td><input type="radio" name="lsms_float" value="1" <?php echo (get_option('lsms_float') ? 'checked="checked"' : ''); ?> /> Left</td>
							<td><input type="radio" name="lsms_float" value="0" <?php echo (get_option('lsms_float') ? '' : 'checked="checked"'); ?> /> Right</td>
						</tr>
					</table>
				
				<br />
				<input type="submit" name="submit_lsms" class="button-primary" value="<?php echo translate('Save Options') ?>"/>
			</form>
		</div>
		<?php
	} else {
		wp_die(translate('You do not have permissions to view this page.'));
	}
}

//Register Settings
add_action('admin_init', 'lsms_register_settings');
function lsms_register_settings() {
	register_setting('lsms_settings', 'lsms_facebook');
	register_setting('lsms_settings', 'lsms_twitter');
	register_setting('lsms_settings', 'lsms_google_plus');
}

/**
* Front-End Block
*/

add_filter('the_content', 'lsms_add_to_post', 9999);
function lsms_add_to_post($content) {
	if (is_single()) {
		$url = getURL();
		wp_enqueue_style('lsms_style', plugins_url('/style/buttons.css', __FILE__));
		if (get_option('lsms_mouseover')) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('lsms_mouseover_script', plugins_url('/script/mouseover.js', __FILE__));
		}
		$content = '<div class="social_wrap">'. $content;
		$content .= '<div class="lsms_social_bar" style="' .(get_option('lsms_float') == true ? 'float: left;' : 'float: right;') . (get_option('lsms_mouseover') ? 'display: none;' : '') . '">';
			
			//Label
			$label = get_option('lsms_label');
			if (strlen($label) > 0) {
				$content .= '<div class="lsms_label">' . $label . '</div> ';
			}
			
			//Facebook
			if (get_option('lsms_Facebook')) {
				$content .= '<div class="social"><iframe class="fb-like" src="//www.facebook.com/plugins/like.php?href='.urlencode($url).'&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe></div>';
			}
			
			//Twitter
			if (get_option('lsms_Twitter')) {
				wp_enqueue_script('twitter', plugins_url('/script/twitter.js', __FILE__));
				$content .= '<div class="social"><a href="https://twitter.com/share" class="twitter-share-button" data-dnt="true" data-url="'.$url.'" data-counturl="'.$url.'">Tweet</a></div>';
			}
			
			//Google+
			if (get_option('lsms_Google_Plus')) {
				wp_enqueue_script('gplus', 'https://apis.google.com/js/plusone.js');
				$content .= '<div class="social"><div class="g-plusone" data-size="tall" data-annotation="none"></div></div>';
			}
			
			//tumblr
			if (get_option('lsms_Tumblr')) {
				wp_enqueue_script('lsms_tumblr', 'http://platform.tumblr.com/v1/share.js');
				$content .= '<div class="social"><a class="tumblr-share" href="http://www.tumblr.com/share" title="Share on Tumblr" style="display:inline-block; text-indent:-9999px; overflow:hidden; width:81px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_1.png\') top left no-repeat transparent;">Share on Tumblr</a></div>';
			}
			
			//Stumbleupon
			if (get_option('lsms_StumbleUpon')) {
				wp_enqueue_script('lsms_stumbleupon', plugins_url('/script/stumbleupon.js', __FILE__));
				$content .= '<div class="social"><su:badge layout="1" location="'.$url.'" class="stumble-share"></su:badge></div>';
			}
			
		$content .= '</div><div style="width: 1px; height: 1px;"></div></div>';
	}
	return $content;
}

//Custom URL get
function getURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}
?>