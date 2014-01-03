<?php

class GJ_API_Endpoint{
  
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
    if(isset($wp->query_vars['gjmaps_api'])){
      $this->send_response();
      exit;
    }
  }
  
  /** Response Handler
  * This sends a JSON response to the browser
  */
  protected function send_response(){
    $GJ_api = new GJ_api();
    $GJ_cat = new GJ_cat();

    $gj_poi_list = get_option('gj_poi_list') ? $gj_poi_list : '0';
    $center_lat = get_option('gj_center_lat') ? $center_lat : 'null';
    $center_lng = get_option('gj_center_lng') ? $center_lng : 'null';

    $data = array(
      'poi' => $GJ_api->gj_get_POI(),
      'cat' => $cat = $GJ_cat->gj_get_cat(),
      'poi_list' => $gj_poi_list,
      'center_lat' => $center_lat,
      'center_lng' => $center_lng
    );

    header('content-type: application/json; charset=utf-8');
    header("access-control-allow-origin: *");
    echo json_encode($data);
  }
}
new GJ_API_Endpoint();