<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tests our ability to create queues for later processing.
 *
 * @group headless
 */
class api_v3_MemRelSync_CreatequeueTest extends \CRM_MemrelTest implements HeadlessInterface, TransactionalInterface {

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    $this->createRelationshipType('test_admin');
    $this->createRelationshipType('test_exec');
    $this->createRelationshipType('test_secretary');
  }

  /**
   * Test that a queue item is created for each qualified relationship.
   */
  public function test_success_createQueue() {
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
    $expected = 2;

    $test = civicrm_api3('MemRelSync', 'CreateQueue', array());
    // tests custom logic around calculating the count
    $this->assertEquals($expected, $test['count']);

    $selectCount = CRM_Utils_SQL_Select::from('civicrm_queue_item')
      ->select('COUNT(*) as cnt')
      ->where('queue_name = @name', array('name' => CRM_Memrel_QueueManager::NAME));

    // tests that the queue items were actually created
    $this->assertEquals($expected, CRM_Core_DAO::singleValueQuery($selectCount->toSQL()));

    $selectTask = CRM_Utils_SQL_Select::from('civicrm_queue_item')
      ->select('data')
      ->where('queue_name = @name', array('name' => CRM_Memrel_QueueManager::NAME))
      ->limit(1);

    $task = CRM_Core_DAO::singleValueQuery($selectTask->toSQL());
    $this->assertInstanceOf('CRM_Memrel_QueueTask', unserialize($task));
  }

}
