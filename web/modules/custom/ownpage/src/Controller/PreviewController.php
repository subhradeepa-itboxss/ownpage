<?php

namespace Drupal\ownpage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\ownpage\Service\BuilderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preview step of the Builder wizard.
 *
 * Read-only: renders the node through the entity view builder, reusing the
 * site's existing "default" view display and the op-template / op-theme
 * public styling (ownpage_theme_preprocess_node() + scss/style.scss) — no
 * new Twig template needed.
 */
class PreviewController extends ControllerBase {

  // Named ...Service, not $entityTypeManager, because ControllerBase already
  // declares an untyped $entityTypeManager property for its own lazy
  // entityTypeManager() accessor; a typed override of it is a PHP fatal.
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManagerService,
    protected BuilderService $builderService,
  ) {}

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('ownpage.builder')
    );
  }

  public function preview(NodeInterface $node) {
    $build['preview'] = $this->entityTypeManagerService->getViewBuilder('node')->view($node, 'default');

    $previousRoute = $this->builderService->getPreviousStepRoute($node, 'preview');
    if ($previousRoute) {
      $build['back'] = Link::fromTextAndUrl(
        $this->t('Back'),
        Url::fromRoute($previousRoute, ['node' => $node->id()])
      )->toRenderable();
    }

    $nextRoute = $this->builderService->getNextStepRoute($node, 'preview');
    if ($nextRoute) {
      $build['publish'] = Link::fromTextAndUrl(
        $this->t('Publish'),
        Url::fromRoute($nextRoute, ['node' => $node->id()])
      )->toRenderable();
    }

    return $build;
  }

}
