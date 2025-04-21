<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

function disi_delete_all_disi_options() {
    $option_names = array(
        'disi_dashboard_widget_enable',
        'disi_admin_bar_enable',
        'disi_footer_enable',
        'disi_shortcode_enable'
    );

    foreach ( $option_names as $option_name ) {
        delete_option( sanitize_key( $option_name ) );
    }
}

if ( is_multisite() ) {
    $site_ids = function_exists( 'get_sites' )
        ? get_sites( array( 'fields' => 'ids' ) )
        : wp_list_pluck( wp_get_sites(), 'blog_id' );

    foreach ( $site_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        disi_delete_all_disi_options();
        restore_current_blog();
    }
} else {
    disi_delete_all_disi_options();
}
