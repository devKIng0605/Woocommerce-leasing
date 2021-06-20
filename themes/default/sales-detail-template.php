<?php
/**
 * Template Name: FindLeasing Car Detail
 * Description: A Page Template with a darker design.
 * [FL] Salgsbiler, single
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$offer_id = get_query_var('listing_id');
$api = new FindLeasingAPI(get_option('findleasing-offers-api-key'));
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
                        <div class="col-12 text-center">
                            <h2><?php echo $offer['full_title']; ?></h2>
                        </div>
                        <div class="col-12 text-center fl-offset-15">
                            <ul id="fl-image-slider">
                                <?php foreach ($offer['images'] as $image) { ?>
                                    <li>
                                        <img src="<?php echo esc_attr($image['image']); ?>"
                                             class="<?php echo esc_attr($image_class); ?>"/>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6 col-md-3 text-center">
                                    <h5>Kilometer</h5>
                                    <h4><?php echo number_format_i18n($car['mileage']); ?> km.</h4>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <h5>Årgang</h5>
                                    <h4><?php echo $car['year']; ?></h4>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <h5>Brændstof</h5>
                                    <h4><?php echo $car['fuel_type']; ?></h4>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <h5>Km/L</h5>
                                    <h4><?php echo number_format_i18n($car['efficiency'], 1); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 fl-offset-15">
                            <div class="fl-detail-price">
                                <div class="">Detailpris</div>
                                <div class="fl-price"><?php echo number_format_i18n($offer['detail_price']); ?>kr.
                                </div>
                                <div class=""><?php echo $offer['detail_price_with_tax'] ? 'inkl.' : 'ekskl.'; ?>moms
                                </div>
                            </div>
                        </div>
                        <div class="col-12 fl-offset-15">
                            <h3>Beskrivelse</h3>
                        </div>
                        <div class="col-12">
                            <?php echo wpautop($offer['description']); ?>
                        </div>
                        <div class="col-12">
                            <h3>Modeldata</h3>
                        </div>
                        <div class="col-12">
                            <div class="row text-muted">
                                <div class="col-sm-6 col-12">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <td>HK / Nm</td>
                                            <td class="text-right"><?php echo(!empty($car['power_in_hp']) ? $car['power_in_hp'] . ' hk' : '-'); ?>
                                                / <?php echo(!empty($car['torque_in_nm']) ? $car['torque_in_nm'] . ' nm' : '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <td>0 - 100 km/t</td>
                                            <td class="text-right">
                                                <?php echo !empty($car['acceleration_0_100_in_sec']) ? number_format_i18n($car['acceleration_0_100_in_sec'], 1) . ' sek' : '-'; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Tophastighed</td>
                                            <td class="text-right"><?php echo !empty($car['max_speed_in_km_h']) ? number_format_i18n($car['max_speed_in_km_h']) . ' km/t' : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Km/l</td>
                                            <td class="text-right"><?php echo !empty($car['efficiency']) ? number_format_i18n($car['efficiency'], 1) . ' km/l' : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Trækhjul</td>
                                            <td class="text-right"><?php echo !empty($car['number_of_gears']) ? $car['wheel_drive'] : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Cylindre</td>
                                            <td class="text-right"><?php echo !empty($car['cylinders']) ? $car['cylinders'] : '-'; ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <td>Totalvægt</td>
                                            <td class="text-right"><?php echo !empty($car['total_weight_in_kg']) ? number_format_i18n($car['total_weight_in_kg']) . ' kg' : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Bredde</td>
                                            <td class="text-right"><?php echo !empty($car['width_in_mm']) ? number_format_i18n($car['width_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Længde</td>
                                            <td class="text-right"><?php echo !empty($car['length_in_mm']) ? number_format_i18n($car['length_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Højde</td>
                                            <td class="text-right"><?php echo !empty($car['height_in_mm']) ? number_format_i18n($car['height_in_mm'] / 10) . ' cm' : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Gear Type</td>
                                            <td class="text-right"><?php echo !empty($car['gear_type']) ? $car['gear_type'] : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Antal Gear</td>
                                            <td class="text-right"><?php echo !empty($car['number_of_gears']) ? $car['number_of_gears'] : '-'; ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
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