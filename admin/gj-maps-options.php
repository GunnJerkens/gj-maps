<?php
/*
* Options page for gj-maps.
*/

if ('gj-maps-options.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
  die();
}

$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'gj_maps_settings';

?>

<h2 class="nav-tab-wrapper">
  <a href="?page=gj_maps_options&tab=gj_maps_settings" class="nav-tab <?php echo $active_tab == 'gj_maps_settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
  <a href="?page=gj_maps_options&tab=gj_maps_import" class="nav-tab <?php echo $active_tab == 'gj_maps_import' ? 'nav-tab-active' : ''; ?>">Import</a>
  <a href="?page=gj_maps_options&tab=gj_maps_delete" class="nav-tab <?php echo $active_tab == 'gj_maps_delete' ? 'nav-tab-active' : ''; ?>">Delete</a>
</h2>

<div class="wrap"><?php

  if( $active_tab == 'gj_maps_settings' ) {
    if (file_exists(__DIR__. '/gj-maps-settings.php')) {
      include_once(__DIR__. '/gj-maps-settings.php');
    }
    else {
      echo 'Settings file is missing';  
    }
  }

  if( $active_tab == 'gj_maps_import' ) {
    if (file_exists(__DIR__. '/gj-maps-import.php')) {
      include_once(__DIR__. '/gj-maps-import.php');
    }
    else {
      echo 'Import file is missing';  
    }
  } 

  if( $active_tab == 'gj_maps_delete' ) {
    if (file_exists(__DIR__. '/gj-maps-delete.php')) {
      include_once(__DIR__. '/gj-maps-delete.php');
    }
    else {
      echo 'Delete file is missing';  
    }
  } ?>

</div>
