/**
 * GJ-Maps JS
 *
 * @description: Frontend google maps rendering Wordpress plugin
 */

jQuery(document).ready(function($) {

  var gjMaps = new GJMaps();

  google.maps.event.addDomListener(window, 'load', function(){gjMaps.initMap()});

  /**
   * @name GJMaps
   * @class This class creates a GJMap with initial properties.
   * @global {array} poi Points of interest array.
   * @global {object} settings Settings object.
   * @global {object} google Google object.
   * @global {array} cat Category array.
   * @property {google.maps.Map} [map] Our instance of the google maps Map object.
   * @property {object} [mapOptions] The initial options for our map.
   * @property {object} [catIndexed] Holds our id indexed categories.
   * @property {google.maps.InfoWindow} [infoWindow] Our instance of the google maps InfoWindow object.
   * @property {array} [filter] Array used to filter the POIs by category.
   */
  function GJMaps() {
     this.map;
     this.mapOptions = {
          zoom: Math.floor(settings.map_zoom),
          maxZoom: Math.floor(settings.max_zoom),
          center: new google.maps.LatLng(0, 0),
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          styles: (settings.map_styles === '0' ? '' : jQuery.parseJSON(settings.map_styles)),
          draggable: (settings.mouse_drag === '0') ? true : false,
          scrollwheel: (settings.mouse_scroll === '0') ? true : false
        };
      this.catIndexed = {};
      this.infoWindow = new google.maps.InfoWindow();
      this.filter = [];
  }

  /**
   * Initilaizes google map and POI with data.
   */
  GJMaps.prototype.initMap = function() {

    this.indexCatData();

    this.initClickEvents();

    if(settings.center_lat && settings.center_lng) {
      this.mapOptions.center = new google.maps.LatLng(settings.center_lat, settings.center_lng)
    }

    this.map = new google.maps.Map(document.getElementById('map-canvas'), this.mapOptions);

    google.maps.event.addDomListener(window, "resize", function() {
      var center = gjMaps.map.getCenter();
      google.maps.event.trigger(gjMaps.map, "resize");
      gjMaps.map.setCenter(center);
    });

    if(settings.filter_load) {
      this.filterLoad();
    } else {
      this.placeMarkers(settings.fit_bounds);
    }

    this.setupPOILists();
  }

  /**
   * Places markers on the map.
   * @param {string} forceFit Forces map to fit to this marker.
   */
   GJMaps.prototype.placeMarkers = function(forceFit) {
     var markerBounds = new google.maps.LatLngBounds();

     for (var i = 0, len = poi.length; i < len; i++) {
       var isMatch = (
         !this.filter ||
         (
           typeof this.filter == "object" &&
           (
             !this.filter.length ||
             this.filter.indexOf(poi[i].cat_id) !== -1 ||
             this.catIndexed[poi[i].cat_id].filter_resist == '1'
           )
         ) ||
         (
           (
             typeof this.filter == "string" ||
             typeof this.filter == "number"
           ) &&
           poi[i].id == this.filter
         )
       );

       if (typeof poi[i].marker !== "undefined") {
         if (isMatch) {
           poi[i].marker.setMap(this.map);
           markerBounds.extend(poi[i].marker.getPosition());
         } else {
           poi[i].marker.setMap(null);
         }
       } else if(isMatch) {

         if (Number(poi[i].lat) && Number(poi[i].lng)) {

           var poiCat = this.catIndexed[poi[i].cat_id],
               catOptions = ['filter_resist'],
               hasOptions = this.categoryOptionCheck(catOptions),
               position = new google.maps.LatLng(poi[i].lat, poi[i].lng);

           if("1" != settings.poi_num || hasOptions.indexOf(poi[i].cat_id) > -1) {

             var markerOptions = {
               position: position,
               map: this.map,
               title: poi[i].name
             };

             if (poiCat) {
               markerOptions.icon = {
                 url: poiCat.icon !== "" ? poiCat.icon : settings.poi_icon,
                 anchor: new google.maps.Point(5, 33),
               };
             }

             if(hasOptions.indexOf(poi[i].cat_id) > -1) {
               $.extend(markerOptions, { zIndex: 8675309 });
             }

             poi[i].marker = new google.maps.Marker(markerOptions);

           } else {

             poi[i].marker = new MarkerWithLabel({
               position: position,
               draggable: false,
               map: this.map,
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
             // get the center point of the infowindow
             var center = new google.maps.LatLng(poi[i].lat, poi[i].lng)
             // pan to this new point
             gjMaps.map.panTo(center);
             gjMaps.showPOIInfo(poi[i]);
           }})(i));

           markerBounds.extend(position);
         }
       }
     }

     var mapBounds = this.map.getBounds();

     if (forceFit == '1' || (mapBounds && !mapBounds.intersects(markerBounds))) {
       if (markerBounds.toString() != "((1, 180), (-1, -180))") {
         this.map.fitBounds(markerBounds);
       }
     }
   }

   /**
    * Indexes category data based on id.
    */
  GJMaps.prototype.indexCatData = function() {
     for (var i = 0, len = cat.length; i < len; i++) {
       this.catIndexed[cat[i].id] = cat[i];
     }
   }

   /**
    * Sets up POI lists.
    */
   GJMaps.prototype.setupPOILists = function() {
     var markup = '';

     for (var i = 0; i < cat.length; i++) {
       if (cat[i].hide_list != '1') {
         markup += this.markupCategoryList(cat[i]);
       }
     }

     $(".gjmaps-categories").append(markup);

     $(".gjmaps-category div[data-type='label']").click(function() {
       gjMaps.showCategoryByEl($(this));
     });

     if(settings.filter_load == 0) {
       this.showCategoryByEl($(".gjmaps-category[data-cat-id='all']"));
     }

     // Check if categories are loaded
     this.gjmapsEvents('gjmapsCatLoad', {'loaded': true});

     this.resizeCategories();
   }

   /**
    * Resizes our category <li> for responsive
    */
   GJMaps.prototype.resizeCategories = function() {
     var $cat = $('.gjmaps-category'), percent;

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

   /**
    * Returns markup for category and POI lists.
    * @param {object} cat Category data.
    */
   GJMaps.prototype.markupCategoryList = function(cat) {
     var markup, address, symbolPath, color, background, catCount;

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
       for (var i = 0, len = poi.length; i < len; i++) {
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

 /**
  * Creates and displays markup in infoWindow when a POI marker is clicked.
  * @param {object} poi Point of interest data.
  */
  GJMaps.prototype.showPOIInfo = function(poi) {
     var content, linkName, $pageTop, mapTop, phone;

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
       phone = poi.phone.replace(/[\.\(\)\-\s]/g, '');
       content += '<a href="tel:+1' + phone + '">' + poi.phone + '</a>';
       if (poi.url) content += '<br>';
     }

     if (poi.url) {
       linkName = poi.url.replace(/^https?:\/\/|\/$/g, '');
       content += settings.link_text ? '<a href="'+poi.url+'" target="_blank">'+settings.link_text+'</a>' : '<a href="'+poi.url+'" target="_blank">'+linkName+'</a>';
     }

     content += '</div>' +
       '</div>';

     this.infoWindow.setContent(content);
     this.infoWindow.open(this.map, poi.marker);
     $pageTop = $("body");
     mapTop = $("#map-canvas").offset().top - $pageTop.position().top;

     if ($(document.body).scrollTop() > mapTop) {
       $(document.body).animate({scrollTop: mapTop}, 300);
     }

     this.gjmapsEvents('gjmapsPOIInfo', {'id': poi.id, 'cat_id': poi.cat_id});
   }

 /**
  * Shows POI list/markers based on category.
  * @param {object} el Category list element.
  */
  GJMaps.prototype.showCategoryByEl = function(el) {
     var catElement = el.closest(".gjmaps-category"),
      catID = catElement.attr("data-cat-id"),
        filterIndex;

     if (catID === "all") {
       this.filter = [];
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
       this.filter = [catID];
     }

     // Check which category is clicked
     this.gjmapsEvents('gjmapsCatClick', {'category': catID});

     this.infoWindow.close();

     this.placeMarkers(settings.fit_bounds);

     if (catID === "all" && !settings.fit_bounds) {
       this.map.panTo(this.mapOptions.center);
       this.map.setZoom(this.mapOptions.zoom);
     }
   }

   /**
    * Shows category based on array being passed.
    * @param {array} arr Category array.
    */
  GJMaps.prototype.showCategoryByArr = function(arr) {
     this.filter = arr;
     this.infoWindow.close();
     this.placeMarkers(settings.fit_bounds);
   }

 /**
  * Filters the map on load to only show filter resists categories.
  */
  GJMaps.prototype.filterLoad = function() {
     this.filter = [];
     for(var i = 0; i < cat.length; i++) {
       if(cat[i]['filter_resist'] != null) {
         this.filter.push(cat[i]['id']);
       }
     }
     this.placeMarkers();
   }

 /**
  * Checks which categories have options enabled and returns them.
  * @param {array} options Options array.
  */
  GJMaps.prototype.categoryOptionCheck = function(options) {
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

   /**
    * Triggers custom events.
    * @param {string} name Event name.
    * @param {object} name Event parameter(s).
    */
   GJMaps.prototype.gjmapsEvents = function(name, param) {
     $.event.trigger({ type: name, 'gjmaps': param });
   }

   /**
    * Initializes click events
    */
   GJMaps.prototype.initClickEvents = function() {
     // Handles click functions on the poi list items
     $('div.gjmaps-wrapper').on('click', 'li.poi', function() {
       var id = $(this).data('poi-id'), marker = false;

       for(var i = 0; poi.length > i; i++) {
         if(poi[i].id == id) {
           marker = poi[i];
           break;
         }
       }

       if(marker != false) {
         gjMaps.showPOIInfo(marker);
       }
     });

     // Handles click functions on the parents
     $(document).on('click', '.gjmaps-parent', function() {
       var cats = $(this).data('cat-ids').split(',');
       gjMaps.showCategoryByArr(cats);
     });
   }

});
