<?php

namespace Drupal\ownpage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
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

    $build['create'] = Link::fromTextAndUrl(
      $this->t('Create Website'),
      Url::fromRoute('ownpage.dashboard_websites_add')
    )->toRenderable();

    $items = [];
    foreach ($nodes as $node) {
      $items[] = $node->toLink()->toString();
    }

    $build['list'] = $items
      ? [
        '#theme' => 'item_list',
        '#items' => $items,
        '#title' => $this->t('My Websites'),
      ]
      : ['#markup' => $this->t('You have no websites yet.')];

    return $build;
  }
}