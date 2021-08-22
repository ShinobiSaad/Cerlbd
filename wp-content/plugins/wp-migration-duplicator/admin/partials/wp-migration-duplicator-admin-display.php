<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wp_Migration_Duplicator
 * @subpackage Wp_Migration_Duplicator/admin/partials
 */
$wf_admin_view_path=WT_MGDP_PLUGIN_PATH.'admin/views/';
$wf_img_path=WT_MGDP_PLUGIN_URL.'images/';
?>
<div class="wrap">
    <h2 class="wp-heading-inline">
	<?php _e('WordPress Migrator','wp-migration-duplicator');?>
	</h2>
	<div class="nav-tab-wrapper wp-clearfix wf-tab-head">
		<?php
	    $tab_head_arr=array(
	        //'wt-mgdp-help'=>__('Help Guide','wp-migration-duplicator')
	    );
	    if(isset($_GET['debug']))
	    {
	        $tab_head_arr['wt-mgdp-debug']='Debug';
	    }
	    Wp_Migration_Duplicator_Admin::generate_settings_tabhead($tab_head_arr);
	    ?>
	</div>
	<div class="wf-tab-container">
        <?php
        //inside the settings form
        $setting_views_a=array(
                      
        );

        //outside the settings form
        $setting_views_b=array(          
            //'wt-mgdp-help'=>'admin-settings-help.php',           
        );
        if(isset($_GET['debug']))
        {
            $setting_views_b['wt-mgdp-debug']='admin-settings-debug.php';
        }
        ?>
        <form method="post" action="<?php echo esc_url($_SERVER["REQUEST_URI"]);?>" class="wf_settings_form">
            <input type="hidden" value="plugin_settings" class="wt-mgdp_update_action" />
            <?php
            // Set nonce:
            if (function_exists('wp_nonce_field'))
            {
                wp_nonce_field(WT_MGDP_PLUGIN_FILENAME);
            }
            foreach ($setting_views_a as $target_id=>$value) 
            {
                $settings_view=$wf_admin_view_path.$value;
                if(file_exists($settings_view))
                {
                    include $settings_view;
                }
            }
            ?>
            <?php 
            //settings form fields for module
            do_action('wt_mgdp_plugin_settings_form');?>           
        </form>
        <?php
        foreach ($setting_views_b as $target_id=>$value) 
        {
            $settings_view=$wf_admin_view_path.$value;
            if(file_exists($settings_view))
            {
                include $settings_view;
            }
        }
        ?>
        <?php do_action('wt_mgdp_plugin_out_settings_form');?> 
    </div>
</div>
