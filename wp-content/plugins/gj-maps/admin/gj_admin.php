	<?php

	require_once('db.php');

		if($_POST['gj_hidden'] == 'Y') {
			//Form data sent
				global $post;

				if ($_POST['id']) {

					if ($_POST['delete']) {
						//Delete Selected POI
						deletePOI($_POST['id']);
					} else {
						//Update existing POI
						$poi = array();
						foreach ($_POST as $key=>$value) {
							if ($key !== 'gj_hidden') {
								$poi[$key] = $value;
							}
						}
						editPOI($poi);
					}

				} else if ($_POST['geocode']) {
					//Update geocodes
			         global $wpdb;

			         if ( ! $GJ_api ) {
			            $GJ_api = new GJ_api();
			         }
			         $query = $GJ_api->gj_get_POI('ARRAY_A', 'lat=0');

			         foreach ($query as $poi) {
			         	$address = urlencode($poi["address"].', '.$poi['city'].', '.$poi['state'].' '.$poi['zip']);
						$url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
				    	$url .= '&address='.$address;

				    	$response = wp_remote_get( $url );
						if( is_wp_error( $response ) ) {
						   $error_message = $response->get_error_message();
						   echo "Something went wrong: $error_message";
						}

						$response2 = json_decode($response['body']);
					    $location = $response2->results[0]->geometry->location;
					    $poi['lat'] = $location->lat;
					    $poi['lng'] = $location->lng;
					    editPOI($poi);
			         }

				} else {
					//Add new POI
					$poi = array();
					foreach ($_POST as $key=>$value) {
						if ($key !== 'gj_hidden') {
							$poi[$key] = $value;
						}
					}

					$address = urlencode($poi["address"].', '.$poi['city'].', '.$poi['state'].' '.$poi['zip']);
					$url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
			    	$url .= '&address='.$address;

			    	$response = wp_remote_get( $url );
					if( is_wp_error( $response ) ) {
					   $error_message = $response->get_error_message();
					   echo "Something went wrong: $error_message";
					}

					$response2 = json_decode($response['body']);
				    $location = $response2->results[0]->geometry->location;
				    $poi['lat'] = $location->lat;
				    $poi['lng'] = $location->lng;

					$POIs = array($poi);
					print_r($POIs);
					savePOI($POIs);
				}

		}

		$GJ_api = new GJ_poi();
		$poi = $GJ_poi->gj_get_POI();

		$GJ_cat = new GJ_cat();
		$cat = $GJ_cat->gj_get_cat();

		?>
		<div class="wrap">
			<?php    echo "<h2>" . __( 'GJ Maps Points Of Interest', 'gj_trdom' ) . "</h2>"; ?>

			<h4>Add New</h4>
				<form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
					<input type="hidden" name="gj_hidden" value="Y"/>
					<input type="text" name="name" placeholder="Name"/>
					<select name="cat_id">
						<?php 
						foreach ($cat as $key=>$value) {
							echo "<option value='$value->id'>$value->name</option>";
						}

						?>
					</select>
					<input type="text" name="address" placeholder="Street Address"/>
					<input type="text" name="city" placeholder="City"/>
					<input type="text" name="state" placeholder="State"/>
					<input type="text" name="zip" placeholder="Zip/Postal Code"/>
					<input type="text" name="country" placeholder="Country"/>
					<input type="text" name="phone" placeholder="Phone Number"/>
					<input type="text" name="url" placeholder="URL"/>

					<p class="submit"><input type="submit" value="<?php _e('Add POI', 'gj_trdom' ) ?>" /></p>
				</form>

			<h4>Find Geocodes</h4>
				<form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
					<input type="hidden" name="gj_hidden" value="Y"/>
					<input type="hidden" name="geocode" value="1"/>
					<p class="submit"><input type="submit" value="<?php _e('Find Geocodes', 'gj_trdom' ) ?>" /></p>
				</form>

			<h4>Edit POIs</h4>

				<?php

				foreach ($poi as $index=>$object) {
				?>
					<form name="gj_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
					<input type="hidden" name="gj_hidden" value="Y"/>
					<input type="hidden" name="id" value="<?php echo $object->id; ?>"/>

					<label for="name">Name: 
					<input type="text" name="name" placeholder="Name" value="<?php echo $object->name; ?>"/>
					</label>

					<label for="cat_id">Category: 
					<select name="cat_id">
						<?php 
						foreach ($cat as $key=>$value) {
							
							if ( $object->cat_id == $value->id ) {
								echo "<option value='$value->id' selected>$value->name</option>";
							} else {
								echo "<option value='$value->id'>$value->name</option>";
							}
						}

						?>
					</select>
					</label>

					<label for="address">Street Address: 
					<input type="text" name="address" placeholder="Street Address" value="<?php echo $object->address; ?>"/>
					</label>

					<label for="city">City: 
					<input type="text" name="city" placeholder="City" value="<?php echo $object->city; ?>"/>
					</label>

					<label for="state">State: 
					<input type="text" name="state" placeholder="State" value="<?php echo $object->state; ?>"/>
					</label>

					<label for="zip">Zip/Postal Code: 
					<input type="text" name="zip" placeholder="Zip/Postal Code" value="<?php echo $object->zip; ?>"/>
					</label>

					<label for="country">Country: 
					<input type="text" name="country" placeholder="Country" value="<?php echo $object->country; ?>"/>
					</label>

					<label for="phone">Phone Number: 
					<input type="text" name="phone" placeholder="Phone Number" value="<?php echo $object->phone; ?>"/>
					</label>

					<label for="url">URL: 
					<input type="text" name="url" placeholder="URL" value="<?php echo $object->url; ?>"/>
					</label>

					<label for="lat">Latitude: 
					<input type="text" name="lat" placeholder="Latitude" id="lat<?php echo $object->id; ?>" value="<?php echo $object->lat; ?>"/>
					</label>

					<label for="lng">Longitude: 
					<input type="text" name="lng" placeholder="Longitude" id="lng<?php echo $object->id; ?>" value="<?php echo $object->lng; ?>"/>
					</label>

					<br />

					<label for="delete">Delete this POI? : 
					<input type="checkbox" name="delete"/>
					</label>

					<br />
					<input type="submit" name="Submit" value="<?php _e('Submit Changes', 'gj_trdom' ) ?>" />

					</form>

				<br /><hr /><br />
				<?php } ?>

		</div>