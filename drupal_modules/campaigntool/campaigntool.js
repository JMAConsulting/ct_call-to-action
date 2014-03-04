(function ($) {

  Drupal.behaviors.exampleModule = {
    attach: function (context, settings) {
	function loadDialog( url, fieldName) {
	    $.ajax({
		url: url,
		success: function( content ) {
		    $("#locale-dialog_" +fieldName ).dialog({
			modal       : true,
			resizable   : true,
			bgiframe    : true,
			overlay     : { opacity: 0.3, background: "black" },
			beforeclose : function(event, ui) {
			    $(this).dialog("destroy");
			}
		    });
		    $("#locale-dialog_" +fieldName ).html(content);
		    $("#locale-dialog_" +fieldName ).dialog('open');
		}
	    });
	}

	$('#title-field, #summary-field, #description-field, #link-field').click( function() {
	    var id = $(this).data('id');
	    var table = $(this).data('table');
	    var field = $(this).data('field');
	    var row = $(this).data('row');
	    loadDialog('/i18n/'+ table +'/' + field + '/' + id + '/' + row, field); 
	    return false;
	});	
	if ($('#edit-action-type').val() != 'event') {
	    $('#address-wrapper').hide();
	    $('#location-choose-wrapper').hide();
	    $('.container-inline-date').hide();
	    $('#edit-link').val('');
	}
	$('#edit-action-type').change( function() {
	    switch($(this).val()) {
	    case 'event':
		$('.container-inline-date').show();
		$('#address-wrapper').show();
		$('#edit-link').val('');
		break;
	    case 'petition':
		$('#edit-link').val('');
		$('.container-inline-date').hide();
		$('#address-wrapper').hide();
		break;
	    case 'survey':
		$('.container-inline-date').hide();
		$('#address-wrapper').hide();
		$('#edit-link').val('Send your Survey');
		break;
	    case 'subscription':
		$('.container-inline-date').hide();
		$('#address-wrapper').hide();
		$('#edit-link').val('');
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