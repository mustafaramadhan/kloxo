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

	public static function generate(array $payload, int $ttlSeconds = 86400): string
	{
		$header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
		$payload['exp'] = time() + $ttlSeconds;
		$payload['iat'] = time();
		$payloadEncoded = base64_encode(json_encode($payload));

		$signature = hash_hmac('sha256', "{$header}.{$payloadEncoded}", self::getSecret(), true);
		$signatureEncoded = base64_encode($signature);

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

		if (!hash_equals($expectedSignature, (string)base64_decode($signatureEncoded))) {
			return null;
		}

		$payload = json_decode((string)base64_decode($payloadEncoded), true);
		if (!is_array($payload)) {
			return null;
		}

		if (isset($payload['exp']) && time() > $payload['exp']) {
			return null;
		}

		return $payload;
	}
}
