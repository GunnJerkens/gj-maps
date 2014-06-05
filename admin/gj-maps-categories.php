<?php

var_dump($_POST);

$databaseFunctions = new gjMapsDB();
$adminFunctions = new gjMapsAdmin();

/*
* This is our POST handling
*/

if(!empty($_POST)) {

  if($_POST['form_name'] === 'gj_maps_map_name') {

    $response = $adminFunctions->renameMap($_POST);

  }

  if($_POST['form_name'] === 'gj_maps_cat') {

    foreach($_POST as $post) {

      if(isset($post['delete']) && $post['delete'] === 'on') {

        $deleteItems[] = $post;

      }

    }

    if(!empty($deleteItems)) {

      $response = $adminFunctions->deleteCat($deleteItems);

    }

  }

}

/*
* This is the maps tabbing system
*/

$map_id = $adminFunctions->tabsMapID($_GET);
$map = $databaseFunctions->get_map();

echo '<h2 class="nav-tab-wrapper">';

foreach ($map as $key => $value) {

  echo '<a href="?page=gj_maps_categories&map_id='.$value->id.'" class="nav-tab '.($map_id === $value->id ? 'nav-tab-active' : '').'">'.$value->name.'</a>';

  if($value->id === $map_id) {
    $map_name = $value->name;
  }

}

if(!isset($map_name)) {
  $map_name = $map[0];
  $map_name = $map_name->name;
}

echo '<a href="?page=gj_maps_categories&map_id=new" class="nav-tab">+</a>';

echo '</h2>';

/*
* These calls are for retrieving the POI data for the table.
*/

$cat = $databaseFunctions->get_cat($type='OBJECT', 'map_id=' . $map_id);

/*
* This is our response messaging
*/

if($response['status'] === 'success') {

  echo '<div id="message" class="updated"><p>'.$response['message'].'</p></div>';

} else if ($response['status'] === 'error') {

  echo '<div id="message" class="error"><p>'.$response['message'].'</p></div>';

}


// LEGACY::

    if(isset($_POST['gj_hidden']) && $_POST['gj_hidden'] == 'Y') {
      $icon = null;
      if ($_FILES['icon']) {
        $upload = wp_handle_upload($_FILES['icon'], array('test_form'=>false));
        if (isset($upload['url'])) {
          $icon = $upload['url'];
        }
      }

      //Form data sent
        global $post;
        if (isset($_POST['id'])) {

          if (isset($_POST['delete'])) {
            //Delete Selected cat
            deleteCat($_POST['id']);
          } else {

            //Update existing cat
            $cat = array();
            $defaultCat = array(
              "id" => "1",
              "name" => "category",
              "color" => "#000000",
              "hide_list" => 0,
              "filter_resist" => 0,
              "icon" => NULL
              );

            foreach ($_POST as $key=>$value) {
              if ($key !== 'gj_hidden') {
                $cat[$key] = stripslashes($value);
              }
            }
            $cat['icon'] = $icon;
            $cat = array_merge($defaultCat, $cat);
            editCat($cat);
          }
 
        } else {
          //Add new Category
          $cat = array();
          foreach ($_POST as $key=>$value) {
            if ($key !== 'gj_hidden') {
              $cat[$key] = $value;
            }
          }
          $cat['icon'] = $icon;
          saveCat($cat);
        }

    }?>


<div class="wrap">

  <form name="gj_maps_map_name" class="top-form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="form_name" value="gj_maps_map_name">
    <input type="hidden" name="id" value="<?php echo $map_id; ?>">
    <input type="text" name="name" placeholder="Map Name" value="<?php echo $map_name; ?>"/>
    <button type="submit" class="btn button">Change Map Name</button>
  </form>

  <form name="gj_maps_cat" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
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
            <input type="checkbox" class="maps-detect-change delete-box" name="<?php echo $category->id; ?>[delete]">
          </th>
          <td><input type="text" class="maps-detect-change full-width" name="<?php echo $category->id; ?>[name]" value="<?php echo $category->name; ?>"></td>
          <td><input type="text" name="<?php echo $category->id; ?>[color]" class="color-picker" value="<?php echo $category->color; ?>"></td>
          <td><input type="file" name="<?php echo $category->id; ?>[icon]" value="<?php echo $category->icon; ?>"></td>
          <td><input type="checkbox" class="maps-detect-change" name="<?php echo $category->id; ?>[hide_list]" value="1" <?php if ($category->hide_list) echo 'checked'; ?>></td>
          <td><input type="checkbox" class="maps-detect-change" name="<?php echo $category->id; ?>[filter_resist]" value="1" <?php if ($category->filter_resist) echo 'checked'; ?>></td>
        </tr><?php

      } ?>

      </tbody>
    </table>

    <div class="gj-buttons">
      <div class="btn button table-button add-row">Add Category</div>
      <button class="btn button table-button" type="submit">Update POI</button>
    </div>

  </form>

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
