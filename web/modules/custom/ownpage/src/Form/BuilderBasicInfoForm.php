<?php

namespace Drupal\ownpage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\ownpage\Service\TemplateService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class BuilderBasicInfoForm extends FormBase {

  public function __construct(
  protected TemplateService $templateService,
  protected EntityTypeManagerInterface $entityTypeManager
) {}

public static function create(ContainerInterface $container) {
  return new static(
    $container->get('ownpage.template_service'),
    $container->get('entity_type.manager')
  );
}

  public function getFormId() {
    return 'ownpage_builder_basic_info';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL) {
    $form['node_id'] = [
      '#type' => 'value',
      '#value' => $node->id(),
    ];

    $website_type_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $website_type_ids = $website_type_storage->getQuery()
    ->condition('vid', 'website_type')
    ->accessCheck(FALSE)
    ->execute();
    $website_type_terms = $website_type_storage->loadMultiple($website_type_ids);

    $website_type_options = [];
    foreach ($website_type_terms as $term) {
    $website_type_options[$term->id()] = $term->label();
    }

    $form['website_type'] = [
    '#type' => 'select',
    '#title' => $this->t('Website Type'),
    '#options' => $website_type_options,
    '#default_value' => $node->get('field_op_website_type')->target_id,
    '#required' => TRUE,
    ];

    $templates = $this->templateService->getAvailableTemplates();
    $options = [];
    foreach ($templates as $term) {
      $options[$term->id()] = $term->label();
    }
    $form['template'] = [
      '#type' => 'select',
      '#title' => $this->t('Template'),
      '#options' => $options,
      '#default_value' => $node->get('field_op_template')->target_id,
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->entityTypeManager->getStorage('node')->load($form_state->getValue('node_id'));
    $node->set('field_op_website_type', $form_state->getValue('website_type'));
    $node->set('field_op_template', $form_state->getValue('template'));
    $node->save();
    $form_state->setRedirect('ownpage.builder.sections', ['node' => $node->id()]);
  }
}