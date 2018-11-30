<?php

class gjMapsAdmin
{

  /**
   * Holds our database class
   *
   * @var $db object
   */
  private $db;

  /**
   * Constructor
   *
   * @return void
   */
  function __construct()
  {
    $this->db = new gjMapsDB($this);
  }

  /**
   * Messaging method to the view
   *
   * @since 0.3
   *
   * @param $status bool
   * @param $message string
   *
   * @return array
   */
  function response($status, $message, $modal = null)
  {
    return array('error' => $status, 'message' => $message, 'modal' => $modal);
  }

  /**
  *
  * Creates the maps pagination
  *
  * Expects an integer, returns an array for pagination
  *
  * @since 0.3
  *
  **/
  function gjMapsPaginateTable($map_id, $showItems)
  {
    $allPoints  = $this->db->countRows();
    $totalItems = 0;

    foreach($allPoints as $map) {
      if((int) $map->map_id === $map_id) {
        $totalItems = (int) $map->{'COUNT(*)'};
        break;
      }
    }

    $pages = 1;

    if($totalItems != 0) {
      $pages = ceil($totalItems / $showItems);
    }

    $currentPage = 1;

    $url = parse_url($_SERVER['REQUEST_URI']);
    parse_str($url['query'], $urlArray);

    if(isset($urlArray['paged'])) {
      $currentPage = $urlArray['paged'];
    }

    $sqlOffset = ($currentPage * $showItems) - ($showItems);

    $pagination = array(
      'show_items'   => $showItems,
      'total_items'  => $totalItems,
      'pages'        => $pages,
      'current_page' => $currentPage,
      'sql_offset'   => $sqlOffset,
      'sql_length'   => $showItems
    );

    return $pagination;
  }

  /**
  *
  * Builds the url for pagination
  *
  * Push through the $_GET data
  *
  * @since 0.3
  *
  **/
  function gjMapsBuildURL($map_id = NULL)
  {
    $base = '?page=gj_maps';

    if($map_id != NULL || $map_id !== 'new') {
      $base .= '&map_id='.$map_id;
    }

    return $base;
  }

  /**
   * Creates the tabs on maps and categories pages.
   *
   * @since 0.3
   *
   * @param $page string
   * @param $maps array
   * @param $map_id int
   *
   * @return string
   */
  function mapsTab($page, $maps, $map)
  {
    $page = $page === 'cat' ? 'gj_maps_categories' : 'gj_maps';
    $tabs = '<h2 class="nav-tab-wrapper">';

    foreach ($maps as $key=>$value) {
      $tabs .= '<a href="?page='.$page.'&map_id='.$value->id.'" class="nav-tab'.((int) $map->id === (int) $value->id ? ' nav-tab-active' : '').'">'.$value->name.'</a>';
      if($value->id == $map->id) {
        $map_name = $value->name;
      }
    }

    $tabs .= '<a href="?page='.$page.'&map_id=new" class="nav-tab">+</a>';
    $tabs .= '</h2>';

    return $tabs;
  }

  /**
   * Create a map
   *
   * @since 0.3
   *
   * @return $map_id
   */
  function createMap()
  {
    $this->db->createMap();
    return $this->db->maxMapId();
  }

  /**
   * Delete an entire map
   *
   * @since 0.3
   *
   * @param $map_id int
   *
   * @return array
   */
  function deleteMap($map_id)
  {
    $responsePOI = $this->db->deletePoiByMap($map_id);
    $responseCat = $this->db->deleteCategoriesByMap($map_id);
    $responseMap = $this->db->deleteMap($map_id);

    if($responsePOI === false || $responseCat === false || $responseMap === false) {
      $response = $this->response(true, 'Something went wrong during the delete process.');
    } else {
      $response = $this->response(false, 'Map '.$map_id.' was successfully deleted.');
    }

    return $response;
  }

  /**
   * Renames the map
   *
   * @since 0.3
   *
   * @param array
   *
   * @return array
   */
  function renameMap($post)
  {
    if($this->db->updateMap($post)) {
      $response = $this->response(false, 'Map name changed successfully');
    } else {
      $response = $this->response(true, 'Map name failed to update');
    }
    return $response;
  }

  /**
   * Geocode the POI
   *
   * @since 0.1
   *
   * @param $map_id int
   *
   * @return array
   */
  function geocodePOI($map_id)
  {
    $poi = $this->db->getPoiWithZeroLatLng($map_id, 'ARRAY_A');
    $apiKey = get_option('gj_maps_api_key');
    if(sizeof($poi) > 0) {
      foreach($poi as $point) {

        if($point['address'] && $point['zip']) {
          $address = urlencode($point["address"].', '.$point['zip']);
        } else {
          $address = urlencode($point["address"].', '.$point['city'].', '.$point['state'].' '.$point['zip']);
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.$apiKey.'&sensor=false';
        $url .= '&address='.$address;

        $googleResponseEncoded = wp_remote_get($url);

        if(!is_wp_error($googleResponseEncoded)) {
          $googleResponse = json_decode($googleResponseEncoded['body']);

          if(isset($googleResponse->results[0]->geometry->location->lat) && isset($googleResponse->results[0]->geometry->location->lng)) {
            $point['lat'] = $googleResponse->results[0]->geometry->location->lat;
            $point['lng'] = $googleResponse->results[0]->geometry->location->lng;
          } else {
            $point['lat'] = '0';
            $point['lng'] = '0';
          }

          $updatePOI[] = $point;
        }

        // Do not remove, this avoids rate limiting on the Google API
        usleep(125000);
      }
    }

    if(!empty($updatePOI)) {
      $this->db->updatePoi($updatePOI);
      $response = $this->response(false, 'Updated coordinates successfully');
    } else {
      $response = $this->response(true, 'There were not points to update.');
    }

    return $response;
  }

  /**
   *
   * Create the poi
   * Requires the $poi, an array of poi data
   *
   * @since 0.1
   *
   */
  function createPOI($poi)
  {
    foreach($poi as $single) {
      if($single['cat_id'] === '0') {
        $defaultCatId = $this->db->createDefaultCategory($single['map_id']);
        $single['cat_id'] = $defaultCatId;
      }
      $createItems[] = $single;
    }

    if($this->db->createPoi($createItems)) {
      $response = $this->response(false, 'Successfully created new points of interest.');
    } else {
      $response = $this->response(true, 'Failed to create points of interest.');
    }

    return $response;
  }

  /**
   * Update the poi
   *
   * @since 0.1
   *
   * @param array
   *
   * @return array
   */
  function editPOI($post)
  {
    if($this->db->updatePoi($post)) {
      $response = $this->response(false, 'Points of interest updated successfully.');
    } else {
      $response = $this->response(true, 'Something went wrong during the update process.');
    }
    return $response;
  }

  /**
   * Delete the POI
   *
   * @since 0.3
   *
   * @param array
   *
   * @return array
   */
  function deletePOI($deleteItems)
  {
    foreach($deleteItems as $item) {
      $poi[] = $item['id'];
    }

    if($this->db->deletePoi($poi) > 0) {
      $response = $this->response(false, 'Items deleted successfully.');
    } else {
      $response = $this->response(true, 'Some items failed to delete');
    }

    return $response;
  }

  /**
  *
  * Create categories
  *
  * Expects an array of items to create
  *
  * @since 0.1
  *
  **/
  function createCat($createItems)
  {
    $hasError = false;

    foreach($createItems as $item) {
      unset($item['mode']);
      unset($item['id']);
      $response[] = $this->db->createCategory($item);
    }

    foreach($response as $response) {
      if($response !== 1) {
        $hasError = true;
      }
    }

    if(!$hasError) {
      $response = $this->response(false, 'Categories created successfully.');
    } else {
      $response = $this->response(true, 'Categories failed to be created.');
    }

    return $response;
  }

  /**
  *
  * Edit categories
  *
  * Expects an array of items to update
  *
  * @since 0.1
  *
  **/
  function editCat($updateItems)
  {
    $hasError = false;

    foreach($updateItems as $item) {
      unset($item['mode']);
      unset($item['delete']);
      $responses[] = $this->db->updateCategories($item);
    }

    foreach($responses as $response) {
      if($response !== 1) {
        $hasError = true;
      }
    }

    if(!$hasError) {
      $response = $this->response(false, 'Categories updated successfully.');
    } else {
      $response = $this->response(true, 'Categories failed to update.');
    }

    return $response;
  }

  /**
  *
  * Delete categories
  *
  * Expects an array of categories to delete, returns a response array
  *
  * @since 0.1
  *
  **/
  function deleteCat($deleteItems)
  {
    $hasError = false;
    $hasModal = false;
    $modalItems = array();

    foreach($deleteItems as $item) {
      // changes all POI to the Default category
      if($item['cat_delete_poi'] === '0') {
        if(!$this->db->updatePoiByCategoryToDefault($item['map_id'], $item['id'])) {
          $hasError = true;
        }
      // deletes all POI
      } else if($item['cat_delete_poi'] === '1') {
        if($this->db->deletePoiByCategory($item['id']) !== 1) {
          $hasError = true;
        }
      }
      $poiCount = $this->db->countPoiByCategory($item['id']);
      if($poiCount === '0') {
        if($this->db->deleteCategory($item['id']) !== 1) {
          $hasError = true;
        }
      } else {
        $hasError = true;
        $hasModal = 'has_poi';
        array_push($modalItems, $item['id']);
      }
    }

    if(!$hasError) {
      $response = $this->response(false, 'Items deleted successfully.');
    } else {
      if(!$hasModal) {
        $response = $this->response(true, 'Some items failed to delete');
      } else {
        if($hasModal === 'has_poi') {
          $hasModal = array('type' => 'has_poi', 'items' => $modalItems);
          $response = $this->response(null, 'Some categories have points of interest associated with them.', $hasModal);
        }
      }
    }

    return $response;
  }

  /**
   * Update settings
   *
   * @since 0.1
   *
   * @param array
   *
   * @return array
   */
  function updateSettings($settings)
  {
    $api_key = $_POST['api_key'];
    update_option('gj_maps_api_key', $api_key);

    $styles = isset($_POST['use_styles']);
    update_option('gj_maps_use_styles', $styles);

    $label_color = $_POST['label_color'];
    update_option('gj_maps_label_color', $label_color);

    $poi_list = isset($_POST['poi_list']);
    update_option('gj_maps_poi_list', $poi_list);

    $poi_alpha_list = isset($_POST['poi_alpha_list']);
    update_option('gj_maps_poi_alpha_list', $poi_alpha_list);

    $poi_num = isset($_POST['poi_num']);
    update_option('gj_maps_poi_num', $poi_num);

    $poi_filter_load = isset($_POST['poi_filter_load']);
    update_option('gj_poi_filter_load', $poi_filter_load);

    $disable_mouse_scroll = isset($_POST['disable_mouse_scroll']);
    update_option('gj_disable_mouse_scroll', $disable_mouse_scroll);

    $disable_mouse_drag = isset($_POST['disable_mouse_drag']);
    update_option('gj_disable_mouse_drag', $disable_mouse_drag);

    $enable_fit_bounds = isset($_POST['enable_fit_bounds']);
    update_option('gj_enable_fit_bounds', $enable_fit_bounds);

    $enable_phone_link = isset($_POST['enable_phone_link']);
    update_option('gj_enable_phone_link', $enable_phone_link);

    $cat_default = $_POST['cat_default'];
    update_option('gj_maps_cat_default', $cat_default);

    $center_lat = $_POST['center_lat'];
    update_option('gj_maps_center_lat', $center_lat);

    $center_lng = $_POST['center_lng'];
    update_option('gj_maps_center_lng', $center_lng);

    $map_zoom = $_POST['map_zoom'];
    update_option('gj_maps_map_zoom', $map_zoom);

    $max_zoom = $_POST['max_zoom'];
    update_option('gj_maps_max_zoom', $max_zoom);

    $link_text = $_POST['link_text'];
    update_option('gj_maps_link_text', $link_text);

    $map_styles = $_POST['map_styles'];
    update_option('gj_maps_map_styles', $map_styles);

    return $this->response(false, 'Settings updated successfully.');
  }

  /**
   * Returns the global maps settings
   *
   * @since 0.1
   *
   * @return object
   */
  public static function getSettings()
  {
    return (object) array(
      "api_key"              => get_option('gj_maps_api_key'),
      "use_styles"           => get_option('gj_maps_use_styles'),
      "label_color"          => get_option('gj_maps_label_color'),
      "poi_list"             => get_option('gj_maps_poi_list'),
      "poi_alpha_list"       => get_option('gj_maps_poi_alpha_list'),
      "poi_num"              => get_option('gj_maps_poi_num'),
      "poi_filter_load"      => get_option('gj_poi_filter_load'),
      "disable_mouse_scroll" => get_option('gj_disable_mouse_scroll'),
      "disable_mouse_drag"   => get_option('gj_disable_mouse_drag'),
      "enable_fit_bounds"    => get_option('gj_enable_fit_bounds'),
      "enable_phone_link"    => get_option('gj_enable_phone_link'),
      "cat_default"          => get_option('gj_maps_cat_default'),
      "center_lat"           => get_option('gj_maps_center_lat'),
      "center_lng"           => get_option('gj_maps_center_lng'),
      "map_zoom"             => get_option('gj_maps_map_zoom'),
      "max_zoom"             => get_option('gj_maps_max_zoom'),
      "link_text"            => get_option('gj_maps_link_text'),
      "map_styles"           => stripslashes(get_option('gj_maps_map_styles')),
    );
  }

  /**
  *
  * Import CSV
  *
  * Expects the uploaded file and a mapID
  *
  * @since 0.1
  *
  **/
  function importData($uploadedFile, $mapID)
  {

    $labels = array();
    $poi    = array();
    $value  = array();
    $unset  = array();

    // Create a new map if one does not exist
    if($mapID === 'new') {
      $this->db->createMap();
      $mapID = $this->db->maxMapID();
    }

    // Complete the upload of the CSV
    ini_set('auto_detect_line_endings',TRUE);

    if (($handle = fopen($uploadedFile['tmp_name'], "r")) !== FALSE) {
      while (($data = fgetcsv($handle, ",")) !== FALSE) {
        array_push($poi, $data);
      }
      fclose($handle);
    }

    // Handle our labels, make sure the CSV matches our reqs
    foreach ($poi[0] as $key=>$value) {
      $str = preg_replace("/[^\w\d]/","",strtolower($value));

      if(!empty($str)) {
        $labels[$str] = $str;
      } else {
        $unset[] = $key;
      }
    }

    $labels['lat'] = 'lat';
    $labels['lng'] = 'lng';

    foreach ($poi as $key=>$value) {
      foreach($unset as $destroy) {
        unset($value[$destroy]);
      }

      array_push($value, null);
      array_push($value, null);

      if (count($labels) == count($value)) {
        $poi[$key] = array_combine($labels, $value);
      } else {
        return $this->response(true, 'Check your CSV column headers.');
      }
    }

    // Unset the headers
    unset($poi[0]);

    // This parses through the data
    foreach ($poi as $key=>$value) {
      // Sets each category to an integer, creates categories if needed
      if(isset($value['category'])) {
        $categoryMatch = $this->db->getCategory($mapID, $value['category']);

        if(empty($categoryMatch)) {
          $newCategory = array(
            'map_id' => $mapID,
            'name'   => $value['category']
          );

          $id = $this->db->createCategory($newCategory);

          if($id > 0) {
            $categoryMatch = $this->db->getCategory($mapID, $value['category']);
            $poi[$key]['cat_id'] = (int) $categoryMatch[0]->id;
          } else {
            return $this->response(true, 'Category failed to be created.');
          }
        } else {
          $poi[$key]['cat_id'] = (int) $categoryMatch[0]->id;
        }
      } else {
        $newCategory = array(
          'map_id' => $mapID,
          'name'   => 'default'
        );

        $id = $this->db->createCategory($newCategory);

        if($id > 0) {
          $categoryMatch = $this->db->getCategory($mapID, 'default');
          if(isset($categoryMatch[0])) {
            $poi[$key]['cat_id'] = (int) $categoryMatch[0]->id;
          } else {
            return $this->response(true, 'Something went wrong retrieving the category.');
          }
        }
      }

      // Adds the map id to each of the rows
      $poi[$key]['map_id'] = $mapID;

      // Set our lat/lng to 0, it's too taxing to
      // upload an unknown sized csv and to geocode
      $poi[$key]['lat'] = 0;
      $poi[$key]['lng'] = 0;
    }

    $savePOI  = $this->db->createPoi($poi);

    return $this->response(false, 'CSV data successfully uploaded.');
  }

  /**
   * Bulk delete data from options
   *
   * @since 0.3
   *
   * @param array
   *
   * @return array
   */
  function deleteData($post)
  {
    if(isset($post['delete'])) {
      switch($post['delete']) {
        case('default'):
          $response = $this->response(true, 'You must select data to delete');
          break;
        case('delete_categories'):
          $dbResponse = $this->db->truncateCategories();
          break;
        case('delete_maps'):
          $dbResponse = $this->db->truncateMaps();
          break;
        case('delete_poi'):
          $dbResponse = $this->db->truncatePoi();
          break;
        case('delete_all'):
          $dbResponse = $this->db->deleteAllData();
          if($dbResponse['poi']) {
            $response = $this->response(false, 'All POI deleted along with '.$dbResponse['cat'].' categories and '.$dbResponse['maps'].' maps. So fresh.');
          }
          break;
        default:
          $response = $this->response(true, 'Something went horribly wrong.');
          break;
      }
    } else {
      $response = $this->response(true, 'You need to specify what to delete.');
    }
    return $response;
  }

}
