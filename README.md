gj-maps
=======

WordPress plugin for POI maps using Google Maps API

## setup

Install as a WordPress plugin or git submodule.

To import a CSV use the column names:
[category, name, address, city, state, zip, country, phone, url]

## usage

Use the shortcode `[gjmaps]` in your content to place the map.

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

## license

MIT
