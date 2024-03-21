<?php

namespace Drupal\registration_form\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\RegisterForm;
use Drupal\weather_info\Service\UserCityHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Provides a Registration form.
 */
class RegistrationForm extends RegisterForm {

  public function __construct(
    EntityRepositoryInterface $entity_repository,
    LanguageManagerInterface $language_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL,
    TimeInterface $time = NULL,
    protected AccountProxyInterface $currentUser,
    protected Connection $database,
    protected SessionInterface $session,
    protected UserCityHandler $userCityHandler,
  ) {
    parent::__construct($entity_repository, $language_manager, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('entity.repository'),
    $container->get('language_manager'),
    $container->get('entity_type.bundle.info'),
    $container->get('datetime.time'),
    $container->get('current_user'),
    $container->get('database'),
    $container->get('session'),
    $container->get('weather_info.user_selected_city'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $countries = CountryManager::getStandardList();
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $terms = $term_storage->loadTree('category');
    foreach ($terms as $term) {
      $options[$term->tid] = $term->name;
    }
    $cities = $this->userCityHandler->getCities();
    $form['country'] = [
      '#title' => $this->t("Country name:"),
      '#type' => 'select',
      '#options' => $countries,
      '#required' => TRUE,
    ];
    $form['city'] = [
      '#title' => $this->t('City:'),
      '#type' => 'select',
      '#options' => $cities,
      '#required' => TRUE,
    ];
    $form['interests'] = [
      '#title' => $this->t("Interests:"),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $user_entity = $form_state->getFormObject()->getEntity();
    $user_entity->save();
    $uid = $user_entity->id();
    $values = $form_state->getValues();
    $selected_interests = [];
    foreach ($values['interests'] as $tid => $selected) {
      if ($selected != 0) {
        $selected_interests[] = $tid;
      }
    }
    $this->database->insert('user_data')
      ->fields([
        'uid' => $uid,
        'city' => $values['city'],
        'country' => $values['country'],
      ])
      ->execute();
    foreach ($selected_interests as $tid) {
      $this->database->insert('user_interests')
        ->fields([
          'uid' => $uid,
          'tid' => $tid,
        ])
        ->execute();
    }
  }

}
