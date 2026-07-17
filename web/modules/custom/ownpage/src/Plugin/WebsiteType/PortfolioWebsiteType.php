<?php

namespace Drupal\ownpage\Plugin\WebsiteType;

/**
 * @WebsiteType(
 *   id = "portfolio",
 *   label = @Translation("Portfolio"),
 *   weight = 10
 * )
 */
class PortfolioWebsiteType extends WebsiteTypeBase {

  public function description(): string {
    return 'A one-page portfolio for showcasing projects and creative work.';
  }

  /**
   * Limited to Paragraph types that actually exist in this site
   * (Structure > Paragraph types: hero, education, experience, skill,
   * product). docs/TEMPLATE_THEME_ARCHITECTURE.md §3 also lists Projects,
   * Services, Testimonials and Contact for Portfolio — none of those
   * Paragraph types exist yet, so Portfolio has only Hero until they're
   * created. This website type is not really usable end-to-end yet.
   */
  public function sections(): array {
    return [
      ['id' => 'hero', 'label' => 'Hero', 'required' => TRUE],
    ];
  }

}
