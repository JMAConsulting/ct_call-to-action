(function ($) {

  Drupal.behaviors.exampleModule = {
    attach: function (context, settings) {	
	if ($('#edit-action-type').val() != 'event') {
	    $('#address-wrapper').hide();
	    $('#location-choose-wrapper').hide();
	    $('.container-inline-date').hide();
	}
	$('#edit-action-type').change( function() {
	    switch($(this).val()) {
	    case 'event':
		$('.container-inline-date').show();
		$('#address-wrapper').show();
		break;
	    case 'petition':
		$('.container-inline-date').hide();
		$('#address-wrapper').hide();
		break;
	    case 'subscription':
		$('.container-inline-date').hide();
		$('#address-wrapper').hide();
		break;
	    default:
		break;
	    }
	});
	$('#location-choice-wrapper').find(':radio').each( function() {
	    $(this).click( function() {
		if ($(this).val() == 2) {
		    $('#location-choose-wrapper').show();
		}
		else {
		    $('#location-choose-wrapper').hide();
		    $( "input[name='street_address']" ).val('');
		    $( "input[name='add1_address']" ).val('');
		    $( "input[name='add2_address']" ).val('');
		    $( "input[name='city']" ).val('');
		    $( "input[name='zip']" ).val('');
		    $( "input[name='suffix']" ).val('');
		    $("select[name='location']").val(0); 
		    $("select[name='country']").val(1228); 
		    $("select[name='state']").val(1000); 
		    $( "input[name='email']" ).val('');
		    $( "input[name='phone']" ).val('');
		}
	    });
	}); 
    }
  };

})(jQuery);