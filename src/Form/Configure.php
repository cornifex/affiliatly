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
    $config = $this->config('affiliatly.settings');
    $form['code'] = [
      '#type' => 'textfield',
      '#title' => 'Affiliatly code',
      '#default_value' => $config->get('code'),
    ];
    $form['hash'] = [
      '#type' => 'textfield',
      '#title' => 'Affiliatly hash',
      '#default_value' => $config->get('hash'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('affiliatly.settings')
      ->set('code', $form_state->getValue('code'))
      ->set('hash', $form_state->getValue('hash'))
      ->save();
  }

}
