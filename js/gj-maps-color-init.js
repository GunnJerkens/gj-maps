jQuery(document).ready(function($) {

  $('.color-picker').wpColorPicker({
    change: function(event, ui) {
      colorID = $(this).attr('id');
      $('#newColor').attr('value', ui.color.toString());
    }
  });

  $('.wp-color-result').click(function() {
    console.log('yep');
    $(this).parents('tr').children('.mode').val('update');

  });

});