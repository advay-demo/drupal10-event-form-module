<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class EventRegistrationForm extends FormBase {

  public function getFormId() {
    return 'event_registration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $connection = Database::getConnection();

    // ---- Load categories ----
    $categories = $connection->select('event_registration_event', 'e')
      ->fields('e', ['category'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $category_options = $categories ? array_combine($categories, $categories) : [];

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => 'Full Name',
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Email Address',
      '#required' => TRUE,
    ];

    $form['college'] = [
      '#type' => 'textfield',
      '#title' => 'College Name',
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => 'Department',
      '#required' => TRUE,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => 'Category',
      '#options' => $category_options,
      '#empty_option' => '- Select Category -',
      '#ajax' => [
        'callback' => '::updateDates',
        'wrapper' => 'date-wrapper',
      ],
      '#required' => TRUE,
    ];

    // ---- Date wrapper ----
    $form['date_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'date-wrapper'],
    ];

    $form['date_wrapper']['event_date'] = [
      '#type' => 'select',
      '#title' => 'Event Date',
      '#options' => $this->getDates($form_state->getValue('category')),
      '#empty_option' => '- Select Date -',
      '#ajax' => [
        'callback' => '::updateEvents',
        'wrapper' => 'event-wrapper',
      ],
    ];

    // ---- Event wrapper ----
    $form['event_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-wrapper'],
    ];

    $form['event_wrapper']['event_id'] = [
      '#type' => 'select',
      '#title' => 'Event Name',
      '#options' => $this->getEvents(
        $form_state->getValue('category'),
        $form_state->getValue('event_date')
      ),
      '#empty_option' => '- Select Event -',
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Register',
    ];

    return $form;
  }

  // ---------- AJAX ----------

  public function updateDates(array &$form, FormStateInterface $form_state) {
    return $form['date_wrapper'];
  }

  public function updateEvents(array &$form, FormStateInterface $form_state) {
    return $form['event_wrapper'];
  }

  // ---------- DATA HELPERS ----------

  private function getDates($category) {
    if (!$category) return [];

    $results = Database::getConnection()
      ->select('event_registration_event', 'e')
      ->fields('e', ['event_date'])
      ->condition('category', $category)
      ->execute()
      ->fetchCol();

    $options = [];
    foreach ($results as $ts) {
      $options[$ts] = date('Y-m-d', $ts);
    }

    return $options;
  }

  private function getEvents($category, $date) {
    if (!$category || !$date) return [];

    $results = Database::getConnection()
      ->select('event_registration_event', 'e')
      ->fields('e', ['id','event_name'])
      ->condition('category', $category)
      ->condition('event_date', $date)
      ->execute();

    $options = [];
    foreach ($results as $row) {
      $options[$row->id] = $row->event_name;
    }

    return $options;
  }

  private function getEventName($id) {
    return Database::getConnection()
      ->select('event_registration_event','e')
      ->fields('e',['event_name'])
      ->condition('id',$id)
      ->execute()
      ->fetchField();
  }

  // ---------- VALIDATION ----------

  public function validateForm(array &$form, FormStateInterface $form_state) {

    foreach (['full_name','college','department'] as $field) {
      if (!preg_match('/^[a-zA-Z0-9 ]+$/', $form_state->getValue($field))) {
        $form_state->setErrorByName($field, 'Special characters not allowed.');
      }
    }

    // duplicate check
    $exists = Database::getConnection()
      ->select('event_registration_entry','r')
      ->condition('email',$form_state->getValue('email'))
      ->condition('event_id',$form_state->getValue('event_id'))
      ->countQuery()
      ->execute()
      ->fetchField();

    if ($exists) {
      $form_state->setErrorByName('email','You already registered for this event.');
    }

    // registration window check
    $event = Database::getConnection()
      ->select('event_registration_event','e')
      ->fields('e',['reg_start_date','reg_end_date'])
      ->condition('id',$form_state->getValue('event_id'))
      ->execute()
      ->fetchAssoc();

    if ($event) {
      $now = time();
      if ($now < $event['reg_start_date'] || $now > $event['reg_end_date']) {
        $form_state->setErrorByName('event_id','Registration is closed for this event.');
      }
    }
  }

  // ---------- SUBMIT ----------

  public function submitForm(array &$form, FormStateInterface $form_state) {

    Database::getConnection()->insert('event_registration_entry')
      ->fields([
        'event_id' => $form_state->getValue('event_id'),
        'full_name' => $form_state->getValue('full_name'),
        'email' => $form_state->getValue('email'),
        'college' => $form_state->getValue('college'),
        'department' => $form_state->getValue('department'),
        'created' => time(),
      ])
      ->execute();

    // ---- Mail sending ----
    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::currentUser()->getPreferredLangcode();

    $params = [
      'name' => $form_state->getValue('full_name'),
      'email' => $form_state->getValue('email'),
      'event_name' => $this->getEventName($form_state->getValue('event_id')),
      'event_date' => date('Y-m-d'),
      'category' => $form_state->getValue('category'),
    ];

    // user mail
    $mailManager->mail(
      'event_registration',
      'registration_user',
      $form_state->getValue('email'),
      $langcode,
      $params
    );

    // admin mail (config driven)
    $config = \Drupal::config('event_registration.settings');

    if ($config->get('admin_notify') && $config->get('admin_email')) {
      $mailManager->mail(
        'event_registration',
        'registration_admin',
        $config->get('admin_email'),
        $langcode,
        $params
      );
    }

    $this->messenger()->addStatus('Registration successful.');
  }

}
