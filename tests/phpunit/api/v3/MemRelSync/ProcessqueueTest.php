<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tests our ability to process queues.
 *
 * @group headless
 */
class api_v3_MemRelSync_ProcessqueueTest extends \CRM_MemrelTest implements HeadlessInterface, TransactionalInterface {

  /**
   * Number of dummy relationships to create on setup.
   *
   * Needs to be large enough that the queue can't process them all in one
   * second and small enough that they can be processed in a few seconds.
   *
   * @var int
   */
  private $numberOfDummyRelationships = 500;

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    $this->createRelationshipType('test_admin');
    $this->createRelationshipType('test_exec');

    for ($i = 0; $i < $this->numberOfDummyRelationships; $i += 2) {
      list($a, $b) = $this->createContacts();
      $this->createRelationship(array(
        'contact_id_a' => $a,
        'contact_id_b' => $b,
        'relationship_type_id' => $this->getRelTypeId('test_admin'),
      ));
      $this->createRelationship(array(
        'contact_id_a' => $a,
        'contact_id_b' => $b,
        'relationship_type_id' => $this->getRelTypeId('test_exec'),
      ));
    }

    $queue = civicrm_api3('MemRelSync', 'createqueue', array());
    $this->assertEquals($this->numberOfDummyRelationships, $queue['count']);

    parent::setUp();
  }

  /**
   * Test timeout parameter.
   */
  public function test_shortTimeout_processQueue() {
    $maxRunTime = 1;

    $start = microtime(TRUE);
    civicrm_api3('MemRelSync', 'processqueue', array(
      'max_run_time' => $maxRunTime,
    ));
    $end = microtime(TRUE);

    // Test that queue processing runs approximately the max time.
    $this->assertEquals($maxRunTime, round($end - $start));

    $select = CRM_Utils_SQL_Select::from('civicrm_queue_item')
      ->select('COUNT(*) as cnt')
      ->where('queue_name = @name', array('name' => CRM_Memrel_QueueManager::NAME));
    $remaining = CRM_Core_DAO::singleValueQuery($select->toSQL());

    // Test that some but not all items were processed.
    $this->assertTrue($this->numberOfDummyRelationships > $remaining && $remaining > 0);
  }

  /**
   * Test the essential queue-processing features of the API.
   */
  public function test_success_processQueue() {
    $api = civicrm_api3('MemRelSync', 'processqueue', array());

    $count = CRM_Utils_SQL_Select::from('civicrm_queue_item')
        ->select('COUNT(*) as cnt')
        ->where('queue_name = @name', array('name' => CRM_Memrel_QueueManager::NAME));

    // Test that the queue is shortened by a run of the processor.
    $this->assertLessThan($this->numberOfDummyRelationships, CRM_Core_DAO::singleValueQuery($count->toSQL()));

    // Test return parameters for accuracy.
    $this->assertEquals($this->numberOfDummyRelationships, $api['values']['qtyProcessed']);
    $this->assertEquals(0, $api['values']['qtyRemaining']);
  }

}
