<?php

/**
 * Task class for this extension's queue.
 */
class CRM_Memrel_QueueTask {

  /**
   * The first of two contact IDs to evaluate for a shadow relationship. Order
   * is insignificant.
   *
   * @var int|string
   */
  private $contactIdA;

  /**
   * The second of two contact IDs to evaluate for a shadow relationship. Order
   * is insignificant.
   *
   * @var int|string
   */
  private $contactIdB;

  /**
   * A printable string which describes this task.
   *
   * @var type
   */
  public $title = NULL;

  /**
   * @param int|string $contactIdA
   *   Contact ID to evaluate for relationship shadowing.
   * @param int|string $contactIdB
   *   Contact ID to evaluate for relationship shadowing.
   */
  public function __construct($contactIdA, $contactIdB) {
    $this->contactIdA = $contactIdA;
    $this->contactIdB = $contactIdB;
    $this->title = "Evaluating contacts $contactIdA and $contactIdB";
  }

  /**
   * Perform the task.
   *
   * @param CRM_Queue_TaskContext $taskCtx
   *   Not sure why the task runner wants to pass this... for logging, perhaps.
   * @return bool
   *   TRUE if task completes successfully
   */
  public function run(CRM_Queue_TaskContext $taskCtx) {
    CRM_Memrel_Conferment::doSync($this->contactIdA, $this->contactIdB);
    return TRUE;
  }

}
