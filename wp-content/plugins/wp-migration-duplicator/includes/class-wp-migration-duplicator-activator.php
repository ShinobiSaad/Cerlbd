<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wp_Migration_Duplicator
 * @subpackage Wp_Migration_Duplicator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Migration_Duplicator
 * @subpackage Wp_Migration_Duplicator/includes
 * @author     WebToffee <support@webtoffee.com>
 */
class Wp_Migration_Duplicator_Activator {

	/**
	 * Activation hook
	 *
	 * @since   1.0.0
	 * @since 	1.1.2 added new table for export/backup log
	 */
	public static function activate()
	{
		global $wpdb;
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );       
        if(is_multisite()) 
        {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach($blog_ids as $blog_id ) 
            {
                switch_to_blog( $blog_id );
                self::install_tables();
                restore_current_blog();
            }
        }
        else 
        {
            self::install_tables();
        }
	}

	/**
	 *  create table for export/backup log
	 * 	@since 	1.1.2 
	 */
	public static function install_tables()
	{
		global $wpdb;
		//install necessary tables
		//creating table for saving log data================
        $search_query = "SHOW TABLES LIKE %s";
        $charset_collate = $wpdb->get_charset_collate();
        $tb='wtmgdp_log';
        $like = '%' . $wpdb->prefix.$tb.'%';
        $table_name = $wpdb->prefix.$tb;
        if(!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) 
        {
            $sql_settings = "CREATE TABLE IF NOT EXISTS `$table_name` (
			  `id_wtmgdp_log` int(11) NOT NULL AUTO_INCREMENT,
			  `log_name` varchar(200) NOT NULL,
			  `log_data` text NOT NULL,
			  `status` int(11) NOT NULL DEFAULT '0',
			  `log_type` varchar(200) NOT NULL,
			  `created_at` int(11) NOT NULL DEFAULT '0',
			  `updated_at` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY(`id_wtmgdp_log`)
			) DEFAULT CHARSET=utf8;";
            dbDelta($sql_settings);
        }
        //creating table for saving log data================
	}
}
