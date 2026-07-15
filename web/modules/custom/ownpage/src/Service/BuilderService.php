<?php

namespace Drupal\ownpage\Service;

use Drupal\node\NodeInterface;

class BuilderService {

  const STEPS = [
    'basic_info',
    'sections',
    'theme',
    'seo',
    'preview',
    'publish',
  ];

  public function getCurrentStep(NodeInterface $node): string {
    if (!$this->hasValue($node, 'field_op_website_type') || !$this->hasValue($node, 'field_op_template')) {
      return 'basic_info';
    }
    if (!$this->hasValue($node, 'field_op_hero')) {
      return 'sections';
    }
    if (!$this->hasValue($node, 'field_op_theme')) {
      return 'theme';
    }
    if ($node->get('field_op_status')->value !== 'published') {
      return 'publish';
    }
    return 'publish';
  }

  public function saveStepData(NodeInterface $node, string $step, array $data): void {
    foreach ($data as $field => $value) {
      if ($node->hasField($field)) {
        $node->set($field, $value);
      }
    }
    $node->save();
  }

  public function getNextStep(string $step): ?string {
    $index = array_search($step, self::STEPS);
    return self::STEPS[$index + 1] ?? NULL;
  }

  public function getPreviousStep(string $step): ?string {
    $index = array_search($step, self::STEPS);
    return $index > 0 ? self::STEPS[$index - 1] : NULL;
  }

  private function hasValue(NodeInterface $node, string $field): bool {
    return $node->hasField($field) && !$node->get($field)->isEmpty();
  }
}