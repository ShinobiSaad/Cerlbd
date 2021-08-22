<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<style type="text/css">
.wt_mgdp_string{ color:darkgreen; }
.wt_mgdp_builtin{ color:blue; }
.wt_mgdp_fn{ color:brown; }
.wt_mgdp_arg{ color:orange; }
.wt_mgdp_cmnt{ color:gray; }
.wt_mgdp_code_example{padding:20px; font-size:14px; background:#f6f6f6; box-shadow:inset 1px 1px 1px 0px #ccc; display:none;}
.wt_mgdp_code_example_readmore{ cursor:pointer; }
.wt_mgdp_code_indent{ padding-left:40px; display:inline-block; }
</style>
<div style="padding-top:15px; padding-bottom:35px;">
	<h3><?php _e('Export','wp-migration-duplicator');?></h3>

	<p><?php _e('Generates a complete dump of the wp-contents in a zip format. You can also replace the text within the database file to match the destination for a smooth import.','wp-migration-duplicator');?>
		<br />
		<?php _e(sprintf('Note: In case of an error during export try excluding large files(Eg: Backup files from other backup plugins) and try again. You can do this by using the filter %s.','<i>`wt_mgdp_exclude_files`</i>'),'wp-migration-duplicator'); ?>
		<a class="wt_mgdp_code_example_readmore" data-more-text="<?php _e('Read more');?>" data-less-text="<?php _e('Read less');?>"><?php _e('Read more');?></a>
	</p>
	<p class="wt_mgdp_code_example">
		<span class="wt_mgdp_cmnt"> // to exclude file/folder</span> <br />
		<span class="wt_mgdp_fn">add_filter</span>(<span class="wt_mgdp_string">'wt_mgdp_exclude_files'</span>, <span class="wt_mgdp_string">'wt_mgdp_exclude_files_fn'</span>);<br />
		<span class="wt_mgdp_builtin">function</span> <span class="wt_mgdp_fn">wt_mgdp_exclude_files_fn</span>(<span class="wt_mgdp_arg">$arr</span>)<br />
		{ <br />
		<span class="wt_mgdp_code_indent">
		$arr[]=<span class="wt_mgdp_string">'uploads/backup-guard'</span>; <span class="wt_mgdp_cmnt"> // add folder/file path relative to wp-content folder</span> <br />
		$arr[]=<span class="wt_mgdp_string">'ai1wm-backups'</span>; <br />
		<span class="wt_mgdp_builtin">return</span> $arr; 
		</span><br />
		} <br />
		<br />
		<br />
		<span class="wt_mgdp_cmnt"> // to exclude file types</span> <br />
		<span class="wt_mgdp_fn">add_filter</span>(<span class="wt_mgdp_string">'wt_mgdp_exclude_extensions'</span>, <span class="wt_mgdp_string">'wt_mgdp_exclude_extensions_fn'</span>);<br />
		<span class="wt_mgdp_builtin">function</span> <span class="wt_mgdp_fn">wt_mgdp_exclude_extensions_fn</span>(<span class="wt_mgdp_arg">$arr</span>)<br />
		{ <br />
		<span class="wt_mgdp_code_indent">
		$arr[]=<span class="wt_mgdp_string">'zip'</span>; <br />
		$arr[]=<span class="wt_mgdp_string">'png'</span>; <br />
		<span class="wt_mgdp_builtin">return</span> $arr; 
		</span><br />
		} <br />
	</p>
	
	<div style="float:left; width:100%; box-sizing:border-box; padding:3px; margin-bottom:0px;">
		<div class="wf_progress_bar_label"></div>
		
		<div class="wf_export_main" style="display:none;">
			<div class="wf_progress_bar_label"></div>
			<div class="wf_progress_bar">
				<div class="wf_progress_bar_inner">
					0%
				</div>
			</div>
		</div>

		<div class="wf_export_sub" style="display:none;">
			<div class="wf_progress_bar_label"></div>
			<div class="wf_progress_bar">
				<div class="wf_progress_bar_inner">
					0%
				</div>
			</div>
		</div>	
	</div>
	<table class="wf-form-table wt_mgdp_export_find_replace_tb">
		<tr class="">
			<td>
				<p style="margin-top:15px; float:left;">
					<?php _e(sprintf('Find and replace %stext%s with %sanother text%s in the database.','<i><b>&lt;','&gt;</b></i>','<i><b>&lt;','&gt;</b></i>'),'wp-migration-duplicator'); ?>
				</p>
			</td>
		</tr>
		<tr class="wt_mgdp_export_find_replace_row">
			<td>
				<div style="float:left; width:200px; margin-right:15px;">
					<input type="text" name="find[]" placeholder="<?php _e('Find','wp-migration-duplicator'); ?>">
				</div>
				<div style="float:left; width:200px; margin-right:15px;">
					<input type="text" name="replace[]" placeholder="<?php _e('Replace with','wp-migration-duplicator'); ?>">
				</div>
				<div style="float:left; width:40px;">
					<button class="button wt_mgdp_export_find_replace_btn_add button-secondary" title="<?php _e('Add new row','wp-migration-duplicator'); ?>">
						<span class="dashicons dashicons-plus-alt" style="margin-top:2px;"></span>
					</button>
					<button class="button wt_mgdp_export_find_replace_btn_delete button-secondary" style=" display:none;" title="<?php _e('Delete row','wp-migration-duplicator'); ?>">
						<span class="dashicons dashicons-dismiss" style="margin-top:2px;"></span>
					</button>
				</div>
			</td>
		</tr>
	</table>
</div>
<div style="clear: both;"></div>
<div class="wf-plugin-toolbar bottom">
    <div class="left">
    </div>
    <div class="right">
        <button name="wt_mgdp_export_btn" class="button button-primary" style="float:right;"><?php _e('Export','wp-migration-duplicator'); ?></button>
        <button name="wt_mgdp_export_stop_btn" class="button button-primary" style="float:right; display:none; margin-right:10px;"><?php _e('Stop export','wp-migration-duplicator'); ?></button>
        <span class="spinner" style="margin-top:11px;"></span>
    </div>
</div>