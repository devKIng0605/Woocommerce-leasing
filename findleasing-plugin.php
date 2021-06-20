<?php
/**
Plugin Name: FindLeasing Plugin
Plugin URI: https://www.findleasing.nu/
Version: 0.2.1
Author: FindLeasing ApS <ole@findleasing.dk>
Author URI: https://www.findleasing.nu/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Required constants
 */
define( 'FIND_LEASING_VERSION', '0.2.0' );
define( 'FIND_LEASING_MAIN_FILE', __FILE__ );
define( 'FIND_LEASING_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'FIND_LEASING_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

require_once('classes/findleasing-core.php');
require_once('functions.php');

class FindLeasingPlugin {

    /**
    * A reference to an instance of this class.
    */
    private static $instance;

    /**
    * The array of templates that this plugin tracks.
    */
    protected $templates;


    private $template_leasing_name = '[FL] Leasingbiler';
    private $template_sales_name = '[FL] Salgsbiler';

    /**
    * Returns an instance of this class.
    */
    public static function get_instance() {

    if ( self::$instance == null ) {
        self::$instance = new FindLeasingPlugin();
    }

    return self::$instance;

    }

    /**
    * Initializes the plugin by setting filters and administration functions.
    */
    private function __construct() {
        $this->templates = array();

        // Add a filter to the attributes metabox to inject template into the cache.
        if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

            // 4.6 and older
            add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ) );

        } else {

            // Add a filter to the wp 4.7 version attributes metabox
            add_filter( 'theme_page_templates', array( $this, 'add_new_template' ) );

        }

        // Add a filter to the save post to inject out template into the page cache
        add_filter( 'wp_insert_post_data', array( $this, 'register_project_templates' ) );


        // Add a filter to the template include to determine if the page has our
        // template assigned and return it's path
        add_filter( 'template_include', array( $this, 'view_project_template') );

        // Add your templates to this array.
        $this->templates = array(
            'templates/leasing-template.php' => $this->template_leasing_name,
            'templates/sales-template.php' => $this->template_sales_name,
        );

        add_action( 'admin_menu', array($this, 'admin_menu') );

        add_action( 'admin_init', array($this, 'admin_settings') );

        add_action( 'save_post', array( $this, 'save_post_callback'), 10, 3 );

        add_action( 'render-findleasing-header', array( $this, 'render_findleasing_header'), 10, 3 );

        add_filter( 'query_vars', array( $this, 'query_vars') );

        add_filter( 'findleasing-leasing-preview', array( 'FindLeasingPlugin', 'findleasing_leasing_preview') );

        add_action( 'init', array( $this, 'rewrites_init') );

        add_action( 'wp_enqueue_scripts', array( $this, 'include_scripts' ) );

        add_shortcode( 'findleasing-offers', array( $this, 'shortcode') );

        add_shortcode( 'findleasing-sales', array( $this, 'shortcode_sales') );

        add_shortcode( 'findleasing-offers-paginated', array( $this, 'shortcode_paginated') );

        add_shortcode( 'findleasing-listings-paginated', array( $this, 'shortcode_listings_paginated') );

        add_shortcode( 'findleasing-sales-paginated', array( $this, 'shortcode_sales_paginated') );

        add_shortcode( 'findleasing-sliders', array( $this, 'shortcode_sliders') );
    }

    public function include_scripts() {
        $gallery_slider = get_option('findleasing-offers-gallery');

        $css_dep = array();
        $js_dep = array();

        if( $gallery_slider === 'lightslider' ) {
            wp_register_style('lightslider', FIND_LEASING_PLUGIN_URL . '/assets/lightslider/css/lightslider.css', array(), FIND_LEASING_VERSION );

            wp_register_script( 'lightslider', FIND_LEASING_PLUGIN_URL . '/assets/lightslider/js/lightslider.js', array('jquery'), FIND_LEASING_VERSION, true );

            $css_dep = array('lightslider');
            $js_dep = array( 'jquery', 'lightslider' );
        } elseif ( $gallery_slider === 'slick' ){
            wp_register_style('slick', FIND_LEASING_PLUGIN_URL . '/assets/slick/slick.css', array(), FIND_LEASING_VERSION );
            wp_register_style('slick-theme', FIND_LEASING_PLUGIN_URL . '/assets/slick/slick-theme.css', array(), FIND_LEASING_VERSION );

            wp_register_script( 'slick', FIND_LEASING_PLUGIN_URL . '/assets/slick/slick.min.js', array('jquery'), FIND_LEASING_VERSION, true );

            $css_dep = array( 'slick', 'slick-theme' );
            $js_dep = array( 'jquery', 'slick' );
        }
        $css_dep[] = 'find-leasing-bootstrap';
        wp_enqueue_style('find-leasing-bootstrap', FIND_LEASING_PLUGIN_URL . '/assets/flbootstrap.min.css', array(), FIND_LEASING_VERSION );
        wp_enqueue_style('find-leasing', FIND_LEASING_PLUGIN_URL . '/assets/findleasing.css', $css_dep, FIND_LEASING_VERSION );

        wp_enqueue_script('find-leasing', FIND_LEASING_PLUGIN_URL . '/assets/findleasing.js', $js_dep, FIND_LEASING_VERSION, true );
        wp_localize_script( 'find-leasing', 'find_leasing_object',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'gallery_slider' => $gallery_slider
            )
        );
    }

    function rewrites_init() {
        $findleasing_leasing_page_name = get_option('findleasing_leasing_page_name');
        $findleasing_sales_page_name = get_option('findleasing_sales_page_name');

        if (isset($findleasing_leasing_page_name)) {
            add_rewrite_rule(
                $findleasing_leasing_page_name . '/([^/]+)/([a-z0-9]{5,}+)/?$',
                'index.php?pagename=' . $findleasing_leasing_page_name . '&listing_id=$matches[2]',
                'top'
            );
        }

        if (isset($findleasing_sales_page_name)) {
            add_rewrite_rule(
                $findleasing_sales_page_name . '/([^/]+)/([a-z0-9]{5,}+)/?$',
                'index.php?pagename=' . $findleasing_sales_page_name . '&listing_id=$matches[2]',
                'top'
            );
        }
    }

    public static function findleasing_leasing_preview($offer) {
        return fl_embed_leasing_car_preview($offer);
    }

    function shortcode( $atts = [], $content = null, $tag = '' ) {
        return fl_embed_shortcode( $atts, $content, $tag );
    }

    function shortcode_sales( $atts = [], $content = null, $tag = '' ) {
        return fl_embed_sales_shortcode( $atts, $content, $tag );
    }

    function shortcode_paginated( $atts = [], $content = null, $tag = '' ) {
        return fl_embed_paginated_shortcode( $atts, $content, $tag );
    }

    function shortcode_listings_paginated( $atts = [], $content = null, $tag = '' ) {
        return fl_embed_listings_paginated_shortcode( $atts, $content, $tag );
    }

    function shortcode_sales_paginated( $atts = [], $content = null, $tag = '' ) {
        return fl_embed_sales_paginated_shortcode( $atts, $content, $tag );
    }

    function shortcode_sliders( $atts = [], $content = null, $tag = '' ) {
        return fl_embed_sliders( $atts, $content, $tag );
    }

    function query_vars( $query_vars ){
        $query_vars[] = 'listing_id';
        return $query_vars;
    }

    function render_findleasing_header( $title, $url, $thumbnail ) {
        /* only "reliable" way of altering the title for all wordpress themes
         * since most theme authors dont seem to care about what standards are .... */

        function _findleasing_hook_header() {
            global $url;
            global $thumbnail;
            ?>
            <meta property="og:image" content="<?php echo $thumbnail; ?>">
            <meta property="og:url" content="<?php echo $url; ?>">
            <?php
        }
        add_action( 'wp_head', '_findleasing_hook_header' );

        ob_start();
        get_header();
        $header = ob_get_contents();
        ob_end_clean();

        $header = preg_replace('/<title[^>]*>.*?<\/title>/i', '<title>' . htmlspecialchars($title) . '</title>', $header);
        $header = preg_replace('/<meta property="og:url"[^>]*>/i', '<meta property="og:url" content="' . $url . '">', $header);
        $header = preg_replace('/<link rel="canonical"[^>]*>/i', '<link rel="canonical" href="' . $url . '">', $header);

        echo $header;
    }

    function admin_menu() {

        add_submenu_page('options-general.php', 'FindLeasing Indstillinger', 'FindLeasing', 'manage_options', 'findleasing-settings', array($this, 'settings_page'));
        /*
        add_options_page( 'FindLeasing', 'FindLeasing', 'manage_options', 'findleasing-settings', array( $this, 'settings_page' ) );
        */
    }

    function settings_page() {
        ?>
        <div class="wrap">
            <h1>FindLeasing Indstillinger<h1>

            <form method="post" action="options.php">
            <?php
            settings_fields('findleasing_offers_config');
            do_settings_sections('findleasing-offers');
            submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    function admin_settings() {
        add_settings_section('findleasing_offers_config', '', null, 'findleasing-offers');

        add_settings_field('findleasing-offers-api-key', 'FindLeasing API Key', array($this, 'options_display'), 'findleasing-offers', 'findleasing_offers_config',
            array('name' => 'findleasing-offers-api-key', 'option' => 'findleasing-offers-api-key'));
        register_setting('findleasing_offers_config', 'findleasing-offers-api-key');

        add_settings_field('findleasing-offers-ordering', 'Standard Sortering', array($this, 'ordering_settings_display'), 'findleasing-offers', 'findleasing_offers_config',
            array('name' => 'findleasing-offers-ordering', 'option' => 'findleasing-offers-ordering'));
        register_setting('findleasing_offers_config', 'findleasing-offers-ordering', array('default' => 'make'));

        add_settings_field('findleasing-offers-tax', 'Priser på liste', array($this, 'tax_settings_display'), 'findleasing-offers', 'findleasing_offers_config',
            array('name' => 'findleasing-offers-tax', 'option' => 'findleasing-offers-tax'));
        register_setting('findleasing_offers_config', 'findleasing-offers-tax', array('default' => 'exclusive'));

        add_settings_field('findleasing-offers-row', 'Antal biler per linje (max)', array($this, 'row_settings_display'), 'findleasing-offers', 'findleasing_offers_config',
            array('name' => 'findleasing-offers-row', 'option' => 'findleasing-offers-row'));
        register_setting('findleasing_offers_config', 'findleasing-offers-row', array('default' => '3'));

        add_settings_field('findleasing-offers-gallery', 'Gallerislider', array($this, 'gallery_settings_display'), 'findleasing-offers', 'findleasing_offers_config',
            array('name' => 'findleasing-offers-gallery', 'option' => 'findleasing-offers-gallery'));
        register_setting('findleasing_offers_config', 'findleasing-offers-gallery', array('default' => 'lightslider'));

        add_settings_field('findleasing-offers-theme', 'Tema', array($this, 'theme_settings_display'), 'findleasing-offers', 'findleasing_offers_config',
            array('name' => 'findleasing-offers-theme', 'option' => 'findleasing-offers-theme'));
        register_setting('findleasing_offers_config', 'findleasing-offers-theme', array('default' => 'default'));

        add_settings_field('findleasing-offers-type', 'Annoncetype', array($this, 'type_settings_display'), 'findleasing-offers', 'findleasing_offers_config',
            array('name' => 'findleasing-offers-type', 'option' => 'findleasing-offers-type'));
        register_setting('findleasing_offers_config', 'findleasing-offers-type', array('default' => 'offers'));
    }

    function options_display($args) {
        ?>
        <input style="min-width: 240px;" type="text" class="regular-text ltr" name="<?php echo $args['name'] ?>" value="<?php
        echo stripslashes_deep(esc_attr(get_option($args['option']))); ?>" />
        <?php
        if (array_key_exists('description', $args)) { ?>
            <p class="description"><?php echo $args['description']; ?></p>
            <?php
        }
    }

    function ordering_settings_display($args) {
        ?>
        <select name="<?php echo $args['name'] ?>">
          <option value="make" <?php selected(get_option('findleasing-offers-ordering'), "make"); ?>>Mærke</option>
          <option value="-id" <?php selected(get_option('findleasing-offers-ordering'), "-id"); ?>>Nyeste</option>
        </select>
        <?php
    }

    function tax_settings_display($args) {
        ?>
        <select name="<?php echo $args['name'] ?>">
          <option value="exclusive" <?php selected(get_option('findleasing-offers-tax'), "exclusive"); ?>>Eksklusiv moms</option>
          <option value="inclusive" <?php selected(get_option('findleasing-offers-tax'), "inclusive"); ?>>Inklusiv moms</option>
        </select>
        <?php
    }

    function row_settings_display($args) {
        ?>
        <select name="<?php echo $args['name'] ?>">
          <option value="3" <?php selected(get_option('findleasing-offers-row'), "3"); ?>>3</option>
          <option value="4" <?php selected(get_option('findleasing-offers-row'), "4"); ?>>4</option>
        </select>
        <?php
    }

    function gallery_settings_display($args) {
        ?>
        <select name="<?php echo $args['name'] ?>">
          <option value="lightslider" <?php selected(get_option('findleasing-offers-gallery'), "lightslider"); ?>>lightSlider</option>
          <option value="slick" <?php selected(get_option('findleasing-offers-gallery'), "slick"); ?>>slick</option>
        </select>
        <?php
    }

    function theme_settings_display($args) {

        $themes_folder = findleasing_plugins_dir() . 'themes/';
        $directories = scandir($themes_folder);
        $themes = array();

        foreach ($directories as $key => $value)
        {
            if (!in_array($value, array('.', '..'))) {
                $dir = $themes_folder . '/' . $value;
                if (is_dir($dir)) {
                    $theme_file = $dir . '/theme.json';
                    if (is_file($theme_file)) {
                        $cfg = json_decode(file_get_contents($theme_file), true);

                        array_push($themes, array("name" => $cfg['name'], "theme" => $value));
                    }
                }
            }
        }
        ?>
        <select name="<?php echo $args['name'] ?>">
            <?php foreach ($themes as $key => $theme) ?>
            <option value="<?php echo $theme['theme']; ?>" <?php selected(get_option('findleasing-offers-theme'), $theme['theme']); ?>><?php echo $theme['name']; ?></option>
        </select>
        <?php
    }

    function type_settings_display($args) {
        ?>
        <select name="<?php echo $args['name'] ?>">
            <option value="offers" <?php selected(get_option('findleasing-offers-type'), "offers"); ?>>Dynamiske</option>
            <option value="listings" <?php selected(get_option('findleasing-offers-type'), "listings"); ?>>Statiske</option>
        </select>
        <?php
    }

    function save_post_callback( $post_id, $post, $update ) {

        $template = get_post_meta($post_id, '_wp_page_template', true);
        $template_name = array_key_exists($template, $this->templates) ? $this->templates[$template] : null;

        // If no custom post template is given, just exit the function
        if ( ! isset( $template_name ) ) {
            return;
        }

        //remove_action( 'save_post', array( $this, 'save_post_callback'), 10, 3 );

        if ($template_name == $this->template_leasing_name) {
            update_option('findleasing_leasing_page_name', $post->post_name);
        } elseif ($template_name == $this->template_sales_name) {
            update_option('findleasing_sales_page_name', $post->post_name);
        }

        // update the post, which calls save_post again
        //wp_update_post( array( 'ID' => $post_id, 'post_content' => $content ) );

        // re-hook this function
        //add_action( 'save_post', array( $this, 'save_post_callback'), 10, 3 );

        $this->rewrites_init();
        flush_rewrite_rules();
    }

    /**
    * Adds our template to the page dropdown for v4.7+
    *
    */
    public function add_new_template( $posts_templates ) {
        $posts_templates = array_merge( $posts_templates, $this->templates );
        return $posts_templates;
    }

    /**
    * Adds our template to the pages cache in order to trick WordPress
    * into thinking the template file exists where it doens't really exist.
    */
    public function register_project_templates( $atts ) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list.
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;
    }

    /**
    * Checks if the template is assigned to the page
    */
    public function view_project_template( $template ) {
        // Return the search template if we're searching (instead of the template for the first result)
        if ( is_search() ) {
            return $template;
        }

        // Get global post
        global $post;

        // Return template if post is empty
        if ( ! $post ) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if ( ! isset( $this->templates[get_post_meta(
            $post->ID, '_wp_page_template', true
        )] ) ) {
            return $template;
        }

        // Allows filtering of file path
        $filepath = apply_filters( 'page_templater_plugin_dir_path', plugin_dir_path( __FILE__ ) );

        $file =  $filepath . get_post_meta(
            $post->ID, '_wp_page_template', true
        );

        // Just to be safe, we check if the file exist first
        if ( file_exists( $file ) ) {
            return $file;
        }

        // Return template
        return $template;

    }

}
add_action( 'plugins_loaded', array( 'FindLeasingPlugin', 'get_instance' ) );
