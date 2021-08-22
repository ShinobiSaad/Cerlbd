<?php
if (!defined('ABSPATH')) {
	exit;
}

/* scan the directory and make the zip list */
$zip_list=array();
if(is_dir(Wp_Migration_Duplicator::$backup_dir))
	{
		foreach (new DirectoryIterator(Wp_Migration_Duplicator::$backup_dir) as $file)
		{
			if($file->isFile())
			{
				$file_name=$file->getFilename();
				$file_ext_arr=explode(".",$file_name);
				$file_ext=end($file_ext_arr);
				if($file_ext=='zip')
				{
					$zip_list[$file_name]=array(content_url().Wp_Migration_Duplicator::$backup_dir_name."/".$file_name,$file->getSize());
				}
			}
		}
	}
?>
<div style="padding-top:15px; padding-bottom:35px;">
	<h3><?php _e('Backups','wp-migration-duplicator');?> <?php do_action('wt_mgdp_backups_head',$backup_list,$offset);?></h3>
	<p>
		<?php _e('Lists the activity log for every export with the option to restore from a backup or delete the logs that are no longer necessary.','wp-migration-duplicator');?>
	</p>
	<?php
	if($total_list>20)
	{
		?>
		<div class="wt_warn_box">
			<?php _e('Your backups going larger. Deleting unwanted backups will save space.','wp-migration-duplicator');?>
		</div>
		<?php
	}
	do_action('wt_mgdp_backups_table_top',$backup_list,$offset);
	?>
	<table class="wt_mgdp_list_table wt_mgdp_backup_list_table">
		<thead>
			<tr>
				<th style="width:50px;">#</th>
				<th><?php _e('File','wp-migration-duplicator');?></th>
				<th><?php _e('Date','wp-migration-duplicator');?></th>
				<th><?php _e('Size','wp-migration-duplicator');?></th>
				<th><?php _e('Status','wp-migration-duplicator');?></th>
				<th><?php _e('Actions','wp-migration-duplicator');?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$num=$offset;
			foreach($backup_list as $backup)
			{
				$log_data=json_decode($backup['log_data'],true);
				$file_name=(isset($log_data['backup_file']) ? $log_data['backup_file'] : '');
				$file_path=Wp_Migration_Duplicator::$backup_dir.'/'.$file_name;
				$file_exists=(file_exists($file_path) && $file_name!="" ? true : false);
				$file_url='';
				$num++;
				if(isset($zip_list[$file_name]))
				{
					unset($zip_list[$file_name]);
				}
				?>
				<tr>
					<td>
						<?php echo $num;?>
					</td>
					<td>
						<?php 
							if($file_exists)
							{
								$file_url=content_url().Wp_Migration_Duplicator::$backup_dir_name."/".$file_name;
							?>
								<a href="<?php echo $file_url;?>" target="_blank">
									<?php echo $file_name; ?>
								</a>
							<?php
							}else
							{
								echo $file_name.' <span style="color:red; display:inline;">('.__('File not found','wp-migration-duplicator').')</span>';
							}
					 	?>					 	
					 </td>
					<td><?php echo date('Y-m-d h:i:s A',$backup['created_at']); ?></td>
					<td>
						<?php
						if($file_exists)
						{
							echo Wp_Migration_Duplicator::format_size_units(filesize($file_path));
						}
						?>
					</td>
					<td>
						<?php
						echo Wp_Migration_Duplicator::get_status_label($backup['status']);
						?>
					</td>
					<td>
						<?php do_action('wt_mgdp_backups_action_column',$backup,$file_exists,$file_url); ?>
						<button data-id="<?php echo $backup['id_wtmgdp_log']; ?>" title="<?php _e('Delete','wp-migration-duplicator'); ?>" class="button button-secondary wt_mgdp_delete_backup"><span class="dashicons dashicons-no-alt" style="margin-top:4px;"></span></button>
					</td>
				</tr>
				<?php
			}
			$no_bckup_html='<tr><td colspan="6" style="text-align:center; padding:10px">'.__('No backups found.','wp-migration-duplicator').'</td></tr>';
			if(count($backup_list)==0)
			{
				echo $no_bckup_html;
			}
			?>
		</tbody>		
	</table>

	<div class="wt_mgdp_other_backupfiles">
	<?php
	if(count($zip_list)>0)
	{
		?>
		<h4 style="margin-top:30px; margin-bottom:5px;"><?php _e("Archived files in the backup directory, that are not in our backup list.",'wp-migration-duplicator'); ?></h4>
		<table class="wt_mgdp_list_table" style="margin-top:5px;">
			<thead>
				<tr>
					<th style="width:50px;">#</th>
					<th><?php _e('File','wp-migration-duplicator');?></th>
					<th><?php _e('Size','wp-migration-duplicator');?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$file_num=0;
				foreach($zip_list as $key=>$value)
				{
					$file_num++;
					?>
					<tr>
						<td><?php echo $file_num;?></td>
						<td>
							<a href="<?php echo $value[0];?>" target="_blank">
								<?php echo $key; ?>
							</a>
						</td>
						<td>
							<?php echo Wp_Migration_Duplicator::format_size_units($value[1]);?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
	?>
	</div>
</div>
<script type="text/javascript">
	var wt_mgdp_no_bckup_html='<?php echo $no_bckup_html;?>';
</script>