<?php

namespace Drupal\ownpage\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\ownpage\Service\WebsiteService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Publish step of the Builder wizard.
 *
 * Delegates entirely to WebsiteService::publish(), which already handles
 * slug-uniqueness validation (Doc04 "Slug already exists." error) and the
 * field_op_status/status transition — this form only confirms the action
 * and reports the result.
 */
class PublishForm extends ConfirmFormBase {

  protected ?NodeInterface $node = NULL;

  public function __construct(
    protected WebsiteService $websiteService,
  ) {}

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ownpage.website_service')
    );
  }

  public function getFormId() {
    return 'ownpage_builder_publish';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL) {
    $this->node = $node;
    return parent::buildForm($form, $form_state);
  }

  public function getQuestion() {
    return $this->t('Publish %title?', ['%title' => $this->node->label()]);
  }

  public function getDescription() {
    return $this->t('Your website will become publicly available at its URL.');
  }

  public function getConfirmText() {
    return $this->t('Publish');
  }

  public function getCancelUrl() {
    return Url::fromRoute('ownpage.builder.preview', ['node' => $this->node->id()]);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $this->websiteService->publish($this->node);
    }
    catch (\RuntimeException $e) {
      $this->messenger()->addError($e->getMessage());
      $form_state->setRedirectUrl($this->getCancelUrl());
      return;
    }

    $this->messenger()->addStatus($this->t('Website published. It is now live at <a href="@url">@url</a>.', [
      '@url' => $this->node->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ]));
    $form_state->setRedirect('ownpage.dashboard_websites');
  }

}
