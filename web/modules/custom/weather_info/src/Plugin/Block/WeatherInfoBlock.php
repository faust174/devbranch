<?php

namespace Drupal\weather_info\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\weather_info\Service\UserSelectedCity;
use GuzzleHttp\ClientInterface;
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
    protected ClientInterface $httpClient,
    protected AccountProxyInterface $currentUser,
    protected Connection $database,
    protected UserSelectedCity $userSelectedCity,
    protected CacheBackendInterface $cache) {
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
      $container->get('http_client'),
      $container->get('current_user'),
      $container->get('database'),
      $container->get('weather_info.user_selected_city'),
      $container->get('cache.default'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $city = $this->userSelectedCity->getUserSelectedCity();
    $cache_id = 'weather_info_block_' . $city;
    if ($value = $this->cache->get($cache_id)) {
      return $value->data;
    }
    try {
      $weatherData = $this->weatherData($city);
    }
    catch (\Exception $e) {
      $this->logger->get('weather_info')->error('An error occurred while fetching weather data: @error', ['@error' => $e->getMessage()]);
    }
    if (empty($weatherData)) {
      return [];
    }
    return [
      '#theme' => 'fausttheme_weather_info',
      '#temp' => $weatherData['main']['temp'],
      '#wind' => $weatherData['wind']['speed'],
      '#city' => $weatherData['name'],
      '#attached' => [
        'library' => [
          'weather_info/weather_info',
        ],
      ],
    ];
    $this->cache->set($cache_id, $build, time() + 1800);
    return $build;
  }

  /**
   * Receive the data from weather API.
   */
  protected function weatherData($city): array {
    $config = $this->config->get('weather_info.settings');
    $apiKey = $config->get('api');
    $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
      'query' => [
        'q' => $city,
        'appid' => $apiKey,
        'units' => 'metric',
      ],
    ]);
    return json_decode($response->getBody()->getContents(), TRUE);
  }

}
