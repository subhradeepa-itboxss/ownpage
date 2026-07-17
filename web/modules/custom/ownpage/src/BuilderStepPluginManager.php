<?php

namespace Drupal\ownpage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery of Builder wizard step plugins.
 *
 * Per docs/BUILDER_IMPLEMENTATION_GUIDE.md §41-42 ("Builder Step Manager"):
 * discovers step plugins, sorts by weight, and filters unsupported steps —
 * BuilderService itself never hardcodes the step order.
 */
class BuilderStepPluginManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/BuilderStep',
      $namespaces,
      $module_handler,
      'Drupal\ownpage\Plugin\BuilderStep\BuilderStepInterface',
      'Drupal\ownpage\Annotation\BuilderStep'
    );
    $this->alterInfo('ownpage_builder_step_info');
    $this->setCacheBackend($cache_backend, 'ownpage_builder_step_plugins');
  }

  /**
   * All discovered steps, filtered by website type and sorted by weight.
   *
   * @return \Drupal\ownpage\Plugin\BuilderStep\BuilderStepInterface[]
   */
  public function getOrderedSteps(?string $websiteTypeId = NULL): array {
    $steps = [];
    foreach ($this->getDefinitions() as $id => $definition) {
      $step = $this->createInstance($id);
      if ($step->supportsWebsiteType($websiteTypeId)) {
        $steps[] = $step;
      }
    }
    usort($steps, static fn ($a, $b) => $a->weight() <=> $b->weight());
    return $steps;
  }

}
