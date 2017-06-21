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
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function groupadmin_civicrm_xmlMenu(&$files) {
  _groupadmin_civix_civicrm_xmlMenu($files);
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
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function groupadmin_civicrm_uninstall() {
  _groupadmin_civix_civicrm_uninstall();
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
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function groupadmin_civicrm_disable() {
  _groupadmin_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function groupadmin_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _groupadmin_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function groupadmin_civicrm_managed(&$entities) {
  _groupadmin_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function groupadmin_civicrm_caseTypes(&$caseTypes) {
  _groupadmin_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function groupadmin_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _groupadmin_civix_civicrm_alterSettingsFolders($metaDataFolders);
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
