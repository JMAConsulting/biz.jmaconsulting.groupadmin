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

  public static function getGroupAdmin($cid, $phone, $activityTypeID, $status) {
    // Get phone ID.
    $result = civicrm_api3('Phone', 'get', array(
      'return' => array('id'),
      'phone' => $phone,
    ));
    if (!empty($result['id'])) {
      $phoneID = $result['id'];
    }
    $sql = "SELECT GROUP_CONCAT(e.contact_id)
      FROM civicrm_mailing_event_queue q
      INNER JOIN civicrm_mailing_job j ON j.id = q.job_id
      INNER JOIN civicrm_mailing_group g ON g.mailing_id = j.mailing_id
      INNER JOIN civicrm_groupadmin_entity e ON e.group_id = g.entity_id
      WHERE q.contact_id = %1 AND q.phone_id = %2
      GROUP BY q.job_id
      ORDER BY q.job_id DESC
      LIMIT 1";
    $queryParams = array(1 => array($cid, 'Integer'), 2 => array($phoneID, 'Integer'));
    $admins = CRM_Core_DAO::singleValueQuery($sql, $queryParams);
    if (!empty($admins)) {
      $admins = explode(',', $admins);
    }
    else {
      return NULL;
    }

    // Iterate through group admins to find out who should be assigned the issue.
    foreach ($admins as $admin) {
      $sql = "SELECT COUNT(a.id)
        FROM civicrm_activity a
        INNER JOIN civicrm_activity_contact c ON c.activity_id = a.id
        WHERE activity_type_id = %1
        AND c.record_type_id = 1
        AND a.status_id = %2
        AND c.contact_id = %3";
      $queryParams = array(1 => array($activityTypeID, 'Integer'), 2 => array($status, 'Integer'), 3 => array($admin, 'Integer'));

      $count[$admin] = CRM_Core_DAO::singleValueQuery($sql, $queryParams);
    }
    if (!empty($count)) {
      asort($count);
      return key($count);
    }
    return NULL;
  }
}