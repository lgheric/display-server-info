<?php
/*
Plugin Name: Display Server Info
Description: This plug-in will create a widget called "server information" in the admin dashboard,
             and the PHP Version, MySQL Version, Server Software, Operating System and WordPress Version
             will be displayed in this new widget.
Version: 1.0
Author: Robert South
*/


function dsi_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'dsi_dashboard_widget', // Widget slug.
        'Server Information', // Title.
        'dsi_display_server_info' // Display function.
    );
}
add_action('wp_dashboard_setup', 'dsi_add_dashboard_widgets');

function dsi_display_server_info() {
	global $wp_version;		
    echo '<p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';
    echo '<p><strong>MySQL Version:</strong> ' . $GLOBALS['wpdb']->db_version() . '</p>';
    echo '<p><strong>Server Software:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</p>';
    echo '<p><strong>Operating System:</strong> ' . PHP_OS . '</p>';
   	echo '<p><strong>WordPress Version:</strong> ' . $wp_version . '</p>';
    do_action( 'dsi_finish_info');

}
