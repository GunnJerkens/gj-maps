<?php

class gjMapsAPI {

  /** Hook WordPress
  * @return void
  */
  public function __construct(){
    add_filter('query_vars', array($this, 'add_query_vars'), 0);
    add_action('parse_request', array($this, 'sniff_requests'), 0);
    add_action('init', array($this, 'add_endpoint'), 0);
  }

  /** Add public query vars
  * @param array $vars List of current public query vars
  * @return array $vars
  */
  public function add_query_vars($vars){
    $vars[] = 'gjmaps_api';
    return $vars;
  }

  /** Add API Endpoint
  * @return void
  */
  public function add_endpoint(){
    add_rewrite_rule('^gjmaps_api/','index.php?gjmaps_api','top');
  }

  /** Sniff Requests
  * This is where we hijack all API requests
  *   If $_GET['__api'] is set, we kill WP and serve up pug bomb awesomeness
  * @return die if API request
  */
  public function sniff_requests(){
    global $wp;
    if(isset($wp->query_vars['gjmaps_api'])) {
      $mapID = $wp->query_vars['gjmaps_api'];
      $this->send_response($mapID);
      exit;
    }
  }

  /** clean icon link categories
  * We will serve up the full URL on the api, make it relative and make sure it is from this site.
  */
  protected function make_cat_icons_relative($cats){
    foreach ($cats as $key => $value) {
      $value->icon = get_home_url() . wp_make_link_relative($value->icon);
    }
    return $cats;
  }

  /** Response Handler
  * This sends a JSON response to the browser
  */
  protected function send_response($mapID){
    $db = new gjMapsDB();

    $data = array(
      'poi' => $db->getPoi($mapID, 0, 999, 'OBJECT', get_option('gj_maps_poi_alpha_list')),
      'cat' => $this->make_cat_icons_relative($db->getCategories($mapID))
    );
    //wp_make_link_relative(
    header('content-type: application/json; charset=utf-8');
    header("access-control-allow-origin: *");
    echo json_encode($data);
  }
}
