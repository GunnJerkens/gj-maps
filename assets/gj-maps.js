$(document).ready(function() {

	// Add Styling Function

	GMaps.prototype.addStyle = function(options){       

		var styledMapType = new google.maps.StyledMapType(options.styles, options.styledMapName);

		this.map.mapTypes.set(options.mapTypeId, styledMapType);

	};

	

	GMaps.prototype.setStyle = function(mapTypeId){     

		this.map.setMapTypeId(mapTypeId);

	};

	

	// Map Styles

	var mapStyles = [

	  {

		featureType: "road",

		elementType: "geometry.fill",

		stylers: [

		  { color: "#ffffff" }

		]

	  },{

		featureType: "road",

		elementType: "labels.text.fill",

		stylers: [

		  { color: "#6d634c" }

		]

	  },{

		featureType: "road",

		elementType: "labels.text.stroke",

		stylers: [

		  { color: "#ffffff" },

		  { visibility: "on" }

		]

	  },{

		featureType: "poi.park",

		elementType: "geometry.fill",

		stylers: [

		  { color: "#99954c" },

		  { lightness: 70 }

		]

	  },{

		featureType: "poi.business",

		elementType: "geometry",

		stylers: [

		  { color: "#99944c" },

		  { lightness: 70 }

		]

	  },{

		featureType: "administrative.land_parcel",

		elementType: "geometry",

		stylers: [

		  { visibility: "off" }

		]

	  }

	];

	

	// Init Map
if (! center_lat) {
	var center_lat = 0;
}
if (! center_lng) {
	var center_lng = 0;
}
	var map = new GMaps({

		div: '#map',

		zoom: 16,

		lat: center_lat,

		lng: center_lng,

		mapTypeControl: false,

		scaleControl: false,

		panControl: false,

		zoomControl: true,

		zoomControlOptions: {

			style: google.maps.ZoomControlStyle.SMALL

		}

	});

	

	// Init Styles

	map.addStyle({

		styledMapName:"Oakhurst",

		styles: mapStyles,

		mapTypeId: "map_style"  

	});

	

	map.setStyle("map_style");


	// Build Markers

	var buildMarkers = function(cats){

		//Here, I'm creating a new object because the one returned from the WPDB query isn't sturctured to allow me to access a category by 
		//its ID.  I'm not sure if there's a better way to do this.
		var cat_info = [];
		for (var i = 0; i < cat.length; i++) {
			cat_info[cat[i].id] = {
				name: cat[i].name,
				color: cat[i].color,
				icon: cat[i].icon
			}
		};

		var deferred = $.Deferred();

		$.each(poi, function(i, val){

			//Set the current category based on its ID.
			var cat_id = parseInt(this.cat_id, 10);
			var curr_cat = cat_info[cat_id];

			if (this.lat !== '0' && this.lng !== '0') {

				var myContent  = '<div class="infoContentWrapper">';

					myContent += '<h3>'+ this.name +'</h3>' + '<p>'+ curr_cat.name +'</p>';

					myContent += (this.url) ? '<p><a href="'+ this.url +'" target="_blank" rel="nofollow">Visit Website</a><br />' : '<p>';

					myContent += this.address +'<br />'+ this.city +', ' + this.state + ' ' + this.zip;

					myContent += (this.phone)? '<br />'+this.phone +'</p>': '</p>';

					myContent += '</div>';

					
				if (cats) {

					if (curr_cat.name == cats) {

						map.addMarker({

							lat: this.lat,

							lng: this.lng,

							title: this.name,

							infoWindow: {

								content: myContent

							},

							icon: curr_cat.icon

						});

					}

				} else {

						map.addMarker({

							lat: this.lat,

							lng: this.lng,

							title: this.name,

							infoWindow: {

								content: myContent

							},

							icon: curr_cat.icon


						});

				}

				

				if(i == poi.length - 1){

					deferred.resolve();

				}

			}

			});
		

		deferred.done(function(){

			map.fitZoom();

		});

	}

	

	buildMarkers();

	

	

	$('.map-category').click( function(e){

		e.preventDefault();

		$('.map-category').removeClass('active');
		$(this).addClass('active');

		var target = $(this).attr('data-target');

		map.removeMarkers();

		if(target == ''){

			buildMarkers();

		} else {

			buildMarkers(target);

		}

	});

	map.fitZoom();

});