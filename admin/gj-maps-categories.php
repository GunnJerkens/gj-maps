<?php

$databaseFunctions = new gjMapsDB();
$adminFunctions = new gjMapsAdmin();

/*
* This is our DELETE handling
*/

if(isset($_GET['delete'])) {

  $delete_map_id = $_GET['delete'];

  $response = $adminFunctions->deleteMap($delete_map_id);

}

/*
* This is our POST handling
*/

if(!empty($_POST)) {

  if($_POST['form_name'] === 'gj_maps_map_name') {

    $response = $adminFunctions->renameMap($_POST);

  }

  if($_POST['form_name'] === 'gj_maps_cat') {

    foreach($_POST as $postKey=>$postValue) {

      if(isset($postValue['delete']) && $postValue['delete'] === 'on') {

        $deleteItems[] = $postValue;

      }

      if(isset($postValue['mode']) && $postValue['mode'] === 'update') {

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

                $postValue['icon'] = $upload['url'];

              }

            }

          }

        }

        $updateItems[] = $postValue;

      }

      if(isset($postValue['mode']) && $postValue['mode'] === 'create') {

        $createItems[] = $postValue;

      }

    }

    if(!empty($deleteItems)) {

      $response = $adminFunctions->deleteCat($deleteItems);

    }

    if(!empty($updateItems)) {

      $response = $adminFunctions->editCat($updateItems);

    }

    if(!empty($createItems)) {

      $response = $adminFunctions->createCat($createItems);

    }

  }

  if($_GET['map_id'] === 'new') {

    $createMap = false;

  }

}

/*
* This is the maps tabbing system
*/

$map = $databaseFunctions->get_map();

if(isset($createMap) && $createMap === false) {

  $map_id = $_POST['id'];

} else {

  $map_id = $adminFunctions->tabsMapID($_GET);

}

echo $adminFunctions->mapsTab('cat', $map, $map_id);


/*
* Retrieve the cat data for the table
*/

$cat = $databaseFunctions->get_cat($type='OBJECT', $map_id);
wp_localize_script('gj_maps_admin_js', 'map', array('id' => $map_id));


/*
* This is our response messaging
*/

if(isset($response)) {

  if($response['status'] === 'success') {

    echo '<div id="message" class="updated"><p>'.$response['message'].'</p></div>';

  } else if ($response['status'] === 'error') {

    echo '<div id="message" class="error"><p>'.$response['message'].'</p></div>';

  }

}

/*
* Sets our POST action
*/

$current_uri = str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);
$parsed_uri = parse_url($current_uri);
$post_uri = $map_id ? $parsed_uri['path'].'?page=gj_maps_categories&map_id='.$map_id : $current_uri; ?>


<div class="wrap"><?php

  if($cat !== false) { ?>

    <form name="gj_maps_map_name" class="top-form" method="post" action="<?php echo $post_uri; ?>">
      <input type="hidden" name="form_name" value="gj_maps_map_name">
      <input type="hidden" name="id" value="<?php echo $map_id; ?>">
      <input type="text" name="name" placeholder="Map Name" value="<?php echo isset($map[0]->name) ? $map[0]->name : ''; ?>"/>
      <button type="submit" class="btn button">Change Map Name</button>
    </form>
    <a href="?page=gj_maps_categories&delete=<?php echo $map_id; ?>" id="delete">Delete Map</a>

    <form name="gj_maps_cat" method="post" enctype="multipart/form-data" action="<?php echo $post_uri; ?>">
      <input type="hidden" name="form_name" value="gj_maps_cat">
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


        foreach ($cat as $category) { ?>

          <tr id="map-<?php echo $category->id; ?>" class="alternate cat" data-id="<?php echo $category->id; ?>" data-map="<?php echo $map_id; ?>">
            <input type="hidden" name="<?php echo $category->id; ?>[id]" value="<?php echo $category->id; ?>">
            <input type="hidden" name="<?php echo $category->id; ?>[map_id]" value="<?php echo $map_id; ?>">
            <input type="hidden" class="mode" name="<?php echo $category->id; ?>[mode]" value="">
            <th class="check-column">
              <input type="checkbox" class="delete-box" name="<?php echo $category->id; ?>[delete]">
            </th>
            <td><input type="text" class="maps-detect-change full-width" name="<?php echo $category->id; ?>[name]" value="<?php echo $category->name; ?>"></td>
            <td><input type="text" class="maps-detect-change color-picker" name="<?php echo $category->id; ?>[color]" value="<?php echo $category->color; ?>"></td>
            <td><img src="<?php echo $category->icon; ?>"><input type="file" class="maps-detect-change" name="<?php echo $category->id; ?>[icon]" value="<?php echo $category->icon; ?>"></td>
            <td><input type="checkbox" class="maps-detect-change" name="<?php echo $category->id; ?>[hide_list]" value="1" <?php if ($category->hide_list) echo 'checked'; ?>></td>
            <td><input type="checkbox" class="maps-detect-change" name="<?php echo $category->id; ?>[filter_resist]" value="1" <?php if ($category->filter_resist) echo 'checked'; ?>></td>
          </tr><?php

        } ?>

        </tbody>
      </table>

      <div class="gj-buttons">
        <div class="btn button table-button add-cat-row">Add Category</div>
        <button class="btn button table-button" type="submit">Update Categories</button>
      </div>

    </form><?php

  } else {

    echo '<h2>Hit the + to create your first map.</h2>';

  } ?>

</div>
