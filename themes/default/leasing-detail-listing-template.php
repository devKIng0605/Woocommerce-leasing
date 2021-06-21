<?php
/**
 * Template Name: FindLeasing Car Detail
 * Description: A Page Template with a darker design.
 * [FL] Leasingbiler, Offer type is listings, single
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$listing_id = get_query_var('listing_id');
$api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));
$price_tax = get_option('findleasing-offers-tax') == 'inclusive';
$listing = $api->getListing($listing_id);

$gallery_slider = get_option('findleasing-offers-gallery');

$image_class = '';

if ($gallery_slider == 'lightslider') {
    $image_class = 'lightslider-image';
} elseif ($gallery_slider == 'slick') {
    $image_class = 'slick-image-slider-image';
}

$title = $listing['full_header'];
$canonical_url = fl_get_static_listing_url($listing);

do_action('render-findleasing-header', $title, $canonical_url, $listing['thumbnail_url']);

?>

    <div id="main-content" class="main-content fl-detail-page">
        <div id="primary" class="content-area">
            <div id="content" class="site-main" role="main">
                <div class="fl-bs">
                    <div class="row">
                        <div class="col-12 fl-detail-title">
                            <h2><?php echo $listing['full_header']; ?></h2>
                        </div>
                        <div class="col-12 fl-offset-15">
                            <div class="row">
                                <div class="col-8 text-center">
                                    <div class="row">
                                        <div class="col-12">
                                            <ul id="fl-image-slider">
                                                <?php foreach ( $listing['images'] as $image ) { ?>
                                                    <li>
                                                        <img src="<?php echo esc_attr($image['image']); ?>" class="<?php echo esc_attr($image_class); ?>"/>
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
                                            <div class="row info-content">
                                                <div class="col-6 col-md-3 text-center display-flex">
                                                    <img src="<?php echo FIND_LEASING_PLUGIN_URL;?>/assets/img/speedometer.png" alt="">
                                                    <h4>km. <?php if (!empty($listing['mileage'])) {
                                                            echo number_format_i18n($listing['mileage']);
                                                        } ?></h4>
                                                </div>
                                                <div class="col-6 col-md-3 text-center display-flex">
                                                    <img src="<?php echo FIND_LEASING_PLUGIN_URL;?>/assets/img/calendar.png" alt="">
                                                    <h5>Årg.</h5>
                                                    <h4><?php if (!empty($listing['year'])) {
                                                            echo $listing['year'];
                                                        } ?></h4>
                                                </div>
                                                <div class="col-6 col-md-3 text-center display-flex">
                                                    <!-- <h5>Brændstof</h5> -->
                                                    <img src="<?php echo FIND_LEASING_PLUGIN_URL;?>/assets/img/oil.png" alt="">
                                                    <h4><?php if (!empty($listing['fuel_type'])) {
                                                            echo $listing['fuel_type'];
                                                        } ?></h4>
                                                </div>
                                                <div class="col-6 col-md-3 text-center display-flex">
                                                    <img src="<?php echo FIND_LEASING_PLUGIN_URL;?>/assets/img/paint.png" alt="">
                                                    <h4><?php if (!empty($listing['efficiency'])) {
                                                            echo number_format_i18n($listing['efficiency'], 1);
                                                        } ?></h4>
                                                    <h5>Km/L</h5>
                                                </div>
                                            </div>
                                            <div class="col-12 info-title">
                                                <h3>Specifikationer</h3>
                                            </div>
                                            <div class="col-12 info-content">
                                                <div class="row text-muted">
                                                    <div class="col-sm-6 col-12">
                                                        <table class="table">
                                                            <tbody>
                                                            <tr>
                                                                <td>HK / Nm</td>
                                                                <td class="text-left"><?php echo(!empty($listing['power_in_hp']) ? $listing['power_in_hp'] . ' hk' : '-'); ?>
                                                                    / <?php echo(!empty($listing['torque_in_nm']) ? $listing['torque_in_nm'] . ' nm' : '-'); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>0 - 100 km/t</td>
                                                                <td class="text-left">
                                                                    <?php echo !empty($listing['acceleration_0_100_in_sec']) ? number_format_i18n($listing['acceleration_0_100_in_sec'], 1) . ' sek' : '-'; ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Tophastighed</td>
                                                                <td class="text-left"><?php echo !empty($listing['max_speed_in_km_h']) ? number_format_i18n($listing['max_speed_in_km_h']) . ' km/t' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Km/l</td>
                                                                <td class="text-left"><?php echo !empty($listing['efficiency']) ? number_format_i18n($listing['efficiency'], 1) . ' km/l' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Trækhjul</td>
                                                                <td class="text-left"><?php echo !empty($listing['number_of_gears']) ? $listing['wheel_drive'] : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Cylindre</td>
                                                                <td class="text-left"><?php echo !empty($listing['cylinders']) ? $listing['cylinders'] : '-'; ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-sm-6 col-12">
                                                        <table class="table">
                                                            <tbody>
                                                            <tr>
                                                                <td>Totalvægt</td>
                                                                <td class="text-left"><?php echo !empty($listing['total_weight_in_kg']) ? number_format_i18n($listing['total_weight_in_kg']) . ' kg' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Bredde</td>
                                                                <td class="text-left"><?php echo !empty($listing['width_in_mm']) ? number_format_i18n($listing['width_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Længde</td>
                                                                <td class="text-left"><?php echo !empty($listing['length_in_mm']) ? number_format_i18n($listing['length_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Højde</td>
                                                                <td class="text-left"><?php echo !empty($listing['height_in_mm']) ? number_format_i18n($listing['height_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Gear Type</td>
                                                                <td class="text-left"><?php echo !empty($listing['gear_type']) ? $listing['gear_type'] : '-'; ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Antal Gear</td>
                                                                <td class="text-left"><?php echo !empty($listing['number_of_gears']) ? $listing['number_of_gears'] : '-'; ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 info-title">
                                                <h2>Om MB Group</h2>
                                            </div>
                                            <div class="col-12 info-content text-left">
                                                <?php echo wpautop($listing['description']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="row">
                                        <div class="col-12 text-center">
                                            <div class="ownership-buttons">
                                                <button class="ownership-button btn <?php echo $price_tax ? ' active' : ''; ?>"
                                                        value="private-tab">Privat
                                                </button>
                                                <button class="ownership-button btn <?php echo !$price_tax ? ' active' : ''; ?>"
                                                        value="business-tab">Erhverv
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row fl-offset-15">
                                        <div class="col-12">
                                            <?php for ($i = 0; $i < 2; $i++) { ?>
                                                <div class="leasing-tab <?php echo ( $price_tax && $i === 1 || !$price_tax && $i === 0 ) ? 'hidden' : ''; ?>"
                                                     id="<?php echo $i === 0 ? 'private-tab' : 'business-tab'; ?>">
                                                    <div class="row">
                                                        <div class="col-12 order-sm-last col-md-5 col-lg-4 xs-top20 d-none d-flex flex-column">
                                                            <div class="row flex-grow-1">
                                                                <div class="col-12 monthly-pay-wrapper d-flex align-items-center">
                                                                    <div class="monthly-pay-container xs-top15 xs-bot15">
                                                                        <?php echo( $i === 0 ? 'Privat inkl. moms' : 'Erherv ex. moms' ); ?>
                                                                        <h3 class="price-monthly-header title" style="margin: 0">
                                                                            <?php if ( ! empty($listing['price_monthly']) ) { ?>
                                                                                Pr. mnd.
                                                                                <strong><?php echo number_format_i18n( ( $i === 0 ? $listing['price_monthly'] : $listing['price_monthly'] * 0.8 ) ); ?>
                                                                                    kr.</strong>
                                                                            <?php } else { ?>
                                                                                Ring for pris
                                                                            <?php } ?>
                                                                        </h3>
                                                                        <?php if ( $i === 1 && ! empty($listing['tax_value']) && isset($listing['tax_value']) ) { ?>
                                                                            <h5>
                                                                                Beskatningsværdi: <?php echo number_format_i18n($listing['tax_value']); ?>
                                                                                kr.</h5>
                                                                        <?php } ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-7 order-sm-first col-lg-8">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="row slider-row">
                                                                        <div class="col-6 slider-label">
                                                                            Periode
                                                                        </div>
                                                                        <div class="col-6 slider-value-field text-right">
                                                                            <?php echo $listing['leasing_time']; ?> mdr.
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <div class="slider"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php if (!empty($listing['kilometers'])) { ?>
                                                                    <div class="col-12 fl-offset-7">
                                                                        <div class="row slider-row">
                                                                            <div class="col-8 slider-label xs-right0">
                                                                                Kilometer pr. år
                                                                            </div>
                                                                            <div class="col-4 xs-left0 slider-value-field text-right">
                                                                                <?php echo number_format_i18n($listing['kilometers']); ?>
                                                                                km.
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <div class="slider"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php } ?>
                                                                <div class="col-12 fl-offset-7">
                                                                    <div class="row slider-row">
                                                                        <div class="col-8 slider-label xs-right0">
                                                                            Udbetaling
                                                                        </div>
                                                                        <div class="col-4 xs-left0 slider-value-field text-right">
                                                                            <?php echo ! empty($listing['first_pay']) ? (number_format_i18n( ( $i === 0 ? $listing['first_pay'] : $listing['first_pay'] * 0.8 ) ) ) : '-'; ?>
                                                                            kr.
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <div class="slider"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php if ($listing['funding'] === 'Finansiel') { ?>
                                                                    <div class="col-12 fl-offset-7">
                                                                        <div class="row slider-row">
                                                                            <div class="col-8 slider-label xs-right0">
                                                                                Restværdi <?php echo(!$listing['remaining_value_tax'] ? 'ex. moms' : 'inkl. moms'); ?>
                                                                            </div>
                                                                            <div class="col-4 xs-left0 slider-value-field text-right">
                                                                                <?php echo number_format_i18n( ( ! $listing['remaining_value_tax'] ? ( $listing['remaining_value'] * 0.8 ) : $listing['remaining_value'] ) ); ?>
                                                                                kr.
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <div class="slider"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="row fl-offset-15">
                                        <div class="col-12 text-center">
                                            <h3>Udstyr</h3>
                                        </div>
                                        <div class="col-12 fl-offset-15">
                                            <ul class="fl-equipments">
                                                <?php
                                                $equipments = explode( ',', $listing['description'] );
                                                foreach( $equipments as $equipment ) {
                                                    $equipment = trim($equipment);
                                                    echo '<li>' . $equipment . '</li>';
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
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