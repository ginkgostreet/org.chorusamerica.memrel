<?php

/**
 * An assortment of generic utilities needed by this extension.
 */
class CRM_Memrel_Utils {

  /**
   * Ensures the passed relationship has contact_id_a, contact_id_b, and
   * relationship_type_id properties, fetching them from the DB if necessary.
   *
   * @param CRM_Contact_DAO_Relationship $rel
   *   A relationship object.
   * @throws CRM_Core_Exception
   *   If the properties that make the object useful are not present and cannot
   *   be fetched.
   */
  public static function makeRelationshipUsable(CRM_Contact_DAO_Relationship $rel) {
    if (isset($rel->contact_id_a, $rel->contact_id_b, $rel->relationship_type_id)) {
      return;
    }

    if (!isset($rel->id)) {
      throw new CRM_Core_Exception('Relationship is unusable.', 0, array($rel));
    }

    $api = civicrm_api3('Relationship', 'getsingle', array(
      'id' => $rel->id,
    ));

    foreach (array('contact_id_a', 'contact_id_b', 'relationship_type_id') as $property) {
      if (!isset($rel->$property)) {
        $rel->$property = $api[$property];
      }
    }
  }

}
