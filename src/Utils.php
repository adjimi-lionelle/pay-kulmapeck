<?php
namespace App;

class Utils
{
    const SUCCESS = 'SUCCESS';
    const PENDING = 'PENDING';
    const FAILED = 'FAILED';
    const CREATED = 'CREATED';

    public static function getStatusValues(): array
    {
        return [
            self::SUCCESS,
            self::PENDING,
            self::FAILED,
            self::CREATED,
        ];
    }

    public function applyPercentIncrease($number, $percentage) {
        // Calculate the percentage
        $result = ($percentage / 100) * $number;
        
        return $result;
    }
}
