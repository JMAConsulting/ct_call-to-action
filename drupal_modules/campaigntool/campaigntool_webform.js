(function ($) {

  Drupal.behaviors.exampleModule = {
      attach: function (context, settings) {
	  $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-2-contact-1-contact-birth-date').hide();
	  $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-4-contact-1-fieldset-fieldset').hide();
	  $('#edit-submitted-civicrm-1-contact-1-fieldset-fieldset-occasion').find(':radio').each( function(){
	      if ($(this+':checked').val() == 'birthday') {
		  $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-4-contact-1-fieldset-fieldset').hide();
		  $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-2-contact-1-contact-birth-date').show();
	      }
	      else if ($(this+':checked').val() == 'anniversary') {
		  $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-4-contact-1-fieldset-fieldset').show();
		  $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-2-contact-1-contact-birth-date').hide();
	      }
	      $(this).click( function(){
		  if ($(this).val() == 'birthday') {
		      $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-4-contact-1-fieldset-fieldset').hide();
		      $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-2-contact-1-contact-birth-date').show();
		  }
		  else if ($(this).val() == 'anniversary') {
		      $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-4-contact-1-fieldset-fieldset').show();
		      $('#webform-component-civicrm-2-contact-1-fieldset-fieldset--civicrm-2-contact-1-contact-birth-date').hide();
		  }
	      });
	  }); 
      }
  };

})(jQuery);