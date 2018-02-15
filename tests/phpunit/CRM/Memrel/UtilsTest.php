<?php

use CRM_Memrel_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Memrel_UtilsTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * Relationship type IDs of new relationship types for testing, keyed by names.
   *
   * @var array
   */
  private $relTypeIds = array();

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->callback(function() {
        $admin = civicrm_api3('RelationshipType', 'create', array(
          'name_a_b' => 'test_admin',
          'name_b_a' => 'test_admin',
        ));

        $exec = civicrm_api3('RelationshipType', 'create', array(
          'name_a_b' => 'test_exec',
          'name_b_a' => 'test_exec',
        ));

        Civi::settings()->set('memrel_mapping', array(
          CRM_Memrel_Utils::getConfermentRelTypeId() => array($admin['id'], $exec['id']),
        ));
      }, 'configureRelationships')
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Helper function for retrieving relationship type IDs by name.
   *
   * Assumes the same name is used for both directions of the relationship.
   *
   * @param string $name
   *   The relationship type name.
   * @return string
   *   The relationship type ID.
   */
  private function getRelTypeId($name) {
    if (!isset($this->relTypeIds[$name])) {
      $this->relTypeIds[$name] = civicrm_api3('RelationshipType', 'getvalue', array(
        'return' => 'id',
        'name_a_b' => $name,
        'name_b_a' => $name,
      ));
    }
    return $this->relTypeIds[$name];
  }

  /**
   * Test that we can get the "shadow" relationship type for a relationship type
   * configured to confer membership.
   */
  public function testSuccessGetAssocConfermentRelTypeId() {
    $execRelTypeId = $this->getRelTypeId('test_exec');
    $result = CRM_Memrel_Utils::getAssocConfermentRelTypeIds($execRelTypeId);
    $this->assertInternalType('array', $result);

    $shadowId = CRM_Memrel_Utils::getConfermentRelTypeId();
    $this->assertContains($shadowId, $result);
  }

  /**
   * Test that a relationship type NOT configured to confer membership returns
   * an empty array.
   */
  public function testFailureGetAssocConfermentRelTypeId() {
    $noConfer = civicrm_api3('RelationshipType', 'create', array(
      'name_a_b' => 'test_notConfiguredForConferment',
      'name_b_a' => 'test_notConfiguredForConferment',
    ));
    $actual = CRM_Memrel_Utils::getAssocConfermentRelTypeIds($noConfer['id']);
    $this->assertInternalType('array', $actual);
    $this->assertCount(0, $actual);
  }

}
