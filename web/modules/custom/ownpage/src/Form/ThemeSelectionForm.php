<?php

namespace Drupal\ownpage\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\ownpage\Service\BuilderService;
use Drupal\ownpage\Service\ThemeService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Theme selection step of the Builder wizard.
 *
 * Only themes compatible with the Website's already-selected Template are
 * offered (Doc 04 "Theme Selection" validation rule) — this filtering is
 * real business logic (ThemeService::getCompatibleThemes()), so it can't
 * be expressed by a stock entity reference field widget.
 */
class ThemeSelectionForm extends FormBase {

  public function __construct(
    protected ThemeService $themeService,
    protected BuilderService $builderService,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ownpage.theme_service'),
      $container->get('ownpage.builder'),
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'ownpage_builder_theme';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL) {
    $form['node_id'] = [
      '#type' => 'value',
      '#value' => $node->id(),
    ];

    $templateId = $node->get('field_op_template')->target_id;
    $themes = $templateId ? $this->themeService->getCompatibleThemes((int) $templateId) : [];

    if (!$themes) {
      $form['empty'] = [
        '#markup' => $this->t('No themes are configured for the selected template yet.'),
      ];
      return $form;
    }

    $options = [];
    foreach ($themes as $term) {
      $options[$term->id()] = $term->label();
    }

    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => $options,
      '#default_value' => $node->get('field_op_theme')->target_id,
      '#required' => TRUE,
    ];

    $previousRoute = $this->builderService->getPreviousStepRoute($node, 'theme');
    if ($previousRoute) {
      $form['back'] = Link::fromTextAndUrl(
        $this->t('Back'),
        Url::fromRoute($previousRoute, ['node' => $node->id()])
      )->toRenderable();
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->entityTypeManager->getStorage('node')->load($form_state->getValue('node_id'));

    $this->builderService->saveStepData($node, 'theme', [
      'field_op_theme' => $form_state->getValue('theme'),
    ]);

    $route = $this->builderService->getNextStepRoute($node, 'theme');
    $form_state->setRedirect($route ?? 'ownpage.dashboard_websites', $route ? ['node' => $node->id()] : []);
  }

}
