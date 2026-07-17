<?php

namespace Drupal\ownpage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery of Website Type plugins.
 *
 * Per docs/BUILDER_IMPLEMENTATION_GUIDE.md §39: new website types are added
 * only by registering a plugin under src/Plugin/WebsiteType; BuilderService
 * itself never changes.
 */
class WebsiteTypePluginManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/WebsiteType',
      $namespaces,
      $module_handler,
      'Drupal\ownpage\Plugin\WebsiteType\WebsiteTypeInterface',
      'Drupal\ownpage\Annotation\WebsiteType'
    );
    $this->alterInfo('ownpage_website_type_info');
    $this->setCacheBackend($cache_backend, 'ownpage_website_type_plugins');
  }

}
