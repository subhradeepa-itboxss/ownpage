<?php

namespace Drupal\ownpage\Plugin\BuilderStep;

use Drupal\Core\Plugin\PluginBase;

/**
 * Base class providing shared plumbing for Builder step plugins.
 */
abstract class BuilderStepBase extends PluginBase implements BuilderStepInterface {

  public function id(): string {
    return $this->pluginDefinition['id'];
  }

  public function label(): string {
    return (string) $this->pluginDefinition['label'];
  }

  public function weight(): int {
    return (int) ($this->pluginDefinition['weight'] ?? 0);
  }

  public function route(): string {
    return (string) ($this->pluginDefinition['route'] ?? '');
  }

  /**
   * All core wizard steps apply to every website type by default.
   */
  public function supportsWebsiteType(?string $websiteTypeId): bool {
    return TRUE;
  }

}
