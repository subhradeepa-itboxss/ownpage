<?php

namespace Drupal\ownpage\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class TemplateService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager
  ) {}

  public function getAvailableTemplates(): array {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $ids = $storage->getQuery()
      ->condition('vid', 'template')
      ->accessCheck(FALSE)
      ->execute();
    return $storage->loadMultiple($ids);
  }

  public function isAvailable(int $termId): bool {
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($termId);
    return $term !== NULL;
  }
}