<?php

namespace Drupal\ownpage\Plugin\WebsiteType;

/**
 * @WebsiteType(
 *   id = "shop",
 *   label = @Translation("Shop"),
 *   weight = 20
 * )
 */
class ShopWebsiteType extends WebsiteTypeBase {

  public function description(): string {
    return 'A one-page storefront for selling products online.';
  }

  /**
   * Limited to Paragraph types that actually exist in this site
   * (Structure > Paragraph types: hero, education, experience, skill,
   * product). docs/TEMPLATE_THEME_ARCHITECTURE.md §3 also lists
   * Categories, Featured Collection, FAQ and Contact for Shop — none of
   * those Paragraph types exist yet.
   */
  public function sections(): array {
    return [
      ['id' => 'hero', 'label' => 'Hero', 'required' => TRUE],
      ['id' => 'product', 'label' => 'Products', 'required' => TRUE],
    ];
  }

}
