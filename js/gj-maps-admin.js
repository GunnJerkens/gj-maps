jQuery(document).ready(function($) {

  var mapsTable, tableRow, addRow, ID, map, catOptions;

  mapsTable = $('.gj-maps > tbody:last');
  addRow = $('.add-row');

  function createRow() {
    addRow.click(function() {

      ID = $('.gj-maps > tbody:last').children('tr').last().data('id') + 1;
      ID = isNaN(ID) ? 1 : ID + 1;

      for(i = 0; i < cat.length; i++) {

        catOptions += '<option value="' + cat[i].id + '">' + cat[i].name + '</option>';

      }

      tableRow = [
        '<tr id="maps-' + ID + '" class="alternate maps" data-id="' + ID + '">',
          '<input type="hidden" name="' + ID + '[id]" value="' + ID + '">',
          '<input type="hidden" name="' + ID + '[mode]" value="create">',
          '<input type="hidden" name="' + ID + '[map_id]" value="' + map_id + '">',
          '<th class="check-column">',
            '<input type="checkbox" name="' + ID + '[delete]">',
          '</th>',
          '<td><input type="text" class="full-width" name="' + ID +'[name]" value=""></td>',
          '<td><select name="'+ ID + '[cat_id]">' + catOptions + '</select></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[address]" value=""></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[city]" value=""></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[state]" value=""></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[zip]" value=""></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[country]" value=""></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[phone]" value=""></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[url]" value=""></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[lat]" id="lat' + ID +'" value=""></td>',
          '<td><input type="text" class="full-width" name="' + ID + '[lng]" id="lng' + ID + '" value=""></td>',
        '</tr>'
      ].join("\n");

      mapsTable.append(tableRow);

    });
  }
  createRow();


  $('.maps-detect-change').change(function() {

    $(this).parents('tr').children('.mode').val('update');

  });

});
