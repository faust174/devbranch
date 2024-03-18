<?php

namespace Drupal\weather_info\Service;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * Provides connection to weather API.
 */
class WeatherAPIConnectionHandler {

  public function __construct(protected ClientInterface $httpClient, protected ConfigFactoryInterface $config, protected CacheBackendInterface $cache) {
  }

  /**
   * {@inheritDoc}
   */
  public function getWeatherData($city) {
    $cache_id = 'weather_api_data_' . $city;
    if ($cache = $this->cache->get($cache_id)) {
      return $cache->data;
    }
    $apiKey = $this->config->get('weather_info.settings')->get('api');
    $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
      'query' => [
        'q' => $city,
        'appid' => $apiKey,
        'units' => 'metric',
      ],
    ]);

    $weatherData = json_decode($response->getBody()->getContents(), TRUE);
    $this->cache->set($cache_id, $weatherData, time() + 1800);

    return $weatherData;
  }

}
