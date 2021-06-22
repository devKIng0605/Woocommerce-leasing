<?php
/**
 * Template Name: FindLeasing Car Detail
 * Description: A Page Template with a darker design.
 * [FL] Salgsbiler, list
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_size = 24;

$api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));

$ordering = array_key_exists('ordering', $_GET) ? $_GET['ordering'] : null;
$make_filter = array_key_exists('make', $_GET) ? $_GET['make'] : '';
$page = array_key_exists('page_num', $_GET) ? $_GET['page_num'] : 1;

if (!in_array($ordering, $api->ORDERING)) {
    $ordering = get_option('findleasing-offers-ordering') ? get_option('findleasing-offers-ordering') : 'make';
}

$page = (int)$page;

$makes = $api->getAvailableSaleMakes();

$response = $api->getSaleOffers(array(
    'show_in_iframe' => 'true',
    'page_size' => $page_size,
    'page' => $page,
    'ordering' => $ordering,
    'search' => $make_filter,
));

$_order = ltrim($ordering, '-');
$desc = substr($ordering, 0, 1) === '-';
$num_pages = ceil($response['count'] / $page_size);

get_header();

global $wp;
$action_url = home_url($wp->request);

?>

    <div id="main-content" class="main-content fl-listing-page fl-sale-page">

        <div id="primary" class="content-area">
            <div id="content" class="site-main" role="main">

                <?php get_template_part('template-parts/content', 'page'); ?>

                <div class="fl-bs">
                    <div class="row">
                        <div class="col-6">
                            <?php if (sizeof($makes) < 5) { ?>
                                <ul class="fl-sorting-container">
                                    <?php
                                    foreach ($makes as $make) { ?>
                                        <li>
                                            <a href="<?php echo fl_get_params('make', strtolower($make['name'])); ?>"><?php echo $make['name']; ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } else { ?>
                                <form method="get" action="<?php echo $action_url; ?>">
                                    <select name="make" onchange="this.form.submit()">
                                        <option value="" <?php $make_filter == '' ? 'selected' : '' ?>>Vælg mærke
                                        </option>
                                        <?php foreach ($makes as $make) {
                                            $_make = strtolower($make['name']);
                                            ?>
                                            <option value="<?php echo $_make; ?>" <?php echo($_make == strtolower($make_filter) ? 'selected' : '') ?>><?php echo $make['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </form>
                            <?php } ?>
                        </div>
                        <div class="col-6 text-right">
                            <ul class="fl-sorting-container">
                                <li class="hidden-xs text-muted">Sortering:</li>
                                <li>
                                    <a href="<?php echo fl_get_params('ordering', ($_order == 'make' && !$desc ? '-' : '') . 'make'); ?>">Mærke <?php echo $_order == 'make' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') . '"></i>' : ''; ?></a>
                                </li>
                                <li>
                                    <a href="<?php echo fl_get_params('ordering', ($_order == 'year' && !$desc ? '-' : '') . 'year'); ?>">Årgang <?php echo $_order == 'year' ? '<i class="fa fa-caret-' . ($desc ? 'down' : 'up') . '"></i>' : ''; ?></a>
                                </li>
                                <li>
                                    <a href="<?php echo fl_get_params('ordering', ($_order == 'id' && !$desc ? '-' : '') . 'id'); ?>">Nyeste <?php echo $_order == 'id' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') . '"></i>' : ''; ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        foreach ($response['results'] as $offer) {
                            $car = $offer['car'];
                            ?>
                            <div class="<?php echo esc_attr(fl_get_row_classes()); ?> fl-offset-20 car-item">
                                
                                    <div class="row">
                                        <div class="col-12">
                                            <img src="<?php echo esc_attr($offer['thumbnail']); ?>"
                                                 alt="<?php echo esc_attr($offer['full_title']) ?>"
                                                 class="fl-img-responsive">
                                        </div>
                                        <div class="col-12 car-title">
                                            <h5 class="fl-title"><?php echo $car['make'] . ' ' . $car['model']; ?></h5>
                                            <span class="fl-sub-title"><?php echo $offer['title']; ?></span>
                                        </div>
                                        <div class="col-12 leasing-info">
                                            <div class="row">
                                                <h5 class="col-6">Pr. mnd.</h5>
                                                <div class="col-6 text-right fl-price">
                                                    <span class="fl-price-monthly-value fl-detail-price-value"><?php echo number_format_i18n($offer['detail_price']); ?></span>
                                                    kr.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 text-center fl-price-monthly-container fl-offset-5 hide">
                                            <div class="fl-price-monthly">
                                                <span class="fl-price-monthly-value fl-detail-price-value"><?php echo number_format_i18n($offer['detail_price']); ?></span>
                                                kr.
                                            </div>
                                        </div>
                                        <div class="col-12 text-muted">
                                            <div class="row fl-offset-5" style="margin-left: -15px; margin-right: -15px">
                                                 <div class="col-4 text-center no-padding">
                                                    <span class="fl-stat-label">Årg. </span><br>
                                                    <span class="fl-stat-value"><?php echo $car['year']; ?></span>
                                                </div>
                                                <div class="col-4 text-center no-padding">
                                                    <span class="fl-stat-label">Km. </span><br>
                                                    <span class="fl-stat-value"><?php echo number_format_i18n($offer['car']['mileage']); ?></span>
                                                </div>
                                                <div class="col-4 text-center no-padding">
                                                <a href="<?php echo esc_attr(fl_get_sales_listing_url($offer)); ?>" title="<?php echo esc_attr($offer['full_title']); ?>"> Se detaljer</a>
                                                </div>
                                                <div class="col-6 col-md-3 text-center hide">
                                                    <span class="fl-stat-label">Brændstof</span><br>
                                                    <span class="fl-stat-value"><?php echo $car['fuel_type']; ?></span>
                                                </div>
                                                <div class="col-6 col-md-3 text-center hide">
                                                    <span class="fl-stat-label">Km/L</span><br>
                                                    <span class="fl-stat-value"><?php echo number_format_i18n($car['efficiency'], 1); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                    <li>
                                        <a href="?page_num=<?php echo $i; ?>&make=<?php echo $make_filter; ?>&ordering=<?php echo $ordering; ?>"
                                           class="<?php echo($i === $page ? 'active' : '') ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>
                </div> <!-- .fl-bs -->
            </div><!-- #content -->
        </div><!-- #primary -->
    </div><!-- #main-content -->

<?php
get_footer();