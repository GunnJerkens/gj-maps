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

    var i, len, markup = '';

    for (i = 0; i < cat.length; i++) {
      if (cat[i].hide_list != '1') {
        markup += markupCategoryList(cat[i]);
      }
    }

    $(".gjmaps-categories").append(markup);

    $(".gjmaps-category div[data-type='label']").click(function(event) {
      showCategory($(this));
    });

    if(settings.filter_load == 0) {
      showCategory($(".gjmaps-category[data-cat-id='all']"));
    }

    // Check if categories are loaded
    gjmapsEvents('gjmapsCatLoad', {'loaded': true});

    resizeCategories();

  }

  // Resizes our category <li> for responsive
  function resizeCategories() {
    var $cat = $('.gjmaps-category');

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

  function markupCategoryList(cat) {

    var markup, i, len, address, symbolPath, color, background, catCount;

    background = '';
    color = '';
    text = true;

    if (settings.label_color === "background") {
      if (cat.icon) {
        symbolPath = cat.icon.replace(/\/marker-/, '/symbol-');
        background = 'background-image: url(' + symbolPath + ');';
      } else {
        background = '';
      }
      color = 'background-color: ' + cat.color +';';
    } else if (settings.label_color === "text") {
      background = '';
      color = 'color: ' + cat.color + ';';
    } else if (settings.label_color === "icon") {
      symbolPath = cat.icon.replace(/\/marker-/, '/symbol-');
      background = 'background-image: url(' + symbolPath + ');';
      text = false;
    }

    if(text) {
      markup = '<li class="gjmaps-category" data-cat-id="' + cat.id + '">' +
        '<div style="' + background + color + '" class="gjmaps-label" data-type="label"><span>' + 
        cat.name + '</span></div><ul>';
    } else {
      markup = '<li class="gjmaps-category" data-cat-id="' + cat.id + '">' +
        '<div style="' + background + '" class="gjmaps-label" data-type="label"></div><ul>';
    }

    if (settings.poi_list == 1) {
      for (i = 0, len = poi.length; i < len; i++) {
        if (poi[i].cat_id === cat.id) {
          markup += '<li class="poi" data-poi-id="' + poi[i].id + '">';
          if ("1" == settings.poi_num) {
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

  function showCategory(el) {

    var mouse_scroll = settings.mouse_scroll,
        mouse_drag = settings.mouse_drag,
        catID, filterIndex;

    catElement = el.closest(".gjmaps-category");
    catID = catElement.attr("data-cat-id");


    if (catID === "all") {
      filter = [];
      $("[data-cat-id='all']").addClass("active");
      catElement.siblings(".gjmaps-category").removeClass("active");
      if (settings.poi_list == 1) {
        $(".gjmaps-category ul").slideDown(); 
      } // show all lists
    } else {
      $("[data-cat-id='all']").removeClass("active");
      catElement.siblings(".gjmaps-category").removeClass("active");
      $(".gjmaps-category[data-cat-id=" + catID + "]").addClass("active");
      if (settings.poi_list == 1) {
        catElement.siblings(".gjmaps-category").find("ul").slideUp();
        $("ul", catElement).slideDown(); // show this list
        $(".gjmaps-category[data-cat-id=" + catID + "]").slideDown();
      }
      filter = [catID];
    }

    // Check which category is clicked
    gjmapsEvents('gjmapsCatClick', {'category': catID});

    infoWindow.close();
    placeMarkers(mouse_scroll || mouse_drag);

    if (catID === "all") {
      map.panTo(mapOptions.center);
      map.setZoom(mapOptions.zoom);
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

          var poiCat = catIndexed[poi[i].cat_id],
              catOptions = ['filter_resist'],
              hasOptions = categoryOptionCheck(catOptions);

          position = new google.maps.LatLng(poi[i].lat, poi[i].lng);

          if("1" != settings.poi_num || hasOptions.indexOf(poi[i].cat_id) > -1) {

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

          } else {
            
            poi[i].marker = new MarkerWithLabel({
              position: position,
              draggable: false,
              map: map,
              icon: settings.poi_icon,
              labelContent: poi[i].num,
              labelAnchor: new google.maps.Point(12,0),
              labelClass: "gj-maps-marker-label",
              labelStyle: {
                "width": "25px",
                "height": "25px",
                "paddingTop": "4px",
                "color": "white",
                "background": poiCat.color,
                "border-radius": "50%"
              }
            });

          }


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

  // Filters the map on load to only show filter resists categories
  function filterLoad() {
    filter = [];
    for(var i = 0; i < cat.length; i++) {
      if(cat[i]['filter_resist'] != null) {
        filter.push(cat[i]['id']);
      }
    }
    placeMarkers();
  }

  // Check which categories have options enabled
  function categoryOptionCheck(options) {
    var hasOptions = [];

    for (var i = 0; i < cat.length; i++) {
      for (var j = 0; j < options.length; j++) {
        if (cat[i][options[j]] == true) {
          hasOptions.push(cat[i]['id']);
        }
      };
    };

    return hasOptions;
  }

  // Instantiates the map
  function initMap() {
    var center_lat = settings.center_lat,
        center_lng = settings.center_lng,
        zoom = Math.floor(settings.map_zoom),
        mouse_scroll = settings.mouse_scroll,
        mouse_drag = settings.mouse_drag;

    mapOptions = {
      zoom: zoom,
      center: new google.maps.LatLng(0, 0),
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      styles: (settings.map_styles === '0' ? '' : jQuery.parseJSON(settings.map_styles)),
      draggable: (mouse_drag === '0') ? true : false,
      scrollwheel: (mouse_scroll === '0') ? true : false
    };

    if(settings.center_lat && settings.center_lng) {
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

    if(settings.filter_load) {
      filterLoad();
    } else {
      placeMarkers(!center_lat || !center_lng);
    }

    setupPOILists();
  }

  indexPOIData();
  google.maps.event.addDomListener(window, 'load', initMap);

  // Handles click functions on the poi list items
  $('div.gjmaps-wrapper').on('click', 'li.poi', function() {
    var id = $(this).data('poi-id'),
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

  // Custom Events
  function gjmapsEvents(name, param) {
    $.event.trigger({ type: name, 'gjmaps': param });
  }

});
