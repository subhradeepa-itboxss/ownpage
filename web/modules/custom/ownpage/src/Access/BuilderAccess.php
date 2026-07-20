<?php

namespace Drupal\ownpage\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Ownership check for Builder wizard routes.
 *
 * Per docs/BUILDER_IMPLEMENTATION_GUIDE.md §16-17, builder routes must
 * verify permission AND ownership. The blanket 'manage own ownpage
 * website' permission alone would let any customer open another
 * customer's /builder/{node} by guessing the node ID — this class adds
 * the missing ownership layer. Administrators (with 'administer ownpage
 * websites') bypass the ownership check by design.
 */
class BuilderAccess implements ContainerInjectionInterface {

  public static function create(ContainerInterface $container) {
    return new static();
  }

  public function access(NodeInterface $node, AccountInterface $account): AccessResultInterface {
    if ($node->bundle() !== 'website') {
      return AccessResult::forbidden('Not a Website node.')->addCacheableDependency($node);
    }

    if ($account->hasPermission('administer ownpage websites')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    $ownsWebsite = $account->hasPermission('manage own ownpage website')
      && (int) $node->getOwnerId() === (int) $account->id();

    return AccessResult::allowedIf($ownsWebsite)
      ->addCacheableDependency($node)
      ->cachePerPermissions()
      ->cachePerUser();
  }

}
