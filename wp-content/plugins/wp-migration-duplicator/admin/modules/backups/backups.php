<?php
/**
 * Backups section of the plugin
 *
 * @link       
 * @since 1.1.2     
 *
 * @package  Wp_Migration_Duplicator  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wp_Migration_Duplicator_Backups
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='backups';
	public $ajax_action_list=array('delete');
	public $export_id=0;
	public function __construct()
	{
		$this->module_id=Wp_Migration_Duplicator::get_module_id($this->module_base);
		add_action('wp_ajax_wt_mgdp_backups',array($this,'ajax_main'),1);

		add_filter('wt_mgdp_plugin_settings_tabhead',array($this,'settings_tabhead'));
		add_action('wt_mgdp_plugin_out_settings_form',array($this,'out_settings_form'));
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
			'action'=>$action,
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
			$this->export_id=(isset($_POST['export_id']) ? intval($_POST['export_id']) : 0);
			$out=$this->{$action}($out);
		}else
		{
			//error
		}

		echo json_encode($out);
		exit();
	}

	/**
	* @since 1.1.2
	* Delete export/backup log and file
	*/
	public function delete($out)
	{
		if($this->export_id>0)
		{
			$export_log=Wp_Migration_Duplicator::get_log_by_id($this->export_id);
			if($export_log && $export_log['log_type']=='export')
			{
				$log_data=json_decode($export_log['log_data'],true);
				$where_arr=array('id_wtmgdp_log'=>$this->export_id,'log_type'=>'export');
				if(Wp_Migration_Duplicator::delete_log($where_arr))
				{
					$file_name=(isset($log_data['backup_file']) ? $log_data['backup_file'] : '');
					$file_path=Wp_Migration_Duplicator::$backup_dir.'/'.$file_name;
					if(file_exists($file_path) && $file_name!="") //must check file name is not empty
					{
						@unlink($file_path);
					}
					$out['status']=true;
				}
			}
		}
		return $out;
	}

	/**
	 *  @since 1.1.2
	 * 	Backups tab head filter callback
	 **/
	public function settings_tabhead($arr)
	{
		$out=array();
		$added=0;
		foreach($arr as $k=>$v)
		{
			$out[$k]=$v;
			if($k=='wt-mgdp-import') //add after export
			{
				$out['wt-mgdp-backups']=__('Backups','wp-migration-duplicator');
				$added=1;
			}
		}
		if($added==0) //no export menu, then add it as first item
		{
			$out=array_merge(array('wt-mgdp-backups'=>__('Backups','wp-migration-duplicator')),$arr);
		}
		return $out;
	}

	/**
	 *  @since 1.1.2
	 * 	Backups page tab content filter callback
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
	        	'sure'=>__("You can't undo this action. Are you sure?",'wp-migration-duplicator'),
	        	'saving'=>__("Saving",'wp-migration-duplicator'),
	        	'connecting'=>__("Connecting...",'wp-migration-duplicator'),
	        )
		);
		wp_localize_script($this->module_id,$this->module_id,$params);
		$view_file=plugin_dir_path( __FILE__ ).'views/backups.php';
		$offset=(isset($_GET['offset']) ? intval($_GET['offset']) : 0);
		$limit=20;
		$backup_list=Wp_Migration_Duplicator::get_logs($offset,$limit);
		$total_list=Wp_Migration_Duplicator::get_log_total();

		$params=array(
			'backup_list'=>$backup_list,
			'total_list'=>$total_list,
			'offset'=>$offset,
			'limit'=>$limit,
		);
		Wp_Migration_Duplicator_Admin::envelope_settings_tabcontent('wt-mgdp-backups',$view_file,'',$params,0);
	}
}
new Wp_Migration_Duplicator_Backups();