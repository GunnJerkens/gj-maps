<?php

class gjMapsDB
{

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
   * Delete all data, this is just a wrapper for 3 class methods
   *
   * @since 0.3
   *
   * @return array
   */
  function deleteAllData()
  {
    $response['poi']  = $this->truncatePoi();
    $response['cat']  = $this->truncateCategories();
    $response['maps'] = $this->truncateMaps();

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
   * Retrives a single map by id
   *
   * @since 1.0.0
   *
   * @param $map_id int
   *
   * @return object
   */
  function getMap($map_id, $type='OBJECT')
  {
    $sql = $this->wpdb->prepare(
    "SELECT *
     FROM $this->mapsTable
     WHERE id = %d",
     $map_id
    );
    return $this->wpdb->get_results($sql, $type);
  }

  /**
   * Returns the maps max poi id
   *
   * @since 0.3
   *
   * @param $type string
   *
   * @return int || false
   */
  function maxMapId($type = 'OBJECT')
  {
    $max = $this->wpdb->get_results("SELECT MAX(id) AS 'max_id' FROM $this->mapsTable", $type);
    return isset($max[0]) && isset($max[0]->max_id) ? (int) $max[0]->max_id : false;
  }

  /**
   * Returns the maps min poi id
   *
   * @since 0.3
   *
   * @param $type string
   *
   * @return int || false
   */
  function minMapId($type = 'OBJECT')
  {
    $min = $this->wpdb->get_results("SELECT MIN(id) AS 'low_id' FROM $this->mapsTable", $type);
    return isset($min[0]) && isset($min[0]->low_id) ? (int) $min[0]->low_id : false;
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
   * Create a map
   *
   * @since 0.3
   *
   * @param $map_id int
   *
   * @return object
   */
  function createMap()
  {
    return $this->wpdb->insert($this->mapsTable, array('name' => "New Map"));
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
    if(isset($data['name']) && isset($data['map_id'])) {
      if(empty(trim($data['map_id']))) {
        $this->createMap();
        $data['map_id'] = $this->maxMapId();
      }
      return $this->wpdb->update($this->mapsTable, array('name' => $data['name']), array('id' => $data['map_id']));
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

    return $this->wpdb->query($sql);
  }

  /**
   * Delete ALL maps
   *
   * @since 0.3
   *
   * @return int || false
   */
  function truncateMaps()
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
   * Creates POI
   *
   * @since 0.1
   *
   * @param array
   *
   * @return int || false
   */
  function createPoi($poi)
  {
    foreach ($poi as $key=>$value) {
      $insert[] = $this->wpdb->insert($this->poiTable,
        array(
          'cat_id'  => isset($value['cat_id'])  ? $value['cat_id']  : '',
          'map_id'  => isset($value['map_id'])  ? $value['map_id']  : '',
          'name'    => isset($value['name'])    ? $value['name']    : '',
          'address' => isset($value['address']) ? $value['address'] : '',
          'city'    => isset($value['city'])    ? $value['city']    : '',
          'state'   => isset($value['state'])   ? $value['state']   : '',
          'zip'     => isset($value['zip'])     ? $value['zip']     : '',
          'country' => isset($value['country']) ? $value['country'] : '',
          'phone'   => isset($value['phone'])   ? $value['phone']   : '',
          'url'     => isset($value['url'])     ? $value['url']     : '',
          'lat'     => isset($value['lat'])     ? $value['lat']     : '',
          'lng'     => isset($value['lng'])     ? $value['lng']     : ''
        )
      );
    }

    return (in_array(false, $insert, true)) ? false : sizeof($insert);
  }

  /**
   * Update POI
   *
   * @since 0.1
   *
   * @param $poi array
   *
   * @return int || false
   */
  function updatePoi($editItems)
  {
    foreach($editItems as $poi) {
      $update[] = $this->wpdb->update($this->poiTable,
        array(
          'map_id'  => $poi['map_id'],
          'cat_id'  => $poi['cat_id'],
          'name'    => $poi['name'],
          'address' => $poi['address'],
          'city'    => $poi['city'],
          'state'   => $poi['state'],
          'zip'     => $poi['zip'],
          'country' => $poi['country'],
          'phone'   => $poi['phone'],
          'url'     => $poi['url'],
          'lat'     => $poi['lat'],
          'lng'     => $poi['lng']
        ),
        array('id' => $poi['id'])
      );
    }

    return (in_array(false, $update, true)) ? false : sizeof($update);
  }

  /**
   * Delete POI
   *
   * @since 0.1
   *
   * @param $id array
   */
  function deletePoi($poi)
  {
    foreach($poi as $id) {
      $sql = $this->wpdb->prepare("DELETE FROM $this->poiTable WHERE id = %d", $id);
      $delete[] = $this->wpdb->query($sql);
    }

    return (in_array(false, $delete, true)) ? false : sizeof($delete);
  }

  /**
   * Delete all Poi based on a map_id
   *
   * @since 0.3
   *
   * @param $map_id int
   *
   * @return bool
   */
  function deletePoiByMap($map_id)
  {
    $sql = $this->wpdb->prepare("DELETE FROM $this->poiTable WHERE map_id = %d", $map_id);
    return $this->wpdb->query($sql);
  }

  /**
   * Truncates all poi from the the table
   *
   * @since 0.3
   *
   * @return bool
   */
  function truncatePoi()
  {
    return $this->wpdb->query("TRUNCATE TABLE $this->poiTable");
  }

  /**
   * Get a category
   *
   * @since 0.3
   *
   * @param $map_id int
   * @param $cat string
   * @param $type string
   *
   * @return
   */
  function getCategory($map_id, $category_name, $type='OBJECT')
  {
    $sql = $this->wpdb->prepare(
      "SELECT *
       FROM $this->catTable
       WHERE name = %s
       AND map_id = %d",
       $category_name, $map_id
    );

    return $this->wpdb->get_results($sql, $type);
  }

  /**
   * Get all categories for a map
   *
   * @since 0.1
   *
   * @param $map_id int
   * @param $type string
   *
   * @return object
   */

  function getCategories($map_id, $type='OBJECT')
  {
    $sql = $this->wpdb->prepare(
      "SELECT *
       FROM $this->catTable
       WHERE map_id = %d",
       $map_id
    );

    return stripslashes_deep($this->wpdb->get_results($sql, $type));
  }

  /**
   * Create a category
   *
   * @since 0.1
   *
   * @param $category array
   *
   * @return bool
   */
  function createCategory($category)
  {
    $category['color'] = isset($category['color']) ? $category['color'] : '';
    $category['icon']  = isset($category['icon']) ? $category['icon'] : '';

    return $this->wpdb->insert($this->catTable, $category);
  }

  /**
   * Updates categories
   *
   * @since 0.1
   *
   * @param array
   *
   * @return int || false
   */
  function updateCategories($category)
  {
    return $this->wpdb->update($this->catTable, $category, array('id' => $category['id'], 'map_id' => $category['map_id']));
  }

  /**
   * Delete a category
   *
   * @since 0.3
   *
   * @param $map_id int
   *
   * @return bool
   */
  function deleteCategory($cat_id)
  {
    $sql = $this->wpdb->prepare("DELETE FROM $this->catTable WHERE id = %d", $cat_id);
    return $this->wpdb->query($sql);
  }

  /**
   * Delete all map categories
   *
   * @since 0.3
   *
   * @param $map_id int
   *
   * @return bool
   */
  function deleteCategoriesByMap($map_id)
  {
    $sql = $this->wpdb->prepare("DELETE FROM $this->catTable WHERE map_id = %d", $map_id);
    return $this->wpdb->query($sql);
  }

  /**
   * Truncate all categories
   *
   * @since 0.3
   *
   * @return bool
   */
  function truncateCategories()
  {
    return $this->wpdb->query("DELETE FROM $this->catTable");
  }

}
