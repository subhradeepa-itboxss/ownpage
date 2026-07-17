<?php

namespace Drupal\ownpage\Plugin\BuilderStep;

/**
 * Route is the same as the Type step: BuilderBasicInfoForm currently
 * collects Website Type and Template together on one page. Doc 07 §14
 * specifies a separate `ownpage.builder.template` route; splitting the
 * form is a future increment, not done here.
 *
 * @BuilderStep(
 *   id = "template",
 *   label = @Translation("Template"),
 *   weight = 20,
 *   route = "ownpage.builder"
 * )
 */
class TemplateStep extends BuilderStepBase {

}
