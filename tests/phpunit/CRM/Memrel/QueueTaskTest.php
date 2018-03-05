<?php

use CRM_Memrel_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tests that the queued task actually facilitates membership conferment.
 *
 * @group headless
 */
class CRM_Memrel_QueueTaskTest extends \CRM_MemrelTest implements HeadlessInterface, TransactionalInterface {

  protected $directMemberContactId;
  protected $indirectMemberContactId;
  protected $membershipId;
  protected $membershipTypeId;
  protected $relationshipId;

  public function setUp() {
    $this->createRelationshipType('pal');
    list($this->directMemberContactId, $this->indirectMemberContactId) = $this->createContacts();

    $relationship = $this->createRelationship(array(
      'contact_id_a' => $this->directMemberContactId,
      'contact_id_b' => $this->indirectMemberContactId,
      'relationship_type_id' => $this->getRelTypeId('pal'),
    ));
    $this->relationshipId = $relationship['id'];

    $membershipType = civicrm_api3('MembershipType', 'create', array(
      'domain_id' => 1,
      'member_of_contact_id' => 1,
      'financial_type_id' => 'Member Dues',
      'duration_unit' => 'year',
      'duration_interval' => 1,
      'period_type' => 'rolling',
      'name' => 'Belieber',
    ));
    $this->membershipTypeId = $membershipType['id'];

    $membership = civicrm_api3('Membership', 'create', array(
      'contact_id' => $this->directMemberContactId,
      'membership_type_id' => $this->membershipTypeId,
    ));
    $this->membershipId = $membership['id'];

    // Now that all the entities are in place, edit membership type config so
    // that the $indirectMemberContactId contact is entitled to a conferred
    // membership.
    civicrm_api3('MembershipType', 'create', array(
      'id' => $this->membershipTypeId,
      'relationship_type_id' => $this->getRelTypeId('pal'),
      'relationship_direction' => 'a_b',
    ));

    parent::setUp();
  }

  /**
   * Test that a conferred membership is created by running the task.
   */
  public function testRelationshipKickTask() {
    $taskContext = new CRM_Queue_TaskContext();

    $task = new CRM_Memrel_QueueTask($this->relationshipId);
    $task->run($taskContext);

    $conferredMembershipCount = civicrm_api3('Membership', 'getcount', array(
      'contact_id' => $this->indirectMemberContactId,
      'membership_type_id' => $this->membershipTypeId,
      'owner_membership_id' => $this->membershipId,
    ));

    $this->assertEquals(1, $conferredMembershipCount);
  }

}
