<?php
// helpers/currency.php
// Currency label helpers (no conversion). Safe to include multiple times.

declare(strict_types=1);

if (!function_exists('user_currency_code')) {
  function user_currency_code(): string {
    if (!empty($_SESSION['user']['currency_code'])) {
      return (string)$_SESSION['user']['currency_code'];
    }
    if (!empty($_SESSION['currency_code'])) {
      return (string)$_SESSION['currency_code'];
    }
    return '';
  }
}

if (!function_exists('user_currency_symbol')) {
  function user_currency_symbol(): string {
    if (!empty($_SESSION['user']['currency_symbol'])) {
      return (string)$_SESSION['user']['currency_symbol'];
    }
    if (!empty($_SESSION['currency_symbol'])) {
      return (string)$_SESSION['currency_symbol'];
    }
    return 'RM';
  }
}

// For wallet tables, we need a stable key (fits VARCHAR(3) in current schema).
// Prefer currency_code (e.g., USD, SAR). For older MYR users, fall back to 'RM' if symbol is RM.
if (!function_exists('wallet_currency_key')) {
  function wallet_currency_key(): string {
    $code = user_currency_code();
    $sym  = user_currency_symbol();

    if ($code !== '') {
      if ($code === 'MYR' && strtoupper($sym) === 'RM') return 'RM';
      return strtoupper($code);
    }
    // Last resort: use the symbol text (works for RM, SAR, etc.)
    return strtoupper(trim($sym)) !== '' ? strtoupper(trim($sym)) : 'RM';
  }
}

// What to show in UI (prefer symbol if it looks like letters like RM/SAR; otherwise show code)
if (!function_exists('currency_display')) {
  function currency_display(): string {
    $sym = user_currency_symbol();
    if (preg_match('/^[A-Z]{2,4}$/', strtoupper($sym))) return strtoupper($sym);
    $code = user_currency_code();
    if ($code !== '') return strtoupper($code);
    return 'RM';
  }
}
