--
-- Table structure for table `civicrm_groupadmin_entity`
--

CREATE TABLE  IF NOT EXISTS `civicrm_groupadmin_entity` (
  `id` int unsigned NOT NULL AUTO_INCREMENT  ,
  `group_id` int unsigned NOT NULL   COMMENT 'FK to Group ID',
  `contact_id` int unsigned  COMMENT 'FK to contact id',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_groupadmin_entity_group_id FOREIGN KEY (`group_id`)
    REFERENCES `civicrm_group`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_groupadmin_entity_contact_id FOREIGN KEY (`contact_id`)
    REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

ALTER TABLE `civicrm_groupadmin_entity` ADD UNIQUE `unique_index_group_contact`(`group_id`, `contact_id`);
