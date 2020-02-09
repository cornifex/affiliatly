<?php

namespace Drupal\affiliatly\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Configure.
 *
 * Configuration form for Affiliatly.
 *
 * @package Drupal\affiliatly\Form
 */
class Configure extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['affiliatly.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'affiliatly_configure';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $hash = $this->config('affiliatly.settings')->get('hash');
    $form['hash'] = [
      '#type' => 'textfield',
      '#title' => 'Affiliatly hash',
      '#default_value' => $hash,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('affiliatly.settings')
      ->set('hash', $form_state->getValue('hash'))
      ->save();
  }

}
