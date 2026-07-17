<?php

namespace Drupal\ownpage\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a BuilderStep annotation object.
 *
 * @Annotation
 */
class BuilderStep extends Plugin {

  /**
   * The plugin ID (also the wizard step ID, e.g. 'sections').
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
   * Sort weight relative to other steps (docs/BUILDER_IMPLEMENTATION_GUIDE.md
   * §43: Website Type=10, Template=20, Theme=30, Sections=40, Preview=50,
   * Publish=60; custom modules should use weights greater than 100).
   *
   * @var int
   */
  public $weight = 0;

  /**
   * The route name this step's form/controller is served from.
   *
   * @var string
   */
  public $route = '';

}
