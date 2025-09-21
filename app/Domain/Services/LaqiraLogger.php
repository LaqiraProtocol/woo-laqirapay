<?php
namespace LaqiraPay\Domain\Services;

/**
 * Service for logging application events, integrated with WooCommerce Logger.
 */
class LaqiraLogger
{
	private $logger;
	private array $sensitiveKeys = [
		'authorization',
		'api_key',
		'apikey',
		'jwt',
		'token',
		'password',
		'secret',
		'cid',
	];

	public function __construct()
	{
		// Ensure WooCommerce and WC_Logger are available
		if (!class_exists('\WC_Logger') || !function_exists('wc_get_order')) {
			$this->logger = null;
			return;
		}

		// Use get_logger if available (WooCommerce 7.0+), fallback to new WC_Logger
		if (method_exists('\WC_Logger', 'get_logger')) {
			$this->logger = \WC_Logger::get_logger('laqira_pay');
		} else {
			$this->logger = new \WC_Logger();
		}
	}

	/**
	 * Magic handler for instance calls to log().
	 */
	public function __call(string $name, array $arguments): void
	{
		if ($name === 'log' && $this->logger) {
			$this->write(...$arguments);
		}
	}

	/**
	 * Magic handler for static calls to log().
	 */
	public static function __callStatic(string $name, array $arguments): void
	{
		if ($name === 'log') {
			$logger = new self();
			$logger->write(...$arguments);
		}
	}

	/**
	 * Actual logging implementation using WC_Logger.
	 */
	private function write(int $level, string $type, string $event, array $context = [], string $message = ''): void
	{
		// Check if logger is initialized
		if (!$this->logger) {
			return;
		}

		// Check if logging is enabled
		$loggingEnabled = (bool) get_option('laqirapay_log_enabled', true);
		if (!$loggingEnabled) {
			return;
		}

		// Enrich and mask context
		$context = $this->enrichContext($context, $event, $message);
		$context = $this->maskSensitive($context);

		// Map custom level to WC_Logger PSR-3 levels
		$wcLevel = $this->mapLevel($level);
		$fullMessage = sprintf('[%s] %s: %s', $type, $event, $message ?: 'No message');
		$context['created_at'] = function_exists('current_time') ? current_time('mysql', 1) : gmdate('Y-m-d H:i:s');

		// Add source for older WooCommerce versions
		if (!method_exists('\WC_Logger', 'get_logger')) {
			$context['source'] = 'laqira_pay';
		}

		// Log to WC_Logger
		$this->logger->log($wcLevel, $fullMessage, $context);
	}

	/**
	 * Map custom numeric levels to WC_Logger PSR-3 levels.
	 */
	private function mapLevel(int $level): string
	{
		$map = [
			100 => 'debug',
			200 => 'info',
			250 => 'notice',
			300 => 'warning',
			400 => 'error',
			500 => 'critical',
			600 => 'alert',
			700 => 'emergency',
		];
		return $map[$level] ?? 'info';
	}

	/**
	 * Enrich log context with environment-specific details.
	 */
	private function enrichContext(array $context, string $event, string $message): array
	{
		// Add WordPress user information
		if (function_exists('wp_get_current_user')) {
			$user = wp_get_current_user();
			if ($user && isset($user->ID) && $user->ID) {
				$context['user'] = $user->ID . ' - ' . $user->user_email;
			}
		}

		// Add WooCommerce order information
		$orderId = $context['order_id'] ?? ($_REQUEST['order_id'] ?? null);
		if ($orderId && function_exists('wc_get_order')) {
			$order = wc_get_order($orderId);
			if ($order) {
				$context['order'] = [
					'id' => $order->get_id(),
					'status' => $order->get_status(),
					'total' => $order->get_total(),
				];
			}
		}

		// Add IP address (always hashed for privacy)
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		if ($ip !== '') {
			$context['ip'] = hash('sha256', $ip);
		}

		// Add request ID
		$requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? ($_SERVER['REQUEST_ID'] ?? null);
		if (!$requestId && function_exists('wp_generate_uuid4')) {
			$requestId = wp_generate_uuid4();
		}
		if ($requestId) {
			$context['request_id'] = $requestId;
		}

		// Add event and message
		$context['event'] = $event;
		if ($message !== '') {
			$context['message'] = $message;
		}

		// Add caller information
		$context['source'] = $this->detectSource();

		return $context;
	}

	/**
	 * Detect the origin of the log call using a backtrace.
	 */
	private function detectSource(): string
	{
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		foreach ($trace as $frame) {
			if (($frame['class'] ?? '') === self::class) {
				continue;
			}
			$function = $frame['function'] ?? '';
			$class = $frame['class'] ?? '';
			$file = isset($frame['file']) ? basename($frame['file']) : '';
			$line = $frame['line'] ?? '';
			$location = $file !== '' ? $file . ':' . $line : '';
			$callable = $class ? $class . '::' . $function : $function;
			return trim($callable . ($location ? ' (' . $location . ')' : ''));
		}
		return '';
	}

	/**
	 * Recursively mask sensitive keys within the context array.
	 */
	private function maskSensitive(array $context): array
	{
		foreach ($context as $key => $value) {
			if (is_array($value)) {
				$context[$key] = $this->maskSensitive($value);
				continue;
			}
			if (in_array(strtolower((string) $key), $this->sensitiveKeys, true)) {
				$context[$key] = '***';
			}
		}
		return $context;
	}
}