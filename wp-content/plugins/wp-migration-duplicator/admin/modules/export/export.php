<?php
/**
 * Export section of the plugin
 *
 * @link       
 * @since 1.1.2     
 *
 * @package  Wp_Migration_Duplicator  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wp_Migration_Duplicator_Export
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='export';
	public $export_id=0;
	public $step_list=array(
		'start_export',
		'export_db',
		'export_files',
	);
	public $ajax_action_list=array(
		'start_export',
		'stop_export',
		'export_db',
		'export_files',
	);

	public function __construct()
	{
		$this->module_id=Wp_Migration_Duplicator::get_module_id($this->module_base);
		add_action('wp_ajax_wt_mgdp_export',array($this,'ajax_main'),1);

		add_filter('wt_mgdp_plugin_settings_tabhead',array($this,'settings_tabhead'));
		add_action('wt_mgdp_plugin_out_settings_form',array($this,'out_settings_form'));
		add_action('wt_mgdp_backups_head',array($this,'backup_page_export_btn'),10,2);
	}

	/**
	* 	@since 1.1.2
	*	Showing an export button on top of the backup list table
	*/
	public function backup_page_export_btn($backup_list,$offset)
	{
		?>
		<button class="button button-primary wt_mgdp_create_backup" style="float:right;"><?php _e('Goto Export','wp-migration-duplicator'); ?></button>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery(document).on('click','.wt_mgdp_create_backup',function(){
					window.location.hash="#wt-mgdp-export"; /* switching tab */
				});
			});
		</script>
		<?php
	}

	/**
	* 	@since 1.1.2 	Main ajax hook to handle all ajax requests
	*	@since 1.1.5 	User role checking enabled
	*/
	public function ajax_main()
	{
		$action=sanitize_text_field($_POST['sub_action']);
		$out=array(
			'status'=>false,
			'msg'=>__('Error','wp-migration-duplicator'),
			'step_finished'=>0,
			'finished'=>0,
			'step'=>$action,
			'sub_percent'=>0,
			'percent'=>0,
			'percent_label'=>'',
			'sub_percent_label'=>'',
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

		if(in_array($action, $this->ajax_action_list) && method_exists($this,$action))
		{
			$this->export_id=(isset($_POST['export_id']) ? intval($_POST['export_id']) : 0);
			$out=$this->{$action}($out);
		}else
		{
			//error
		}
		$current_step_index=(int) array_search($action,$this->step_list);
		$single_step_percent=(100/count($this->step_list));
		$main_percent=$single_step_percent*$current_step_index;
		$out['percent']=round($main_percent+(($out['sub_percent']/100)*$single_step_percent));
		$out['export_id']=$this->export_id;

		if($out['step_finished']==1) //step finished move to next step
		{
			$step_array_key=array_search($action,$this->step_list);
			if(isset($this->step_list[$step_array_key+1])) //next step exists
			{
				$out['step']=$this->step_list[$step_array_key+1];
				$out['offset']=0;
				if($out['step']=='export_files')
				{
					$out['limit']=1;
				}else
				{
					$out['limit']=12;
				}
			}else
			{
				$out['finished']=1;
				$out['percent']=100;
				$out['sub_percent']=100;
				$out['percent_label']='<span style="color:green;">'.__('Export completed','wp-migration-duplicator').'</span>';
				//$out['sub_percent_label']=__('Finished','wp-migration-duplicator');
			}
		}
		echo json_encode($out);
		exit();
	}

	/**
	* 	@since 1.1.2
	*	Stop the current export
	*/
	private function stop_export($out)
	{
		//update log status
        $to_db_arr=array('status'=>Wp_Migration_Duplicator::$status_stopped);
        $to_db_where_arr=array('id_wtmgdp_log'=>$this->export_id);        
        Wp_Migration_Duplicator::update_log($to_db_arr,$to_db_where_arr);
        $out['status']=true;
        return $out;
	}

	/**
	* @since 1.1.4
	* Get list of extensions to be excluded while exporting. via filter
	* 
	*/
	private function get_exclude_extensions()
	{
		$to_exclude_extensions=array();
        $to_exclude_extensions=apply_filters('wt_mgdp_exclude_extensions', $to_exclude_extensions);
        $to_exclude_extensions=(!is_array($to_exclude_extensions) ? array() : $to_exclude_extensions); 
        return $to_exclude_extensions;
	}

	/**
	* @since 1.1.2
	* Get list of items(files and folders) to be excluded while exporting. via filter
	* 
	*/
	private function get_exclude_items()
	{
		/* filter to exclude items via filter only items that are directly under `wp-content` */
        $to_exclude_items=array('ai1wm-backups', 'updraft', 'uploads/backup-guard');
        $to_exclude_items=apply_filters('wt_mgdp_exclude_files', $to_exclude_items);
        $to_exclude_items=(!is_array($to_exclude_items) ? array() : $to_exclude_items);      
        $must_exclude_items=array('.','..','webtoffee_migrations');
        $to_exclude_items=array_unique(array_merge($to_exclude_items,$must_exclude_items));

        return $to_exclude_items;
	}

	/**
	* @since 1.1.2
	* Start export, checks files and DB count and also exports DB via `export_db` method, Ajax sub function
	* @since 1.1.4 file extension exclusion checking added
	*/
	private function start_export($out)
	{
		global $wpdb;
		$mysqli =$this->get_mysqli();
		$tables_arr=array();
		$queryTables = $mysqli->query('SHOW TABLES');
        while($row=$queryTables->fetch_row())
        {
        	$tables_arr[]=$row[0];
        }
        $to_exclude_items=$this->get_exclude_items();

        /**
		* @since 1.1.4  take all file extensions to exclude
		*/
        $to_exclude_extensions=$this->get_exclude_extensions();

        $file_arr=array();
        $dir_arr=array();
        //directories & files in wp-conetnt
        $files=scandir(WP_CONTENT_DIR);
        foreach($files as $file)
        {
        	if(!in_array($file,$to_exclude_items))
        	{
        		$full_path=WP_CONTENT_DIR.'/'.$file;
        		if(is_dir($full_path))
        		{
					$dir_arr[]=$file;
        		}else
        		{
        			/**
					* @since 1.1.4  check file is need exclude by its extension
					*/
        			if(!$this->is_need_to_exclude_extension($to_exclude_extensions, $file))
        			{
        				$file_arr[]=$file;
        			}       			
        		}
        	}
        }

        if(!is_dir(Wp_Migration_Duplicator::$backup_dir))
        {
            if(!mkdir(Wp_Migration_Duplicator::$backup_dir,0755))
            {
            	$out['status']=false;
            	$out['msg']=__('Unable to create backup directory. Please check write permission for `wp-content` folder.','wp-migration-duplicator');
                return $out;
            }else
            {
            	//add an index file to block directory listing
            	$fh=fopen(Wp_Migration_Duplicator::$backup_dir.'/index.php', "w");
		    	if(is_resource($fh))
		    	{
			        fwrite($fh,'<?php // Silence is golden');
			    }
			    fclose($fh);
            }
        }

        $find=(isset($_POST['find']) && is_array($_POST['find']) ? $_POST['find'] : array());
        $replace=(isset($_POST['replace']) && is_array($_POST['replace']) ? $_POST['replace'] : array());

        /* ---------  */
		$find[] = $wpdb->prefix . 'capabilities';
        $replace[] = 'webtoffee_capabilities';

        $find[] = $wpdb->prefix . 'user_level';
        $replace[] = 'webtoffee_user_level';

        $find[] = $wpdb->prefix . 'user-settings';
        $replace[] = 'webtoffee_user-settings';

        $find[] = $wpdb->prefix . 'user-settings-time';
        $replace[] = 'webtoffee_user-settings-time';

        $find[] = $wpdb->prefix . 'dashboard_quick_press_last_post_id';
        $replace[] = 'webtoffee_dashboard_quick_press_last_post_id';

        $find[] = $wpdb->prefix . 'user_roles';
        $replace[] = 'webtoffee_user_roles';
        /* ---------  */

        $find=Wp_Migration_Duplicator_Admin::sanitize_array($find);
		$replace=Wp_Migration_Duplicator_Admin::sanitize_array($replace);

		$tme=time();
        $log_data=array('tables'=>$tables_arr,'files'=>$file_arr,'dirs'=>$dir_arr,'find'=>$find,'replace'=>$replace,'backup_file'=>date('Y-m-d-h-i-sa',$tme).'.zip');
        
        $data_arr=array(
			'log_name'=>date('Y-m-d h:i:s A'),
			'log_data'=>json_encode($log_data),
			'status'=>Wp_Migration_Duplicator::$status_incomplete,
			'log_type'=>'export',
			'created_at'=>$tme,
			'updated_at'=>$tme,			
        );
		$this->export_id=Wp_Migration_Duplicator::create_log($data_arr);
		return $this->export_db($out);
	}

	private function is_need_to_exclude_extension($to_exclude_extensions,$file_path)
	{
		$file_arr=explode('.',$file_path);
		$ext=end($file_arr);
		return in_array($ext, $to_exclude_extensions);
	}

	/**
	* @since 1.1.2
	* Checking the {file/its parent folder} is in the exclude list
	* @param array $to_exclude_items list of items to exclude
	* @param string $sub_real_path current file path
	*/
	private function is_need_to_exclude($to_exclude_items,$sub_real_path)
	{
		$is_need_to_exclude=0;
		/* checking files in exclude list */
		$str_reminder1=str_replace($to_exclude_items, '', $sub_real_path);
		if($str_reminder1!=$sub_real_path) /* found then need to verfify its on front of the string */
		{
			foreach($to_exclude_items as $excl)
			{
				if(strpos($sub_real_path,$excl)===0)
				{
					$is_need_to_exclude=1; //skipping the file
				}
			}
		}
		return $is_need_to_exclude;
	}

	/**
	* @since 1.1.2
	* Export files recrusively, Ajax sub function
	* @param array $out array of output
	*/
	public function export_files($out)
	{
		$export_log=$this->get_check_export_log();
		if(!$export_log)
		{
			return $out; //error
		}

		$offset=intval($_POST['offset']);
		$limit=intval($_POST['limit']);
		$log_data=json_decode($export_log['log_data'],true);

		$zip = new ZipArchive();
        if($offset==0) //offset is zero then create or overwrite the file(if exists)
        {
        	$backup_file_name=$log_data['backup_file'];
        	$backup_file=Wp_Migration_Duplicator::$backup_dir.'/'.$backup_file_name;
            $zip->open($backup_file,ZipArchive::CREATE | ZipArchive::OVERWRITE);
        }else
        {
        	$backup_file_name=$log_data['backup_file'];
        	$backup_file=Wp_Migration_Duplicator::$backup_dir.'/'.$backup_file_name;
            $zip->open($backup_file);
        }

        $to_exclude_items=$this->get_exclude_items();

        /**
		* @since 1.1.4  take all file extensions to exclude
		*/
        $to_exclude_extensions=$this->get_exclude_extensions();
        
        /* we take all files as one entry */
        $total_files=count($log_data['files']);
        $total_dirs=count($log_data['dirs']);
        $total_items=($total_files>0 ? 1 : 0)+count($log_data['dirs']);
        if($offset==0 && $total_files>0) //first export files (if exists)
        {
        	foreach($log_data['files'] as $file) 
        	{
        		$full_path=WP_CONTENT_DIR.'/'.$file;
        		if(!in_array($file,$to_exclude_items))
        		{
        			$zip->addFile($full_path,$file);
        		}      		
        	}
        	$out['sub_percent_label']=__($total_files." file(s) exported.",'wp-migration-duplicator');
        }else //export directories
        {
        	if(count($log_data['dirs'])>0)
        	{
        		//if file exists then real offset will be leasser than one
        		$real_offset=($total_files>0 ? $offset-1 : $offset);
        		if(isset($log_data['dirs'][$real_offset]))
        		{
        			$dir_to_add_rel_path=$log_data['dirs'][$real_offset];
	        		$dir_to_add=WP_CONTENT_DIR.'/'.$dir_to_add_rel_path;	        		
	        		$total_exported_directories=$real_offset+1;
	        		if(is_dir($dir_to_add)) //check directory exists to avoid issues
	        		{
	        			// Create recursive directory iterator
		        		$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir_to_add),RecursiveIteratorIterator::LEAVES_ONLY);
		        		foreach($files as $name=>$file)
		        		{
		        			// Skip directories (they would be added automatically if not empty)
		        			if(!$file->isDir())
		        			{
		        				$filePath=$file->getRealPath();
		        				$relativePath=substr($filePath,strlen($dir_to_add)+1);
		        				$sub_real_path=$dir_to_add_rel_path.'/'.$relativePath;
		        				
		        				/* checking files in exclude list */
		        				$is_need_to_exclude=$this->is_need_to_exclude($to_exclude_items,$sub_real_path);

		        				if($is_need_to_exclude==0)
		        				{
		        					/**
									* @since 1.1.4  check file is need exclude by its extension
									*/
				        			if(!$this->is_need_to_exclude_extension($to_exclude_extensions, $sub_real_path))
				        			{
		        						$zip->addFile($filePath, basename($dir_to_add).'/'.$relativePath);
		        					}
		        				}	        				
		        			}
		        		}
		        		$out['sub_percent_label']=__($total_exported_directories." out of ".$total_dirs." directories exported.",'wp-migration-duplicator');
	        		}        		
	        	}else
	        	{
	        		$out['status']=true;   	
		        	$out['step_finished']=1; 
		        	return $out;
	        	}
        	}else
        	{
        		$out['status']=true;   	
	        	$out['step_finished']=1;
	        	return $out;
        	}
        }
        $zip->close();

        $new_offset=$offset+$limit;
        if($total_items<=$new_offset)
        {
        	//delete the database.sql file.
        	$upload = wp_upload_dir();
        	$upload_dir = $upload['basedir'];
        	$db_filename = $upload_dir."/" . 'database.sql';
        	if(file_exists($db_filename))
        	{
        		@unlink($db_filename);
        	}

        	//update log status
	        $to_db_arr=array('status'=>Wp_Migration_Duplicator::$status_complete);
	        $to_db_where_arr=array('id_wtmgdp_log'=>$this->export_id);        
	        Wp_Migration_Duplicator::update_log($to_db_arr,$to_db_where_arr);

        	$out['step_finished']=1;
        	$out['backup_file']=content_url().Wp_Migration_Duplicator::$backup_dir_name."/".$backup_file_name; 
        	
        	$out['sub_percent_label']=__(sprintf("%d files and %d directories exported",$total_files,$total_dirs),'wp-migration-duplicator');
		    $out['percent_label']=__(sprintf("%d files, %d directories, %d database tables exported",$total_files,$total_dirs,count($log_data['tables'])),'wp-migration-duplicator');
        }else
        {
        	$out['percent_label']=__("Exporting files and directories",'wp-migration-duplicator');
        }
        $out['status']=true;
        $out['step']='export_files';
        $out['offset']=$new_offset;
        $out['limit']=$limit;
        $total_steps=ceil($total_items/$limit);
        $out['sub_percent']=round((100/$total_steps)*(($offset/$limit)+1));      
        return $out;
	}

	/**
	* @since 1.1.2
	* Get export DB entry
	* @param array $out array of output
	* @return array $export_log export details
	*/
	private function get_check_export_log()
	{
		if($this->export_id==0)
		{ 
			return false; //error
		}	
		$export_log=Wp_Migration_Duplicator::get_log_by_id($this->export_id);
		if(empty($export_log))
		{
			return false; //no record found
		}
		return $export_log;
	}

	/**
	* @since 1.1.2
	* Get mysqli connection object
	* @return object $mysqli mysqli object
	*/
	private function get_mysqli()
	{
		$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
        $mysqli->select_db($name);
        $mysqli->query("SET NAMES 'utf8'");
        return $mysqli;
	}

	/**
	* @since 1.1.2
	* Export database,  Ajax sub function
	* @param array $out array format for output
	*/
	public function export_db($out)
	{		
		global $wpdb;		
		$export_log=$this->get_check_export_log();
		if(!$export_log)
		{
			return $out; //error
		}

		$offset=intval($_POST['offset']);
		$limit=intval($_POST['limit']);
		$log_data=json_decode($export_log['log_data'],true);
		$find=$log_data['find']; //taking find and replace from db
		$replace=$log_data['replace'];

		ini_set('max_execution_time', 300);
        ini_set('memory_limit', '-1');
		$upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $download_path = $upload_dir .'/.';
        $mysqli =$this->get_mysqli();
        $target_tables=$log_data['tables']; //taking table list from db
       
        $total_tables=count($target_tables);       
        $out['total_tables']=$total_tables;
        if($total_tables<=$offset)
        {  
        	$out['status']=true;   	
        	$out['step_finished']=1;
        	return $out;
        }
        $list_arr=array_chunk($target_tables,$limit);
        $content='';
        $total_exported_tables=$offset;
        $current_offset_pos=($offset/$limit);
        foreach ($list_arr as $offset_pos=>$tb_arr)
        {
        	if($offset_pos==$current_offset_pos)
        	{
        		foreach($tb_arr as $table)
        		{
        			$total_exported_tables++;
        			$result = $mysqli->query('SELECT * FROM ' . $table);
		            $fields_amount = $result->field_count;
		            $rows_num = $mysqli->affected_rows;
		            $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
		            $TableMLine = $res->fetch_row();

		            $table = str_replace($wpdb->prefix, 'webtoffee_', $table);
		            $TableMLine[1] = str_replace($wpdb->prefix, 'webtoffee_', $TableMLine[1]);
		            $content =$content. "\n\n" . "DROP TABLE IF EXISTS `$table` ;/*END*/ " . "\n\n" . "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";/*END*/\r\nSET time_zone = \"+00:00\";/*END*/\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;/*END*/\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;/*END*/\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;/*END*/\r\n/*!40101 SET NAMES utf8 */;/*END*/\r\n--\r\n-- Database: `".DB_NAME."`\r\n--\r\n\r\n\r\n" . "\n\n" . $TableMLine[1] . ";/*END*/\n\n";


		            for($i=0, $st_counter= 0; $i<$fields_amount; $i++, $st_counter = 0)
		            {
		                while ($row = $result->fetch_row())
		                { 	
		                	//when started (and every after 100 command cycle):
		                    if($st_counter % 100 == 0 || $st_counter == 0)
		                    {
		                        $content .= "\nINSERT INTO " . $table . " VALUES";
		                    }
		                    $content .= "\n(";
		                    for($j=0; $j<$fields_amount; $j++)
		                    {
		                        if(isset($row[$j]))
		                        {
		                            $row[$j]=$this->webtoffee_serialize($find, $replace, $row[$j]);
		                            $content.='"'.addslashes($row[$j]) . '"';
		                        }
		                        else
		                        {
		                            $content.='""';
		                        }
		                        if($j<($fields_amount-1))
		                        {
		                            $content .= ',';
		                        }
		                    }
		                    $content .= ")";
		                    if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
		                        $content .= ";/*END*/";
		                    } else {
		                        $content .= ",";
		                    }
		                    $st_counter = $st_counter + 1;
		                }
		            }
		            $content .= "\n\n\n";
        		}
        		break;
        	}
        }

        $file_name="database.sql";
        $fwrite_mode=($offset==0 ? "w" : "a");
        $fp=fopen($download_path.'/'.$file_name,$fwrite_mode);
        fwrite($fp,$content);
        fclose($fp);
        $new_offset=$offset+$limit;
        if($total_tables<=$new_offset)
        {
        	$out['step_finished']=1;       	
        }
        $out['status']=true;
        $out['step']='export_db';
        $out['offset']=$new_offset;
        $out['limit']=$limit;
        $total_steps=ceil($total_tables/$limit);
        $out['sub_percent']=round((100/$total_steps)*(($offset/$limit)+1));
        $out['sub_percent_label']=__($total_exported_tables." out of ".$total_tables." tables exported.",'wp-migration-duplicator');
        $out['percent_label']=__("Exporting database",'wp-migration-duplicator');
        return $out;
	}

	/**
	* @since 1.0.0
	* Serailize and unserailze db data for find and replace
	* 
	*/
	//TODO Helper Fucntions to be moved using namespacing
    public function webtoffee_serialize($search = '', $replace = '', $data = '', $serialised = FALSE)
    {
        if (is_string($data) && ($unserialized = @unserialize($data)) !== FALSE) {
            $data = $this->webtoffee_serialize($search, $replace, $unserialized, TRUE);
        } elseif (is_array($data)) {
            $_tmp = [];
            foreach ($data as $key => $value) {
                $_tmp[$key] = $this->webtoffee_serialize($search, $replace, $value, FALSE);
            }

            $data = $_tmp;
            unset($_tmp);
        } elseif (is_object($data)) {
            $_tmp = $data; // new instance
            $props = get_object_vars($data);
            foreach ($props as $key => $value) {
                $_tmp->$key = $this->webtoffee_serialize($search, $replace, $value, FALSE);
            }
            $data = $_tmp;
            unset($_tmp);
        } else {
            if (is_string($data)) {
                $data = str_replace($search, $replace, $data);
            }
        }
        if ($serialised) {
            return maybe_serialize($data);
        }

        return $data;
    }

	/**
	 *  @since 1.1.2
	 * 	Export tab head filter callback
	 **/
	public function settings_tabhead($arr)
	{
		return array_merge(array('wt-mgdp-export'=>__('Export','wp-migration-duplicator')),$arr);
	}

	/**
	 *  @since 1.1.2
	 * 	Export page tab content filter callback
	**/
	public function out_settings_form($arr)
	{
		wp_enqueue_script($this->module_id,plugin_dir_url( __FILE__ ).'assets/js/main.js',array('jquery'),WP_MIGRATION_DUPLICATOR_VERSION);
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
	        	'connecting'=>__("Connecting",'wp-migration-duplicator'),
	        	'stopped'=>__("Stopped",'wp-migration-duplicator'),
	        	'stopping'=>__("Stopping",'wp-migration-duplicator'),
	        	'failedtostop'=>__("Failed to stop export",'wp-migration-duplicator'),
	        	'startnewexport'=>__("Start new export",'wp-migration-duplicator'),
	        )
		);
		wp_localize_script($this->module_id,$this->module_id,$params);

		$view_file=plugin_dir_path( __FILE__ ).'views/exporter.php';
		$params=array();
		Wp_Migration_Duplicator_Admin::envelope_settings_tabcontent('wt-mgdp-export',$view_file,'',$params,0);
	}
}
new Wp_Migration_Duplicator_Export();