function initMaps() {

	var mapOptions = {
		center: new google.maps.LatLng(37.3921265,-121.86583109999998),
		zoom: 13,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

	var markers = [];

	function buildMarkers(cat) {

		var infowindow = new google.maps.InfoWindow();

		var marker, i;

		// ICONS
		//var iconBase = 'https://maps.google.com/mapfiles/kml/shapes/';

		if (cat) {

			for (i = 0; i < poi.length; i++) {

				if (poi[i].category == cat || poi[i].category == 'community') {
					marker = new google.maps.Marker({
						position: new google.maps.LatLng(poi[i]['lat'], poi[i]['lng']),
						icon: poi[i]['icon'],
						map: map
					});

					markers.push(marker);

					google.maps.event.addListener(marker, 'click', (function(marker, i) {
				        return function() {
				          var content = '<h4>'+poi[i]['title']+'</h4>';
				          content += '<p>'+poi[i]['address']+'<br />';
				          content += poi[i]['phone']+'<br />';
				          infowindow.setContent(content);
				          infowindow.open(map, marker);
				        }
			      	})(marker, i));
				}
			}

		} else {

			for (i = 0; i < poi.length; i++) {
				marker = new google.maps.Marker({
					position: new google.maps.LatLng(poi[i]['lat'], poi[i]['lng']),
					icon: poi[i]['icon'],
					map: map
				});

				markers.push(marker);

				google.maps.event.addListener(marker, 'click', (function(marker, i) {
			        return function() {
			          var content = '<h4>'+poi[i]['title']+'</h4>';
			          content += '<p>'+poi[i]['address']+'<br />';
			          content += poi[i]['phone']+'<br />';
			          infowindow.setContent(content);
			          infowindow.open(map, marker);
			        }
		      	})(marker, i));
			}

		}
	}

	buildMarkers();


	//Filter by categories

	$('#map_categories li').click(function() {
		for (var i = 0; i < markers.length; i++ ) {
			markers[i].setMap(null);
		} 
		buildMarkers($(this).attr('data-cat'));
	});
}