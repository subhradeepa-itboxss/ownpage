<?php

namespace Drupal\ownpage\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\ownpage\Service\BuilderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sections step of the Builder wizard.
 *
 * The section list is dynamic: it comes from the Website Type plugin
 * selected in the previous step (docs/BUILDER_IMPLEMENTATION_GUIDE.md §30),
 * never hardcoded here.
 */
class BuilderSectionsForm extends FormBase {

  public function __construct(
    protected BuilderService $builderService,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ownpage.builder'),
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'ownpage_builder_sections';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL) {
    $form['node_id'] = [
      '#type' => 'value',
      '#value' => $node->id(),
    ];

    $sections = $this->builderService->getAvailableSections($node);

    if (!$sections) {
      $form['empty'] = [
        '#markup' => $this->t('Select a Website Type first to see its sections.'),
      ];
      return $form;
    }

    $completedIds = $this->builderService->getCompletedSectionIds($node);

    $form['sections'] = [
      '#type' => 'table',
      '#header' => [$this->t('Section'), $this->t('Required'), $this->t('Status')],
    ];
    foreach ($sections as $section) {
      $form['sections'][$section['id']]['label'] = ['#markup' => $section['label']];
      $form['sections'][$section['id']]['required'] = [
        '#markup' => $section['required'] ? $this->t('Required') : $this->t('Optional'),
      ];
      $form['sections'][$section['id']]['status'] = [
        '#markup' => in_array($section['id'], $completedIds, TRUE) ? $this->t('Complete') : $this->t('Not started'),
      ];
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->entityTypeManager->getStorage('node')->load($form_state->getValue('node_id'));
    $this->builderService->saveStepData($node, 'sections', []);
    $form_state->setRedirect('ownpage.dashboard_websites');
  }

}
