<?php
/**
 * @file
 * Contains services to communicate with the KOBA proxy.
 */

namespace Drupal\koba_booking\Service;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\koba_booking\BookingInterface;

class Mailer {

  protected $mailManager;

  /**
   * Default construct.
   *
   * @param $mailManager
   *   Mail manager service to send mail with.
   */
  public function __construct($mailManager) {
    $this->mailManager = $mailManager;
  }

  /**
   * Helper function to send mail based on a booking.
   *
   * @param string $type
   *   The type of the mail (request, accepted, etc.)
   * @param \Drupal\koba_booking\BookingInterface $booking
   *   Booking object that this mail is about.
   * @param bool $notifyAdmin
   *   Send notification mail to administrator. Defaults to TRUE.
   */
  public function send($type, BookingInterface $booking = NULL, $notifyAdmin = TRUE) {
    // Get to mail address.
    $to = $booking->booking_email->value;

    // Generate content.
    $content = (object) $this->generateUserMailContent($type, $booking);

    // Send the mail.
    $this->mailer($to, $content->subject, $content->body);

    // Send notification to administrator.
    if ($notifyAdmin) {
      // Generate content.
      $content = (object) $this->generateAdminNotificationMailContent($type, $booking);

      // Try to get to address from the site configuration.
      $site_config = \Drupal::config('system.site');
      $to = $site_config->get('mail');
      if (empty($to)) {
        $to = ini_get('sendmail_from');
      }

      // Send the mail.
      $this->mailer($to, $content->subject, $content->body);
    }
  }

  /**
   * Generate mail content for administrator notification mails.
   *
   * @param $type
   *   The type of mail message to build.
   * @param BookingInterface $booking
   *   The booking to use.
   * @return array
   *   Array indexed with "body" and "subject" as keys.
   */
  protected function generateAdminNotificationMailContent($type, BookingInterface $booking) {
    // Build render array for the mail body.
    $bookingContent = \Drupal::getContainer()->get('koba_booking.booking_content');
    switch ($type) {
      case 'request':
        $subject = $bookingContent->get('koba_email_admin.email_admin_pending_title');

        // Build render array.
        $content = array(
          '#theme' => 'booking_mail_request',
          '#message' => $bookingContent->get('koba_email_admin.email_admin_pending_body'),
        );
        break;

      case 'accepted':
        $subject = $bookingContent->get('koba_email_admin.email_admin_accepted_title');

        // Build render array.
        $content = array(
          '#theme' => 'booking_mail_accepted',
          '#message' => $bookingContent->get('koba_email_admin.email_admin_accepted_body'),
        );
        break;

      case 'rejected':
        $subject = $bookingContent->get('koba_email_admin.email_admin_rejected_title');

        // Build render array.
        $content = array(
          '#theme' => 'booking_mail_rejected',
          '#message' => $bookingContent->get('koba_email_admin.email_admin_rejected_body'),
        );
        break;

      case 'cancelled':
        $subject = $bookingContent->get('koba_email_admin.email_admin_cancelled_title');

        // Build render array.
        $content = array(
          '#theme' => 'booking_mail_cancelled',
          '#message' => $bookingContent->get('koba_email_admin.email_admin_cancelled_body'),
        );
        break;

      default:
        $subject = 'Unknown mail type';
        $content = array(
          '#type' => 'markup',
          '#message' => 'Error unknown mail type',
        );
        break;
    }

    // Add logo.
    $content += $this->generateLogo();

    // Extend content with booking information.
    if (!is_null($booking)) {
      $content += $this->generateBookingArray($booking);
    }

    $this->replaceTokens($content, $booking);
    $this->setTheme($bookingContent->get('koba_email_admin.admin_email_theme'));

    // Render the body content for the mail.
    return array(
      'subject' => $subject,
      'body' => render($content),
    );
  }

  /**
   * Generate mail content for user mails.
   *
   * @param $type
   *   The type of mail message to build.
   * @param BookingInterface $booking
   *   The booking to use.
   * @return array
   *   Array indexed with "body" and "subject" as keys.
   */
  protected function generateUserMailContent($type, BookingInterface $booking) {
    // Build render array for the mail body.
    $bookingContent = \Drupal::getContainer()->get('koba_booking.booking_content');

    switch ($type) {
      case 'request':
        $subject = $bookingContent->get('koba_email.email_pending_title');

        // Build render array.
        $content = array(
          '#theme' => 'booking_mail_request',
          '#message' => $bookingContent->get('koba_email.email_pending_body'),
        );

        // If this booking is in the next search phase, attach search phase message.
        $last_booking_date = $bookingContent->get('koba_booking.last_booking_date');
        $last_booking_date_minus_half_year = null;
        if (date('n', $last_booking_date) > 6) {
          $last_booking_date_minus_half_year = mktime(0, 0, 0, 7, 1, date('Y', $last_booking_date));
        }
        else {
          $last_booking_date_minus_half_year = mktime(0, 0, 0, 1, 1, date('Y', $last_booking_date));
        }
        $booking_from_date = $booking->booking_from_date->value;
        if ($booking_from_date >= $last_booking_date_minus_half_year &&
          $booking_from_date <= $last_booking_date
        ) {
          $content['#search_phase_message'] = $bookingContent->get('koba_email.email_pending_search_phase_body');
        }

        break;

      case 'accepted':
        $subject = $bookingContent->get('koba_email.email_accepted_title');

        // Build render array.
        $content = array(
          '#theme' => 'booking_mail_accepted',
          '#message' => $bookingContent->get('koba_email.email_accepted_body'),
        );
        break;

      case 'rejected':
        $subject = $bookingContent->get('koba_email.email_rejected_title');

        // Build render array.
        $content = array(
          '#theme' => 'booking_mail_rejected',
          '#message' => $bookingContent->get('koba_email.email_rejected_body'),
        );
        break;

      case 'cancelled':
        $subject = $bookingContent->get('koba_email.email_cancelled_title');

        // Build render array.
        $content = array(
          '#theme' => 'booking_mail_cancelled',
          '#message' => $bookingContent->get('koba_email.email_cancelled_body'),
        );
        break;

      default:
        $subject = 'Unknown mail type';
        $content = array(
          '#type' => 'markup',
          '#message' => 'Error unknown mail type',
        );
        break;
    }

    // Add logo.
    $content += $this->generateLogo();

    // Extend content with booking information.
    if (!is_null($booking)) {
      $content += $this->generateBookingArray($booking);
    }

    $this->replaceTokens($content, $booking);
    $this->setTheme($bookingContent->get('koba_email.email_theme'));

    // Render the body content for the mail.
    return array(
      'subject' => $subject,
      'body' => render($content),
    );
  }

  /**
   * Set theme for rendering email templates.
   *
   * @param string
   *   The theme name.
   */
  protected function setTheme($themeName) {
    if ($themeName) {
      $theme = \Drupal::service('theme.initialization')->getActiveThemeByName($themeName);
      if ($theme) {
        \Drupal::theme()->setActiveTheme($theme);
      }
    }
  }

  /**
   * Replace tokens in content.
   *
   * @param array $content
   *   The content.
   *
   * @return array
   *   The content with tokens replaced by actual content.
   */
  protected function replaceTokens(array &$content, BookingInterface $booking) {
    if (isset($content['#message'])) {
      $token_service = \Drupal::token();
      // Fetch current language for language options.
      $language_interface = \Drupal::languageManager()->getCurrentLanguage();
      // Output the content with tokens replaced.
      $content['#message'] = $token_service->replace($content['#message'], array('booking' => $booking), array('langcode' => $language_interface->getId()));
    }
  }

  /**
   * Generate logo as base64 encode content field.
   *
   * @return array
   *   Array with logo data.
   */
  protected function generateLogo() {
    $content['#logo_url'] = theme_get_setting('logo.url');

    return $content;
  }

  /**
   * Build render array with booking information.
   *
   * @param \Drupal\koba_booking\BookingInterface $booking
   *   Booking object to generate array for.
   *
   * @return array
   *   Array with booking information.
   */
  protected function generateBookingArray(BookingInterface $booking) {
    // Load room for the booking.
    $room = $booking->getRoomEntity();

    $generator = \Drupal::urlGenerator();
    $url = $generator->generateFromRoute('koba_booking.receipt', array(
      'hash' => $booking->booking_hash->value,
    ), array(
      'absolute' => TRUE,
    ));

    return array(
      '#booking' => array(
        'id' => $booking->id->value,
        'title' => Html::escape($booking->name->value),
        'date' => \Drupal::service('date.formatter')->format($booking->booking_from_date->value, 'dokk1_booking_dato'),
        'time' => \Drupal::service('date.formatter')->format($booking->booking_from_date->value, 'dokk1_booking_time') . ' - ' . \Drupal::service('date.formatter')->format($booking->booking_to_date->value, 'dokk1_booking_time'),
        'room' => array(
          'title' => $room->title->value,
          'price' => $room->field_price->value,
          'url' => Url::fromUri($room->url('canonical', array('absolute' => TRUE))),
        ),
        'name' => Html::escape($booking->booking_name->value),
        'mail' => Html::escape($booking->booking_email->value),
        'phone' => Html::escape($booking->booking_phone->value),
        'type' => koba_booking_room_usage()[Html::escape($booking->booking_usage->value)],
        'message' => Html::escape($booking->booking_message->value),
        'url' => $url,
      ),
    );
  }

  /**
   * Send HTML mails.
   *
   * @TODO: This is not the Drupal way to send mail, but rather a hack to send
   *        HTML mails. Drupal MailManger service hardcode plain/text as content
   *        type, so HTML is not supported.
   *
   *        When the SwiftMailer module have been ported to D8... USE IT.
   *
   * @param $to
   *   Mail address to send mail to.
   * @param $subject
   *   The mails subject.
   * @param $body
   *   The HTML body content to send.
   * @param string $name
   *   The name of the sender. Defaults to 'Dokk1'.
   */
  protected function mailer($to, $subject, $body, $name = 'Dokk1') {
    // Try to get from address from the site configuration.
    $site_config = \Drupal::config('system.site');
    $from = $site_config->get('mail');
    if (empty($from)) {
      $from = ini_get('sendmail_from');
    }

    // Get hold of the RAW mailer client.
    $key = Crypt::randomBytesBase64();
    $mailer = $this->mailManager->getInstance(array('module' => 'koba_booking', 'key' => $key));

    // Build mail configuration and set the type to HTML.
    $params = array(
      'headers' => array(
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Return-Path' => $from,
        'Reply-to' => $from,
        'Sender' => $from,
        'From' => $name . ' <' . $from . '>',
      ),
      'to' => $to,
      'body' => $body,
      'subject' => $subject,
    );

    // Send the mail.
    $mailer->mail($params);
  }
}
