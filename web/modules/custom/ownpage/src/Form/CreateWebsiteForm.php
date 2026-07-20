<?php

namespace Drupal\ownpage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ownpage\Service\WebsiteService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entry point for "Create Website" (Doc 06 §9: /dashboard/websites/add).
 *
 * Creates a blank draft Website node via WebsiteService, then redirects
 * straight into the Builder wizard's first step — this is the missing
 * link between the dashboard and the Builder routes, which previously
 * only worked on an already-existing node.
 */
class CreateWebsiteForm extends FormBase {

  public function __construct(
    protected WebsiteService $websiteService,
  ) {}

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ownpage.website_service')
    );
  }

  public function getFormId() {
    return 'ownpage_create_website';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Website Name'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Website'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->websiteService->createWebsite(
      $form_state->getValue('title'),
      (int) $this->currentUser()->id()
    );

    $form_state->setRedirect('ownpage.builder', ['node' => $node->id()]);
  }

}
