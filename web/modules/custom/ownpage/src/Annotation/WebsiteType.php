<?php

namespace Drupal\ownpage\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a WebsiteType annotation object.
 *
 * @Annotation
 */
class WebsiteType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable label.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * Sort weight relative to other website types.
   *
   * @var int
   */
  public $weight = 0;

}
