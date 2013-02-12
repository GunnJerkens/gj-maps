<?php

/*
 * Adds POIs to the database
 */

function savePOI ($poi) {

   global $wpdb;
   $table_name = $wpdb->prefix . "gj_poi";

   foreach ($poi as $key=>$value) {

   	$rows_affected = $wpdb->insert( $table_name, array( 'cat'=>$value['cat'], 'name'=>$value['name'], 'address'=>$value['address'], 'city'=>$value['city'], 'state'=>$value['state'], 'zip'=>$value['zip'], 'country'=>$value['country'], 'phone'=>$value['phone'], 'url'=>$value['url'], 'lat'=>$value['lat'], 'lng'=>$value['lng'] ) );

   }

}

function editPOI ($poi) {

   global $wpdb;
   $table_name = $wpdb->prefix . "gj_poi";

   $rows_affected = $wpdb->update( $table_name, array( 'cat'=>$poi['cat'], 'name'=>$poi['name'], 'address'=>$poi['address'], 'city'=>$poi['city'], 'state'=>$poi['state'], 'zip'=>$poi['zip'], 'country'=>$poi['country'], 'phone'=>$poi['phone'], 'url'=>$poi['url'], 'lat'=>$poi['lat'], 'lng'=>$poi['lng'] ), array( 'id'=>$poi['id'] ) );

}

function deletePOI ($id) {
	global $wpdb;

	$table_name = $wpdb->prefix . "gj_poi";

   if ($id) {
      $wpdb->query(
         $wpdb->prepare(
            "
            DELETE FROM $table_name 
            WHERE id = %d
            ",
            $id
         )
      );
   } else {
      $wpdb->query(
         $wpdb->prepare(
            "TRUNCATE TABLE $table_name"
         )
      );
   }
}