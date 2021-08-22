<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wp_Migration_Duplicator
 * @subpackage Wp_Migration_Duplicator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Migration_Duplicator
 * @subpackage Wp_Migration_Duplicator/includes
 * @author     WebToffee <support@webtoffee.com>
 */
class Wp_Migration_Duplicator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Migration_Duplicator_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */

	public static $log_tb='wtmgdp_log';
	private static $log_tb_data_type=array(
		'id_wtmgdp_log'=>'%d',
		'log_name'=>'%s',
		'log_data'=>'%s',
		'status'=>'%d',
		'log_type'=>'%s',
		'created_at'=>'%d',
		'updated_at'=>'%d',
	);

	public static $status_incomplete=2;
	public static $status_failed=0;
	public static $status_complete=1;
	public static $status_stopped=3;


	public static $backup_dir=WP_CONTENT_DIR.'/webtoffee_migrations';
	public static $backup_dir_name='/webtoffee_migrations';

	public function __construct()
	{
		if ( defined( 'WP_MIGRATION_DUPLICATOR_VERSION' ) ) {
			$this->version = WP_MIGRATION_DUPLICATOR_VERSION;
		} else {
			$this->version = '1.1.5';
		}
		$this->plugin_name = 'wp-migration-duplicator';

		/* Status label  */


		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Migration_Duplicator_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Migration_Duplicator_i18n. Defines internationalization functionality.
	 * - Wp_Migration_Duplicator_Admin. Defines all hooks for the admin area.
	 * - Wp_Migration_Duplicator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-migration-duplicator-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-migration-duplicator-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-migration-duplicator-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-migration-duplicator-public.php';

		$this->loader = new Wp_Migration_Duplicator_Loader();
		$this->plugin_admin=new Wp_Migration_Duplicator_Admin( $this->get_plugin_name(), $this->get_version() );

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Migration_Duplicator_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_Migration_Duplicator_i18n();

		$this->loader->add_action('plugins_loaded',$plugin_i18n,'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->loader->add_action('admin_menu',$this->plugin_admin,'admin_menu',11); /* Adding admin menu */

		$this->loader->add_action( 'admin_enqueue_scripts',$this->plugin_admin,'enqueue_styles');
		$this->loader->add_action( 'admin_enqueue_scripts',$this->plugin_admin,'enqueue_scripts');
		$this->plugin_admin->admin_modules();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Migration_Duplicator_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	* @since 1.1.2
	* get module id
	*/
	public static function get_module_id($module_base)
	{
		return WT_MGDP_POST_TYPE.'_'.$module_base;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp_Migration_Duplicator_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	* 	@since 1.1.2
	*  	Get logs count
	*/
	public static function get_log_total()
	{
		global $wpdb;
		$tb=$wpdb->prefix.self::$log_tb;
		$out=0;
		$total=$wpdb->get_row("SELECT COUNT(id_wtmgdp_log) AS ttnum FROM $tb",ARRAY_A);
		if(!empty($total) && isset($total['ttnum']))
		{
			$out=$total['ttnum'];
		}
		return $out;
	}


	/**
	* 	@since 1.1.2
	*  	Get a logs
	*/
	public static function get_logs($offset,$limit)
	{
		global $wpdb;
		$tb=$wpdb->prefix.self::$log_tb;
		$logs=$wpdb->get_results($wpdb->prepare("SELECT * FROM $tb ORDER BY created_at DESC LIMIT %d,%d",$offset,$limit),ARRAY_A);
		if(empty($logs))
		{
			$logs=array();
		}
		return $logs;
	}

	/**
	* 	@since 1.1.2
	*  	Get a log by ID
	*/
	public static function get_log_by_id($id)
	{
		global $wpdb;
		$tb=$wpdb->prefix.self::$log_tb;
		return $wpdb->get_row($wpdb->prepare("SELECT * FROM $tb WHERE id_wtmgdp_log=%d",$id),ARRAY_A);
	}

	/**
	* 	@since 1.1.2
	*  	Delete a log entry
	*/
	public static function delete_log($where_arr)
	{
		global $wpdb;
		$tb=$wpdb->prefix.self::$log_tb;
		$delete_where=array();
		$delete_where_type=array();
		foreach($where_arr as $datak=>$datav)
		{
			if(isset(self::$log_tb_data_type[$datak]))
			{
				$delete_where[$datak]=$datav;
				$delete_where_type[]=self::$log_tb_data_type[$datak];
			}
		}
		return $wpdb->delete($tb,$delete_where,$delete_where_type);
	}

	/**
	* 	@since 1.1.2
	*  	Update a log entry
	*/
	public static function update_log($data_arr,$where_arr)
	{
		global $wpdb;
		$tb=$wpdb->prefix.self::$log_tb;
		$update_data=array();
		$update_data_type=array();
		foreach($data_arr as $datak=>$datav)
		{
			if(isset(self::$log_tb_data_type[$datak]))
			{
				$update_data[$datak]=$datav;
				$update_data_type[]=self::$log_tb_data_type[$datak];
			}
		}

		$update_where=array();
		$update_where_type=array();
		foreach($where_arr as $datak=>$datav)
		{
			if(isset(self::$log_tb_data_type[$datak]))
			{
				$update_where[$datak]=$datav;
				$update_where_type[]=self::$log_tb_data_type[$datak];
			}
		}
		$wpdb->update($tb,$update_data,$update_where,$update_data_type,$update_where_type);
		return true;
	}

	/**
	* 	@since 1.1.2
	*  	Create a log entry
	*/
	public static function create_log($data_arr)
	{
		global $wpdb;
		$tb=$wpdb->prefix.self::$log_tb;
		$insert_data=array();
		$insert_data_type=array();
		foreach($data_arr as $datak=>$datav)
		{
			if(isset(self::$log_tb_data_type[$datak]))
			{
				$insert_data[$datak]=$datav;
				$insert_data_type[]=self::$log_tb_data_type[$datak];
			}
		}
		$wpdb->insert($tb,$insert_data,$insert_data_type);
		return $wpdb->insert_id;
	}

	/**
	* 	@since 1.1.2
	*  	Get status label
	*/
	public static function get_status_label($status)
	{
		$status_label=array(
			'Failed','Completed','Incomplete','Stopped'
		);
		$label=(isset($status_label[$status]) ? $status_label[$status] : 'Unknown');
		return __($label,'wp-migration-duplicator');
	}

	/**
	* 	@since 1.1.2
	*  	Format size units
	*/
	public static function format_size_units($bytes)
	{
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		} elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		} elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		} elseif ($bytes > 1) {
			$bytes = $bytes . ' bytes';
		} elseif ($bytes == 1) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}
		return $bytes;
	}

}
