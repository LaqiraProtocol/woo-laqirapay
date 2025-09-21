<?php

namespace LaqiraPay\Http\Controllers\Frontend;

use LaqiraPay\Domain\Services\UtilityService;
use LaqiraPay\Domain\Services\LaqiraLogger;

/**
 * Enqueues frontend assets for the LaqiraPay plugin.
 */
class AssetsController
{
    private UtilityService $utilityService;

    /**
     * Initialize the controller.
     *
     * @param UtilityService|null $utilityService Optional utility service.
     */
    public function __construct(?UtilityService $utilityService = null)
    {
        $this->utilityService = $utilityService ?: new UtilityService();
    }

    /**
     * Enqueue public styles.
     *
     * @return void
     */
    public function enqueue_styles(): void
    {
        $timestamp = function_exists('current_time') ? current_time('timestamp') : time(); // Simple cache buster.
        $rtl       = $this->utilityService->detectRtl();
        $css_file  = $rtl ? 'assets/public/css/laqirapay-public-rtl.css' : 'assets/public/css/laqirapay-public.css'; // Load RTL stylesheet when needed.

        wp_enqueue_style(
            'laqirapay-public-style',
            LAQIRA_PLUGINS_URL . $css_file,
            [],
            $timestamp
        );

        $this->render('public/assets/styles', ['is_rtl' => $rtl]);

        LaqiraLogger::log(200, 'assets', 'enqueue_styles', [
            'rtl'       => $rtl,
            'css_file'  => $css_file,
        ]);
    }

    /**
     * Enqueue public scripts.
     *
     * @return void
     */
    public function enqueue_scripts(): void
    {
        $timestamp = function_exists('current_time') ? current_time('timestamp') : time(); // Sync with stylesheet timestamp.

        wp_enqueue_script(
            'laqirapay-public-script',
            LAQIRA_PLUGINS_URL . 'assets/public/js/laqirapay-first.js',
            ['jquery'],
            $timestamp,
            true
        );

        $this->render('public/assets/scripts');

        LaqiraLogger::log(200, 'assets', 'enqueue_scripts', [
            'timestamp' => $timestamp,
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        $path = LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/' . $view . '.php';
        if (file_exists($path)) {
            (function (array $data) use ($path) {
                include $path;
            })($data);
        }
    }
}
