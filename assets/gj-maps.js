function initMap() {

  var mapOptions = {
    center: new google.maps.LatLng(center_lat,center_lng),
    zoom: 14,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

  var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

  var markers = [];

  function buildMarkers(cat_name) {

    var infowindow = new google.maps.InfoWindow();

    var marker, i;

    if (cat_name) {

      for (i = 0; i < poi.length; i++) {
      	var cat_id = Number(poi[i]['cat_id']);

        if (cat[cat_id]['name'] == cat_name || poi[i].category == 'community') {
          marker = new google.maps.Marker({
            position: new google.maps.LatLng(poi[i]['lat'], poi[i]['lng']),
            icon: cat[poi[i]['cat_id']].icon,
            map: map
          });

          markers.push(marker);

          google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
              var content = '<div id="hook"><h4>' + poi[i]['name'] + '</h4>';
              content += '<p>' + poi[i]['address'] + '<br />';
              content += poi[i]['phone'] + '</div>';
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
          icon: cat[poi[i]['cat_id']].icon,
          map: map
        });

        markers.push(marker);

        google.maps.event.addListener(marker, 'click', (function(marker, i) {
          return function() {
            var content = '<div id="hook"><h4>' + poi[i]['name'] + '</h4>';
            content += '<p>' + poi[i]['address'] + '<br />';
            content += poi[i]['phone'] + '</div>';
            infowindow.setContent(content);
            infowindow.open(map, marker);
            var l = $('#hook').parent().parent().parent().siblings();
          }
        })(marker, i));
      }

    }
  }

  cat.unshift({});

  buildMarkers();

  $('#map_categories li').click(function() {
    for (var i = 0; i < markers.length; i++) {
      markers[i].setMap(null);
    }
    buildMarkers($(this).attr('data-cat'));
  });
}
