<?php

$ad = new gjMapsAdmin();

if(isset($_POST['gj_hidden']) && $_POST['gj_hidden'] == 'settings_update') {
  if(1 !== check_admin_referer('gj-maps-settings')) {
    die('Permission denied');
  }

  $post = $_POST;
  $response = $ad->updateSettings($post);

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
}

$settings = gjMapsAdmin::getSettings(); ?>


<div class="wrap">
  <h3>Basic Settings</h3>
  <form name="gj_maps_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="gj_hidden" value="settings_update">
    <?php wp_nonce_field('gj-maps-settings'); ?>
    <table class="form-table">
      <tr>
        <th><label for="api_key">API Key</label></th>
        <td><input type="text" name="api_key" value="<?php echo $settings->api_key; ?>" required></td>
      </tr>
      <tr>
        <th><label for="use_styles">Styles</label></th>
        <td><input type="checkbox" name="use_styles" <?php echo $settings->use_styles ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="label_color">Label Color</label></th>
        <td>
          <select name="label_color">
            <option value="none" <?php echo $settings->label_color === 'none' ? 'selected': ''; ?>>None</option>
            <option value="background" <?php echo $settings->label_color === 'background' ? 'selected' : ''; ?>>Background</option>
            <option value="text" <?php echo $settings->label_color === 'text' ? 'selected' : ''; ?>>Text</option>
            <option value="icon" <?php echo $settings->label_color === 'icon' ? 'selected' : ''; ?>>Icon</option>
          </select>
        </td>
      </tr>
      <tr>
        <th><label for="poi_list">Show POI List</label></th>
        <td><input type="checkbox" name="poi_list" <?php echo $settings->poi_list ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="poi_alpha_list">Alphabetize POI List</label></th>
        <td><input type="checkbox" name="poi_alpha_list" <?php echo $settings->poi_alpha_list ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="poi_num">Numbered POI</label></th>
        <td><input type="checkbox" name="poi_num" <?php echo $settings->poi_num ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="poi_filter_load">Filter Resist (on load)</label></th>
        <td><input type="checkbox" name="poi_filter_load" <?php echo $settings->poi_filter_load ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="disable_mouse_scroll">Disable Mouse Scroll</label></th>
        <td><input type="checkbox" name="disable_mouse_scroll" <?php echo $settings->disable_mouse_scroll ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="disable_mouse_drag">Disable Mouse Drag</label></th>
        <td><input type="checkbox" name="disable_mouse_drag" <?php echo $settings->disable_mouse_drag ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="enable_fit_bounds">Enable Fit Bounds</label></th>
        <td><input type="checkbox" name="enable_fit_bounds" <?php echo $settings->enable_fit_bounds ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="enable_phone_link">Enable Phone Link</label></th>
        <td><input type="checkbox" name="enable_phone_link" <?php echo $settings->enable_phone_link ? 'checked' : ''; ?>></td>
      </tr>
      <tr>
        <th><label for="cat_default">View All Default Color</label></th>
        <td><input type="text" name="cat_default" class="color-picker" value="<?php echo $settings->cat_default; ?>"/></td>
      </tr>
      <tr>
        <th><label for="center_lat">Center Latitude</label></th>
        <td><input type="text" name="center_lat" value="<?php echo $settings->center_lat; ?>"></td>
      </tr>
      <tr>
        <th><label for="center_lng">Center Longitude</label></th>
        <td><input type="text" name="center_lng" value="<?php echo $settings->center_lng; ?>"></td>
      </tr>
      <tr>
        <th><label for="map_zoom">Map Zoom</label></th>
        <td><input type="text" name="map_zoom" value="<?php echo $settings->map_zoom; ?>"></td>
      </tr>
      <tr>
        <th><label for="max_zoom">Max Zoom</label></th>
        <td><input type="text" name="max_zoom" value="<?php echo $settings->max_zoom; ?>"></td>
      </tr>
      <tr>
        <th><label for="link_text">Link Text</label></th>
        <td><input type="text" name="link_text" value="<?php echo $settings->link_text; ?>"></td>
      </tr>
      <tr>
        <th><label for="map_styles">Map Styles<br><a href="http://snazzymaps.com/" target="_blank">[View Samples]</a></label></th>
        <td><textarea rows="6" cols="50" type="textarea" name="map_styles"><?php echo $settings->map_styles; ?></textarea></td>
      </tr>

    </table>
    <input class="btn button" type="submit" name="Submit" value="Update Settings" />

  </form>
</div>
