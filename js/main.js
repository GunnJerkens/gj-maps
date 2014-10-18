jQuery(document).ready(function(a){function b(){var a,b;for(q={},a=0,b=poi.length;b>a;a++)q[poi[a].id]=poi[a];for(r={},a=0,b=cat.length;b>a;a++)r[cat[a].id]=cat[a]}function c(){var b,c="";for(b=0;b<cat.length;b++)"1"!=cat[b].hide_list&&(c+=e(cat[b]));a(".gjmaps-categories").append(c),a(".gjmaps-category div[data-type='label']").click(function(){g(a(this))}),d()}function d(){var b=a(".gjmaps-category");percent=a(window).innerWidth()>768?b.length>2?(100-2*b.length)/b.length+"%":"50%":"100%",b.css("width",percent)}function e(a){var b,c,d,e,f,g;if(g="",f="","background"===settings.label_color?(a.icon?(e=a.icon.replace(/\/marker-/,"/symbol-"),g="background-image: url("+e+");"):g="",f="background-color: "+a.color+";"):"text"===settings.label_color&&(g="",f="color: "+a.color+";"),b='<li class="gjmaps-category" data-cat-id="'+a.id+'"><div style="'+g+f+'" class="gjmaps-label" data-type="label"><span>'+a.name+"</span></div><ul>",1===settings.poi_list)for(c=0,d=poi.length;d>c;c++)poi[c].cat_id==a.id&&(b+='<li class="poi" data-poi-id="'+poi[c].id+'">',poi_number&&(b+="<span>"+poi[c].num+" </span>"),b+=poi[c].name+"</li>");return b+="</ul></li>"}function f(b){var c,d,e,f;c='<div class="poi-info" style="overflow:hidden;"><h4>'+b.name+"</h4>",b.description&&(c+='<div class="description">'+b.description+"</div>"),c+='<div class="address">'+b.address+"<br>"+b.city+", "+b.state+" "+b.zip+'</div><div class="contact">',b.phone&&(c+=b.phone,b.url&&(c+="<br>")),b.url&&(d=b.url.replace(/^https?:\/\/|\/$/g,""),c+='<a href="'+b.url+'" target="_blank">'+d+"</a>"),c+="</div></div>",t.setContent(c),t.open(l,b.marker),e=a("header"),f=a("#map-canvas").offset().top-e.height()-e.position().top,a(document.body).scrollTop()>f&&a(document.body).animate({scrollTop:f},300)}function g(b){var c;catElement=b.closest(".gjmaps-category"),c=catElement.attr("data-cat-id"),"all"===c?(m=[],a("[data-cat-id='all']").addClass("active"),catElement.siblings(".gjmaps-category").removeClass("active"),1===settings.poi_list&&a(".gjmaps-category ul").slideDown()):(a("[data-cat-id='all']").removeClass("active"),catElement.siblings(".gjmaps-category").removeClass("active"),a(".gjmaps-category[data-cat-id="+c+"]").addClass("active"),1===settings.poi_list&&(catElement.siblings(".gjmaps-category").find("ul").slideUp(),a("ul",catElement).slideDown(),a(".gjmaps-category[data-cat-id="+c+"]").slideDown()),m=[c]),t.close(),h(),"all"===c&&(l.panTo(n.center),l.setZoom(n.zoom))}function h(a){var b,c,d,e,g;for(p=new google.maps.LatLngBounds,b=0,c=poi.length;c>b;b++)if(d=!m||"object"==typeof m&&(!m.length||-1!==m.indexOf(poi[b].cat_id)||"1"==r[poi[b].cat_id].filter_resist)||("string"==typeof m||"number"==typeof m)&&poi[b].id==m,"undefined"!=typeof poi[b].marker)d?(poi[b].marker.setMap(l),p.extend(poi[b].marker.getPosition())):poi[b].marker.setMap(null);else if(d&&Number(poi[b].lat)&&Number(poi[b].lng)){var h=r[poi[b].cat_id];e=new google.maps.LatLng(poi[b].lat,poi[b].lng),settings.poi_number?poi[b].marker=new MarkerWithLabel({position:e,draggable:!1,map:l,icon:settings.poi_icon,labelContent:poi[b].num,labelAnchor:new google.maps.Point(12,0),labelClass:"gj-maps-marker-label",labelStyle:{width:"25px",height:"25px",paddingTop:"4px",color:"white",background:h.color,"border-radius":"50%"}}):(g={position:e,map:l,title:poi[b].name},h&&(g.icon={url:h.icon,anchor:s}),poi[b].marker=new google.maps.Marker(g)),google.maps.event.addListener(poi[b].marker,"click",function(a){return function(){f(poi[a])}}(b)),p.extend(e)}o=l.getBounds(),(a||o&&!o.intersects(p))&&"((1, 180), (-1, -180))"!=p.toString()&&l.fitBounds(p)}function j(){m=[];for(var a=0;a<cat.length;a++)null!=cat[a].filter_resist&&m.push(cat[a].id);h()}function k(){var a=settings.center_lat,b=settings.center_lng,d=Math.floor(settings.map_zoom);n={zoom:d,center:new google.maps.LatLng(0,0),mapTypeId:google.maps.MapTypeId.ROADMAP,styles:"0"===settings.map_styles?"":jQuery.parseJSON(settings.map_styles)},settings.center_lat&&settings.center_lng&&(n.center=new google.maps.LatLng(a,b)),l=new google.maps.Map(document.getElementById("map-canvas"),n),google.maps.event.addDomListener(window,"resize",function(){var a=l.getCenter();google.maps.event.trigger(l,"resize"),l.setCenter(a)}),m=[],t=new google.maps.InfoWindow,h(!a||!b),c(),settings.filter_load&&j()}var l,m,n,o,p,q,r,s,t;s=new google.maps.Point(5,33),b(),google.maps.event.addDomListener(window,"load",k),a("div.gjmaps-wrapper").on("click","li.poi",function(){var b=a(this).data("poi-id"),c=!1;for(i=0;poi.length>i;i++)if(poi[i].id==b){c=poi[i];break}0!=c&&f(c)})});