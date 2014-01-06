<?php
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

civicrm_initialize();
$dao = CRM_Core_DAO::executeQuery("SELECT FromName AS name, From_Address AS email, ToName AS target, CCName as ccname FROM pj_emails.Emails"); // The imported DB.
while ($dao->fetch()) {
  $sql = "SELECT c.id, e.email
    FROM civicrm_contact c
    LEFT JOIN civicrm_email e ON e.contact_id = c.id
    WHERE c.display_name = '$dao->name' OR c.sort_name = '$dao->name' OR e.email = '$dao->email'"; //Check if either name or email is present 
  $result = CRM_Core_DAO::executeQuery($sql);
  $params = array(
    'display_name' => $dao->name,
    'sort_name' => $dao->name,
    'email' => $dao->email,
    'contact_type' => 'Individual',
  );
  while ($result->fetch()) { // if there is a result (i.e Contact is present)
    $params['id'] = $result->id;
  }
  //$contact = civicrm_api3('Contact', 'create', $params); Uncomment this when script is ready to create contacts.
  // Activity creation - TODO
  $activityParams = array(
    
  );
}
  exit;
?>