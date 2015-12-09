<?php
/**
 * Plugin Name: Your Custom CSS
 * Plugin URI: http://blog.ppfeufer.de/wordpress-plugin-your-custom-css/
 * Description: Add your own CSS to your WordPress Backend and Frontend.
 * Version: 1.3
 * Author: H.-Peter Pfeufer
 * Author URI: http://ppfeufer.de
 */
define('YOUR_CUSTOM_CSS_VERSION', '1.3');
define('CUSTOM_CSS_FILE', WP_CONTENT_DIR . '/uploads/custom-css-');
define('CUSTOM_CSS_URI', WP_CONTENT_URL . '/uploads/custom-css-');

/**
 * Plugin initialisieren.
 *
 * @since 1.0.0
 */
if(!function_exists('custom_css_init')) {
	function custom_css_init() {
		/**
		 * Einstellungen registrieren.
		 */
		if(function_exists('register_setting')) {
			register_setting('custom-css-options', 'custom-css-options');
		}

		/**
		 * Sprachdatei wählen.
		 */
		if(function_exists('load_plugin_textdomain')) {
			load_plugin_textdomain('custom-css', false, dirname(plugin_basename( __FILE__ )) . '/l10n/');
		}
	}

	if(is_admin()) {
		add_action('admin_init', 'custom_css_init');
	}
}

/**
 * Festlegen was zu tun ist, bei Aktivierung des Plugins.
 *
 * @since 1.0.0
 */
if(!function_exists('custom_css_activate')) {
	function custom_css_activate() {
		$sh_adminbar_add_options = array(
			'custom-css-pluginname' => 'Your Custom CSS',
			'custom-css-pluginversion' => YOUR_CUSTOM_CSS_VERSION,
			'custom-css-frontend' => custom_css_get_default('frontend'),
			'custom-css-backend' => custom_css_get_default('backend'),
		);

		if(is_array(get_option('custom-css-options'))) {
			add_option('custom-css-options', $sh_adminbar_add_options);
		} else {
			update_option('custom-css-options', $sh_adminbar_add_options);
		}
	}

	/**
	 * Plugin aktivieren.
	 */
	register_activation_hook(__FILE__, 'custom_css_activate');
}

/**
 * Default CSS Frontend and Backend.
 *
 * Usage:
 * 		custom_css_get_default('frontend');
 * 		custom_css_get_default('backend');
 *
 * @since 1.0.0
 */
if(!function_exists('custom_css_get_default')) {
	function custom_css_get_default($var_sWhere = '') {
		if(!$var_sWhere) {
			return;
		}

		switch($var_sWhere) {
			case 'frontend':
				$var_sCustomCssDefault = '@charset "UTF-8";';
				break;

			case 'backend':
				$var_sCustomCssDefault = '@charset "UTF-8";';
				break;
		}

		return $var_sCustomCssDefault;
	}
}

/**
 * Optionsseite in das Dashboard einbinden.
 *
 * @since 1.0.0
 */
if(!function_exists('custom_css_options')) {
	function custom_css_options() {
		if(current_user_can('manage_options')) {
			add_options_page('Your CSS', __('Your CSS', 'custom-css'), 8, basename(__FILE__, '.php'), 'custom_css_options_page');
		}

		if($_REQUEST['page'] == 'your-custom-css') {
			$var_sCssUrl = plugins_url(basename(dirname(__FILE__)) . '/css/custom-css-admin.css');

			/**
			 * CSS in Wordpress einbinden
			 */
			wp_register_style('custom-css', $var_sCssUrl, array(), YOUR_CUSTOM_CSS_VERSION, 'screen');
			wp_enqueue_style('custom-css');
		}
	}

	if(is_admin()) {
		add_action('admin_menu', 'custom_css_options');
	}
}

if(!function_exists('custom_css_options_page')) {
	function custom_css_options_page() {
		/**
		 * Status von $_REQUEST abfangen.
		 *
		 * @since 1.0.0
		 */
		if(!empty($_REQUEST) && isset($_REQUEST['Submit'])) {
			/**
			 * Validate the nonce.
			 *
			 * @since 1.0.0
			 */
			check_admin_referer('custom-css-options');

			$array_NewOptions = array(
				'custom-css-frontend' => stripslashes(wp_filter_post_kses($_REQUEST['custom-css-frontend'])),
				'custom-css-backend' => stripslashes(wp_filter_post_kses($_REQUEST['custom-css-backend']))
			);

			custom_css_set_options($array_NewOptions);

			echo '<div id="message" class="updated fade">';
			echo '<p><strong>';
			_e('CSS saved.', 'custom-css');
			echo '</strong></p>';
			echo '</div>';
		}
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br /></div>
			<h2><?php _e('Your Custom CSS', 'custom-css'); ?></h2>
			<p><?php _e('Define your own CSS for your WordPress Backend and Frontend.', 'custom-css'); ?></p>
			<form method="post" action="" id="wp-twitter-options">
				<?php wp_nonce_field('custom-css-options'); ?>

				<div class="custom-css-wrapper">
					<div>
						<p><?php _e('Frontend', 'custom-css'); ?></p>
						<p><textarea name="custom-css-frontend"><?php echo esc_textarea(custom_css_get_options('custom-css-frontend')); ?></textarea></p>
					</div>
					<div>
						<p><?php _e('Backend', 'custom-css'); ?></p>
						<p><textarea name="custom-css-backend"><?php echo esc_textarea(custom_css_get_options('custom-css-backend')); ?></textarea></p>
					</div>
				</div>
				<div class="flattr-button">
					<?php _e('Support me if you like the Plugin', 'custom-css'); ?><br />
					<a href="http://flattr.com/thing/290220/WordPress-Plugin-Your-Custom-CSS" target="_blank"><img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
				</div>
				<p class="submit">
					<input type="submit" name="Submit" value="<?php _e('Save CSS', 'custom-css'); ?>" />
				</p>
			</form>
		</div>
		<?php
	}
}

if(!function_exists('custom_css_get_options')) {
	function custom_css_get_options($var_sOption = '') {
		$array_PluginOptions = get_option('custom-css-options');

		if(empty($var_sOption)) {
			return $array_PluginOptions;
		} else {
			return $array_PluginOptions[$var_sOption];
		}
	}
}

/**
 * Neues CSS in die Datenbank eintragen.
 *
 * @since 1.0.0
 */
if(!function_exists('custom_css_set_options')) {
	function custom_css_set_options($array_NewOptions = array()) {
		$array_Options = array_merge((array) get_option('custom-css-options'), $array_NewOptions);

		update_option('custom-css-options', $array_Options);
		wp_cache_set('custom-css-options', $array_Options);

		if(custom_css_get_options('custom-css-frontend') != custom_css_get_default('frontend')) {
			custom_css_write('frontend', $array_Options['custom-css-frontend']);
		}

		if(custom_css_get_options('custom-css-backend') != custom_css_get_default('backend')) {
			custom_css_write('backend', $array_Options['custom-css-backend']);
		}
	}
}

/**
 * CSS-Dateien schreiben.
 * Diese werden in /wp-content/uploads/ abgelegt.
 *
 * @since 1.0.0
 */
if(!function_exists('custom_css_write')) {
	function custom_css_write($var_sWhere = '', $var_sCss = '') {
		if($var_sCss == '' || $var_sCss == custom_css_get_default($var_sWhere)) {
			@unlink(CUSTOM_CSS_FILE . $var_sWhere . '.css');
		} else {
			@file_put_contents(CUSTOM_CSS_FILE . $var_sWhere . '.css', $var_sCss);
		}
	}
}

/**
 * CSS in WordPress einbinden.
 *
 * @since 1.0.0
 */
if(!function_exists('custom_css_to_frontend')) {
	// Frontend
	function custom_css_to_frontend() {
		if(file_exists(CUSTOM_CSS_FILE . 'frontend.css') && custom_css_get_options('custom-css-frontend') != custom_css_get_default('frontend')) {
			echo '<link rel="stylesheet" type="text/css" href="' . CUSTOM_CSS_URI . 'frontend.css?ver=' . YOUR_CUSTOM_CSS_VERSION . '" />';
		}
	}

	add_action('wp_head', 'custom_css_to_frontend');
}

if(!function_exists('custom_css_to_backend')) {
	// Backend
	function custom_css_to_backend() {
		if(file_exists(CUSTOM_CSS_FILE . 'backend.css') && custom_css_get_options('custom-css-backend') != custom_css_get_default('backend')) {
			echo '<link rel="stylesheet" type="text/css" href="' . CUSTOM_CSS_URI . 'backend.css?ver=' . YOUR_CUSTOM_CSS_VERSION . '" />';
		}
	}

	add_action('admin_head', 'custom_css_to_backend');
}