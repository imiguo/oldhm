<?php
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_DEPRECATED);
define('ROOT', dirname(dirname(__DIR__)));
define('SUBDOMAIN', !empty($_SERVER['SUBDOMAIN']) ? $_SERVER['SUBDOMAIN'] : '');
if (SUBDOMAIN && is_dir(ROOT . '/templates/' . SUBDOMAIN . '/tmpl/')) {
    define('TMPL_PATH', ROOT . '/templates/' . SUBDOMAIN . '/tmpl/');
} else {
    define('TMPL_PATH', dirname(__DIR__) . '/tmpl/');
}
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
    define('HTTPS', true);
} else {
    define('HTTPS', false);
}

if (file_exists('local')) {
    define('ENV', 'local');
    $settingsFile = 'settings.local.php';
} elseif (file_exists('test')) {
    define('ENV', 'test');
    $settingsFile = 'settings.test.php';
} else {
    define('ENV', 'prod');
    $settingsFile = 'settings.prod.php';
}

function class_autoloader($class) {
    include 'classes/' . $class . '.php';
}
spl_autoload_register('class_autoloader');

require 'function.inc.php';

global $frm;
if (!extension_loaded ('gd'))
{
    $prefix = (PHP_SHLIB_SUFFIX == 'dll' ? 'php_' : '');
    dl ($prefix . 'gd.' . PHP_SHLIB_SUFFIX);
}

$get = $_GET;
$post = $_POST;
$frm = array_merge ($get, $post);
$frm_cookie = $_COOKIE;
$frm_orig = $frm;
$gpc = ini_get ('magic_quotes_gpc');
reset ($frm);
while (list ($kk, $vv) = each ($frm))
{
    if (is_array ($vv))
    {
    }
    else
    {
        if ($gpc == '1')
        {
            $vv = str_replace ('\\\'', '\'', $vv);
            $vv = str_replace ('\\"', '"', $vv);
            $vv = str_replace ('\\\\', '\\', $vv);
        }

        $vv = trim ($vv);
        $vv_orig = $vv;
        $vv = strip_tags ($vv);
    }

    $frm[$kk] = $vv;
    $frm_orig[$kk] = $vv_orig;
}

$gpc = ini_get ('magic_quotes_gpc');
reset ($frm_cookie);
while (list ($kk, $vv) = each ($frm_cookie))
{
    if (is_array ($vv))
    {
    }
    else
    {
        if ($gpc == '1')
        {
            $vv = str_replace ('\\\'', '\'', $vv);
            $vv = str_replace ('\\"', '"', $vv);
            $vv = str_replace ('\\\\', '\\', $vv);
        }

        $vv = trim ($vv);
        $vv = strip_tags ($vv);
    }

    $frm_cookie[$kk] = $vv;
}

$frm_env = array_merge($_ENV, $_SERVER);
$referer = isset($frm_env['HTTP_REFERER']) ? $frm_env['HTTP_REFERER'] : null;
$frm_env['HTTP_HOST'] = preg_replace('/^' . SUBDOMAIN . '\./', '', $frm_env['HTTP_HOST']);
$frm_env['HTTP_HOST'] = preg_replace('/^www\./', '', $frm_env['HTTP_HOST']);
$host = $frm_env['HTTP_HOST'];
if ( ! strpos($referer, '//'.$host)) {
    setcookie('CameFrom', $referer, time() + 630720000);
}

$settings = get_settings();
$transtype = [
    'withdraw_pending'             => 'Withdrawal request',
    'add_funds'                    => 'Transfer from external processings',
    'deposit'                      => 'Deposit',
    'bonus'                        => 'Bonus',
    'penality'                     => 'Penalty',
    'earning'                      => 'Earning',
    'withdrawal'                   => 'Withdrawal',
    'commissions'                  => 'Referral commission',
    'early_deposit_release'        => 'Deposit release',
    'early_deposit_charge'         => 'Commission for an early deposit release',
    'release_deposit'              => 'Deposit returned to user account',
    'exchange_out'                 => ' Received on exchange',
    'exchange_in'                  => 'Spent on exchange',
    'exchange'                     => 'Exchange',
    'internal_transaction_spend'   => 'Spent on Internal Transaction',
    'internal_transaction_receive' => 'Received from Internal Transaction'
];
$exchange_systems = [
    0 => ['name' => 'e-gold', 'sfx' => 'egold'],
    2 => ['name' => 'INTGold', 'sfx' => 'intgold'],
    3 => ['name' => 'PerfectMoney', 'sfx' => 'perfectmoney'],
    4 => ['name' => 'StormPay', 'sfx' => 'stormpay'],
    5 => ['name' => 'e-Bullion', 'sfx' => 'ebullion'],
    6 => ['name' => 'PayPal', 'sfx' => 'paypal'],
    7 => ['name' => 'GoldMoney', 'sfx' => 'goldmoney'],
    8 => ['name' => 'eeeCurrency', 'sfx' => 'eeecurrency'],
    9 => ['name' => 'Pecunix', 'sfx' => 'pecunix'],
    10 => ['name' => 'Payeer', 'sfx' => 'payeer'],
    11 => ['name' => 'BitCoin', 'sfx' => 'bitcoin'],
];
foreach ($exchange_systems as $id => $data) {
    if (isset($settings['def_payee_account_'.$data['sfx']]) AND $settings['def_payee_account_'.$data['sfx']] != '' AND $settings['def_payee_account_'.$data['sfx']] != '0') {
        $exchange_systems[$id]['status'] = 1;
        continue;
    } else {
        $exchange_systems[$id]['status'] = 0;
        continue;
    }
}

$settings['site_url'] = (is_SSL() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];

$ip = $frm_env['REMOTE_ADDR'];
$time = time();
$url = $frm_env['REQUEST_URI'];
$agent = $frm_env['HTTP_USER_AGENT'];
db_open();
$ret = db_query("insert hm2_visit (`ip`, `time`, `url`, `agent`) values('$ip', '$time', '$url', '$agent')");
