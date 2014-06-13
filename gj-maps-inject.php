<?php

class gjMapsInject {

  private $databaseFunctions;

  function __construct() {

    $this->databaseFunctions = new gjMapsDB();

    add_shortcode('gjmaps', array(&$this, 'shortcode'));

  }

  function doScripts() {

    global $post;

    $gjMaps = new gjMaps();

    if(!is_admin() && shortcode_exists('gjmaps') && (stripos($post->post_content,'gjmaps') !== false)) {

      $gjMaps->print_scripts();

      $loadState = true;

    } else {

      $loadState = false;

    }

    return $loadState;

  }

  function shortcode($atts) {

    $hasScripts = $this->doScripts();

    if($hasScripts) {

      $this->loadJS = true;

      $cat_default = get_option('gj_cat_default');
      if ($cat_default === "") { $cat_default = "#ffffff"; }

      $gjWrapper = '<div class="gjmaps-wrapper">';
      $gjCanvas = '<div id="map-canvas" class="gjmaps-map-canvas"></div>';
      $gjCategories = '
        <ul class="gjmaps-categories">
          <li class="gjmaps-category active" data-cat-id="all">
            <div class="gjmaps-label" style="background-color: '.$cat_default.';" data-type="label"><span>View All</span></label>
          </li>
        </ul>
        ';
      $gjWrapperClose = '</div>';

      $top = $right = $left = $gjWrapper.$gjCategories.$gjCanvas.$gjWrapperClose;
      $bottom = $gjWrapper.$gjCanvas.$gjCategories.$gjWrapperClose;


      extract(shortcode_atts(array(
        'map' => 'Single',
        'map_id' => '1',
        'position' => 'top',
        'latitude' => get_option('gj_maps_center_lat'),
        'longitude' => get_option('gj_maps_center_lng'),
        'zoom' => get_option('gj_maps_map_zoom')
      ), $atts));

      $gjmapsAPI = $this->frontend($map, $map_id, $latitude, $longitude, $zoom);

      if ($pos === 'bottom' OR $pos === 'bot') {
        return $gjmapsAPI.$bottom;
      } else {
        return $gjmapsAPI.$top;
      }

    } else {

      // Scripts we're not loaded, abort.

    }

  }

  function frontend($map = NULL, $map_id = NULL, $latitude = NULL, $longitude = NULL, $zoom = NULL) {

    if($map_id === NULL && $map !== NULL) {

      $map_id = $this->databaseFunctions->getMapID($map);

    }

    //Writes the JS to the page, including POIs and categories
    $poi = json_encode($this->databaseFunctions->get_poi($type='OBJECT', $map_id));
    echo '<script type="text/javascript">';
    echo 'var poi = ';
    print_r($poi);
    echo ';';

    $poi = json_encode($this->databaseFunctions->get_cat($type='OBJECT', $map_id));
    echo 'var cat = ';
    print_r($poi);
    echo ';';

    echo 'var center_lat = '.($latitude ? $latitude : '34.0459231').';';
    echo 'var center_lng = '.($longitude ? $longitude : '-118.2504648').';';
    echo 'var map_zoom = '.($zoom ? $zoom : '14').';';

    $gj_poi_list = get_option('gj_maps_poi_list');
    $gj_map_styles = get_option('gj_maps_map_styles');
    $gj_label_color = get_option('gj_maps_label_color');

    // Strip slashes and remove whitespace
    $map_styles = stripslashes($gj_map_styles);
    $map_styles = preg_replace("/\s+/", "", $map_styles);

    echo 'var poi_list = '.($gj_poi_list ? $gj_poi_list : '0').';';
    echo 'var label_color = "'.($gj_label_color ? $gj_label_color : '0').'";';
    echo 'var map_styles = '.($gj_map_styles ? $map_styles : '0').';';
    echo '</script>';

  }

}
