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
   * Creates/enables a "shadow" conferment relationship between two contacts.
   *
   * @param string|int $shadowRelTypeId
   *   The type of relationship that should exist between the two contacts.
   * @param string|int $contactA
   *   Contact ID.
   * @param string|int $contactB
   *   Contact ID.
   * @throws CiviCRM_API3_Exception
   */
  public static function enableConferment($shadowRelTypeId, $contactA, $contactB) {
    $params = array(
      'is_active' => TRUE,
    );

    $relationshipId = self::getConfermentRelationshipId($shadowRelTypeId, $contactA, $contactB);
    if ($relationshipId === FALSE) {
      $params['contact_id_a'] = $contactA;
      $params['contact_id_b'] = $contactB;
      $params['relationship_type_id'] = $shadowRelTypeId;
    }
    else {
      $params['id'] = $relationshipId;
    }
    civicrm_api3('Relationship', 'create', $params);
  }

  /**
   * Deletes a "shadow" conferment relationship between two contacts.
   *
   * @param string|int $shadowRelTypeId
   *   The type of relationship between the two contacts that should be deleted
   *   if it exists.
   * @param string|int $contactA
   *   Contact ID.
   * @param string|int $contactB
   *   Contact ID.
   * @throws CiviCRM_API3_Exception
   */
  public static function disableConferment($shadowRelTypeId, $contactA, $contactB) {
    $relationshipId = self::getConfermentRelationshipId($shadowRelTypeId, $contactA, $contactB);
    if ($relationshipId) {
      civicrm_api3('Relationship', 'delete', array(
        'id' => $relationshipId,
      ));
    }
  }

  /**
   * Returns the IDs of the conferment relationship types associated with the
   * specified relationship type.
   *
   * @param string $relTypeId
   *   The ID of the relationship type which may need shadowing.
   * @return array
   *   Array of relationship type IDs. Empty array if the relationship type is
   *   not configured for conferment.
   */
  public static function getAssocConfermentRelTypeIds($relTypeId) {
    $result = array();
    foreach (Civi::settings()->get('memrel_mapping') as $shadow => $config) {
      if (in_array($relTypeId, $config)) {
        $result[] = $shadow;
      }
    }
    return $result;
  }

  /**
   * Returns the ID of the instance of the specified relationship type between
   * two contacts.
   *
   * @param mixed $relTypeId
   *   The ID of the relationship type to filter by.
   * @param string $contactA
   *   Contact ID.
   * @param string $contactB
   *   Contact ID.
   * @return string|FALSE
   *   Relationship ID or FALSE if none exists.
   */
  public static function getConfermentRelationshipId($relTypeId, $contactA, $contactB) {
    $params = array(
      'return' => 'id',
      'relationship_type_id' => $relTypeId,
    );

    try {
      $result = civicrm_api3('Relationship', 'getvalue', $params + array(
        'contact_id_a' => $contactA,
        'contact_id_b' => $contactB,
      ));
    }
    catch (CiviCRM_API3_Exception $e) {
      // No dice? Try again with the contacts in reverse order.
      try {
        $result = civicrm_api3('Relationship', 'getvalue', $params + array(
          'contact_id_a' => $contactB,
          'contact_id_b' => $contactA,
        ));
      }
      catch (CiviCRM_API3_Exception $e) {
        $result = FALSE;
      }
    }

    return $result;
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
    $qualifyingRelTypeIds = CRM_Utils_Array::value($confermentRelTypeId, Civi::settings()->get('memrel_mapping'), array());
    if (!count($qualifyingRelTypeIds)) {
      return FALSE;
    }

    $params = array(
      'is_active' => TRUE,
      'relationship_type_id' => array('IN' => $qualifyingRelTypeIds),
    );

    $api = civicrm_api3('Relationship', 'get', $params + array(
      'contact_id_a' => $contactA,
      'contact_id_b' => $contactB,
    ));
    $result = ($api['count'] > 0);

    // No dice? Try again with the contacts in reverse order.
    if (!$result) {
      $api = civicrm_api3('Relationship', 'get', $params + array(
        'contact_id_a' => $contactB,
        'contact_id_b' => $contactA,
      ));
      $result = ($api['count'] > 0);
    }

    return $result;
  }

}
