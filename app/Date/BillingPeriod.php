<?php

namespace App\Date;


class BillingPeriod
{


    public static function monthly(): array
    {
        return [
            'name'  => 'mensuelle',
            'start' => date('Y-m-01 00:00:00'),
            'end'   => date('Y-m-01 00:00:00', strtotime('first day of next month')),
        ];
    }
}
