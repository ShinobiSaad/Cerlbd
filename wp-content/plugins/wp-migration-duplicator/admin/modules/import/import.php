<?php
/**
 * Import section of the plugin
 *
 * @link       
 * @since 1.1.2     
 *
 * @package  Wp_Migration_Duplicator  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wp_Migration_Duplicator_Import
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='import';
	public $step_list=array(
		'start_import',
		'import_db',
	);
	public $ajax_action_list=array(
		'start_import',
		'import_db',
	);
	public $button_click_enabled=false;

	public function __construct()
	{
		$this->module_id=Wp_Migration_Duplicator::get_module_id($this->module_base);
		add_action('wp_ajax_wt_mgdp_import',array($this,'ajax_main'),1);

		add_filter('wt_mgdp_plugin_settings_tabhead',array($this,'settings_tabhead'));
		add_action('wt_mgdp_plugin_out_settings_form',array($this,'out_settings_form'));
		add_action('wt_mgdp_backups_action_column',array($this,'restore_backup_btn'),10,3);
		add_action('wt_mgdp_backups_table_top',array($this,'restore_notice'),10,2);
	}

	/**
	* 	@since 1.1.2
	*	Showing a notice on top of the backup list table
	*/
	public function restore_notice($backup_list,$offset)
	{
		?>
		<div class="wt_warn_box">
			<?php _e('The backups are maintained in wp-content/webtoffee_migrations folder. We recommend that you either delete these backup when no longer require or secure them accordingly.','wp-migration-duplicator');?>
			<br /><br />
			<?php _e('Restoring from backup will overwrite all files in the system, including the existing backups. It will basically take you to the system state at the time of backup file.','wp-migration-duplicator');?>
			<br />
			<?php _e('Do not restore unless you are sure about what you are doing.','wp-migration-duplicator');?>
		</div>
		<?php
	}


	/**
	* 	@since 1.1.2
	*	Showing a restore button on restore list table (if file exists)
	*/
	public function restore_backup_btn($backup,$file_exists,$file_url)
	{
		if($file_exists && $backup['status']==Wp_Migration_Duplicator::$status_complete)
		{
		?>
			<button data-file-url="<?php echo $file_url; ?>" data-id="<?php echo $backup['id_wtmgdp_log']; ?>" title="<?php _e('Restore','wp-migration-duplicator'); ?>" class="button button-secondary wt_mgdp_restore_backup" style=""><span class="dashicons dashicons-update-alt" style="margin-top:4px;"></span></button>
		<?php
		}
		if(!$this->button_click_enabled)
		{
			?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('.wt_mgdp_restore_backup').unbind('click').click(function(){
						if(confirm('<?php _e('Are you sure?','wp-migration-duplicator');?>'))
						{
							var file_url=jQuery(this).attr('data-file-url');
							if(jQuery.trim(file_url)=="")
							{
								alert('<?php _e('Error','wp-migration-duplicator');?>');
								return false;
							}
							window.location.hash="#wt-mgdp-import"; /* switching tab */
							jQuery('[name="attachment_url"]').val(file_url);
							jQuery('.wt_mgdp_import_attachment_url').html(file_url);
							jQuery('[name="wt_mgdp_import_btn"]').trigger('click');
						}
					});
				});
			</script>
			<?php
			$this->button_click_enabled=true;
		}
	}

	/**
	* 	@since 1.1.2 	Main ajax hook to handle all ajax requests
	*	@since 1.1.5 	User role checking enabled
	*/
	public function ajax_main()
	{
		//sleep(1);
		ini_set('max_execution_time', 300);
        ini_set('memory_limit', '-1');
        set_time_limit(0);

		$action=sanitize_text_field($_POST['sub_action']);
		$out=array(
			'status'=>false,
			'msg'=>__('Error','wp-migration-duplicator'),
			'step_finished'=>0,
			'finished'=>0,
			'step'=>$action,
			'label'=>'',
			'sub_label'=>'',
		);

		/**
		*	@since 1.1.5
		*	User role checking enabled
		*/
		if(!Wp_Migration_Duplicator_Admin::check_write_access($this->module_id))
		{
			echo json_encode($out);
			exit();
		}

		if(in_array($action,$this->ajax_action_list) && method_exists($this,$action))
		{
			$out=$this->{$action}($out);
		}else
		{
			//error
		}

		if($out['step_finished']==1) //step finished move to next step
		{
			$step_array_key=array_search($action,$this->step_list);
			if(isset($this->step_list[$step_array_key+1])) //next step exists
			{
				$out['step']=$this->step_list[$step_array_key+1];
			}else
			{
				$out['finished']=1;
				$out['label']='<span style="color:green; font-weight:bold;">'.__('Import completed.','wp-migration-duplicator').'</span>';
			}
		}
		echo json_encode($out);
		exit();
	}

	/**
	* @since 1.1.2
	* import database
	*/
	private function import_db($out)
	{
		global $wpdb;

		/*  check backup file exists */
		$upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $filename = $upload_dir."/" . 'database.sql';
        if(!file_exists($filename))
        {
        	$out['msg']=__('Database backup file is missing. Unable to import database','wp-migration-duplicator');
        	$out['sub_label']='<br /><span style="color:red;">'.$out['msg'].'</span>';
        	return $out;
        }

        /*  check DB connection is possible */
        $connection =@mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);                       
        @mysqli_query($connection, "SET FOREIGN_KEY_CHECKS = 0;");
        
        $mysql_version =substr(mysqli_get_server_info($connection), 0, 3); // Get Mysql Version
        if (mysqli_connect_errno())
        {
        	$out['msg']=__('Unable to connect to database.','wp-migration-duplicator');
        	$out['sub_label']='<br /><span style="color:red;">'.$out['msg'].'</span>';
        	return $out;
        }


        $templine = '';
        $error_count=0;
        $non_error_count=0;
        $fp=fopen($filename,'r');
        // Loop through each line
        while(($line=fgets($fp))!== false)
        {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            // Add this line to the current segment
            $templine .= $line;
            $templine = str_replace('webtoffee_', $wpdb->prefix, $templine);
            if($mysql_version >= 5.5)
            {
                $templine = str_replace('utf8mb4_unicode_520_ci', 'utf8mb4_unicode_ci', $templine);
            }

            // If it has a semicolon at the end, it's the end of the query
            if(substr(trim($line), -8, 8) == ';/*END*/')
            {
                // Perform the query
                if(!@mysqli_query($connection, $templine))
                {
                   $error_count++; 
                }else
                {
                 	$non_error_count++;
                }
                // Reset temp variable to empty
                $templine = '';
            }
        }

        @mysqli_query($connection,"SET FOREIGN_KEY_CHECKS = 1;");    
        @mysqli_close($connection);
        fclose($fp);

        if($non_error_count==0) 
        {
        	$out['msg']=__('Database import failed.','wp-migration-duplicator');
        	$out['sub_label']='<br /><span style="color:red;">'.$out['msg'].'</span>';
        }else
        {
        	if($error_count>0)
        	{
        		$out['msg']=__('Database import done with errors.','wp-migration-duplicator');
        		$out['sub_label']='<br /><span style="color:red;">'.$out['msg'].'</span>';
        	}else
        	{
        		$out['msg']=__('Database imported.','wp-migration-duplicator');
        		$out['sub_label']='<br />'.$out['msg'].'<br />'.__('Import completed.','wp-migration-duplicator');
        		$out['label']='';     		
        	}
        	$out['step_finished']=1;
        	$out['status']=true;
        }
        return $out;
	}

	/**
	* 	@since 1.1.2 
	* 	start the import (Import files and directories)
	*
	*/
	private function start_import($out)
	{
        $extract_to = WP_CONTENT_DIR;
        $attachment_url=sanitize_text_field($_POST['attachment_url']);
        $parse_url=parse_url($attachment_url);
        $real_url=$_SERVER['DOCUMENT_ROOT'].($parse_url['path']);
        if(!strpos($real_url,'.zip'))
        {
 			$out['msg']=__("Please upload Zip file",'wp-migration-duplicator');
 			$out['sub_label']='<br /><span style="color:red;">'.$out['msg'].'</span>';
 			return $out;
        }

        /* extracting zip file */
        $zip=new ZipArchive;
        $zip->open($real_url);
        $zip->extractTo($extract_to);
        $imported=$zip->close();
        $out['status']=true;
        $out['step_finished']=1;
        $out['label']=__('Importing database....','wp-migration-duplicator');
        $out['sub_label']='<br />'.__('Files imported.','wp-migration-duplicator').'<br />'.__('Importing database....','wp-migration-duplicator');
        return $out;
	}

	/**
	 *  @since 1.1.2
	 * 	Import tab head filter callback
	 **/
	public function settings_tabhead($arr)
	{
		$out=array();
		$added=0;
		foreach($arr as $k=>$v)
		{
			$out[$k]=$v;
			if($k=='wt-mgdp-export') //add after export
			{
				$out['wt-mgdp-import']=__('Import','wp-migration-duplicator');
				$added=1;
			}
		}
		if($added==0) //no export menu, then add it as first item
		{
			$out=array_merge(array('wt-mgdp-import'=>__('Import','wp-migration-duplicator')),$arr);
		}
		return $out;
	}

	/**
	 *  @since 1.1.2
	 * 	Import page tab content filter callback
	**/
	public function out_settings_form($arr)
	{
		wp_enqueue_script($this->module_id,plugin_dir_url( __FILE__ ).'assets/js/main.js',array('jquery'),WP_MIGRATION_DUPLICATOR_VERSION);
		/* enque media library */
		wp_enqueue_media();
		$params=array(
			'nonces' => array(
	            'main'=>wp_create_nonce($this->module_id),
	        ),
	        'ajax_url' => admin_url('admin-ajax.php'),
	        'labels'=>array(
	        	'error'=>__('Error','wp-migration-duplicator'),
	        	'success'=>__('Success','wp-migration-duplicator'),
	        	'finished'=>__('Finished','wp-migration-duplicator'),
	        	'sure'=>__("You can't undo this action. Are you sure?",'wp-migration-duplicator'),
	        	'saving'=>__("Saving",'wp-migration-duplicator'),
	        	'connecting'=>__("Connecting....",'wp-migration-duplicator'),
	        	'backupfilenotempty'=>__("Please upload a backup file.",'wp-migration-duplicator'),
	        	'onlyzipfile'=>__("Please upload a zip file.",'wp-migration-duplicator'),
	        )
		);
		wp_localize_script($this->module_id,$this->module_id,$params);

		$view_file=plugin_dir_path( __FILE__ ).'views/importer.php';
		$params=array();
		Wp_Migration_Duplicator_Admin::envelope_settings_tabcontent('wt-mgdp-import',$view_file,'',$params,0);
	}
}
new Wp_Migration_Duplicator_Import();