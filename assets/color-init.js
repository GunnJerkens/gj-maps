jQuery(document).ready(function($){
    $('.color-picker').wpColorPicker({
    	change: function(event, ui) {
    		colorID = $(this).attr('id');
    		$('#newColor').attr('value', ui.color.toString());
    	}
    });
});
