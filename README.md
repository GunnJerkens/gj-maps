gj-maps
=======

WordPress plugin for POI maps using Google Maps API

## setup

Install as a WordPress plugin or git submodule. Currently it is suggested that you use the [latest release](https://github.com/GunnJerkens/gj-maps/releases) as master may be unstable as the database class is rewritten for version 1.0.0.

## styles
Here is a block of [SASS](http://sass-lang.com) that describes the resulting
markup. Your styles don't need to be quite so specific.

```
div.gjmaps-wrapper {
  ul.gjmaps-categories {
    li.gjmaps-category {
      &.active {}
      div.gjmaps-label {
        span {}
      }
    }
  }
  .gjmaps-map-canvas {}
}
```

## usage

### csv import
Column names (all required):
[category, name, address, city, state, zip, country, phone, url]

### map placement
Use the shortcode `[gjmaps]` in your content to place the maps.

Shortcode options include:

| Option | Value | Required | Notes |
| :----- | :---: | :------: | :---- |
| map    | string | optional* | defaults to map_id |
| map_id | integer | optional* | required if map/api not present |
| position | string | optional | top or bottom, defaults top |
| latitude | integer | optional | defaults to options setting |
| longitude | integer | optional | defaults to options setting |
| zoom | integer | optional | defaults to options setting |
| fit_bounds | boolean | optional | defaults to options setting |
| api | string | optional* | overrides map/map_id, url must have map id |

*one of the three [map, map_id, api] is required.

#### the_content


`[gjmaps map_id="4" position="bottom" zoom="16"]`

#### do_shortcode

`<?php echo do_shortcode('[gjmaps api="http://example.com?gjmaps_api=4"]'); ?>`

## api

By default each maps has its own API (JSON) endpoint available for consumption by other gj-maps installations. The trailing id number is a requirement else the data will be entirely false.

`http://example.com/?gjmaps_api=4`

## events

| Event          | Fires                          |
| -----          | -----                          |
| gjmapsCatLoad  | When categories finish loading |
| gjmapsCatClick | When a catagory is clicked     |
| gjmapsPOIInfo  | After InfoWindow is opened     |

```
$(window).on('gjmapsCatLoad', function() {
  // do stuff
});
```

Alternatively if you would like to group categories or use self made markup for the map categories you can add the `.gjmaps-parent` class with `data-cat-ids` to trigger the map to change it's display. This click event is added to the document so items appended to the DOM will inherit it. This is more of a hack feature than a final solution.

```
<div class="gjmaps-parent" data-cat-ids="1,2,3">Click to Show Categories 1,2,3</div>
```

## disable

You can define a constant on a page template prior to `get_header()` to disable gj-maps from loading any scripts. This is helpful and suggested if you are
running another Google Maps application on the page. Our maps default to the latest stable version of maps, currently `3.17`.

```
define('DISABLE_GJ_MAPS', [any value]);
```

## caching

If you are using W3 Total Cache it will strip the defined CORS header. To workaround this you must disable caching on the API pages. That can be fixed by adding `/?gjmaps_api=*` to the setting under `Performance > Page Cache  > Never cache the following pages`.

## troubleshooting 

### deleted map

If a map is accidentally deleted from a production website, there are some features that allow map recovery without having to build the entire thing from scratch

1. Go to the staging or local version of website, which ever has the most recent data
2. Use the GJ Maps API endpoint to get a JSON dump of all map data `http://[domain].com?gjmaps_api=[ID]`
3. Copy and paste JSON into a JSON to CSV converter - [something like this](https://www.convertcsv.com/json-to-csv.htm)
4. Save CSV file
5. Category icon image URL will be provided - save these locally and use them to recreate categories
6. Create New Map
7. Create New Categories
8. Make sure file permissions are allow upload to WP Media Gallery - GJ Maps will not output specific error message
9. If category icons are used in a sidebar the SCSS for the page template will need to be updated - new categories will have new IDs and those are the selectors for the SCSS
10. Build and push changes to SCSS
10. Extract POI columns from CSV for `category, name, address, city, state, zip, country, phone, url, lat, lng` into a new CSV document, removing the `poi/`
11. Import POIs into new map
12. Adjust any fields as needed
13. Update map ID in shortcode on the page where deleted map was located

## issues

[GitHub Issues](https://github.com/GunnJerkens/gj-maps/issues)

## license

MIT
