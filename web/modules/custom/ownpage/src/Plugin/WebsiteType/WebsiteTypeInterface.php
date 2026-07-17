<?php

namespace Drupal\ownpage\Plugin\WebsiteType;

/**
 * Defines the contract every Website Type plugin must implement.
 *
 * Per docs/BUILDER_IMPLEMENTATION_GUIDE.md §39-40: new website types are
 * added only by registering a plugin here — BuilderService must never
 * hardcode business logic for an individual website type.
 */
interface WebsiteTypeInterface {

  /**
   * The plugin ID. Must match the lowercased Website Type taxonomy label.
   */
  public function id(): string;

  /**
   * The human-readable label.
   */
  public function label(): string;

  /**
   * A short description shown during Website Type selection.
   */
  public function description(): string;

  /**
   * Ordered list of sections available to this website type.
   *
   * @return array<int, array{id: string, label: string, required: bool}>
   */
  public function sections(): array;

  /**
   * Sort weight relative to other website types.
   */
  public function weight(): int;

}
