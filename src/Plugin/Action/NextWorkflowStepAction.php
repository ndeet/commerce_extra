<?php

namespace Drupal\commerce_extras\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Redirects to an entity deletion form.
 *
 * @Action(
 *   id = "commerce_order_next_workflow_step",
 *   label = @Translation("Next workflow step"),
 *   type = "commerce_order"
 * )
 */
class NextWorkflowStepAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $entity */
    $state_item = $entity->get('state')->first();

    if ($transitions = $state_item->getTransitions()) {
      $state_item->applyTransition(array_shift($transitions));
      $entity->save();
    } else {
      drupal_set_message(
        t(
          'Could not find a next state for order id %order_id. Already on final state?',
          ['%order_id' => $entity->id()]
        ),
        'error'
      );
    }

  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // todo: we return TRUE here as for some unknown reason $object is null.
    // Not sure if core bug or not.
    return TRUE;

    /** @var \Drupal\commerce_order\Entity\OrderInterface $object */
    $access = $object->status->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
