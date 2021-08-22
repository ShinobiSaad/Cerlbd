<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<style type="text/css">
.wt_mgdp_import_log_main{ display:none; font-weight:bold; padding-bottom:5px;}
.wt_mgdp_import_loglist_main{display:none; float:left; width:100%; height:200px; overflow:auto; padding:10px 0px; margin-bottom:20px; background:#fdfdfd; box-shadow:inset 0 0 3px #ccc;}
.wt_mgdp_import_loglist_inner{float:left; width:98%; height:auto; overflow:auto; margin:0px 1%; font-style:italic;}

.wt_mgdp_import_form{ float:left; width:auto; margin-top:-5px; }
.wt_mgdp_import_form td{padding:5px 10px; }
.wt_mgdp_import_form label{ font-weight:bold; }
</style>
<div style="padding-top:15px; padding-bottom:35px;">
	
	<h3><?php _e('Import','wp-migration-duplicator');?></h3>
	<p><?php _e('Import data via a zip file(containing data from the server to be migrated).','wp-migration-duplicator'); ?></p>
	<div class="wt_mgdp_import_log_main"></div>
	<div class="wt_mgdp_import_loglist_main">
		<div class="wt_mgdp_import_loglist_inner">
			
		</div>
	</div>

	<table class="wt_mgdp_import_form" style="margin-bottom:20px; margin-top:10px;">
		<tr class="wt_mgdp_import_er" style="display:none;">
			<td colspan="3" style="color:red;"></td>
		</tr>
		<tr>
	   		<td>
	   			<input type="hidden" name="attachment_url" id="attachment_url">
	    		<input style="text-align:center;" type="button" name="upload-btn" id="upload-btn" class="button button-primary" value="<?php _e('Upload backup file', 'wp-migration-duplicator') ?>">
	    	</td>
	    	<td class="wt_mgdp_import_attachment_url"></td>
		</tr>
    </table>
    <div style="clear: both;"></div>
    <div class="wt_info_box" style=" width:98%; margin:0px 1%;">
		<?php _e('Bypass upload limit: Cases where your server upload limit is smaller than the size of the file to be imported, follow below steps.', 'wp-migration-duplicator') ?>
		<ul style="list-style:disc; margin-left:20px;">
			<li><?php _e('First upload a small zip file into media library and replace that file through FTP with actual backup zip.','wp-migration-duplicator');?></li>
			<li><?php _e('Choose this file from media by clicking on the Upload button.','wp-migration-duplicator');?></li>
			<li><?php _e('Then click on import to process the file.','wp-migration-duplicator');?></li>
		</ul>
	</div>
</div>

<div class="wf-plugin-toolbar bottom">
    <div class="left">
    </div>
    <div class="right">
        <button name="wt_mgdp_import_btn" class="button button-primary" style="float:right;"><?php _e('Import','wp-migration-duplicator'); ?></button>
        <button name="" class="button button-primary wt_mgdp__start_new_import" style="float:right; display:none;"><?php _e('Start new import','wp-migration-duplicator'); ?></button>
        <span class="spinner" style="margin-top:11px;"></span>
    </div>
</div>