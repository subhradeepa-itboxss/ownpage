<?php

namespace Drupal\ownpage\Service;

use Drupal\node\NodeInterface;

/**
 * Service for managing the website builder wizard.
 */
class BuilderService {

  /**
   * The ordered list of wizard steps.
   */
  const STEPS = [
    'basic_info',
    'sections',
    'theme',
    'seo',
    'preview',
    'publish',
  ];

  /**
   * Gets the current step for a website node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Website node.
   *
   * @return string
   *   The machine name of the current step.
   */
  public function getCurrentStep(NodeInterface $node): string {
    // TODO: read from a field on the node, e.g. field_op_builder_step.
    return self::STEPS[0];
  }

  /**
   * Saves progress for a given step.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The Website node.
   * @param string $step
   *   The step machine name.
   * @param array $data
   *   The submitted data for this step.
   */
  public function saveStepData(NodeInterface $node, string $step, array $data): void {
    // TODO: map $data to the correct fields on $node, then $node->save().
  }

  /**
   * Gets the next step after the given one.
   *
   * @param string $step
   *   The current step machine name.
   *
   * @return string|null
   *   The next step, or NULL if this is the last step.
   */
  public function getNextStep(string $step): ?string {
    $index = array_search($step, self::STEPS);
    return self::STEPS[$index + 1] ?? NULL;
  }

}