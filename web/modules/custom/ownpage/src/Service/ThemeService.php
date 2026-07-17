<?php

namespace Drupal\ownpage\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides Theme taxonomy terms, filtered by Template compatibility.
 *
 * Compatibility is genuinely dynamic: it reads the Template term's
 * `field_compatible_themes` entity reference field (Structure > Taxonomy >
 * Template > Manage fields), not a hardcoded list.
 */
class ThemeService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager
  ) {}

  /**
   * Themes compatible with the given Template term.
   *
   * If the template has no themes set in field_compatible_themes, this
   * returns an empty array — that's a real "not configured yet" state, not
   * a bug, and should surface as such in the UI rather than silently
   * falling back to showing every theme.
   */
  public function getCompatibleThemes(int $templateId): array {
    $template = $this->entityTypeManager->getStorage('taxonomy_term')->load($templateId);
    if (!$template || !$template->hasField('field_compatible_themes')) {
      return [];
    }
    return $template->get('field_compatible_themes')->referencedEntities();
  }

}
