<?php

class gjMapsDB {

  /**
   * WordPress database object
   *
   * @var $wpdb object
   */
  private $wpdb;

  /**
   * Table prefix
   *
   * @var $prefix string
   */
  protected $prefix;

  /**
   * Maps table
   *
   * @var $mapsTable string
   */
  protected $mapsTable;

  /**
   * Poi table
   *
   * @var $poiTable string
   */
  protected $poiTable;

  /**
   * Categories table
   *
   * @var $catTable
   */
  protected $catTable;

  /**
   * Constructor
   *
   * @return void
   */
  function __construct()
  {
    global $wpdb;

    $this->wpdb   = $wpdb;
    $this->prefix = $wpdb->prefix;

    $this->setMapsTable();
    $this->setPoiTable();
    $this->setCatTable();
  }

  /**
   * Sets the maps table to a class var
   *
   * @since 0.3
   *
   * @return void
   */
  function setMapsTable()
  {
    $this->mapsTable = $this->prefix."gjm_maps";
  }

  /**
   * Sets the poi table to a class var
   *
   * @since 0.3
   *
   * @return void
   */
  function setPoiTable()
  {
    $this->poiTable = $this->prefix."gjm_poi";
  }

  /**
   * Sets the category table to a class var
   *
   * @since 0.3
   *
   * @return void
   */
  function setCatTable()
  {
    $this->catTable = $this->prefix."gjm_cat";
  }

  /**
   * Returns a count of the number of POI, does not distinctly care about what map they are assigned
   *
   * @since 0.3
   *
   * @param $type string
   *
   * @return integer
   */
  function countRows($type='OBJECT')
  {
    $count = $this->wpdb->get_results(
      "SELECT map_id, COUNT(*) 
       FROM $this->poiTable 
       GROUP BY map_id;
      ");

    return $count;
  }

  /**
   * Delete all data
   *
   * Built to delete all data, does not accept any argments, returns a $response array
   *
   * @since 0.3
   *
   * @return array
   */
  function deleteAllData()
  {
    $response['poi']  = $this->deleteAllPOI();
    $response['cat']  = $this->deleteAllCat();
    $response['maps'] = $this->deleteAllMaps();

    return $response;
  }

  /**
   * Retrieves all the maps as an object
   *
   * @since 0.3
   *
   * @param $type string
   *
   * @return object
   */
  function getMaps($type='OBJECT')
  {
    return $this->wpdb->get_results("SELECT * FROM $this->mapsTable WHERE 1=1", $type);
  }

  /**
   * Returns the maps max poi id
   * 
   * @since 0.3
   *
   * @param $type string
   *
   * @return object
   */
  function maxMapId($type = 'OBJECT')
  {
    return $this->wpdb->get_results("SELECT MAX(id) AS 'max_id' FROM $this->mapsTable", $type);
  }

  /**
   * Returns the maps min poi id
   *
   * @since 0.3
   *
   * @param $type string
   *
   * @return object
   */
  function minMapId($type = 'OBJECT')
  {
    return $this->wpdb->get_results("SELECT MIN(id) AS 'low_id' FROM $this->mapsTable", $type);
  }

  /**
   * Returns a map id based off a maps name
   *
   * @since 0.3
   *
   * @param $name string
   * @param $type string
   *
   * @return int || false
   */
  function getMapId($name, $type='OBJECT')
  {
    $sql = $this->wpdb->prepare(
      "SELECT *
       FROM $this->mapsTable
       WHERE name = %s",
       $name
    );

    $query = $this->wpdb->get_results($sql, $type);

    if (isset($query[0]) && isset($query[0]->id)) {
      return $query[0]->id;
    }

    return false;
  }

  /**
   * Returns a map name based off a maps id
   *
   * @since 0.3
   *
   * @param $id int
   * @param $type string
   *
   * @return string || false
   */
  function getMapName($id, $type='OBJECT')
  {
    $sql = $this->wpdb->prepare(
      "SELECT *
       FROM $this->mapsTable
       WHERE id = %d",
       $id
    );

    $query = $this->wpdb->get_results($sql, $type);

    if (isset($query[0]) && isset($query[0]->name)) {
      return $query[0]->name;
    }

    return false;
  }

  /**
   * Saves a map to the database
   *
   * @since 0.3
   *
   * @param $map_id int
   *
   * @return object
   */
  function saveMap($map_id)
  {
    return $this->wpdb->insert($this->mapsTable,
      array(
        'id'   => $map_id,
        'name' => 'Map ' . $map_id
      )
    );
  }

  /**
   * Updates a maps name 
   *
   * @since 0.3
   *
   * @param $data array
   *
   * @return int || false
   */
  function updateMap($data)
  {
    if(isset($data['name']) && $data['id']) {
      return $this->wpdb->update($this->mapsTable, array('name' => $data['name']), array('id' => $data['id']));
    }

    return false;
  }

  /**
   * Deletes a map
   *
   * @since 0.3
   *
   * @param $map_id int
   *
   * @return 
   */

  function deleteMap($map_id)
  {
    $sql = $this->wpdb->prepare(
      "DELETE FROM $this->mapsTable
       WHERE id = %d",
       $map_id
    );

    return $this->wpdb->prepare($sql);
  }

  /**
   * Delete ALL maps
   * 
   * @since 0.3
   *
   * @return int || false
   */
  function deleteAllMaps()
  {
    return $this->wpdb->query("DELETE FROM $this->mapsTable");
  }

  /**
   * Retrieve POI for a specific map id, allows offset/length overrides
   * 
   * @since 0.3
   *
   * @param $map_id int
   * @param $offset int
   * @param $length int
   * @param $type string
   *
   * @return object
   */
  function getPoi($map_id, $offset = 0, $length = 999, $type = 'OBJECT')
  {
    $sql = $this->wpdb->prepare(
      "SELECT *
       FROM $this->poiTable
       WHERE map_id = %d
       LIMIT %d, %d",
       $map_id, $offset, $length
    );

    $query = $this->wpdb->get_results($sql, $type);

    return stripslashes_deep($query);
  }

  /**
   * Retrieves POI that have a 0 latitude OR longitude
   *
   * @since 1.0.0
   *
   * @param $map_id int
   * @param $type string
   *
   * @return object
   */
  function getPoiWithZeroLatLng($map_id, $type='OBJECT')
  {
    $sql = $this->wpdb->prepare(
      "SELECT *
       FROM $this->poiTable
       WHERE map_id = %d
       AND lat=0 OR lng=0",
       $map_id
    );
    return $this->wpdb->get_results($sql, $type);
  }

  /**
   * Create Poi
   *
   * @since 0.1
   *
   * @param array
   *
   * @return int || false
   */
  function createPOI($poi)
  {
    foreach ($poi as $key=>$value) {
      $insert[] = $this->wpdb->insert($this->poiTable,
        array(
          'cat_id'  => isset($value['cat_id']) ? $value['cat_id'] : '',
          'map_id'  => isset($value['map_id']) ? $value['map_id'] : '',
          'name'    => isset($value['name']) ? $value['name'] : '',
          'address' => isset($value['address']) ? $value['address'] : '',
          'city'    => isset($value['city']) ? $value['city'] : '',
          'state'   => isset($value['state']) ? $value['state'] : '',
          'zip'     => isset($value['zip']) ? $value['zip'] : '',
          'country' => isset($value['country']) ? $value['country'] : '',
          'phone'   => isset($value['phone']) ? $value['phone'] : '',
          'url'     => isset($value['url']) ? $value['url'] : ''
        )
      );
    }

    return (in_array(false, $insert, true)) ? false : sizeof($insert);
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

    return stripslashes_deep($query);
  
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
