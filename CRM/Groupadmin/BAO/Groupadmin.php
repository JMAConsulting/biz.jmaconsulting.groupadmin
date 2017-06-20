<?php


class CRM_Groupadmin_BAO_Groupadmin extends CRM_Groupadmin_DAO_Groupadmin {

  public static $_groupEntities = array();

  public static function getStaff() {
    $staff = array();
    $result = civicrm_api3('Contact', 'get', array(
      'contact_sub_type' => 'Staff',
      'return' => array('display_name', 'contact_id'),
      'options' => array('limit' => 0),
      'is_deleted' => 0,
    ));
    if ($result['count'] > 0) {
      foreach ($result['values'] as $values) {
        $staff[$values['contact_id']] = $values['display_name'];
      }
    }
    return $staff;
  }

  public static function getGroups() {
    $groups = array();
    $result = civicrm_api3('Group', 'get', array(
      'options' => array('limit' => 0),
      'is_active' => 1,
    ));
    if ($result['count'] > 0) {
      foreach ($result['values'] as $values) {
        $groups[$values['id']] = $values['title'];
      }
    }
    return $groups;
  }

  public static function getEntities() {
    $sql = "SELECT * FROM civicrm_groupadmin_entity";
    $entity = CRM_Core_DAO::executeQuery($sql);
    while ($entity->fetch()) {
      $groupAdmin['staff_id_' . $entity->group_id][] = $entity->contact_id;
      self::$_groupEntities[$entity->group_id][] = $entity->contact_id;
    }
    return $groupAdmin;
  }

  public static function createEntity($params) {
    self::$_groupEntities;
    foreach (self::$_groupEntities as $key => $value) {
      $diff = array_diff($value, $params[$key]);
      if (!empty($diff)) {
        foreach ($diff as $k => $v) {
          CRM_Core_DAO::executeQuery("DELETE FROM civicrm_groupadmin_entity WHERE group_id = {$key} AND contact_id = {$v}");
        }
      }
    }
    foreach ($params as $groupID => $staff) {
      if (empty($staff)) {
        CRM_Core_DAO::executeQuery("DELETE FROM civicrm_groupadmin_entity WHERE group_id = {$groupID}");
      }
      foreach ($staff as $contactID) {
        $sql = "INSERT INTO civicrm_groupadmin_entity (group_id, contact_id) VALUES ({$groupID}, {$contactID})
          ON DUPLICATE KEY UPDATE group_id = {$groupID}, contact_id = {$contactID}";
        CRM_Core_DAO::executeQuery($sql);
      }
    }
  }
}