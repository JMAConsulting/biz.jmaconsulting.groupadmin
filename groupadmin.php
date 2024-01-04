<?php

require_once 'groupadmin.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function groupadmin_civicrm_config(&$config) {
  _groupadmin_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function groupadmin_civicrm_install() {
  _groupadmin_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function groupadmin_civicrm_enable() {
  _groupadmin_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
 */
function groupadmin_civicrm_navigationMenu(&$menu) {
  $groupID = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'Contacts', 'id', 'name');
  $maxID = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
  $menu[$groupID]['child'][$maxID + 1] = array(
    'attributes' => array(
      'label' => ts('Assign Group Administrator', array('domain' => 'biz.jmaconsulting.groupadmin')),
      'name' => 'groupadmin',
      'url' => 'civicrm/groupadmin?reset=1',
      'permission' => 'edit groups',
      'operator' => NULL,
      'separator' => NULL,
      'parentID' => $groupID,
      'navID' => $maxID + 1,
      'active' => 1,
    ),
  );
}

/**
 * Implementation of hook_civicrm_pre
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pre
 */
function groupadmin_civicrm_pre($op, $objectName, $id, &$params) {
  if ($op == "create" && $objectName == "Activity" ) {
    $activityTypeID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Inbound SMS');
    if (CRM_Utils_Array::value('activity_type_id', $params) == $activityTypeID && CRM_Utils_Array::value('result', $params)) {
      // This is a callback activity being processed for Inbound SMS.

      // Change status to scheduled.
      $activityStatusID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_status_id', 'Scheduled');
      $params['status_id'] = $activityStatusID;

      // Modify the $params to change the assignee contact ID according to current group administrator.
      // We use contact ID of sender and phone number to determine which group the contact is in.
      $admin = CRM_Groupadmin_BAO_Groupadmin::getGroupAdmin($params['target_contact_id'], $params['phone_number'], $activityTypeID, $activityStatusID);

      if ($admin) {
        $params['assignee_contact_id'] = $admin;
      }
    }
  }
}
