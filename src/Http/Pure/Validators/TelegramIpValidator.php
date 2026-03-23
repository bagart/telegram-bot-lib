<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Http\Pure\Validators;

class TelegramIpValidator
{
    private const TELEGRAM_IP_RANGES = [
        '149.154.160.0/20',
        '91.108.4.0/22',
    ];

    public function validate(string $ip): bool
    {
        foreach (self::TELEGRAM_IP_RANGES as $cidr) {
            if ($this->ipInRange($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    private function ipInRange(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $maskLong = -1 << (32 - (int)$mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }
}
