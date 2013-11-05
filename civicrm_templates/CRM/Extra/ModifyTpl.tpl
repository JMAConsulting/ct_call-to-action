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

  var after = '{/literal}{$afterdiv}{literal}';
  var eachdiv = ' {/literal}{$eachdiv}{literal}  .crm-section';
  cj("<div class='newDiv'></div>").insertAfter(after);
  var formattings = new Array("", "title", "summaryp", "descriptionp", "morelink");
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
  cj('.newDiv').append('<div class="crm-submit-buttons">' + htm + '</div>');
});



{/literal}
</script>
