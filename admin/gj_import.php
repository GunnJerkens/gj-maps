<?php

ini_set('auto_detect_line_endings',TRUE);

require_once('db.php');

if(isset($_POST['gj_hidden']) && $_POST['gj_hidden'] == 'Y') {
	//Form data sent
	global $post;
	$uploadedfile = $_FILES['gj_upload'];
	$row = 1;
	$poi = array();
	if (($handle = fopen($uploadedfile['tmp_name'], "r")) !== FALSE) {
		while (($data = fgetcsv($handle, ",")) !== FALSE) {
			array_push($poi, $data);
		}
		fclose($handle);
	}

var_dump($data);
echo "\nend $data";
var_dump($poi);
echo "\nend $poi";
var_dump($handle);
echo "\nend $handle";

	$labels = array();
	foreach ($poi[0] as $key=>$value) {
		$labels[$value] = $value;
	}
	$labels['lat'] = 'lat';
	$labels['lng'] = 'lng';

	foreach ($poi as $key=>$value) {
		array_push($value, null);
		array_push($value, null);
		$poi[$key] = array_combine($labels, $value);
	}
	unset($poi[0]);

	if ( !isset($GJ_cat) ) {
		$GJ_cat = new GJ_cat();
	}
	$cat = $GJ_cat->gj_get_cat('ARRAY_A');
	$cats = array();
	foreach ($cat as $key=>$value) {
		$cats[$value['name']] = new stdClass;
		$cats[$value['name']]->id = $value['id']; 
		$cats[$value['name']]->color = $value['color'];
	}
	$cat = (object) $cats;

	foreach ($poi as $key=>$value) {
		$address = urlencode($value["address"].', '.$value['city'].', '.$value['state'].' '.$value['zip']);
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
		$url .= '&address='.$address;

		$response = wp_remote_get( $url );
		if( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		} 
		echo $value['address'].' -- '.$value['name'].'<br />';
		$response2 = json_decode($response['body']);
		if(isset($response2->results[0])){
			$location = $response2->results[0]->geometry->location;
			$poi[$key]['lat'] = $location->lat;
			$poi[$key]['lng'] = $location->lng;
		} else {
			$poi[$key]['lat'] = 0;
			$poi[$key]['lng'] = 0;
		}

		if (isset($cats[$value['category']])) {
			$poi[$key]['cat_id'] = $cats[$value['category']]->id;
		} else {
			$poi[$key]['cat_id'] = 1;
		}
		unset($poi[$key]['category']);
	}
	savePOI($poi);

	echo '<h4>Your POIs have been uploaded.</h4>';





} else {
?>
		<div class="wrap">
		<?php    echo "<h2>" . __( 'GJ Maps - Import CSV', 'gj_trdom' ) . "</h2>"; ?>
		<form name="gj_form" method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="gj_hidden" value="Y">
		<?php    echo "<h4>" . __( 'Upload CSV', 'gj_trdom' ) . "</h4>"; ?>
		<p><?php _e("File: " ); ?><input type="file" name="gj_upload" value="<?php echo $upload; ?>" size="20"></p>
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Upload CSV', 'gj_trdom' ) ?>" />
		</p>
		<p>Include columns for: name, category, address, city, state, zip, country, phone, url.<p>
		</form>
		</div>
<?php
}
?>
