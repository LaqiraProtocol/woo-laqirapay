<?php

namespace LaqiraPay\Http\Controllers\Frontend;

use LaqiraPay\Domain\Services\UtilityService;
use LaqiraPay\Domain\Services\LaqiraLogger;

/**
 * Enqueues frontend assets for the LaqiraPay plugin.
 */
class AssetsController {

	private UtilityService $utilityService;

	/**
	 * Allowed frontend asset view templates.
	 *
	 * @var array<string,array{path:string,allowed_keys:string[]}>
	 */
	private const VIEW_CONFIG = array(
		'public/assets/styles'  => array(
			'path'         => 'public/assets/styles',
			'allowed_keys' => array( 'is_rtl' ),
		),
		'public/assets/scripts' => array(
			'path'         => 'public/assets/scripts',
			'allowed_keys' => array(),
		),
	);

	/**
	 * Initialize the controller.
	 *
	 * @param UtilityService|null $utilityService Optional utility service.
	 */
	public function __construct( ?UtilityService $utilityService = null ) {
		$this->utilityService = $utilityService ?: new UtilityService();
	}

	/**
	 * Enqueue public styles.
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		$timestamp = function_exists( 'current_time' ) ? current_time( 'timestamp' ) : time(); // Simple cache buster.
		$rtl       = $this->utilityService->detectRtl();
		$css_file  = $rtl ? 'assets/public/css/laqirapay-public-rtl.css' : 'assets/public/css/laqirapay-public.css'; // Load RTL stylesheet when needed.

		wp_enqueue_style(
			'laqirapay-public-style',
			LAQIRA_PLUGINS_URL . $css_file,
			array(),
			$timestamp
		);

		$this->render( 'public/assets/styles', array( 'is_rtl' => $rtl ) );

		LaqiraLogger::log(
			200,
			'assets',
			'enqueue_styles',
			array(
				'rtl'      => $rtl,
				'css_file' => $css_file,
			)
		);
	}

	/**
	 * Enqueue public scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$timestamp = function_exists( 'current_time' ) ? current_time( 'timestamp' ) : time(); // Sync with stylesheet timestamp.

		wp_enqueue_script(
			'laqirapay-public-script',
			LAQIRA_PLUGINS_URL . 'assets/public/js/laqirapay-first.js',
			array( 'jquery' ),
			$timestamp,
			true
		);

		$this->render( 'public/assets/scripts' );

		LaqiraLogger::log(
			200,
			'assets',
			'enqueue_scripts',
			array(
				'timestamp' => $timestamp,
			)
		);
	}

	private function render( string $view, array $data = array() ): void {
		$normalized_view = strtolower( str_replace( '\\', '/', $view ) );
		if ($normalized_view === null) {
   			 $normalized_view = '';
			}

		$normalized_view = preg_replace( '/[^a-z0-9_\/-]/', '', $normalized_view ?? '' );

		if ( $normalized_view === '' || ! isset( self::VIEW_CONFIG[ $normalized_view ] ) ) {
			LaqiraLogger::log(
				400,
				'assets',
				'render_invalid_view',
				array(
					'view' => sanitize_text_field( (string) $view ),
				)
			);
			return;
		}

		$config = self::VIEW_CONFIG[ $normalized_view ];
		$path   = LAQIRAPAY_PLUGIN_DIR . 'app/Http/Views/' . $config['path'] . '.php';

		if ( ! is_readable( $path ) ) {
			LaqiraLogger::log( 500, 'assets', 'render_missing_view', array( 'view' => $normalized_view ) );
			return;
		}

		$allowed_keys = $config['allowed_keys'];
		$view_data    = $allowed_keys === array()
			? array()
			: array_intersect_key( $data, array_flip( $allowed_keys ) );

		( static function ( array $safe_data ) use ( $path ): void {
			include $path;
		} )( $view_data );
	}
}
