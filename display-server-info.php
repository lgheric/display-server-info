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

    //out put info
    echo '<div class="disi-display-board"><ul>';
    echo '<li><span>'.esc_html(__( 'PHP Version', 'display-server-info' )).':</span> '. esc_html(PHP_VERSION) . '</li>';
    echo '<li class="disi-line-gray-bg"><span>'.esc_html(__( 'MySQL Version', 'display-server-info' )).':</span> '. esc_html(sanitize_text_field($GLOBALS['wpdb']->db_version())) . '</li>';
    if(isset($_SERVER['SERVER_SOFTWARE'])){
        echo '<li><span>'.esc_html(__('Server Software','display-server-info')).':</span> '. esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']))) . '</li>';
    }
    if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
        echo '<li class="disi-line-gray-bg"><span>'.esc_html(__('Server IP','display-server-info')).':</span> '. esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR']))).'</li>';
    }
    if ( function_exists( 'php_uname' ) ) {
        echo '<li><span>'.esc_html(__('Server Hostname','display-server-info')).':</span> '. esc_html(sanitize_text_field(wp_unslash(php_uname( 'n' )))).'</li>';
    }
    echo '<li class="disi-line-gray-bg"><span>'.esc_html(__('Operating System','display-server-info')).':</span> '. esc_html(PHP_OS) . '</li>';

    echo '<li><a href="'.admin_url( "options-general.php?page=display_server_info" ).'">'.esc_html(__('More','display-server-info')).'</a></li>';
    echo '</ul></div>';

    do_action( 'disi_end_display_server_info');

}

//register styles
function disi_register_all_styles_and_js() {
    //css
    wp_register_style('disi-common-style-css', DISI_CSS_URL . '/disi-common-style.css', array(), DISI_PLUGIN_VERSION );
    wp_register_style('disi-dashboard-style-css', DISI_CSS_URL . '/disi-dashboard-style.css', array(), DISI_PLUGIN_VERSION );
    wp_register_style('disi-more-style-css', DISI_CSS_URL . '/disi-more-style.css', array(), DISI_PLUGIN_VERSION );

    wp_register_style('disi-bootstrap-css', DISI_CSS_URL.'/bootstrap.min.css', array(), '3.3.5', 'all');

    //js
    wp_register_script( 'disi-common-js', DISI_JS_URL . '/disi-common.min.js', array( 'jquery' ), DISI_PLUGIN_VERSION, true );
    wp_register_script('disi-bootstrap-js', DISI_JS_URL.'/bootstrap.min.js', array('jquery'), '3.3.5', true);

}
add_action('admin_enqueue_scripts', 'disi_register_all_styles_and_js');

//load css & js
function disi_move_display_server_info_widget($hook) {

    //
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script('disi-bootstrap-js');
    wp_enqueue_script( 'disi-common-js' );

    //
    // For Dashboard
    if ($hook === 'index.php') {
        wp_enqueue_style('disi-dashboard-style-css');
    }

    // For more page
    if ($hook === 'settings_page_display_server_info') {
        wp_enqueue_style('disi-bootstrap-css' );
        wp_enqueue_style( 'disi-more-style-css' );
    }

    wp_enqueue_style( 'disi-common-style-css' );


}
add_action( 'admin_enqueue_scripts', 'disi_move_display_server_info_widget', 10 );


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

    if (!current_user_can('manage_options')) {
        return;
    }

    $enabled_admin_bar=0;
    $enabled_widget=0;
    $enabled_footer=0;
    $resInfo = '';

    // 保存设置
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!check_admin_referer('disi_dashboard_widget_save_settings')) {
            echo '<div class="error"><p>Nonce 验证失败！</p></div>';
            return;
        }

        // 获取表单值
        $enabled_admin_bar = !empty($_POST['disi_enable_admin_bar']) ? '1' : '0';
        $enabled_widget = !empty($_POST['disi_enable_widget']) ? '1' : '0';
        $enabled_footer = !empty($_POST['disi_enable_footer']) ? '1' : '0';

        update_option('disi_admin_bar_enable', $enabled_admin_bar);
        update_option('disi_dashboard_widget_enable', $enabled_widget);
        update_option('disi_footer_enable', $enabled_footer);

        $resInfo = '<div class="updated"><p>'.esc_html(__('Settings Saved')).'</p></div>';


    }

    // 获取当前设置值
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
                            <a href="#panel-280630" data-toggle="tab"><?php _e('Server Info','display-server-info')?></a>
                        </li>
                        <li >
                            <a href="#panel-81025" data-toggle="tab"><?php _e('Settings','display-server-info')?></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="panel-280630">
                            <h3>Server Information</h3>
                            <p>&nbsp;</p>
                            <p>
                                <?php echo esc_html(__('This page provides detailed information about the server environment, PHP configuration, and database setup. It includes essential data such as server specifications, PHP version and settings, and database connection details to help with troubleshooting and system optimization.', 'display-server-info'));?>
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
                            <h3>Settings</h3>
                            <p>&nbsp;</p>
                            <p>
                                Here you can set the way to display server info,show them in the dadmin bar ,footer,or dashboard(default) whatever you like.
                            </p>
                            <p>&nbsp;</p>
                            <div class="row clearfix">
                                <div class="col-md-12 column">
                                    <form method="post" id="disi_setting_form">
                                        <?php wp_nonce_field('disi_dashboard_widget_save_settings'); ?>
                                        <fieldset>
                                            <legend></legend>
                                            <?php if($resInfo){echo $resInfo;}?>
                                            <div class="checkbox">
                                                <label class="switch">
                                                    <input type="checkbox" id="disi_enable_admin_bar" name="disi_enable_admin_bar" value="1" <?php checked($enabled_admin_bar, '1'); ?> />
                                                    <span class="slider round"></span>
                                                </label>
                                                <?php echo esc_html(__('Show server info in admin bar','display-server-info'));?>
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

    <?php
}



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


