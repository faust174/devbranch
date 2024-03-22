<?php

namespace Drupal\weather_info\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\weather_info\Service\UserCityHandler;
use Drupal\weather_info\Service\WeatherAPIConnectionHandler;
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
    protected LoggerChannelFactoryInterface $logger,
    protected UserCityHandler $userCityHandler,
    protected WeatherAPIConnectionHandler $weatherHandler,
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
      $container->get('logger.factory'),
      $container->get('weather_info.user_selected_city'),
      $container->get('weather_info.weather_api_connection_handler'),
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
      return [
        '#cache' => [
          'max-age' => 1800,
        ],
      ];
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
      '#cache' => [
        'max-age' => 1800,
        'contexts' => ['weather_data'],
      ],
    ];
  }

}
