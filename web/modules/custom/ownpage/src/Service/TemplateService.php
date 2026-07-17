<?php

namespace Drupal\ownpage\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides Template taxonomy terms, optionally filtered by Website Type.
 *
 * Filtering reads the real `field_website_type` entity reference field on
 * the Template term (Structure > Taxonomy > Template > Manage fields) — not
 * a guessed match against template labels.
 */
class TemplateService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Templates available, optionally restricted to a Website Type term.
   *
   * @param int|null $websiteTypeTermId
   *   Website Type taxonomy term ID, or NULL for all templates.
   */
  public function getAvailableTemplates(?int $websiteTypeTermId = NULL): array {
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $query = $storage->getQuery()
      ->condition('vid', 'template')
      ->accessCheck(FALSE);

    if ($websiteTypeTermId !== NULL) {
      $query->condition('field_website_type', $websiteTypeTermId);
    }

    return $storage->loadMultiple($query->execute());
  }

  public function isAvailable(int $termId): bool {
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($termId);
    return $term !== NULL;
  }

}
