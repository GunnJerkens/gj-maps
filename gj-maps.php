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


class gjMaps {

  private $loadJS;

  function __construct() {

    include('json_api.php');

    add_action('admin_menu', array($this, 'admin_actions'));
    add_action('init', array($this, 'register_scripts'));
    add_action('wp_footer', array($this, 'print_scripts'));
    add_action('admin_enqueue_scripts', array($this, 'mw_enqueue_color_picker'));

    add_action('wp_enqueue_scripts', array('gjMapsDB', 'get_poi'));
    add_action('wp_enqueue_scripts', array('gjMapsDB', 'get_cat'));

    

    register_activation_hook(__FILE__,  array($this, 'table_install'));

    $this->api = new gjMapsAPI();
  }

  function admin_actions() {
    add_menu_page('GJ Maps', 'GJ Maps', 'administrator', 'gj_maps', array($this, 'admin_edit'));
    add_submenu_page('gj_maps', 'Categories', 'Categories', 'administrator', 'gj_admin_categories', array($this, 'admin_categories'));
    add_submenu_page('gj_maps', 'Import CSV', 'Import CSV', 'administrator', 'gj_admin_import', array($this, 'admin_import'));
    add_submenu_page('gj_maps', 'Settings', 'Settings', 'administrator', 'gj_admin_options', array($this, 'admin_options'));
  }

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
    include('admin/gj_options.php');
  }

  function register_scripts() {

    wp_register_script('google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', null, null);
    wp_register_script('gj-maps', WP_PLUGIN_URL.'/gj-maps/js/main.js', array('jquery', 'google-maps'), null, true);

    if (get_option('gj_styles') && !(is_admin()) ) {

      wp_register_style('gj-maps-style', WP_PLUGIN_URL.'/gj-maps/style/screen.css', null, true);

    }

  }

  function print_scripts() {

    if ($this->loadJS) {

      wp_print_scripts('google-maps');
      wp_print_scripts('gj-maps');

      if (get_option('gj_styles') && !(is_admin()) ) {

        wp_print_styles('gj-maps-style');

      }

    }

  }

  function mw_enqueue_color_picker($hook_suffix) {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'color-init', plugins_url('js/color-init.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
  }

  function table_install() {

    global $wpdb;

    $gj_maps = $wpdb->prefix . "gjm_maps";
    $gj_cat  = $wpdb->prefix . "gjm_cat";
    $gj_poi  = $wpdb->prefix . "gjm_poi";

    //MAPS table
    $sql_maps = "CREATE TABLE $gj_maps (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name VARCHAR(55) NOT NULL,
      c_lat float(12,8),
      c_lng float(12,8),
      m_zoom longtext,
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

    if($wpdb->get_var("SHOW TABLES LIKE '$gj_maps'") != $gj_maps) { //If table did not exist
      $wpdb->insert($gj_maps, array('name'=>'Map 1'));
    }
    if($wpdb->get_var("SHOW TABLES LIKE '$gj_cat'") != $gj_cat) { //If table did not exist
      $wpdb->insert($gj_cat, array('name'=>'All', 'map_id'=>$wpdb->insert_id));
    } 

  }


}
new gjMaps();
