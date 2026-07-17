<?php

namespace Drupal\ownpage\Plugin\BuilderStep;

/**
 * No form/route exists yet for Theme selection (known gap — see CLAUDE.md
 * Current status). `route()` returns the doc-canonical name from §14 even
 * though the route isn't registered; nothing currently resolves it to a URL.
 *
 * @BuilderStep(
 *   id = "theme",
 *   label = @Translation("Theme"),
 *   weight = 30,
 *   route = "ownpage.builder.theme"
 * )
 */
class ThemeStep extends BuilderStepBase {

}
