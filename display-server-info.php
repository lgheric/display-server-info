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
    // Use a hook to move the widget
    add_action('admin_footer', 'dsi_move_display_server_info_widget');
}
add_action('wp_dashboard_setup', 'dsi_add_dashboard_widgets');

function dsi_display_server_info() {
    echo '<p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';
    echo '<p><strong>MySQL Version:</strong> ' . $GLOBALS['wpdb']->db_version() . '</p>';
    echo '<p><strong>Server Software:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</p>';
    echo '<p><strong>Operating System:</strong> ' . PHP_OS . '</p>';
    do_action( 'dsi_end_display_server_info');

}

function dsi_move_display_server_info_widget() {
    echo "
    <script type='text/javascript'>
    jQuery(document).ready(function($) {
        var displayServerInfoWidget = $('#dsi_dashboard_widget');
        var atAGlance = $('#dashboard_right_now');

        if (atAGlance.length && displayServerInfoWidget.length) {
            displayServerInfoWidget.insertAfter(atAGlance);
        }
    });
    </script>
    ";
}
