<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;

class AdminRegistrationController extends ControllerBase {

  /**
   * Admin listing page (Phase 9 — AJAX filter form).
   */
  public function listing() {
    return \Drupal::formBuilder()->getForm(
      'Drupal\event_registration\Form\AdminFilterForm'
    );
  }

  /**
   * CSV export of all registrations.
   */
  public function exportCsv() {

    $connection = Database::getConnection();

    $query = $connection->select('event_registration_entry', 'r')
      ->fields('r');

    $query->leftJoin('event_registration_event', 'e', 'r.event_id = e.id');
    $query->addField('e', 'event_name');

    $results = $query->execute();

    $output = fopen('php://temp', 'r+');

    // CSV header row
    fputcsv($output, [
      'Name',
      'Email',
      'Event',
      'College',
      'Department',
      'Submitted'
    ]);

    foreach ($results as $row) {
      fputcsv($output, [
        $row->full_name,
        $row->email,
        $row->event_name,
        $row->college,
        $row->department,
        date('Y-m-d H:i', $row->created),
      ]);
    }

    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);

    return new Response(
      $csv,
      200,
      [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="registrations.csv"',
      ]
    );
  }

}
