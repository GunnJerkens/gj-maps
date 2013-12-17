<?php
/*
 * Plugin Name: GJ Maps
 * Plugin URI: http://www.gunnjerkens.com
 * Description: Ability top upload Points of Interest from a CSV or manually, then access them in JSON format.
 * Version: 0.1
 * Author: Andrew Kostka & Kevin Crawford
 * License: GPL2
 */

include('poi.php');
include('category.php');


// Admin menus
function gj_admin_edit() {
	include('admin/gj_admin.php');
}
function gj_admin_categories() {
	include('admin/gj_category.php');
}
function gj_admin_import() {  
	include('admin/gj_import.php'); 
}
function gj_admin_options() {
	include ('admin/gj_options.php');
}
function gj_admin_actions() {
	add_menu_page( "GJ Maps", "GJ Maps", 'administrator', "gj_maps", "gj_admin_edit" );
	add_submenu_page("gj_maps", "Categories", "Categories", 'administrator', "gj_admin_categories", "gj_admin_categories");
	add_submenu_page("gj_maps", "Import CSV", "Import CSV", 'administrator', "gj_admin_import", "gj_admin_import");
	add_submenu_page("gj_maps", "Settings", "Settings", 'administrator', "gj_admin_options", "gj_admin_options");
}
add_action('admin_menu', 'gj_admin_actions');


// Add scripts && styles // todo -- grunt/sass w/ default stylesheet
function gj_add_styles () {
	if (get_option('gj_styles') && !(is_admin()) ) {
		wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', null, null);
		wp_enqueue_script('gj-maps', WP_PLUGIN_URL.'/gj-maps/assets/gj-maps.js', array('jquery', 'google-maps'), null, true);
		wp_enqueue_script('mscrollbar', WP_PLUGIN_URL.'/gj-maps/assets/jquery.mCustomScrollbar.min.js', array('jquery'), null, true);
		wp_enqueue_script('poi', WP_PLUGIN_URL.'/gj-maps/assets/poi.js', array('jquery'), null, true);
	}
}
add_action('get_header', 'gj_add_styles');

//Color picker
add_action( 'admin_enqueue_scripts', 'mw_enqueue_color_picker' );
function mw_enqueue_color_picker( $hook_suffix ) {
	// first check that $hook_suffix is appropriate for your admin page
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'color-init', plugins_url('assets/color-init.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

// Init database
function gj_table_install () {

	global $wpdb;

	$gj_cat = $wpdb->prefix . "gj_cat";
	$gj_poi = $wpdb->prefix . "gj_poi";

	//CAT table
	$sql_cat = "CREATE TABLE $gj_cat (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		   name VARCHAR(55) NOT NULL,
		   color VARCHAR(7) NOT NULL,
		   icon VARCHAR(255) NOT NULL,
		   PRIMARY KEY (id)
			   );";

	//POI table
	$sql_poi = "CREATE TABLE $gj_poi (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		   cat_id mediumint(9) NOT NULL,
		   name VARCHAR(255) NOT NULL,
		   address VARCHAR(255) NOT NULL,
		   city tinytext NOT NULL,
		   state tinytext NOT NULL,
		   zip tinytext,
		   country tinytext,
		   phone tinytext,
		   url VARCHAR(255) DEFAULT '' NOT NULL,
		   lat float(12,8) NOT NULL,
		   lng float(12,8) NOT NULL,
		   PRIMARY KEY (id),
		   FOREIGN KEY (cat_id) REFERENCES $gj_cat(id)
			   );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_cat . $sql_poi);

	$wpdb->insert($wpdb->prefix . 'gj_cat', array('name'=>'All', 'color'=>'#000000'));

}

register_activation_hook(__FILE__,'gj_table_install');

// Register [gjmaps] shortcode
function gjmaps_shortcode(){
	global $GJ_api;
	$gjmapsAPI = $GJ_api->gj_POI_frontend();
	$gjmapsMarkup = '
	  <ul class="poi-categories">
      <li class="poi-category" data-cat-id="">
        <label style="background-color: #766761;">View All</label>
      </li>
    </ul>
    <div id="map-canvas"></div>
	';
	return $gjmapsAPI.$gjmapsMarkup;
}
add_shortcode( 'gjmaps', 'gjmaps_shortcode' );
