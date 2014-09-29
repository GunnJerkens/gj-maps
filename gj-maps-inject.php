<?php

class gjMapsInject {

  private $databaseFunctions;

  function __construct() {

    $this->databaseFunctions = new gjMapsDB();

    add_shortcode('gjmaps', array(&$this, 'shortcode'));

    define('DISABLE_GJ_MAPS', FALSE);

  }

  function doScripts() {
    global $post;
    $gjMaps = new gjMaps();

    if(!is_admin() && shortcode_exists('gjmaps') && !constant('DISABLE_GJ_MAPS')) {

      $gjMaps->print_scripts();

      return true;

    } else {

      return false;

    }
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
        'map' => null,
        'map_id' => null,
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

    }

  }

  function frontend($mapSettings) {

    if($mapSettings['api'] != null) {

      $json = @file_get_contents($mapSettings['api']);
      $data = json_decode($json);

      $poi = $data->poi;
      $cat = $data->cat;

    } else {

      if($mapSettings['map_id'] === null && $mapSettings['map'] !== null) {

        $mapSettings['map_id'] = $this->databaseFunctions->getMapID($mapSettings['map']);

      } elseif($mapSettings['map'] === null && $mapSettings['map_id'] !== null) {

        $mapSettings['map'] = $this->databaseFunctions->getMapName($mapSettings['map_id']);

      } else {

        $mapSettings['map'] = 'Map 1';
        $mapSettings['map_id'] = '1';

      }

      $poi = $this->databaseFunctions->get_poi($type='OBJECT', $mapSettings['map_id']);
      $cat = $this->databaseFunctions->get_cat($type='OBJECT', $mapSettings['map_id']);

    }

    if(get_option('gj_maps_poi_num')) {
      foreach($cat as $singleCat) {
        $count = 1;
        foreach($poi as $singlePOI) {
          if($singlePOI->cat_id === $singleCat->id) {
            $singlePOI->num = $count;
            $count++;
          }
        }
      }
    }

    $poi = json_encode($poi);
    $cat = json_encode($cat);

    /*
    *  This is all really shitty and needs to be rewritten using wp_localize_script
    *  09/15/14 ps
    */

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
    $gj_poi_num = get_option('gj_maps_poi_num');
    $gj_map_styles = get_option('gj_maps_map_styles');
    $gj_label_color = get_option('gj_maps_label_color');

    // Strip slashes and remove whitespace
    $gj_map_styles = stripslashes($gj_map_styles);
    $gj_map_styles = preg_replace("/\s+/", "", $gj_map_styles);

    echo 'var poi_list = '.($gj_poi_list ? $gj_poi_list : '0').';';
    echo 'var poi_number = '.($gj_poi_num ? $gj_poi_num : '0').';';
    echo 'var poi_icon_url = '.($gj_poi_num ? '"'.plugin_dir_url(__FILE__) . 'img/trans.png"' : '0').';';
    echo 'var label_color = "'.($gj_label_color ? $gj_label_color : '0').'";';
    echo 'var map_styles = '.($gj_map_styles ? $gj_map_styles : '0').';';
    echo '</script>';

  }

}
