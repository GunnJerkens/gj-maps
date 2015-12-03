<?php

/**
 * Contains the markup for the import and the basic controller logic
 *
 * @TODO: Move controller logic into a controller
 */

$mapsDatabase = new gjMapsDB();
$mapsAdmin    = new gjMapsAdmin();

if(isset($_FILES['file']) && isset($_POST)) {
  if(1 !== check_admin_referer('gj-maps-upload')) {
    die('Permission denied.');
  }

  if($_POST['map'] === 'new') {
    $mapID = 'new';
  } else {
    $mapID = $_POST['map'];
  }

  $response = $mapsAdmin->importData($_FILES['file'], $mapID);

  if($response['status'] === 'success') {
    echo '<div id="message" class="updated"><p>'.$response['message'].'</p></div>';
  } else {
    echo '<div id="message" class="error"><p>'.$response['message'].'</p></div>';
  }
}

$maps = $mapsDatabase->getMaps(); ?>

<div class="wrap">
  <form name="gj_maps_upload" method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <?php wp_nonce_field('gj-maps-upload'); ?>
    <table class="form-table">
      <tr>
        <th><label for="file">Choose CSV</label></th>
        <td><input type="file" name="file" value="" size="20"></td>
      </tr>
      <tr>
        <th><label for="map">Select Map</label></th>
        <td>
          <select name="map">
            <option value="new" selected>Create New</option><?php
            foreach($maps as $map) {
              echo '<option value="'.$map->id.'">'.$map->name.'</option>';
            } ?>
          </select>
        </td>
      </tr>
    </table>
    <p>Required columns: name, category, address, city, state, zip, country, phone, url.<p>
    <button class="btn button" type="submit">Upload Data</button>
  </form>
</div>
