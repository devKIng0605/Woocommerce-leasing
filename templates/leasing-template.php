<?php
/**
 * Template Name: FindLeasing Overview
 * Description: A Page Template with a darker design.
 */

if (!defined('ABSPATH')) {
    exit;
}

$list_type = get_option('findleasing-offers-type') ? get_option('findleasing-offers-type') : 'offers';

if (get_query_var('listing_id')) {
    if ($list_type == 'offers') {
        require_once(findleasing_theme_file('leasing-detail-offer-template.php'));
    } else {
        require_once(findleasing_theme_file('leasing-detail-listing-template.php'));
    }
} else {
    if ($list_type == 'offers') {
        require_once(findleasing_theme_file('offers-overview-template.php'));
    } else {
        require_once(findleasing_theme_file('listing-overview-template.php'));
    }
}