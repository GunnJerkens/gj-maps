  <?php

  global $GJ_Maps;

  require_once('db.php');

  $map_id = isset($_GET['map_id']) ? $_GET['map_id'] : '1';

  if(isset($_POST['gj_hidden']) && $_POST['gj_hidden'] == 'Y') {
    //Form data sent
    global $post;

    if (isset($_POST['id']) && $_POST['id']) {

      if (isset($_POST['delete'])) {
        //Delete Selected POI
        deletePOI($_POST['id']);
      } else {
        //Update existing POI
        $poi = array();
        foreach ($_POST as $key=>$value) {
          if ($key !== 'gj_hidden') {
            $poi[$key] = stripslashes($value);
          }
        }
        editPOI($poi);
      }

    } else if (isset($_POST['geocode'])) {
      //Update geocodes
      global $wpdb;

      $query = $GJ_Maps->get_poi('ARRAY_A', 'lat=0');

      foreach ($query as $poi) {
        if ($poi['address'] && $poi['zip']) { // these two are most reliable, if you have them
          $address = urlencode($poi["address"].', '.$poi['zip']);
        } else {
          $address = urlencode($poi["address"].', '.$poi['city'].', '.$poi['state'].' '.$poi['zip']);
        }
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
        $url .= '&address='.$address;

        $response = wp_remote_get( $url );
        if( is_wp_error( $response ) ) {
          $error_message = $response->get_error_message();
          echo "Something went wrong: $error_message";
        }

        $response2 = json_decode($response['body']);
        if( $response2 = 'ZERO_RESULTS') {
          echo "Error: Google Maps returned no results for ".$poi['name'].". You will need to add the Lat/Long manually.<br />";
          $poi ['lat'] = '0';
          $poi ['lng'] = '0';
        } else {
          $location = $response2->results[0]->geometry->location;
          $poi['lat'] = $location->lat;
          $poi['lng'] = $location->lng;
        }
        editPOI($poi);
      }

    } else if (isset($_POST['map_settings'])) {
      $ms = array();

      $ms['id'] = $map_id;
      $ms['name'] = $_POST['name'];
      $ms['c_lat'] = $_POST['c_lat'];
      $ms['c_lng'] = $_POST['c_lng'];
      $ms['m_zoom'] = $_POST['m_zoom'];
      editMapSettings($ms);
    } else {
      //Add new POI
      $poi = array();
      foreach ($_POST as $key=>$value) {
        if ($key !== 'gj_hidden') {
          $poi[$key] = $value;
        }
      }

      $address = urlencode($poi["address"].', '.$poi['city'].', '.$poi['state'].' '.$poi['zip']);
      $url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
      $url .= '&address='.$address;

      $response = wp_remote_get( $url );
      if( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
      }

      $response2 = json_decode($response['body']);
      if( $response2 = 'ZERO_RESULTS') {
        echo "Error: Google Maps returned no results for ".$poi['name'].". You will need to add the Lat/Long manually.<br />";
        $poi ['lat'] = '0';
        $poi ['lng'] = '0';
      } else {
        $location = $response2->results[0]->geometry->location;
        $poi['lat'] = $location->lat;
        $poi['lng'] = $location->lng;
      }

      $poi ['map_id'] = $map_id;
      $POIs = array($poi);
      savePOI($POIs);
    }

  }

  $map = $GJ_Maps->get_map();
  $last_map = end($map)->id;
  $map_key = $GJ_Maps->get_map_key($map_id, $map);

  $poi = $GJ_Maps->get_poi($type='OBJECT', 'map_id=' . $map_id);
  $cat = $GJ_Maps->get_cat();

  if ($map_id > $last_map) { //If map does not exist, add new map
    saveMap($map_id);
    $map = $GJ_Maps->get_map();
    $last_map = end($map)->id;
  } ?>

  <h2 class="nav-tab-wrapper">

    <?php
    foreach ($map as $key => $value) {
    ?>
    <a href="?page=gj_maps&map_id=<?php echo $value->id; ?>" class="nav-tab<?php echo $map_id == $value->id ? ' nav-tab-active' : ''; ?>"><?php echo $value->name; ?></a>
    <?php
    }
    ?>
    <a href="?page=gj_maps&map_id=<?php echo $last_map + 1; ?>" class="nav-tab">+</a>

  </h2>

    <div class="wrap">

      <?php echo '<h2>'.$map[$map_key]->name.'</h2>'; ?>

      <h4>Settings</h4>
        <form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
          <input type="hidden" name="gj_hidden" value="Y"/>
          <input type="hidden" name="map_settings" value="1"/>
          <input type="text" name="name" placeholder="Map Name" value="<?php echo $map[$map_key]->name; ?>"/>
          <input type="text" name="c_lat" placeholder="Center Latitude" value="<?php echo $map[$map_key]->c_lat; ?>"/>
          <input type="text" name="c_lng" placeholder="Center Longitude" value="<?php echo $map[$map_key]->c_lng; ?>"/>
          <input type="text" name="m_zoom" placeholder="Map Zoom" value="<?php echo $map[$map_key]->m_zoom; ?>"/>

          <p class="submit"><input type="submit" value="<?php echo 'Update Settings'; ?>" /></p>
        </form>

      <?php    echo "<h2>" . __( 'GJ Maps - Points Of Interest', 'gj_trdom' ) . "</h2>"; ?>

      <h4>Add New</h4>
        <form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
          <input type="hidden" name="gj_hidden" value="Y"/>
          <input type="text" name="name" placeholder="Name"/>
          <select name="cat_id">
            <?php 
            foreach ($cat as $key=>$value) {
              echo "<option value='$value->id'>$value->name</option>";
            }

            ?>
          </select>
          <input type="text" name="address" placeholder="Street Address"/>
          <input type="text" name="city" placeholder="City"/>
          <input type="text" name="state" placeholder="State"/>
          <input type="text" name="zip" placeholder="Zip/Postal Code"/>
          <input type="text" name="country" placeholder="Country"/>
          <input type="text" name="phone" placeholder="Phone Number"/>
          <input type="text" name="url" placeholder="URL"/>

          <p class="submit"><input type="submit" value="<?php _e('Add POI', 'gj_trdom' ) ?>" /></p>
        </form>

      <h4>Find Geocodes</h4>
        <form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
          <input type="hidden" name="gj_hidden" value="Y"/>
          <input type="hidden" name="geocode" value="1"/>
          <p class="submit"><input type="submit" value="<?php _e('Find Geocodes', 'gj_trdom' ) ?>" /></p>
        </form>

      <h4>Edit POIs</h4>

        <?php

        foreach ($poi as $index=>$object) {
        ?>
          <form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
          <input type="hidden" name="gj_hidden" value="Y"/>
          <input type="hidden" name="id" value="<?php echo $object->id; ?>"/>

          <label for="name">Name: 
          <input type="text" name="name" placeholder="Name" value="<?php echo $object->name; ?>"/>
          </label>

          <label for="cat_id">Category: 
          <select name="cat_id">
            <?php 
            foreach ($cat as $key=>$value) {
              
              if ( $object->cat_id == $value->id ) {
                echo "<option value='$value->id' selected>$value->name</option>";
              } else {
                echo "<option value='$value->id'>$value->name</option>";
              }
            }

            ?>
          </select>
          </label>

          <label for="address">Street Address: 
          <input type="text" name="address" placeholder="Street Address" value="<?php echo $object->address; ?>"/>
          </label>

          <label for="city">City: 
          <input type="text" name="city" placeholder="City" value="<?php echo $object->city; ?>"/>
          </label>

          <label for="state">State: 
          <input type="text" name="state" placeholder="State" value="<?php echo $object->state; ?>"/>
          </label>

          <label for="zip">Zip/Postal Code: 
          <input type="text" name="zip" placeholder="Zip/Postal Code" value="<?php echo $object->zip; ?>"/>
          </label>

          <label for="country">Country: 
          <input type="text" name="country" placeholder="Country" value="<?php echo $object->country; ?>"/>
          </label>

          <label for="phone">Phone Number: 
          <input type="text" name="phone" placeholder="Phone Number" value="<?php echo $object->phone; ?>"/>
          </label>

          <label for="url">URL: 
          <input type="text" name="url" placeholder="URL" value="<?php echo $object->url; ?>"/>
          </label>

          <label for="lat">Latitude: 
          <input type="text" name="lat" placeholder="Latitude" id="lat<?php echo $object->id; ?>" value="<?php echo $object->lat; ?>"/>
          </label>

          <label for="lng">Longitude: 
          <input type="text" name="lng" placeholder="Longitude" id="lng<?php echo $object->id; ?>" value="<?php echo $object->lng; ?>"/>
          </label>

          <br />

          <label for="delete">Delete this POI? : 
          <input type="checkbox" name="delete"/>
          </label>

          <br />
          <input type="submit" name="Submit" value="<?php _e('Submit Changes', 'gj_trdom' ) ?>" />

          </form>

        <br /><hr /><br />
        <?php } ?>

    </div>
