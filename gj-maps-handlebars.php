<script id="info-window" type="text/x-handelbars-template">
  <div class="poi-info" style="overflow:hidden;">
    {{#if name}}
      <h4>{{name}}</h4>
    {{/if}}
    {{#if description}}
      <div class="description">{{description}}</div>
    {{/if}}
    <div class="address">
      {{address}}<br>{{city}}{{#if city}},{{/if}} {{state}} {{zip}}
    </div>
    <div class="contact">
      {{#if phone}}
        {{#if phone_link}}
          <a href="tel:+1{{phone_link}}">{{phone}}</a>
        {{else}}
          <span>{{phone}}</span>
        {{/if}}
      {{/if}}
      {{#if url}}
        <br><a href="{{url}}" target="_blank">{{linkName}}</a>
      {{/if}}
    </div>
  </div>
</script>

<script id="category-list" type="text/x-handelbars-template">
  {{#if text}}
  <style>
  .gjmaps-categories li[data-cat-id="{{id}}"] .gjmaps-label { {{color_style}} }
  {{#if background_true}}
  .gjmaps-categories li[data-cat-id="{{id}}"] .gjmaps-label:hover {background-color: white;}
  .gjmaps-categories li[data-cat-id="{{id}}"] .gjmaps-label span {color: white;}
  .gjmaps-categories li[data-cat-id="{{id}}"] .gjmaps-label:hover span {color: {{color}};}
  {{/if}}
  </style>
  {{/if}}
  <li class="gjmaps-category" data-cat-id="{{id}}">
    <div style="{{background}}" class="gjmaps-label" data-type="label">
      {{#if text}}
        <span>{{name}}</span>
      {{/if}}
    </div>
    <ul>
      {{#if poi_list}}
        {{#each poi_array}}
          <li class="poi" data-poi-id="{{id}}">
            {{#if show_num}}
              <span>{{num}} </span>
            {{/if}}
            {{name}}
          </li>
        {{/each}}
      {{/if}}
    </ul>
  </li>
</script>
