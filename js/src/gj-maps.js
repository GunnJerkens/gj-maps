jQuery(document).ready(function($) {

  var map, filter, mapOptions, mapBounds, markerBounds, poiIndexed, catIndexed, iconAnchor, infoWindow;

  iconAnchor = new google.maps.Point(5, 33);

  function indexPOIData() {

    var i, len;

    poiIndexed = {};
    for (i = 0, len = poi.length; i < len; i++) {
      poiIndexed[poi[i].id] = poi[i];
    }

    catIndexed = {};
    for (i = 0, len = cat.length; i < len; i++) {
      catIndexed[cat[i].id] = cat[i];
    }

  }

  function setupPOILists() {

    var i, len;

    var markup = '';

    for (i = 0; i < cat.length; i++) {
      if (cat[i].hide_list != '1') {
        markup += markupCategoryList(cat[i]);
      }
    }

    $(".gjmaps-categories").append(markup);

    $(".gjmaps-category div[data-type='label']").click(function(event) {

      var catID, filterIndex;

      catElement = $(this).closest(".gjmaps-category");
      catID = catElement.attr("data-cat-id");

      if (catID === "all") {
        filter = [];
        $("[data-cat-id='all']").addClass("active");
        catElement.siblings(".gjmaps-category").removeClass("active");
        if (poi_list === 1) { 
          $(".gjmaps-category ul").slideDown(); 
        } // show all lists
      } else {
        $("[data-cat-id='all']").removeClass("active");
        catElement.siblings(".gjmaps-category").removeClass("active");
        $(".gjmaps-category[data-cat-id=" + catID + "]").addClass("active");
        if (poi_list === 1) {
          catElement.siblings(".gjmaps-category").find("ul").slideUp();
          $("ul", catElement).slideDown(); // show this list
          $(".gjmaps-category[data-cat-id=" + catID + "]").slideDown();
        }
        filter = [catID];
      }

      infoWindow.close();
      placeMarkers();

      if (catID === "") {
        map.panTo(mapOptions.center);
        map.setZoom(mapOptions.zoom);
      }

    });

    function resizeCategories() {

      var $cat;

      $cat = $('.gjmaps-category');

      if($(window).innerWidth() > 768) {

        if($cat.length > 2) {
          percent = ((100-($cat.length*2))/$cat.length) + '%';
        } else {
          percent = '50%';
        }

      } else {
        percent = '100%';
      }

      $cat.css('width',percent);
    }

    resizeCategories();

  }

  function markupCategoryList(cat) {

    var markup, i, len, address, symbolPath, color, background, catCount;

    background = '';
    color = '';

    if (label_color === "background") {

      if (cat.icon) {
        symbolPath = cat.icon.replace(/\/marker-/, '/symbol-');
        background = 'background-image: url(' + symbolPath + ');';
      } else {
        background = '';
      }

      color = 'background-color: ' + cat.color +';';

    } else if (label_color === "text") {

      background = '';
      color = 'color: ' + cat.color + ';';

    }

    markup = '<li class="gjmaps-category" data-cat-id="' + cat.id + '">' +
      '<div style="' + background + color + '" class="gjmaps-label" data-type="label"><span>' + 
      cat.name + '</span></div>' + '<ul>';

    if (poi_list === 1) {

      for (i = 0, len = poi.length; i < len; i++) {
        if (poi[i].cat_id == cat.id) {
          markup += '<li class="poi" data-poi-id="' + poi[i].id + '">';
          if (poi_number) {
            markup += '<span>' + poi[i].num + ' </span>';
          }
          markup += poi[i].name + '</li>';
        }
      }

    }

    markup += '</ul>' +
      '</li>';

    return markup;
  }

  function showPOIInfo(poi) {

    var content, linkName, $header, mapTop;

    content = '<div class="poi-info" style="overflow:hidden;">' +
      '<h4>'+poi.name+'</h4>';

    if (poi.description) {
      content += '<div class="description">' + poi.description + '</div>';
    }

    content += '<div class="address">'+poi.address + '<br>' +
      poi.city + ', ' + poi.state + ' ' + poi.zip +
      '</div>' +
      '<div class="contact">';

    if (poi.phone) {
      content += poi.phone;
      if (poi.url) content += '<br>';
    }

    if (poi.url) {
      linkName = poi.url.replace(/^https?:\/\/|\/$/g, '');
      content += '<a href="'+poi.url+'" target="_blank">'+linkName+'</a>';
    }

    content += '</div>' +
      '</div>';

    infoWindow.setContent(content);
    infoWindow.open(map, poi.marker);
    $header = $("header");
    mapTop = $("#map-canvas").offset().top - $header.height() - $header.position().top;

    if ($(document.body).scrollTop() > mapTop) {
      $(document.body).animate({scrollTop: mapTop}, 300);
    }

  }

  function placeMarkers(forceFit) {

    var i, len, isMatch, position, markerOptions;

    markerBounds = new google.maps.LatLngBounds();

    for (i = 0, len = poi.length; i < len; i++) {
      isMatch = (
        !filter ||
        (
          typeof filter == "object" &&
          (
            !filter.length ||
            filter.indexOf(poi[i].cat_id) !== -1 ||
            catIndexed[poi[i].cat_id].filter_resist == '1'
          )
        ) ||
        (
          (
            typeof filter == "string" ||
            typeof filter == "number"
          ) &&
          poi[i].id == filter
        )
      );

      if (typeof poi[i].marker !== "undefined") {

        if (isMatch) {
          poi[i].marker.setMap(map);
          markerBounds.extend(poi[i].marker.getPosition());
        } else {
          poi[i].marker.setMap(null);
        }

      } else if(isMatch) {

        if (Number(poi[i].lat) && Number(poi[i].lng)) {

          var poiCat = catIndexed[poi[i].cat_id];

          position = new google.maps.LatLng(poi[i].lat, poi[i].lng);

          markerOptions = {
            position: position,
            map: map,
            title: poi[i].name
          };

          if (poiCat) {
            markerOptions.icon = {
              url: poiCat.icon,
              anchor: iconAnchor,
            };
          }

          poi[i].marker = new google.maps.Marker(markerOptions);

          google.maps.event.addListener(poi[i].marker, 'click', (function(i) { return function() {
            showPOIInfo(poi[i]);
          }})(i));

          markerBounds.extend(position);
        }

      }

    }

    mapBounds = map.getBounds();

    if (forceFit || (mapBounds && !mapBounds.intersects(markerBounds))) {
      if (markerBounds.toString() != "((1, 180), (-1, -180))") {
        map.fitBounds(markerBounds);
      }
    }

  }

  function initMap() {

    var styles;

    if(map_styles === '0') {
      map_styles = '';
    }

    mapOptions = {
      zoom: map_zoom,
      center: new google.maps.LatLng(0, 0),
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      styles: map_styles
    };

    if (center_lat && center_lng) {
      mapOptions.center = new google.maps.LatLng(center_lat, center_lng)
    }

    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

    google.maps.event.addDomListener(window, "resize", function() {
      var center = map.getCenter();
      google.maps.event.trigger(map, "resize");
      map.setCenter(center);
    });

    filter = [];

    infoWindow = new google.maps.InfoWindow();
    placeMarkers(!center_lat || !center_lng);
    setupPOILists();

  }

  indexPOIData();

  google.maps.event.addDomListener(window, 'load', initMap);

  // This registers our click event on the POI list items
  $('div.gjmaps-wrapper').on('click', 'li.poi', function() {
    var id, marker;

    id = $(this).data('poi-id');
    marker = false;

    for(i = 0; poi.length > i; i++) {
      if(poi[i].id == id) {
        marker = poi[i];
        break;
      }
    }

    if(marker != false) {
      showPOIInfo(marker);
    }
  });

});
