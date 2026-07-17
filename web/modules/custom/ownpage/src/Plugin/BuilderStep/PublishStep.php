<?php

namespace Drupal\ownpage\Plugin\BuilderStep;

/**
 * No form/route exists yet for Publish (known gap — see CLAUDE.md Current
 * status). `route()` returns the doc-canonical name from §14 even though
 * the route isn't registered.
 *
 * @BuilderStep(
 *   id = "publish",
 *   label = @Translation("Publish"),
 *   weight = 60,
 *   route = "ownpage.builder.publish"
 * )
 */
class PublishStep extends BuilderStepBase {

}
