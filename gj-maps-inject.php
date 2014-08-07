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
        'zoom' => get_option('gj_maps_map_zoom'),
        'api' => null
      ), $atts));

      $gjmapsAPI = $this->frontend(array(
          'map' => $map,
          'map_id' => $map_id,
          'latitude' => $latitude,
          'longitude' => $longitude,
          'zoom' => $zoom,
          'api' => $api
        )
      );

      if ($position === 'bottom' OR $position === 'bot') {

        return $gjmapsAPI.$bottom;

      } else {

        return $gjmapsAPI.$top;

      }

    } else {

      // Scripts we're not loaded, abort.

    }

  }

  function frontend($mapSettings) {

    if($mapSettings['api'] != null) {

      $json = @file_get_contents($mapSettings['api']);
      $data = json_decode($json);

      $poi = json_encode($data->poi);
      $cat = json_encode($data->cat);

    } else {

      if($mapSettings['map_id'] === NULL && $mapSettings['map !== NULL']) {

        $mapSettings['map_id'] = $this->databaseFunctions->getMapID($map);

      }

      $poi = json_encode($this->databaseFunctions->get_poi($type='OBJECT', $mapSettings['map_id']));
      $cat = json_encode($this->databaseFunctions->get_cat($type='OBJECT', $mapSettings['map_id']));

    }

    echo '<script type="text/javascript">';

    echo 'var poi = ';
    print_r($poi);
    echo ';';

    echo 'var cat = ';
    print_r($cat);
    echo ';';

    echo 'var center_lat = '.($mapSettings['latitude'] ? $mapSettings['latitude'] : '34.0459231').';';
    echo 'var center_lng = '.($mapSettings['longitude'] ? $mapSettings['longitude'] : '-118.2504648').';';
    echo 'var map_zoom = '.($mapSettings['zoom'] ? $mapSettings['zoom'] : '14').';';

    $gj_poi_list = get_option('gj_maps_poi_list');
    $gj_map_styles = get_option('gj_maps_map_styles');
    $gj_label_color = get_option('gj_maps_label_color');

    // Strip slashes and remove whitespace
    $gj_map_styles = stripslashes($gj_map_styles);
    $gj_map_styles = preg_replace("/\s+/", "", $gj_map_styles);

    echo 'var poi_list = '.($gj_poi_list ? $gj_poi_list : '0').';';
    echo 'var label_color = "'.($gj_label_color ? $gj_label_color : '0').'";';
    echo 'var map_styles = '.($gj_map_styles ? $gj_map_styles : '0').';';
    echo '</script>';

  }

}
