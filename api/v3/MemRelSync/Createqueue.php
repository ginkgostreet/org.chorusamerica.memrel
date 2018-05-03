<?php
use CRM_Memrel_ExtensionUtil as E;

/**
 * MemRelSync.createqueue API specification (optional)
 *
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_mem_rel_sync_createqueue_spec(&$spec) {
  $spec['rel_type_id'] = array(
    'title' => 'Relationship type ID',
    'description' => 'The ID of a relationship type to "kick" to trigger
      membership conferment behavior. Useful in cases where a membership type
      has been reconfigured to confer based on different relationship types.',
    'type' => CRM_Utils_Type::T_INT,
    'FKApiName' => 'RelationshipType',
    'api.required' => 1,
  );
}

/**
 * MemRelSync.createqueue API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_mem_rel_sync_createqueue($params) {
  // Let's be paranoid about SQL injection. Make sure we've got an array of integers.
  $relTypeIds = (array) $params['rel_type_id'];
  foreach ($relTypeIds as $id) {
    // Throws exception for invalid parameters.
    CRM_Utils_Type::validate($id, 'Int', TRUE, 'One of the relationship type IDs', TRUE);
  }
  // End paranoia.

  $query = '
    SELECT r.id FROM civicrm_contact conferee
    -- limit selection of conferee contacts to those with conferring relationships
    INNER JOIN civicrm_relationship r
    ON r.contact_id_a = conferee.id -- all the membership types are configured to confer to the "A" contact
    AND r.is_active = 1
    AND r.relationship_type_id IN (' . implode(', ', $relTypeIds) . ')
    -- limit selection to contacts with a relationship to a current member
    INNER JOIN civicrm_membership m_conferer
    ON r.contact_id_b = m_conferer.contact_id  -- all the membership types are configured to confer from the "B" contact
    AND m_conferer.status_id IN (1, 2, 3)
    -- exclude contacts who already have conferred memberships
    LEFT JOIN civicrm_membership m_conferee
    ON m_conferee.contact_id = conferee.id
    AND m_conferee.owner_membership_id = m_conferer.id
    AND m_conferee.status_id = m_conferer.status_id
    WHERE m_conferee.id IS NULL';
  $result = CRM_Core_DAO::executeQuery($query);

  $cnt = 0; // used to override the default API count output
  $queue = CRM_Memrel_QueueManager::singleton()->getQueue();
  while ($result->fetch()) {
    $task = new CRM_Memrel_QueueTask($result->id);
    $queue->createItem($task);
    $cnt++;
  }

  $dao = NULL; // needed because the arg is passed by reference
  return civicrm_api3_create_success(1, $params, 'MemRelSync', 'Createqueue', $dao, array('count' => $cnt));
}
