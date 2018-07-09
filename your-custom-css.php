<?php
/**
 * Plugin Name: Your Custom CSS
 * Plugin URI: https://github.com/ppfeufer/your-custom-css
 * Description: Add your own CSS to your WordPress Backend and Frontend.
 * Version: 1.4
 * Author: H.-Peter Pfeufer
 * Author URI: http://ppfeufer.de
 */

namespace WordPress\Plugin\YourCustomCss;

class CustomCss {
    /**
     * Plugin Version
     *
     * @var type string
     */
    protected $customCssVersion = '1.4';

    /**
     * Directory for custom CSS files
     *
     * @var type string
     */
    protected $customCssFileDirectory = \WP_CONTENT_DIR . '/uploads/custom-css-';

    /**
     * URI for custom CSS files
     *
     * @var type string
     */
    protected $customCssFileUri = \WP_CONTENT_URL . '/uploads/custom-css-';

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize hooks and stuff
     */
    public function init() {
        \register_activation_hook(__FILE__, [$this, 'customCssActivate']);

        $this->addActions();
    }

    /**
     * Adding actions
     */
    public function addActions() {
        if(\is_admin()) {
            \add_action('admin_init', [$this, 'customCssInit']);
            \add_action('admin_menu', [$this, 'customCssOptions']);
            \add_action('admin_head', [$this, 'customCssToBackend']);
        }

        if(!\is_admin()) {
            \add_action('wp_head', [$this, 'customCssToFrontend']);
        }
    }

    /**
     * On plugin activation
     */
    public function customCssActivate() {
        $sh_adminbar_add_options = array(
            'custom-css-pluginname' => 'Your Custom CSS',
            'custom-css-pluginversion' => $this->customCssVersion,
            'custom-css-frontend' => $this->customCssGetDefault('frontend'),
            'custom-css-backend' => $this->customCssGetDefault('backend'),
        );

        if(\is_array(\get_option('custom-css-options'))) {
            \add_option('custom-css-options', $sh_adminbar_add_options);
        } else {
            \update_option('custom-css-options', $sh_adminbar_add_options);
        }
    }

    /**
     * Backend init
     */
    public function customCssInit() {
        /**
         * Einstellungen registrieren.
         */
        \register_setting('custom-css-options', 'custom-css-options');

        /**
         * Sprachdatei wählen.
         */
        \load_plugin_textdomain('custom-css', false, \dirname(\plugin_basename(__FILE__)) . '/l10n/');
    }

    /**
     * Defaults
     *
     * @param string $where
     * @return string
     */
    public function customCssGetDefault($where = '') {
        if(!$where) {
            return;
        }

        switch($where) {
            case 'frontend':
                $default = '';
                break;

            case 'backend':
                $default = '';
                break;
        }

        return $default;
    }

    /**
     * Initialize options page
     */
    public function customCssOptions() {
        if(\current_user_can('manage_options')) {
            \add_options_page('Your CSS', \__('Your CSS', 'custom-css'), 'manage_options', \basename(__FILE__, '.php'), [$this, 'customCssOptionsPage']);
        }

        if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'your-custom-css') {
            $cssUri = \plugins_url(\basename(\dirname(__FILE__)) . '/css/custom-css-admin.css');

            /**
             * CSS in Wordpress einbinden
             */
            \wp_register_style('custom-css', $cssUri, array(), $this->customCssVersion, 'screen');
            \wp_enqueue_style('custom-css');
        }
    }

    /**
     * Options page
     */
    public function customCssOptionsPage() {
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
            \check_admin_referer('custom-css-options');

            $newOptions = array(
                'custom-css-frontend' => \stripslashes(\wp_filter_post_kses($_REQUEST['custom-css-frontend'])),
                'custom-css-backend' => \stripslashes(\wp_filter_post_kses($_REQUEST['custom-css-backend']))
            );

            $this->customCssSetOptions($newOptions);

            echo '<div id="message" class="updated fade">';
            echo '<p><strong>';
            \_e('CSS saved.', 'custom-css');
            echo '</strong></p>';
            echo '</div>';
        }
        ?>
        <div class="wrap">
            <div class="icon32" id="icon-options-general"><br /></div>
            <h2><?php \_e('Your Custom CSS', 'custom-css'); ?></h2>
            <p><?php \_e('Define your own CSS for your WordPress Backend and Frontend.', 'custom-css'); ?></p>
            <form method="post" action="" id="wp-twitter-options">
                <?php \wp_nonce_field('custom-css-options'); ?>

                <div class="custom-css-wrapper">
                    <div>
                        <p><?php \_e('Frontend', 'custom-css'); ?></p>
                        <p><textarea name="custom-css-frontend"><?php echo \esc_textarea($this->customCssGetOptions('custom-css-frontend')); ?></textarea></p>
                    </div>
                    <div>
                        <p><?php \_e('Backend', 'custom-css'); ?></p>
                        <p><textarea name="custom-css-backend"><?php echo \esc_textarea($this->customCssGetOptions('custom-css-backend')); ?></textarea></p>
                    </div>
                </div>
                <p class="submit">
                    <input type="submit" name="Submit" value="<?php \_e('Save CSS', 'custom-css'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    public function customCssGetOptions($option = '') {
        $array_PluginOptions = \get_option('custom-css-options');

        if(empty($option)) {
            return $array_PluginOptions;
        } else {
            return $array_PluginOptions[$option];
        }
    }

    public function customCssSetOptions($newOptions = array()) {
        $options = \array_merge((array) \get_option('custom-css-options'), $newOptions);

        \update_option('custom-css-options', $options);
        \wp_cache_set('custom-css-options', $options);

        $this->customCssWrite('frontend', $options['custom-css-frontend']);

        $this->customCssWrite('backend', $options['custom-css-backend']);
    }

    public function customCssWrite($where = '', $cssFile = '') {
        if(empty($cssFile)) {
            \unlink($this->customCssFileDirectory . $where . '.css');
        } else {
            \file_put_contents($this->customCssFileDirectory . $where . '.css', $cssFile);
        }
    }

    public function customCssToBackend() {
        if(\file_exists($this->customCssFileDirectory . 'backend.css')) {
            echo '<link rel="stylesheet" type="text/css" href="' . $this->customCssFileUri . 'backend.css" />';
        }
    }

    function customCssToFrontend() {
        if(\file_exists($this->customCssFileDirectory . 'frontend.css')) {
            echo '<link rel="stylesheet" type="text/css" href="' . $this->customCssFileUri . 'frontend.css" />';
        }
    }
}

/**
 * Start the show
 */
new CustomCss;
