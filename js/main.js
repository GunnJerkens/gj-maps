jQuery(document).ready(function(a){function b(){var a,b;for(s={},a=0,b=poi.length;b>a;a++)s[poi[a].id]=poi[a];for(t={},a=0,b=cat.length;b>a;a++)t[cat[a].id]=cat[a]}function c(){var b,c="";for(b=0;b<cat.length;b++)"1"!=cat[b].hide_list&&(c+=e(cat[b]));a(".gjmaps-categories").append(c),a(".gjmaps-category div[data-type='label']").click(function(){g(a(this))}),0==settings.filter_load&&g(a(".gjmaps-category[data-cat-id='all']")),m("gjmapsCatLoad",{loaded:!0}),d()}function d(){var b=a(".gjmaps-category");percent=a(window).innerWidth()>768?b.length>2?(100-2*b.length)/b.length+"%":"50%":"100%",b.css("width",percent)}function e(a){var b,c,d,e,f,g;if(g="",f="",text=!0,"background"===settings.label_color?(a.icon?(e=a.icon.replace(/\/marker-/,"/symbol-"),g="background-image: url("+e+");"):g="",f="background-color: "+a.color+";"):"text"===settings.label_color?(g="",f="color: "+a.color+";"):"icon"===settings.label_color&&(e=a.icon.replace(/\/marker-/,"/symbol-"),g="background-image: url("+e+");",text=!1),b=text?'<li class="gjmaps-category" data-cat-id="'+a.id+'"><div style="'+g+f+'" class="gjmaps-label" data-type="label"><span>'+a.name+"</span></div><ul>":'<li class="gjmaps-category" data-cat-id="'+a.id+'"><div style="'+g+'" class="gjmaps-label" data-type="label"></div><ul>',1==settings.poi_list)for(c=0,d=poi.length;d>c;c++)poi[c].cat_id===a.id&&(b+='<li class="poi" data-poi-id="'+poi[c].id+'">',"1"==settings.poi_num&&(b+="<span>"+poi[c].num+" </span>"),b+=poi[c].name+"</li>");return b+="</ul></li>"}function f(b){var c,d,e,f;c='<div class="poi-info" style="overflow:hidden;"><h4>'+b.name+"</h4>",b.description&&(c+='<div class="description">'+b.description+"</div>"),c+='<div class="address">'+b.address+"<br>"+b.city+", "+b.state+" "+b.zip+'</div><div class="contact">',b.phone&&(c+=b.phone,b.url&&(c+="<br>")),b.url&&(d=b.url.replace(/^https?:\/\/|\/$/g,""),c+='<a href="'+b.url+'" target="_blank">'+d+"</a>"),c+="</div></div>",v.setContent(c),v.open(n,b.marker),e=a("body"),f=a("#map-canvas").offset().top-e.position().top,a(document.body).scrollTop()>f&&a(document.body).animate({scrollTop:f},300),m("gjmapsPOIInfo",{id:b.id})}function g(b){{var c;settings.mouse_scroll,settings.mouse_drag}catElement=b.closest(".gjmaps-category"),c=catElement.attr("data-cat-id"),"all"===c?(o=[],a("[data-cat-id='all']").addClass("active"),catElement.siblings(".gjmaps-category").removeClass("active"),1==settings.poi_list&&a(".gjmaps-category ul").slideDown()):(a("[data-cat-id='all']").removeClass("active"),catElement.siblings(".gjmaps-category").removeClass("active"),a(".gjmaps-category[data-cat-id="+c+"]").addClass("active"),1==settings.poi_list&&(catElement.siblings(".gjmaps-category").find("ul").slideUp(),a("ul",catElement).slideDown(),a(".gjmaps-category[data-cat-id="+c+"]").slideDown()),o=[c]),m("gjmapsCatClick",{category:c}),v.close(),h(settings.fit_bounds),"all"===c&&(n.panTo(p.center),n.setZoom(p.zoom))}function h(a){var b,c,d,e,g;for(r=new google.maps.LatLngBounds,b=0,c=poi.length;c>b;b++)if(d=!o||"object"==typeof o&&(!o.length||-1!==o.indexOf(poi[b].cat_id)||"1"==t[poi[b].cat_id].filter_resist)||("string"==typeof o||"number"==typeof o)&&poi[b].id==o,"undefined"!=typeof poi[b].marker)d?(poi[b].marker.setMap(n),r.extend(poi[b].marker.getPosition())):poi[b].marker.setMap(null);else if(d&&Number(poi[b].lat)&&Number(poi[b].lng)){var h=t[poi[b].cat_id],i=["filter_resist"],j=k(i);e=new google.maps.LatLng(poi[b].lat,poi[b].lng),"1"!=settings.poi_num||j.indexOf(poi[b].cat_id)>-1?(g={position:e,map:n,title:poi[b].name},h&&(g.icon={url:h.icon,anchor:u}),poi[b].marker=new google.maps.Marker(g)):poi[b].marker=new MarkerWithLabel({position:e,draggable:!1,map:n,icon:settings.poi_icon,labelContent:poi[b].num,labelAnchor:new google.maps.Point(12,0),labelClass:"gj-maps-marker-label",labelStyle:{width:"25px",height:"25px",paddingTop:"4px",color:"white",background:h.color,"border-radius":"50%"}}),google.maps.event.addListener(poi[b].marker,"click",function(a){return function(){f(poi[a])}}(b)),r.extend(e)}q=n.getBounds(),("1"==a||q&&!q.intersects(r))&&"((1, 180), (-1, -180))"!=r.toString()&&n.fitBounds(r)}function j(){o=[];for(var a=0;a<cat.length;a++)null!=cat[a].filter_resist&&o.push(cat[a].id);h()}function k(a){for(var b=[],c=0;c<cat.length;c++)for(var d=0;d<a.length;d++)1==cat[c][a[d]]&&b.push(cat[c].id);return b}function l(){var a=settings.center_lat,b=settings.center_lng,d=Math.floor(settings.map_zoom),e=Math.floor(settings.max_zoom),f=settings.mouse_scroll,g=settings.mouse_drag;p={zoom:d,maxZoom:e,center:new google.maps.LatLng(0,0),mapTypeId:google.maps.MapTypeId.ROADMAP,styles:"0"===settings.map_styles?"":jQuery.parseJSON(settings.map_styles),draggable:"0"===g?!0:!1,scrollwheel:"0"===f?!0:!1},settings.center_lat&&settings.center_lng&&(p.center=new google.maps.LatLng(a,b)),n=new google.maps.Map(document.getElementById("map-canvas"),p),google.maps.event.addDomListener(window,"resize",function(){var a=n.getCenter();google.maps.event.trigger(n,"resize"),n.setCenter(a)}),o=[],v=new google.maps.InfoWindow,settings.filter_load?j():h(settings.fit_bounds),c()}function m(b,c){a.event.trigger({type:b,gjmaps:c})}var n,o,p,q,r,s,t,u,v;u=new google.maps.Point(5,33),b(),google.maps.event.addDomListener(window,"load",l),a("div.gjmaps-wrapper").on("click","li.poi",function(){var b=a(this).data("poi-id"),c=!1;for(i=0;poi.length>i;i++)if(poi[i].id==b){c=poi[i];break}0!=c&&f(c)})});