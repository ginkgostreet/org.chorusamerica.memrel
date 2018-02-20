<?php

use CRM_Memrel_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tips:
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Memrel_UtilsTest extends \CRM_MemrelTest implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Test cases like relationship delete, where the object in the hook has only
   * an ID to work with.
   */
  public function test_makeRelationshipUsable_onlyIdAvailable() {
    list($a, $b) = $this->createContacts();
    $childOf = 1;
    $testData = $this->createRelationship(array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'relationship_type_id' => $childOf,
    ));

    $rel = new CRM_Contact_BAO_Relationship();
    $rel->id = $testData['id'];

    CRM_Memrel_Utils::makeRelationshipUsable($rel);

    $this->assertEquals($a, $rel->contact_id_a);
    $this->assertEquals($b, $rel->contact_id_b);
    $this->assertEquals($childOf, $rel->relationship_type_id);
  }

}
