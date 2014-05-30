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


  /*
  * Options-Import Functions
  */

  function importData($post) {

    ini_set('auto_detect_line_endings',TRUE);

    $error = false;

    $uploadedfile = $_FILES['gj_upload'];

    if ($uploadedfile['name'] === '') {

      $response = $this->gjMapsMessaging('error', 'You must upload a CSV.');

    } else {

      $row = 1;
      $poi = array();

      if (($handle = fopen($uploadedfile['tmp_name'], "r")) !== FALSE) {

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

    } else {

      $response = $this->gjMapsMessaging('error', 'Data failed to delete');

    }

    return $response;

  }

}
