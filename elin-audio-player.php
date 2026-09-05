<?php
/**
 * Plugin Name: Elin Audio Player
 * Description: Custom Elementor Podcast Audio Player
 * Version: 1.4.0
 * Author: Elin
 * Text Domain: elin-audio-player
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires Plugins: elementor
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('ELIN_AUDIO_VERSION', '1.4.0');
define('ELIN_AUDIO_URL', plugin_dir_url(__FILE__));
define('ELIN_AUDIO_PATH', plugin_dir_path(__FILE__));

define('ELIN_AUDIO_MIN_ELEMENTOR_VERSION', '3.5.0');

function elin_audio_load_textdomain(){

    load_plugin_textdomain(
        'elin-audio-player',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}

add_action(
    'init',
    'elin_audio_load_textdomain'
);

/**
 * Bail out with an admin notice when Elementor is missing or too old,
 * instead of registering a widget against a class that does not exist.
 */
function elin_audio_check_requirements(){

    if ( ! did_action('elementor/loaded') ) {

        add_action('admin_notices', function(){
            elin_audio_admin_notice(
                __('«Elin Audio Player» برای کار کردن به افزونه Elementor نیاز دارد.', 'elin-audio-player')
            );
        });

        return false;
    }

    if ( ! version_compare(ELEMENTOR_VERSION, ELIN_AUDIO_MIN_ELEMENTOR_VERSION, '>=') ) {

        add_action('admin_notices', function(){
            elin_audio_admin_notice(
                sprintf(
                    /* translators: %s: minimum required Elementor version */
                    __('«Elin Audio Player» به Elementor نسخه %s یا بالاتر نیاز دارد.', 'elin-audio-player'),
                    ELIN_AUDIO_MIN_ELEMENTOR_VERSION
                )
            );
        });

        return false;
    }

    return true;
}

function elin_audio_admin_notice($message){

    printf(
        '<div class="notice notice-warning"><p>%s</p></div>',
        esc_html($message)
    );
}

function elin_audio_init(){

    if ( ! elin_audio_check_requirements() ) return;

    add_action(
        'elementor/widgets/register',
        'elin_audio_register_widget'
    );

    add_action(
        'wp_enqueue_scripts',
        'elin_audio_enqueue_assets'
    );

    /**
     * Inside the editor preview iframe the widget is injected over AJAX, so
     * get_script_depends() never gets a chance to print its assets. Enqueue
     * them up front there instead of only registering them.
     */
    add_action(
        'elementor/preview/enqueue_scripts',
        'elin_audio_enqueue_preview_assets'
    );
}

function elin_audio_enqueue_preview_assets(){

    elin_audio_enqueue_assets();

    wp_enqueue_style('elin-audio-style');
    wp_enqueue_script('wavesurfer');
    wp_enqueue_script('elin-audio-script');
}

add_action(
    'plugins_loaded',
    'elin_audio_init'
);

function elin_audio_register_widget($widgets_manager){

    require_once ELIN_AUDIO_PATH . 'widgets/elin-audio-player-widget.php';

    $widgets_manager->register(
        new \ElinAudioPlayerWidget()
    );
}

function elin_audio_enqueue_assets(){

    wp_register_style(
        'elin-audio-style',
        ELIN_AUDIO_URL . 'assets/css/player.css',
        [],
        ELIN_AUDIO_VERSION
    );

    wp_register_script(
        'wavesurfer',
        ELIN_AUDIO_URL . 'assets/js/wavesurfer.min.js',
        [],
        '7.8.4',
        true
    );

    wp_register_script(
        'elin-audio-script',
        ELIN_AUDIO_URL . 'assets/js/player.js',
        ['jquery', 'wavesurfer'],
        ELIN_AUDIO_VERSION,
        true
    );
}
