jQuery(document).ready(function(a){function b(){this.map,this.mapOptions={zoom:Math.floor(settings.map_zoom),maxZoom:Math.floor(settings.max_zoom),center:new google.maps.LatLng(0,0),mapTypeId:google.maps.MapTypeId.ROADMAP,styles:"0"===settings.map_styles?"":jQuery.parseJSON(settings.map_styles),draggable:"0"===settings.mouse_drag,scrollwheel:"0"===settings.mouse_scroll},this.catIndexed={},this.infoWindow=new google.maps.InfoWindow,this.filter=[]}var c=new b,d=a("#info-window").html(),e=Handlebars.compile(d),f=a("#category-list").html(),g=Handlebars.compile(f),h=new Autolinker;google.maps.event.addDomListener(window,"load",function(){c.initMap()}),b.prototype.initMap=function(){this.indexCatData(),this.initClickEvents(),settings.center_lat&&settings.center_lng&&(this.mapOptions.center=new google.maps.LatLng(settings.center_lat,settings.center_lng)),this.map=new google.maps.Map(document.getElementById("map-canvas"),this.mapOptions),google.maps.event.addDomListener(window,"resize",function(){var a=c.map.getCenter();google.maps.event.trigger(c.map,"resize"),c.map.setCenter(a)}),settings.filter_load?this.filterLoad():this.placeMarkers(settings.fit_bounds),this.setupPOILists()},b.prototype.checkIsMatch=function(a){return!this.filter||"object"==typeof this.filter&&(!this.filter.length||-1!==this.filter.indexOf(poi[a].cat_id)||"1"==this.catIndexed[poi[a].cat_id].filter_resist)||("string"==typeof this.filter||"number"==typeof this.filter)&&poi[a].id==this.filter},b.prototype.createMarker=function(b,d){if(Number(poi[b].lat)&&Number(poi[b].lng)){var e=this.catIndexed[poi[b].cat_id],f=["filter_resist"],g=this.categoryOptionCheck(f),h=new google.maps.LatLng(poi[b].lat,poi[b].lng);if("1"!=settings.poi_num||g.indexOf(poi[b].cat_id)>-1){var i={position:h,map:this.map,title:poi[b].name};e&&(i.icon={url:""!==e.icon?e.icon:settings.poi_icon,anchor:new google.maps.Point(5,33)}),g.indexOf(poi[b].cat_id)>-1&&a.extend(i,{zIndex:8675309}),poi[b].marker=new google.maps.Marker(i)}else poi[b].marker=new MarkerWithLabel({position:h,draggable:!1,map:this.map,icon:settings.poi_icon,labelContent:poi[b].num,labelAnchor:new google.maps.Point(12,0),labelClass:"gj-maps-marker-label",labelStyle:{width:"25px",height:"25px",paddingTop:"4px",color:"white",background:e.color,"border-radius":"50%"}});google.maps.event.addListener(poi[b].marker,"click",function(a){return function(){var b=new google.maps.LatLng(poi[a].lat,poi[a].lng);c.map.panTo(b),c.showPOIInfo(poi[a])}}(b)),d.extend(h)}return d},b.prototype.placeMarkers=function(a){for(var b=new google.maps.LatLngBounds,c=0,d=poi.length;d>c;c++){var e=this.checkIsMatch(c);"undefined"!=typeof poi[c].marker?e?(poi[c].marker.setMap(this.map),b.extend(poi[c].marker.getPosition())):poi[c].marker.setMap(null):e&&(b=this.createMarker(c,b))}var f=this.map.getBounds();("1"==a||f&&!f.intersects(b))&&"((1, 180), (-1, -180))"!=b.toString()&&this.map.fitBounds(b)},b.prototype.indexCatData=function(){for(var a=0,b=cat.length;b>a;a++)this.catIndexed[cat[a].id]=cat[a]},b.prototype.setupPOILists=function(){for(var b="",d=0;d<cat.length;d++)"1"!=cat[d].hide_list&&(b+=this.markupCategoryList(cat[d]));a(".gjmaps-categories").append(b),a(".gjmaps-category div[data-type='label']").click(function(){c.showCategoryByEl(a(this))}),0==settings.filter_load&&this.showCategoryByEl(a(".gjmaps-category[data-cat-id='all']")),this.gjmapsEvents("gjmapsCatLoad",{loaded:!0}),this.resizeCategories()},b.prototype.resizeCategories=function(){var b,c=a(".gjmaps-category");b=a(window).innerWidth()>768?c.length>2?(100-2*c.length)/c.length+"%":"50%":"100%",c.css("width",b)},b.prototype.getCatStyle=function(a){var b;return a.background="",a.text=!0,"background"===settings.label_color?(a.icon?(b=a.icon.replace(/\/marker-/,"/symbol-"),a.background="background-image: url("+b+");"):a.background="",a.color_style="background-color: "+a.color+";"):"text"===settings.label_color?(a.background="",a.color_style="color: "+a.color+";"):"icon"===settings.label_color&&(b=a.icon.replace(/\/marker-/,"/symbol-"),a.background="background-image: url("+b+");",a.text=!1),a},b.prototype.markupCategoryList=function(a){if(a.poi_list=settings.poi_list,a.poi_array=[],a=this.getCatStyle(a),1==settings.poi_list)for(var b=0,c=poi.length;c>b;b++)poi[b].cat_id===a.id&&(poi[b].show_num="1"==settings.poi_num,a.poi_array.push(poi[b]));return g(a)},b.prototype.showPOIInfo=function(b){if(b.phone&&(b.phone_link="1"===settings.phone_link?b.phone.replace(/[\.\(\)\-\s]/g,""):!1),b.url){var c=h.parse(b.url),d=b.url.replace(/^https?:\/\/|\/$/g,"");c.length&&(b.url=c[0].getAnchorHref(),d=c[0].getAnchorText(),console.log(b.url,d)),b.linkName=settings.link_text?settings.link_text:d}var f=e(b);this.infoWindow.setContent(f),this.infoWindow.open(this.map,b.marker);var g=a("body"),i=a("#map-canvas").offset().top-g.position().top;a(document.body).scrollTop()>i&&a(document.body).animate({scrollTop:i},300),this.gjmapsEvents("gjmapsPOIInfo",{id:b.id,cat_id:b.cat_id})},b.prototype.showCategoryByEl=function(b){var c=b.closest(".gjmaps-category"),d=c.attr("data-cat-id");"all"===d?(this.filter=[],a("[data-cat-id='all']").addClass("active"),c.siblings(".gjmaps-category").removeClass("active"),1==settings.poi_list&&a(".gjmaps-category ul").slideDown()):(a("[data-cat-id='all']").removeClass("active"),c.siblings(".gjmaps-category").removeClass("active"),a(".gjmaps-category[data-cat-id="+d+"]").addClass("active"),1==settings.poi_list&&(c.siblings(".gjmaps-category").find("ul").slideUp(),a("ul",c).slideDown(),a(".gjmaps-category[data-cat-id="+d+"]").slideDown()),this.filter=[d]),this.gjmapsEvents("gjmapsCatClick",{category:d}),this.infoWindow.close(),this.placeMarkers(settings.fit_bounds),"all"!==d||settings.fit_bounds||(this.map.panTo(this.mapOptions.center),this.map.setZoom(this.mapOptions.zoom))},b.prototype.showCategoryByArr=function(a){this.filter=a,this.infoWindow.close(),this.placeMarkers(settings.fit_bounds)},b.prototype.filterLoad=function(){this.filter=[];for(var a=0;a<cat.length;a++)null!=cat[a].filter_resist&&this.filter.push(cat[a].id);this.placeMarkers()},b.prototype.categoryOptionCheck=function(a){for(var b=[],c=0;c<cat.length;c++)for(var d=0;d<a.length;d++)1==cat[c][a[d]]&&b.push(cat[c].id);return b},b.prototype.gjmapsEvents=function(b,c){a.event.trigger({type:b,gjmaps:c})},b.prototype.initClickEvents=function(){a("div.gjmaps-wrapper").on("click","li.poi",function(){for(var b=a(this).data("poi-id"),d=!1,e=0;poi.length>e;e++)if(poi[e].id==b){d=poi[e];break}0!=d&&c.showPOIInfo(d)}),a(document).on("click",".gjmaps-parent",function(){var b=a(this).data("cat-ids").split(",");c.showCategoryByArr(b)})}});