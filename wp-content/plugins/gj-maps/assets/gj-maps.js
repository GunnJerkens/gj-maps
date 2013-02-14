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

		var cat_info = [];
		for (var i = 0; i < cat.length; i++) {
			cat_info[cat[i].id] = {
				name: cat[i].name,
				color: cat[i].color
			}
		};

		var deferred = $.Deferred();

		$.each(poi, function(i, val){

			var cat_id = parseInt(this.cat_id, 10);
			var curr_cat = cat_info[cat_id];
			var cat_color = curr_cat.color.slice(1);

			if (this.lat !== '0' && this.lng !== '0') {

				var myContent  = '<div class="infoContentWrapper">';

					myContent += '<h3>'+ this.name +'</h3>' + '<p>'+ curr_cat.name +'</p>';

					myContent += (this.url) ? '<p><a href="'+ this.url +'" target="_blank" rel="nofollow">Visit Website</a><br />' : '<p>';

					myContent += this.address +'<br />'+ this.city +', ' + this.state + ' ' + this.zip;

					myContent += (this.phone)? '<br />'+this.phone +'</p>': '</p>';

					myContent += '</div>';

					
				if (cats) {

					if (this.cat == cats) {

						map.addMarker({

							lat: this.lat,

							lng: this.lng,

							title: this.name,

							infoWindow: {

								content: myContent

							},

							icon: 'http://chart.apis.google.com/chart?chst=d_map_pin_icon&chld=home%7C' + cat_color

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

							icon: 'http://chart.apis.google.com/chart?chst=d_map_pin_icon&chld=home%7C' + cat_color


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