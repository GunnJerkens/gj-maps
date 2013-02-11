<?php

/*
 * View POIs menu
 * Edit indvidual POI menu
 * Upload CSVs menu
 */


function gj_admin() {  
    include('admin/gj_import_admin.php');  
}  
function gj_admin_delete() {
   include ('admin/gj_delete.php');
}
function gj_admin_actions() {
   add_options_page("GJ Maps", "GJ Maps", 'administrator', "gj_maps_upload", "gj_admin");
   add_options_page("GJ Maps Delete", "GJ Maps Delete", 'administrator', "gj_maps_delete", "gj_admin_delete");
}
add_action('admin_menu', 'gj_admin_actions');