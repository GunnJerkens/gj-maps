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

      wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?v=3&sensor=false', null, null);
      if(get_option('gj_maps_poi_num')) wp_enqueue_script('gj-maps-mwl', plugin_dir_url(__FILE__).'js/libs/markerwithlabel.js', array('jquery', 'google-maps'), false, true);
      wp_enqueue_script('gj-maps-main', plugin_dir_url(__FILE__).'js/main.js', array('jquery', 'google-maps'), false, true);
      if (get_option('gj_maps_use_styles')) wp_enqueue_style('gj-maps-screen', plugin_dir_url(__FILE__).'css/screen.css', null, true);

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
        'map'       => null,
        'map_id'    => null,
        'position'  => 'top',
        'latitude'  => get_option('gj_maps_center_lat'),
        'longitude' => get_option('gj_maps_center_lng'),
        'zoom'      => get_option('gj_maps_map_zoom'),
        'api'       => null
      ), $atts));

      $gjmapsAPI = $this->frontend(array(
          'map'       => $map,
          'map_id'    => $map_id,
          'latitude'  => $latitude,
          'longitude' => $longitude,
          'zoom'      => $zoom,
          'api'       => $api
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

    $poi_list     = get_option('gj_maps_poi_list');
    $poi_num      = get_option('gj_maps_poi_num');
    $filter_load  = get_option('gj_poi_filter_load');
    $mouse_scroll = get_option('gj_disable_mouse_scroll');
    $mouse_drag   = get_option('gj_disable_mouse_drag');
    $fit_bounds   = get_option('gj_enable_fit_bounds');
    $label_color  = get_option('gj_maps_label_color');
    $max_zoom     = get_option('gj_maps_max_zoom');
    $link_text    = get_option('gj_maps_link_text');
    $map_styles   = preg_replace("/\s+/", "", stripslashes(get_option('gj_maps_map_styles')));

    $settings['center_lat']   = $mapSettings['latitude'] ? $mapSettings['latitude'] : '34.0459231';
    $settings['center_lng']   = $mapSettings['longitude'] ? $mapSettings['longitude'] : '-118.2504648';
    $settings['map_zoom']     = $mapSettings['zoom'] ? (int) $mapSettings['zoom'] : 14;
    $settings['max_zoom']     = $max_zoom ? (int) $max_zoom : '';
    $settings['link_text']    = $link_text ? $link_text : '';
    $settings['poi_list']     = $poi_list ? $poi_list : '0';
    $settings['poi_num']      = $poi_num ? $poi_num : '0';
    $settings['poi_icon']     = $poi_num ? plugin_dir_url(__FILE__) . 'img/trans.png' : '0';
    $settings['filter_load']  = $filter_load ? $filter_load : '0';
    $settings['mouse_scroll'] = $mouse_scroll ? $mouse_scroll : '0';
    $settings['mouse_drag']   = $mouse_drag ? $mouse_drag : '0';
    $settings['fit_bounds']   = $fit_bounds ? $fit_bounds : '0';
    $settings['label_color']  = $label_color ? $label_color : '0';
    $settings['map_styles']   = $map_styles ? $map_styles : '0';

    wp_localize_script('gj-maps-main', 'poi', $poi);
    wp_localize_script('gj-maps-main', 'cat', $cat);
    wp_localize_script('gj-maps-main', 'settings', $settings);

  }

}
