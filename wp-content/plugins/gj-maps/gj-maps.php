<?php
   /*
   Plugin Name: GJ Maps
   Plugin URI: http://www.gunnjerkens.com
   Description: Ability top upload Points of Interest from a CSV or manually, then access them in JSON format.
   Version: 0.1
   Author: Andrew Kostka & Kevin Crawford
   License: GPL2
   */

/*
require 'admin.php';
require 'upload.php';
require 'api.php';
*/

//ADMIN

function gj_admin() {  
    include('gj_import_admin.php');  
}  
function gj_admin_delete() {
   include ('gj_delete.php');
}
function gj_admin_actions() {
   add_options_page("GJ Maps", "GJ Maps", 'administrator', "gj_maps_display_main", "gj_admin");
   add_options_page("GJ Maps Delete", "GJ Maps Delete", 'administrator', "gj_maps_delete", "gj_admin_delete");
}
add_action('admin_menu', 'gj_admin_actions');

function gj_table_install () {

   global $wpdb;

   $table_name = $wpdb->prefix . "gj_poi";

   global $wpdb;
   $sql = "CREATE TABLE $table_name (
     id mediumint(9) NOT NULL AUTO_INCREMENT,
     cat tinytext NOT NULL,
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
     UNIQUE KEY id (id)
   );";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);

}

register_activation_hook(__FILE__,'gj_table_install');