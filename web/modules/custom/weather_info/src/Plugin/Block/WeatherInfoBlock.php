<?php

namespace Drupal\weather_info\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\weather_info\Service\UserCityHandler;
use Drupal\weather_info\Service\WeatherAPIConnectionHandler;
use Drupal\weather_info\Service\WeatherInfoCacheService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Weather Info' block.
 *
 * @package Drupal\weather_info\Plugin\Block
 */
#[Block(
  id: "weather_info_block",
  admin_label: new TranslatableMarkup("Weather Info Block"),
  category: new TranslatableMarkup("Custom Weather"),
)]
class WeatherInfoBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructor for WeatherInfoBlock.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected ConfigFactoryInterface $config,
    protected LoggerChannelFactoryInterface $logger,
    protected UserCityHandler $userCityHandler,
    protected WeatherAPIConnectionHandler $weatherHandler,
    protected CacheBackendInterface $cache,
    protected WeatherInfoCacheService $cacheService,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition):static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('weather_info.user_selected_city'),
      $container->get('weather_info.weather_api_connection_handler'),
      $container->get('cache.default'),
      $container->get('cache_context.weather_data'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $city = $this->userCityHandler->getUserSelectedCity() ?? 'Lutsk';
    try {
      $weatherData = $this->weatherHandler->getWeatherData($city);
    }
    catch (\Exception $e) {
      $this->logger->get('weather_info')
        ->error('An error occurred while fetching weather data: @error',
          ['@error' => $e->getMessage()]);
    }

    if (empty($weatherData)) {
      return [];
    }
    $build = [
      '#theme' => 'fausttheme_weather_info',
      '#temp' => $weatherData['main']['temp'],
      '#wind' => $weatherData['wind']['speed'],
      '#city' => $weatherData['name'],
      '#attached' => [
        'library' => [
          'weather_info/weather_info',
        ],
      ],
      '#cache' => [
        'max-age' => 1800,
        'contexts' => ['weather_data'],
      ],
    ];
    return $build;
  }

}
