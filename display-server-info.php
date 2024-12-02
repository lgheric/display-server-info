<?php
/**
 * Plugin Name: Display Server Info
 * Description: This plug-in will create a widget called "server information" in the admin dashboard,
             and the PHP Version, MySQL Version, Server Software, Operating System and WordPress Version
             will be displayed in this new widget.
 * Version: 1.0
 * Author: Robert South
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *

*/

if ( !defined( 'ABSPATH' ) ) {
    die( esc_html(__( "Can't load this file directly", 'display-server-info' ) ));
}

// Plugin version
if ( !defined( 'DISI_PLUGIN_VERSION' ) ) {
    define( 'DISI_PLUGIN_VERSION', '1.0.1' );
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

function disi_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'disi_dashboard_widget', // Widget slug.
        'Server Information', // Title.
        'disi_display_server_info' // Display function.
    );
    // Use a hook to move the widget
    add_action('admin_footer', 'disi_move_display_server_info_widget');
}
add_action('wp_dashboard_setup', 'disi_add_dashboard_widgets');

function disi_display_server_info() {

    //out put info
    echo '<p><strong>PHP Version:</strong> ' . esc_html(PHP_VERSION) . '</p>';
    echo '<p><strong>MySQL Version:</strong> ' . esc_html(sanitize_text_field($GLOBALS['wpdb']->db_version())) . '</p>';
    if(isset($_SERVER['SERVER_SOFTWARE'])){
        echo '<p><strong>Server Software:</strong> ' . esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']))) . '</p>';
    }
    echo '<p><strong>Operating System:</strong> ' . esc_html(PHP_OS) . '</p>';
    do_action( 'disi_end_display_server_info');

}

function disi_move_display_server_info_widget() {
    wp_register_script( 'disi-common-js', DISI_JS_URL . '/disi-common.min.js', array( 'jquery' ), DISI_PLUGIN_VERSION, true );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'disi-common-js' );
}
add_action( 'admin_enqueue_scripts', 'disi_move_display_server_info_widget', 10 );
