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
   * @param string $relTypeId
   *   The ID of the relationship type which may need shadowing.
   * @return string|FALSE
   *   String ID or FALSE if the relationship type is not configured for conferment.
   */
  public static function getConfermentRelTypeId($relTypeId) {

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
