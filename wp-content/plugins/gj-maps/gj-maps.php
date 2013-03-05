<?php
   /*
   Plugin Name: GJ Maps
   Plugin URI: http://www.gunnjerkens.com
   Description: Ability top upload Points of Interest from a CSV or manually, then access them in JSON format.
   Version: 0.1
   Author: Andrew Kostka & Kevin Crawford
   License: GPL2
   */

include('poi.php');
include('category.php');


//ADMIN MENUS
function gj_admin_edit() {
   include('admin/gj_admin.php');
}
function gj_admin_categories() {
   include('admin/gj_category.php');
}
function gj_admin_import() {  
    include('admin/gj_import.php'); 
}  
function gj_admin_delete() {
   include ('admin/gj_delete.php');
}
function gj_admin_options() {
   include ('admin/gj_options.php');
}
function gj_admin_actions() {
   add_menu_page( "GJ Maps", "GJ Maps", 'administrator', "gj_maps", "gj_admin_edit" );
   add_submenu_page("gj_maps", "Categories", "Categories", 'administrator', "gj_admin_categories", "gj_admin_categories");
   add_submenu_page("gj_maps", "Import CSV", "Import CSV", 'administrator', "gj_admin_import", "gj_admin_import");
   add_submenu_page("gj_maps", "GJ Maps Delete", "GJ Maps Delete", 'administrator', "gj_admin_delete", "gj_admin_delete");
   add_submenu_page("gj_maps", "Settings", "Settings", 'administrator', "gj_admin_options", "gj_admin_options");
}
add_action('admin_menu', 'gj_admin_actions');


//ADD GMAPS AND STYLES
function gj_add_styles () {
   if (get_option('gj_styles') && !(is_admin()) ) {
      wp_enqueue_script('google-maps', 'http://maps.googleapis.com/maps/api/js?sensor=false&#038;v=3&#038;language=en', null, null);
      wp_enqueue_script('gmaps', WP_PLUGIN_URL.'/gj-maps/assets/gmaps.js', array('jquery', 'google-maps'), null);
      wp_enqueue_script('gj-maps', WP_PLUGIN_URL.'/gj-maps/assets/gj-maps.js', array('jquery', 'gmaps'), null, true);
   }
}

//Color Picker
add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
function mw_enqueue_color_picker( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'color-init', plugins_url('assets/color-init.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

//INIT DB

function gj_table_install () {

   global $wpdb;

   $poi_table = $wpdb->prefix . "gj_poi";
   $cat_table = $wpdb->prefix . "gj_cat";

   global $wpdb;

  //CAT table
   $sql_cat = "CREATE TABLE $cat_table (
     id mediumint(9) NOT NULL AUTO_INCREMENT,
     name VARCHAR(55) NOT NULL,
     color VARCHAR(7) NOT NULL,
     icon VARCHAR(255) NOT NULL,
     PRIMARY KEY (id)
   );";

   //POI table
   $sql_poi = "CREATE TABLE $poi_table (
     id mediumint(9) NOT NULL AUTO_INCREMENT,
     cat_id mediumint(9) NOT NULL,
     name VARCHAR(55) NOT NULL,
     address VARCHAR(55) NOT NULL,
     city tinytext NOT NULL,
     state tinytext NOT NULL,
     zip tinytext DEFAULT '' NOT NULL,
     country tinytext DEFAULT '' NOT NULL,
     phone tinytext DEFAULT '' NOT NULL,
     url VARCHAR(55) DEFAULT '' NOT NULL,
     lat float NOT NULL,
     lng float NOT NULL,
     PRIMARY KEY (id),
     FOREIGN KEY (cat_id) REFERENCES $cat_table(id)
   );";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql_cat);
   dbDelta($sql_poi);

   $wpdb->insert($wpdb->prefix . 'gj_cat', array('name'=>'All', 'color'=>'#000000'));

}

register_activation_hook(__FILE__,'gj_table_install');


