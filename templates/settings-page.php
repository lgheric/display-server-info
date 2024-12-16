<?php
// Get current settings
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
                    <li <?php echo isset($_REQUEST['ref']) && $_REQUEST['ref']=='plugins'?'':'class="active"';?>>
                        <a href="#panel-280630" data-toggle="tab" class="glyphicon glyphicon-info-sign"><?php _e('Server Info','display-server-info')?></a>
                    </li>
                    <li <?php echo isset($_REQUEST['ref']) && $_REQUEST['ref']=='plugins'?'class="active"':'';?>>
                        <a href="#panel-81025" data-toggle="tab" class="glyphicon glyphicon-cog"><?php _e('Settings','display-server-info')?></a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane <?php echo isset($_REQUEST['ref']) && $_REQUEST['ref']=='plugins'?'':'active';?>" id="panel-280630">
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
                                                            __('%1$s:', 'display-server-info'),
                                                            $key
                                                        ); ?></span> <?php echo esc_html($value); ?>&nbsp;
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                    <div class="tab-pane <?php echo isset($_REQUEST['ref']) && $_REQUEST['ref']=='plugins'?'active':'';?>" id="panel-81025">
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
    <img src="<?php echo $this->plugin_url;?>assets/images/spinner.gif" alt="Loading...">
</div>



