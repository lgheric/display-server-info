<?php
/**
 * Plugin Name: Display Server Info
 * Description: This plug-in will create a widget called "server information" in the admin dashboard,
             and the PHP Version, MySQL Version, Server Software, Operating System and WordPress Version
             will be displayed in this new widget.
 * Version: 1.1.0
 * Author: Robert South
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: display-server-info
 * Domain Path: /languages/

*/

if ( !defined( 'ABSPATH' ) ) {
    die( esc_html(__( "Can't load this file directly", 'display-server-info' ) ));
}

// Plugin version
if ( !defined( 'DISI_PLUGIN_VERSION' ) ) {
    define( 'DISI_PLUGIN_VERSION', '1.1.0' );
}

// Plugin base name
if ( !defined( 'DISI_PLUGIN_FILE' ) ) {
    define( 'DISI_PLUGIN_FILE', __FILE__ );
}

// Plugin Folder Path
if ( !defined( 'DISI_PLUGIN_DIR' ) ) {
    define( 'DISI_PLUGIN_DIR', realpath( plugin_dir_path( DISI_PLUGIN_FILE ) ) . '/' );
}

$plugin_url = plugin_dir_url( DISI_PLUGIN_FILE );

if ( is_ssl() ) {
    $plugin_url = str_replace( 'http://', 'https://', $plugin_url );
}
if ( !defined( 'DISI_PLUGIN_URL' ) ) {
    define( 'DISI_PLUGIN_URL', untrailingslashit( $plugin_url ) );
}

if ( !defined( 'DISI_ASSETS_URL' ) ) {
    define( 'DISI_ASSETS_URL', DISI_PLUGIN_URL . '/assets' );
}
if ( !defined( 'DISI_JS_URL' ) ) {
    define( 'DISI_JS_URL', DISI_ASSETS_URL . '/js' );
}
if ( !defined( 'DISI_CSS_URL' ) ) {
    define( 'DISI_CSS_URL', DISI_ASSETS_URL . '/css' );
}
if ( !defined( 'DISI_IMAGES_URL' ) ) {
    define( 'DISI_IMAGES_URL', DISI_ASSETS_URL . '/images' );
}

//----------------- start main-------------------------------


//register styles & js
function disi_register_all_styles_and_js() {
    //css
    wp_register_style('disi-common-style-min-css', DISI_CSS_URL . '/disi-common-style.min.css', array(), DISI_PLUGIN_VERSION );
    wp_register_style('disi-dashboard-style-min-css', DISI_CSS_URL . '/disi-dashboard-style.min.css', array(), DISI_PLUGIN_VERSION );
    wp_register_style('disi-more-style-min-css', DISI_CSS_URL . '/disi-more-style.min.css', array(), DISI_PLUGIN_VERSION );

    wp_register_style('disi-bootstrap-min-css', DISI_CSS_URL.'/bootstrap.min.css', array(), '3.3.5', 'all');

    //js
    wp_register_script( 'disi-common-min-js', DISI_JS_URL . '/disi-common.min.js', array( 'jquery' ), DISI_PLUGIN_VERSION, true );
    wp_register_script( 'disi-ajax-handle-min-js', DISI_JS_URL . '/disi-ajax-handle.min.js', array( 'jquery' ), DISI_PLUGIN_VERSION, true );

    wp_register_script('disi-bootstrap-min-js', DISI_JS_URL.'/bootstrap.min.js', array('jquery'), '3.3.5', true);

}
add_action('admin_enqueue_scripts', 'disi_register_all_styles_and_js');


//---========================start handling ajax reuqest=============================================
function disi_enqueue_scripts_saving_settings() {
    wp_enqueue_scripts(
        'disi-ajax-handle-min-js',
        DISI_JS_URL.'/disi-ajax-handle.min.js',
        array('jquery'),
        '1.0',
        true
    );

    wp_localize_script('disi-ajax-handle-min-js', 'dISIAjaxObject', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('disi_save_settings_nonce'),
        'disiLocalizeText' => disi_load_msg()
    ));

}
add_action('admin_enqueue_scripts', 'disi_enqueue_scripts_saving_settings');

function disi_load_msg(){
        return array(
            "settingsSavedText"  => __( 'Settings saved successfully!', 'display-server-info' ),
            "errorOccurredText"  => __( 'An error occurred when saving settings', 'display-server-info' ),
            "loginTimeoutText"  => __( 'Login timeout, please log in again', 'display-server-info' )
            );
}

function disi_save_settings_action() {
    if (!is_admin() || !defined('DOING_AJAX') || !DOING_AJAX || !isset( $_POST[ 'action' ])  || sanitize_text_field( $_POST[ 'action' ] ) != 'disi_save_settings') {
        wp_send_json_error(array('message' => __('Invalid request', 'display-server-info')), 405);
        wp_die();
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Your login has expired, please log in again!', 'display-server-info')], 401);
        wp_die();
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions.', 'display-server-info')), 403);
        wp_die();
    }

    if (!check_ajax_referer('disi_save_settings_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Invalid request or has expired, please try again.', 'display-server-info')), 403);
        wp_die();
    }

    $enabled_admin_bar = !empty($_POST['disi_enable_admin_bar']) ? '1' : '0';
    $enabled_widget = !empty($_POST['disi_enable_widget']) ? '1' : '0';
    $enabled_footer = !empty($_POST['disi_enable_footer']) ? '1' : '0';

    update_option('disi_admin_bar_enable', $enabled_admin_bar);
    update_option('disi_dashboard_widget_enable', $enabled_widget);
    update_option('disi_footer_enable', $enabled_footer);

    wp_send_json_success(array('message' => __('Settings Saved Successfully!', 'display-server-info')));
}
add_action('wp_ajax_disi_save_settings', 'disi_save_settings_action');

//---========================end of handling ajax reuqest ==============================================

function disi_add_dashboard_widgets() {
    $enabled = get_option('disi_dashboard_widget_enable', '1');
    if ($enabled === '1') {
        wp_add_dashboard_widget(
            'disi_dashboard_widget', // Widget slug.
            'Server Information', // Title.
            'disi_display_server_info' // Display function.
        );
    }
    // Use a hook to move the widget
    add_action('admin_footer', 'disi_move_display_server_info_widget');
}
add_action('wp_dashboard_setup', 'disi_add_dashboard_widgets');

function disi_display_server_info() {

    $html  = '<div class="disi-display-board"><ul>';
    $html .= '<li><span>'.esc_html(__( 'PHP Version', 'display-server-info' )).':</span> '. esc_html(PHP_VERSION) . '</li>';
    $html .= '<li class="disi-line-gray-bg"><span>'.esc_html(__( 'MySQL Version', 'display-server-info' )).':</span> '. esc_html(sanitize_text_field($GLOBALS['wpdb']->db_version())) . '</li>';
    if(isset($_SERVER['SERVER_SOFTWARE'])){
        $html .= '<li><span>'.esc_html(__('Server Software','display-server-info')).':</span> '. esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']))) . '</li>';
    }
    if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
        $html .= '<li class="disi-line-gray-bg"><span>'.esc_html(__('Server IP','display-server-info')).':</span> '. esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR']))).'</li>';
    }
    if ( function_exists( 'php_uname' ) ) {
        $html .= '<li><span>'.esc_html(__('Server Hostname','display-server-info')).':</span> '. esc_html(sanitize_text_field(wp_unslash(php_uname( 'n' )))).'</li>';
    }
    $html .= '<li class="disi-line-gray-bg"><span>'.esc_html(__('Operating System','display-server-info')).':</span> '. esc_html(PHP_OS) . '</li>';

    $html .= '<li><a href="'.admin_url( "options-general.php?page=display_server_info" ).'">'.esc_html(__('More','display-server-info')).'</a></li>';
    $html .= '</ul></div>';

    echo $html;

    do_action( 'disi_end_display_server_info');
}

//load css & js
function disi_move_display_server_info_widget($hook) {

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


}
add_action( 'admin_enqueue_scripts', 'disi_move_display_server_info_widget', 10 );


//---start more info page---
function disi_show_more_server_info_page() {
    add_submenu_page(
        'options-general.php',
        'More Server Info',
        'Display Server Info',
        'manage_options',
        'display_server_info',
        'disi_display_more_server_info_callback'
    );
}
add_action('admin_menu', 'disi_show_more_server_info_page');

function disi_display_more_server_info_callback() {

    $enabled_admin_bar = get_option('disi_admin_bar_enable', '0');
    $enabled_widget = get_option('disi_dashboard_widget_enable', '1');
    $enabled_footer = get_option('disi_footer_enable', '0');

    global $wpdb;

    $clientVersion = null;
    if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
        $clientVersion = $wpdb->dbh->client_info;
    }

    if(empty($wpdb->collation)){
        $query = "SELECT DEFAULT_COLLATION_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = DATABASE()";
        $collation = $wpdb->get_var($query);
    }else{
        $collation = $wpdb->collation;
    }

    $serverInfo = [
        'Hosting Server Info' => [
            __('Operating System','display-server-info') => esc_html(PHP_OS),
            __('Hostname','display-server-info') =>  esc_html(sanitize_text_field(wp_unslash(php_uname( 'n' )))),
            __('Server IP','display-server-info') => esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR']))),
            __('Protocol','display-server-info') => isset($_SERVER['SERVER_PROTOCOL']) ? esc_html(sanitize_text_field($_SERVER['SERVER_PROTOCOL'])) : '',
            __('Server Software','display-server-info') => esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']))),
            __('Web Port','display-server-info') => isset($_SERVER['SERVER_PORT'])? esc_html(sanitize_text_field($_SERVER['SERVER_PORT'])) :'',
            __('CGI Version','display-server-info') => isset($_SERVER['GATEWAY_INTERFACE']) ? esc_html(sanitize_text_field($_SERVER['GATEWAY_INTERFACE'])) :''
        ],
        'PHP Info' => [
            __('PHP Version','display-server-info') => esc_html(PHP_VERSION),
            __('Memory Limit','display-server-info') => function_exists('ini_get') ? ini_get( 'memory_limit' ) : '',
            __('Max Execution Time','display-server-info') => function_exists('ini_get') ? ini_get('max_execution_time') : '',
            __('Upload Max Filesize','display-server-info') => function_exists('ini_get') ? ini_get('upload_max_filesize') : '',
            __('Max File Uploads','display-server-info') => function_exists('ini_get') ? ini_get('max_file_uploads') : ''
        ],
        'Database Info' => [
            __('Server version','display-server-info') => esc_html(sanitize_text_field($wpdb->db_version())),
            __('Client version','display-server-info') => esc_html(sanitize_text_field($clientVersion)),
            __('Database host','display-server-info') => esc_html(sanitize_text_field($wpdb->dbhost)),
            __('Database username','display-server-info') => esc_html(sanitize_text_field($wpdb->dbuser)),
            __('Database name','display-server-info') => esc_html(sanitize_text_field($wpdb->dbname)),
            __('Table prefix','display-server-info') => esc_html(sanitize_text_field($wpdb->prefix)),
            __('Database charset','display-server-info') => esc_html(sanitize_text_field($wpdb->charset)),
            __('Database collation','display-server-info') => esc_html(sanitize_text_field($collation))
        ]
    ];

?>
    <p>&nbsp;</p>
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-md-12 column">
                <div class="tabbable" id="tabs-104416">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#panel-280630" data-toggle="tab" class="glyphicon glyphicon-info-sign"><?php _e('Server Info','display-server-info')?></a>
                        </li>
                        <li >
                            <a href="#panel-81025" data-toggle="tab" class="glyphicon glyphicon-cog"><?php _e('Settings','display-server-info')?></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="panel-280630">
                            <p>&nbsp;</p>
                            <p>
                                <?php esc_html(_e('This page provides detailed information about the server environment, PHP configuration, and database setup. <br/> 
It includes essential data such as server specifications, PHP version and settings, and database connection details to help with troubleshooting and system optimization.', 'display-server-info'));?>
                            </p>
                            <p>&nbsp;</p>

                            <div class="row clearfix">
                                <?php foreach ($serverInfo as $sectionTitle => $data): ?>
                                    <div class="col-md-4 column">
                                        <div class="list-group">
                                            <div class="list-group-item active"><?php echo esc_html($sectionTitle); ?></div>
                                            <?php
                                            $i = 0;
                                            foreach ($data as $key => $value):
                                                $bgClass = ($i % 2 == 0) ? '' : 'disi-line-gray-bg';
                                                $i++;
                                                ?>
                                                <div class="list-group-item <?php echo $bgClass; ?>">
                                                    <span><?php printf(
                                                            /* translators: %s is a placeholder for the server info key */
                                                            __('%s', 'display-server-info'),
                                                            $key
                                                        ); ?>:</span> <?php echo esc_html($value); ?>&nbsp;
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                        <div class="tab-pane" id="panel-81025">
                            <p>&nbsp;</p>
                            <p>
                                <?php _e('**Feature Description:** <br/> 
This page allows you to configure the display of server information in specific locations on your WordPress site.<br/> 
You can easily choose whether to show server information in the following areas:<br/>  
- WordPress Dashboard  <br/>
- Admin Bar  <br/>
- Website Footer  <br/>
<br/>
With flexible display options, you can customize how and where server information is presented to enhance convenience and manageability.','display-server-info');?>
                            </p>
                            <p>&nbsp;</p>
                            <div class="row clearfix">
                                <div class="col-md-12 column">
                                    <form method="post" id="disi_setting_form"  action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                                        <?php wp_nonce_field('disi_save_settings'); ?>
                                        <fieldset>
                                            <legend></legend>
                                            <input type="hidden" name="action" value="disi_save_settings" />
                                            <div class="checkbox">
                                                <label class="switch">
                                                    <input type="checkbox" id="disi_enable_admin_bar" name="disi_enable_admin_bar" value="1" <?php checked($enabled_admin_bar, '1'); ?> />
                                                    <span class="slider round"></span>
                                                </label>
                                                <?php esc_html(_e('Show server info in admin bar','display-server-info'));?>
                                            </div>

                                            <div class="checkbox disi-line-gray-bg">
                                                <label class="switch">
                                                    <input type="checkbox" id="disi_enable_widget" name="disi_enable_widget" value="1"  <?php checked($enabled_widget, '1'); ?> />
                                                    <span class="slider round"></span>
                                                </label>
                                                <?php echo esc_html(__('Show server info as dashboard widget','display-server-info'));?>
                                            </div>

                                            <div class="checkbox">
                                                <label class="switch">
                                                    <input type="checkbox" id="disi_enable_footer" name="disi_enable_footer" value="1"  <?php checked($enabled_footer, '1'); ?> />
                                                    <span class="slider round"></span>
                                                </label>
                                                <?php echo esc_html(__('Show server info in footer','display-server-info'));?>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <div id="disi-ajax-loader" style="display: none;">
        <img src="<?php echo DISI_IMAGES_URL;?>/spinner.gif" alt="Loading...">
    </div>

    <?php
}
//---end of more info page---

function disi_add_server_info_to_admin_bar($wp_admin_bar) {

    if (!current_user_can('manage_options')) {
        return;
    }

    $enabled_admin_bar = get_option('disi_admin_bar_enable', 0);

    if ($enabled_admin_bar != 1) {
        return;
    }

    $php_version = phpversion();
    $mysql_version = $GLOBALS['wpdb']->db_version();
    $server_software = $_SERVER['SERVER_SOFTWARE'];

    $server_info = "<span class='disi-top-admin-bar-red'>PHP: $php_version</span> | <span class='disi-top-admin-bar-red'>MySQL: $mysql_version</span> | <span class='disi-top-admin-bar-red'>Server: $server_software</span>";
    $server_info = '<span class="disi-top-admin-bar-info">' . $server_info . '</span>';

    $wp_admin_bar->add_node([
        'id'    => 'disi_display_server_info',
        'title' => $server_info,
        'href'  => false,
        'parent' => 'top-secondary',
        'meta'  => ['class' => 'disi-server-info']
    ]);
}
add_action('admin_bar_menu', 'disi_add_server_info_to_admin_bar', 100);


function disi_add_server_info_to_footer()
{
    $enabled_footer = get_option('disi_footer_enable', 0);

    if ($enabled_footer != 1) {
        return;
    }

    $php_version = phpversion();
    $mysql_version = $GLOBALS['wpdb']->db_version();
    $server_software = $_SERVER['SERVER_SOFTWARE'];

    echo '<div class="disi-admin-footer-info">';
    echo sprintf(
        __('PHP Version: %s | MySQL Version: %s | Server Software: %s','display-server-info'),
        esc_html($php_version),
        esc_html($mysql_version),
        esc_html($server_software)
    );
    echo '</div>';
}

add_action('admin_footer', 'disi_add_server_info_to_footer',100);


