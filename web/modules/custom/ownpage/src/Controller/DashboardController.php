<?php

namespace Drupal\ownpage\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for the OwnPage customer dashboard.
 */
class DashboardController extends ControllerBase {

  /**
   * Displays the customer dashboard.
   *
   * @return array
   *   A render array.
   */
  public function view() {
    return [
      '#markup' => $this->t('Welcome to your OwnPage dashboard. This page will list your websites soon.'),
    ];
  }

  /**
   * Lists all Website nodes owned by the current user.
   *
   * @return array
   *   A render array.
   */
  public function websites() {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $storage->getQuery()
  ->condition('type', 'website')
  ->condition('uid', $this->currentUser()->id())
  ->accessCheck(TRUE)
  ->execute();

    $nodes = $storage->loadMultiple($nids);

    $items = [];
    foreach ($nodes as $node) {
      $items[] = $node->toLink()->toString();
    }

    if (empty($items)) {
      return [
        '#markup' => $this->t('You have no websites yet.'),
      ];
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#title' => $this->t('My Websites'),
    ];
  }

}
