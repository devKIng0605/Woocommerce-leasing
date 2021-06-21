<?php

/**
 * Template Name: FindLeasing Car Detail
 * Description: A Page Template with a darker design.
 * [FL] Leasingbiler, Offer type is offer, list
 */

if (!defined('ABSPATH')) {
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

$price_tax = get_option('findleasing-offers-tax') == 'inclusive';

$page = (int)$page;

$makes = $api->getAvailableMakes();

$response = $api->getOffers(array(
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

<div id="main-content" class="main-content fl-listing-page">

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
                            <form method="get" action="<?php echo esc_attr($action_url); ?>">
                                <select name="make" onchange="this.form.submit()">
                                    <option value="" <?php $make_filter == '' ? 'selected' : '' ?>>Vælg mærke
                                    </option>
                                    <?php foreach ($makes as $make) {
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
                            <li>
                                <a href="<?php echo fl_get_params('ordering', ($_order == 'make' && !$desc ? '-' : '') . 'make'); ?>">Mærke <?php echo $_order == 'make' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') . '"></i>' : ''; ?></a>
                            </li>
                            <li>
                                <a href="<?php echo fl_get_params('ordering', ($_order == 'year' && !$desc ? '-' : '') . 'year'); ?>">Årgang <?php echo $_order == 'year' ? '<i class="fa fa-caret-' . ($desc ? 'down' : 'up') . '"></i>' : ''; ?></a>
                            </li>
                            <li>
                                <a href="<?php echo fl_get_params('ordering', ($_order == 'price_monthly' && !$desc ? '-' : '') . 'price_monthly'); ?>">Pris <?php echo $_order == 'price_monthly' ? '<i class="fa fa-caret-' . ($desc ? 'up' : 'down') . '"></i>' : ''; ?></a>
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
                        echo apply_filters('findleasing-leasing-preview', $offer);
                    }
                    ?>
                </div>
                <div class="row">
                    <div class="col-12" style="text-align: center;">
                        <ul class="fl-pagination">
                            <?php
                            for ($i = 1; $i < $num_pages + 1; $i++) : ?>
                                <li>
                                    <a href="?page_num=<?php echo $i; ?>&make=<?php echo $make_filter; ?>&ordering=<?php echo $ordering; ?>" class="<?php echo ($i === $page ? 'active' : '') ?>"><?php echo $i; ?></a>
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

?>