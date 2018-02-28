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
  // setting format is $shadowRelTypeId => array($shadowed1, $shadowed2, etc.)
  $relTypeIds = array();
  foreach (Civi::settings()->get('memrel_mapping') as $config) {
    $relTypeIds = array_merge($relTypeIds, $config);
  }
  $relTypeIds = array_unique($relTypeIds);

  if (empty($relTypeIds)) {
    throw new API_Exception(E::LONG_NAME . ' must be configured');
  }

  $api = civicrm_api3('Relationship', 'get', array(
    'options' => array('limit' => 0),
    'relationship_type_id' => array('IN' => $relTypeIds),
    'return' => array('contact_id_a', 'contact_id_b'),
  ));

  $cnt = 0; // used to override the default API count output
  $queue = CRM_Memrel_QueueManager::singleton()->getQueue();
  foreach ($api['values'] as $data) {
    $queue->createItem($data);
    $cnt++;
  }

  $dao = NULL; // needed because the arg is passed by reference
  return civicrm_api3_create_success(1, $params, 'MemRelSync', 'Createqueue', $dao, array('count' => $cnt));
}
