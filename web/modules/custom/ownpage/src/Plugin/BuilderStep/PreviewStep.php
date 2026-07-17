<?php

namespace Drupal\ownpage\Plugin\BuilderStep;

/**
 * No form/route exists yet for Preview (known gap — see CLAUDE.md Current
 * status). `route()` returns the doc-canonical name from §14 even though
 * the route isn't registered.
 *
 * @BuilderStep(
 *   id = "preview",
 *   label = @Translation("Preview"),
 *   weight = 50,
 *   route = "ownpage.builder.preview"
 * )
 */
class PreviewStep extends BuilderStepBase {

}
