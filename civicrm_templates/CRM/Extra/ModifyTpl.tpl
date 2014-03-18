{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
<script>
{literal}
cj(document).ready(function() {
 {/literal}{if $buttonText}{literal}
  var button = {/literal}'{$buttonText}'{literal};
  cj('#_qf_Register_upload-bottom').attr('value', button);
 {/literal}{/if}{literal}

 {/literal}{if $profileButtonText}{literal}
  var button = {/literal}'{$profileButtonText}'{literal};
  cj('#_qf_Edit_next').attr('value', button);
 {/literal}{/if}{literal}

 {/literal}{if $surveyText}{literal}
  var buttonText = {/literal}'{$surveyText}'{literal};
  buttonText = cj('<div />').html(buttonText).text();
  cj('#_qf_Signature_next-bottom').attr('value', buttonText);
 {/literal}{/if}{literal}

  var after = '{/literal}{$afterdiv}{literal}';
  var eachdiv = ' {/literal}{$eachdiv}{literal}  .crm-section';
  if (after == '.crm-petition-activity-profile') {
    cj("<div class='profileDiv'></div>").insertAfter(after);
    after = '.profileDiv';
  }
  cj("<div class='newDiv'></div>").insertAfter(after);
  var formattings = new Array("", "title", "summaryp", "descriptionp", "morelink", "descriptionp");
  var count = 1; 
  cj(eachdiv).each(function(index) {
    var className = cj(this).attr('class');
    if (!className.match(/formatting/g)) {
      var htm = '<div class="' + className + '">' + cj(this).html() + '</div>';
      cj(this).remove();
      cj('.newDiv').append(htm);
    }
    else {
      cj(this).addClass(formattings[count]);
      count++;
    }	      
  });
  htm = cj('.crm-submit-buttons').html();
  cj('.crm-submit-buttons').remove();
  if (after == '.profileDiv') {
    var cProfile = cj('.crm-petition-contact-profile').html();
    var aProfile = cj('.crm-petition-activity-profile').html();
    cj('.crm-petition-activity-profile').remove();
    cj('.crm-petition-contact-profile').remove();

    cj('.profileDiv').append('<div class="crm-section crm-petition-contact-profile">' + cProfile + '</div>');
    cj('.profileDiv').append('<div class="crm-section crm-petition-activity-profile">' + aProfile + '</div>');
  }
  cj('.newDiv').append('<div class="crm-submit-buttons">' + htm + '</div>');
});



{/literal}
</script>
