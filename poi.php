<?php

/*
 * Retrieve POIs from $wpdb
 */

if ( ! class_exists( 'GJ_api') ) {
   class GJ_api {

      function __construct() {
        //Makes gj_get_POI available from front-end
         add_action( 'wp_enqueue_scripts', array( &$this, 'gj_get_POI' ) );
      }

      public function gj_get_POI($type='OBJECT', $where='1=1') {
        //Allows you to set the type of the return value (assc. array or stdClass) and the WHERE clause, if necessary
         global $wpdb;

         $table_name = $wpdb->prefix . "gj_poi";
         $query = $wpdb->get_results(
            "
            SELECT *
            FROM $table_name
            WHERE $where
            ",
            $type
         );

         return $query;

      }

      public function gj_POI_frontend() {
         if ( ! $GJ_api ) {
            $GJ_api = new GJ_api();
         }

         //writes the JS to the page, including POIs and categories
           $poi = json_encode($GJ_api->gj_get_POI());
           echo '<script type="text/javascript">';
           echo 'var poi = ';
           print_r($poi);
           echo ';';

           if ( ! $GJ_cat ) {
              $GJ_cat = new GJ_cat();
           }
           $poi = json_encode($GJ_cat->gj_get_cat());
           echo 'var cat = ';
           print_r($poi);
           echo ';';

           echo 'var center_lat = '.get_option('gj_center_lat').';';
           echo 'var center_lng = '.get_option('gj_center_lng').';';

           echo '</script>';
      }
   }
}


if ( class_exists( 'GJ_api' ) ) {
$GJ_api = new GJ_api();
}