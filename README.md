gj-maps
=======

WordPress plugin for POI maps using Google Maps API

### Setup

Importing a CSV use the column names

[category, name, address, city, state, zip, country, phone, url]

### Issues

### CSS Classes
ul.gjmaps-categories {
  //display: none; <-- hide categories
  li.gjmaps-category {
    &.active{}
  }
  div.gjmaps-label { // <-- customize columns (default 4+1)
    span {}
  }
  ul {
    .poi {
      display: none; // <-- sample bit
    }
  }
}
div#map-canvas {}