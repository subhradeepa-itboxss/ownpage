<?php

namespace Drupal\ownpage\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class ThemeService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager
  ) {}

  public function getCompatibleThemes(int $templateId): array {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $ids = $storage->getQuery()
      ->condition('vid', 'theme')
      ->accessCheck(FALSE)
      ->execute();
    return $storage->loadMultiple($ids);
  }
}
