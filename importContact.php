<?php
header('Content-type: text/html; charset=utf-8');
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
global $user;


if (in_array('administrator', array_values($user->roles))) {
  civicrm_initialize();
  $dao = CRM_Core_DAO::executeQuery("SELECT FromName AS name, From_Address AS email, ToName AS target, CCName as ccname, DateReceived as date, Subject as subject, Body as body 
      FROM pj_emails.Emails WHERE FromType LIKE '%EX%'");
  // The imported DB.
  while ($dao->fetch()) {
    $cid = array();
    $ruleType = 'Unsupervised'; // Can contain any one of  -  Supervised, Unsupervised, General

    $dupesParams = array(
      'display_name' => $dao->name,
      'sort_name' => $dao->name,
      'email' => $dao->email,
    );
    $dedupeParams = CRM_Dedupe_Finder::formatParams($dupesParams, 'Individual');
    $dupes = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual', $ruleType);
    /* $sql = "SELECT c.id, e.email */
    /*   FROM civicrm_contact c */
    /*   LEFT JOIN civicrm_email e ON e.contact_id = c.id */
    /*   WHERE c.display_name = '$dao->name'  */
    /*   OR c.sort_name = '$dao->name'  */
    /*   OR e.email = '$dao->email'";   //Check if either name or email is present  */
    /* $result = CRM_Core_DAO::executeQuery($sql); */
    $params = array(
      'display_name' => $dao->name,
      'sort_name' => $dao->name,
      'email' => $dao->email,
      'contact_type' => 'Individual',
    );
    if ($dupes) { // if there is a result (i.e Contact is present)
      $params['id'] = $dupes[0];
    }
    echo "<b>Name:</b> $dao->name <br/>";
    echo "<b>Email:</b> $dao->email <br/><br/><br/>";
    $contact = civicrm_api3('Contact', 'create', $params);
    //Get the target contacts
    $cc = explode(';', $dao->ccname);
    $to = explode(';', $dao->target);
    $targets = array_merge($cc, $to);
    //Retrieve the contacts for Activity creation
    foreach ($targets as $sort) {
      $sortNames = explode('-', $sort);
      if (!empty($sortNames[0])) {
        $cid[] = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $sortNames[0], 'id', 'sort_name');
      }
    }
    // Activity creation - TODO
    $activityParams = array(
      'activity_type_id' => 12, // Inbound Email
      'source_contact_id' => $contact['id'],
      'subject' => $dao->subject,
      'activity_date_time' => $dao->date,
      'status_id' => 2,
      'target_contact_id' => array_unique(array_filter($cid)),
      'details' => "$dao->body",
    );
    $activity = civicrm_api3('Activity', 'create', $activityParams);
  }
}
else {
  echo "You need to login with an administrator account.";
}
?>