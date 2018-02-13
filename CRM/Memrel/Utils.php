<?php

/**
 * A collection of static utilities used to determine whether a "shadow"
 * conferment relationship is needed for a pair of contacts (and to manage it
 * when appropriate).
 */
class CRM_Memrel_Utils {

  /**
   *
   * @param type $relTypeId
   * @param type $contactA
   * @param type $contactB
   */
  public static function doConfermentSync($relTypeId, $contactA, $contactB) {

  }

  /**
   * Returns the ID of the conferment relationship type associated with the
   * specified relationship type.
   *
   * @param string $relTypeId
   *   The ID of the relationship type which may need shadowing.
   * @return string|FALSE
   *   String ID or FALSE if the relationship type is not configured for conferment.
   */
  public static function getAssocConfermentRelTypeId($relTypeId) {

  }

  /**
   * @param string $contactA
   *   Contact ID.
   * @param string $contactB
   *   Contact ID.
   * @return string|FALSE
   *   Relationship ID or FALSE if none exists.
   */
  public static function getConfermentRelationshipId($contactA, $contactB) {

  }

  /**
   * Returns the ID of the relationship type installed by this extension.
   *
   * @return int
   */
  public static function getConfermentRelTypeId() {
    return (int) civicrm_api3('RelationshipType', 'getvalue', array(
      'return' => 'id',
      'name_a_b' => 'membership_conferment',
      'name_b_a' => 'membership_conferment',
    ));
  }

  /**
   * @param mixed $confermentRelTypeId
   *   The ID of the "shadow" relationship type the contacts may qualify for.
   * @param type $contactA
   *   Contact ID.
   * @param type $contactB
   *   Contact ID.
   * @return bool
   */
  public static function qualifyingRelationshipExists($confermentRelTypeId, $contactA, $contactB) {

  }

}
