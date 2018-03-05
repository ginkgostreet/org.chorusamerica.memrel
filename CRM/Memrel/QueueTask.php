<?php

/**
 * Task class for this extension's queue.
 *
 * Loads and saves a given relationship (without changes) to trigger
 * CRM_Contact_BAO_Relationship::relatedMemberships(), which is responsible for
 * conferring membership. This indirect approach is preferable to invoking the
 * static method directly because:
 *
 * 1) CRM_Contact_BAO_Relationship::relatedMemberships() is not a supported
 *    extension point, and we have no "contract" with CiviCRM that it will not
 *    change or even be removed.
 * 2) The parameters for CRM_Contact_BAO_Relationship::relatedMemberships() are
 *    not well documented. "Kicking" the relationship with an otherwise
 *    pointless update is easier.
 */
class CRM_Memrel_QueueTask {

  /**
   * The ID of the relationship to "kick."
   *
   * @var int|string
   */
  private $relationshipId;

  /**
   * A printable string which describes this task.
   *
   * @var type
   */
  public $title = NULL;

  /**
   * @param int|string $relationshipId
   *   Relationship ID to "kick" for possible membership conferment.
   */
  public function __construct($relationshipId) {
    $this->relationshipId = CRM_Utils_Type::validate($relationshipId, 'Int');
    $this->title = "Kick relationship ID {$this->relationshipId} to trigger membership conferment behavior";
  }

  /**
   * Perform the task.
   *
   * @param CRM_Queue_TaskContext $taskCtx
   *   Not sure why the task runner wants to pass this... for logging, perhaps.
   * @return bool
   *   TRUE if task completes successfully
   */
  public function run(CRM_Queue_TaskContext $taskCtx) {
    try {
      civicrm_api3('Relationship', 'getsingle', array(
        'id' => $this->relationshipId,
        'api.relationship.create' => array(),
      ));
    }
    catch (Exception $ex) {
      // Nothing to do here. If the API call fails it probably means the
      // relationship has been deleted since being enqueued, which means any
      // conferring memberships would have been deleted along with it.
    }
    return TRUE;
  }

}
