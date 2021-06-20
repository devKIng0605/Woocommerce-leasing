<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class FindLeasingAPI
{
  const API_URL = 'https://www.findleasing.nu/api/';
  public $API_KEY;
  public $ORDERING = array(
    'make', '-make', 'year', '-year', 'price_monthly', '-price_monthly', 'id', '-id'
  );

  public function __construct($api_key) {
    $this->API_KEY = $api_key;
  }

  private function getPage($page, $params = array()) {
    $curl = curl_init();
    $url = self::API_URL . $page .'/';

    if (!empty($params)) {
      $i = 0;
      foreach($params as $key => $value) {
        $url .= ($i === 0 ? '?' : '&') . $key . '=' . urlencode($value);
        $i++;
      }
    }

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        'cache-control: no-cache',
        'Authorization: Token ' . $this->API_KEY,
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    return json_decode($response, true);

    //return $response;
  }

  public function getAvailableMakes() {
    return $this->getPage('car-make', $params = array('existing' => '1'));
  }

  public function getAvailableSaleMakes() {
    return $this->getPage('car-make', $params = array('existing' => '1', 'sales' => '1'));
  }

  public function getListings() {
    return $this->getPage('listings');
  }

  public function getOffers($params = array()) {
    $params['calculated'] = 'true';
    $params['plugin_version'] = '0.1.14';
    return $this->getPage('offers', $params = $params);
  }

  public function getSaleOffers($params = array()) {
    return $this->getPage('offers-sale', $params = $params);
  }

  public function getOffer($id) {
    return $this->getPage("offers/$id");
  }

  public function getListing($id) {
    return $this->getPage("listings/$id");
  }

  public function getOffersFind($lookup) {
    return $this->getPage("offers-find/$lookup");
  }
}

?>
