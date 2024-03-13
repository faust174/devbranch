<?php

namespace Drupal\weather_info\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
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
    protected ClientInterface $httpClient) {
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
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build():array {
    try {
      $weatherData = $this->weatherData();
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
      '#attached' => [
        'library' => [
          'weather_info/weather_info',
        ],
      ],
    ];
  }
  /**
   * Receive the data from weather API.
   */
  protected function weatherData():array {
    $config = $this->config->get('weather_info.settings');
    $city = !empty($config->get('city')) ? $config->get('city') : 'Lutsk';
    $apiKey = $config->get('api');
    $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
      'query' => [
        'q' => $city,
        'appid' => $apiKey,
        'units' => 'metric',
      ],
    ]);
    $request = $response->getBody()->getContents();
    return json_decode($request, TRUE);
  }
}
