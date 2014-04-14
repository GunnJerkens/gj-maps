<?php
/*
 * Plugin Name: GJ Maps
 * Plugin URI: https://github.com/GunnJerkens/gj-maps
 * Description: Ability top upload Points of Interest from a CSV or manually, then access them in JSON format.
 * Version: 0.2
 * Author: GunnJerkens
 * Author URI: http://gunnjerkens.com
 * License: GPL2
 */

class GJ_Maps {

  function __construct() {
    include('poi.php');
    include('category.php');
    include('json_api.php');

    add_action('admin_menu',            array($this, 'admin_actions'));
    add_action('init',                  array($this, 'register_scripts'));
    add_action('wp_footer',             array($this, 'print_scripts'));
    add_action('admin_enqueue_scripts', array($this, 'mw_enqueue_color_picker'));

    add_shortcode('gjmaps',             array($this, 'shortcode'));

    register_activation_hook(__FILE__,  array($this, 'table_install'));
  }

  // Admin menus
  function admin_edit() {
    include('admin/gj_admin.php');
  }
  function admin_categories() {
    include('admin/gj_category.php');
  }
  function admin_import() {  
    include('admin/gj_import.php'); 
  }
  function admin_options() {
    include ('admin/gj_options.php');
  }

  function admin_actions() {
    add_menu_page('GJ Maps', 'GJ Maps', 'administrator', 'gj_maps', array($this, 'admin_edit'));

    add_submenu_page('gj_maps', 'Categories', 'Categories', 'administrator', 'gj_admin_categories', array($this, 'admin_categories'));
    add_submenu_page('gj_maps', 'Import CSV', 'Import CSV', 'administrator', 'gj_admin_import',     array($this, 'admin_import'));
    add_submenu_page('gj_maps', 'Settings',   'Settings',   'administrator', 'gj_admin_options',    array($this, 'admin_options'));
  }

  // Add scripts && styles // todo -- grunt/sass w/ default stylesheet
  function register_scripts() {
    wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', null, null);
    if (get_option('gj_styles') && !(is_admin()) ) {
      wp_register_script('gj-maps', WP_PLUGIN_URL.'/gj-maps/js/main.js', array('jquery', 'google-maps'), null, true);
      wp_register_style('gj-maps-style', WP_PLUGIN_URL.'/gj-maps/style/screen.css', null, true);
    }
  }

  function print_scripts() {
    global $gj_load;

    if ( ! $gj_load )
      return;

    wp_print_scripts('google-maps');
    if (get_option('gj_styles') && !(is_admin()) ) {
      wp_print_scripts('gj-maps');
      wp_print_styles('gj-maps-style');
    }
  }

  //Color picker
  function mw_enqueue_color_picker( $hook_suffix ) {
    // first check that $hook_suffix is appropriate for your admin page
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'color-init', plugins_url('js/color-init.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
  }

  // Init database
  function table_install() {

    global $wpdb;

    $gj_maps = $wpdb->prefix . "gjm_maps";
    $gj_cat  = $wpdb->prefix . "gjm_cat";
    $gj_poi  = $wpdb->prefix . "gjm_poi";

    //MAPS table
    $sql_maps = "CREATE TABLE $gj_maps (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name VARCHAR(55) NOT NULL,
      PRIMARY KEY (id)
    );";

    //CAT table
    $sql_cat = "CREATE TABLE $gj_cat (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      map_id mediumint(9) NOT NULL,
      name VARCHAR(55) NOT NULL,
      color VARCHAR(7) NOT NULL DEFAULT '#000000',
      icon VARCHAR(255),
      hide_list VARCHAR(1),
      filter_resist VARCHAR(1),
      PRIMARY KEY (id),
      FOREIGN KEY (map_id) REFERENCES $gj_maps(id)
    );";

    //POI table
    $sql_poi = "CREATE TABLE $gj_poi (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      map_id mediumint(9) NOT NULL,
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
      FOREIGN KEY (cat_id) REFERENCES $gj_cat(id),
      FOREIGN KEY (map_id) REFERENCES $gj_maps(id)
     );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_maps . $sql_cat . $sql_poi);

    $wpdb->insert($gj_maps, array('name'=>'Map'));
    $wpdb->insert($gj_cat, array('name'=>'All', 'map_id'=>$wpdb->insert_id));

  }

  // Register [gjmaps] shortcode -- need to fix hard coded color and add as menu option!
  function shortcode($atts) {
    global $GJ_api;
    global $gj_load;
    $gj_load = true;
    $cat_default = get_option('gj_cat_default');
    if ($cat_default === "") { $cat_default = "#ffffff"; }

    $gjmapsAPI = $GJ_api->gj_POI_frontend();

    $gjWrapper = '<div class="gjmaps-wrapper">';
    $gjCanvas = '<div id="map-canvas" class="gjmaps-map-canvas"></div>';
    $gjCategories = '
      <ul class="gjmaps-categories">
      <li class="gjmaps-category active" data-cat-id="all">
      <div class="gjmaps-label" style="background-color: '.$cat_default.';" data-type="label"><span>View All</span></label>
      </li>
      </ul>
      ';
    $gjWrapperClose = '</div>';

    $top = $right = $left = $gjWrapper.$gjCategories.$gjCanvas.$gjWrapperClose;
    $bottom = $gjWrapper.$gjCanvas.$gjCategories.$gjWrapperClose;


    extract(shortcode_atts(array(
      "pos" => 'top'
    ), $atts));


    if ($pos === 'bottom' OR $pos === 'bot') {
      return $gjmapsAPI.$bottom;
    } else if ($pos === 'left') {
      return $gjmapsAPI.$left;
    } else if ($pos === 'right') {
      return $gjmapsAPI.$right;
    } else {
      return $gjmapsAPI.$top;
    }
  }

}

new GJ_Maps();
