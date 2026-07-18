<?php

namespace Drupal\ownpage\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\ownpage\BuilderStepPluginManager;
use Drupal\ownpage\Plugin\WebsiteType\WebsiteTypeInterface;
use Drupal\ownpage\WebsiteTypePluginManager;

/**
 * Orchestrates the Website Builder wizard.
 *
 * The step order is never hardcoded here — it comes from
 * BuilderStepPluginManager, which discovers plugins under
 * src/Plugin/BuilderStep (docs/BUILDER_IMPLEMENTATION_GUIDE.md §41-43).
 * Field machine names follow the canonical table in CLAUDE.md —
 * field_op_* per Doc 06, the authoritative source. There is one lifecycle
 * field (field_op_status); only WebsiteService may set it to
 * published/archived/deleted, this service only sets draft/in_progress/
 * ready as the wizard progresses.
 */
class BuilderService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected WebsiteTypePluginManager $websiteTypeManager,
    protected BuilderStepPluginManager $stepManager,
  ) {}

  /**
   * Ordered step IDs, e.g. ['type', 'template', 'theme', 'sections', ...].
   *
   * @return string[]
   */
  private function orderedStepIds(NodeInterface $node): array {
    $websiteTypePlugin = $this->getWebsiteTypePlugin($node);
    return array_map(
      static fn ($step) => $step->id(),
      $this->stepManager->getOrderedSteps($websiteTypePlugin?->id())
    );
  }

  /**
   * Determines the wizard step the user should be shown next.
   */
  public function getCurrentStep(NodeInterface $node): string {
    if (!$this->hasValue($node, 'field_op_website_type')) {
      return 'type';
    }
    if (!$this->hasValue($node, 'field_op_template')) {
      return 'template';
    }
    if (!$this->hasValue($node, 'field_op_theme')) {
      return 'theme';
    }
    if (!$this->requiredSectionsComplete($node)) {
      return 'sections';
    }
    if ($this->publishStatus($node) !== 'published') {
      return 'preview';
    }
    return 'publish';
  }

  public function getNextStep(NodeInterface $node, string $step): ?string {
    $steps = $this->orderedStepIds($node);
    $index = array_search($step, $steps, TRUE);
    return $index === FALSE ? NULL : ($steps[$index + 1] ?? NULL);
  }

  public function getPreviousStep(NodeInterface $node, string $step): ?string {
    $steps = $this->orderedStepIds($node);
    $index = array_search($step, $steps, TRUE);
    return $index > 0 ? $steps[$index - 1] : NULL;
  }

  /**
   * Persists step data, then advances field_op_status if not already
   * published/archived/deleted.
   *
   * This service only ever sets field_op_status to draft/in_progress/ready
   * — moving it to published/archived/deleted is exclusively
   * WebsiteService's job (CLAUDE.md canonical field table).
   */
  public function saveStepData(NodeInterface $node, string $step, array $data): void {
    foreach ($data as $field => $value) {
      if ($node->hasField($field)) {
        $node->set($field, $value);
      }
    }
    if (!in_array($this->publishStatus($node), ['published', 'archived', 'deleted'], TRUE)) {
      $node->set('field_op_status', $this->deriveLifecycleStatus($node));
    }
    $node->save();
  }

  /**
   * Coarse lifecycle status derived from wizard progress: draft (nothing
   * entered yet), in_progress (mid-wizard) or ready (required sections
   * complete, awaiting Publish).
   */
  private function deriveLifecycleStatus(NodeInterface $node): string {
    if (!$this->hasValue($node, 'field_op_website_type')) {
      return 'draft';
    }
    return $this->requiredSectionsComplete($node) ? 'ready' : 'in_progress';
  }

  /**
   * Resolves the Website Type plugin selected on the node, if any.
   *
   * The taxonomy term's label (lowercased) is matched against the plugin ID
   * — e.g. the "Resume" term resolves to the `resume` plugin.
   */
  public function getWebsiteTypePlugin(NodeInterface $node): ?WebsiteTypeInterface {
    if (!$this->hasValue($node, 'field_op_website_type')) {
      return NULL;
    }
    $term = $node->get('field_op_website_type')->entity;
    if (!$term) {
      return NULL;
    }
    $pluginId = strtolower($term->label());
    if (!$this->websiteTypeManager->hasDefinition($pluginId)) {
      return NULL;
    }
    return $this->websiteTypeManager->createInstance($pluginId);
  }

  /**
   * Sections available for the node's selected website type.
   *
   * Per docs/BUILDER_IMPLEMENTATION_GUIDE.md §30, this is fully dynamic:
   * BuilderService never hardcodes a section list per website type.
   *
   * @return array<int, array{id: string, label: string, required: bool}>
   */
  public function getAvailableSections(NodeInterface $node): array {
    return $this->getWebsiteTypePlugin($node)?->sections() ?? [];
  }

  /**
   * Section IDs (from getAvailableSections) that already have paragraph data.
   *
   * @return string[]
   */
  public function getCompletedSectionIds(NodeInterface $node): array {
    $ids = array_column($this->getAvailableSections($node), 'id');
    return array_values(array_intersect($ids, $this->sectionParagraphBundles($node)));
  }

  /**
   * Progress engine per docs/BUILDER_IMPLEMENTATION_GUIDE.md §44: each
   * discovered wizard step contributes an equal share of the total.
   */
  public function calculateProgress(NodeInterface $node): int {
    $stepIds = $this->orderedStepIds($node);
    $currentIndex = array_search($this->getCurrentStep($node), $stepIds, TRUE);
    $sectionsIndex = array_search('sections', $stepIds, TRUE);

    $ratios = [];
    foreach ($stepIds as $index => $stepId) {
      $ratios[] = match ($stepId) {
        'type' => $this->hasValue($node, 'field_op_website_type') ? 1.0 : 0.0,
        'template' => $this->hasValue($node, 'field_op_template') ? 1.0 : 0.0,
        'theme' => $this->hasValue($node, 'field_op_theme') ? 1.0 : 0.0,
        'sections' => $this->sectionsCompletionRatio($node),
        'preview' => $sectionsIndex !== FALSE && $currentIndex > $sectionsIndex ? 1.0 : 0.0,
        'publish' => $this->publishStatus($node) === 'published' ? 1.0 : 0.0,
        default => 0.0,
      };
    }

    return $ratios ? (int) round((array_sum($ratios) / count($ratios)) * 100) : 0;
  }

  public function isComplete(NodeInterface $node): bool {
    return $this->calculateProgress($node) === 100;
  }

  private function requiredSectionsComplete(NodeInterface $node): bool {
    return $this->sectionsCompletionRatio($node) >= 1.0;
  }

  private function sectionsCompletionRatio(NodeInterface $node): float {
    $required = array_column(array_filter(
      $this->getAvailableSections($node),
      static fn (array $section) => $section['required']
    ), 'id');

    if (!$required) {
      return 0.0;
    }
    return count(array_intersect($required, $this->getCompletedSectionIds($node))) / count($required);
  }

  /**
   * Bundles present across both section fields — Hero (field_op_hero,
   * single/required) is deliberately separate from the other section types
   * (field_op_sections, unlimited), per Doc 03.
   */
  private function sectionParagraphBundles(NodeInterface $node): array {
    $bundles = [];
    if ($node->hasField('field_op_hero') && !$node->get('field_op_hero')->isEmpty()) {
      $bundles[] = 'hero';
    }
    if ($node->hasField('field_op_sections') && !$node->get('field_op_sections')->isEmpty()) {
      foreach ($node->get('field_op_sections')->referencedEntities() as $paragraph) {
        $bundles[] = $paragraph->bundle();
      }
    }
    return $bundles;
  }

  private function publishStatus(NodeInterface $node): ?string {
    return $node->hasField('field_op_status') ? $node->get('field_op_status')->value : NULL;
  }

  private function hasValue(NodeInterface $node, string $field): bool {
    return $node->hasField($field) && !$node->get($field)->isEmpty();
  }

}
