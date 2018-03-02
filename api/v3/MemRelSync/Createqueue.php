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
 * @throws API_Exception
 */
function civicrm_api3_mem_rel_sync_createqueue($params) {
  $api = civicrm_api3('Relationship', 'get', array(
    'options' => array('limit' => 0),
    'relationship_type_id' => array('IN' => (array) $params['rel_type_id']),
    'return' => array('id'),
  ));

  $cnt = 0; // used to override the default API count output
  $queue = CRM_Memrel_QueueManager::singleton()->getQueue();
  foreach ($api['values'] as $data) {
    $task = new CRM_Memrel_QueueTask($data['id']);
    $queue->createItem($task);
    $cnt++;
  }

  $dao = NULL; // needed because the arg is passed by reference
  return civicrm_api3_create_success(1, $params, 'MemRelSync', 'Createqueue', $dao, array('count' => $cnt));
}
