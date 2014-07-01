gj-maps
=======

WordPress plugin for POI maps using Google Maps API

## setup

Install as a WordPress plugin or git submodule.

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

To import a CSV use the column names:
[category, name, address, city, state, zip, country, phone, url]

Use the shortcode `[gjmaps]` in your content to place the maps.

Shortcode options include:

```
'map'       => 'Single', (optional, defaults to map_id)
'map_id'    => '1', (optional, defaults to 1)
'position'  => 'top', (optional, top or bottom - refers to html structure)
'latitude'  => 33.8274746, (optional, defaults to options setting)
'longitude' => -118.1475189, (optional, defaults to options setting)
'zoom'      => 14, (optional, defaults to options setting)
'api'       => http://example.com/?gjmaps_api=1 (optional, requires a map_id)
```

### the_content


`[gjmaps map_id="4" position="bottom" zoom="16"]`

### do_shortcode

`<?php echo do_shortcode('[gjmaps api="http://example.com/gjmaps_api=4"'); ?>

## issues
[GitHub Issues](https://github.com/GunnJerkens/gj-maps/issues)

## license

MIT
