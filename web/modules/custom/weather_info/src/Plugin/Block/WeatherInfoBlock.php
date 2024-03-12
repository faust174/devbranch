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
    $client = new Client();
    $response = $client->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
      'query' => [
        'q' => 'Lutsk',
        'appid' => 'c8e4bbc86dc0de8d0da33ec01aa9cbf2',
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
}
