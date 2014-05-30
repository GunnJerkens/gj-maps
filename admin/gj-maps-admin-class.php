<?php

class gjMapsAdmin {

  function gjMapsMessaging($status, $message) {

    $response = array (
      'status' => $status,
      'message' => $message
    );

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

    ini_set('auto_detect_line_endings',TRUE);

    $error = false;
    $row = 1;
    $poi = array();

    if (($handle = fopen($uploadedFile['tmp_name'], "r")) !== FALSE) {

      while (($data = fgetcsv($handle, ",")) !== FALSE) {

        array_push($poi, $data);

      }

      fclose($handle);
    }

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

        $error = true;

      }

    }

    unset($poi[0]);

    $databaseFunctions = new gjMapsDB();
    $cat = $databaseFunctions->get_cat('ARRAY_A');
    $cats = array();

    foreach ($cat as $key=>$value) {

      $cats[$value['name']] = new stdClass;
      $cats[$value['name']]->id = $value['id']; 
      $cats[$value['name']]->color = $value['color'];

    }

    $cat = (object) $cats;

    foreach ($poi as $key=>$value) {

      $address = urlencode($value["address"].', '.$value['city'].', '.$value['state'].' '.$value['zip']);
      $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
      $url .= '&address='.$address;

      $response = wp_remote_get( $url );

      if( is_wp_error( $response ) ) {

        $error_message = $response->get_error_message();
        $response = $this->gjMapsMessaging('error', $error_message);

      } 

      echo $value['address'].' -- '.$value['name'].'<br />';
      $response2 = json_decode($response['body']);

      if(isset($response2->results[0])){

        $location = $response2->results[0]->geometry->location;
        $poi[$key]['lat'] = $location->lat;
        $poi[$key]['lng'] = $location->lng;

      } else {

        $poi[$key]['lat'] = 0;
        $poi[$key]['lng'] = 0;

      }

      if (isset($cats[$value['category']])) {

        $poi[$key]['cat_id'] = $cats[$value['category']]->id;

      } else {

        $poi[$key]['cat_id'] = 1;

      }

      unset($poi[$key]['category']);

    }

    $response = $databaseFunctions->savePOI($poi);
    $error = $response;

    if(!error) {
      $response = $this->gjMapsMessaging('error', 'An error was encountered during upload.');
    } else {
      $response = $this->gjMapsMessaging('success', 'CSV uploaded successfully.');
    }



    return $response;

  }

  /*
  * Options-Delete Functions
  */

  function deleteData($post) {

    $databaseFunctions = new gjMapsDB();
    

    if($post['delete'] === 'default') {

      $response = $this->gjMapsMessaging('error', 'You must select data to delete');
      $dbResponse = NULL;

    } else if ($post['delete'] === 'delete_categories') {

      $dbResponse = $databaseFunctions->deleteAllCat();

    } else if ($post['delete'] === 'delete_maps') {

      $dbResponse = $databaseFunctions->deleteAllMaps();

    } else if ($post['delete'] === 'delete_poi') {

      $dbResponse = $databaseFunctions->deleteAllPOI();

    } else if ($post['delete'] === 'delete_all') {

      $dbResponse = $databaseFunctions->deleteAllData();

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
