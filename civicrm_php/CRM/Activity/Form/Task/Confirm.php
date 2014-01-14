<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
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
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * This class provides the functionality to email a group of contacts
 */
class CRM_Activity_Form_Task_Confirm extends CRM_Activity_Form_Task {

  /**
   * the title of the group
   *
   * @var string
   */
  protected $_title;

  /**
   * variable to store redirect path
   *
   */
  protected $_userContext;

  /**
   * variable to store contact Ids
   *
   */
  public $_contacts;

  /**
   * build all the data structures needed to build the form
   *
   * @return void
   * @access public
   */
  function preProcess() {
    /*
     * initialize the task and row fields
     */

    parent::preProcess();
    $session = CRM_Core_Session::singleton();
    $this->_userContext = $session->readUserContext();

    CRM_Utils_System::setTitle(ts('Create Followup Activities?'));
    // Check if activities are followup activities
    $this->_countInvalid = 0;
    $sql = "SELECT id, parent_id FROM civicrm_activity WHERE parent_id IN (".implode(', ', $this->_activityHolderIds).")";
    $result = CRM_Core_DAO::executeQuery($sql);
    $this->_originalSelected = count($this->_activityHolderIds);
    while ($result->fetch()) {
      if(($key = array_search($result->parent_id, $this->_activityHolderIds)) !== FALSE) {
        $this->_countInvalid++;
        unset($this->_activityHolderIds[$key]);
      }
    }
  }

  /**
   * Build the form
   *
   * @access public
   *
   * @return void
   */
  function buildQuickForm() {
    $message = '';
    $continue = FALSE;
    if ($this->_countInvalid && $this->_activityHolderIds) {
      $message = $this->_countInvalid.' of the '.$this->_originalSelected.' selected Activities cannot have a Follow-up Activity added since they already have one.';
    }
    else if ($this->_countInvalid && !$this->_activityHolderIds) {
      $message = 'The '.$this->_countInvalid.' selected Activities cannot have a Follow-up Activity added since they already have one.';
    }
    if ($this->_countInvalid < count($this->_activityHolderIds)) {
      $message .= 'Would you like to create follow-up activities for '.(count($this->_activityHolderIds) - $this->_countInvalid).' of the '.$this->_originalSelected.' selected Activities that do not yet have Follow-up Activities?';  
      $continue = TRUE;
    }
    $this->assign('alertMessage', $message);
    if ($continue) {
      $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => 'Continue >>',
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'back',
          'name' => ts('Cancel'),
        ),
      )
     );
    }
    else {
      $this->addButtons(array(
        array(
          'type' => 'back',
          'name' => ts('Cancel'),
        ),
      )
    );
    }
  }
}

