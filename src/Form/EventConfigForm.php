<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class EventConfigForm extends ConfigFormBase {

  public function getFormId() {
    return 'event_config_form';
  }

  protected function getEditableConfigNames() {
    return ['event_registration.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('event_registration.settings');

    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => 'Event Name',
      '#required' => TRUE,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => 'Category',
      '#options' => [
        'Online Workshop' => 'Online Workshop',
        'Hackathon' => 'Hackathon',
        'Conference' => 'Conference',
        'One-day Workshop' => 'One-day Workshop',
      ],
      '#required' => TRUE,
    ];

    $form['event_date'] = [
      '#type' => 'date',
      '#title' => 'Event Date',
      '#required' => TRUE,
    ];

    $form['reg_start_date'] = [
      '#type' => 'date',
      '#title' => 'Registration Start Date',
      '#required' => TRUE,
    ];

    $form['reg_end_date'] = [
      '#type' => 'date',
      '#title' => 'Registration End Date',
      '#required' => TRUE,
    ];

    $form['admin_email'] = [
      '#type' => 'email',
      '#title' => 'Admin notification email',
      '#default_value' => $config->get('admin_email'),
    ];

    $form['admin_notify'] = [
      '#type' => 'checkbox',
      '#title' => 'Enable admin email notifications',
      '#default_value' => $config->get('admin_notify'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (!preg_match('/^[a-zA-Z0-9 ]+$/', $form_state->getValue('event_name'))) {
      $form_state->setErrorByName('event_name', 'Special characters are not allowed.');
    }

    $start = strtotime($form_state->getValue('reg_start_date'));
    $end = strtotime($form_state->getValue('reg_end_date'));

    if ($start > $end) {
      $form_state->setErrorByName('reg_end_date', 'End date must be after start date.');
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    Database::getConnection()->insert('event_registration_event')
      ->fields([
        'event_name' => $form_state->getValue('event_name'),
        'category' => $form_state->getValue('category'),
        'event_date' => strtotime($form_state->getValue('event_date')),
        'reg_start_date' => strtotime($form_state->getValue('reg_start_date')),
        'reg_end_date' => strtotime($form_state->getValue('reg_end_date')),
        'created' => time(),
      ])
      ->execute();

    $this->config('event_registration.settings')
      ->set('admin_email', $form_state->getValue('admin_email'))
      ->set('admin_notify', $form_state->getValue('admin_notify'))
      ->save();     


    parent::submitForm($form, $form_state);

    $this->messenger()->addStatus('Event saved successfully.');
  }

}
