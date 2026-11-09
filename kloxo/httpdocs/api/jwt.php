<?php
/**
 * Lightweight Zero-Dependency HMAC-SHA256 Token Generator & Verifier
 * Compliant with Ponytail minimal code principle.
 */

class ApiToken
{
	private static function getSecret(): string
	{
		$secretFile = dirname(__FILE__) . '/../../etc/api_secret.key';
		if (file_exists($secretFile)) {
			$secret = trim((string)file_get_contents($secretFile));
			if (!empty($secret)) {
				return $secret;
			}
		}

		$secret = bin2hex(random_bytes(32));
		@file_put_contents($secretFile, $secret);
		@chmod($secretFile, 0660);
		return $secret;
	}

	public static function base64UrlEncode(string $data): string
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	public static function base64UrlDecode(string $data): string
	{
		$data = str_replace(' ', '+', $data);
		$remainder = strlen($data) % 4;
		if ($remainder) {
			$data .= str_repeat('=', 4 - $remainder);
		}
		return (string)base64_decode(strtr($data, '-_', '+/'));
	}

	public static function generate(array $payload, int $ttlSeconds = 86400): string
	{
		$header = self::base64UrlEncode((string)json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
		$payload['exp'] = time() + $ttlSeconds;
		$payload['iat'] = time();
		$payloadEncoded = self::base64UrlEncode((string)json_encode($payload));

		$signature = hash_hmac('sha256', "{$header}.{$payloadEncoded}", self::getSecret(), true);
		$signatureEncoded = self::base64UrlEncode($signature);

		return "{$header}.{$payloadEncoded}.{$signatureEncoded}";
	}

	public static function verify(string $token): ?array
	{
		$parts = explode('.', $token);
		if (count($parts) !== 3) {
			return null;
		}

		[$header, $payloadEncoded, $signatureEncoded] = $parts;
		$expectedSignature = hash_hmac('sha256', "{$header}.{$payloadEncoded}", self::getSecret(), true);

		if (!hash_equals($expectedSignature, self::base64UrlDecode($signatureEncoded))) {
			return null;
		}

		$payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
		if (!is_array($payload)) {
			return null;
		}

		if (isset($payload['exp']) && time() > $payload['exp']) {
			return null;
		}

		return $payload;
	}
}
