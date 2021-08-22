<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
$settings_button_title=isset($settings_button_title) ? $settings_button_title : 'Update Settings';
?>
<div style="clear: both;"></div>
<div class="wf-plugin-toolbar bottom">
    <div class="left">
    </div>
    <div class="right">
        <input type="submit" name="update_admin_settings_form" value="<?php _e($settings_button_title,'wp-migration-duplicator'); ?>" class="button button-primary" style="float:right;"/>
        <span class="spinner" style="margin-top:11px"></span>
    </div>
</div>