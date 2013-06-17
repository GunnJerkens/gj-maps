function GJMap(mapOptions) {

	var self = this;

	self.buildMarkers = function(cat) {

		var infowindow = new google.maps.InfoWindow();

		var markers = [];

		var marker, i;

		// ICONS
		//var iconBase = 'https://maps.google.com/mapfiles/kml/shapes/';

		if (cat) {

			for (i = 0; i < poi.length; i++) {

				if (poi[i].category == cat || poi[i].category == 'community') {
					marker = new google.maps.Marker({
						position: new google.maps.LatLng(poi[i]['lat'], poi[i]['lng']),
						icon: poi[i]['icon'],
						map: self.map
					});

					markers.push(marker);

					google.maps.event.addListener(marker, 'click', (function(marker, i) {
				        return function() {
				          var content = '<h4>'+poi[i]['name']+'</h4>';
				          content += '<p>'+poi[i]['address']+'<br />';
				          content += poi[i]['phone']+'<br />';
				          infowindow.setContent(content);
				          infowindow.open(self.map, marker);
				        }
			      	})(marker, i));
				}
			}

		} else {

			for (i = 0; i < poi.length; i++) {
				marker = new google.maps.Marker({
					position: new google.maps.LatLng(poi[i]['lat'], poi[i]['lng']),
					icon: poi[i]['icon'],
					map: self.map
				});

				markers.push(marker);

			/*	google.maps.event.addListener(marker, 'click', (function(marker, i) {
			        return function() {
			          var content = '<h4>'+poi[i]['name']+'</h4>';
			          content += '<p>'+poi[i]['address']+'<br />';
			          content += poi[i]['phone']+'<br />';
			          infowindow.setContent(content);
			          infowindow.open(self.map, marker);
			        }
		      	})(marker, i)); */
			}

		}
	};

	self.init = function(mapOptions) {

		delete map;

		self.map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

		self.buildMarkers();

		//Filter by categories

		$('#map_categories li').click(function() {
			for (var i = 0; i < markers.length; i++ ) {
				markers[i].setMap(null);
			} 
			buildMarkers($(self).attr('data-cat'));
		});
	};
}