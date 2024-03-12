<?php

namespace Drupal\weather_info\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use GuzzleHttp\Client;

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
class WeatherInfoBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    try {
      $config = \Drupal::config('weather_info.settings');
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
      ];
    }
    catch (\Exception $e) {
      \Drupal::logger('weather_info')->error('An error occurred while fetching weather data: @error', ['@error' => $e->getMessage()]);
      return [];
    }
  }
}
