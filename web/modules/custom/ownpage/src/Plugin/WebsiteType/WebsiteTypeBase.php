<?php

namespace Drupal\ownpage\Plugin\WebsiteType;

use Drupal\Core\Plugin\PluginBase;

/**
 * Base class providing shared plumbing for Website Type plugins.
 */
abstract class WebsiteTypeBase extends PluginBase implements WebsiteTypeInterface {

  public function id(): string {
    return $this->pluginDefinition['id'];
  }

  public function label(): string {
    return (string) $this->pluginDefinition['label'];
  }

  public function weight(): int {
    return (int) ($this->pluginDefinition['weight'] ?? 0);
  }

}
