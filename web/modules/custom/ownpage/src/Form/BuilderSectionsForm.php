<?php

namespace Drupal\ownpage\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\ownpage\Service\BuilderService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sections step of the Builder wizard.
 *
 * Renders the `node.website.builder_sections` form display mode, which is
 * configured entirely through the Drupal UI (Structure > Content types >
 * Website > Manage form display > Builder: Sections tab) to show only
 * field_op_hero and field_op_sections using the Paragraphs module's own
 * "paragraphs" widget. That gives the full native Add/Edit/Reorder/Remove
 * UI for Hero, Education, Experience, Skill and Product content — this
 * class only wires that existing display into the wizard's
 * validate/save/redirect flow, the same way
 * \Drupal\Core\Entity\ContentEntityForm does internally for the normal
 * node edit form.
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
    $form_state->set('ownpage_website', $node);

    $formDisplay = $this->entityTypeManager->getStorage('entity_form_display')
      ->load('node.website.builder_sections');

    if (!$formDisplay) {
      $form['missing'] = [
        '#markup' => $this->t('The "Builder: Sections" form display mode has not been set up yet in Structure > Content types > Website > Manage form display.'),
      ];
      return $form;
    }

    $form_state->set('ownpage_form_display', $formDisplay);
    $formDisplay->buildForm($node, $form, $form_state);

    // #weight forces these below the entity form display's fields, whose
    // own #weight comes from Manage form display's row order (small
    // integers there previously placed these buttons in between fields).
    $previousRoute = $this->builderService->getPreviousStepRoute($node, 'sections');
    if ($previousRoute) {
      $form['back'] = Link::fromTextAndUrl(
        $this->t('Back'),
        Url::fromRoute($previousRoute, ['node' => $node->id()])
      )->toRenderable();
      $form['back']['#weight'] = 100;
    }

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 101,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $node = $form_state->get('ownpage_website');
    $formDisplay = $form_state->get('ownpage_form_display');
    if (!$node || !$formDisplay) {
      return;
    }

    $formDisplay->extractFormValues($node, $form, $form_state);
    $formDisplay->validateFormValues($node, $form, $form_state);

    if (!$this->builderService->requiredSectionsComplete($node)) {
      $form_state->setErrorByName('', $this->t('Please complete all required sections.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form_state->get('ownpage_website');
    // Already extracted onto $node in validateForm() — Drupal always runs
    // validation before submit handlers for a plain submit button, so no
    // need to extract a second time here.
    $this->builderService->saveStepData($node, 'sections', []);

    $route = $this->builderService->getNextStepRoute($node, 'sections');
    $form_state->setRedirect($route ?? 'ownpage.dashboard_websites', $route ? ['node' => $node->id()] : []);
  }

}
