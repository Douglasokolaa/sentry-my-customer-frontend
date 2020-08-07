<?php

use Illuminate\Support\Facades\Cookie;

if (!function_exists('format_money')) {

    /**
     * Properly Format money with currency
     *
     * @param number $amount 
     * @param string $currency
     * @return strimg
     */
    function format_money($amount, $currency = null)
    {
        $formatted = number_format($amount);
        $user_role = Cookie::get('user_role');
        if ($user_role == 'store_admin' || $user_role == 'store_assistant') {
            if (is_numeric($amount)) {
                if ($currency == null) {
                    // currency is set already otherwise use NGN the default
                    $currency = (Cookie::get('currency') != '') ? Cookie::get('currency') : 'NGN';
                }
                $format_amount = number_format($amount, 2);
                $currency = get_currency_symbol($currency);
                $formatted = $currency . ' ' . $format_amount;
            }
        } else {
            $currency = get_currency_symbol($currency);
            $formatted = $currency . ' ' . $formatted;
        }
        return html_entity_decode($formatted);
    }
}

if (!function_exists('get_currency_symbol')) {

    /**
     * Properly format curency with symbol instead of ISO code
     *
     * @param string $currency
     * @return strimg
     */
    function get_currency_symbol($currency)
    {
        $currency = strtolower($currency);
        $_currency = $currency;

        switch ($currency) {
            case 'ngn':
                $_currency = '&#8358;';
                break;

            case 'usd':
                $_currency = '&#36;';
                break;

            case 'inr':
                $_currency = '&#x20B9;';
                break;

            default:
                $_currency = strtoupper($currency);
                break;
        }
        return $_currency;
    }
}
