<?php

namespace Drupal\ownpage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;

class BuilderSectionsForm extends FormBase {

  public function getFormId() {
    return 'ownpage_builder_sections';
  }

  public function buildForm(array $form, FormStateInterface $form_state, ?NodeInterface $node = NULL) {
    $form['node_id'] = [
      '#type' => 'value',
      '#value' => $node->id(),
    ];

    $form['placeholder'] = [
      '#markup' => $this->t('Sections step placeholder — form fields coming next.'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Placeholder - real logic comes later.
  }

}
