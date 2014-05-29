<?php

class gjMapsDB {

  function saveMap ($id) {

    global $wpdb;
    $table_name = $wpdb->prefix . "gjm_maps";

    $rows_affected = $wpdb->insert(
      $table_name,
      array(
        'id'=>$id,
        'name'=>'Map ' . $id,
      )
    );
  }

  function editMapSettings($ms) {

    global $wpdb;
    $table_name = $wpdb->prefix . "gjm_maps";

    $rows_affected = $wpdb->update(
      $table_name, 
      array(
        'name'=>$ms['name'],
        'c_lat'=>$ms['c_lat'],
        'c_lng'=>$ms['c_lng'],
        'm_zoom'=>$ms['m_zoom']
      ),
      array('id'=>$ms['id'])
    );

  }

  function savePOI ($poi) {

    global $wpdb;
    $table_name = $wpdb->prefix . "gjm_poi";

    foreach ($poi as $key=>$value) {
      $rows_affected = $wpdb->insert( $table_name, array(
        'cat_id'=>$value['cat_id'],
        'map_id'=>$value['map_id'],
        'name'=>$value['name'],
        'address'=>$value['address'],
        'city'=>$value['city'],
        'state'=>$value['state'],
        'zip'=>$value['zip'],
        'country'=>$value['country'],
        'phone'=>$value['phone'],
        'url'=>$value['url'],
        'lat'=>$value['lat'],
        'lng'=>$value['lng']
      ) );
    }

  }

  function editPOI ($poi) {

    global $wpdb;
    $table_name = $wpdb->prefix . "gjm_poi";

    $rows_affected = $wpdb->update(
      $table_name,
      array(
        'cat_id'=>$poi['cat_id'],
        'name'=>$poi['name'],
        'address'=>$poi['address'],
        'city'=>$poi['city'],
        'state'=>$poi['state'],
        'zip'=>$poi['zip'],
        'country'=>$poi['country'],
        'phone'=>$poi['phone'],
        'url'=>$poi['url'],
        'lat'=>$poi['lat'],
        'lng'=>$poi['lng']
      ),
      array( 'id'=>$poi['id'] )
    );
  }

  function deletePOI ($id = false) {
    global $wpdb;

    $table_name = $wpdb->prefix . "gjm_poi";

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
          "TRUNCATE TABLE $table_name"
       );
     }
  }

  function saveCat ($cat) {

    global $wpdb;
    $table_name = $wpdb->prefix . "gjm_cat";
    $rows_affected = $wpdb->insert( $table_name, array( 'name'=>$cat['name'], 'color'=>$cat['color'], 'icon'=>$cat['icon'], ) );

  }

  function editCat ($cat) {
    if(array_key_exists('icon',$cat) && $cat['icon'] == null) {
      unset($cat['icon']);
    }
    global $wpdb;
    $table_name = $wpdb->prefix . "gjm_cat";

    $rows_affected = $wpdb->update( 
      $table_name, $cat, array( 'id'=>$cat['id']) 
    );

  }

  function deleteCat ($id) {
    global $wpdb;

    $table_name = $wpdb->prefix . "gjm_cat";

    $wpdb->query(
      $wpdb->prepare(
        "
        DELETE FROM $table_name 
        WHERE id = %d
        ",
        $id
      )
    );

  }

  function get_poi($type='OBJECT', $where='1=1') {
    //Allows you to set the type of the return value (assc. array or stdClass) and the WHERE clause, if necessary
    global $wpdb;

    $table_name = $wpdb->prefix . "gjm_poi";
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

  function get_cat($type='OBJECT', $where='1=1') {
    //Allows you to set the type of the return value (assc. array or stdClass) and the WHERE clause, if necessary
    global $wpdb;

    $table_name = $wpdb->prefix . "gjm_cat";
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

  function get_map($type='OBJECT', $where='1=1') {
    //Allows you to set the type of the return value (assc. array or stdClass) and the WHERE clause, if necessary
    global $wpdb;

    $table_name = $wpdb->prefix . "gjm_maps";
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

  function get_map_key($id, $obj) {
    foreach ($obj as $key => $value) {
      if ($value->id == $id) {
        return $key;
      }
    }
  }


}
