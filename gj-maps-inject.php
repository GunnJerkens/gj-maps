<?php

class gjMapsInject {

  function __construct() {

    add_shortcode('gjmaps', array(&$this, 'shortcode'));

  }

  function shortcode($atts) {

    $this->loadJS = true;

    $cat_default = get_option('gj_cat_default');
    if ($cat_default === "") { $cat_default = "#ffffff"; }

    $gjmapsAPI = $this->frontend();

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
      "pos" => 'top'
    ), $atts));


    if ($pos === 'bottom' OR $pos === 'bot') {
      return $gjmapsAPI.$bottom;
    } else if ($pos === 'left') {
      return $gjmapsAPI.$left;
    } else if ($pos === 'right') {
      return $gjmapsAPI.$right;
    } else {
      return $gjmapsAPI.$top;
    }
  }

  function frontend() {

    $gjMapsDatabase = new gjMapsDB();

    //Writes the JS to the page, including POIs and categories
    $poi = json_encode($gjMapsDatabase->get_poi());
    echo '<script type="text/javascript">';
    echo 'var poi = ';
    print_r($poi);
    echo ';';

    $poi = json_encode($gjMapsDatabase->get_cat());
    echo 'var cat = ';
    print_r($poi);
    echo ';';

    $gj_poi_list = get_option('gj_poi_list');
    $center_lat = get_option('gj_center_lat');
    $center_lng = get_option('gj_center_lng');
    $gj_map_zoom = get_option('gj_map_zoom');
    $gj_map_styles = get_option('gj_map_styles');
    $gj_label_color = get_option('gj_label_color');

    // Strip slashes and remove whitespace
    $map_styles = stripslashes($gj_map_styles);
    $map_styles = preg_replace("/\s+/", "", $map_styles);

    echo 'var poi_list = '.($gj_poi_list ? $gj_poi_list : '0').';';
    echo 'var center_lat = '.($center_lat ? $center_lat : '34.0459231').';';
    echo 'var center_lng = '.($center_lng ? $center_lng : '-118.2504648').';';
    echo 'var map_zoom = '.($gj_map_zoom ? $gj_map_zoom : '14').';';
    echo 'var label_color = "'.($gj_label_color ? $gj_label_color : '0').'";';
    echo 'var map_styles = '.($gj_map_styles ? $map_styles : '0').';';
    echo '</script>';

  }

}
new gjMapsInject();