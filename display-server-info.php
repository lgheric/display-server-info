<?php
namespace DisplayServerInfoPlugin;
/**
 * Plugin Name: Display Server Info
 * Description: This plugin including PHP, MySQL, server software,and OS details in the WordPress admin dashboard.
 * It also provides options to show the information in the admin bar and footer.
 * Version: 2.0.0
 * Author: Robert South
 * License: GPLv3 or later
 * Text Domain: display-server-info
 * Domain Path: /languages/
 */

if ( !defined('ABSPATH') ) {
    exit; // Prevent direct access
}

class DisplayServerInfo {

    const VERSION = '2.0.0';
    private $plugin_url;

    public function __construct() {
        $this->plugin_url = plugin_dir_url(__FILE__);

        // Actions
        add_action('admin_enqueue_scripts', [$this, 'handle_css_js']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        add_action('admin_bar_menu', [$this, 'add_admin_bar_info'], 100);
        add_action('admin_footer', [$this, 'add_footer_info']);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('wp_ajax_disi_save_settings', [$this, 'handle_ajax_request']);

        // Filters
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_action_links']);
    }

    public function handle_css_js($hook) {

        wp_register_style('disi-common-style-min-css', $this->plugin_url . 'assets/css/disi-common-style.min.css', [], self::VERSION );
        wp_register_style('disi-dashboard-style-min-css', $this->plugin_url . 'assets/css/disi-dashboard-style.min.css', [], self::VERSION );
        wp_register_style('disi-more-style-min-css', $this->plugin_url . 'assets/css/disi-more-style.min.css', [], self::VERSION );

        wp_register_style('disi-bootstrap-min-css', $this->plugin_url.'assets/css/bootstrap.min.css', array(), '3.3.5', 'all');


        wp_register_script( 'disi-common-min-js', $this->plugin_url . 'assets/js/disi-common.min.js', array( 'jquery' ), self::VERSION, true );
        wp_register_script( 'disi-ajax-handle-min-js', $this->plugin_url . 'assets/js/disi-ajax-handle.min.js', array( 'jquery' ), self::VERSION, true );

        wp_register_script('disi-bootstrap-min-js', $this->plugin_url . 'assets/js/bootstrap.min.js', array('jquery'), '3.3.5', true);

        //
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script('disi-bootstrap-min-js');
        wp_enqueue_script( 'disi-common-min-js' );

        //
        // For Dashboard
        if ($hook === 'index.php') {
            wp_enqueue_style('disi-dashboard-style-min-css');
        }

        // For more page
        if ($hook === 'settings_page_display_server_info') {
            wp_enqueue_style('disi-bootstrap-min-css' );
            wp_enqueue_style( 'disi-more-style-min-css' );

            wp_enqueue_script( 'disi-ajax-handle-min-js' );
        }

        wp_enqueue_style( 'disi-common-style-min-css' );

        // Localize script for AJAX
        wp_localize_script('disi-ajax-handle-min-js', 'disiAjaxObject', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('disi_save_settings_nonce'),
            'disiLocalizeText' => $this->load_msg()
        ]);

    }

    public function add_dashboard_widget() {
        if (get_option('disi_dashboard_widget_enable', '1') === '1') {
            wp_add_dashboard_widget('disi_dashboard_widget', __('Server Information', 'display-server-info'), [$this, 'display_dashboard_widget']);
        }
    }

    public function display_dashboard_widget() {
        $server_info = $this->get_server_info();

        $html  = '<div class="disi-display-board"><ul>';
        $i=0;
        foreach ($server_info as $key => $value) {
            $class = $i%2==0 ? '' : 'class="disi-line-gray-bg"';
            $html .= '<li '.$class.'>';
            $html .= '<span>' . esc_html($key) . ':</span> '.esc_html($value);
            $html .= '</li>';
            $i++;
        }
        $html .= '<li><a href="'.admin_url( "options-general.php?page=display_server_info" ).'">'.esc_html(__('More','display-server-info')).'</a></li></ul></div>';
        echo $html;

        // Add action for extending the widget
        do_action('disi_dashboard_widget_after_content');
    }

    public function add_admin_bar_info($wp_admin_bar) {
        if (!current_user_can('manage_options') || get_option('disi_admin_bar_enable', '0') !== '1') {
            return;
        }

        $server_info = $this->get_server_info();
        $info = sprintf('PHP: %s | MySQL: %s | Server: %s', $server_info['PHP Version'], $server_info['MySQL Version'], $server_info['Server Software']);

        $wp_admin_bar->add_node([
            'id' => 'disi_display_server_info',
            'title' => esc_html($info),
            'href'  => false,
            'parent' => 'top-secondary',
            'meta' => ['class' => 'disi-server-info']
        ]);
    }

    public function add_footer_info() {
        if (get_option('disi_footer_enable', '0') === '1') {
            $server_info = $this->get_server_info();
            echo '<div class="disi-admin-footer-info">';
            echo sprintf(
                'PHP Version: %s | MySQL Version: %s | Server Software: %s',
                esc_html($server_info['PHP Version']),
                esc_html($server_info['MySQL Version']),
                esc_html($server_info['Server Software'])
            );
            echo '</div>';
        }
    }

    public function add_settings_page() {
        add_submenu_page(
            'options-general.php',
            __('More - Display Server Info', 'display-server-info'),
            __('Display Server Info', 'display-server-info'),
            'manage_options',
            'display_server_info',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        include  plugin_dir_path(__FILE__) . 'templates/settings-page.php';
    }

    public function handle_ajax_request() {
        if (!$this->is_valid_request()) {
            wp_send_json_error(__('Invalid request', 'display-server-info'), 400);
            wp_die();
        }

        if (!is_user_logged_in()) {
            wp_send_json_error(__('Your login has expired, please log in again!', 'display-server-info'), 401);
            wp_die();
        }

        if (!check_ajax_referer('disi_save_settings_nonce', 'nonce', false)) {
            wp_send_json_error(__('Illegal request', 'display-server-info'), 418);
            wp_die();
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'display-server-info'), 403);
            wp_die();
        }

        $this->update_settings();

        wp_send_json_success(__('Settings saved successfully', 'display-server-info'));
        wp_die();
    }

    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=display_server_info&ref=plugins') . '">' . __('Settings', 'display-server-info') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    private function get_server_info() {
        global $wpdb;

        return [
            __('PHP Version', 'display-server-info') => PHP_VERSION,
            __('MySQL Version', 'display-server-info') => sanitize_text_field($wpdb->db_version()),
            __('Server Software', 'display-server-info') => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '',
            __('Server IP', 'display-server-info') => isset($_SERVER['SERVER_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR'])) : '',
            __('Server Hostname', 'display-server-info') => function_exists( 'php_uname' ) ? sanitize_text_field(wp_unslash(php_uname( 'n' ))) : '',
            __('Operating System', 'display-server-info') => PHP_OS,
        ];
    }

    private function is_valid_request() {
        return is_admin() && defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && sanitize_text_field($_POST['action']) === 'disi_save_settings';
    }

    private function update_settings() {
        update_option('disi_admin_bar_enable', isset($_POST['disi_enable_admin_bar']) ? '1' : '0');
        update_option('disi_dashboard_widget_enable', isset($_POST['disi_enable_widget']) ? '1' : '0');
        update_option('disi_footer_enable', isset($_POST['disi_enable_footer']) ? '1' : '0');
    }

    private function load_msg(){
        return [
            "settingsSavedText"  => __( 'Settings saved successfully!', 'display-server-info' ),
            "errorOccurredText"  => __( 'An error occurred when saving settings', 'display-server-info' ),
            "invalidRequestText"  => __( 'Invalid request', 'display-server-info' ),
            "loginTimeoutText"  => __( 'Login timeout, please log in again', 'display-server-info' ),
            "illegalRequestText"  => __( 'Illegal request', 'display-server-info' ),
            "permissionDeniedText"  => __( 'Permission denied', 'display-server-info' )
        ];
    }

}

new DisplayServerInfo();
