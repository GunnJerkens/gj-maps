<?php
  if(isset($_POST['gj_hidden']) && $_POST['gj_hidden'] == 'gj_form_update_options') {
    //Form data sent
    $styles = isset($_POST['gj_styles']);
    update_option('gj_styles', $styles);

    $label_color = $_POST['gj_label_color'];
    update_option('gj_label_color', $label_color);

    $poi_list = isset($_POST['gj_poi_list']);
    update_option('gj_poi_list', $poi_list);

    $map_styles = $_POST['gj_map_styles'];
    update_option('gj_map_styles', $map_styles);
    $map_styles_strip = stripslashes($map_styles);

    $cat_default = $_POST['gj_cat_default'];
    update_option('gj_cat_default', $cat_default);

    $center_lat = $_POST['gj_center_lat'];
    update_option('gj_center_lat', $center_lat);

    $center_lng = $_POST['gj_center_lng'];
    update_option('gj_center_lng', $center_lng);

    $map_zoom = $_POST['gj_map_zoom'];
    update_option('gj_map_zoom', $map_zoom);
    ?>
    <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
    <?php
  } else {
    //Normal page display
    $styles = get_option('gj_styles');
    $label_color = get_option('gj_label_color');
    $poi_list = get_option('gj_poi_list');
    $cat_default = get_option('gj_cat_default');
    $center_lat = get_option('gj_center_lat');
    $center_lng = get_option('gj_center_lng');
    $map_zoom = get_option('gj_map_zoom');
    $map_styles = get_option('gj_map_styles');
    $map_styles_strip = stripslashes($map_styles);
  }
?>



<div class="wrap">
  <h3>Basic</h3>
  <form name="gj_maps_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="gj_hidden" value="settings_update">
    <table class="form-table">
      <tr>
        <th><label for="use_gj_style">Styles</label></th>
        <td><input type="checkbox" name="use_gj_style" <?php if ($styles) echo 'checked'; ?>></td>
      </tr>
      <tr>
        <th><label for="label_color">Label Color</label></th>
        <td>
          <select name="label_color">
            <option value="none" <?php if ($label_color === 'none') echo 'selected'; ?>>None</option>
            <option value="background" <?php if ($label_color === 'background') echo 'selected'; ?>>Background</option>
            <option value="text" <?php if ($label_color === 'text') echo 'selected'; ?>>Text</option>
          </select>
        </td>
      </tr>
      <tr>
        <th><label for="poi_list">Show POI List</label></th>
        <td><input type="checkbox" name="poi_list" <?php if ($poi_list) echo 'checked'; ?>></td>
      </tr>
      <tr>
        <th><label for="cat_default">View All Default Color</label></th>
        <td><input type="text" name="cat_default" class="color-picker" value="<?php echo $cat_default; ?>"/></td>
      </tr>
      <tr>
        <th><label for="center_lat">Center Latitude</label></th>
        <td><input type="text" name="center_lat" value="<?php echo $center_lat; ?>"></td>
      </tr>
      <tr>
        <th><label for="center_lng">Center Longitude</label></th>
        <td><input type="text" name="center_lng" value="<?php echo $center_lng; ?>"></td>
      </tr>
      <tr>
        <th><label for="map_zoom">Map Zoom</label></th>
        <td><input type="text" name="map_zoom" value="<?php echo $map_zoom; ?>"></td>
      </tr>
      <tr>
        <th><label for="map_styles">Map Styles<br><a href="http://snazzymaps.com/" target="_blank">[View Samples]</a></label></th>
        <td><textarea cols="50" type="textarea" name="map_styles"><?php echo $map_styles_strip; ?></textarea></td>
      </tr>

    </table>
    <input class="btn button" type="submit" name="Submit" value="Update Settings" />

  </form>
</div>
