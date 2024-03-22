<?php

namespace Drupal\weather_info\Service;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Defines a cache context for weather information.
 */
class WeatherInfoCacheService implements CacheContextInterface {

  public function __construct(protected AccountProxyInterface $currentUser, protected UserCityHandler $userCityHandler) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('City cache context');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext():string {
    return $this->userCityHandler->getUserSelectedCity();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata(): CacheableMetadata {
    return new CacheableMetadata();
  }

}
