<?php

/*
 * Retrieve POIs from $wpdb
 */

if ( ! class_exists( 'GJ_api') ) {
   class GJ_api {

      function __construct() {
         add_action( 'wp_enqueue_scripts', array( &$this, 'gj_get_POI' ) );
      }

      public function gj_get_POI() {

         global $wpdb;

         $table_name = $wpdb->prefix . "gj_poi";
         $query = $wpdb->get_results(
            "
            SELECT *
            FROM $table_name
            WHERE 1=1

            "
         );

         return json_encode($query);

      }
   }
}


if ( class_exists( 'GJ_api' ) ) {
$GJ_api = new GJ_api();
}