<?php

if(isset($_POST['gj_hidden']) && $_POST['gj_hidden'] == 'gj_maps_delete') {

  $adminFunctions = new gjMapsAdmin();

  $response = $adminFunctions->deleteData($_POST);

  if($response['status'] === 'success') {

    echo '<div id="message" class="updated"><p>'.$response['message'].'</p></div>';

  } else if ($response['status'] === 'error') {

    echo '<div id="message" class="error"><p>'.$response['message'].'</p></div>';

  } 

var_dump($response);

} ?>


<div class="wrap">
  <h4>Are you sure you want to delete all data?</h4>
  <form name="gj_maps_delete" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="gj_hidden" value="gj_maps_delete">
    <table class="form-table">
      <tr>
        <th><label for="delete">Select Data</label></th>
        <td>
          <select name="delete">
            <option value="default" selected>Do Not Delete</option>
<!--             <option value="delete_categories">Delete All Categories</option>
            <option value="delete_maps">Delete All Maps</option>
            <option value="delete_poi">Delete All POI</option> -->
            <option value="delete_all">Delete All Data</option>
          </select>
        </td>
      </tr>
    </table>
    <button class="btn button" type="submit">Delete Data</button>
  </form>
</div>
