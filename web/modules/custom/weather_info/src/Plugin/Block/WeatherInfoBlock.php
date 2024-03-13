<?php

namespace Drupal\weather_info\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use GuzzleHttp\Client;
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
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $config;
  /**
   * The Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $logger;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config, LoggerChannelFactoryInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    try {
      $config = $this->config->get('weather_info.settings');
      $city = !empty($config->get('city')) ? $config->get('city') : 'Lutsk';
      $apiKey = $config->get('api');
      $client = new Client();
      $response = $client->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
        'query' => [
          'q' => $city,
          'appid' => $apiKey,
          'units' => 'metric',
        ],
      ]);

      $body = $response->getBody()->getContents();
      $data = json_decode($body, TRUE);

      return [
        '#theme' => 'fausttheme_weather_info',
        '#temp' => $data['main']['temp'],
        '#wind' => $data['wind']['speed'],
        '#attached' => [
          'library' => [
            'weather_info/weather_info',
          ],
        ],
      ];
    }
    catch (\Exception $e) {
      $this->logger->get('weather_info')->error('An error occurred while fetching weather data: @error', ['@error' => $e->getMessage()]);
      return [];
    }
  }

}
