<?php

namespace Drupal\ownpage\Service;

use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Owns Website node lifecycle operations (create, list, duplicate, archive,
 * publish). Field machine names follow the canonical table in CLAUDE.md.
 */
class WebsiteService {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected SlugService $slugService,
  ) {}

  public function createWebsite(string $title, int $ownerId): Node {
    $node = Node::create([
      'type' => 'website',
      'title' => $title,
      'uid' => $ownerId,
      'field_slug' => $this->slugService->generate($title),
      'field_publish_status' => 'draft',
      'field_builder_status' => 'TYPE',
      'field_builder_progress' => 0,
      'status' => 0,
    ]);
    $node->save();
    return $node;
  }

  public function getOwnWebsites(int $uid): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->condition('type', 'website')
      ->condition('uid', $uid)
      ->accessCheck(TRUE)
      ->execute();
    return $storage->loadMultiple($ids);
  }

  public function duplicate(Node $node): Node {
    $clone = $node->createDuplicate();
    $clone->set('title', $node->label() . ' (Copy)');
    $clone->set('field_slug', $this->slugService->generate($node->label() . ' copy'));
    $clone->set('field_publish_status', 'draft');
    $clone->set('status', 0);
    $clone->save();
    return $clone;
  }

  public function archive(Node $node): void {
    $node->set('field_publish_status', 'archived');
    $node->set('status', 0);
    $node->save();
  }

  public function publish(Node $node): void {
    if (!$this->slugService->isAvailable($node->get('field_slug')->value, (int) $node->id())) {
      throw new \RuntimeException('Slug already exists.');
    }
    $node->set('field_publish_status', 'published');
    $node->set('status', 1);
    $node->save();
  }

}
