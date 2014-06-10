<?php

class gjMapsDB {

  private $wpdb;

  function __construct() {

    global $wpdb;

    $this->wpdb = $wpdb;

  }

  function deleteAllData() {

    $response['poi'] = $this->deleteAllPOI();
    $response['cat'] = $this->deleteAllCat();
    $response['maps'] = $this->deleteAllMaps();

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

  function maxMapID($type = 'OBJECT') {

    $table_name = $this->mapsTable();

    $maxMapID = $this->wpdb->get_results(
      "
      SELECT MAX(id) AS 'max_id'
      FROM $table_name
      ",
      $type
    );

    return $maxMapID;

  }

  function minMapID($type = 'OBJECT') {

    $table_name = $this->mapsTable();

    $minMapID = $this->wpdb->get_results(
      "
      SELECT MIN(id) AS 'low_id'
      FROM $table_name
      ",
      $type
    );

    return $minMapID;

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

  function getMapName($id, $type='OBJECT') {

    $table_name = $this->mapsTable();
    $where = "id = '$id'";

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

  function editMapSettings($post) {

    $table_name = $this->mapsTable();

    $insert = $this->wpdb->update(
      $table_name, 
      array(
        'name'=>$post['name']
      ),
      array('id'=>$post['id'])
    );

    return $insert;

  }

  function deleteAllMaps() {

    $table_name = $this->mapsTable();

    $query = $this->wpdb->query(
      "
      DELETE FROM $table_name
      "
    );

    return $query;

  }

  /*
  * POI Database Functions
  */

  function get_poi($type='OBJECT', $where = NULL) {

    $table_name = $this->poiTable();

    if($where !== NULL && $where !== 'new') {

      $where = "map_id = $where";

      $query = $this->wpdb->get_results(
        "
        SELECT *
        FROM $table_name
        WHERE $where
        ",
        $type
      );

    } else {

      $query = false;

    }

    return $query;

  }

  function createPOI($poi) {

    $table_name = $this->poiTable();

    foreach ($poi as $key=>$value) {

      $insert = $this->wpdb->insert(
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

  function editPOI($editItems) {

    $table_name = $this->poiTable();

    foreach($editItems as $poi) {

      $update = $this->wpdb->update(
        $table_name,
        array(
          'map_id' => $poi['map_id'],
          'cat_id' => $poi['cat_id'],
          'name' => $poi['name'],
          'address' => $poi['address'],
          'city' => $poi['city'],
          'state' => $poi['state'],
          'zip' => $poi['zip'],
          'country'=> $poi['country'],
          'phone' => $poi['phone'],
          'url' => $poi['url'],
          'lat' => $poi['lat'],
          'lng' => $poi['lng']
        ),
        array('id'=>$poi['id'])
      );

    }

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

  function getCatID($name, $mapID, $type='OBJECT') {

    $table_name = $this->catTable();
    $catName = "name = '$name'";
    $mapID = "map_id = '$mapID'";

    $query = $this->wpdb->get_results(
      "
      SELECT *
      FROM $table_name
      WHERE $catName
      AND $mapID
      ",
      $type
    );

    return $query;

  }

  function get_cat($type='OBJECT', $where = NULL) {

    $table_name = $this->catTable();

    if($where !== NULL && $where !== 'new') {

      $where = "map_id = $where";

      $query = $this->wpdb->get_results(
        "
        SELECT *
        FROM $table_name
        WHERE $where
        ",
        $type
      );

    } else {

      $query = false;

    }

    return $query;
  
  }

  function createCat($cat) {

    $table_name = $this->catTable();

    $insert = $this->wpdb->insert(
      $table_name, 
      array(
        'map_id' => $cat['map_id'],
        'name' => $cat['name'],
        'color' => $cat['color'],
        'icon' => $cat['icon'],
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
        'id' => $cat['id'],
        'map_id' => $cat['map_id']
      ) 
    );

    return $update;

  }

  function deleteCat($id) {

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
      "
      DELETE FROM $table_name
      "
    );

    return $query;

  }

}
