<?php

namespace Drupal\ownpage\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Generates and validates unique public-facing website slugs.
 */
class SlugService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager
  ) {}

  public function isAvailable(string $slug, ?int $excludeNid = NULL): bool {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'website')
      ->condition('field_slug', $slug)
      ->accessCheck(FALSE);
    if ($excludeNid) {
      $query->condition('nid', $excludeNid, '<>');
    }
    return empty($query->execute());
  }

  public function generate(string $title): string {
    $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
    $slug = $base;
    $i = 1;
    while (!$this->isAvailable($slug)) {
      $slug = $base . '-' . $i++;
    }
    return $slug;
  }
}