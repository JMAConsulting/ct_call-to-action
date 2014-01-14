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
 * This class generates form components for Activity
 *
 */
class CRM_Activity_Form_Task_FollowUp extends CRM_Activity_Form_Task {

  /**
   * The id of the object being edited / created
   *
   * @var int
   */
  public $_activityId;

  /**
   * store activity ids when multiple activities are created
   *
   * @var int
   */
  public $_activityIds = array();

  /**
   * The id of activity type
   *
   * @var int
   */
  public $_activityTypeId;

  /**
   * The name of activity type
   *
   * @var string
   */
  public $_activityTypeName;
  /**
   * The target contact Ids for activities
   *
   * @var array
   */
  public $_targetContactIDs;

  /**
   * The id of currently viewed contact
   *
   * @var int
   */
  public $_currentlyViewedContactId;

  /**
   * The id of source contact and target contact
   *
   * @var int
   */
  public $_sourceContactId;
  public $_targetContactId;
  public $_assigneeContactId;

  protected $_single;

  public $_context;
  public $_compContext;
  public $_action;
  public $_activityTypeFile;

  /**
   * The id of the logged in user, used when add / edit
   *
   * @var int
   */
  public $_currentUserId;

  /**
   * The array of form field attributes
   *
   * @var array
   */
  public $_fields;

  /**
   * The the directory inside CRM, to include activity type file from
   *
   * @var string
   */
  protected $_crmDir = 'Activity';

  /*
     * Survey activity
     *
     * @var boolean
     */

  protected $_isSurveyActivity;

  protected $_values = array();

  /**
   * The _fields var can be used by sub class to set/unset/edit the
   * form fields based on their requirement
   *
   */
  function setFields() {
    $this->_fields = array(
      'subject' => array(
        'type' => 'text',
        'label' => ts('Subject'),
        'attributes' => CRM_Core_DAO::getAttribute('CRM_Activity_DAO_Activity',
          'subject'
         ),
      ),
      'duration' => array(
        'type' => 'text',
        'label' => ts('Duration'),
        'attributes' => array('size' => 4, 'maxlength' => 8),
        'required' => FALSE,
      ),
      'location' => array(
        'type' => 'text',
        'label' => ts('Location'),
        'attributes' =>
        CRM_Core_DAO::getAttribute('CRM_Activity_DAO_Activity',
          'location'
        ),
        'required' => FALSE
      ),
      'details' => array(
        'type' => 'wysiwyg',
        'label' => ts('Details'),
        // forces a smaller edit window
        'attributes' => array('rows' => 4, 'cols' => 60),
        'required' => FALSE
      ),
      'status_id' => array(
        'type' => 'select',
        'label' => ts('Status'),
        'attributes' =>
        CRM_Core_PseudoConstant::activityStatus(),
        'required' => TRUE
      ),
      'priority_id' => array(
        'type' => 'select',
        'label' => ts('Priority'),
        'attributes' =>
        CRM_Core_PseudoConstant::get('CRM_Activity_DAO_Activity', 'priority_id'),
        'required' => TRUE
      ),
      'source_contact_id' => array(
        'type' => 'text',
        'label' => ts('Added By'),
        'required' => FALSE
      ),
      'followup_activity_type_id' => array(
        'type' => 'select',
        'label' => ts('Followup Activity'),
        'attributes' => array(
          '' => '- ' . ts('select activity') . ' -'
        ) +
        CRM_Core_PseudoConstant::ActivityType(FALSE)
      ),
      'followup_activity_subject' => array(
        'type' => 'text',
        'label' => ts('Subject'),
        'attributes' => CRM_Core_DAO::getAttribute('CRM_Activity_DAO_Activity',
          'subject'
        )
      )
    );

    if (($this->_context == 'standalone') &&
      ($printPDF = CRM_Utils_Array::key('Print PDF Letter', $this->_fields['followup_activity_type_id']['attributes']))
    ) {
      unset($this->_fields['followup_activity_type_id']['attributes'][$printPDF]);
    }
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  function preProcess() {

    $session = CRM_Core_Session::singleton();
    $this->_currentUserId = $session->get('userID');

    $this->_currentlyViewedContactId = $this->get('contactId');
    if (!$this->_currentlyViewedContactId) {
      $this->_currentlyViewedContactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
    }
    $this->assign('contactId', $this->_currentlyViewedContactId);

    if ($this->_currentlyViewedContactId) {
      CRM_Contact_Page_View::setTitle($this->_currentlyViewedContactId);
    }

    //give the context.
    if (!isset($this->_context)) {
      $this->_context = CRM_Utils_Request::retrieve('context', 'String', $this);
      if (CRM_Contact_Form_Search::isSearchContext($this->_context)) {
        $this->_context = 'search';
      }
      elseif (!in_array($this->_context, array('dashlet', 'dashletFullscreen'))
        && $this->_currentlyViewedContactId
      ) {
        $this->_context = 'activity';
      }
      $this->_compContext = CRM_Utils_Request::retrieve('compContext', 'String', $this);
    }

    $this->assign('context', $this->_context);

    if ($this->_action != CRM_Core_Action::ADD &&
      get_class($this->controller) != 'CRM_Contact_Controller_Search'
    ) {
      $this->_activityId = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    }

    $this->_activityTypeId = CRM_Utils_Request::retrieve('atype', 'Positive', $this);
    $this->assign('atype', $this->_activityTypeId);

    if ($this->_activityId &&
      in_array($this->_action, array(
        CRM_Core_Action::UPDATE,
        CRM_Core_Action::VIEW
      )) &&
      !CRM_Activity_BAO_Activity::checkPermission($this->_activityId, $this->_action)
    ) {
      CRM_Core_Error::fatal(ts('You do not have permission to access this page.'));
    }
    if (($this->_action & CRM_Core_Action::VIEW) &&
      CRM_Activity_BAO_Activity::checkPermission($this->_activityId, CRM_Core_Action::UPDATE)
    ) {
      $this->assign('permission', 'edit');
    }

    if (!$this->_activityTypeId && $this->_activityId) {
      $this->_activityTypeId = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity',
        $this->_activityId,
        'activity_type_id'
      );
    }

    //Assigning Activity type name
    if ($this->_activityTypeId) {
      $activityTName = CRM_Core_OptionGroup::values('activity_type', FALSE, FALSE, FALSE, 'AND v.value = ' . $this->_activityTypeId, 'name');
      if ($activityTName[$this->_activityTypeId]) {
        $this->_activityTypeName = $activityTName[$this->_activityTypeId];
        $this->assign('activityTName', $activityTName[$this->_activityTypeId]);
      }
    }

    // Assign pageTitle to be "Activity - "+ activity name
    if (isset($activityTName)) {
      $pageTitle = 'Activity - ' . CRM_Utils_Array::value($this->_activityTypeId, $activityTName);
      $this->assign('pageTitle', $pageTitle);
    }

    //check the mode when this form is called either single or as
    //search task action
    if ($this->_activityTypeId ||
      $this->_context == 'standalone' ||
      $this->_currentlyViewedContactId
    ) {
      $this->_single = TRUE;
      $this->assign('urlPath', 'civicrm/activity');
    }
    else {
      //set the appropriate action
      $url = CRM_Utils_System::currentPath();
      $urlArray = explode('/', $url);
      $searchPath = array_pop($urlArray);
      $searchType = 'basic';
      $this->_action = CRM_Core_Action::BASIC;
      switch ($searchPath) {
        case 'basic':
          $searchType = $searchPath;
          $this->_action = CRM_Core_Action::BASIC;
          break;

        case 'advanced':
          $searchType = $searchPath;
          $this->_action = CRM_Core_Action::ADVANCED;
          break;

        case 'builder':
          $searchType = $searchPath;
          $this->_action = CRM_Core_Action::PROFILE;
          break;

        case 'custom':
          $this->_action = CRM_Core_Action::COPY;
          $searchType = $searchPath;
          break;
      }

      parent::preProcess();
      $this->_single = FALSE;

      $this->assign('urlPath', "civicrm/contact/search/$searchType");
      $this->assign('urlPathVar', "_qf_Activity_display=true&qfKey={$this->controller->_key}");
    }

    $this->assign('single', $this->_single);
    $this->assign('action', $this->_action);

    if ($this->_activityTypeId) {
      //set activity type name and description to template
      list($this->_activityTypeName, $activityTypeDescription) = CRM_Core_BAO_OptionValue::getActivityTypeDetails($this->_activityTypeId);
      $this->assign('activityTypeName', $this->_activityTypeName);
      $this->assign('activityTypeDescription', $activityTypeDescription);
    }

    // set user context
    $urlParams = $urlString = NULL;
    $qfKey = CRM_Utils_Request::retrieve('key', 'String', $this);
    if (!$qfKey) {
      $qfKey = CRM_Utils_Request::retrieve('qfKey', 'String', $this);
    }

    //validate the qfKey
    if (!CRM_Utils_Rule::qfKey($qfKey)) {
      $qfKey = NULL;
    }

    if ($this->_context == 'fulltext') {
      $keyName = '&qfKey';
      $urlParams = 'force=1';
      $urlString = 'civicrm/contact/search/custom';
      if ($this->_action == CRM_Core_Action::UPDATE) {
        $keyName = '&key';
        $urlParams .= '&context=fulltext&action=view';
        $urlString = 'civicrm/contact/view/activity';
      }
      if ($qfKey) {
        $urlParams .= "$keyName=$qfKey";
      }
      $this->assign('searchKey', $qfKey);
    }
    elseif (in_array($this->_context, array(
      'standalone',
      'home',
      'dashlet',
      'dashletFullscreen'
    ))
    ) {
      $urlParams = 'reset=1';
      $urlString = 'civicrm/dashboard';
    }
    elseif ($this->_context == 'search') {
      $urlParams = 'force=1';
      if ($qfKey) {
        $urlParams .= "&qfKey=$qfKey";
      }
      $path = CRM_Utils_System::currentPath();
      if ($this->_compContext == 'advanced' ) {
        $urlString = 'civicrm/contact/search/advanced';
      }
      elseif ($path == 'civicrm/contact/search'
        || $path == 'civicrm/contact/search/advanced'
        || $path == 'civicrm/contact/search/custom') {
        $urlString = $path;
      }
      else {
        $urlString = 'civicrm/activity/search';
      }
      $this->assign('searchKey', $qfKey);
    }
    elseif ($this->_context != 'caseActivity') {
      $urlParams = "action=browse&reset=1&cid={$this->_currentlyViewedContactId}&selectedChild=activity";
      $urlString = 'civicrm/contact/view';
    }

    if ($urlString) {
      $session->pushUserContext(CRM_Utils_System::url($urlString, $urlParams));
    }

    // hack to retrieve activity type id from post variables
    if (!$this->_activityTypeId) {
      $this->_activityTypeId = CRM_Utils_Array::value('activity_type_id', $_POST);
    }

    $this->setFields();

    $this->_values = $this->get('values');
    if (!is_array($this->_values)) {
      $this->_values = array();
      if (isset($this->_activityId) && $this->_activityId) {
        $params = array('id' => $this->_activityId);
        CRM_Activity_BAO_Activity::retrieve($params, $this->_values);
      }
      $this->set('values', $this->_values);
    }
    $this->_assigneeContactId = $this->setRelatedIDs('Activity Assignees');
    $this->_sourceContactId = $this->setRelatedIDs('Activity Source');
    $this->_targetContactId = $this->setRelatedIDs('Activity Targets');
    parent::preProcess();
  }

  public function setRelatedIDs($type) {
    $IDs = implode(',', $this->_activityHolderIds);
    $contactIDs = array();
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $sourceID = CRM_Utils_Array::key($type, $activityContacts);
    $query = "
SELECT contact_id, activity_id
FROM   civicrm_activity_contact
WHERE  activity_id IN ( $IDs ) AND
       record_type_id = {$sourceID}";

    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
      $contactIDs[] = $dao->contact_id;
      if ($type == "Activity Targets") {
        $this->_targetContactIDs[$dao->activity_id][] = $dao->contact_id;
      }
    }
    return $contactIDs;
  }
  /**
   * This function sets the default values for the form. For edit/view mode
   * the default values are retrieved from the database
   *
   * @access public
   *
   * @return None
   */
  function setDefaultValues() {
    
    $defaults = $this->_values;

    // if we're editing...
    if (isset($this->_activityHolderIds)) {

      $defaults['source_contact_id'] = self::_getDisplayNameById($this->_currentUserId);
      $defaults['source_contact_qid'] = $this->_currentUserId;

      if (!CRM_Utils_Array::crmIsEmptyArray($this->_targetContactId)) {
        $target_contact_value = $this->_targetContactId;
        //  $target_contact = array_combine(array_unique($this->_targetContactId), $target_contact_value);

        if ($this->_action & CRM_Core_Action::VIEW) {
          $this->assign('target_contact', $this->_targetContactId);
        }
        else {
          //this assigned variable is used by newcontact creation widget to set defaults
          $this->assign('prePopulateData', $this->formatContactValues($target_contact));
        }
      }

      if (!CRM_Utils_Array::crmIsEmptyArray($this->_assigneeContactId)) {
        foreach ($this->_assigneeContactId as $key => $id) {
          $assignee_contact[$id] = self::_getDisplayNameById($id);
        }
        $this->assign('assignee_contact', $this->formatContactValues($assignee_contact));
      }

      list($defaults['activity_date_time'], $defaults['activity_date_time_time']) = CRM_Utils_Date::setDateDefaults(NULL, 'activityDateTime');

      //set the assignee contact count to template
      if (!empty($defaults['assignee_contact'])) {
        $this->assign('assigneeContactCount', count($this->_assigneeContactId));
      }
      else {
        $this->assign('assigneeContactCount', 1);
      }

      //set the target contact count to template
      if (!empty($defaults['target_contact'])) {
        $this->assign('targetContactCount', count($this->_targetContactId));
      }
      else {
        $this->assign('targetContactCount', 1);
      }

      if ($this->_context != 'standalone') {
        $this->assign('target_contact_value',
          CRM_Utils_Array::value('target_contact_value', $defaults)
        );
        $this->assign('assignee_contact_value',
          CRM_Utils_Array::value('assignee_contact_value', $defaults)
        );
        $this->assign('source_contact_value',
          CRM_Utils_Array::value('source_contact', $defaults)
        );
      }

      // set default tags if exists
      $defaults['tag'] = CRM_Core_BAO_EntityTag::getTag($this->_activityId, 'civicrm_activity');
    }
    else {
      // if it's a new activity, we need to set default values for associated contact fields
      // since those are jQuery fields, unfortunately we cannot use defaults directly
      $this->_sourceContactId = $this->_currentUserId;
      $this->_targetContactId = $this->_currentlyViewedContactId;
      $target_contact = array();

      $defaults['source_contact_id'] = self::_getDisplayNameById($this->_sourceContactId);
      $defaults['source_contact_qid'] = $this->_sourceContactId;
      if ($this->_context != 'standalone' && isset($this->_targetContactId)) {
        $target_contact[$this->_targetContactId] = self::_getDisplayNameById($this->_targetContactId);
      }

      //this assigned variable is used by newcontact creation widget to set defaults
      $this->assign('prePopulateData', $this->formatContactValues($target_contact));

      list($defaults['activity_date_time'], $defaults['activity_date_time_time']) =
        CRM_Utils_Date::setDateDefaults(NULL, 'activityDateTime');
    }

    $defaults['assignee_contact_ids'] = 0;
    return $defaults;
  }

  /**
   * Function to format contact values before assigning to autocomplete widget
   *
   * @param array $contactNames associated array of contact name and ids
   *
   * @return json encoded object
   * @private
   */
  function formatContactValues(&$contactNames) {
    //format target/assignee contact
    $formatContacts = array();
    if (empty($contactNames)) {
      return;
    }
    foreach ($contactNames as $id => $name) {
      $formatContacts[] = array(
        'id' => $id,
        'name' => $name
      );
    }

    return json_encode($formatContacts);
  }

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Create Followup Activities'));
    CRM_Core_Session::setStatus('');
    if (!$this->_single && !empty($this->_targetContactId)) {
      $withArray = array();
      $this->_targetContactId = array_unique($this->_targetContactId);
      foreach ($this->_targetContactId as $contactId) {
        $withDisplayName = self::_getDisplayNameById($contactId);
        $withArray[] = "\"$withDisplayName\" ";
      }
      $this->assign('with', implode(', ', $withArray));
    }

    //build other activity links
    CRM_Activity_Form_ActivityLinks::commonBuildQuickForm($this);

    //enable form element (ActivityLinks sets this true)
    $this->assign('suppressForm', FALSE);

    $element = & $this->add('select', 'activity_type_id', ts('Activity Type'),
      $this->_fields['followup_activity_type_id']['attributes']
    );

    //freeze for update mode.
    if ($this->_action & CRM_Core_Action::UPDATE) {
      $element->freeze();
    }

    foreach ($this->_fields as $field => $values) {
      if (CRM_Utils_Array::value($field, $this->_fields)) {
        $attribute = NULL;
        if (CRM_Utils_Array::value('attributes', $values)) {
          $attribute = $values['attributes'];
        }

        $required = FALSE;
        if (CRM_Utils_Array::value('required', $values)) {
          $required = TRUE;
        }
        if ($values['type'] == 'wysiwyg') {
          $this->addWysiwyg($field, $values['label'], $attribute, $required);
        }
        else {
          $this->add($values['type'], $field, $values['label'], $attribute, $required);
        }
      }
    }

    CRM_Campaign_BAO_Campaign::addCampaign($this, CRM_Utils_Array::value('campaign_id', $this->_values));

    $buildEngagementLevel = FALSE;
    if (CRM_Campaign_BAO_Campaign::isCampaignEnable() &&
      CRM_Campaign_BAO_Campaign::accessCampaign()
    ) {
      $buildEngagementLevel = TRUE;
      $this->add('select', 'engagement_level',
        ts('Engagement Index'),
        array('' => ts('- select -')) + CRM_Campaign_PseudoConstant::engagementLevel()
      );
      $this->addRule('engagement_level',
        ts('Please enter the engagement index as a number (integers only).'),
        'positiveInteger'
      );
    }
    $this->assign('buildEngagementLevel', $buildEngagementLevel);

    // check for survey activity
    $this->_isSurveyActivity = FALSE;

    if ($this->_activityId && CRM_Campaign_BAO_Campaign::isCampaignEnable() &&
      CRM_Campaign_BAO_Campaign::accessCampaign()
    ) {

      $this->_isSurveyActivity = CRM_Campaign_BAO_Survey::isSurveyActivity($this->_activityId);
      if ($this->_isSurveyActivity) {
        $surveyId = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity',
          $this->_activityId,
          'source_record_id'
        );
        $responseOptions = CRM_Campaign_BAO_Survey::getResponsesOptions($surveyId);
        if ($responseOptions) {
          $this->add('select', 'result', ts('Result'),
            array('' => ts('- select -')) + array_combine($responseOptions, $responseOptions)
          );
        }
        $surveyTitle = NULL;
        if ($surveyId) {
          $surveyTitle = CRM_Core_DAO::getFieldValue('CRM_Campaign_DAO_Survey', $surveyId, 'title');
        }
        $this->assign('surveyTitle', $surveyTitle);
      }
    }
    $this->assign('surveyActivity', $this->_isSurveyActivity);

    // this option should be available only during add mode
    if ($this->_action != CRM_Core_Action::UPDATE) {
      $this->add('advcheckbox', 'is_multi_activity', ts('Create a separate activity for each contact.'));
    }

    $this->addRule('duration',
      ts('Please enter the duration as number of minutes (integers only).'), 'positiveInteger'
    );
    $this->addDateTime('activity_date_time', ts('Date'), TRUE, array('formatType' => 'activityDateTime'));

    //add followup date
    $this->addDateTime('followup_date', ts('in'), FALSE, array('formatType' => 'activityDateTime'));

    //autocomplete url
    $dataUrl = CRM_Utils_System::url("civicrm/ajax/rest",
      "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=activity&reset=1",
      FALSE, NULL, FALSE
    );
    $this->assign('dataUrl', $dataUrl);

    //tokeninput url
    $tokenUrl = CRM_Utils_System::url("civicrm/ajax/checkemail",
      "noemail=1",
      FALSE, NULL, FALSE
    );
    $this->assign('tokenUrl', $tokenUrl);

    $admin = CRM_Core_Permission::check('administer CiviCRM');
    //allow to edit source contact field field if context is civicase.
    if ($this->_context == 'caseActivity') {
      $admin = TRUE;
    }

    $this->assign('admin', $admin);

    $sourceContactField = & $this->add($this->_fields['source_contact_id']['type'],
      'source_contact_id',
      $this->_fields['source_contact_id']['label'],
      NULL,
      $admin
    );

    $this->add('hidden', 'source_contact_qid', '', array('id' => 'source_contact_qid'));
    CRM_Contact_Form_NewContact::buildQuickForm($this);
    $options = array(
      ts('Assignee(s) of Existing Activity'),
      ts('Specified Contacts'),
    ); 
    $this->addRadio('assignee_contact_ids', NULL, $options, NULL, '<br />', TRUE);
    $this->add('text', 'assignee_contact_id', ts('assignee'));

    if ($sourceContactField->getValue()) {
      $this->assign('source_contact', $sourceContactField->getValue());
    }
    elseif ($this->_currentUserId) {
      // we're setting currently LOGGED IN user as source for this activity
      $this->assign('source_contact_value', self::_getDisplayNameById($this->_currentUserId));
    }

    $this->addFormRule(array('CRM_Activity_Form_Task_FollowUp', 'formRule'), $this);

    if (CRM_Core_BAO_Setting::getItem(
      CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
      'activity_assignee_notification'
    )
    ) {
      $this->assign('activityAssigneeNotification', TRUE);
    }
    else {
      $this->assign('activityAssigneeNotification', FALSE);
    }
    $this->addButtons(array(
        array(
          'type' => 'upload',
          'name' => ts('Save'),
          'isDefault' => TRUE
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel')
        )
      )
    );
  }

  /**
   * global form rule
   *
   * @param array $fields  the input form values
   * @param array $files   the uploaded files if any
   * @param array $options additional user data
   *
   * @return true if no errors, else array of errors
   * @access public
   * @static
   */
  static function formRule($fields, $files, $self) {
    // skip form rule if deleting
    if (CRM_Utils_Array::value('_qf_Activity_next_', $fields) == 'Delete') {
      return TRUE;
    }
    $errors = array();
    if (!$self->_single && !$fields['activity_type_id']) {
      $errors['activity_type_id'] = ts('Activity Type is a required field');
    }

    if (array_key_exists('activity_type_id', $fields) &&
      !CRM_Utils_Array::value('activity_type_id', $fields)
    ) {
      $errors['activity_type_id'] = ts('Activity Type is required field.');
    }

    if ($fields['source_contact_id'] && !is_numeric($fields['source_contact_qid'])) {
      $errors['source_contact_id'] = ts('Source Contact non-existent!');
    }

    if (CRM_Utils_Array::value('activity_type_id', $fields) == 3 &&
      CRM_Utils_Array::value('status_id', $fields) == 1
    ) {
      $errors['status_id'] = ts('You cannot record scheduled email activity.');
    }
    elseif (CRM_Utils_Array::value('activity_type_id', $fields) == 4 &&
      CRM_Utils_Array::value('status_id', $fields) == 1
    ) {
      $errors['status_id'] = ts('You cannot record scheduled SMS activity.');
    }

    if (CRM_Utils_Array::value('followup_activity_type_id', $fields) && !CRM_Utils_Array::value('followup_date', $fields)) {
      $errors['followup_date_time'] = ts('Followup date is a required field.');
    }
    if ((CRM_Utils_Array::value('followup_activity_subject', $fields) || CRM_Utils_Array::value('followup_date', $fields)) &&
      !CRM_Utils_Array::value('followup_activity_type_id', $fields)
    ) {
      $errors['followup_activity_subject'] = ts('Follow-up Activity type is a required field.');
    }
    return $errors;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess($params = NULL) {

    // store the submitted values in an array
    if (!$params) {
      $params = $this->controller->exportValues($this->_name);
    }

    //set activity type id
    if (!CRM_Utils_Array::value('activity_type_id', $params)) {
      $params['activity_type_id'] = $this->_activityTypeId;
    }
    $params['followup_activity_subject'] = CRM_Utils_Array::value('subject', $params);
    $params['followup_activity_type_id'] = $params['activity_type_id'];
    $params['followup_date'] = $params['activity_date_time'];
    $params['followup_date_time'] = $params['activity_date_time_time'];
    // store the date with proper format
    $params['activity_date_time'] = CRM_Utils_Date::processDate($params['activity_date_time'], $params['activity_date_time_time']);

    // format with contact (target contact) values
    if (isset($params['contact'][1])) {
      $params['target_contact_id'] = explode(',', $params['contact'][1]);
    }
    else {
      $params['target_contact_id'] = array();
    }

    // assigning formated value to related variable
    if (CRM_Utils_Array::value('assignee_contact_id', $params)) {
      $params['assignee_contact_id'] = explode(',', $params['assignee_contact_id']);
    }
    else {
      $params['assignee_contact_id'] = array();
    }

    // get ids for associated contacts
    if (!$params['source_contact_id']) {
      $params['source_contact_id'] = $this->_currentUserId;
    }
    else {
      $params['source_contact_id'] = $this->_submitValues['source_contact_qid'];
    }

    if (isset($this->_activityId)) {
      $params['id'] = $this->_activityId;
    }

    // add attachments as needed
    CRM_Core_BAO_File::formatAttachment($params,
      $params,
      'civicrm_activity',
      $this->_activityId
    );

    // format target params
    if (!$this->_single) {
      $params['target_contact_id'] = $this->_targetContactId;
    }
    $activity = array();
    if (!CRM_Utils_Array::crmIsEmptyArray($params['target_contact_id'])) {
      $targetContacts = $params['target_contact_id'];
      foreach ($this->_activityHolderIds as $activityId) {
        // save activity
        $activity[] = $this->processActivity($params, $activityId);
      }
    }

    return array('activity' => $activity);
  }

  /**
   * Process activity creation
   *
   * @param array $params associated array of submitted values
   * @access protected
   */
  protected function processActivity(&$params, $activityId) {
    $activityAssigned = array();
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
    // format assignee params
    if (!CRM_Utils_Array::crmIsEmptyArray($params['assignee_contact_id'])) {
      $activityAssigned = array_flip($params['assignee_contact_id']);
      if ($this->_activityId) {
        $assigneeContacts = CRM_Activity_BAO_ActivityContact::getNames($this->_activityId, $assigneeID);
        $activityAssigned = array_diff_key($activityAssigned, $assigneeContacts);
      }
    }

    if (CRM_Utils_Array::value('is_multi_activity', $params)) {
      $params['target_contact_id'] = $this->_targetContactIDs[$activityId];
    }
    else {
      $params['target_contact_id'] = $this->_targetContactId;
    }
    $activities[] = $activity = CRM_Activity_BAO_Activity::createFollowupActivity($activityId, $params);

    $this->_activityIds[] = $activity->id; 
    $mailStatus = '';

      if (!CRM_Utils_Array::crmIsEmptyArray($params['assignee_contact_id']) &&
        CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
          'activity_assignee_notification'
        )
      ) {
        $mailToContacts = array();
        $assigneeContacts = CRM_Activity_BAO_ActivityContact::getNames($activity->id, $assigneeID);

        //build an associative array with unique email addresses.
        foreach ($activityAssigned as $id => $dnc) {
          if (isset($id) && array_key_exists($id, $assigneeContacts)) {
            $mailToContacts[$assigneeContacts[$id]['email']] = $assigneeContacts[$id];
          }
        }

        if (!CRM_Utils_Array::crmIsEmptyArray($mailToContacts)) {
          //include attachments while sending a copy of activity.
          $attachments = CRM_Core_BAO_File::getEntityFile('civicrm_activity', $activity->id);

          $ics = new CRM_Activity_BAO_ICalendar($activity);
          $ics->addAttachment($attachments, $mailToContacts);

          CRM_Case_BAO_Case::sendActivityCopy(NULL, $activity->id, $mailToContacts, $attachments, NULL);

          $ics->cleanup();

          $mailStatus .= ts("A copy of the activity has also been sent to assignee contacts(s).");
        }
      }

      // set status message
      $subject = '';
      if (CRM_Utils_Array::value('subject', $params)) {
        $subject = "'" . $params['subject'] . "'";
      }

      $followupStatus = ts('A followup activity has been scheduled.');
      CRM_Core_Session::setStatus(ts('Activity %1 has been saved. %2 %3',
          array(
            1 => $subject,
            2 => $followupStatus,
            3 => $mailStatus
          )
        ), ts('Saved'), 'success');

    return $activities;
  }

  /**
   * Shorthand for getting id by display name (makes code more readable)
   *
   * @access protected
   */
  protected function _getIdByDisplayName($displayName) {
    return CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
      $displayName,
      'id',
      'sort_name'
    );
  }

  /**
   * Shorthand for getting display name by id (makes code more readable)
   *
   * @access protected
   */
  protected function _getDisplayNameById($id) {
    return CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
      $id,
      'sort_name',
      'id'
    );
  }
}

