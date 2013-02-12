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

		var deferred = $.Deferred();

		

		$.each(poi, function(i, val){

			var myContent  = '<div class="infoContentWrapper">';

				myContent += '<h3>'+ this.name +'</h3>' + '<p>'+ this.cat +'</p>';

				myContent += (this.url) ? '<p><a href="'+ this.url +'" target="_blank" rel="nofollow">Visit Website</a><br />' : '<p>';

				myContent += this.address +'<br />'+ this.city +', ' + this.state + ' ' + this.zip;

				myContent += (this.phone)? '<br />'+this.phone +'</p>': '</p>';

				myContent += '</div>';

			

			if((typeof cats === 'object') && ($.inArray(this.cat_slug, cats) !== -1)){

				map.addMarker({

					lat: this.lat,

					lng: this.lng,

					title: this.title2,

					infoWindow: {

						content: myContent

					},

					icon: this.icon

				});

			} else if (typeof cats !== 'object') {

				map.addMarker({

					lat: this.lat,

					lng: this.lng,

					title: this.title,

					infoWindow: {

						content: myContent

					},

					icon: this.icon

				});

			}

			

			if(i == poi.length - 1){

				deferred.resolve();

			}

		});

		

		deferred.done(function(){

			map.fitZoom();

		});

	}

	

	buildMarkers();

	

	

	$('#map-categories').on('click', 'a', function(e){

		e.preventDefault();

		var $myLink = $(e.currentTarget);

		var targets = [$myLink.attr('data-target').split('#')[1], 'location'];

		map.removeMarkers();

		

		if(targets[0]){

			buildMarkers(targets);

		} else {

			buildMarkers();

		}

	});

});