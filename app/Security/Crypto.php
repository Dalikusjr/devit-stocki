<?php

namespace App\Security;

/**
 * Chiffrement des informations sensibles (accès des projets) : AES-256-GCM,
 * clé APP_KEY du fichier .env (32 octets en base64).
 * Format stocké : "enc:v1:" + base64(iv[12] . tag[16] . texte chiffré).
 * Une valeur sans ce préfixe est un ancien texte en clair : elle est rendue telle quelle.
 */
class Crypto
{
    private const PREFIX = 'enc:v1:';
    private const CIPHER = 'aes-256-gcm';

    private static function key(): string
    {
        $key = base64_decode($_ENV['APP_KEY'] ?? '', true);
        if ($key === false || strlen($key) !== 32) {
            throw new \RuntimeException("APP_KEY absente ou invalide dans le fichier .env");
        }
        return $key;
    }

    public static function isEncrypted(?string $value): bool
    {
        return $value !== null && str_starts_with($value, self::PREFIX);
    }

    public static function encrypt(string $plain): string
    {
        if ($plain === '' || self::isEncrypted($plain)) {
            return $plain;
        }
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new \RuntimeException("Échec du chiffrement");
        }
        return self::PREFIX . base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(?string $value): string
    {
        if ($value === null || !self::isEncrypted($value)) {
            return (string) $value;
        }
        $raw = base64_decode(substr($value, strlen(self::PREFIX)), true);
        if ($raw === false || strlen($raw) < 28) {
            throw new \RuntimeException("Valeur chiffrée corrompue");
        }
        $plain = openssl_decrypt(substr($raw, 28), self::CIPHER, self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        if ($plain === false) {
            throw new \RuntimeException("Déchiffrement impossible (clé APP_KEY différente ?)");
        }
        return $plain;
    }
}
