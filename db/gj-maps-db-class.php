<?php

class gjMapsDB {

  private $wpdb;

  function __construct() {

    global $wpdb;

    $this->wpdb = $wpdb;

  }

  function deleteAllData() {

    $response['poi'] = $this->deleteAllPOI();
    $response['maps'] = $this->deleteAllMaps();
    $response['cat'] = $this->deleteAllCat();

    return $response;

  }

  /*
  * Table Name Functions
  */

  function mapsTable() {

    $table = $this->wpdb->prefix.'gjm_maps';

    return $table;

  }

  function poiTable() {

    $table = $this->wpdb->prefix.'gjm_poi';

    return $table;

  }

  function catTable() {

    $table = $this->wpdb->prefix.'gjm_cat';

    return $table;

  }


  /*
  * Map Database Functions
  */

  function get_map($type='OBJECT', $where='1=1') {
    global $wpdb;

    $table_name = $this->mapsTable();

    $query = $this->wpdb->get_results(
      "
      SELECT *
      FROM $table_name
      WHERE $where
      ",
      $type
    );

    return $query;

  }

  /*
  * I don't know what this does?
  * It should probably move to the admin-class, it's not a db function?
  */
  function get_map_key($id, $obj) {

    $mapKey = false;

    foreach ($obj as $key => $value) {

      if ($value->id == $id) {

        $mapKey = $key;

      }

    }

    return $mapKey;

  }

  function maxMapID() {

    $table_name = $this->mapsTable();

    $maxMapID = $this->wpdb->get_results(
      "
      SELECT MAX(id)
      FROM $table_name
      "
    );

    return $maxMapID;

  }

  function getMapID($name, $type='OBJECT') {

    $table_name = $this->mapsTable();
    $where = "name = '$name'";

    $query = $this->wpdb->get_results(
      "
      SELECT *
      FROM $table_name
      WHERE $where
      ",
      $type
    );

    return $query;

  }

  function saveMap($id) {

    $table_name = $this->mapsTable();

    $insert = $this->wpdb->insert(
      $table_name,
      array(
        'id'=>$id,
        'name'=>'Map ' . $id,
      )
    );

    return $insert;

  }

  function editMapSettings($ms) {

    $table_name = $this->mapsTable();

    $insert = $this->wpdb->update(
      $table_name, 
      array(
        'name'=>$ms['name'],
        'c_lat'=>$ms['c_lat'],
        'c_lng'=>$ms['c_lng'],
        'm_zoom'=>$ms['m_zoom']
      ),
      array('id'=>$ms['id'])
    );

    return $insert;

  }

  function deleteAllMaps() {

    $table_name = $this->mapsTable();

    $query = $this->wpdb->query(
      "TRUNCATE TABLE $table_name"
    );

    return $query;

  }

  /*
  * POI Database Functions
  */

  function get_poi($type='OBJECT', $where='1=1') {

    $table_name = $this->poiTable();

    $query = $this->wpdb->get_results(
      "
      SELECT *
      FROM $table_name
      WHERE $where
      ",
      $type
    );

    return $query;

  }

  function savePOI ($poi) {

    $table_name = $this->poiTable();

    foreach ($poi as $key=>$value) {

      $insert[] = $this->wpdb->insert(
        $table_name, 
        array(
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
        )
      );

    }

    return $insert;

  }

  function editPOI ($poi) {

    $table_name = $this->poiTable();

    $update = $this->wpdb->update(
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

    return $update;

  }

  function deletePOI($id = false) {

    $table_name = $this->poiTable();

    if($id) {

      $query = $this->wpdb->query(
          $this->wpdb->prepare(
            "
            DELETE FROM $table_name 
            WHERE id = %d
            ",
            $id
        )
      );

    }

    return $query;

  }

  function deleteAllPOI() {

    $table_name = $this->poiTable();

    $query = $this->wpdb->query(
      "TRUNCATE TABLE $table_name"
    );

    return $query;

  }

  /*
  * Category Database Functions
  */

  function get_cat($type='OBJECT', $where='1=1') {

    $table_name = $this->catTable();
  
    $query = $this->wpdb->get_results(
      "
      SELECT *
      FROM $table_name
      WHERE $where
      ",
      $type
    );

    return $query;
  
  }

  function saveCat($cat) {

    $table_name = $this->catTable();

    $insert = $this->wpdb->insert(
      $table_name, 
      array(
        'name'=>$cat['name'],
        'color'=>$cat['color'],
        'icon'=>$cat['icon'],
      )
    );

    return $insert;

  }

  function editCat($cat) {

    if(array_key_exists('icon',$cat) && $cat['icon'] == null) {

      unset($cat['icon']);

    }

    $table_name = $this->catTable();

    $update = $this->wpdb->update( 
      $table_name, 
      $cat, 
      array(
        'id'=>$cat['id']
      ) 
    );

    return $update;

  }

  function deleteCat ($id) {

    $table_name = $this->catTable();

    $query = $this->wpdb->query(
      $this->wpdb->prepare(
        "
        DELETE FROM $table_name 
        WHERE id = %d
        ",
        $id
      )
    );

    return $query;

  }

  function deleteAllCat() {

    $table_name = $this->catTable();

    $query = $this->wpdb->query(
      "TRUNCATE TABLE $table_name"
    );

    return $query;

  }

}
