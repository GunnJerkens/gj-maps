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

function deletePOI () {
	global $wpdb;

	$table_name = $wpdb->prefix . "gj_poi";

	$wpdb->query(
		$wpdb->prepare(
			"TRUNCATE TABLE $table_name"
		)
	);
}