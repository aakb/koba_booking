<?php
/**
 * @file
 * Contains Drupal\koba_booking\Form\BookingMessagesForm.
 */

namespace Drupal\koba_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class ContentEntityExampleSettingsForm.
 * @package Drupal\koba_booking\Form
 * @ingroup koba_booking
 */
class BookingMessagesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'koba_booking_messages';
  }

  /**
   * Get key/value storage for booking content.
   *
   * @return object
   */
  private function getBookingContent() {
    return \Drupal::getContainer()->get('koba_booking.booking_content');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('koba_booking.settings');
    $bookingContent = $this->getBookingContent();
    $tokens_description = t('Available tokens are: [booking:name], [booking:description], [booking:resource], [booking:status], [booking:date], [booking:from_time], [booking:to_time], [booking:email], [booking:id]');

    // If in search phase print message.
    if ($bookingContent->get('koba_booking.search_phase') > 0) {
      $search_period_message = t('Notice! Search period is active, remember to deactivate the setting when planning starts');
      drupal_set_message($search_period_message, $type = 'warning');
    }

    // Form wrappers.
    $form['message_settings_wrapper'] = array(
      '#title' => $this->t('Message settings'),
      '#type' => 'details',
      '#weight' => '0',
    );

    $form['user_email_settings_wrapper'] = array(
      '#title' => $this->t('User email settings'),
      '#type' => 'details',
      '#weight' => '1',
    );

    $form['admin_email_settings_wrapper'] = array(
      '#title' => $this->t('Admin email settings'),
      '#type' => 'details',
      '#weight' => '2',
    );

    // Message settings.
    $form['message_settings_wrapper']['message_settings'] = array(
      '#type' => 'vertical_tabs',
      '#description' => t('Messages to the user, displayed in the browser.'),
    );

    $form['message_settings']['messages'] = array(
      '#title' => $this->t('Booking request received message'),
      '#type' => 'details',
      '#weight' => '1',
      '#group' => 'message_settings',
    );

    $form['message_settings']['messages']['booking_created'] = array(
      '#type' => 'textarea',
      '#title' => t('Created booking'),
      '#description' => t('The message displayed to the user when the booking is created.') . '</br>' . $tokens_description,
      '#default_value' => $bookingContent->get('koba_booking.created_booking_message'),
      '#weight' => '0',
    );

    $form['message_settings']['messages']['booking_created_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save booking created message'),
      '#weight' => '1',
      '#submit' => array('::booking_created_submit'),
    );


    $form['message_settings']['help_texts'] = array(
      '#title' => $this->t('Help texts'),
      '#type' => 'details',
      '#weight' => '2',
      '#group' => 'message_settings',
    );

    $form['message_settings']['help_texts']['why_email_description'] = array(
      '#type' => 'text_format',
      '#title' => t('Why we need your email'),
      '#description' => t('The message displayed to the user when clicking "Why we need your email".') . '</br>' . $tokens_description,
      '#default_value' => $bookingContent->get('koba_booking.why_email'),
      '#weight' => '0',
    );

    $form['message_settings']['help_texts']['why_title_description'] = array(
      '#type' => 'text_format',
      '#title' => t('Why we need a title'),
      '#description' => t('The message displayed to the user when clicking "Why we need a title".') . '</br>' . $tokens_description,
      '#default_value' => $bookingContent->get('koba_booking.why_title'),
      '#weight' => '1',
    );

    $form['message_settings']['help_texts']['booking_help_text_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save help text messages'),
      '#weight' => '2',
      '#submit' => array('::booking_help_text_submit'),
    );




    // User email settings.
    $form['user_email_settings_wrapper']['user_email_settings'] = array(
      '#type' => 'vertical_tabs',
      '#description' => t('Messages sent to the users email address.'),
    );

    // Pending email settings.
    $form['user_email_settings']['pending_email'] = array(
      '#title' => $this->t('Request received (User)'),
      '#type' => 'details',
      '#weight' => '1',
      '#group' => 'user_email_settings',
    );

    $form['user_email_settings']['pending_email']['pending_email_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email title'),
      '#default_value' => $bookingContent->get('koba_email.email_pending_title'),
    );

    $form['user_email_settings']['pending_email']['pending_email_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Email body'),
      '#default_value' => $bookingContent->get('koba_email.email_pending_body'),
      '#description' => $tokens_description,
    );

    $form['user_email_settings']['pending_email']['pending_email_search_phase_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Search phase message'),
      '#default_value' => $bookingContent->get('koba_email.email_pending_search_phase_body'),
      '#description' => $tokens_description,
    );

    $form['user_email_settings']['pending_email']['pending_email_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save pending email settings'),
      '#weight' => 1,
      '#submit' => array('::pending_email_submit'),
    );

    // Accepted email settings.
    $form['user_email_settings']['accepted_email'] = array(
      '#title' => $this->t('Booking accepted (User)'),
      '#type' => 'details',
      '#weight' => '2',
      '#group' => 'user_email_settings',
    );

    $form['user_email_settings']['accepted_email']['accepted_email_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email title'),
      '#default_value' => $bookingContent->get('koba_email.email_accepted_title'),
    );

    $form['user_email_settings']['accepted_email']['accepted_email_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Email body'),
      '#default_value' => $bookingContent->get('koba_email.email_accepted_body'),
      '#description' => $tokens_description,
    );

    $form['user_email_settings']['accepted_email']['accepted_email_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save accepted email settings'),
      '#weight' => 1,
      '#submit' => array('::accepted_email_submit'),
    );

    // Rejected email settings.
    $form['user_email_settings']['rejected_email'] = array(
      '#title' => $this->t('Booking rejected (User)'),
      '#type' => 'details',
      '#weight' => '3',
      '#group' => 'user_email_settings',
    );

    $form['user_email_settings']['rejected_email']['rejected_email_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email title'),
      '#default_value' => $bookingContent->get('koba_email.email_rejected_title'),
    );

    $form['user_email_settings']['rejected_email']['rejected_email_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Email body'),
      '#default_value' => $bookingContent->get('koba_email.email_rejected_body'),
      '#description' => $tokens_description,
    );

    $form['user_email_settings']['rejected_email']['rejected_email_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save rejected email settings'),
      '#weight' => 1,
      '#submit' => array('::rejected_email_submit'),
    );

    // Cancelled email settings.
    $form['user_email_settings']['cancelled_email'] = array(
      '#title' => $this->t('Booking cancelled (User)'),
      '#type' => 'details',
      '#weight' => '4',
      '#group' => 'user_email_settings',
    );

    $form['user_email_settings']['cancelled_email']['cancelled_email_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email title'),
      '#default_value' => $bookingContent->get('koba_email.email_cancelled_title'),
    );

    $form['user_email_settings']['cancelled_email']['cancelled_email_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Email body'),
      '#default_value' => $bookingContent->get('koba_email.email_cancelled_body'),
      '#description' => $tokens_description,
    );

    $form['user_email_settings']['cancelled_email']['cancelled_email_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save cancelled email settings'),
      '#weight' => 1,
      '#submit' => array('::cancelled_email_submit'),
    );



    // Admin emails settings.
    $form['admin_email_settings_wrapper']['admin_email_settings'] = array(
      '#type' => 'vertical_tabs',
      '#description' => t('Messages sent to a shared administration email account.'),
    );

    // Pending email settings.
    $form['admin_email_settings']['pending_admin_email'] = array(
      '#title' => $this->t('Request received (Admin)'),
      '#type' => 'details',
      '#weight' => 1,
      '#group' => 'admin_email_settings',
    );

    $form['admin_email_settings']['pending_admin_email']['pending_admin_email_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email title'),
      '#default_value' => $bookingContent->get('koba_email_admin.email_admin_pending_title'),
    );

    $form['admin_email_settings']['pending_admin_email']['pending_admin_email_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Email body'),
      '#default_value' => $bookingContent->get('koba_email_admin.email_admin_pending_body'),
      '#description' => $tokens_description,
    );

    $form['admin_email_settings']['pending_admin_email']['pending_admin_email_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save admin pending email settings'),
      '#weight' => 1,
      '#submit' => array('::pending_admin_email_submit'),
    );

    // Accepted email settings.
    $form['admin_email_settings']['accepted_admin_email'] = array(
      '#title' => $this->t('Booking accepted (Admin)'),
      '#type' => 'details',
      '#weight' => '2',
      '#group' => 'admin_email_settings',
    );

    $form['admin_email_settings']['accepted_admin_email']['accepted_admin_email_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email title'),
      '#default_value' => $bookingContent->get('koba_email_admin.email_admin_accepted_title'),
    );

    $form['admin_email_settings']['accepted_admin_email']['accepted_admin_email_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Email body'),
      '#default_value' => $bookingContent->get('koba_email_admin.email_admin_accepted_body'),
      '#description' => $tokens_description,
    );

    $form['admin_email_settings']['accepted_admin_email']['accepted_admin_email_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save admin accepted email settings'),
      '#weight' => 1,
      '#submit' => array('::accepted_admin_email_submit'),
    );

    // Rejected email settings.
    $form['admin_email_settings']['rejected_admin_email'] = array(
      '#title' => $this->t('Booking rejected (Admin)'),
      '#type' => 'details',
      '#weight' => '3',
      '#group' => 'admin_email_settings',
    );

    $form['admin_email_settings']['rejected_admin_email']['rejected_admin_email_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email title'),
      '#default_value' => $bookingContent->get('koba_email_admin.email_admin_rejected_title'),
    );

    $form['admin_email_settings']['rejected_admin_email']['rejected_admin_email_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Email body'),
      '#default_value' => $bookingContent->get('koba_email_admin.email_admin_rejected_body'),
      '#description' => $tokens_description,
    );

    $form['admin_email_settings']['rejected_admin_email']['rejected_admin_email_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save admin rejected email settings'),
      '#weight' => 1,
      '#submit' => array('::rejected_admin_email_submit'),
    );

    // Cancelled email settings.
    $form['admin_email_settings']['cancelled_admin_email'] = array(
      '#title' => $this->t('Booking cancelled (Admin)'),
      '#type' => 'details',
      '#weight' => '4',
      '#group' => 'admin_email_settings',
    );

    $form['admin_email_settings']['cancelled_admin_email']['cancelled_admin_email_title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Email title'),
      '#default_value' => $bookingContent->get('koba_email_admin.email_admin_cancelled_title'),
    );

    $form['admin_email_settings']['cancelled_admin_email']['cancelled_admin_email_body'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Email body'),
      '#default_value' => $bookingContent->get('koba_email_admin.email_admin_cancelled_body'),
      '#description' => $tokens_description,
    );

    $form['admin_email_settings']['cancelled_admin_email']['cancelled_admin_email_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save admin cancelled email settings'),
      '#weight' => 1,
      '#submit' => array('::cancelled_admin_email_submit'),
    );

    // Email theme settings.
    $installed_themes = array_filter(\Drupal::service('theme_handler')->rebuildThemeData(), function($theme) {
      return $theme->status;
    });
    $installed_themes_options = array('' => '');
    foreach ($installed_themes as $name => $theme) {
      $installed_themes_options[$name] = $theme->info['name'];
    }

    $form['user_email_settings']['theme'] = array(
      '#title' => $this->t('Theme'),
      '#type' => 'details',
      '#weight' => '87',
      '#group' => 'user_email_settings',
    );

    $form['user_email_settings']['theme']['email_theme'] = array(
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => $installed_themes_options,
      '#default_value' => $bookingContent->get('koba_email.email_theme'),
      '#description' => t('Theme used for sending user emails'),
    );

    $form['user_email_settings']['theme']['theme_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save email theme settings'),
      '#weight' => 1,
      '#submit' => array('::theme_submit'),
    );

    // Admin email theme settings.
    $form['admin_email_settings']['admin_theme'] = array(
      '#title' => $this->t('Theme'),
      '#type' => 'details',
      '#weight' => '87',
      '#group' => 'admin_email_settings',
    );

    $form['admin_email_settings']['admin_theme']['admin_email_theme'] = array(
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#options' => $installed_themes_options,
      '#default_value' => $bookingContent->get('koba_email_admin.admin_email_theme'),
      '#description' => t('Theme used for sending admin emails'),
    );

    $form['admin_email_settings']['admin_theme']['admin_theme_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save admin email theme settings'),
      '#weight' => 1,
      '#submit' => array('::admin_theme_submit'),
    );


    return $form;
  }


  /**
   * Form submission handler for booking created message config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function booking_created_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Booking message settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_booking.created_booking_message' => $form_state->getValue('booking_created'),
    ));
  }


  /**
   * Form submission handler for booking created message config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function booking_help_text_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Booking message settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_booking.why_email' => $form_state->getValue('why_email_description')['value'],
      'koba_booking.why_title' => $form_state->getValue('why_title_description')['value'],
    ));
  }


  /**
   * Form submission handler for pending email config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function pending_email_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Pending email settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email.email_pending_title' => $form_state->getValue('pending_email_title'),
      'koba_email.email_pending_body' => $form_state->getValue('pending_email_body')['value'],
      'koba_email.email_pending_search_phase_body' => $form_state->getValue('pending_email_search_phase_body')['value'],
    ));
  }


  /**
   * Form submission handler for accepted email config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function accepted_email_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Accepted email settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email.email_accepted_title' => $form_state->getValue('accepted_email_title'),
      'koba_email.email_accepted_body' => $form_state->getValue('accepted_email_body')['value'],
    ));
  }


  /**
   * Form submission handler for rejected email config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function rejected_email_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Rejected email settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email.email_rejected_title' => $form_state->getValue('rejected_email_title'),
      'koba_email.email_rejected_body' => $form_state->getValue('rejected_email_body')['value'],
    ));
  }


  /**
   * Form submission handler for cancelled email config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function cancelled_email_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Cancelled email settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email.email_cancelled_title' => $form_state->getValue('cancelled_email_title'),
      'koba_email.email_cancelled_body' => $form_state->getValue('cancelled_email_body')['value'],
    ));
  }


  /**
   * Form submission handler for email theme config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function theme_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Email theme settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email.email_theme' => $form_state->getValue('email_theme'),
    ));
  }


  /**
   * Form submission handler for email admin theme config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function admin_theme_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Email theme settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email_admin.admin_email_theme' => $form_state->getValue('admin_email_theme'),
    ));
  }


  /**
   * Form submission handler for pending email config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */

  public function pending_admin_email_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Pending email settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email_admin.email_admin_pending_title' => $form_state->getValue('pending_admin_email_title'),
      'koba_email_admin.email_admin_pending_body' => $form_state->getValue('pending_admin_email_body')['value'],
    ));
  }


  /**
   * Form submission handler for accepted email config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function accepted_admin_email_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Accepted email settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email_admin.email_admin_accepted_title' => $form_state->getValue('accepted_admin_email_title'),
      'koba_email_admin.email_admin_accepted_body' => $form_state->getValue('accepted_admin_email_body')['value'],
    ));
  }


  /**
   * Form submission handler for rejected email config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function rejected_admin_email_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Rejected email settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email_admin.email_admin_rejected_title' => $form_state->getValue('rejected_admin_email_title'),
      'koba_email_admin.email_admin_rejected_body' => $form_state->getValue('rejected_admin_email_body')['value'],
    ));
  }


  /**
   * Form submission handler for cancelled email config.
   *
   * @param $form
   *   An associative array containing the structure of the form.
   * @param $form_state
   *   The current state of the form.
   */
  public function cancelled_admin_email_submit(array $form, FormStateInterface $form_state) {
    drupal_set_message('Cancelled email settings saved');
    $this->getBookingContent()->setMultiple(array(
      'koba_email_admin.email_admin_cancelled_title' => $form_state->getValue('cancelled_admin_email_title'),
      'koba_email_admin.email_admin_cancelled_body' => $form_state->getValue('cancelled_admin_email_body')['value'],
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }
}
