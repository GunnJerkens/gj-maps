<?php

/**
 * Contains the markup for the categories and the basic controller logic
 *
 * @TODO: Move controller logic into a controller
 */

$db = new gjMapsDB();
$ad = new gjMapsAdmin();

/**
 * Delete a map
 */
if(isset($_GET['delete'])) {
  $delete_map_id = $_GET['delete'];
  $response = $ad->deleteMap($delete_map_id);
}

/**
 * Create a map else set our map ID
 */
if(isset($_GET['map_id']) && $_GET['map_id'] === "new" && empty($_POST)) {
  $map_id = $ad->createMap();
} elseif (!isset($_GET['map_id']) || (isset($_GET['map_id']) && $_GET['map_id'] === "new")) {
  $map_id = $db->minMapId();
} else {
  $map_id = (int) $_GET['map_id'];
}

/**
 * Handle POST items
 */
if(!empty($_POST)) {
  if(1 !== check_admin_referer('gj-maps-cat')) {
    die('Permission denied.');
  }

  if($_POST['form_name'] === 'gj_maps_map_name') {
    $response = $ad->renameMap($_POST);
  }

  if($_POST['form_name'] === 'gj_maps_cat') {

    foreach($_POST as $postKey=>$postValue) {

      if(isset($postValue['delete']) && $postValue['delete'] === 'on') {
        $postValue['cat_delete_poi'] = $_POST['cat_delete_poi'];
        $deleteItems[] = $postValue;
      }

      if(isset($postValue['mode']) && ($postValue['mode'] === 'update' || $postValue['mode'] === 'create')) {
        foreach($_FILES as $fileKey=>$fileValue) {

          if($postKey === $fileKey) {
            $icon['name'] = isset($fileValue['name']['icon']) ? $fileValue['name']['icon'] : '';
            $icon['type'] = isset($fileValue['type']['icon']) ? $fileValue['type']['icon'] : '';
            $icon['tmp_name'] = isset($fileValue['tmp_name']['icon']) ? $fileValue['tmp_name']['icon'] : '';
            $icon['error'] = isset($fileValue['error']['icon']) ? $fileValue['error']['icon'] : '';
            $icon['size'] = isset($fileValue['size']['icon']) ? $fileValue['size']['icon'] : '';

            if(isset($icon['name'])) {
              $upload = wp_handle_upload($icon, array('test_form'=>false));

              if(isset($upload['url'])) {
                $postValue['icon'] = wp_make_link_relative($upload['url']);
              }
            }
          }
        }
        // Set's our checkboxes to false as they do not POST unchecked
        if(!isset($postValue['hide_list'])) $postValue['hide_list'] = false;
        if(!isset($postValue['filter_resist'])) $postValue['filter_resist'] = false;

        if($postValue['mode'] === 'update') {
          $updateItems[] = $postValue;
        }else  if($postValue['mode'] === 'create') {
          $createItems[] = $postValue;
        }
      }
    }

    if(!empty($updateItems)) {
      $response = $ad->editCat($updateItems);
    }

    if(!empty($createItems)) {
      $response = $ad->createCat($createItems);
    }

    if(!empty($deleteItems)) {
      $response = $ad->deleteCat($deleteItems);
    }
  }

  if(isset($_GET['map_id']) && $_GET['map_id'] === 'new') {
    $createMap = false;
  }
}

$maps = $db->getMaps();
$map  = $db->getMap($map_id);
$cat  = false;

if(isset($map[0])) {
  $map = $map[0];
  $cat = $db->getCategories($map_id);
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

$hasModal = false;
if(isset($response) && isset($response['modal'])) {
  $hasModal = true;
}


echo $ad->mapsTab('cat', $maps, $map); ?>

<div class="wrap">
  <form name="gj_maps_map_name" class="top-form" method="post">
    <input type="hidden" name="form_name" value="gj_maps_map_name">
    <?php wp_nonce_field('gj-maps-cat'); ?>
    <input type="hidden" name="map_id" value="<?php echo $map_id; ?>">
    <input type="text" name="name" placeholder="Map Name" value="<?php echo isset($map->name) ? $map->name : ''; ?>"/>
    <button type="submit" class="btn button">Change Map Name</button>
  </form>
  <a href="?page=gj_maps_categories&delete=<?php echo $map_id; ?>" id="delete">Delete Map</a>

  <form name="gj_maps_cat" method="post" enctype="multipart/form-data">
    <input type="hidden" name="form_name" value="gj_maps_cat">
    <?php wp_nonce_field('gj-maps-cat'); ?>
    <table class="wp-list-table widefat fixed gj-maps">
      <thead class="">
        <tr>
          <th scope="col" id="cb" class="column-cb check-column">
            <input id="cb-select-all-1" type="checkbox">
          </th>
          <th><span>Name</span></th>
          <th style="width: 250px;"><span>Color</span></th>
          <th><span>Icon</span></th>
          <th><span>Hide Listing</span></th>
          <th><span>Resist Filter</span></th>
        </tr>
      </thead>
      <tbody><?php

      if($cat && sizeof($cat) > 0) {
        foreach ($cat as $category) { ?>

          <tr id="map-<?php echo $category->id; ?>" class="alternate cat" data-id="<?php echo $category->id; ?>" data-map="<?php echo $map_id; ?>">
            <input type="hidden" name="<?php echo $category->id; ?>[id]" value="<?php echo $category->id; ?>">
            <input type="hidden" name="<?php echo $category->id; ?>[map_id]" value="<?php echo $map_id; ?>">
            <input type="hidden" class="mode" name="<?php echo $category->id; ?>[mode]" value="">
            <th class="check-column">
              <input type="checkbox" class="delete-box" name="<?php echo $category->id; ?>[delete]"
                <?php
                  if ($hasModal) {
                    if($response['modal']['type'] === 'has_poi')
                      echo in_array($category->id, $response['modal']['items']) ? 'checked' : '';
                  }
                ?>
              >
            </th>
            <td><input type="text" class="maps-detect-change full-width" name="<?php echo $category->id; ?>[name]" value="<?php echo $category->name; ?>"></td>
            <td><input type="text" class="maps-detect-change color-picker" name="<?php echo $category->id; ?>[color]" value="<?php echo $category->color; ?>"></td>
            <td><img src="<?php echo wp_make_link_relative($category->icon); ?>"><input type="file" class="maps-detect-change" name="<?php echo $category->id; ?>[icon]" value="<?php echo wp_make_link_relative($category->icon); ?>"></td>
            <td><input type="checkbox" class="maps-detect-change" name="<?php echo $category->id; ?>[hide_list]" value="1" <?php if ($category->hide_list) echo 'checked'; ?>></td>
            <td><input type="checkbox" class="maps-detect-change" name="<?php echo $category->id; ?>[filter_resist]" value="1" <?php if ($category->filter_resist) echo 'checked'; ?>></td>
          </tr><?php

        }
      } ?>

      </tbody>
    </table>

    <div class="gj-buttons">
      <div class="btn button table-button add-cat-row">Add Category</div>
      <button class="btn button table-button" type="submit">Update Categories</button>
    </div>

    <?php if($hasModal) { ?>
      <div id="cat-delete-modal" class="gj-modal open">
        <div class="gj-modal-dialog">
          <div class="gj-modal-content">
            <h4 class="gj-modal-message"><?php echo $response['message']; ?></h4>
            <?php if($response['modal']['type'] === 'has_poi') { ?>
              <p>Please select an option:</p>
              <div class="gj-modal-input">
                <label>
                  <input type="radio" name="cat_delete_poi" value="1" checked>
                  Delete all related points of interest.
                </label>
              </div>
              <div class="gj-modal-input">
                <label>
                  <input class="gj-modal-input" type="radio" name="cat_delete_poi" value="0">
                  Set all related points of interest to the <strong>Default</strong> category.
                </label>
              </div>
            <?php } ?>
            <div class="gj-modal-footer">
              <button type="button" class="gj-modal-cancel btn button table-button">Cancel</button>
              <button class="btn button table-button" type="submit">Update Categories</button>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </form>
</div>
