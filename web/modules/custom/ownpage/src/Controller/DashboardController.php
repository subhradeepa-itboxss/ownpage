<?php

namespace Drupal\ownpage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ownpage\Service\WebsiteService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DashboardController extends ControllerBase {

  public function __construct(
    protected WebsiteService $websiteService
  ) {}

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ownpage.website_service')
    );
  }

  public function view() {
    return [
      '#markup' => $this->t('Welcome to your OwnPage dashboard. This page will list your websites soon.'),
    ];
  }

  public function websites() {
    $nodes = $this->websiteService->getOwnWebsites((int) $this->currentUser()->id());

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