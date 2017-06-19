<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Groupadmin_Form_Group extends CRM_Core_Form {

  function setDefaultValues() {
    $defaults = CRM_Groupadmin_BAO_Groupadmin::getEntities();
    return $defaults;
  }

  function buildQuickForm() {
    $staff = CRM_Groupadmin_BAO_Groupadmin::getStaff();

    $groups = CRM_Groupadmin_BAO_Groupadmin::getGroups();

    foreach ($groups as $key => $values) {
      $this->add('select', "staff_id_{$key}", ts('Staff in charge of group'), $staff, FALSE,
        array('id' => "staff_id_{$key}", 'multiple' => 'multiple', 'class' => 'crm-select2', 'style' => 'width: 100%;')
      );
    }

    $this->assign('groups', $groups);
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));
    parent::buildQuickForm();
  }

  function postProcess() {
    $values = $this->exportValues();

    foreach ($values as $key => $params) {
      if (strpos($key, 'staff_id_') !== FALSE) {
        $groupid = substr(strrchr($key, "_"), 1);
        $groupAdminParams[$groupid] = $params;
      }
    }

    CRM_Groupadmin_BAO_Groupadmin::createEntity($groupAdminParams);
    CRM_Core_Error::debug( '$groupAdminParams', $groupAdminParams );
    exit;
    parent::postProcess();
  }
}
