<?php

namespace Drupal\ownpage\Plugin\BuilderStep;

/**
 * Defines the contract every Builder wizard step plugin must implement.
 *
 * Per docs/BUILDER_IMPLEMENTATION_GUIDE.md §41-43: wizard steps are
 * discovered dynamically and sorted by weight — BuilderService must never
 * hold the step order as a hardcoded array. Adding a step means adding one
 * new plugin file here, not editing BuilderService.
 */
interface BuilderStepInterface {

  /**
   * The step ID (e.g. 'type', 'template', 'sections') — not persisted to any
   * field; BuilderService derives the current step live from which fields
   * are already filled.
   */
  public function id(): string;

  /**
   * The human-readable label.
   */
  public function label(): string;

  /**
   * Sort weight relative to other steps.
   */
  public function weight(): int;

  /**
   * The route name this step is served from.
   */
  public function route(): string;

  /**
   * Whether this step applies to the given website type plugin ID.
   *
   * All core steps apply to every website type; this exists so a future
   * feature module can add a step limited to one website type without
   * modifying BuilderService.
   */
  public function supportsWebsiteType(?string $websiteTypeId): bool;

}
