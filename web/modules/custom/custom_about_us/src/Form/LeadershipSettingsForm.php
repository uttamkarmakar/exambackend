<?php

namespace Drupal\custom_about_us\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for managing leadership settings.
 */
class LeadershipSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'about_us_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $totalRows = $form_state->get('rows_count');

    if ($totalRows === NULL) {
      $totalRows = 1;
      $form_state->set('rows_count', $totalRows);
    }

    $form['leader'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Index'),
        $this->t('Name'),
        $this->t('Designation'),
        $this->t('LinkedIn Profile Link'),
        $this->t('Profile Image'),
        $this->t('Operations'),
      ],
      '#prefix' => '<div id="groups-wrapper">',
      '#suffix' => '</div>',
    ];

    for ($i = 0; $i < $totalRows; $i++) {
      $form['leader'][$i]['index'] = [
        '#markup' => $i + 1,
      ];

      $form['leader'][$i]['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
      ];

      $form['leader'][$i]['designation'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Designation'),
      ];

      $form['leader'][$i]['linkedin_profile'] = [
        '#type' => 'url',
        '#title' => $this->t('LinkedIn Profile Link'),
      ];

      $form['leader'][$i]['profile_image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Profile Image'),
        '#upload_location' => 'public://',
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg gif'],
        ],
      ];

      $form['leader'][$i]['operations'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#submit' => ['::removeLeader'],
        '#name' => $i,
        '#attributes' => [
          'class' => ['remove-button'],
        ],
      ];
    }

    $form['anchor_user'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Select Anchor User'),
      '#target_type' => 'user',
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
      '#tags' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['add'] = [
      '#type' => 'submit',
      '#value' => 'Add Row',
      '#submit' => ['::addMore'],
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'groups-wrapper',
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * Submit handler for adding more rows to the leadership settings table.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function addMore(array &$form, FormStateInterface $form_state) {
    $totalRows = $form_state->get('rows_count');
    $form_state->set('rows_count', $totalRows + 1);
    $form_state->setRebuild();
  }

  /**
   * Callback to rebuild the form with added rows for leadership settings.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The updated form element for the leadership settings table.
   */
  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['leader'];
  }

  /**
   * Submit handler for removing a row from the leadership settings table.
   *
   * @param array $form
   *   The form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function removeLeader(array &$form, FormStateInterface $form_state) {
    $totalRows = $form_state->get('rows_count');
    if ($totalRows > 1) {
      $triggeringElement = $form_state->getTriggeringElement();
      $index = $triggeringElement['#name'];
      unset($form_state->getValues()['leader'][$index]);
      $form_state->set('rows_count', $totalRows - 1);
      $form_state->setRebuild();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    foreach ($values['leader'] as $group_data) {
      $selectedUsers = $values['anchor_user'];
      $config = \Drupal::configFactory()->getEditable('custom_about_us.settings');
      $config->set('anchor_user', $selectedUsers)->save();
      $name = $group_data['name'];
      $designation = $group_data['designation'];
      $linkedin_profile = $group_data['linkedin_profile'];
      $profile_image = $group_data['profile_image'][0] ?? NULL;

      $config = \Drupal::configFactory()->getEditable('custom_about_us.settings');

      $leadership_settings = $config->get('leadership_settings') ?: [];

      $leadership_settings[] = [
        'name' => $name,
        'designation' => $designation,
        'linkedin_profile' => $linkedin_profile,
        'profile_image' => $profile_image,
      ];

      $config->set('leadership_settings', $leadership_settings)->save();
    }

    $this->messenger()->addStatus($this->t('Form submission successful.'));
  }

}
