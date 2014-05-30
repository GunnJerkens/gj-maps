<?php

  $databaseFunctions = new gjMapsDB();



  /*
  * This is the maps tabbing system
  */

  $map_id = isset($_GET['map_id']) ? $_GET['map_id'] : '1';
  $map = $databaseFunctions->get_map();
  $map_key = $databaseFunctions->get_map_key($map_id, $map);
  $last_map = end($map)->id;

  var_dump($map_key);
  var_dump($map_id);

  var_dump($map);


  if ($map_id > $last_map) { //If map does not exist, add new map
    saveMap($map_id);
    $map = $GJ_Maps->get_map();
    $last_map = end($map)->id; // Use the maxMapID DB call here!
  }

  echo '<h2 class="nav-tab-wrapper">';

  foreach ($map as $key => $value) {

    echo '<a href="?page=gj_maps&map_id='.$value->id.'" class="nav-tab'.($map_id == $value->id ? ' nav-tab-active' : '').'">'.$value->name.'</a>';

  }

  echo '<a href="?page=gj_maps&map_id='.($last_map + 1).'" class="nav-tab">+</a>';
  echo '</h2>';

  /*
  * These calls are for retrieving the POI data for the table.
  */

  $poi = $databaseFunctions->get_poi($type='OBJECT', 'map_id=' . $map_id);
  $cat = $databaseFunctions->get_cat();

  // var_dump($poi);

  ?>

<div class="wrap">

  <h4>POI</h4>
  <form name="gj_maps_poi" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="form_name" value="gj_maps_poi">
    <table class="wp-list-table widefat fixed gj-maps">
      <thead class="">
        <tr>
          <th scope="col" id="cb" class="column-cb check-column">
            <input id="cb-select-all-1" type="checkbox">
          </th>
          <th><span>Name</span></th>
          <th><span>Category</span></th>
          <th><span>Address</span></th>
          <th><span>City</span></th>
          <th><span>State</span></th>
          <th><span>Zip</span></th>
          <th><span>Country</span></th>
          <th><span>Phone</span></th>
          <th><span>URL</span></th>
          <th><span>Latitude</span></th>
          <th><span>Longitude</span></th>
        </tr>
      </thead>
      <tbody><?php


      foreach ($poi as $point) { ?>

        <tr id="map-<?php echo $point->id; ?>" class="alternate poi" data-id="<?php echo $point->id; ?>">
          <input type="hidden" name="<?php echo $point->id; ?>[id]" value="<?php echo $point->id; ?>">
          <input type="hidden" class="mode" name="<?php echo $point->id; ?>[mode]" value="">
          <th class="check-column">
            <input type="checkbox" name="<?php echo $point->id; ?>[delete]">
          </th>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[name]" value="<?php echo $point->name; ?>"></td>
          <td>
            <select class="detect-change" name="<?php echo $point->id; ?>[cat_id]"><?php

              foreach ($cat as $key=>$value) {

                if ( $point->cat_id == $value->id ) {
                  echo "<option value='$value->id' selected>$value->name</option>";
                } else {
                  echo "<option value='$value->id'>$value->name</option>";
                }

              } ?>

            </select>
          </td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[address]" value="<?php echo $point->address; ?>"></td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[city]" value="<?php echo $point->city; ?>"></td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[state]" value="<?php echo $point->state; ?>"></td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[zip]" value="<?php echo $point->zip; ?>"></td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[country]" value="<?php echo $point->country; ?>"></td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[phone]" value="<?php echo $point->phone; ?>"></td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[url]" value="<?php echo $point->url; ?>"></td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[lat]" id="lat<?php echo $point->id; ?>" value="<?php echo $point->lat; ?>"></td>
          <td><input type="text" class="detect-change full-width" name="<?php echo $point->id; ?>[lng]" id="lng<?php echo $point->id; ?>" value="<?php echo $point->lng; ?>"></td><?php
      } ?>

      </tbody>
    </table>
  </form>

  <div class="gj-buttons">
    <div class="btn button table-button add-row">Add Row</div>
    <form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <input type="hidden" name="gj_hidden" value="Y"/>
      <input type="hidden" name="geocode" value="1"/>
      <div class="btn button table-button" type="submit">Find Geocodes</div>
    </form>
    <button class="btn button table-button" type="submit">Update Settings</button>
    <form name="gj_maps_map_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <input type="hidden" name="form_name" value="gj_maps_map_settings">
      <input type="text" name="name" placeholder="Map Name" value="<?php echo $map[$map_key]->name; ?>"/>
      <button type="submit" class="btn button">Change Map Name</button>
    </form>
  </div>

  <div class="tablenav bottom">
    <div class="tablenav-pages">
      <span class="displaying-num"><?php echo $pagination['total_items'].' items'; ?></span>
      <span class="pagination-links"><a class="first-page <?php echo $pagination['current_page'] - 1 > 0 ? '' : 'disabled'; ?>" title="Go to the first page" href="?page=gj_redirect&tab=gj_redirect_redirects&paged=1">«</a>
      <a class="prev-page <?php echo $pagination['current_page'] - 1 > 0 ? '' : 'disabled'; ?>" title="Go to the previous page" href="?page=gj_redirect&tab=gj_redirect_redirects&paged=<?php echo $pagination['current_page'] - 1 > 0 ? $pagination['current_page'] - 1 : $pagination['current_page']; ?>">‹</a>
      <span class="paging-input"><?php echo $pagination['current_page']; ?> of <span class="total-pages"><?php echo $pagination['pages'] == 0 ? '1' : $pagination['pages']; ?></span></span>
      <a class="next-page <?php echo $pagination['current_page'] + 1 > $pagination['pages'] ? 'disabled' : ''; ?>" title="Go to the next page" href="?page=gj_redirect&tab=gj_redirect_redirects&paged=<?php echo $pagination['current_page'] + 1 > $pagination['pages'] ? $pagination['current_page'] : $pagination['current_page'] + 1; ?>">›</a>
      <a class="last-page <?php echo $pagination['current_page'] + 1 > $pagination['pages'] ? 'disabled' : ''; ?>" title="Go to the last page" href="?page=gj_redirect&tab=gj_redirect_redirects&paged=<?php echo $pagination['pages']; ?>">»</a></span>
    </div>
  </div>

</div>