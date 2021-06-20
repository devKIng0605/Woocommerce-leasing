<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function fl_get_params($param, $value) {
  $params = $_GET;
  $params[$param] = $value;

  return '?' . http_build_query($params);
}

function findleasing_plugins_url($url) {
  return plugins_url($url, __FILE__);
}

function findleasing_plugins_dir() {
  return plugin_dir_path(__FILE__);
}

function findleasing_theme_file($file) {
  $template_path = get_template_directory();
  $template_file = $template_path . '/findleasing/' . $file;

  $path = findleasing_plugins_dir();
  $theme = get_option('findleasing-theme') ? get_option('findleasing-theme') : 'default';

  if (file_exists($template_file)) {
    return $template_file;
  }

  return $path . 'themes/' . $theme . '/' . $file;
}

function fl_get_listing_url($prefix, $offer) {
    return get_site_url(null, sprintf('%s/%s/%s/', $prefix, sanitize_title($offer['full_title']), $offer['id']));
}

function fl_get_row_classes() {
  $classes = 'col-md-4 col-sm-6 col-12';

  if (get_option('findleasing-offers-row') == '4') {
    $classes = 'col-lg-3 ' . $classes;
  }
  return $classes;
}

function fl_get_static_listing_url($listing) {
  $findleasing_leasing_page_name = get_option('findleasing_leasing_page_name');

  return get_site_url(null, sprintf('%s/%s/%s/', $findleasing_leasing_page_name, sanitize_title($listing['full_header']), $listing['id']));
}

function fl_get_leasing_listing_url($offer) {
  $findleasing_leasing_page_name = get_option('findleasing_leasing_page_name');

  return fl_get_listing_url($findleasing_leasing_page_name, $offer);
}

function fl_get_sales_listing_url($offer) {
  $findleasing_sales_page_name = get_option('findleasing_sales_page_name');

  return fl_get_listing_url($findleasing_sales_page_name, $offer);
}


function fl_embed_leasing_car_preview($offer) {

  $price_tax = get_option('findleasing-offers-tax') == 'inclusive';
  $car = $offer['car'];

  ob_start();

  ?>

  <div class="<?php echo fl_get_row_classes(); ?> fl-offset-20">
    <a href="<?php echo fl_get_leasing_listing_url($offer); ?>" title="<?php echo $offer['full_title']; ?>">
      <div class="row">
        <div class="col-12">
          <h5 class="fl-title"><?php echo $car['make'] . ' ' . $car['model']; ?></h5>
          <span class="fl-sub-title"><?php echo $offer['title']; ?></span>
        </div>
        <div class="col-12">
          <img src="<?php echo $offer['thumbnail']; ?>" alt="<?php echo $offer['full_header'] ?>" class="fl-img-responsive">
        </div>
        <div class="col-12 text-muted">
          <div class="row fl-offset-5">
            <div class="col-6 col-md-3 text-center">
              <span class="fl-stat-label">Kilometer</span><br>
              <span class="fl-stat-value"><?php echo number_format_i18n($offer['car']['mileage']); ?></span>
            </div>
            <div class="col-6 col-md-3 text-center">
              <span class="fl-stat-label">Årgang</span><br>
              <span class="fl-stat-value"><?php echo $car['year']; ?></span>
            </div>
            <div class="col-6 col-md-3 text-center">
              <span class="fl-stat-label">Brændstof</span><br>
              <span class="fl-stat-value"><?php echo $car['fuel_type']; ?></span>
            </div>
            <div class="col-6 col-md-3 text-center">
              <span class="fl-stat-label">Km/L</span><br>
              <span class="fl-stat-value"><?php echo number_format_i18n($car['efficiency'], 1); ?></span>
            </div>
          </div>
        </div>
        <div class="col-12 text-center fl-price-monthly-container fl-offset-5">
          <div class="fl-price-monthly">
            Pr. mnd. <span class="fl-price-monthly-value"><?php echo number_format_i18n(($price_tax ? $offer['price_monthly_tax'] : $offer['price_monthly'])); ?></span> kr.
          </div>
        </div>
        <div class="col-12 leasing-info">
          <div class="row">
            <div class="col-8">Periode</div>
            <div class="col-4 text-right"><?php echo number_format_i18n($offer['period']); ?> mdr.</div>
          </div>
          <div class="row">
            <div class="col-8">Udbetaling</div>
            <div class="col-4 text-right"><?php echo number_format_i18n(($price_tax ? $offer['first_pay_tax'] : $offer['first_pay'])); ?> kr.</div>
          </div>
          <?php if ($offer['funding']['id'] === 1) { ?>
          <div class="row">
            <div class="col-6">Restværdi</div>
            <div class="col-6 text-right"><?php echo number_format_i18n($offer['remaining_value']); ?> kr. <?php echo ($price_tax && !$offer['price_tax'] ? 'ex. moms' : (!$price_tax && $offer['price_tax'] ? 'inkl. moms' : '')); ?></div>
          </div>
          <?php } ?>
        </div>
        <div class="col-12">
          <hr class="fl-border-bottom">
        </div>
      </div>
    </a>
  </div>

  <?php 

  return ob_get_clean();
}

function fl_embed_shortcode($atts = array(), $content = null, $tag = '') {

  $atts = $atts ? $atts : array();

  $api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));

  $limit = array_key_exists('limit', $atts) ? (int)$atts['limit'] : 9;
  $query = array_key_exists('query', $atts) ? $atts['query'] : '';
  $fuel_type = array_key_exists('fuel_type', $atts) ? $atts['fuel_type'] : '';
  $funding = array_key_exists('funding', $atts) ? $atts['funding'] : '';
  $stock_status = array_key_exists('stock_status', $atts) ? $atts['stock_status'] : '';
  $ordering = array_key_exists('order', $atts) ? $atts['order'] : (get_option('findleasing-offers-ordering') ? get_option('findleasing-offers-ordering') : 'make');
  $price_tax = get_option('findleasing-offers-tax') == 'inclusive';
  $is_van = array_key_exists('is_van', $atts) ? (int)$atts['is_van'] : '';
  $make = array_key_exists('make', $atts) ? $atts['make'] : '';
  $models = array_key_exists('models', $atts) ? $atts['models'] : '';
  $efficiency_min = array_key_exists('efficiency_min', $atts) ? $atts['efficiency_min'] : '';
  $efficiency_max = array_key_exists('efficiency_max', $atts) ? $atts['efficiency_max'] : '';
  $categories = array_key_exists('categories', $atts) ? $atts['categories'] : '';

  $response = $api->getOffers(array(
    'show_in_iframe' => 'true',
    'page_size' => $limit,
    'page' => 1,
    'ordering' => $ordering,
    'search' => $query,
    'fuel_type' => $fuel_type,
    'funding' => $funding,
    'stock_status' => $stock_status,
    'is_van' => $is_van,
    'make' => $make,
    'models' => $models,
    'efficiency_min' => $efficiency_min,
    'efficiency_max' => $efficiency_max,
    'categories' => $categories
  ));

  ob_start();

  ?>

  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/findleasing.css?v=5'); ?>" type="text/css" />
  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/flbootstrap.min.css?v=1'); ?>" type="text/css" />

  <div class="fl-bs fl-embed-wrapper">
    <div class="row">
      <?php
      foreach($response['results'] as $offer) {
        echo apply_filters('findleasing-leasing-preview', $offer);
      }
      ?>
    </div>
  </div>
  <?php

  return ob_get_clean();
}

function fl_embed_sales_shortcode($atts = array(), $content = null, $tag = '') {

  $atts = $atts ? $atts : array();

  $api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));

  $limit = array_key_exists('limit', $atts) ? (int)$atts['limit'] : 9;
  $query = array_key_exists('query', $atts) ? $atts['query'] : '';
  $fuel_type = array_key_exists('fuel_type', $atts) ? $atts['fuel_type'] : '';
  $stock_status = array_key_exists('stock_status', $atts) ? $atts['stock_status'] : '';
  $ordering = array_key_exists('order', $atts) ? $atts['order'] : (get_option('findleasing-offers-ordering') ? get_option('findleasing-offers-ordering') : 'make');
  $is_van = array_key_exists('is_van', $atts) ? (int)$atts['is_van'] : '';
  $make = array_key_exists('make', $atts) ? $atts['make'] : '';
  $models = array_key_exists('models', $atts) ? $atts['models'] : '';

  $response = $api->getSaleOffers(array(
    'show_in_iframe' => 'true',
    'page_size' => $limit,
    'page' => 1,
    'fuel_type' => $fuel_type,
    'search' => $query,
    'ordering' => $ordering,
    'stock_status' => $stock_status,
    'is_van' => $is_van,
    'make' => $make,
    'models' => $models
  ));

  ob_start();

  ?>

  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/findleasing.css?v=5'); ?>" type="text/css" />
  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/flbootstrap.min.css?v=1'); ?>" type="text/css" />

  <div class="fl-bs fl-embed-wrapper">
    <div class="row">

  <?php
  foreach($response['results'] as $offer) {
      $car = $offer['car'];
    ?>
      <div class="<?php echo fl_get_row_classes(); ?> fl-offset-20">
        <a href="<?php echo fl_get_sales_listing_url($offer); ?>" title="<?php echo $offer['full_title']; ?>">
          <div class="row">
            <div class="col-12">
              <h5 class="fl-title"><?php echo $car['make'] . ' ' . $car['model']; ?></h5>
              <span class="fl-sub-title"><?php echo $offer['title']; ?></span>
            </div>
            <div class="col-12">
              <img src="<?php echo $offer['thumbnail']; ?>" alt="<?php echo $offer['full_header'] ?>" class="fl-img-responsive">
            </div>
            <div class="col-12 text-muted">
              <div class="row fl-offset-5">
                <div class="col-6 col-md-3 text-center">
                  <span class="fl-stat-label">Kilometer</span><br>
                  <span class="fl-stat-value"><?php echo number_format_i18n($offer['car']['mileage']); ?></span>
                </div>
                <div class="col-6 col-md-3 text-center">
                  <span class="fl-stat-label">Årgang</span><br>
                  <span class="fl-stat-value"><?php echo $car['year']; ?></span>
                </div>
                <div class="col-6 col-md-3 text-center">
                  <span class="fl-stat-label">Brændstof</span><br>
                  <span class="fl-stat-value"><?php echo $car['fuel_type']; ?></span>
                </div>
                <div class="col-6 col-md-3 text-center">
                  <span class="fl-stat-label">Km/L</span><br>
                  <span class="fl-stat-value"><?php echo number_format_i18n($car['efficiency'], 1); ?></span>
                </div>
              </div>
            </div>
            <div class="col-12 text-center fl-price-monthly-container fl-offset-5">
              <div class="fl-price-monthly">
                <span class="fl-price-monthly-value fl-detail-price-value"><?php echo number_format_i18n($offer['detail_price']); ?></span> kr.
              </div>
            </div>
            <div class="col-12">
              <hr class="fl-border-bottom">
            </div>
          </div>
        </a>
      </div>
      <?php
      }
  ?>
    </div>
  </div>
  <?php

  return ob_get_clean();
}

function fl_embed_paginated_shortcode($atts = array(), $content = null, $tag = '') {

  $atts = $atts ? $atts : array();

  $api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));

  $query = array_key_exists('query', $atts) ? $atts['query'] : '';
  $limit = array_key_exists('limit', $atts) ? (int)$atts['limit'] : 24;
  $fuel_type = array_key_exists('fuel_type', $atts) ? $atts['fuel_type'] : '';
  $funding = array_key_exists('funding', $atts) ? $atts['funding'] : '';
  $price_tax = array_key_exists('tax', $atts) ? ($atts['tax'] == '1') : (get_option('findleasing-offers-tax') == 'inclusive');
  $stock_status = array_key_exists('stock_status', $atts) ? $atts['stock_status'] : '';
  $is_van = array_key_exists('is_van', $atts) ? (int)$atts['is_van'] : '';
  $make = array_key_exists('make', $atts) ? $atts['make'] : '';
  $models = array_key_exists('models', $atts) ? $atts['models'] : '';
  $efficiency_min = array_key_exists('efficiency_min', $atts) ? $atts['efficiency_min'] : '';
  $efficiency_max = array_key_exists('efficiency_max', $atts) ? $atts['efficiency_max'] : '';
  $categories = array_key_exists('categories', $atts) ? $atts['categories'] : '';

  $ordering = array_key_exists('ordering', $_GET) ? $_GET['ordering'] : null;
  $make_filter = array_key_exists('make', $_GET) ? $_GET['make'] : '';
  $page = array_key_exists('page_num', $_GET) ? $_GET['page_num'] : 1;

  if (!in_array($ordering, $api->ORDERING)) {
    $ordering = get_option('findleasing-offers-ordering') ? get_option('findleasing-offers-ordering') : 'make';
  }

  if (!empty($make_filter)) {
    $query = $make_filter;
  }

  $page = (int)$page;

  $makes = $api->getAvailableMakes();

  $response = $api->getOffers(array(
    'show_in_iframe' => 'true',
    'page_size' => $limit,
    'page' => $page,
    'ordering' => $ordering,
    'fuel_type' => $fuel_type,
    'funding' => $funding,
    'stock_status' => $stock_status,
    'search' => $query,
    'is_van' => $is_van,
    'make' => $make,
    'models' => $models,
    'efficiency_min' => $efficiency_min,
    'efficiency_max' => $efficiency_max,
    'categories' => $categories
  ));

  $_order = ltrim($ordering, '-');
  $desc = substr($ordering, 0, 1) === '-';
  $num_pages = ceil($response['count'] / $limit);

  ob_start();

  ?>

  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/findleasing.css?v=5'); ?>" type="text/css" />
  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/flbootstrap.min.css?v=1'); ?>" type="text/css" />

  <div class="fl-bs">
    <div class="row">
      <div class="col-6">
        <?php if (sizeof($makes) < 5) { ?>
          <ul class="fl-sorting-container">
          <?php 
          foreach($makes as $make) { ?>
            <li><a href="<?php echo fl_get_params('make', strtolower($make['name'])); ?>"><?php echo $make['name']; ?></a></li>
          <?php } ?>
          </ul>
        <?php } else { ?>
          <form method="get" action=".">
            <select name="make" onchange="this.form.submit()">
              <option value="" <?php $make_filter == '' ? 'selected' : ''?>>Vælg mærke</option>
              <?php foreach($makes as $make) { 
                $_make = strtolower($make['name']);
                ?>
                <option value="<?php echo $_make; ?>" <?php echo ($_make == strtolower($make_filter) ? 'selected' : '') ?>><?php echo $make['name']; ?></option>
              <?php } ?>
            </select>
          </form>
        <?php } ?>
      </div>
      <div class="col-6 text-right">
        <ul class="fl-sorting-container">
          <li class="hidden-xs text-muted">Sortering:</li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'make' && !$desc ? '-' : '') . 'make'); ?>">Mærke <?php echo $_order == 'make' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') .'"></i>' : ''; ?></a></li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'year' && $desc ? '' : '-') . 'year'); ?>">Årgang <?php echo $_order == 'year' ? '<i class="fa fa-caret-' . ($desc ? 'down' : 'up') .'"></i>' : ''; ?></a></li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'price_monthly' && !$desc ? '-' : '') . 'price_monthly'); ?>">Pris <?php echo $_order == 'price_monthly' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') .'"></i>' : ''; ?></a></li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'id' && $desc ? '' : '-') . 'id'); ?>">Nyeste <?php echo $_order == 'id' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') .'"></i>' : ''; ?></a></li>
        </ul>
      </div>
    </div>
    <div class="row">

  <?php
  foreach($response['results'] as $offer) {
    echo apply_filters('findleasing-leasing-preview', $offer);  
  }
  ?>
    </div>
    <div class="row">
      <div class="col-12" style="text-align: center;">
        <ul class="fl-pagination">
        <?php 
        for ($i = 1; $i < $num_pages + 1; $i++): ?>
          <li><a href="?page_num=<?php echo $i; ?>&make=<?php echo $make_filter; ?>&ordering=<?php echo $ordering; ?>" class="<?php echo ($i === $page ? 'active' : '') ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
        </ul>
      </div>
    </div>
  </div>
  <?php

  return ob_get_clean();
}

function fl_lookup_id($lookup) {
  $api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));
  $response = $api->getOffersFind($lookup);
  return $response;
}

function fl_embed_sliders($atts = array(), $content = null, $tag = '') {
  $atts = $atts ? $atts : array();
  $type = array_key_exists('type', $atts) ? $atts['type'] : '';

  if (!array_key_exists('id', $atts)) {
    return '';
  }

  $id = $atts['id'];
  $fl_id = '';

  $response = fl_lookup_id($id);

  if (($type === '' || $type == 'financial') && $response['financial'] !== null) {
    $fl_id = $response['financial'];
  } elseif (($type === '' || $type == 'operational') && $response['operational'] !== null) {
    $fl_id = $response['operational'];
  }

  if ($fl_id) {
    return '<div id="findleasing-sliders-embed-div" data-findleasing data-width="100%" data-id="'.$fl_id.'"></div>'
         . '<script src="https://www.findleasing.nu/static/javascript/embed-sliders.js"></script>';
  }

  return '';
}

function fl_embed_sales_paginated_shortcode($atts = array(), $content = null, $tag = '') {

  $atts = $atts ? $atts : array();

  $api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));

  $query = array_key_exists('query', $atts) ? $atts['query'] : '';
  $limit = array_key_exists('limit', $atts) ? (int)$atts['limit'] : 24;
  $fuel_type = array_key_exists('fuel_type', $atts) ? $atts['fuel_type'] : '';
  $stock_status = array_key_exists('stock_status', $atts) ? $atts['stock_status'] : '';

  $ordering = array_key_exists('ordering', $_GET) ? $_GET['ordering'] : null;
  $make_filter = array_key_exists('make', $_GET) ? $_GET['make'] : '';
  $page = array_key_exists('page_num', $_GET) ? $_GET['page_num'] : 1;

  $is_van = array_key_exists('is_van', $atts) ? (int)$atts['is_van'] : '';

  $make = array_key_exists('make', $atts) ? $atts['make'] : '';
  $models = array_key_exists('models', $atts) ? $atts['models'] : '';

  if (!in_array($ordering, $api->ORDERING)) {
    $ordering = get_option('findleasing-offers-ordering') ? get_option('findleasing-offers-ordering') : 'make';
  }

  $page = (int)$page;
  $makes = $api->getAvailableSaleMakes();

  if (!empty($make_filter)) {
    $query = $make_filter;
  }

  $response = $api->getSaleOffers(array(
    'show_in_iframe' => 'true',
    'page_size' => $limit,
    'page' => $page,
    'ordering' => $ordering,
    'stock_status' => $stock_status,
    'search' => $query,
    'fuel_type' => $fuel_type,
    'is_van' => $is_van,
    'make' => $make,
    'models' => $models
  ));

  $_order = ltrim($ordering, '-');
  $desc = substr($ordering, 0, 1) === '-';
  $num_pages = ceil($response['count'] / $limit);

  ob_start();

  ?>

  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/findleasing.css?v=5'); ?>" type="text/css" />
  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/flbootstrap.min.css?v=1'); ?>" type="text/css" />

  <div class="fl-bs">
    <div class="row">
      <div class="col-6">
        <?php if (sizeof($makes) < 5) { ?>
          <ul class="fl-sorting-container">
          <?php 
          foreach($makes as $make) { ?>
            <li><a href="<?php echo fl_get_params('make', strtolower($make['name'])); ?>"><?php echo $make['name']; ?></a></li>
          <?php } ?>
          </ul>
        <?php } else { ?>
          <form method="get" action=".">
            <select name="make" onchange="this.form.submit()">
              <option value="" <?php $make_filter == '' ? 'selected' : ''?>>Vælg mærke</option>
              <?php foreach($makes as $make) { 
                $_make = strtolower($make['name']);
                ?>
                <option value="<?php echo $_make; ?>" <?php echo ($_make == strtolower($make_filter) ? 'selected' : '') ?>><?php echo $make['name']; ?></option>
              <?php } ?>
            </select>
          </form>
        <?php } ?>
      </div>
      <div class="col-6 text-right">
        <ul class="fl-sorting-container">
          <li class="hidden-xs text-muted">Sortering:</li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'make' && !$desc ? '-' : '') . 'make'); ?>">Mærke <?php echo $_order == 'make' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') .'"></i>' : ''; ?></a></li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'year' && $desc ? '' : '-') . 'year'); ?>">Årgang <?php echo $_order == 'year' ? '<i class="fa fa-caret-' . ($desc ? 'down' : 'up') .'"></i>' : ''; ?></a></li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'id' && $desc ? '' : '-') . 'id'); ?>">Nyeste <?php echo $_order == 'id' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') .'"></i>' : ''; ?></a></li>
        </ul>
      </div>
    </div>
    <div class="row">

  <?php
  foreach($response['results'] as $offer) {
      $car = $offer['car'];
    ?>
      <div class="<?php echo fl_get_row_classes(); ?> fl-offset-20">
        <a href="<?php echo fl_get_sales_listing_url($offer); ?>" title="<?php echo $offer['full_title']; ?>">
          <div class="row">
            <div class="col-12">
              <h5 class="fl-title"><?php echo $car['make'] . ' ' . $car['model']; ?></h5>
              <span class="fl-sub-title"><?php echo $offer['title']; ?></span>
            </div>
            <div class="col-12">
              <img src="<?php echo $offer['thumbnail']; ?>" alt="<?php echo $offer['full_header'] ?>" class="fl-img-responsive">
            </div>
            <div class="col-12 text-muted">
              <div class="row fl-offset-5">
                <div class="col-6 col-md-3 text-center">
                  <span class="fl-stat-label">Kilometer</span><br>
                  <span class="fl-stat-value"><?php echo number_format_i18n($offer['car']['mileage']); ?></span>
                </div>
                <div class="col-6 col-md-3 text-center">
                  <span class="fl-stat-label">Årgang</span><br>
                  <span class="fl-stat-value"><?php echo $car['year']; ?></span>
                </div>
                <div class="col-6 col-md-3 text-center">
                  <span class="fl-stat-label">Brændstof</span><br>
                  <span class="fl-stat-value"><?php echo $car['fuel_type']; ?></span>
                </div>
                <div class="col-6 col-md-3 text-center">
                  <span class="fl-stat-label">Km/L</span><br>
                  <span class="fl-stat-value"><?php echo number_format_i18n($car['efficiency'], 1); ?></span>
                </div>
              </div>
            </div>
            <div class="col-12 text-center fl-price-monthly-container fl-offset-5">
              <div class="fl-price-monthly">
                <span class="fl-price-monthly-value fl-detail-price-value"><?php echo number_format_i18n($offer['detail_price']); ?></span> kr.
              </div>
            </div>
            <div class="col-12">
              <hr class="fl-border-bottom">
            </div>
          </div>
        </a>
      </div>
      <?php
      }
  ?>
    </div>
    <div class="row">
      <div class="col-12" style="text-align: center;">
        <ul class="fl-pagination">
        <?php 
        for ($i = 1; $i < $num_pages + 1; $i++): ?>
          <li><a href="?page_num=<?php echo $i; ?>&make=<?php echo $make_filter; ?>&ordering=<?php echo $ordering; ?>" class="<?php echo ($i === $page ? 'active' : '') ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
        </ul>
      </div>
    </div>
  </div>
  <?php

  return ob_get_clean();
}

function fl_embed_listings_paginated_shortcode($atts = array(), $content = null, $tag = '') {

  $atts = $atts ? $atts : array();

  $api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));

  $query = array_key_exists('query', $atts) ? $atts['query'] : '';
  $limit = array_key_exists('limit', $atts) ? (int)$atts['limit'] : 24;
  $fuel_type = array_key_exists('fuel_type', $atts) ? $atts['fuel_type'] : '';
  $funding = array_key_exists('funding', $atts) ? $atts['funding'] : '';
  $price_tax = array_key_exists('tax', $atts) ? ($atts['tax'] == '1') : (get_option('findleasing-offers-tax') == 'inclusive');
  $make = array_key_exists('make', $atts) ? $atts['make'] : '';
  $models = array_key_exists('models', $atts) ? $atts['models'] : '';

  $ordering = array_key_exists('ordering', $_GET) ? $_GET['ordering'] : null;
  $make_filter = array_key_exists('make', $_GET) ? $_GET['make'] : '';
  $page = array_key_exists('page_num', $_GET) ? $_GET['page_num'] : 1;

  if (!in_array($ordering, $api->ORDERING)) {
    $ordering = get_option('findleasing-offers-ordering') ? get_option('findleasing-offers-ordering') : 'make';
  }

  if (!empty($make_filter)) {
    $query = $make_filter;
  }

  $page = (int)$page;

  $makes = $api->getAvailableMakes();

  $response = $api->getListings(array(
    'page_size' => $limit,
    'page' => $page,
    'ordering' => $ordering,
    'fuel_type' => $fuel_type,
    'funding' => $funding,
    'search' => $query,
  ));

  $_order = ltrim($ordering, '-');
  $desc = substr($ordering, 0, 1) === '-';
  $num_pages = ceil($response['count'] / $limit);

  ob_start();

  ?>

  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/findleasing.css?v=5'); ?>" type="text/css" />
  <link rel="stylesheet" href="<?php echo findleasing_plugins_url( 'assets/flbootstrap.min.css?v=1'); ?>" type="text/css" />

  <div class="fl-bs">
    <div class="row">
      <div class="col-6">
        <?php if (sizeof($makes) < 5) { ?>
          <ul class="fl-sorting-container">
          <?php 
          foreach($makes as $make) { ?>
            <li><a href="<?php echo fl_get_params('make', strtolower($make['name'])); ?>"><?php echo $make['name']; ?></a></li>
          <?php } ?>
          </ul>
        <?php } else { ?>
          <form method="get" action=".">
            <select name="make" onchange="this.form.submit()">
              <option value="" <?php $make_filter == '' ? 'selected' : ''?>>Vælg mærke</option>
              <?php foreach($makes as $make) { 
                $_make = strtolower($make['name']);
                ?>
                <option value="<?php echo $_make; ?>" <?php echo ($_make == strtolower($make_filter) ? 'selected' : '') ?>><?php echo $make['name']; ?></option>
              <?php } ?>
            </select>
          </form>
        <?php } ?>
      </div>
      <div class="col-6 text-right">
        <ul class="fl-sorting-container">
          <li class="hidden-xs text-muted">Sortering:</li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'make' && !$desc ? '-' : '') . 'make'); ?>">Mærke <?php echo $_order == 'make' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') .'"></i>' : ''; ?></a></li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'year' && $desc ? '' : '-') . 'year'); ?>">Årgang <?php echo $_order == 'year' ? '<i class="fa fa-caret-' . ($desc ? 'down' : 'up') .'"></i>' : ''; ?></a></li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'price_monthly' && !$desc ? '-' : '') . 'price_monthly'); ?>">Pris <?php echo $_order == 'price_monthly' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') .'"></i>' : ''; ?></a></li>
          <li><a href="<?php echo fl_get_params('ordering', ($_order == 'id' && $desc ? '' : '-') . 'id'); ?>">Nyeste <?php echo $_order == 'id' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') .'"></i>' : ''; ?></a></li>
        </ul>
      </div>
    </div>
    <div class="row">

  <?php
  foreach($response['results'] as $listing) {
    ?>
      <div class="<?php echo fl_get_row_classes(); ?> fl-offset-20">
        <a href="<?php echo fl_get_static_listing_url($listing); ?>" title="<?php echo $listing['full_header']; ?>">
          <div class="row">
            <div class="col-12">
              <h5 class="fl-title"><?php echo $listing['make'] . ' ' . $listing['model']; ?></h5>
              <span class="fl-sub-title"><?php echo $listing['header']; ?></span>
            </div>
            <div class="col-12">
              <img src="<?php echo $listing['thumbnail_url']; ?>" alt="<?php echo $listing['full_header'] ?>" class="fl-img-responsive">
            </div>
            <div class="col-12 text-muted">
              <div class="row fl-offset-5">
                <div class="col-6 col-md-3 text-center no-padding-right">
                  <span class="fl-stat-label">Kilometer</span><br>
                  <span class="fl-stat-value"><?php if (!empty($listing['mileage'])) { echo number_format_i18n($listing['mileage']); } ?></span>
                </div>
                <div class="col-6 col-md-3 text-center no-padding">
                  <span class="fl-stat-label">Årgang</span><br>
                  <span class="fl-stat-value"><?php if (!empty($listing['year'])) { echo $listing['year']; } ?></span>
                </div>
                <div class="col-6 col-md-3 text-center no-padding">
                  <span class="fl-stat-label">Brændstof</span><br>
                  <span class="fl-stat-value"><?php if (!empty($listing['fuel_type'])) { echo $listing['fuel_type']; } ?></span>
                </div>
                <div class="col-6 col-md-3 text-center no-padding-left">
                  <span class="fl-stat-label">Km/L</span><br>
                  <span class="fl-stat-value"><?php if (!empty($listing['efficiency'])) { echo number_format_i18n($listing['efficiency'], 1); } ?></span>
                </div>
              </div>
            </div>
            <div class="col-12 text-center fl-price-monthly-container fl-offset-5">
              <div class="fl-price-monthly">
                <?php if (!empty($listing['price_monthly'])) { ?>
                  Pr. mnd. <span class="fl-price-monthly-value"><?php echo number_format_i18n(($price_tax ? $listing['price_monthly'] : $listing['price_monthly'] * 0.8)); ?></span> kr.
                <?php } else { ?>
                  Ring for pris
                <?php } ?>
              </div>
            </div>
            <div class="col-12 leasing-info">
              <div class="row">
                <div class="col-8">Periode</div>
                <div class="col-4 text-right"><?php echo number_format_i18n($listing['leasing_time']); ?> mdr.</div>
              </div>
              <?php if (!empty($listing['kilometers'])) { ?>
              <div class="row">
                <div class="col-8">Km. pr. år</div>
                <div class="col-4 text-right"><?php echo number_format_i18n($listing['kilometers']); ?> km.</div>
              </div>
              <?php } ?>
              <div class="row">
                <div class="col-8">Udbetaling</div>
                <div class="col-4 text-right"><?php echo number_format_i18n(($price_tax ? $listing['first_pay'] : $listing['first_pay'] * 0.8)); ?> kr.</div>
              </div>
              <?php if ($listing['funding'] === 'Finansiel') { ?>
              <div class="row">
                <div class="col-6">Restværdi</div>
                <div class="col-6 text-right"><?php echo number_format_i18n((!$listing['remaining_value_tax'] ? ($listing['remaining_value'] * 0.8) : $listing['remaining_value'])); ?> kr. <?php echo (!$listing['remaining_value_tax'] ? 'ex. moms' : 'inkl. moms'); ?></div>
              </div>
              <?php } ?>
            </div>
            <div class="col-12">
              <hr class="fl-border-bottom">
            </div>
          </div>
        </a>
      </div>
      <?php
      }
  ?>
    </div>
    <div class="row">
      <div class="col-12" style="text-align: center;">
        <ul class="fl-pagination">
        <?php 
        for ($i = 1; $i < $num_pages + 1; $i++): ?>
          <li><a href="?page_num=<?php echo $i; ?>&make=<?php echo $make_filter; ?>&ordering=<?php echo $ordering; ?>" class="<?php echo ($i === $page ? 'active' : '') ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
        </ul>
      </div>
    </div>
  </div>
  <?php

  return ob_get_clean();
}

?>