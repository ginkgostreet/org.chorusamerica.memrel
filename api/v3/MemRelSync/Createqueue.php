<?php
use CRM_Memrel_ExtensionUtil as E;

/**
 * MemRelSync.CreateQueue API
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
    'relationship_type_id' => array('IN' => $params['rel_type_id']),
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
