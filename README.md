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
| api | string | optional* | overrides map/map_id, url must have map id |

*one of the three [map, map_id, api] is required.

#### the_content


`[gjmaps map_id="4" position="bottom" zoom="16"]`

#### do_shortcode

`<?php echo do_shortcode('[gjmaps api="http://example.com?gjmaps_api=4"]'); ?>`

## api

By default each maps has its own API (JSON) endpoint available for consumption by other gj-maps installations. The trailing id number is a requirement else the data will be entirely false.

`http://example.com/?gjmaps_api=4`

## issues
[GitHub Issues](https://github.com/GunnJerkens/gj-maps/issues)

## license

MIT
