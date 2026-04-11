<?php

function currency_symbol() {
    return $_SESSION['currency_symbol'] ?? 'RM';
}

function currency_code() {
    return $_SESSION['currency_code'] ?? 'MYR';
}

function money($amount) {
    $currency = currency_code();

    $locales = [
        'USD' => 'en_US',
        'EUR' => 'de_DE',
        'GBP' => 'en_GB',
        'JPY' => 'ja_JP',
        'IDR' => 'id_ID',
        'MYR' => 'ms_MY',
        'SAR' => 'ar_SA',
        'AED' => 'ar_AE'
    ];

    $locale = $locales[$currency] ?? 'en_US';

    $fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    return $fmt->formatCurrency($amount, $currency);
}
