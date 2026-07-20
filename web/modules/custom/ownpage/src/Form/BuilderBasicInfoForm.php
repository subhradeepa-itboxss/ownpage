<?php

namespace Drupal\ownpage\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\ownpage\Service\BuilderService;
use Drupal\ownpage\Service\TemplateService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Website Type + Template selection step of the Builder wizard.
 *
 * Per docs/BUILDER_IMPLEMENTATION_GUIDE.md §21, this form only renders
 * fields and delegates validation/persistence to BuilderService. The
 * Template list is dynamic: it depends on the Website Type chosen above it.
 */
class BuilderBasicInfoForm extends FormBase {

  public function __construct(
    protected TemplateService $templateService,
    protected BuilderService $builderService,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ownpage.template_service'),
      $container->get('ownpage.builder'),
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'ownpage_builder_basic_info';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL) {
    $form['#tree'] = FALSE;
    $form['node_id'] = [
      '#type' => 'value',
      '#value' => $node->id(),
    ];

    $websiteTypeStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $websiteTypeIds = $websiteTypeStorage->getQuery()
      ->condition('vid', 'website_type')
      ->accessCheck(FALSE)
      ->execute();
    $websiteTypeTerms = $websiteTypeStorage->loadMultiple($websiteTypeIds);

    $websiteTypeOptions = [];
    foreach ($websiteTypeTerms as $term) {
      $websiteTypeOptions[$term->id()] = $term->label();
    }

    $selectedWebsiteTypeId = $form_state->getValue('website_type', $node->get('field_op_website_type')->target_id);
    $selectedWebsiteTypeId = $selectedWebsiteTypeId !== NULL ? (int) $selectedWebsiteTypeId : NULL;

    $form['website_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Website Type'),
      '#options' => $websiteTypeOptions,
      '#default_value' => $node->get('field_op_website_type')->target_id,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateTemplateOptions',
        'wrapper' => 'ownpage-template-wrapper',
      ],
    ];

    $form['template_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'ownpage-template-wrapper'],
    ];
    $form['template_wrapper']['template'] = [
      '#type' => 'select',
      '#title' => $this->t('Template'),
      '#options' => $this->templateOptions($selectedWebsiteTypeId),
      '#default_value' => $node->get('field_op_template')->target_id,
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  /**
   * AJAX callback: refreshes the Template options for the chosen Website Type.
   */
  public function updateTemplateOptions(array &$form, FormStateInterface $form_state): array {
    return $form['template_wrapper'];
  }

  /**
   * Builds the Template options list, restricted to the selected Website Type.
   */
  private function templateOptions(?int $websiteTypeTermId): array {
    $options = [];
    foreach ($this->templateService->getAvailableTemplates($websiteTypeTermId) as $term) {
      $options[$term->id()] = $term->label();
    }
    return $options;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->entityTypeManager->getStorage('node')->load($form_state->getValue('node_id'));

    $this->builderService->saveStepData($node, 'type', [
      'field_op_website_type' => $form_state->getValue('website_type'),
      'field_op_template' => $form_state->getValue('template'),
    ]);

    $route = $this->builderService->getNextStepRoute($node, 'template');
    $form_state->setRedirect($route ?? 'ownpage.dashboard_websites', $route ? ['node' => $node->id()] : []);
  }

}
