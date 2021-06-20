<?php
/**
 * Template Name: FindLeasing Overview
 * Description: A Page Template with a darker design.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (get_query_var('listing_id')) {
    require_once(findleasing_theme_file('sales-detail-template.php'));
} else {
    require_once(findleasing_theme_file('sales-overview-template.php'));
}