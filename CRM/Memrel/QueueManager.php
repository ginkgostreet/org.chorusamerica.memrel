<?php

/**
 * Helper for this extension's queue.
 */
class CRM_Memrel_QueueManager {

  const NAME = 'org.chorusamerica.memrel.sync';

  /**
   * @var CRM_Queue_Queue
   */
  private $queue;

  /**
   * @var CRM_Memrel_QueueManager
   */
  static $singleton;

  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new self();
    }
    return self::$singleton;
  }

  private function __construct() {
    $this->queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => self::NAME,
      'reset' => FALSE,
    ));
  }

  public function getQueue() {
    return $this->queue;
  }

}
