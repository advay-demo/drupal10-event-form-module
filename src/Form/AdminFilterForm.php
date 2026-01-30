<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

class AdminFilterForm extends FormBase {

  public function getFormId() {
    return 'event_registration_admin_filter_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $connection = Database::getConnection();

    // ---- Event Dates ----
    $dates = $connection->select('event_registration_event','e')
      ->fields('e',['event_date'])
      ->distinct()
      ->execute()
      ->fetchCol();

    $date_options = [];
    foreach ($dates as $d) {
      $date_options[$d] = date('Y-m-d', $d);
    }

    $form['event_date'] = [
      '#type' => 'select',
      '#title' => 'Event Date',
      '#options' => $date_options,
      '#empty_option' => '- Select Date -',
      '#ajax' => [
        'callback' => '::updateEvents',
        'wrapper' => 'event-wrapper',
      ],
    ];

    // ---- Event dropdown (AJAX) ----
    $form['event_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'event-wrapper'],
    ];

    $form['event_wrapper']['event_id'] = [
      '#type' => 'select',
      '#title' => 'Event Name',
      '#options' => $this->getEvents($form_state->getValue('event_date')),
      '#empty_option' => '- Select Event -',
      '#ajax' => [
        'callback' => '::updateTable',
        'wrapper' => 'table-wrapper',
      ],
    ];

    // ---- Table wrapper ----
    $form['table_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'table-wrapper'],
    ];

    $event_id = $form_state->getValue('event_id');
    $form['table_wrapper']['table'] = $this->buildTable($event_id);

    return $form;
  }

  public function updateEvents(array &$form, FormStateInterface $form_state) {
    return $form['event_wrapper'];
  }

  public function updateTable(array &$form, FormStateInterface $form_state) {
    return $form['table_wrapper'];
  }

  private function getEvents($date) {
    if (!$date) return [];

    $results = Database::getConnection()
      ->select('event_registration_event','e')
      ->fields('e',['id','event_name'])
      ->condition('event_date',$date)
      ->execute();

    $options = [];
    foreach ($results as $row) {
      $options[$row->id] = $row->event_name;
    }
    return $options;
  }

  private function buildTable($event_id) {

    if (!$event_id) {
      return ['#markup' => '<em>Select event to view registrations</em>'];
    }

    $connection = Database::getConnection();

    $results = $connection->select('event_registration_entry','r')
      ->fields('r')
      ->condition('event_id',$event_id)
      ->execute();

    $header = [
      'Name','Email','College','Department','Submitted'
    ];

    $rows = [];
    foreach ($results as $r) {
      $rows[] = [
        $r->full_name,
        $r->email,
        $r->college,
        $r->department,
        date('Y-m-d H:i',$r->created),
      ];
    }

    $build['count'] = [
      '#markup' => '<h3>Total Participants: '.count($rows).'</h3>',
    ];

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => 'No registrations.',
    ];

    return $build;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {}
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
