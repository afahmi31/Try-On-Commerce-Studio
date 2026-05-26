<?php

namespace App\Support;

class IpMasker
{
    public static function mask(?string $ipAddress): string
    {
        if (! is_string($ipAddress) || trim($ipAddress) === '') {
            return '-';
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ipAddress);

            return ($parts[0] ?? 'x').'.'.($parts[1] ?? 'x').'.xxx.xxx';
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $segments = explode(':', $ipAddress);

            return (($segments[0] ?? 'xxxx').':'.($segments[1] ?? 'xxxx')).':xxxx:xxxx';
        }

        return '-';
    }
}

