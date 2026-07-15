<?php

namespace Drupal\ownpage\Service;

use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
      'field_op_slug' => $this->slugService->generate($title),
      'field_op_status' => 'draft',
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
    $clone->set('field_op_slug', $this->slugService->generate($node->label() . ' copy'));
    $clone->set('field_op_status', 'draft');
    $clone->set('status', 0);
    $clone->save();
    return $clone;
  }

  public function archive(Node $node): void {
    $node->set('field_op_status', 'archived');
    $node->set('status', 0);
    $node->save();
  }

  public function publish(Node $node): void {
    if (!$this->slugService->isAvailable($node->get('field_op_slug')->value, (int) $node->id())) {
      throw new \RuntimeException('Slug already exists.');
    }
    $node->set('field_op_status', 'published');
    $node->set('status', 1);
    $node->save();
  }
}