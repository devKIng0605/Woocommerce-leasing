<?php

/**
 * Template Name: FindLeasing Car Detail
 * Description: A Page Template with a darker design.
 * [FL] Leasingbiler, Offer type is offer, single
 */

if (!defined('ABSPATH')) {
    exit;
}

$offer_id = get_query_var('listing_id');
$api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));
$price_tax = get_option('findleasing-offers-tax') == 'inclusive';
$offer = $api->getOffer($offer_id);
$car = $offer['car'];

$gallery_slider = get_option('findleasing-offers-gallery');

$image_class = '';

if ($gallery_slider == 'lightslider') {
    $image_class = 'lightslider-image';
} elseif ($gallery_slider == 'slick') {
    $image_class = 'slick-image-slider-image';
}

$title = $offer['full_title'];
$canonical_url = fl_get_leasing_listing_url($offer);

do_action('render-findleasing-header', $title, $canonical_url, $offer['thumbnail']);

?>

<div id="main-content" class="main-content fl-detail-page">
    <div id="primary" class="content-area">
        <div id="content" class="site-main" role="main">
            <div class="fl-bs">
                <div class="row">
                    <div class="col-12">
                        <h2><?php echo $offer['full_title']; ?></h2>
                    </div>
                    <div class="col-12 text-center fl-offset-15">
                        <div class="row">
                            <div class="col-9 text-center">
                                <div class="row">
                                    <div class="col-12">
                                        <ul id="fl-image-slider">
                                            <?php foreach ($offer['images'] as $image) { ?>
                                                <li>
                                                    <img src="<?php echo $image['image']; ?>" class="<?php echo $image_class; ?>" />
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row fl-details-info">
                                    <div class="info-wrapper">
                                        <div class="col-12 info-title">
                                            <h3>Info</h3>
                                        </div>
                                        <div class="col-12 info-content">
                                            <div class="row">
                                                <div class="col-6 col-md-3 text-center display-flex">
                                                    <img src="<?php echo FIND_LEASING_PLUGIN_URL;?>/assets/img/speedometer.png" alt="">
                                                    <h4>km. <?php echo number_format_i18n($car['mileage']); ?></h4>
                                                </div>
                                                <div class="col-6 col-md-3 text-center display-flex">
                                                    <img src="<?php echo FIND_LEASING_PLUGIN_URL;?>/assets/img/calendar.png" alt="">
                                                    <h5>Årgang</h5>
                                                    <h4><?php echo $car['year']; ?></h4>
                                                </div>
                                                <div class="col-6 col-md-3 text-center display-flex">
                                                    <img src="<?php echo FIND_LEASING_PLUGIN_URL;?>/assets/img/oil.png" alt="">
                                                    <h4><?php echo $car['fuel_type']; ?></h4>
                                                </div>
                                                <div class="col-6 col-md-3 text-center display-flex">
                                                    <img src="<?php echo FIND_LEASING_PLUGIN_URL;?>/assets/img/paint.png" alt="">
                                                    <h4><?php echo number_format_i18n($car['efficiency'], 1); ?></h4>
                                                    <h5>Km/L</h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 info-title">
                                            <h3>Modeldata</h3>
                                        </div>
                                        <div class="col-12 info-content">
                                            <div class="row text-muted">
                                                <div class="col-sm-6 col-12">
                                                    <table class="table">
                                                        <tbody>
                                                            <tr>
                                                                <td>HK / Nm</td>
                                                                <td class="text-left"><?php echo (!empty($car['power_in_hp']) ? $car['power_in_hp'] . ' hk' : '-'); ?>
                                                                    / <?php echo (!empty($car['torque_in_nm']) ? $car['torque_in_nm'] . ' nm' : '-'); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>0 - 100 km/t</td>
                                                                <td class="text-left">
                                                                    <?php echo !empty($car['acceleration_0_100_in_sec']) ? number_format_i18n($car['acceleration_0_100_in_sec'], 1) . ' sek' : '-'; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tophastighed</td>
                                                                <td class="text-left"><?php echo !empty($car['max_speed_in_km_h']) ? number_format_i18n($car['max_speed_in_km_h']) . ' km/t' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Km/l</td>
                                                                <td class="text-left"><?php echo !empty($car['efficiency']) ? number_format_i18n($car['efficiency'], 1) . ' km/l' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Trækhjul</td>
                                                                <td class="text-left"><?php echo !empty($car['number_of_gears']) ? $car['wheel_drive'] : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Cylindre</td>
                                                                <td class="text-left"><?php echo !empty($car['cylinders']) ? $car['cylinders'] : '-'; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-sm-6 col-12">
                                                    <table class="table">
                                                        <tbody>
                                                            <tr>
                                                                <td>Totalvægt</td>
                                                                <td class="text-left"><?php echo !empty($car['total_weight_in_kg']) ? number_format_i18n($car['total_weight_in_kg']) . ' kg' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Bredde</td>
                                                                <td class="text-left"><?php echo !empty($car['width_in_mm']) ? number_format_i18n($car['width_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Længde</td>
                                                                <td class="text-left"><?php echo !empty($car['length_in_mm']) ? number_format_i18n($car['length_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Højde</td>
                                                                <td class="text-left"><?php echo !empty($car['height_in_mm']) ? number_format_i18n($car['height_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Gear Type</td>
                                                                <td class="text-left"><?php echo !empty($car['gear_type']) ? $car['gear_type'] : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Antal Gear</td>
                                                                <td class="text-left"><?php echo !empty($car['number_of_gears']) ? $car['number_of_gears'] : '-'; ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 info-title">
                                            <h2>Beskrivelse</h2>
                                        </div>
                                        <div class="col-12 info-content">
                                            <?php echo wpautop($offer['description']); ?>

                                            <?php if ($offer['mobile_ad_id'] !== '' && $offer['mobile_ad_id'] !== null) { ?>
                                                <span class="mobilede-brand">
                                                    Bilen er fra <a href="<?php echo $offer['mobile_url']; ?>" target="_blank"><img alt="mobile.de" src="<?php echo findleasing_plugins_url('assets/mobile-logo.svg'); ?>" style="height: 16px;"></a>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3 text-center">
                                <div id="findleasing-sliders-embed-div" data-findleasing data-width="100%" data-id="<?php echo $offer_id ?>" data-color="" data-tax="<?php echo ($price_tax ? '1' : '0'); ?>"></div>
                                <script src="https://www.findleasing.nu/static/javascript/embed-sliders.js"></script>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- #content -->
    </div><!-- #primary -->
</div><!-- #main-content -->

<?php
get_footer();
