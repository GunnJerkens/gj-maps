<?php

/*
 * Categories
 */

//I used 'cat' instead of 'categories' because I am lazy
if ( ! class_exists( 'GJ_cat') ) {
   class GJ_cat {

      function __construct() {
         //allows you to get categories from front-end
         add_action( 'wp_enqueue_scripts', array( &$this, 'gj_get_cat' ) );
      }

      
      function gj_get_cat($type='OBJECT', $where='1=1') {
         //Allows you to set the type of the return value (assc. array or stdClass) and the WHERE clause, if necessary
         global $wpdb;

         $table_name = $wpdb->prefix . "gj_cat";
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
      
      
   }
}


if ( class_exists( 'GJ_cat' ) ) {
$GJ_cat = new GJ_cat();
}