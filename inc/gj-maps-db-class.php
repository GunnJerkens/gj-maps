<?php

class gjMapsDB {

  private $wpdb;

  function __construct() {

    global $wpdb;

    $this->wpdb = $wpdb;

  }

  /**
  *
  * Set maps table
  *
  * Just a helper function to set our default maps table
  *
  * @since 0.3
  *
  */

  function mapsTable() {

    $table = $this->wpdb->prefix.'gjm_maps';

    return $table;

  }

  /**
  *
  * Set poi table
  *
  * Just a helper function to set our default poi table
  *
  * @since 0.3
  *
  */

  function poiTable() {

    $table = $this->wpdb->prefix.'gjm_poi';

    return $table;

  }

  /**
  *
  * Set cat table
  *
  * Just a helper function to set our default cat table
  *
  * @since 0.3
  *
  */

  function catTable() {

    $table = $this->wpdb->prefix.'gjm_cat';

    return $table;

  }

  /**
  *
  * Count poi 
  *
  * Just a helper function to count the number of poi, accepts a return type argument
  * and defaults to 'OBJECT'
  *
  * @since 0.3
  *
  */

  function countRows($type='OBJECT') {

    $table_name = $this->poitable();

    $count = $this->wpdb->get_results("SELECT map_id, COUNT(*) FROM `gj_gjm_poi` GROUP BY map_id;");

    return $count;

  }

  /**
  *
  * Delete all data
  *
  * Built to delete all data, does not accept any argments, returns a $response array
  *
  * @since 0.3
  *
  */

  function deleteAllData() {

    $response['poi'] = $this->deleteAllPOI();
    $response['cat'] = $this->deleteAllCat();
    $response['maps'] = $this->deleteAllMaps();

    return $response;

  }


  /**
  * 
  * DEPRECATED -- Get Map(s)
  *
  * Takes a type and where statement, defaults to Object && 1=1
  * 
  * @since 0.1
  *
  **/

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

  /**
  * 
  * Get Maps
  *
  * Takes an options array (type, map_id), will return all maps if no map id
  * 
  * @since 0.3
  *
  **/

  function getMaps($options) {

    $table_name = $this->mapsTable();

    $type = isset($options['type']) ? $options['type'] : 'OBJECT';
    $where = isset($options['map_id']) ? "WHERE id = '".$options['map_id']."'" : '';

    $query = $this->wpdb->get_results(
      "
      SELECT *
      FROM $table_name
      $where
      ",
      $type
    );

    return $query;

  }

  /**
  * 
  * Max map id
  *
  * Accepts a type to return, defaults to 'OBJECT'
  * 
  * @since 0.3
  *
  **/

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

  /**
  * 
  * Min map id
  *
  * Accepts a type to return, defaults to 'OBJECT'
  * 
  * @since 0.3
  *
  **/

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

  /**
  * 
  * Get map id
  *
  * Requires a name argument, optionally accepts a type argument to return with a default of 'OBJECT'
  * 
  * @since 0.3
  *
  **/

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

    $mapID = $query[0]->id;

    return $mapID;

  }

  /**
  * 
  * Get map name
  *
  * Requires a id argument, optionally accepts a type argument to return with a default of 'OBJECT'
  * 
  * @since 0.3
  *
  **/

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

  /**
  * 
  * Save map
  *
  * Requires an id argument for the newly created map, returns an integer, 0 or 1
  * 
  * @since 0.3
  *
  **/

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

  /**
  * 
  * Edit map
  *
  * Requires the $post as an argument, returns an integer, 0 or 1
  * 
  * @since 0.3
  *
  **/

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

  /**
  * 
  * Delete map
  *
  * Requires the map_id as an argument, returns an integer, 0 or 1
  * 
  * @since 0.3
  *
  **/

  function deleteMap($map_id) {

    $table_name = $this->mapsTable();

    $query = $this->wpdb->query(
      $this->wpdb->prepare(
        "
        DELETE FROM $table_name
        WHERE id = %d
        ",
        $map_id
      )
    );

    return $query;

  }

  /**
  * 
  * Delete ALL maps
  *
  * Requires and accepts no arguments
  * 
  * @since 0.3
  *
  **/

  function deleteAllMaps() {

    $table_name = $this->mapsTable();

    $query = $this->wpdb->query(
      "
      DELETE FROM $table_name
      "
    );

    return $query;

  }

  /**
  * 
  * DEPRECATED -- Retrieve POI
  *
  * Takes the TYPE, WHERE and a single AND statement, returns an OBJECT by default
  * 
  * @since 0.1
  *
  **/

  function get_poi($type='OBJECT', $where = NULL, $and = NULL) {

    $table_name = $this->poiTable();

    if($where !== NULL && $where !== 'new') {

      $where = "map_id = $where";

      if($and !== NULL) {
        $and = "AND $and";
      } else {
        $and = '';
      }

      $query = $this->wpdb->get_results(
        "
        SELECT *
        FROM $table_name
        WHERE $where
        $and
        ",
        $type
      );

    } else {

      $query = false;

    }

    return $query;

  }

  /**
  * 
  * Retrieve POI
  *
  * Requires an options array(type, map_id[required], offset, length, lat) & returns an object of results
  * 
  * @since 0.3
  *
  **/

  function getPOI($options) {

    $table_name = $this->poiTable();

    $type = isset($options['type']) ? $options['type'] : 'OBJECT';
    $where = isset($options['map_id']) && $options['map_id'] !== NULL && $options['map_id'] !== 'new' ? "map_id = '".$options['map_id']."'" : false;

    if(isset($options['lat'])) {

      $where .= " AND lat = ".$options['lat'];

    }

    if(isset($options['offset']) && isset($options['length'])) {

      $where .= " LIMIT ".$options['offset'].", ".$options['length'];

    }

    if($where) {

      $query = $this->wpdb->get_results(
        "
        SELECT *
        FROM $table_name
        WHERE $where
        ",
        $type
        );

    } else {

      $query = 'Map ID option malformed.';

    }

    return $query;

  }

  /**
  *
  * Create POI
  *
  * Expects an array of POI data, returns an integer, 0 or 1
  *
  * @since 0.1
  *
  **/

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
          'url'=>$value['url']
        )
      );

    }

    return $insert;

  }

  /**
  *
  * Edit POI
  *
  * Expects an array of POI data to edit, returns an integer, 0 or 1
  *
  * @since 0.1
  *
  **/

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

  /**
  *
  * Delete POI
  *
  * Requires a POI id as an argument, returns an integer, 0 or 1
  *
  * @since 0.1
  *
  **/

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

  /**
  *
  * Delete POI
  *
  * Requires a map id as an argument, returns an integer, 0 or 1
  *
  * @since 0.3
  *
  **/

  function deleteMapPOI($map_id) {

    $table_name = $this->poiTable();

    $query = $this->wpdb->query(
      $this->wpdb->prepare(
        "
        DELETE FROM $table_name
        WHERE map_id = %d
        ",
        $map_id
      )
    );

    return $query;

  }

  /**
  *
  * Delete POI
  *
  * Requires a map id as an argument, returns an integer, 0 or 1
  *
  * @since 0.3
  *
  **/

  function deleteAllPOI() {

    $table_name = $this->poiTable();

    $query = $this->wpdb->query(
      "TRUNCATE TABLE $table_name"
    );

    return $query;

  }

  /**
  *
  * Get category id
  *
  * Requires a category name and map id, option argument is the return type
  *
  * @since 0.3
  *
  **/

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

  /**
  *
  * Get category
  *
  * Optional return $type and $where that should be map_id
  *
  * @since 0.1
  *
  **/

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

  /**
  *
  * Create category
  *
  * Pass in an array of category date, returns an integer 0/1
  *
  * @since 0.1
  *
  **/

  function createCat($cat) {

    $table_name = $this->catTable();

    $color = isset($cat['color']) ? $cat['color'] : '';
    $icon = isset($cat['icon']) ? $cat['icon'] : '';

    $insert = $this->wpdb->insert(
      $table_name, 
      array(
        'map_id' => $cat['map_id'],
        'name' => $cat['name'],
        'color' => $color,
        'icon' => $icon
      )
    );

    return $insert;

  }

  /**
  *
  * Edit category
  *
  * Pass in an array of category date, returns an integer 0/1
  *
  * @since 0.1
  *
  **/

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

  /**
  *
  * Delete category
  *
  * Pass in the category id, returns an integer 0/1
  *
  * @since 0.3
  *
  **/

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

  /**
  *
  * Delete all map categories
  *
  * Pass in the map id returns an integer 0/1
  *
  * @since 0.3
  *
  **/

  function deleteMapCat($map_id) {

    $table_name = $this->catTable();

    $query = $this->wpdb->query(
      $this->wpdb->prepare(
        "
        DELETE FROM $table_name
        WHERE map_id = %d
        ",
        $map_id
      )
    );

    return $query;

  }

  /**
  *
  * Delete all category
  *
  * Accepts no arguments, returns an integer 0/1
  *
  * @since 0.3
  *
  **/

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
