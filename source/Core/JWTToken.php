<?php

namespace Source\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use Exception;

class JWTToken
{
    /**
     * 🔐 CHAVE CRIPTOGRÁFICA REAL (64 BYTES)
     * Gerada com random_bytes(64) + base64_encode
     */
    private string $secretKey =
    "ho0I8R0922lOHQAvFZq7TSzhbWaPPKrn5YF9Bp9xSz+gVGh4vO99VPyJAeqkx5pxGs2Xjp4Iq1yCO01J84ABwQ==";

    /**
     * Algoritmo forte
     */
    private string $algorithm = "HS512";

    /**
     * Emissor
     */
    private string $url = "http://localhost/rochas";

    /**
     * Criar token JWT
     */
    public function create(array $payLoad): string
    {
        $issuedAt = new DateTimeImmutable();
        $expire   = $issuedAt->modify('+200 minutes')->getTimestamp();

        $data = [
            'iat'  => $issuedAt->getTimestamp(),
            'jti'  => base64_encode(random_bytes(16)),
            'iss'  => $this->url,
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $expire,
            'data' => $payLoad
        ];

        return JWT::encode(
            $data,
            base64_decode($this->secretKey), // ⬅️ IMPORTANTE
            $this->algorithm
        );
    }

    /**
     * Decodificar token JWT
     */
    public function decode(string $token): bool|object
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key(base64_decode($this->secretKey), $this->algorithm)
            );

            $now = new DateTimeImmutable();

            if (
                $decoded->iss !== $this->url ||
                $decoded->nbf > $now->getTimestamp() ||
                $decoded->exp < $now->getTimestamp()
            ) {
                return false;
            }

            return $decoded;

        } catch (Exception) {
            return false;
        }
    }
}
