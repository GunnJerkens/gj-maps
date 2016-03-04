jQuery(document).ready(function($) {

  function gjModal() {
    $('.gj-modal-cancel').click(function(){
      $(this).unbind('click');
      $('.gj-modal').remove();
    });
  }
  gjModal();

  function pickColors() {
    $('.color-picker').wpColorPicker({
      change: function(event, ui) {
        colorID = $(this).attr('id');
        $('#newColor').attr('value', ui.color.toString());
      }
    });

    $('.wp-color-result').click(function() {
      $(this).parents('tr').children('.mode').val('update');
    });
  }
  pickColors();


  var mapsTable, tableRow, addRow, ID, catOptions;

  mapsTable = $('.gj-maps > tbody:last');
  addPOIRow = $('.add-poi-row');
  addCatRow = $('.add-cat-row');

  function createPOIRow() {
    addPOIRow.click(function() {
      catOptions = '';
      ID = $('.gj-maps > tbody:last').children('tr').last().data('id') + 1;
      ID = isNaN(ID) ? 1 : ID + 1;

      for(i = 0; i < cat.length; i++) {

        catOptions += '<option value="' + cat[i].id + '">' + cat[i].name + '</option>';

      }

      if(!cat.length) {
        catOptions = '<option value="0" selected>Default</option>';
      }

      tableRow = [
        '<tr id="maps-' + ID + '" class="alternate maps" data-id="' + ID + '">',
          '<input type="hidden" name="' + ID + '[id]" value="' + ID + '">',
          '<input type="hidden" name="' + ID + '[mode]" value="create">',
          '<input type="hidden" name="' + ID + '[map_id]" value="' + map.id + '">',
          '<th class="check-column">',
            '<input type="checkbox" name="' + ID + '[delete]">',
          '</th>',
          '<td><input type="text" class="full-width" name="' + ID +'[name]" value=""></td>',
          '<td><select name="'+ ID + '[cat_id]" >' + catOptions + '</select></td>',
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
  createPOIRow();

  function createCatRow() {
    addCatRow.click(function() {

      ID = $('.gj-maps > tbody:last').children('tr').last().data('id') + 1;
      ID = isNaN(ID) ? 1 : ID + 1;

      tableRow = [
        '<tr id="maps-' + ID + '" class="alternate maps" data-id="' + ID + '">',
          '<input type="hidden" name="' + ID + '[id]" value="' + ID + '">',
          '<input type="hidden" name="' + ID + '[mode]" value="create">',
          '<input type="hidden" name="' + ID + '[map_id]" value="' + map.id + '">',
          '<th class="check-column">',
            '<input type="checkbox" name="' + ID + '[delete]">',
          '</th>',
          '<td><input type="text" class="full-width" name="' + ID + '[name]" value=""></td>',

          '<td><input type="text" class="color-picker" name="' + ID + '[color]" value=""></td>',
          '<td><input type="file" name="' + ID + '[icon]" value=""></td>',
          '<td><input type="checkbox" name="' + ID + '[hide_list]" value="1"></td>',
          '<td><input type="checkbox" name="' + ID + '[filter_resist]" value="1"></td>',
        '</tr>'
      ].join("\n");

      mapsTable.append(tableRow);

      pickColors();

    });
  }
  createCatRow();


  $('.maps-detect-change').change(function() {

    $(this).parents('tr').children('.mode').val('update');

  });

  $(".widen").focus(function() {

    var column, $header;

    $header = $('.th-header');

    $header.removeClass('active');
    column = $(this).data('column');

    $('table').find("[data-column='" + column + "']").addClass('active');

  });

});
