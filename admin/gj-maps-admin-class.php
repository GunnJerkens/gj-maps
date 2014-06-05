<?php

class gjMapsAdmin {

  private $databaseFunctions;

  function __construct() {

    $this->databaseFunctions = new gjMapsDB();

  }

  function gjMapsMessaging($status, $message) {

    $response = array (
      'status' => $status,
      'message' => $message
    );

    return $response;

  }

  /*
  * GJ-Maps Functions
  */

  function tabsMapID($getData) {

    if(isset($getData['map_id']) && $getData['map_id'] === 'new') {

      $maxMapID = $this->databaseFunctions->maxMapID();
      $maxMapID = ((int) $maxMapID[0]->max_id) + 1;

      $this->databaseFunctions->saveMap($maxMapID);
      $map_id = $maxMapID;

    } else if (isset($getData['map_id'])) {

      $map_id = $getData['map_id'];

    } else {

      $map_id = $this->databaseFunctions->minMapID();
      $map_id = $map_id[0]->low_id;

    }

    return $map_id;

  }

  function renameMap($post) {

    $response = $this->databaseFunctions->editMapSettings($post);

    if($response > 0) {

      $response = $this->gjMapsMessaging('success', 'Map name changed successfully');

    } else {

      $response = $this->gjMapsMessaging('error', 'Map name failed to update');

    }

    return $response;

  }

  function deletePOI($deleteItems) {

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

  function editPOI($post) {

    $editPOI = $this->databaseFunctions->editPOI($post);

    if($editPOI) {

      $this->gjMapsMessaging('success', 'Points of interest updated successfully.');

    } else {

      $this->gjMapsMessaging('error', 'Something went wrong during the update process.');

    }
  
  }

  function createPOI($poi) {

    foreach($poi as $singlePOI) {

      $address = urlencode($singlePOI["address"].', '.$singlePOI['city'].', '.$singlePOI['state'].' '.$singlePOI['zip']);
      $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
      $url .= '&address='.$address;

      $googleResponseEncoded = wp_remote_get($url);

      if(is_wp_error($googleResponseEncoded)) {

        // This is an error!

      }

      $googleResponse = json_decode($googleResponseEncoded['body']);
      if( $googleResponse === 'ZERO_RESULTS') {

        $singlePOI['lat'] = '0';
        $singlePOI['lng'] = '0';

        // This is an error!

      } else {

        $location = $response2->results[0]->geometry->location;
        $singlePOI['lat'] = $location->lat;
        $singlePOI['lng'] = $location->lng;

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

  function geocodePOI() {

    $query = $this->databaseFunctions->get_poi('ARRAY_A', 'lat=0');

    foreach ($query as $poi) {

      if ($poi['address'] && $poi['zip']) { // these two are most reliable, if you have them

        $address = urlencode($poi["address"].', '.$poi['zip']);

      } else {

        $address = urlencode($poi["address"].', '.$poi['city'].', '.$poi['state'].' '.$poi['zip']);

      }

      $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
      $url .= '&address='.$address;

      $googleResponseEncoded = wp_remote_get($url);

      if(is_wp_error($googleResponseEncoded)) {

        // This is an error!

      }


      $googleResponse = json_decode($googleResponseEncoded['body']);

      if( $googleResponse === 'ZERO_RESULTS') {

        $poi['lat'] = '0';
        $poi['lng'] = '0';

        // This is an error!

      } else {

        $location = $googleResponse->results[0]->geometry->location;
        $poi['lat'] = $location->lat;
        $poi['lng'] = $location->lng;

      }

      $updatePOI[] = $poi;

    }

    if(!empty($updatePOI)) {

      $response = $this->databaseFunctions->editPOI($updatePOI);

    } else {

      $response = $this->gjMapsMessaging('success', 'There were not points to update.');

    }

    return $response;

  }

  /*
  * Categories Functions
  */

  function editCat($updateItems) {

    foreach($updateItems as $item) {

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

  function deleteCat($deleteItems) {

    foreach($deleteItems as $item) {

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

  /*
  * Options-Settings Functions
  */

  function updateSettings($post) {

    $styles = isset($_POST['use_styles']);
    update_option('gj_maps_use_styles', $styles);

    $label_color = $_POST['label_color'];
    update_option('gj_maps_label_color', $label_color);

    $poi_list = isset($_POST['poi_list']);
    update_option('gj_maps_poi_list', $poi_list);

    $cat_default = $_POST['cat_default'];
    update_option('gj_maps_cat_default', $cat_default);

    $center_lat = $_POST['center_lat'];
    update_option('gj_maps_center_lat', $center_lat);

    $center_lng = $_POST['center_lng'];
    update_option('gj_maps_center_lng', $center_lng);

    $map_zoom = $_POST['map_zoom'];
    update_option('gj_maps_map_zoom', $map_zoom);

    $map_styles = $_POST['map_styles'];
    update_option('gj_maps_map_styles', $map_styles);

    $response = $this->gjMapsMessaging('success', 'Settings updated successfully.');

    return $response;

  }

  function getSettings() {

    $style = get_option('gj_maps_use_styles');
    $label_color = get_option('gj_maps_label_color');
    $poi_list = get_option('gj_maps_poi_list');
    $cat_default = get_option('gj_maps_cat_default');
    $center_lat = get_option('gj_maps_center_lat');
    $center_lng = get_option('gj_maps_center_lng');
    $map_zoom = get_option('gj_maps_map_zoom');
    $map_styles = get_option('gj_maps_map_styles');

    $map_styles_strip = stripslashes($map_styles);

    $settings = array(
      'use_styles' => $style,
      'label_color' => $label_color,
      'poi_list' => $poi_list,
      'cat_default' => $cat_default,
      'center_lat' => $center_lat,
      'center_lng' => $center_lng,
      'map_zoom' => $map_zoom,
      'map_styles' => $map_styles_strip
    );

    return $settings;

  }


  /*
  * Options-Import Functions
  */

  function importData($uploadedFile, $mapID) {

    // Create a new map
    if($mapID === 'new') {

      $maxID = $this->databaseFunctions->maxMapID();
      $maxID = (array) $maxID[0];

      if($maxID['MAX(id)'] != NULL) {
        $mapID = ((int) $maxID['MAX(id)']) + 1;
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

      $labels[$value] = $value;

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

          $id = $this->databaseFunctions->saveCat($newCategory);

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

        $id = $this->databaseFunctions->saveCat($newCategory);

        if($id > 0) {

          $categoryMatch = $this->databaseFunctions->getCatID('default', $mapID);

          $poi[$key]['cat_id'] = (int) $categoryMatch[0]->id;

        } else {

          // This is an error!

        }

      }


      // Adds the map idea to each of the rows
      $poi[$key]['map_id'] = $mapID;

      // Adding lat & lng via Google encode
      $address = urlencode($value["address"].', '.$value['city'].', '.$value['state'].' '.$value['zip']);
      $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
      $url .= '&address='.$address;

      $googleResponseEncoded = wp_remote_get($url);

      if(is_wp_error($googleResponseEncoded)) {

        // This is an error!

      }

      $googleResponse = json_decode($googleResponseEncoded['body']);

      if(isset($googleResponse->results[0])){

        $location = $googleResponse->results[0]->geometry->location;
        $poi[$key]['lat'] = $location->lat;
        $poi[$key]['lng'] = $location->lng;

      } else {

        $poi[$key]['lat'] = 0;
        $poi[$key]['lng'] = 0;

      }


    }

    $savePOI = $this->databaseFunctions->savePOI($poi);

    var_dump($poi);
    echo '<br>';
    var_dump($savePOI);



    // return $response;


    // if(!error) {
    //   $response = $this->gjMapsMessaging('error', 'An error was encountered during upload.');
    // } else {
    //   $response = $this->gjMapsMessaging('success', 'CSV uploaded successfully.');
    // }


  }

  /*
  * Options-Delete Functions
  */

  function deleteData($post) {

    if($post['delete'] === 'default') {

      $response = $this->gjMapsMessaging('error', 'You must select data to delete');
      $dbResponse = NULL;

    } else if ($post['delete'] === 'delete_categories') {

      $dbResponse = $this->databaseFunctions->deleteAllCat();

    } else if ($post['delete'] === 'delete_maps') {

      $dbResponse = $this->databaseFunctions->deleteAllMaps();

    } else if ($post['delete'] === 'delete_poi') {

      $dbResponse = $this->databaseFunctions->deleteAllPOI();

    } else if ($post['delete'] === 'delete_all') {

      $dbResponse = $this->databaseFunctions->deleteAllData();

    } else {

      $response = $this->gjMapsMessaging('error', 'Something went horribly wrong.');
      $dbResponse = NULL;

    }

    if($dbResponse !== NULL && $dbResponse !== false) {

      $response = $this->gjMapsMessaging('success', $dbResponse);

    }

    return $response;

  }

}
