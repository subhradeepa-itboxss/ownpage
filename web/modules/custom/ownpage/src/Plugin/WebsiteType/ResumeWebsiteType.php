<?php

namespace Drupal\ownpage\Plugin\WebsiteType;

/**
 * @WebsiteType(
 *   id = "resume",
 *   label = @Translation("Resume"),
 *   weight = 0
 * )
 */
class ResumeWebsiteType extends WebsiteTypeBase {

  public function description(): string {
    return 'A one-page resume for professionals, freelancers, consultants, students and job seekers.';
  }

  /**
   * Limited to Paragraph types that actually exist in this site
   * (Structure > Paragraph types: hero, education, experience, skill,
   * product). docs/TEMPLATE_THEME_ARCHITECTURE.md §3 additionally lists
   * About, Certifications, Projects and Contact for Resume — those are
   * left out until matching Paragraph types are created.
   */
  public function sections(): array {
    return [
      ['id' => 'hero', 'label' => 'Hero', 'required' => TRUE],
      ['id' => 'experience', 'label' => 'Experience', 'required' => TRUE],
      ['id' => 'education', 'label' => 'Education', 'required' => TRUE],
      ['id' => 'skill', 'label' => 'Skills', 'required' => FALSE],
    ];
  }

}
