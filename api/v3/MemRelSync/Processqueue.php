<?php
use CRM_Memrel_ExtensionUtil as E;

/**
 * MemRelSync.processqueue API specification (optional)
 *
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_mem_rel_sync_processqueue_spec(&$spec) {
  $spec['max_run_time'] = array(
    'title' => 'Maximum run time',
    'description' => 'The maximum number of seconds this API will spend
      processing items in the queue. Additional items will be processed in
      future runs. Useful for avoiding max execution timeouts.',
    'type' => CRM_Utils_Type::T_INT,
    'api.default' => 30,
  );
}

/**
 * MemRelSync.processqueue API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_mem_rel_sync_processqueue($params) {
  // Note: Microtime is returned *with* microsecs, not *as* microsecs. The
  // result is already in seconds, so max_run_time (also in seconds) is sane.
  $stopAt = microtime(TRUE) + $params['max_run_time'];

  $queue = CRM_Memrel_QueueManager::singleton()->getQueue();
  $returnValues = array(
    'qtyProcessed' => 0,
    'qtyRemaining' => $queue->numberOfItems(),
  );
  $runner = new CRM_Queue_Runner(array(
    'errorMode' => CRM_Queue_Runner::ERROR_CONTINUE, // keep going in the case of failures
    'queue' => $queue,
    'title' => E::ts('Memrel Queue Runner'),
  ));

  $continue = TRUE;
  while ($continue && microtime(TRUE) < $stopAt) {
    try {
      $result = $runner->runNext();
      $returnValues['qtyProcessed']++;
      $returnValues['qtyRemaining']--;
      $continue = $result['is_continue'];
    }
    catch (Exception $e) {
      // This is likely only to occur if there are no items in the queue to begin with.
      $result = FALSE;
    }
  }

  return civicrm_api3_create_success($returnValues, $params, 'MemRelSync', 'Processqueue');
}
