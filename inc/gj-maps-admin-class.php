<?php

class gjMapsAdmin {

  private $databaseFunctions;

  function __construct() {

    $this->databaseFunctions = new gjMapsDB();

  }

  /**
  * 
  * Default messaging for the admin class.
  *
  * Requires status and message as strings, returns an array.
  * 
  * @since 0.3
  *
  **/

  function gjMapsMessaging($status, $message) {

    $response = array (
      'status' => $status,
      'message' => $message
    );

    return $response;

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

  function gjMapsPaginateTable($map_id, $showItems) {

    $count = $this->databaseFunctions->countRows();
    $totalItems = 0;

    foreach($count as $map) {

      $totalItems = 0;

      if($map->map_id === $map_id) {

        $map = (array) $map;
        $totalItems = (int) $map{'COUNT(*)'};
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
      'show_items' => $showItems,
      'total_items' => $totalItems,
      'pages' => $pages,
      'current_page' => $currentPage,
      'sql_offset' => $sqlOffset,
      'sql_length' => $showItems
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

  function gjMapsBuildURL($map_id = NULL) {

    $base = '?page=gj_maps';

    if($map_id != NULL || $map_id !== 'new') {

      $base .= '&map_id='.$map_id; 

    }

    return $base;

  }

  /**
  *
  * Creates the tabs on maps and categories pages.
  * 
  * Requries page, map and map_id, returns a string.
  *
  * @since 0.3
  *
  **/

  function mapsTab($page, $map, $map_id) {

    $page === 'cat' ? $page = 'gj_maps_categories' : $page = 'gj_maps';

    $tabs = '<h2 class="nav-tab-wrapper">';

    foreach ($map as $key => $value) {

      $tabs .= '<a href="?page='.$page.'&map_id='.$value->id.'" class="nav-tab '.($map_id === $value->id ? 'nav-tab-active' : '').'">'.$value->name.'</a>';

      if($value->id === $map_id) {
        $map_name = $value->name;
      }

    }

    if(!isset($map_name) && isset($map[0])) {
      $map_name = $map[0];
      $map_name = $map_name->name;
    }

    $tabs .= '<a href="?page='.$page.'&map_id=new" class="nav-tab">+</a>';

    $tabs .= '</h2>';

    return $tabs;

  }

  /**
  *
  * Return the map_id on maps and cats.
  * 
  * Requires the $_GET.
  *
  * @since 0.3
  *
  **/

  function tabsMapID($get) {

    if(isset($get['map_id']) && $get['map_id'] === 'new') {

      $maxMapID = $this->databaseFunctions->maxMapID();
      $maxMapID = ((int) $maxMapID[0]->max_id) + 1;

      $this->databaseFunctions->saveMap($maxMapID);
      $map_id = $maxMapID;

    } else if (isset($get['map_id'])) {

      $map_id = $get['map_id'];

    } else {

      $map_id = $this->databaseFunctions->minMapID();
      $map_id = $map_id[0]->low_id;

    }

    return $map_id;

  }

  /**
  *
  * Delete Map
  * 
  * Requires the map_id, returns the response array
  *
  * @since 0.3
  *
  **/

  function deleteMap($map_id) {

    $responsePOI = $this->databaseFunctions->deleteMapPOI($map_id);
    $responseCat = $this->databaseFunctions->deleteMapCat($map_id);
    $responseMap = $this->databaseFunctions->deleteMap($map_id);

    if($responsePOI === false || $responseCat === false || $responseMap === false) {

      $response = $this->gjMapsMessaging('error', 'Something went wrong during the delete process.');

    } else {

      $response = $this->gjMapsMessaging('success', 'Map '.$map_id.' was successfully deleted.');

    }

    return $response;

  }

  /**
  *
  * Rename the map.
  * 
  * Requires the $_POST.
  *
  * @since 0.3
  *
  **/

  function renameMap($post) {

    $response = $this->databaseFunctions->editMapSettings($post);

    if($response > 0) {

      $response = $this->gjMapsMessaging('success', 'Map name changed successfully');

    } else {

      $response = $this->gjMapsMessaging('error', 'Map name failed to update');

    }

    return $response;

  }

  /**
  *
  * Geocode the POI
  *
  * Uses usleep to geocode at a rate of 8/s, under the Google API limit of 10/s
  * 
  * Requires the map_id, returns a standard $response
  *
  * @since 0.1
  *
  **/

  function geocodePOI($map_id) {

    $query = $this->databaseFunctions->get_poi('ARRAY_A', $map_id, 'lat = 0');

    foreach($query as $poi) {

      if($poi['address'] && $poi['zip']) {

        $address = urlencode($poi["address"].', '.$poi['zip']);

      } else {

        $address = urlencode($poi["address"].', '.$poi['city'].', '.$poi['state'].' '.$poi['zip']);

      }

      $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
      $url .= '&address='.$address;

      $googleResponseEncoded = wp_remote_get($url);

      if(!is_wp_error($googleResponseEncoded)) {

        $googleResponse = json_decode($googleResponseEncoded['body']);

        if($googleResponse === 'ZERO_RESULTS') {

          $poi['lat'] = '0';
          $poi['lng'] = '0';

        } else {

          $location = $googleResponse->results[0]->geometry->location;
          $poi['lat'] = $location->lat;
          $poi['lng'] = $location->lng;

        }

        $updatePOI[] = $poi;

      }

      usleep(125000);

    }

    if(!empty($updatePOI)) {

      $this->databaseFunctions->editPOI($updatePOI);

      $response = $this->gjMapsMessaging('success', 'Updated coordinates successfully');

    } else {

      $response = $this->gjMapsMessaging('success', 'There were not points to update.');

    }

    return $response;

  }

  /**
  *
  * Create the POI
  * 
  * Requires the $poi, an array of poi data
  *
  * @since 0.1
  *
  **/

  function createPOI($poi) {

    foreach($poi as $singlePOI) {

      $defaultCatExists = false;

      if(!isset($singlePOI['cat_id']) && $defaultCatExists === false) {

        $cat = array (
          'map_id' => $singlePOI['map_id'],
          'name' => 'Default',
          'color' => '#000000',
          'icon' => NULL
        );

        $dbResponse = $this->databaseFunctions->createCat($cat);

        if($dbResponse === 1) {

          $dbResponse = $this->databaseFunctions->getCatID('Default', $singlePOI['map_id']);
          $singlePOI['cat_id'] = $dbResponse[0]->id;

          $defaultCatExists = true;

        }

      }

      $createItems[] = $singlePOI;

    }

    $response = $this->databaseFunctions->createPOI($createItems);

    if($response === 1) {

      $response = $this->gjMapsMessaging('success', 'Successfully created new points of interest.');

    } else {

      $response = $this->gjMapsMessaging('error', 'Failed to create points of interest.');

    }

    return $response;

  }

  /**
  *
  * Edit the POI
  * 
  * Requires the $_POST, the array of POI data
  *
  * @since 0.1
  *
  **/

  function editPOI($post) {

    $editPOI = $this->databaseFunctions->editPOI($post);

    if($editPOI) {

      $this->gjMapsMessaging('success', 'Points of interest updated successfully.');

    } else {

      $this->gjMapsMessaging('error', 'Something went wrong during the update process.');

    }
  
  }

  /**
  *
  * Delete the POI
  * 
  * Requires an array of POI 'id' to delete
  *
  * @since 0.3
  *
  **/

  function deletePOI($deleteItems) {

    $hasError = false;

    foreach($deleteItems as $item) {

      $responses[] = $this->databaseFunctions->deletePOI($item['id']);

    }

    foreach($responses as $response) {

      if($response !== 1) {

        $hasError = true;

      }

    }

    if(!$hasError) {

      $response = $this->gjMapsMessaging('success', 'Items deleted successfully.');

    } else {

      $response = $this->gjMapsMessaging('error', 'Some items failed to delete');

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

  function createCat($createItems) {

    $hasError = false;

    foreach($createItems as $item) {

      unset($item['mode']);

      $response[] = $this->databaseFunctions->createCat($item);

    }

    foreach($response as $response) {

      if($response !== 1) {

        $hasError = true;

      }

    }

    if(!$hasError) {

      $response = $this->gjMapsMessaging('success', 'Categories created successfully.');

    } else {

      $response = $this->gjMapsMessaging('error', 'Categories failed to be created.');

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

  function editCat($updateItems) {

    $hasError = false;

    foreach($updateItems as $item) {

      unset($item['mode']);

      $responses[] = $this->databaseFunctions->editCat($item);

    }

    foreach($responses as $response) {

      if($response !== 1) {

        $hasError = true;

      }

    }

    if(!$hasError) {

      $response = $this->gjMapsMessaging('success', 'Categories updated successfully.');

    } else {

      $response = $this->gjMapsMessaging('error', 'Categories failed to update.');

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

  function deleteCat($deleteItems) {

    $hasError = false;

    foreach($deleteItems as $item) {

      unset($item['delete']);

      $responses[] = $this->databaseFunctions->deleteCat($item['id']);

    }

    foreach($responses as $response) {

      if($response !== 1) {

        $hasError = true;

      }

    }

    if(!$hasError) {

      $response = $this->gjMapsMessaging('success', 'Items deleted successfully.');

    } else {

      $response = $this->gjMapsMessaging('error', 'Some items failed to delete');

    }

    return $response;

  }

  /**
  *
  * Update settings
  * 
  * Expects the post object, returns a response array
  *
  * @since 0.1
  *
  **/

  function updateSettings($post) {

    $styles = isset($_POST['use_styles']);
    update_option('gj_maps_use_styles', $styles);

    $label_color = $_POST['label_color'];
    update_option('gj_maps_label_color', $label_color);

    $poi_list = isset($_POST['poi_list']);
    update_option('gj_maps_poi_list', $poi_list);

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

    $map_styles = $_POST['map_styles'];
    update_option('gj_maps_map_styles', $map_styles);

    $response = $this->gjMapsMessaging('success', 'Settings updated successfully.');

    return $response;

  }

  /**
   * Returns the global maps settings
   * 
   * @return object
   *
   * @since 0.1
   */
  public static function getSettings() {

    $settings = new StdClass();

    $settings->use_styles           = get_option('gj_maps_use_styles');
    $settings->label_color          = get_option('gj_maps_label_color');
    $settings->poi_list             = get_option('gj_maps_poi_list');
    $settings->poi_num              = get_option('gj_maps_poi_num');
    $settings->poi_filter_load      = get_option('gj_poi_filter_load');
    $settings->disable_mouse_scroll = get_option('gj_disable_mouse_scroll');
    $settings->disable_mouse_drag   = get_option('gj_disable_mouse_drag');
    $settings->enable_fit_bounds    = get_option('gj_enable_fit_bounds');
    $settings->cat_default          = get_option('gj_maps_cat_default');
    $settings->center_lat           = get_option('gj_maps_center_lat');
    $settings->center_lng           = get_option('gj_maps_center_lng');
    $settings->map_zoom             = get_option('gj_maps_map_zoom');
    $settings->max_zoom             = get_option('gj_maps_max_zoom');
    $settings->map_styles           = stripslashes(get_option('gj_maps_map_styles'));

    return $settings;

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

  function importData($uploadedFile, $mapID) {

    if($mapID === 'new') {

      $maxID = $this->databaseFunctions->maxMapID();
      $maxID = ((int) $maxID[0]->max_id);

      if($maxID != NULL) {

        $mapID = $maxID + 1;

      } else {

        $mapID = 1;

      }

      $this->databaseFunctions->saveMap($mapID);

    }

    // Complete the upload of the CSV
    ini_set('auto_detect_line_endings',TRUE);

    $poi = array();

    if (($handle = fopen($uploadedFile['tmp_name'], "r")) !== FALSE) {

      while (($data = fgetcsv($handle, ",")) !== FALSE) {

        array_push($poi, $data);

      }

      fclose($handle);
    }

    // Handle our labels, make sure the CSV matches our reqs
    $labels = array();

    foreach ($poi[0] as $key=>$value) {

      $labels[$value] = trim(strtolower($value));

    }

    $labels['lat'] = 'lat';
    $labels['lng'] = 'lng';


    foreach ($poi as $key=>$value) {

      array_push($value, null);
      array_push($value, null);

      if (count($labels) == count($value)) {

        $poi[$key] = array_combine($labels, $value);

      } else {

        $response = $this->gjMapsMessaging('error', 'Check your CSV column headers.');
        return $response;

      }

    }

    // Unset the headers
    unset($poi[0]);

    // This parses through the data
    foreach ($poi as $key=>$value) {

      // Sets each category to an integer, creates categories if needed
      if(isset($value['category'])) {

        $categoryMatch = $this->databaseFunctions->getCatID($value['category'], $mapID);

        if(empty($categoryMatch)) {

          $newCategory = array(
            'map_id' => $mapID,
            'name' => $value['category']
          );

          $id = $this->databaseFunctions->createCat($newCategory);

          if($id > 0) {

            $categoryMatch = $this->databaseFunctions->getCatID($value['category'], $mapID);

            $poi[$key]['cat_id'] = (int) $categoryMatch[0]->id;

          } else {

            // This is an error!

          }

        } else {

          $poi[$key]['cat_id'] = (int) $categoryMatch[0]->id;

        }

      } else {

        $newCategory = array(
          'map_id' => $mapID,
          'name' => 'default'
        );

        $id = $this->databaseFunctions->createCat($newCategory);

        if($id > 0) {

          $categoryMatch = $this->databaseFunctions->getCatID('default', $mapID);

          $poi[$key]['cat_id'] = (int) $categoryMatch[0]->id;

        }

      }


      // Adds the map id to each of the rows
      $poi[$key]['map_id'] = $mapID;

      // Set our lat/lng to 0, it's too taxing to 
      // upload an unknown sized csv and to geocode
      $poi[$key]['lat'] = 0;
      $poi[$key]['lng'] = 0;

    }

    $savePOI = $this->databaseFunctions->createPOI($poi);

    $response = $this->gjMapsMessaging('success', 'CSV data successfully uploaded.');

    return $response;

  }

  /**
  *
  * Bulk delete data
  * 
  * Requires the post, returns the response array
  *
  * @since 0.3
  *
  **/

  function deleteData($post) {

    if($post['delete'] === 'default') {

      $response = $this->gjMapsMessaging('error', 'You must select data to delete');

    } else if ($post['delete'] === 'delete_categories') {

      $dbResponse = $this->databaseFunctions->deleteAllCat();

    } else if ($post['delete'] === 'delete_maps') {

      $dbResponse = $this->databaseFunctions->deleteAllMaps();

    } else if ($post['delete'] === 'delete_poi') {

      $dbResponse = $this->databaseFunctions->deleteAllPOI();

    } else if ($post['delete'] === 'delete_all') {

      $dbResponse = $this->databaseFunctions->deleteAllData();

      if($dbResponse['poi']) {
        $response = $this->gjMapsMessaging('success', 'All POI deleted along with '.$dbResponse['cat'].' categories and '.$dbResponse['maps'].' maps. So fresh.');
      }

    } else {

      $response = $this->gjMapsMessaging('error', 'Something went horribly wrong.');

    }

    return $response;

  }

}
