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
          CRM_Memrel_Utils::getDefaultConfermentRelTypeId() => array($admin['id'], $exec['id']),
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

  private function createContacts() {
    $contactA = civicrm_api3('Contact', 'create', array(
      'contact_type' => 'Individual',
      'first_name' => 'A',
    ));
    $contactB = civicrm_api3('Contact', 'create', array(
      'contact_type' => 'Individual',
      'first_name' => 'B',
    ));

    return array($contactA['id'], $contactB['id']);
  }

  /**
   * Test that we can get the "shadow" relationship type for a relationship type
   * configured to confer membership.
   */
  public function test_success_getFiltered_getConfermentRelTypeIds() {
    $execRelTypeId = $this->getRelTypeId('test_exec');
    $result = CRM_Memrel_Utils::getConfermentRelTypeIds($execRelTypeId);
    $this->assertInternalType('array', $result);

    $shadowId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    $this->assertContains($shadowId, $result);
  }

  /**
   * Test that we can get all "shadow" relationship types.
   */
  public function test_success_getAll_getConfermentRelTypeIds() {
    $execRelTypeId = $this->getRelTypeId('test_exec');
    $result = CRM_Memrel_Utils::getConfermentRelTypeIds($execRelTypeId);
    $this->assertInternalType('array', $result);

    $shadowId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    $this->assertContains($shadowId, $result);
  }

  /**
   * Test that a relationship type NOT configured to confer membership returns
   * an empty array.
   */
  public function test_failure_getConfermentRelTypeIds() {
    $noConfer = civicrm_api3('RelationshipType', 'create', array(
      'name_a_b' => 'test_notConfiguredForConferment',
      'name_b_a' => 'test_notConfiguredForConferment',
    ));
    $actual = CRM_Memrel_Utils::getConfermentRelTypeIds($noConfer['id']);
    $this->assertInternalType('array', $actual);
    $this->assertCount(0, $actual);
  }

  /**
   * Test that the conferment relationship ID can be retrieved if contact IDs
   * are passed to the lookup function in the correct A/B order.
   */
  public function test_success_contactsInProvidedOrder_getConfermentRelationshipId() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $relationship = civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'relationship_type_id' => $confermentRelTypeId,
    ));
    $expected = $relationship['id'];
    $actual = CRM_Memrel_Utils::getConfermentRelationshipId($confermentRelTypeId, $a, $b);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test that the conferment relationship ID can be retrieved if contact IDs
   * are passed to the lookup function in reversed A/B order (e.g., the B
   * contact is passed as the first contact).
   */
  public function test_success_contactsInReversedOrder_getConfermentRelationshipId() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $relationship = civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'relationship_type_id' => $confermentRelTypeId,
    ));
    $expected = $relationship['id'];
    // note that the order of arguments 2 and 3 is the only difference from
    // test_success_contactsInProvidedOrder_getConfermentRelationshipId()
    $actual = CRM_Memrel_Utils::getConfermentRelationshipId($confermentRelTypeId, $b, $a);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test that relationship status has no bearing on lookup result.
   */
  public function test_success_relIsInactive_getConfermentRelationshipId() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $relationship = civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => FALSE,
      'relationship_type_id' => $confermentRelTypeId,
    ));
    $expected = $relationship['id'];
    $actual = CRM_Memrel_Utils::getConfermentRelationshipId($confermentRelTypeId, $a, $b);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test that lookup returns FALSE if no conferment relationship exists.
   */
  public function test_failure_noRel_getConfermentRelationshipId() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $result = CRM_Memrel_Utils::getConfermentRelationshipId($confermentRelTypeId, $a, $b);
    $this->assertFalse($result);
  }

  /**
   * Test that qualifying relationships are found if contact IDs
   * are passed to the lookup function in the correct A/B order.
   */
  public function test_success_contactsInProvidedOrder_qualifyingRelationshipExists() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'relationship_type_id' => $this->getRelTypeId('test_exec'),
    ));
    $this->assertTrue(CRM_Memrel_Utils::qualifyingRelationshipExists($confermentRelTypeId, $a, $b));
  }

  /**
   * Test that the qualifying relationships are found if contact IDs
   * are passed to the lookup function in reversed A/B order (e.g., the B
   * contact is passed as the first contact).
   */
  public function test_success_contactsInReversedOrder_qualifyingRelationshipExists() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'relationship_type_id' => $this->getRelTypeId('test_exec'),
    ));
    // note that the order of arguments 2 and 3 is the only difference from
    // test_success_contactsInProvidedOrder_qualifyingRelationshipExists()
    $this->assertTrue(CRM_Memrel_Utils::qualifyingRelationshipExists($confermentRelTypeId, $b, $a));
  }

  public function test_failure_noRel_qualifyingRelationshipExists() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $this->assertFalse(CRM_Memrel_Utils::qualifyingRelationshipExists($confermentRelTypeId, $a, $b));
  }

  /**
   * Test that inactive relationships don't qualify for membership.
   */
  public function test_failure_relIsInactive_qualifyingRelationshipExists() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => FALSE,
      'relationship_type_id' => $this->getRelTypeId('test_exec'),
    ));
    $this->assertFalse(CRM_Memrel_Utils::qualifyingRelationshipExists($confermentRelTypeId, $a, $b));
  }

  /**
   * Test that the lookup safely returns (rather than causing an API exception)
   * if the passed $confermentRelTypeId isn't configured.
   */
  public function test_failure_badConfig_qualifyingRelationshipExists() {
    list($a, $b) = $this->createContacts();
    $this->assertFalse(CRM_Memrel_Utils::qualifyingRelationshipExists($this->getRelTypeId('test_exec'), $a, $b));
  }

  /**
   * Test that an existing, disabled conferment relationship is updated/enabled.
   */
  public function test_success_recordIsDisabled_enableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $params = array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => FALSE,
      'relationship_type_id' => $confermentRelTypeId,
    );
    $expected = civicrm_api3('Relationship', 'create', $params)['id'];
    CRM_Memrel_Utils::enableConferment($confermentRelTypeId, $a, $b);

    $params['is_active'] = TRUE;
    $params['return'] = 'id';
    $actual = civicrm_api3('Relationship', 'getvalue', $params);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test that an existing, disabled conferment relationship is updated/enabled,
   * even if the contact arguments are reversed.
   */
  public function test_success_recordIsDisabledReversedOrder_enableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $params = array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => FALSE,
      'relationship_type_id' => $confermentRelTypeId,
    );
    $expected = civicrm_api3('Relationship', 'create', $params)['id'];
    // note the reversal of the contacts
    CRM_Memrel_Utils::enableConferment($confermentRelTypeId, $b, $a);

    $params['is_active'] = TRUE;
    $params['return'] = 'id';
    $actual = civicrm_api3('Relationship', 'getvalue', $params);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test that no exception is raised and that the relationship is still active
   * if an attempt is made to enable an already enabled conferment.
   */
  public function test_success_alreadyEnabled_enableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $params = array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => TRUE,
      'relationship_type_id' => $confermentRelTypeId,
    );
    $expected = civicrm_api3('Relationship', 'create', $params)['id'];
    CRM_Memrel_Utils::enableConferment($confermentRelTypeId, $a, $b);

    $params['return'] = 'id';
    $actual = civicrm_api3('Relationship', 'getvalue', $params);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test that no exception is raised and that the relationship is still active
   * if an attempt is made to enable an already enabled conferment, even if the
   * contact arguments are reversed.
   */
  public function test_success_alreadyEnabledReversedOrder_enableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $params = array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => TRUE,
      'relationship_type_id' => $confermentRelTypeId,
    );
    $expected = civicrm_api3('Relationship', 'create', $params)['id'];
    // note the reversal of contacts
    CRM_Memrel_Utils::enableConferment($confermentRelTypeId, $b, $a);

    $params['return'] = 'id';
    $actual = civicrm_api3('Relationship', 'getvalue', $params);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test that a conferment relationship is created in the case that there are
   * no previously existing ones to update.
   */
  public function test_success_newRecord_enableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    CRM_Memrel_Utils::enableConferment($confermentRelTypeId, $a, $b);

    $actual = civicrm_api3('Relationship', 'getcount', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => TRUE,
      'relationship_type_id' => $confermentRelTypeId,
    ));
    $this->assertEquals(1, $actual);
  }

  /**
   * Test that conferment relationship is deleted.
   */
  public function test_success_recordIsEnabled_disableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $params = array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => TRUE,
      'relationship_type_id' => $confermentRelTypeId,
    );
    $relationshipId = civicrm_api3('Relationship', 'create', $params)['id'];
    CRM_Memrel_Utils::disableConferment($confermentRelTypeId, $a, $b);

    $actual = civicrm_api3('Relationship', 'getcount', array(
      'id' => $relationshipId,
    ));
    $this->assertEquals(0, $actual);
  }

  /**
   * Test that conferment relationship is deleted even if contact params are reversed.
   */
  public function test_success_recordIsEnabledReversedOrder_disableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $params = array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => TRUE,
      'relationship_type_id' => $confermentRelTypeId,
    );
    $relationshipId = civicrm_api3('Relationship', 'create', $params)['id'];
    // note the reversal of contacts
    CRM_Memrel_Utils::disableConferment($confermentRelTypeId, $b, $a);

    $actual = civicrm_api3('Relationship', 'getcount', array(
      'id' => $relationshipId,
    ));
    $this->assertEquals(0, $actual);
  }

  /**
   * Test that conferment relationship is deleted even if already disabled.
   */
  public function test_success_recordIsDisabled_disableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $params = array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => FALSE,
      'relationship_type_id' => $confermentRelTypeId,
    );
    $relationshipId = civicrm_api3('Relationship', 'create', $params)['id'];
    CRM_Memrel_Utils::disableConferment($confermentRelTypeId, $a, $b);

    $actual = civicrm_api3('Relationship', 'getcount', array(
      'id' => $relationshipId,
    ));
    $this->assertEquals(0, $actual);
  }

  /**
   * Test that conferment relationship is deleted even if already disabled and
   * contact params are reversed.
   */
  public function test_success_recordIsDisabledReversedOrder_disableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    $params = array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'is_active' => FALSE,
      'relationship_type_id' => $confermentRelTypeId,
    );
    $relationshipId = civicrm_api3('Relationship', 'create', $params)['id'];
    // note the reversal of contacts
    CRM_Memrel_Utils::disableConferment($confermentRelTypeId, $b, $a);

    $actual = civicrm_api3('Relationship', 'getcount', array(
      'id' => $relationshipId,
    ));
    $this->assertEquals(0, $actual);
  }

  /**
   * Test that, in case conferment is disabled between contacts between which no
   * conferment relationship exists, that no exception is raised.
   */
  public function test_success_noRecord_disableConferment() {
    $confermentRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    list($a, $b) = $this->createContacts();
    CRM_Memrel_Utils::disableConferment($confermentRelTypeId, $a, $b);

    $actual = civicrm_api3('Relationship', 'getcount', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'relationship_type_id' => $confermentRelTypeId,
    ));
    $this->assertEquals(0, $actual);
  }

  /**
   * Test that conferment sync will create the "shadow" relationship for
   * contacts with a qualifying relationship.
   *
   * Note: most of the edge cases and heavy lifting are handled in other, more
   * thoroughly tested methods than this one. The tested method contains almost
   * no logic of its own.
   */
  public function test_create_doConfermentSync() {
    list($a, $b) = $this->createContacts();
    civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'relationship_type_id' => $this->getRelTypeId('test_exec'),
    ));
    CRM_Memrel_Utils::doConfermentSync($a, $b);

    $shadowRelTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    $test = CRM_Memrel_Utils::getConfermentRelationshipId($shadowRelTypeId, $a, $b);
    // a non-FALSE result indicates the "shadow" relationship was created
    $this->assertTrue($test !== FALSE);
  }

  /**
   * Test that conferment sync will delete the "shadow" relationship for
   * contacts without a qualifying relationship.
   *
   * Note: most of the edge cases and heavy lifting are handled in other, more
   * thoroughly tested methods than this one. The tested method contains almost
   * no logic of its own.
   */
  public function test_delete_doConfermentSync() {
    list($a, $b) = $this->createContacts();
    $shadowRel = civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $a,
      'contact_id_b' => $b,
      'relationship_type_id' => CRM_Memrel_Utils::getDefaultConfermentRelTypeId(),
    ));
    CRM_Memrel_Utils::doConfermentSync($a, $b);

    $actual = civicrm_api3('Relationship', 'getcount', array(
      'id' => $shadowRel['id'],
    ));
    $this->assertEquals(0, $actual);
  }

}
