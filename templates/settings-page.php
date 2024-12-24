<?php
// Get current settings
$enabled_admin_bar = get_option('disi_admin_bar_enable', '0');
$enabled_widget = get_option('disi_dashboard_widget_enable', '1');
$enabled_footer = get_option('disi_footer_enable', '0');
$enabled_shortcode = get_option('disi_shortcode_enable', '1');

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
        ['text'  => __('Hosting Server Info','display-server-info'),
         'value' => [
                        ['text'=>__('Operating System','display-server-info'),'value'=>esc_html(PHP_OS)],
                        ['text'=>__('Hostname','display-server-info') ,'value' =>  esc_html(sanitize_text_field(wp_unslash(php_uname( 'n' ))))],
                        ['text'=>__('Server IP','display-server-info') ,'value' => esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR'])))],
                        ['text'=>__('Protocol','display-server-info')  ,'value'=> (isset($_SERVER['SERVER_PROTOCOL']) ? esc_html(sanitize_text_field($_SERVER['SERVER_PROTOCOL'])) : '')],
                        ['text'=>__('Server Software','display-server-info')  ,'value'=> esc_html(sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])))],
                        ['text'=>__('Web Port','display-server-info')  ,'value'=> (isset($_SERVER['SERVER_PORT'])? esc_html(sanitize_text_field($_SERVER['SERVER_PORT'])) :'')],
                        ['text'=>__('CGI Version','display-server-info')  ,'value'=> (isset($_SERVER['GATEWAY_INTERFACE']) ? esc_html(sanitize_text_field($_SERVER['GATEWAY_INTERFACE'])) :'')]
                    ],
        ],

        ['text' => __('PHP Info','display-server-info'),
         'value' => [
                         ['text'=>__('PHP Version','display-server-info'),'value' => esc_html(PHP_VERSION)],
                         ['text'=>__('Memory Limit','display-server-info'),'value' => (function_exists('ini_get') ? ini_get( 'memory_limit' ) : '')],
                         ['text'=>__('Max Execution Time','display-server-info'),'value' => (function_exists('ini_get') ? ini_get('max_execution_time') : '')],
                         ['text'=>__('Upload Max Filesize','display-server-info'),'value' => (function_exists('ini_get') ? ini_get('upload_max_filesize') : '')],
                         ['text'=>__('Max File Uploads','display-server-info'),'value' => (function_exists('ini_get') ? ini_get('max_file_uploads') : '')]
                    ]
        ],

        ['text'=>__('Database Info','display-server-info'),
         'value'=>[
                        ['text'=>__('Server version','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->db_version()))],
                        ['text'=>__('Client version','display-server-info'),'value' => esc_html(sanitize_text_field($clientVersion))],
                        ['text'=>__('Database host','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->dbhost))],
                        ['text'=>__('Database username','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->dbuser))],
                        ['text'=>__('Database name','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->dbname))],
                        ['text'=>__('Table prefix','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->prefix))],
                        ['text'=>__('Database charset','display-server-info'),'value' => esc_html(sanitize_text_field($wpdb->charset))],
                        ['text'=>__('Database collation','display-server-info'),'value' => esc_html(sanitize_text_field($collation))]
                    ]
        ],
];
?>
<p>&nbsp;</p>
<div class="container-fluid">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <div class="tabbable" id="tabs-104416">
                <ul class="nav nav-tabs">
                    <li <?php echo isset($_REQUEST['ref']) && $_REQUEST['ref']=='plugins'?'':'class="active"';?>>
                        <a href="#panel-280630" data-toggle="tab" class="glyphicon glyphicon-info-sign"><?php esc_html_e('Server Info','display-server-info')?></a>
                    </li>
                    <li <?php echo isset($_REQUEST['ref']) && $_REQUEST['ref']=='plugins'?'class="active"':'';?>>
                        <a href="#panel-81025" data-toggle="tab" class="glyphicon glyphicon-cog"><?php esc_html_e('Settings','display-server-info')?></a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane <?php echo isset($_REQUEST['ref']) && $_REQUEST['ref']=='plugins'?'':'active';?>" id="panel-280630">
                        <p>&nbsp;</p>
                        <p>
                            <?php esc_html_e('This page provides detailed information about the server environment, PHP configuration, and database setup. It includes essential data such as server specifications, PHP version and settings, and database connection details to help with troubleshooting and system optimization.', 'display-server-info');?>
                        </p>
                        <p>&nbsp;</p>

                        <div class="row clearfix">
                            <?php foreach ($serverInfo as $k=>$data): ?>
                                <div class="col-md-4 column">
                                    <div class="list-group">
                                        <div class="list-group-item active"><?php echo esc_html($data['text']); ?></div>
                                        <?php
                                        $i = 0;
                                        foreach ($data['value'] as $value):
                                            $bgClass = ($i % 2 == 0) ? '' : 'disi-line-gray-bg';
                                            $i++;
                                            ?>
                                            <div class="list-group-item <?php echo esc_attr($bgClass); ?>">
                                                    <span><?php echo esc_html($value['text']); ?></span> <?php echo esc_html($value['value']); ?>&nbsp;
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if($k==1):?>
                                            <div class="list-group-item">
                                                <button id="btn-phpinfo-output" class="btn btn-default btn-info btn-block" type="button">phpinfo()</button>
                                            </div>
                                        <?php endif;?>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="tab-pane <?php echo isset($_REQUEST['ref']) && $_REQUEST['ref']=='plugins'?'active':'';?>" id="panel-81025">
                        <p>&nbsp;</p>
                        <p>
                            <?php esc_html_e('This page allows you to configure the display of server information in specific locations on your WordPress site. You can easily choose whether to show server information in the following areas: - WordPress Dashboard - Admin Bar - Website Footer With flexible display options, you can customize how and where server information is presented to enhance convenience and manageability.','display-server-info');?>
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
                                            <?php esc_html_e('Show server info in admin bar','display-server-info');?>
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

                                        <div class="checkbox">
                                            <label class="switch">
                                                <input type="checkbox" id="disi_enable_shortcode" name="disi_enable_shortcode" value="1"  <?php checked($enabled_shortcode, '1'); ?> />
                                                <span class="slider round"></span>
                                            </label>
                                            <?php echo esc_html(__('Enable the Shortcode. Use the [disi_server_info] shortcode to show server information on a post or page.','display-server-info'));?>
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
<div class="disi-modal-overlay disi-hidden" id="spinnerModal">
    <div class="disi-loader"></div>
</div>



