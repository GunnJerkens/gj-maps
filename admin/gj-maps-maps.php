<?php

/**
 * Contains the markup for the maps and the basic controller logic
 *
 * @TODO: Move controller logic into a controller
 */


$db = new gjMapsDB();
$ad = new gjMapsAdmin();

/**
 * Delete a map
 */
if(isset($_GET['delete'])) {
  $map_id   = (int) $_GET['delete'];
  $response = $ad->deleteMap($map_id);
}

/**
 * Create a map else set our map ID
 */
if(isset($_GET['map_id']) && $_GET['map_id'] === "new") {
  $map_id = $ad->createMap();
} elseif (!isset($_GET['map_id'])) {
  $map_id = $db->minMapId();
} else {
  $map_id = (int) $_GET['map_id'];
}

/**
 * Handle POST items
 */
if(!empty($_POST)) {
  if(1 !== check_admin_referer('gj-maps-poi')) {
    die('Permission denied');
  }

  if($_POST['form_name'] === 'gj_maps_map_name') {
    $response = $ad->renameMap($_POST);
  }

  if($_POST['form_name'] === 'geocode') {
    $response = $ad->geocodePOI($map_id);
  }

  if($_POST['form_name'] === 'gj_maps_poi' ) {

    foreach($_POST as $post) {
      if(isset($post['delete']) && $post['delete'] === 'on') {
        $deleteItems[] = $post;
      } elseif(isset($post['mode']) && $post['mode'] === 'update') {
        $updateItems[] = $post;
      } elseif(isset($post['mode']) && $post['mode'] === 'create') {
        $createItems[] = $post;
      }
    }

    if(!empty($deleteItems)) {
      $response = $ad->deletePOI($deleteItems);
    }

    if(!empty($updateItems)) {
      $response = $ad->editPOI($updateItems);
    }

    if(!empty($createItems)) {
      $response = $ad->createPOI($createItems);
    }

  }
}

$maps = $db->getMaps();
$map  = $db->getMap($map_id);
$poi  = false;
$cat  = false;

if(isset($map[0])) {
  $map = $map[0];
  $pag = $ad->gjMapsPaginateTable($map_id, 30);
  $poi = $db->getPoi($map_id, $pag['sql_offset'], $pag['sql_length']);
  $cat = $db->getCategories($map_id);

  wp_localize_script('gj_maps_admin_js', 'cat', $cat);
  wp_localize_script('gj_maps_admin_js', 'map', array('id' => $map_id));
}

/*
* This is our response messaging
*/
if(isset($response) && isset($response['error'])) {
  if($response['error']) {
    echo '<div id="message" class="error"><p>'.$response['message'].'</p></div>';
  } else {
    echo '<div id="message" class="updated"><p>'.$response['message'].'</p></div>';
  }
}

echo $ad->mapsTab('poi', $maps, $map);

/*
* Sets up the pagination && urls
*/
$url = $ad->gjMapsBuildURL($map_id); ?>


<div class="wrap">
  <form name="gj_maps_geocode" class="top-form" method="post">
    <input type="hidden" name="form_name" value="geocode">
    <input type="hidden" name="map_id" value="<?php echo $map_id; ?>">
    <?php wp_nonce_field('gj-maps-poi'); ?>
    <button type="submit" class="btn button">Find Geocodes</button>
  </form>

  <form name="gj_maps_map_name" class="top-form" method="post">
    <input type="hidden" name="form_name" value="gj_maps_map_name">
    <input type="hidden" name="map_id" value="<?php echo $map_id; ?>">
    <?php wp_nonce_field('gj-maps-poi'); ?>
    <input type="text" name="name" placeholder="Map Name" value="<?php echo !empty($map->name) ? $map->name : ''; ?>"/>
    <button type="submit" class="btn button">Change Map Name</button>
  </form>
  <a href="?page=gj_maps&delete=<?php echo $map_id; ?>" id="delete">Delete Map</a>

  <form name="gj_maps_poi" method="post">
    <input type="hidden" name="form_name" value="gj_maps_poi">
    <input type="hidden" name="map_id" value="<?php echo $map_id; ?>">
    <?php wp_nonce_field('gj-maps-poi'); ?>

    <div id="gj-table-container">
      <table class="wp-list-table widefat fixed gj-maps">
        <thead class="">
          <tr>
            <th scope="col" id="cb" class="column-cb check-column">
              <input id="cb-select-all-1" type="checkbox">
            </th>
            <th class="th-name"><span>Name</span></th>
            <th class="th-category"><span>Category</span></th>
            <th data-column="address" class="th-header active"><span>Address</span></th>
            <th data-column="city" class="th-header"><span>City</span></th>
            <th data-column="state" class="th-header"><span>State</span></th>
            <th data-column="zip" class="th-header"><span>Zip</span></th>
            <th data-column="country" class="th-header"><span>Country</span></th>
            <th data-column="phone" class="th-header"><span>Phone</span></th>
            <th data-column="url" class="th-header"><span>URL</span></th>
            <th data-column="latitude" class="th-header"><span>Latitude</span></th>
            <th data-column="longitude" class="th-header"><span>Longitude</span></th>
          </tr>
        </thead>
        <tbody><?php

          if($poi && sizeof($poi) > 0) {
            foreach ($poi as $point) { ?>

              <tr id="map-<?php echo $point->id; ?>" class="alternate poi" data-id="<?php echo $point->id; ?>" data-map="<?php echo $map_id; ?>">
                <input type="hidden" name="<?php echo $point->id; ?>[id]" value="<?php echo $point->id; ?>">
                <input type="hidden" name="<?php echo $point->id; ?>[map_id]" value="<?php echo $map_id; ?>">
                <input type="hidden" class="mode" name="<?php echo $point->id; ?>[mode]" value="">
                <th class="check-column">
                  <input type="checkbox" class="maps-detect-change delete-box" name="<?php echo $point->id; ?>[delete]">
                </th>
                <td><input type="text" class="maps-detect-change full-width" name="<?php echo $point->id; ?>[name]" value="<?php echo $point->name; ?>"></td>
                <td>
                  <select class="maps-detect-change" name="<?php echo $point->id; ?>[cat_id]"><?php

                    if(sizeof($cat) > 0) {
                      foreach ($cat as $key=>$value) {
                        if ( $point->cat_id == $value->id ) {
                          echo '<option value='.$value->id.' selected>'.$value->name.'</option>';
                        } else {
                          echo '<option value='.$value->id.'>'.$value->name.'</option>';
                        }
                      }
                    } ?>

                  </select>
                </td>
                <td><input data-column="address" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[address]" value="<?php echo $point->address; ?>"></td>
                <td><input data-column="city" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[city]" value="<?php echo $point->city; ?>"></td>
                <td><input data-column="state" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[state]" value="<?php echo $point->state; ?>"></td>
                <td><input data-column="zip" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[zip]" value="<?php echo $point->zip; ?>"></td>
                <td><input data-column="country" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[country]" value="<?php echo $point->country; ?>"></td>
                <td><input data-column="phone" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[phone]" value="<?php echo $point->phone; ?>"></td>
                <td><input data-column="url" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[url]" value="<?php echo $point->url; ?>"></td>
                <td><input data-column="latitude" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[lat]" id="lat<?php echo $point->id; ?>" value="<?php echo $point->lat; ?>"></td>
                <td><input data-column="longitude" type="text" class="widen maps-detect-change full-width" name="<?php echo $point->id; ?>[lng]" id="lng<?php echo $point->id; ?>" value="<?php echo $point->lng; ?>"></td>
              </tr><?php
            }
          } ?>

        </tbody>
      </table>
    </div>

    <div class="gj-buttons">
      <div class="btn button table-button add-poi-row">Add POI</div>
      <button class="btn button table-button" type="submit">Update POI</button>
    </div>

  </form><?php

  if(isset($pag) && $pag['total_items'] > 1) { ?>
    <div class="tablenav bottom">
      <div class="tablenav-pages">
        <span class="displaying-num"><?php echo $pag['total_items'].' items'; ?></span>
        <span class="pagination-links">
          <a 
            class="first-page <?php echo $pag['current_page'] - 1 > 0 ? '' : 'disabled'; ?>" 
            title="Go to the first page" href="<?php echo $url.'&paged=1'; ?>">«
          </a>
          <a 
            class="prev-page <?php echo $pag['current_page'] - 1 > 0 ? '' : 'disabled'; ?>" 
            title="Go to the previous page" 
            href="<?php echo $url.'&paged='.($pag['current_page'] - 1 > 0 ? $pag['current_page'] - 1 : $pag['current_page']); ?>">‹
          </a>
          <span 
            class="paging-input"><?php echo $pag['current_page']; ?> of 
            <span class="total-pages"><?php echo $pag['pages'] == 0 ? '1' : $pag['pages']; ?></span>
          </span>
          <a 
            class="next-page <?php echo $pag['current_page'] + 1 > $pag['pages'] ? 'disabled' : ''; ?>" 
            title="Go to the next page" 
            href="<?php echo $url.'&paged='.($pag['current_page'] + 1 > $pag['pages'] ? $pag['current_page'] : $pag['current_page'] + 1); ?>">›
          </a>
          <a 
            class="last-page <?php echo $pag['current_page'] + 1 > $pag['pages'] ? 'disabled' : ''; ?>" 
            title="Go to the last page" 
            href="<?php echo $url.'&paged='.$pag['pages']; ?>">»
          </a>
        </span>
      </div>
    </div><?php
  } ?>

</div>