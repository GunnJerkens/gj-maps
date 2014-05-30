<?php

var_dump($_POST);


if(isset($_POST['gj_hidden']) && $_POST['gj_hidden'] == 'gj_maps_upload') {

  $adminFunctions = new gjMapsAdmin();

  $response = $adminFunctions->importData($_POST);

}

$databaseFunctions = new gjMapsDB(); 
$maps = $databaseFunctions->get_map(); ?>

<div class="wrap">
  <form name="gj_maps_upload" method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="gj_hidden" value="gj_maps_upload">
    <table class="form-table">
      <tr>
        <th><label for="file">Choose CSV</label></th>
        <td><input type="file" name="file" value="<?php echo $upload; ?>" size="20"></td>
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
