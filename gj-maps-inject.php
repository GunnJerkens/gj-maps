<?php

class gjMapsInject {

  /**
   * Database class
   *
   * @var object
   */
  private $db;

  /**
   * Instantiate the class
   *
   * @return void
   */
  function __construct() {
    $this->db = new gjMapsDB();
    add_shortcode('gjmaps', array(&$this, 'shortcode'));
  }

  /**
   * Handles loading the script to the needed templates
   *
   * @return bool
   */
  function doScripts() {
    if(!is_admin() && shortcode_exists('gjmaps') && !defined('DISABLE_GJ_MAPS')) {
      wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?v=3', null, null);
      wp_enqueue_script('handlebars', plugin_dir_url(__FILE__).'js/libs/handlebars-v4.0.5.js', null, null);
      if(get_option('gj_maps_poi_num')) wp_enqueue_script('gj-maps-mwl', plugin_dir_url(__FILE__).'js/libs/markerwithlabel.js', array('jquery', 'google-maps', 'handlebars'), false, true);
      wp_enqueue_script('gj-maps-main', plugin_dir_url(__FILE__).'js/main.js', array('jquery', 'google-maps', 'handlebars'), false, true);
      if (get_option('gj_maps_use_styles')) wp_enqueue_style('gj-maps-screen', plugin_dir_url(__FILE__).'css/screen.css', null, true);

      return true;
    }

    return false;
  }

  /**
   * Process shortcodes from the_content or do_shortcode()
   *
   * @param $atts array
   *
   * @return string
   */
  function shortcode($atts) {
    $hasScripts = $this->doScripts();

    if($hasScripts) {

      $this->loadJS = true;

      $cat_default = get_option('gj_maps_cat_default');
      $cat_default = $cat_default === "" ? "#ffffff" : $cat_default;
      $label_color  = get_option('gj_maps_label_color');
      $label_style = '';
      if ($label_color !== 'none') {
        $label_style = $label_color === 'background' ? 'style="background-color:'.$cat_default.';"' : 'style="color:'.$cat_default.';"';
      }
      $gjWrapperOpen = '<div class="gjmaps-wrapper">';
      $gjCanvas = '<div id="map-canvas" class="gjmaps-map-canvas"></div>';
      $gjCategories = '
        <ul class="gjmaps-categories">
          <li class="gjmaps-category active" data-cat-id="all">
            <div class="gjmaps-label" '.$label_style.' data-type="label"><span>View All</span></label>
          </li>
        </ul>
      ';
      $gjWrapperClose = '</div>';

      $top = $gjWrapperOpen.$gjCategories.$gjCanvas.$gjWrapperClose;
      $bot = $gjWrapperOpen.$gjCanvas.$gjCategories.$gjWrapperClose;


      extract(shortcode_atts(array(
        'map'       => null,
        'map_id'    => null,
        'position'  => 'top',
        'latitude'  => get_option('gj_maps_center_lat'),
        'longitude' => get_option('gj_maps_center_lng'),
        'zoom'      => get_option('gj_maps_map_zoom'),
        'api'       => false
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

      if($gjmapsAPI) {
        return ($position === 'bottom' OR $position === 'bot') ? $bot : $top;
      }
    }
  }

  /**
   * This method dumps out the content to the frontend as CDATA
   *
   * @param $mapSettings array
   *
   * @return bool
   */
  function frontend($mapSettings) {

    if(isset($mapSettings['api']) && $mapSettings['api'] !== false) {

      $json = file_get_contents($mapSettings['api']);
      $data = json_decode($json);

      if(!$data || !isset($data->poi) || !isset($data->cat)) {
        return false;
      }

      $poi = $data->poi;
      $cat = $data->cat;

    } else {

      if($mapSettings['map_id'] === null && $mapSettings['map'] !== null) {
        $mapSettings['map_id'] = $this->db->getMapID($mapSettings['map']);
      }

      if(!isset($mapSettings['map_id'])) {
        return false;
      }

      $poi = $this->db->getPoi($mapSettings['map_id']);
      $cat = $this->db->getCategories($mapSettings['map_id']);
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
    $phone_link   = get_option('gj_enable_phone_link');
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
    $settings['poi_icon']     = plugin_dir_url(__FILE__) . 'img/trans.png';
    $settings['filter_load']  = $filter_load ? $filter_load : '0';
    $settings['mouse_scroll'] = $mouse_scroll ? $mouse_scroll : '0';
    $settings['mouse_drag']   = $mouse_drag ? $mouse_drag : '0';
    $settings['fit_bounds']   = $fit_bounds ? $fit_bounds : '0';
    $settings['phone_link']   = $phone_link ? $phone_link : '0';
    $settings['label_color']  = $label_color ? $label_color : '0';
    $settings['map_styles']   = $map_styles ? $map_styles : '0';

    wp_localize_script('gj-maps-main', 'poi', $poi);
    wp_localize_script('gj-maps-main', 'cat', $cat);
    wp_localize_script('gj-maps-main', 'settings', $settings);

    return true;
  }

}
