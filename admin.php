<?php
function shop_pin_html()
{
    print '<html><body>Enter pin:<br>
<form method=post>
<input type=hidden name=a value=enter_pin>
<input type=text name=pin value=""><br>
<input type=submit value="Go">
</form></body></html>';
}

$arr = get_defined_vars();
while (list ($kk, $vv) = each($arr)) {
    if (gettype($$kk) != 'array') {
        $$kk = '';
        continue;
    }
}

if (file_exists('install.php')) {
    print 'Delete install.php file for security reason please!';
    exit ();
}

$settings = [];
$userinfo = [];
$frm['a'] = '';
include 'lib/config.inc.php';
global $frm;
if (HTTPS) {
    $frm_env['HTTPS'] = 1;
}

$userinfo = [];
$userinfo['logged'] = 0;
$dbconn = db_open();
if ( ! $dbconn) {
    print 'Cannot connect mysql';
    exit ();
}

$q = 'select * from hm2_processings';
($sth = db_query($q) OR print mysql_error());
while ($row = mysql_fetch_array($sth)) {
    $sfx = strtolower($row['name']);
    $sfx = preg_replace('/([^\\w])/', '_', $sfx);
    $exchange_systems[$row['id']] = [
        'name'        => $row['name'],
        'sfx'         => $sfx,
        status        => $row['status'],
        'has_account' => 0
    ];
}

define('THE_GC_SCRIPT_V2005_04_01', 'answer');
$acsent_settings = get_accsent();
if ($frm['a'] == 'showprogramstat') {
    $login = quote($frm['login']);
    $q = ''.'select * from hm2_users where id = 1 and username = \''.$login.'\' and stat_password <> \'\'';
    ($sth = db_query($q) OR print mysql_error());
    $flag = 0;
    while ($row = mysql_fetch_array($sth)) {
        if ($row['stat_password'] == md5($frm['password'])) {
            $flag = 1;
            continue;
        }
    }

    if ($flag == 0) {
        print '<center>Wrong login or password</center>';
    } else {
        if ($frm['page'] == 'members') {
            include 'inc/admin/members_program.inc.php';
        } else {
            if ($frm['page'] == 'pendingwithdrawal') {
                include 'inc/admin/pending_program.inc.php';
            } else {
                if ($frm['page'] == 'whoonline') {
                    include 'inc/admin/whoonline_program.inc.php';
                } else {
                    if ($frm['page'] == 'TrayInfo') {
                        include 'inc/admin/tray_info.php';
                    } else {
                        include 'inc/admin/main_program.inc.php';
                    }
                }
            }
        }
    }

    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'logout') {
    setcookie('password', '', time() - 86400);
    header('Location: index.php');
    db_close($dbconn);
    exit ();
}

$username = quote($frm_cookie['username']);
$password = $frm_cookie['password'];
$ip = $frm_env['REMOTE_ADDR'];
$add_login_check = ''.' and last_access_time + interval 30 minute > now() and last_access_ip = \''.$ip.'\'';
if ($settings['demomode'] == 1) {
    $add_login_check = '';
}

list ($user_id, $chid) = split('-', $password, 2);
$user_id = sprintf('%d', $user_id);
$chid = quote($chid);
if ($settings['htaccess_authentication'] == 1) {
    $login = $frm_env['PHP_AUTH_USER'];
    $password = $frm_env['PHP_AUTH_PW'];
    $q = 'select * from hm2_users where id = 1';
    ($sth = db_query($q) OR print mysql_error());
    while ($row = mysql_fetch_array($sth)) {
        if (($login == $row['username'] AND md5($password) == $row['password'])) {
            $userinfo = $row;
            $userinfo[logged] = 1;
            continue;
        }
    }

    if ($userinfo[logged] != 1) {
        header('WWW-Authenticate: Basic realm="Authorization Required!"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Authorization Required!';
        exit ();
    }
} else {
    if ($settings['htpasswd_authentication'] == 1) {
        if ((file_exists('./.htpasswd') AND file_exists('./.htaccess'))) {
            $q = 'select * from hm2_users where id = 1';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $userinfo = $row;
                $userinfo[logged] = 1;
            }
        }
    } else {
        $q = 'select *, date_format(date_register + interval '.$settings['time_dif'].(''.' day, \'%b-%e-%Y\') as create_account_date, l_e_t + interval 15 minute < now() as should_count from hm2_users where id = '.$user_id.' and (status=\'on\' or status=\'suspended\') '.$add_login_check.' and id = 1');
        ($sth = db_query($q) OR print mysql_error());
        while ($row = mysql_fetch_array($sth)) {
            if (($settings['brute_force_handler'] == 1 AND $row['activation_code'] != '')) {
                header('Location: index.php?a=login&say=invalid_login&username='.$frm['username']);
                db_close($dbconn);
                exit ();
            }

            $qhid = $row['hid'];
            $hid = substr($qhid, 5, 20);
            if ($chid == md5($hid)) {
                $userinfo = $row;
                $userinfo['logged'] = 1;
                $q = 'update hm2_users set last_access_time = now() where id = 1';
                (db_query($q) OR print mysql_error());
                continue;
            } else {
                $q = 'update hm2_users set bf_counter = bf_counter + 1 where id = '.$row['id'];
                db_query($q);
                if (($settings['brute_force_handler'] == 1 AND $row['bf_counter'] == $settings['brute_force_max_tries'])) {
                    $activation_code = get_rand_md5(50);
                    $q = ''.'update hm2_users set bf_counter = bf_counter + 1, activation_code = \''.$activation_code.'\' where id = '.$row['id'];
                    db_query($q);
                    $info = [];
                    $info['activation_code'] = $activation_code;
                    $info['username'] = $row['username'];
                    $info['name'] = $row['name'];
                    $info['ip'] = $frm_env['REMOTE_ADDR'];
                    $info['max_tries'] = $settings['brute_force_max_tries'];
                    send_template_mail('brute_force_activation', $row['email'], $settings['system_email'], $info);
                    header('Location: index.php?a=login&say=invalid_login&username='.$frm['username']);
                    db_close($dbconn);
                    exit ();
                    continue;
                }

                continue;
            }
        }
    }
}

if ($userinfo['logged'] != 1) {
    header('Location: index.php');
    db_close($dbconn);
    exit ();
}

if ((time() - 900 < $acsent_settings[timestamp] AND $acsent_settings[pin] != '')) {
    if ($frm[a] == 'enter_pin') {
        if ($frm[pin] == $acsent_settings[pin]) {
            $acsent_settings[last_ip] = $frm_env['REMOTE_ADDR'];
            $acsent_settings[last_browser] = $frm_env['HTTP_USER_AGENT'];
            $acsent_settings[timestamp] = 0;
            $acsent_settings[pin] = '';
            set_accsent();
        }

        header('Location: admin.php');
        exit ();
    }

    shop_pin_html();
    exit ();
}

$NEWPIN = get_rand_md5(7);
$message = ''.'Hello,

Someone tried login admin area
ip: '.$frm_env['REMOTE_ADDR'].'
browser: '.$frm_env['HTTP_USER_AGENT'].'

Pin code for entering admin area is:
'.$NEWPIN.'

This code will be expired in 15 minutes.
';
if ($acsent_settings[detect_ip] == 'disabled') {
} else {
    if ($acsent_settings[detect_ip] == 'medium') {
        $z1 = preg_replace(''.'/\\.(\\d+)$/', '', $acsent_settings[last_ip]);
        $z2 = preg_replace(''.'/\\.(\\d+)$/', '', $frm_env['REMOTE_ADDR']);
        if ($z1 != $z2) {
            $acsent_settings['pin'] = $NEWPIN;
            $acsent_settings['timestamp'] = time();
            send_mail($acsent_settings['email'], 'Pin code', $message);
            set_accsent();
            header('Location: admin.php');
            db_close($dbconn);
            exit ();
        }
    } else {
        if ($acsent_settings[detect_ip] == 'high') {
            if ($acsent_settings['last_ip'] != $frm_env['REMOTE_ADDR']) {
                $acsent_settings['pin'] = $NEWPIN;
                $acsent_settings['timestamp'] = time();
                send_mail($acsent_settings['email'], 'Pin code', $message);
                set_accsent();
                header('Location: admin.php');
                db_close($dbconn);
                exit ();
            }
        } else {
            print 'Settings broken. Contact script developer please';
            exit ();
        }
    }
}

if ($acsent_settings[detect_browser] == 'disabled') {
} else {
    if ($acsent_settings[detect_browser] == 'enabled') {
        if ($acsent_settings['last_browser'] != $frm_env['HTTP_USER_AGENT']) {
            $acsent_settings['pin'] = $NEWPIN;
            $acsent_settings['timestamp'] = time();
            send_mail($acsent_settings['email'], 'Pin code', $message);
            set_accsent();
            header('Location: admin.php');
            db_close($dbconn);
            exit ();
        }
    } else {
        print 'Settings broken. Contact script developer please';
        exit ();
    }
}

if ($frm['a'] == 'encrypt_mysql') {
    if ($settings['demomode'] != 1) {
        if (($userinfo['transaction_code'] != '' AND $userinfo['transaction_code'] != $frm['alternative_passphrase'])) {
            header('Location: ?a=security&say=invalid_passphrase');
            db_close($dbconn);
            exit ();
        }

        if ( ! file_exists('./tmpl_c/.htdata')) {
            $fp = fopen('./tmpl_c/.htdata', 'w');
            fclose($fp);
            save_settings();
        }

        header('Location: admin.php?a=security&say=done');
        db_close($dbconn);
        exit ();
    }

    header('Location: admin.php?a=security');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'change_login_security' AND $frm['act'] == 'change')) {
    $acsent_settings['detect_ip'] = $frm['ip'];
    $acsent_settings['detect_browser'] = $frm['browser'];
    $acsent_settings['last_browser'] = $frm_env['HTTP_USER_AGENT'];
    $acsent_settings['last_ip'] = $frm_env['REMOTE_ADDR'];
    $acsent_settings['email'] = $frm['email'];
    set_accsent();
    header('Location: ?a=security');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'startup_bonus' AND $frm['act'] == 'set')) {
    $settings['startup_bonus'] = sprintf('%0.2f', $frm['startup_bonus']);
    $settings['startup_bonus_ec'] = sprintf('%d', $frm['ec']);
    $settings['forbid_withdraw_before_deposit'] = ($frm['forbid_withdraw_before_deposit'] ? 1 : 0);
    $settings['activation_fee'] = sprintf('%0.2f', $frm['activation_fee']);
    save_settings();
    header('Location: ?a=startup_bonus&say=yes');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'exchange_rates' AND $frm['action'] == 'save')) {
    if ($settings['demomode']) {
        header('Location: ?a=exchange_rates&say=demo');
        db_close($dbconn);
        exit ();
    }

    $exch = $frm['exch'];
    if (is_array($exch)) {
        foreach ($exchange_systems as $id_from => $value) {
            foreach ($exchange_systems as $id_to => $value) {
                if ($id_to == $id_from) {
                    continue;
                }

                $percent = sprintf('%.02f', $exch[$id_from][$id_to]);
                if ($percent < 0) {
                    $percent = 0;
                }

                if (100 < $percent) {
                    $percent = 100;
                }

                $q = ''.'select count(*) as cnt from hm2_exchange_rates where `sfrom` = '.$id_from.' and `sto` = '.$id_to;
                $sth = db_query($q);
                $row = mysql_fetch_array($sth);
                if (0 < $row['cnt']) {
                    $q = ''.'update hm2_exchange_rates set percent = '.$percent.' where `sfrom` = '.$id_from.' and `sto` = '.$id_to;
                } else {
                    $q = ''.'insert into hm2_exchange_rates set percent = '.$percent.', `sfrom` = '.$id_from.', `sto` = '.$id_to;
                }

                db_query($q);
            }
        }
    }

    header('Location: ?a=exchange_rates');
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'test_egold_settings') {
    include 'inc/admin/auto_pay_settings_test.inc.php';
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'test_evocash_settings') {
    include 'inc/admin/auto_pay_settings_evocash_test.inc.php';
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'test_intgold_settings') {
    include 'inc/admin/auto_pay_settings_intgold_test.inc.php';
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'test_eeecurrency_settings') {
    include 'inc/admin/auto_pay_settings_eeecurrency_test.inc.php';
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'test_ebullion_settings') {
    include 'inc/admin/auto_pay_settings_ebullion_test.inc.php';
    db_close($dbconn);
    exit ();
}

if ($userinfo['should_count'] == 1) {
    $q = ''.'update hm2_users set last_access_time = now() where username=\''.$username.'\'';
    if ( ! (db_query($q))) {
        exit (mysql_error());;
    }

    count_earning(-1);
}

if (($frm['a'] == 'affilates' AND $frm['action'] == 'remove_ref')) {
    $u_id = sprintf('%d', $frm['u_id']);
    $ref = sprintf('%d', $frm['ref']);
    $q = ''.'update hm2_users set ref = 0 where id = '.$ref;
    (db_query($q) OR print mysql_error());
    header(''.'Location: ?a=affilates&u_id='.$u_id);
    db_close($dbconn);
    exit ();
}

if (($frm[a] == 'affilates' AND $frm['action'] == 'change_upline')) {
    $u_id = sprintf('%d', $frm['u_id']);
    $upline = quote($frm['upline']);
    $q = ''.'select * from hm2_users where username=\''.$upline.'\'';
    ($sth = db_query($q) OR print mysql_error());
    $id = 0;
    while ($row = mysql_fetch_array($sth)) {
        $id = $row['id'];
    }

    $q = ''.'update hm2_users set ref = '.$id.' where id = '.$u_id;
    (db_query($q) OR print mysql_error());
    header(''.'Location: ?a=affilates&u_id='.$u_id);
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'pending_deposit_details' AND $frm['action'] == 'movetoproblem')) {
    $id = sprintf('%d', $frm['id']);
    $q = ''.'update hm2_pending_deposits set status=\'problem\' where id = '.$id;
    (db_query($q) OR print mysql_error());
    header('Location: ?a=pending_deposits');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'pending_deposit_details' AND $frm['action'] == 'movetonew')) {
    $id = sprintf('%d', $frm['id']);
    $q = ''.'update hm2_pending_deposits set status=\'new\' where id = '.$id;
    (db_query($q) OR print mysql_error());
    header('Location: ?a=pending_deposits&type=problem');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'pending_deposit_details' AND $frm['action'] == 'delete')) {
    $id = sprintf('%d', $frm['id']);
    $q = ''.'delete from hm2_pending_deposits where id = '.$id;
    (db_query($q) OR print mysql_error());
    header('Location: ?a=pending_deposits&type='.$frm['type']);
    db_close($dbconn);
    exit ();
}

if ((($frm['a'] == 'pending_deposit_details' AND ($frm['action'] == 'movetodeposit' OR $frm['action'] == 'movetoaccount')) AND $frm['confirm'] == 'yes')) {
    $deposit_id = $id = sprintf('%d', $frm['id']);
    $q = ''.'select
          hm2_pending_deposits.*,
          hm2_users.username
        from
          hm2_pending_deposits,
          hm2_users
        where
          hm2_pending_deposits.user_id = hm2_users.id and
          hm2_pending_deposits.id = '.$id.' and
          hm2_pending_deposits.status != \'processed\'
       ';
    ($sth = db_query($q) OR print mysql_error());
    $amount = sprintf('%0.2f', $frm['amount']);
    while ($row = mysql_fetch_array($sth)) {
        $ps = $row['ec'];
        $username = $row['username'];
        $compound = sprintf('%d', $row['compound']);
        $fields = $row['fields'];
        $user_id = $row['user_id'];
        if ((100 < $compound OR $compound < 0)) {
            $compound = 0;
        }

        $q = 'insert into hm2_history set
            user_id = '.$row['user_id'].(''.',
            date = now(),
            amount = '.$amount.',
            actual_amount = '.$amount.',
            type=\'add_funds\',
            description=\'').quote($exchange_systems[$row['ec']]['name']).' transfer received\',
            ec = '.$row['ec'];
        db_query($q);
        if (($frm['action'] == 'movetodeposit' AND 0 < $row[type_id])) {
            $q = 'select name, delay from hm2_types where id = '.$row['type_id'];
            ($sth1 = db_query($q) OR print mysql_error());
            $row1 = mysql_fetch_array($sth1);
            $delay = $row1[delay];
            if (0 < $delay) {
                --$delay;
            }

            $q = 'insert into hm2_deposits set
              user_id = '.$row['user_id'].',
              type_id = '.$row['type_id'].(''.',
              deposit_date = now(),
              last_pay_date = now() + interval '.$delay.' day,
              status = \'on\',
              q_pays = 0,
              amount = '.$amount.',
              actual_amount = '.$amount.',
              ec = '.$ps.',
              compound = '.$compound);
            db_query($q);
            $deposit_id = mysql_insert_id();
            $q = 'insert into hm2_history set
              user_id = '.$row['user_id'].(''.',
              date = now(),
              amount = -'.$amount.',
              actual_amount = -'.$amount.',
              type=\'deposit\',
              description=\'Deposit to ').quote($row1[name]).(''.'\',
              ec = '.$ps.',
              deposit_id = '.$deposit_id.'
           ');
            db_query($q);
            $ref_sum = referral_commission($row['user_id'], $amount, $ps);
        }

        $info = [];
        $q = 'select * from hm2_users where id = '.$user_id;
        $sth1 = db_query($q);
        $userinfo = mysql_fetch_array($sth1);
        $q = 'select * from hm2_types where id = '.$row['type_id'];
        $sth1 = db_query($q);
        $type = mysql_fetch_array($sth1);
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = number_format($row['amount'], 2);
        $info['currency'] = $exchange_systems[$ps]['name'];
        $info['compound'] = number_format($type['compound'], 2);
        $info['plan'] = (0 < $row[type_id] ? $type['name'] : 'Deposit to Account');
        $q = 'select * from hm2_processings where id = '.$row['ec'];
        $sth = db_query($q);
        $processing = mysql_fetch_array($sth);
        $pfields = unserialize($processing['infofields']);
        $infofields = unserialize($fields);
        $f = '';
        foreach ($pfields as $id => $name) {
            $f .= ''.$name.': '.stripslashes($infofields[$id]).'
';
        }

        $info['fields'] = $f;
        $q = 'select date_format(date + interval '.$settings['time_dif'].' hour, \'%b-%e-%Y %r\') as dd from hm2_pending_deposits where id = '.$row['id'];
        ($sth1 = db_query($q) OR print mysql_error());
        $row1 = mysql_fetch_array($sth1);
        $info['deposit_date'] = $row1['dd'];
        $q = 'select email from hm2_users where id = 1';
        $sth1 = db_query($q);
        $admin_row = mysql_fetch_array($sth1);
        send_template_mail('deposit_approved_admin_notification', $admin_row['email'], $settings['opt_in_email'], $info);
        send_template_mail('deposit_approved_user_notification', $userinfo['email'], $settings['opt_in_email'], $info);
    }

    $id = sprintf('%d', $frm['id']);
    $q = ''.'update hm2_pending_deposits set status=\'processed\' where id = '.$id;
    (db_query($q) OR print mysql_error());
    header('Location: ?a=pending_deposits');
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'mass') {
    if ($frm['action2'] == 'massremove') {
        $ids = $frm['pend'];
        reset($ids);
        while (list ($kk, $vv) = each($ids)) {
            $q = ''.'delete from hm2_history where id = '.$kk;
            (db_query($q) OR print mysql_error());
        }

        header('Location: ?a=thistory&ttype=withdraw_pending&say=massremove');
        db_close($dbconn);
        exit ();
    }

    if ($frm['action2'] == 'masssetprocessed') {
        $ids = $frm['pend'];
        reset($ids);
        while (list ($kk, $vv) = each($ids)) {
            $q = ''.'select * from hm2_history where id = '.$kk;
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $q = 'insert into hm2_history set
		user_id = '.$row['user_id'].',
		amount = -'.abs($row['actual_amount']).',
		actual_amount = -'.abs($row['actual_amount']).',
		type = \'withdrawal\',
		date = now(),
		description = \'Withdrawal processed\',
		ec = '.$row['ec'];
                (db_query($q) OR print mysql_error());
                $q = 'delete from hm2_history where id = '.$row['id'];
                (db_query($q) OR print mysql_error());
                $userinfo = [];
                $q = 'select * from hm2_users where id = '.$row['user_id'];
                $sth1 = db_query($q);
                $userinfo = mysql_fetch_array($sth1);
                $info = [];
                $info['username'] = $userinfo['username'];
                $info['name'] = $userinfo['name'];
                $info['amount'] = number_format(abs($row['amount']), 2);
                $info['currency'] = $exchange_systems[$row['ec']]['name'];
                $info['account'] = 'n/a';
                $info['batch'] = 'n/a';
                $info['paying_batch'] = 'n/a';
                $info['receiving_batch'] = 'n/a';
                send_template_mail('withdraw_user_notification', $userinfo['email'], $settings['opt_in_email'], $info);
                $q = 'select email from hm2_users where id = 1';
                $sth = db_query($q);
                $admin_row = mysql_fetch_array($sth);
                send_template_mail('withdraw_admin_notification', $admin_row['email'], $settings['opt_in_email'], $info);
            }
        }

        header('Location: ?a=thistory&ttype=withdraw_pending&say=massprocessed');
        db_close($dbconn);
        exit ();
    }

    if ($frm['action2'] == 'masscsv') {
        $ids = $frm['pend'];
        if ( ! $ids) {
            print 'Nothing selected.';
            db_close($dbconn);
            exit ();
        }

        reset($ids);
        header('Content-type: text/plain');
        $ec = -1;
        $s = '-1';
        while (list ($kk, $vv) = each($ids)) {
            $s .= ''.','.$kk;
        }

        $q = ''.'select 
		h.*, 
		u.egold_account, 
		u.evocash_account, 
		u.intgold_account,
		u.stormpay_account,
		u.ebullion_account,
		u.paypal_account,
		u.goldmoney_account,
		u.eeecurrency_account
              from hm2_history as h, hm2_users as u where h.id in ('.$s.') and u.id = h.user_id order by ec';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            if (100 < $row['ec']) {
                continue;
            }

            if ($ec != $row['ec']) {
                print '#'.$exchange_systems[$row['ec']]['name'].' transactions (account, amount)
';
                $ec = $row['ec'];
            }

            if ($row['ec'] == 0) {
                $ac = $row['egold_account'];
            } else {
                if ($row['ec'] == 1) {
                    $ac = $row['evocash_account'];
                } else {
                    if ($row['ec'] == 2) {
                        $ac = $row['intgold_account'];
                    } else {
                        if ($row['ec'] == 4) {
                            $ac = $row['stormpay_account'];
                        } else {
                            if ($row['ec'] == 5) {
                                $ac = $row['ebullion_account'];
                            } else {
                                if ($row['ec'] == 6) {
                                    $ac = $row['paypal_account'];
                                } else {
                                    if ($row['ec'] == 7) {
                                        $ac = $row['goldmoney_account'];
                                    } else {
                                        if ($row['ec'] == 8) {
                                            $ac = $row['eeecurrency_account'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $amount = abs($row['amount']);
            $fee = floor($amount * $settings['withdrawal_fee']) / 100;
            if ($fee < $settings['withdrawal_fee_min']) {
                $fee = $settings['withdrawal_fee_min'];
            }

            $to_withdraw = $amount - $fee;
            if ($to_withdraw < 0) {
                $to_withdraw = 0;
            }

            $to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);
            print $ac.','.abs($to_withdraw).'
';
        }

        db_close($dbconn);
        exit ();
    }

    if (($frm['action2'] == 'masspay' AND $frm['action3'] == 'masspay')) {
        if ($settings['demomode'] == 1) {
            exit ();
        }

        $ids = $frm['pend'];
        if ($frm['e_acc'] == 1) {
            $egold_account = $frm['egold_account'];
            $egold_password = $frm['egold_password'];
            $settings['egold_from_account'] = $egold_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'egold_account_password\'';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $egold_account = $settings['egold_from_account'];
                $egold_password = decode_pass_for_mysql($row['v']);
            }
        }

        if ($frm['perfectmoney_acc'] == 1) {
            $egold_account = $frm['perfectmoney_account'];
            $perfectmoney_password = $frm['perfectmoney_password'];
            $settings['perfectmoney_from_account'] = $perfectmoney_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'perfectmoney_account_password\'';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $perfectmoney_account = $settings['perfectmoney_from_account'];
                $perfectmoney_password = decode_pass_for_mysql($row['v']);
            }
        }

        if ($frm['evo_acc'] == 1) {
            $evocash_account = $frm['evocash_account'];
            $evocash_password = $frm['evocash_password'];
            $evocash_code = $frm['evocash_code'];
            $settings['evocash_username'] = $frm[evocash_name];
            $settings['evocash_from_account'] = $evocash_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'evocash_account_password\'';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $evocash_account = $settings['evocash_from_account'];
                $evocash_password = decode_pass_for_mysql($row['v']);
            }

            $q = 'select v from hm2_pay_settings where n=\'evocash_transaction_code\'';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $evocash_code = decode_pass_for_mysql($row['v']);
            }
        }

        if ($frm['intgold_acc'] == 1) {
            $intgold_account = $frm['intgold_account'];
            $intgold_password = $frm['intgold_password'];
            $intgold_code = $frm['intgold_code'];
            $settings['intgold_from_account'] = $intgold_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'intgold_password\'';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $intgold_account = $settings['intgold_from_account'];
                $intgold_password = decode_pass_for_mysql($row['v']);
            }

            $q = 'select v from hm2_pay_settings where n=\'intgold_transaction_code\'';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $intgold_code = decode_pass_for_mysql($row['v']);
            }
        }

        if ($frm['eeecurrency_acc'] == 1) {
            $eeecurrency_account = $frm['eeecurrency_account'];
            $eeecurrency_password = $frm['eeecurrency_password'];
            $eeecurrency_code = $frm['eeecurrency_code'];
            $settings['eeecurrency_from_account'] = $eeecurrency_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'eeecurrency_password\'';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $eeecurrency_account = $settings['eeecurrency_from_account'];
                $eeecurrency_password = decode_pass_for_mysql($row['v']);
            }

            $q = 'select v from hm2_pay_settings where n=\'eeecurrency_transaction_code\'';
            ($sth = db_query($q) OR print mysql_error());
            while ($row = mysql_fetch_array($sth)) {
                $eeecurrency_code = decode_pass_for_mysql($row['v']);
            }
        }

        @set_time_limit(9999999);
        reset($ids);
        while (list ($kk, $vv) = each($ids)) {
            $q = ''.'select h.*, u.egold_account, u.evocash_account, u.intgold_account, u.ebullion_account, u.eeecurrency_account, u.username, u.name, u.email from hm2_history as h, hm2_users as u where h.id = '.$kk.' and u.id = h.user_id and h.ec in (0, 1, 2, 5, 8, 9)';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $amount = abs($row['actual_amount']);
                $fee = floor($amount * $settings['withdrawal_fee']) / 100;
                if ($fee < $settings['withdrawal_fee_min']) {
                    $fee = $settings['withdrawal_fee_min'];
                }

                $to_withdraw = $amount - $fee;
                if ($to_withdraw < 0) {
                    $to_withdraw = 0;
                }

                $to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);
                $success_txt = 'Withdrawal to '.$row['username'].' from '.$settings['site_name'];
                if ($row['ec'] == 0) {
                    $error_txt = ''.'Error, tried to send '.$to_withdraw.' to e-gold account # '.$row['egold_account'].'. Error:';
                    list ($res, $text, $batch) = send_money_to_egold($egold_password, $to_withdraw,
                        $row['egold_account'], $success_txt, $error_txt);
                }

                if ($row['ec'] == 1) {
                    $error_txt = ''.'Error, tried to send '.$to_withdraw.' to evocash account # '.$row['evocash_account'].'. Error:';
                    list ($res, $text, $batch) = send_money_to_evocash(''.$evocash_password.'|'.$evocash_code,
                        $to_withdraw, $row['evocash_account'], $success_txt, $error_txt);
                }

                if ($row['ec'] == 2) {
                    $error_txt = ''.'Error, tried to send '.$to_withdraw.' to IntGold account # '.$row['intgold_account'].'. Error:';
                    list ($res, $text, $batch) = send_money_to_intgold(''.$intgold_password.'|'.$intgold_code,
                        $to_withdraw, $row['intgold_account'], $success_txt, $error_txt);
                }

                if ($row['ec'] == 3) {
                    $error_txt = ''.'Error, tried to send '.$to_withdraw.' to Perfect Money account # '.$row['perfectmoney_account'].'. Error:';
                    list ($res, $text, $batch) = send_money_to_perfectmoney(''.$perfectmoney_password.'|'.$perfectmoney_code,
                        $to_withdraw, $row['perfectmoney_account'], $success_txt, $error_txt);
                }

                if ($row['ec'] == 5) {
                    $error_txt = ''.'Error, tried to send '.$to_withdraw.' to e-Bullion account # '.$row['ebullion_account'].'. Error:';
                    list ($res, $text, $batch) = send_money_to_ebullion('', $to_withdraw, $row['ebullion_account'],
                        $success_txt, $error_txt);
                }

                if ($row['ec'] == 8) {
                    $error_txt = ''.'Error, tried to send '.$to_withdraw.' to eeeCurrency account # '.$row['eeecurrency_account'].'. Error:';
                    list ($res, $text, $batch) = send_money_to_eeecurrency(''.$eeecurrency_password.'|'.$eeecurrency_code,
                        $to_withdraw, $row['eeecurrency_account'], $success_txt, $error_txt);
                }

                if ($res == 1) {
                    $q = ''.'delete from hm2_history where id = '.$kk;
                    db_query($q);
                    $d_account = [
                        $row[egold_account],
                        $row[evocash_account],
                        $row[intgold_account],
                        '',
                        $row[stormpay_account],
                        $row[ebullion_account],
                        $row[paypal_account],
                        $row[goldmoney_account],
                        $row[eeecurrency_account]
                    ];
                    $q = 'insert into hm2_history set
              user_id = '.$row['user_id'].(''.',
              amount = -'.$amount.',
              actual_amount = -'.$amount.',
              type=\'withdrawal\',
              date = now(),
              ec = ').$row['ec'].',
              description = \'Withdrawal to account '.$d_account[$row[ec]].(''.'. Batch is '.$batch.'\'');
                    (db_query($q) OR print mysql_error());
                    $info = [];
                    $info['username'] = $row['username'];
                    $info['name'] = $row['name'];
                    $info['amount'] = sprintf('%.02f', 0 - $row['amount']);
                    $info['account'] = $d_account[$row[ec]];
                    $info['batch'] = $batch;
                    $info['currency'] = $exchange_systems[$row['ec']]['name'];
                    send_template_mail('withdraw_user_notification', $row['email'], $settings['system_email'], $info);
                    print ''.'Sent $ '.$to_withdraw.' to account'.$d_account[$row[ec]].' on '.$exchange_systems[$row['ec']]['name'].(''.'. Batch is '.$batch.'<br>');
                } else {
                    print ''.$text.'<br>';
                }

                flush();
            }
        }

        db_close($dbconn);
        exit ();
    }
}

if (($frm['a'] == 'auto-pay-settings' AND $frm['action'] == 'auto-pay-settings')) {
    if ($settings['demomode'] != 1) {
        if (($userinfo['transaction_code'] != '' AND $userinfo['transaction_code'] != $frm['alternative_passphrase'])) {
            header('Location: ?a=auto-pay-settings&say=invalid_passphrase');
            db_close($dbconn);
            exit ();
        }

        $settings['use_auto_payment'] = $frm['use_auto_payment'];
        $settings['egold_from_account'] = $frm['egold_from_account'];
        $settings['evocash_from_account'] = $frm['evocash_from_account'];
        $settings['evocash_username'] = $frm['evocash_username'];
        if ($frm['evocash_account_password'] != '') {
            $evo_pass = quote(encode_pass_for_mysql($frm['evocash_account_password']));
            $q = 'delete from hm2_pay_settings where n=\'evocash_account_password\'';
            db_query($q);
            $q = ''.'insert into hm2_pay_settings set n=\'evocash_account_password\', v=\''.$evo_pass.'\'';
            db_query($q);
        }

        if ($frm['evocash_transaction_code'] != '') {
            $evo_code = quote(encode_pass_for_mysql($frm['evocash_transaction_code']));
            $q = 'delete from hm2_pay_settings where n=\'evocash_transaction_code\'';
            db_query($q);
            $q = ''.'insert into hm2_pay_settings set n=\'evocash_transaction_code\', v=\''.$evo_code.'\'';
            db_query($q);
        }

        $settings['intgold_from_account'] = $frm['intgold_from_account'];
        if ($frm['intgold_password'] != '') {
            $intgold_pass = quote(encode_pass_for_mysql($frm['intgold_password']));
            $q = 'delete from hm2_pay_settings where n=\'intgold_password\'';
            db_query($q);
            $q = ''.'insert into hm2_pay_settings set n=\'intgold_password\', v=\''.$intgold_pass.'\'';
            db_query($q);
        }

        if ($frm['intgold_transaction_code'] != '') {
            $intgold_code = quote(encode_pass_for_mysql($frm['intgold_transaction_code']));
            $q = 'delete from hm2_pay_settings where n=\'intgold_transaction_code\'';
            db_query($q);
            $q = ''.'insert into hm2_pay_settings set n=\'intgold_transaction_code\', v=\''.$intgold_code.'\'';
            db_query($q);
        }

        $settings['eeecurrency_from_account'] = $frm['eeecurrency_from_account'];
        if ($frm['eeecurrency_password'] != '') {
            $eeecurrency_pass = quote(encode_pass_for_mysql($frm['eeecurrency_password']));
            $q = 'delete from hm2_pay_settings where n=\'eeecurrency_password\'';
            db_query($q);
            $q = ''.'insert into hm2_pay_settings set n=\'eeecurrency_password\', v=\''.$eeecurrency_pass.'\'';
            db_query($q);
        }

        if ($frm['eeecurrency_transaction_code'] != '') {
            $eeecurrency_code = quote(encode_pass_for_mysql($frm['eeecurrency_transaction_code']));
            $q = 'delete from hm2_pay_settings where n=\'eeecurrency_transaction_code\'';
            db_query($q);
            $q = ''.'insert into hm2_pay_settings set n=\'eeecurrency_transaction_code\', v=\''.$eeecurrency_code.'\'';
            db_query($q);
        }

        $settings['min_auto_withdraw'] = $frm['min_auto_withdraw'];
        $settings['max_auto_withdraw'] = $frm['max_auto_withdraw'];
        $settings['max_auto_withdraw_user'] = $frm['max_auto_withdraw_user'];
        save_settings();
        if ($frm['egold_account_password'] != '') {
            $e_pass = quote(encode_pass_for_mysql($frm['egold_account_password']));
            $q = 'delete from hm2_pay_settings where n=\'egold_account_password\'';
            db_query($q);
            $q = ''.'insert into hm2_pay_settings set n=\'egold_account_password\', v=\''.$e_pass.'\'';
            db_query($q);
        }
    }

    header('Location: ?a=auto-pay-settings&say=done');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'referal' AND $frm['action'] == 'change')) {
    if ($settings['demomode'] == 1) {
    } else {
        $q = 'delete from hm2_referal where level = 1';
        (db_query($q) OR print mysql_error());
        for ($i = 0; $i < 300; ++$i) {
            if ($frm['active'][$i] == 1) {
                $qname = quote($frm['ref_name'][$i]);
                $from = sprintf('%d', $frm['ref_from'][$i]);
                $to = sprintf('%d', $frm['ref_to'][$i]);
                $percent = sprintf('%0.2f', $frm['ref_percent'][$i]);
                $percent_daily = sprintf('%0.2f', $frm['ref_percent_daily'][$i]);
                $percent_weekly = sprintf('%0.2f', $frm['ref_percent_weekly'][$i]);
                $percent_monthly = sprintf('%0.2f', $frm['ref_percent_monthly'][$i]);
                $q = ''.'insert into hm2_referal set 
  	level = 1,
  	name= \''.$qname.'\',
  	from_value = '.$from.',
  	to_value= '.$to.',
  	percent = '.$percent.',
  	percent_daily = '.$percent_daily.',
  	percent_weekly = '.$percent_weekly.',
  	percent_monthly = '.$percent_monthly;
                (db_query($q) OR print mysql_error());
                continue;
            }
        }

        $settings['use_referal_program'] = sprintf('%d', $frm['usereferal']);
        $settings['force_upline'] = sprintf('%d', $frm['force_upline']);
        $settings['get_rand_ref'] = sprintf('%d', $frm['get_rand_ref']);
        $settings['use_active_referal'] = sprintf('%d', $frm['useactivereferal']);
        $settings['pay_active_referal'] = sprintf('%d', $frm['payactivereferal']);
        $settings['use_solid_referral_commission'] = sprintf('%d', $frm['use_solid_referral_commission']);
        $settings['solid_referral_commission_amount'] = sprintf('%.02f', $frm['solid_referral_commission_amount']);
        $settings['ref2_cms'] = sprintf('%0.2f', $frm['ref2_cms']);
        $settings['ref3_cms'] = sprintf('%0.2f', $frm['ref3_cms']);
        $settings['ref4_cms'] = sprintf('%0.2f', $frm['ref4_cms']);
        $settings['ref5_cms'] = sprintf('%0.2f', $frm['ref5_cms']);
        $settings['ref6_cms'] = sprintf('%0.2f', $frm['ref6_cms']);
        $settings['ref7_cms'] = sprintf('%0.2f', $frm['ref7_cms']);
        $settings['ref8_cms'] = sprintf('%0.2f', $frm['ref8_cms']);
        $settings['ref9_cms'] = sprintf('%0.2f', $frm['ref9_cms']);
        $settings['ref10_cms'] = sprintf('%0.2f', $frm['ref10_cms']);
        $settings['show_referals'] = sprintf('%d', $frm['show_referals']);
        $settings['show_refstat'] = sprintf('%d', $frm['show_refstat']);
        save_settings();
    }

    header('Location: ?a=referal');
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'deleterate') {
    $id = sprintf('%d', $frm['id']);
    if (($id < 3 AND $settings['demomode'] == 1)) {
    } else {
        $q = ''.'delete from hm2_types where id = '.$id;
        (db_query($q) OR print mysql_error());
        $q = ''.'delete from hm2_plans where parent = '.$id;
        (db_query($q) OR print mysql_error());
    }

    header('Location: ?a=rates');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'newsletter' AND $frm['action'] == 'newsletter')) {
    if ($frm['to'] == 'user') {
        $q = 'select * from hm2_users where username = \''.quote($frm['username']).'\'';
    } else {
        if ($frm['to'] == 'all') {
            $q = 'select * from hm2_users where id > 1';
        } else {
            if ($frm['to'] == 'active') {
                $q = 'select hm2_users.* from hm2_users, hm2_deposits where hm2_users.id > 1 and hm2_deposits.user_id = hm2_users.id group by hm2_users.id';
            } else {
                if ($frm['to'] == 'passive') {
                    $q = 'select u.* from hm2_users as u left outer join hm2_deposits as d on u.id = d.user_id where u.id > 1 and d.user_id is NULL';
                } else {
                    header('Location: ?a=newsletter&say=someerror');
                    db_close($dbconn);
                    exit ();
                }
            }
        }
    }

    ($sth = db_query($q) OR print mysql_error());
    $flag = 0;
    $total = 0;
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>HYIP Manager Pro. Auto-payment, mass payment included.</title>
<link href="images/adminstyle.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFF2" link="#666699" vlink="#666699" alink="#666699" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" >
<center>
';
    print '<br><br><br><br><br><div id=\'newsletterplace\'></div>';
    print '<div id=self_menu0></div>';
    $description = $frm['description'];
    if ($settings['demomode'] != 1) {
        set_time_limit(9999999);
        while ($row = mysql_fetch_array($sth)) {
            $flag = 1;
            ++$total;
            $mailcont = $description;
            $mailcont = ereg_replace('#username#', $row['username'], $mailcont);
            $mailcont = ereg_replace('#name#', $row['name'], $mailcont);
            $mailcont = ereg_replace('#date_register#', $row['date_register'], $mailcont);
            $mailcont = ereg_replace('#egold_account#', $row['egold_account'], $mailcont);
            $mailcont = ereg_replace('#email#', $row['email'], $mailcont);
            send_mail($row['email'], $frm['subject'], $mailcont, 'From: '.$settings['system_email'].'
Reply-To: '.$settings['system_email']);
            print '<script>var obj = document.getElementById(\'newsletterplace\');
var menulast = document.getElementById(\'self_menu'.($total - 1).'\');
menulast.style.display=\'none\';</script>';
            print ''.'<div id=\'self_menu'.$total.'\'>Just sent to '.$row[email].(''.'<br>Total '.$total.' messages sent.</div>');
            print ''.'<script>var menu = document.getElementById(\'self_menu'.$total.'\');
obj.appendChild(menu);
</script>
';
            flush();
        }
    }

    if ($flag == 1) {
    }

    db_close($dbconn);
    print ''.'<br><br><br>Sent '.$total.'.</center></body></html>';
    exit ();
}

if (($frm['a'] == 'edit_emails' AND $frm['action'] == 'update_statuses')) {
    $q = 'update hm2_emails set status = 0';
    db_query($q);
    $update_emails = $frm['emails'];
    if (is_array($update_emails)) {
        foreach ($update_emails as $email_id => $tmp) {
            $q = ''.'update hm2_emails set status = 1 where id = \''.$email_id.'\'';
            db_query($q);
        }
    }

    header('Location: ?a=edit_emails');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'send_bonuce' AND ($frm['action'] == 'send_bonuce' OR $frm['action'] == 'confirm'))) {
    $amount = sprintf('%0.2f', $frm['amount']);
    if ($amount == 0) {
        header('Location: ?a=send_bonuce&say=wrongamount');
        db_close($dbconn);
        exit ();
    }

    $deposit = intval($frm['deposit']);
    $hyip_id = intval($frm['hyip_id']);
    if ($deposit == 1) {
        $q = ''.'select * from hm2_types where id = '.$hyip_id.' and status = \'on\'';
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
        if ( ! $type) {
            header('Location: ?a=send_bonuce&say=wrongplan');
            db_close($dbconn);
            exit ();
        }
    }

    $ec = sprintf('%d', $frm['ec']);
    if ($frm['to'] == 'user') {
        $q = 'select * from hm2_users where username = \''.quote($frm['username']).'\'';
    } else {
        if ($frm['to'] == 'all') {
            $q = 'select * from hm2_users where id > 1';
        } else {
            if ($frm['to'] == 'active') {
                $q = 'select hm2_users.* from hm2_users, hm2_deposits where hm2_users.id > 1 and hm2_deposits.user_id = hm2_users.id group by hm2_users.id';
            } else {
                if ($frm['to'] == 'passive') {
                    $q = 'select u.* from hm2_users as u left outer join hm2_deposits as d on u.id = d.user_id where u.id > 1 and d.user_id is NULL';
                } else {
                    header('Location: ?a=send_bonuce&say=someerror');
                    db_close($dbconn);
                    exit ();
                }
            }
        }
    }

    session_start();
    if ($frm['action'] == 'send_bonuce') {
        $code = substr($_SESSION['code'], 23, -32);
        if ($code === md5($frm['code'])) {
            $sth = db_query($q);
            $flag = 0;
            $total = 0;
            $description = quote($frm['description']);
            while ($row = mysql_fetch_array($sth)) {
                $flag = 1;
                $total += $amount;
                $q = 'insert into hm2_history set
    	user_id = '.$row['id'].(''.',
    	amount = '.$amount.',
    	description = \''.$description.'\',
    	type=\'bonus\',
    	actual_amount = '.$amount.',
    	ec = '.$ec.',
    	date = now()');
                (db_query($q) OR print mysql_error());
                if ($deposit) {
                    $delay = $type['delay'] - 1;
                    if ($delay < 0) {
                        $delay = 0;
                    }

                    $user_id = $row['id'];
                    $q = ''.'insert into hm2_deposits set
               user_id = '.$user_id.',
               type_id = '.$hyip_id.',
               deposit_date = now(),
               last_pay_date = now()+ interval '.$delay.' day,
               status = \'on\',
               q_pays = 0,
               amount = \''.$amount.'\',
               actual_amount = \''.$amount.'\',
               ec = '.$ec.'
               ';
                    (db_query($q) OR print mysql_error());
                    $deposit_id = mysql_insert_id();
                    $q = ''.'insert into hm2_history set 
               user_id = '.$user_id.',
               amount = \'-'.$amount.'\',
               type = \'deposit\',
               description = \'Deposit to '.quote($type['name']).(''.'\',
               actual_amount = -'.$amount.',
               ec = '.$ec.',
               date = now(),
             deposit_id = '.$deposit_id.'
               ');
                    (db_query($q) OR print mysql_error());
                    if ($settings['banner_extension'] == 1) {
                        $imps = 0;
                        if (0 < $settings['imps_cost']) {
                            $imps = $amount * 1000 / $settings['imps_cost'];
                        }

                        if (0 < $imps) {
                            $q = ''.'update hm2_users set imps = imps + '.$imps.' where id = '.$user_id;
                            (db_query($q) OR print mysql_error());
                            continue;
                        }

                        continue;
                    }

                    continue;
                }
            }

            if ($flag == 1) {
                header(''.'Location: ?a=send_bonuce&say=send&total='.$total);
            } else {
                header('Location: ?a=send_bonuce&say=notsend');
            }

            $_SESSION['code'] = '';
            db_close($dbconn);
            exit ();
        } else {
            header('Location: ?a=send_bonuce&say=invalid_code');
            db_close($dbconn);
            exit ();
        }
    }

    $code = '';
    if ($frm['action'] == 'confirm') {
        $account = preg_split('/,/', $frm['conf_email']);
        $conf_email = array_pop($account);
        $frm_env['HTTP_HOST'] = preg_replace('/www\\./', '', $frm_env['HTTP_HOST']);
        $conf_email .= ''.'@'.$frm_env['HTTP_HOST'];
        $code = get_rand_md5(8);
        send_mail($conf_email, 'Bonus Confirmation Code', ''.'Code is: '.$code, ''.'From: '.$settings['system_email'].'
Reply-To: '.$settings['system_email']);
        $code = get_rand_md5(23).md5($code).get_rand_md5(32);
        $_SESSION['code'] = $code;
    }
}

if (($frm['a'] == 'send_penality' AND $frm['action'] == 'send_penality')) {
    $amount = sprintf('%0.2f', abs($frm['amount']));
    if ($amount == 0) {
        header('Location: ?a=send_penality&say=wrongamount');
        db_close($dbconn);
        exit ();
    }

    $ec = sprintf('%d', $frm['ec']);
    if ($frm['to'] == 'user') {
        $q = 'select * from hm2_users where username = \''.quote($frm['username']).'\'';
    } else {
        if ($frm['to'] == 'all') {
            $q = 'select * from hm2_users where id > 1';
        } else {
            if ($frm['to'] == 'active') {
                $q = 'select hm2_users.* from hm2_users, hm2_deposits where hm2_users.id > 1 and hm2_deposits.user_id = hm2_users.id group by hm2_users.id';
            } else {
                if ($frm['to'] == 'passive') {
                    $q = 'select u.* from hm2_users as u left outer join hm2_deposits as d on u.id = d.user_id where u.user_id > 1 and d.user_id is NULL';
                } else {
                    header('Location: ?a=send_penality&say=someerror');
                    db_close($dbconn);
                    exit ();
                }
            }
        }
    }

    $sth = db_query($q);
    $flag = 0;
    $total = 0;
    $description = quote($frm['description']);
    while ($row = mysql_fetch_array($sth)) {
        $flag = 1;
        $total += $amount;
        $q = 'insert into hm2_history set
	user_id = '.$row['id'].(''.',
	amount = -'.$amount.',
	description = \''.$description.'\',
	type=\'penality\',
	actual_amount = -'.$amount.',
	ec = '.$ec.',
	date = now()');
        (db_query($q) OR print mysql_error());
    }

    if ($flag == 1) {
        header(''.'Location: ?a=send_penality&say=send&total='.$total);
    } else {
        header('Location: ?a=send_penality&say=notsend');
    }

    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'info_box' AND $frm['action'] == 'info_box')) {
    if ($settings['demomode'] != 1) {
        $settings['show_info_box'] = sprintf('%d', $frm['show_info_box']);
        $settings['show_info_box_started'] = sprintf('%d', $frm['show_info_box_started']);
        $settings['show_info_box_running_days'] = sprintf('%d', $frm['show_info_box_running_days']);
        $settings['show_info_box_total_accounts'] = sprintf('%d', $frm['show_info_box_total_accounts']);
        $settings['show_info_box_active_accounts'] = sprintf('%d', $frm['show_info_box_active_accounts']);
        $settings['show_info_box_vip_accounts'] = sprintf('%d', $frm['show_info_box_vip_accounts']);
        $settings['vip_users_deposit_amount'] = sprintf('%d', $frm['vip_users_deposit_amount']);
        $settings['show_info_box_deposit_funds'] = sprintf('%d', $frm['show_info_box_deposit_funds']);
        $settings['show_info_box_today_deposit_funds'] = sprintf('%d', $frm['show_info_box_today_deposit_funds']);
        $settings['show_info_box_total_withdraw'] = sprintf('%d', $frm['show_info_box_total_withdraw']);
        $settings['show_info_box_visitor_online'] = sprintf('%d', $frm['show_info_box_visitor_online']);
        $settings['show_info_box_members_online'] = sprintf('%d', $frm['show_info_box_members_online']);
        $settings['show_info_box_newest_member'] = sprintf('%d', $frm['show_info_box_newest_member']);
        $settings['show_info_box_last_update'] = sprintf('%d', $frm['show_info_box_last_update']);
        $settings['show_kitco_dollar_per_ounce_box'] = sprintf('%d', $frm['show_kitco_dollar_per_ounce_box']);
        $settings['show_kitco_euro_per_ounce_box'] = sprintf('%d', $frm['show_kitco_euro_per_ounce_box']);
        $settings['show_stats_box'] = sprintf('%d', $frm['show_stats_box']);
        $settings['show_members_stats'] = sprintf('%d', $frm['show_members_stats']);
        $settings['show_paidout_stats'] = sprintf('%d', $frm['show_paidout_stats']);
        $settings['show_top10_stats'] = sprintf('%d', $frm['show_top10_stats']);
        $settings['show_last10_stats'] = sprintf('%d', $frm['show_last10_stats']);
        $settings['show_refs10_stats'] = sprintf('%d', $frm['show_refs10_stats']);
        $settings['refs10_start_date'] = sprintf('%04d-%02d-%02d', substr($frm['refs10_start_date'], 0, 4),
            substr($frm['refs10_start_date'], 5, 2), substr($frm['refs10_start_date'], 8, 2));
        $settings['show_news_box'] = sprintf('%d', $frm['show_news_box']);
        $settings['last_news_count'] = sprintf('%d', $frm['last_news_count']);
        save_settings();
    }
}

if (($frm['a'] == 'settings' AND $frm['action'] == 'settings')) {
    if ($settings['demomode'] == 1) {
    } else {
        if (($userinfo['transaction_code'] != '' AND $userinfo['transaction_code'] != $frm['alternative_passphrase'])) {
            header('Location: ?a=settings&say=invalid_passphrase');
            db_close($dbconn);
            exit ();
        }

        if ($frm['admin_stat_password'] == '') {
            $q = 'update hm2_users set stat_password = \'\' where id = 1';
            db_query($q);
        } else {
            if ($frm['admin_stat_password'] != '*****') {
                $sp = md5($frm['admin_stat_password']);
                $q = ''.'update hm2_users set stat_password = \''.$sp.'\' where id = 1';
                db_query($q);
            }
        }

        $settings['site_name'] = $frm['site_name'];
        $settings['reverse_columns'] = sprintf('%d', $frm['reverse_columns']);
        $settings['site_start_day'] = $frm['site_start_day'];
        $settings['site_start_month'] = $frm['site_start_month'];
        $settings['site_start_year'] = $frm['site_start_year'];
        $settings['deny_registration'] = ($frm['deny_registration'] ? 1 : 0);

        $settings['def_payee_account_perfectmoney'] = $frm['def_payee_account_perfectmoney'];
        $settings['def_payee_name_perfectmoney'] = $frm['def_payee_name_perfectmoney'];
        $settings['md5altphrase_perfectmoney'] = $frm['md5altphrase_perfectmoney'];

        $settings['def_payee_account_payeer'] = $frm['def_payee_account_payeer'];
        $settings['def_payee_key_payeer'] = $frm['def_payee_key_payeer'];
        $settings['def_payee_additionalkey_payeer'] = $frm['def_payee_additionalkey_payeer'];
        
        $settings['def_payee_account_bitcoin'] = $frm['def_payee_account_bitcoin'];
        $settings['def_payee_qrcode_bitcoin'] = $frm['def_payee_qrcode_bitcoin'];

        $settings['def_payee_account'] = $frm['def_payee_account'];
        $settings['def_payee_name'] = $frm['def_payee_name'];
        $settings['md5altphrase'] = $frm['md5altphrase'];

        $settings['def_payee_account_evocash'] = $frm['def_payee_account_evocash'];
        $settings['md5altphrase_evocash'] = $frm['md5altphrase_evocash'];

        $settings['def_payee_account_intgold'] = $frm['def_payee_account_intgold'];
        $settings['md5altphrase_intgold'] = $frm['md5altphrase_intgold'];
        $settings['intgold_posturl'] = sprintf('%d', $frm['intgold_posturl']);

        $settings['use_opt_in'] = sprintf('%d', $frm['use_opt_in']);
        $settings['opt_in_email'] = $frm['opt_in_email'];
        $settings['system_email'] = $frm['system_email'];

        $settings['usercanchangeegoldacc'] = sprintf('%d', $frm['usercanchangeegoldacc']);
        $settings['usercanchangeperfectmoneyacc'] = sprintf('%d', $frm['usercanchangeperfectmoneyacc']);
        $settings['usercanchangeemail'] = sprintf('%d', $frm['usercanchangeemail']);

        $settings['sendnotify_when_userinfo_changed'] = sprintf('%d', $frm['sendnotify_when_userinfo_changed']);
        $settings['graph_validation'] = sprintf('%d', $frm['graph_validation']);
        $settings['graph_max_chars'] = $frm['graph_max_chars'];
        $settings['graph_text_color'] = $frm['graph_text_color'];
        $settings['graph_bg_color'] = $frm['graph_bg_color'];
        $settings['use_number_validation_number'] = sprintf('%d', $frm['use_number_validation_number']);
        $settings['advanced_graph_validation'] = ($frm['advanced_graph_validation'] ? 1 : 0);
        if ( ! function_exists('imagettfbbox')) {
            $settings['advanced_graph_validation'] = 0;
        }

        $settings['advanced_graph_validation_min_font_size'] = sprintf('%d',
            $frm['advanced_graph_validation_min_font_size']);
        $settings['advanced_graph_validation_max_font_size'] = sprintf('%d',
            $frm['advanced_graph_validation_max_font_size']);
        $settings['enable_calculator'] = $frm['enable_calculator'];
        $settings['accesswap'] = sprintf('%d', $frm['usercanaccesswap']);
        $settings['time_dif'] = $frm['time_dif'];
        $settings['internal_transfer_enabled'] = ($frm['internal_transfer_enabled'] ? 1 : 0);

        $settings['def_payee_account_stormpay'] = $frm['def_payee_account_stormpay'];
        $settings['md5altphrase_stormpay'] = $frm['md5altphrase_stormpay'];
        $settings['stormpay_posturl'] = $frm['stormpay_posturl'];
        $settings['dec_stormpay_fee'] = sprintf('%d', $frm['dec_stormpay_fee']);

        $settings['def_payee_account_paypal'] = $frm['def_payee_account_paypal'];

        $settings['def_payee_account_goldmoney'] = $frm['def_payee_account_goldmoney'];
        $settings['md5altphrase_goldmoney'] = $frm['md5altphrase_goldmoney'];

        $settings['def_payee_account_eeecurrency'] = $frm['def_payee_account_eeecurrency'];
        $settings['md5altphrase_eeecurrency'] = $frm['md5altphrase_eeecurrency'];
        $settings['eeecurrency_posturl'] = sprintf('%d', $frm['eeecurrency_posturl']);

        $settings['gpg_path'] = $frm['gpg_path'];
        $atip_pl = $_FILES['atip_pl'];
        if ((0 < $atip_pl['size'] AND $atip_pl['error'] == 0)) {
            $fp = fopen($atip_pl['tmp_name'], 'r');
            while ( ! feof($fp)) {
                $buf = fgets($fp, 4096);
                if (preg_match('/my\\s+\\(\\$account\\)\\s+\\=\\s+\'([^\']+)\'/', $buf, $matches)) {
                    $frm['def_payee_account_ebullion'] = $matches[1];
                }

                if (preg_match('/my\\s+\\(\\$passphrase\\)\\s+\\=\\s+\'([^\']+)\'/', $buf, $matches)) {
                    $frm['md5altphrase_ebullion'] = $matches[1];
                    continue;
                }
            }

            fclose($fp);
            unlink($atip_pl['tmp_name']);
        }

        $status_php = $_FILES['status_php'];
        if ((0 < $status_php['size'] AND $status_php['error'] == 0)) {
            $fp = fopen($status_php['tmp_name'], 'r');
            while ( ! feof($fp)) {
                $buf = fgets($fp, 4096);
                if (preg_match('/\\$eb_keyID\\s+\\=\\s+\'([^\']+)\'/', $buf, $matches)) {
                    $frm['ebullion_keyID'] = $matches[1];
                    continue;
                }
            }

            fclose($fp);
            unlink($status_php['tmp_name']);
        }

        $pubring_gpg = $_FILES['pubring_gpg'];
        if ((0 < $pubring_gpg['size'] AND $pubring_gpg['error'] == 0)) {
            copy($pubring_gpg['tmp_name'], './tmpl_c/pubring.gpg');
            unlink($pubring_gpg['tmp_name']);
        }

        $secring_gpg = $_FILES['secring_gpg'];
        if ((0 < $secring_gpg['size'] AND $secring_gpg['error'] == 0)) {
            copy($secring_gpg['tmp_name'], './tmpl_c/secring.gpg');
            unlink($secring_gpg['tmp_name']);
        }

        $settings['def_payee_account_ebullion'] = $frm['def_payee_account_ebullion'];
        $settings['def_payee_name_ebullion'] = $frm['def_payee_name_ebullion'];
        $settings['md5altphrase_ebullion'] = encode_pass_for_mysql($frm['md5altphrase_ebullion']);
        $settings['ebullion_keyID'] = $frm['ebullion_keyID'];
        $settings['brute_force_handler'] = ($frm['brute_force_handler'] ? 1 : 0);
        $settings['brute_force_max_tries'] = sprintf('%d', abs($frm['brute_force_max_tries']));
        $settings['redirect_to_https'] = ($frm['redirect_to_https'] ? 1 : 0);
        $settings['use_user_location'] = ($frm['use_user_location'] ? 1 : 0);
        $settings['use_transaction_code'] = ($frm['use_transaction_code'] ? 1 : 0);
        $settings['min_user_password_length'] = sprintf('%d', $frm['min_user_password_length']);
        $settings['use_history_balance_mode'] = ($frm['use_history_balance_mode'] ? 1 : 0);
        $settings['account_update_confirmation'] = ($frm['account_update_confirmation'] ? 1 : 0);
        $settings['withdrawal_fee'] = sprintf('%.02f', $frm['withdrawal_fee']);
        if ($settings['withdrawal_fee'] < 0) {
            $settings['withdrawal_fee'] = '0.00';
        }

        if (100 < $settings['withdrawal_fee']) {
            $settings['withdrawal_fee'] = '100.00';
        }

        $settings['withdrawal_fee_min'] = sprintf('%.02f', $frm['withdrawal_fee_min']);
        $settings['min_withdrawal_amount'] = sprintf('%.02f', $frm['min_withdrawal_amount']);
        $settings[max_daily_withdraw] = sprintf('%0.2f', $frm[max_daily_withdraw]);
        $settings['use_add_funds'] = ($frm['use_add_funds'] ? 1 : 0);
        $login = quote($frm['admin_login']);
        $pass = quote($frm['admin_password']);
        $email = quote($frm['admin_email']);

        if (($login != '' AND $email != '')) {
            $q = ''.'update hm2_users set email=\''.$email.'\', username=\''.$login.'\' where id = 1';
            (db_query($q) OR print mysql_error());
        }

        if ($pass != '') {
            $md_pass = md5($pass);
            $q = ''.'update hm2_users set password = \''.$md_pass.'\' where id = 1';
            (db_query($q) OR print mysql_error());
        }

        if (($frm['use_alternative_passphrase'] == 1 AND $frm['new_alternative_passphrase'] != '')) {
            $altpass = quote($frm['new_alternative_passphrase']);
            $q = ''.'update hm2_users set transaction_code = \''.$altpass.'\' where id = 1';
            (db_query($q) OR print mysql_error());
        }

        if ($frm['use_alternative_passphrase'] == 0) {
            $q = 'update hm2_users set transaction_code = \'\' where id = 1';
            (db_query($q) OR print mysql_error());
        }

        save_settings();
    }

    header('Location: ?a=settings&say=done');
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'rm_withdraw') {
    $id = sprintf('%d', $frm['id']);
    $q = ''.'delete from hm2_history where id = '.$id;
    (db_query($q) OR print mysql_error());
    header('Location: ?a=thistory&ttype=withdraw_pending');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'releasedeposits' AND $frm['action'] == 'releasedeposits')) {
    $u_id = sprintf('%d', $frm['u_id']);
    $type_ids = $frm['type_id'];
    while (list ($kk, $vv) = each($type_ids)) {
        $kk = intval($kk);
        $vv = intval($vv);
        $q = ''.'select compound, actual_amount from hm2_deposits where id = '.$kk;
        ($sth = db_query($q) OR print mysql_error());
        $row = mysql_fetch_array($sth);
        $compound = $row['compound'];
        $amount = $row['actual_amount'];
        $q = ''.'select * from hm2_types where id = '.$vv;
        ($sth = db_query($q) OR print mysql_error());
        $type = mysql_fetch_array($sth);
        if ($type['use_compound'] == 0) {
            $compound = 0;
        } else {
            if ($type['compound_max_deposit'] == 0) {
                $type['compound_max_deposit'] = $amount + 1;
            }

            if (($type['compound_min_deposit'] <= $amount AND $amount <= $type['compound_max_deposit'])) {
                if ($type['compound_percents_type'] == 1) {
                    $cps = preg_split('/\\s*,\\s*/', $type['compound_percents']);
                    if ( ! in_array($compound, $cps)) {
                        $compound = $cps[0];
                    }
                } else {
                    if ($compound < $type['compound_min_percent']) {
                        $compound = $type['compound_min_percent'];
                    }

                    if ($type['compound_max_percent'] < $compound) {
                        $compound = $type['compound_max_percent'];
                    }
                }
            } else {
                $compound = 0;
            }
        }

        $q = ''.'update hm2_deposits set type_id = '.$vv.', compound = '.$compound.' where id = '.$kk;
        (db_query($q) OR print mysql_error());
    }

    $releases = $frm['release'];
    while (list ($kk, $vv) = each($releases)) {
        if ($vv == 0) {
            continue;
        }

        $q = ''.'select actual_amount, ec from hm2_deposits where id = '.$kk;
        ($sth = db_query($q) OR print mysql_errstr());
        if ($row = mysql_fetch_array($sth)) {
            $release_deposit = sprintf('%-.2f', $vv);
            if ($release_deposit <= $row['actual_amount']) {
                $q = ''.'insert into hm2_history set 
    		user_id = '.$u_id.',
	    	amount = '.$release_deposit.', 
    		type = \'early_deposit_release\',
	    	actual_amount = '.$release_deposit.',
        ec = '.$row['ec'].',
	    	date = now()';
                (db_query($q) OR print mysql_error());
                $dif = floor(($row['actual_amount'] - $release_deposit) * 100) / 100;
                if ($dif == 0) {
                    $q = ''.'update hm2_deposits set actual_amount = 0, amount = 0, status = \'off\' where id = '.$kk;
                } else {
                    $q = ''.'update hm2_deposits set actual_amount = actual_amount - '.$release_deposit.' where id = '.$kk;
                }

                (db_query($q) OR print mysql_error());
                continue;
            }

            continue;
        }
    }

    header(''.'Location: ?a=releasedeposits&u_id='.$u_id);
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'addbonuse' AND ($frm['action'] == 'addbonuse' OR $frm['action'] == 'confirm'))) {
    $deposit = intval($frm['deposit']);
    $hyip_id = intval($frm['hyip_id']);
    if ($deposit == 1) {
        $q = ''.'select * from hm2_types where id = '.$hyip_id.' and status = \'on\'';
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
        if ( ! $type) {
            header('Location: ?a=send_bonuce&say=wrongplan');
            db_close($dbconn);
            exit ();
        }
    }

    session_start();
    if ($frm['action'] == 'addbonuse') {
        $code = substr($_SESSION['code'], 23, -32);
        if ($code === md5($frm['code'])) {
            $id = sprintf('%d', $frm['id']);
            $amount = sprintf('%f', $frm['amount']);
            $description = quote($frm['desc']);
            $ec = sprintf('%d', $frm['ec']);
            $q = ''.'insert into hm2_history set
              user_id = '.$id.',
              amount = '.$amount.',
              ec = '.$ec.',
              actual_amount = '.$amount.',
              type = \'bonus\',
              date = now(),
              description = \''.$description.'\'';
            if ( ! (db_query($q))) {
                exit (mysql_error());;
            }

            if ($deposit) {
                $delay = $type['delay'] - 1;
                if ($delay < 0) {
                    $delay = 0;
                }

                $user_id = $id;
                $q = ''.'insert into hm2_deposits set
             user_id = '.$user_id.',
             type_id = '.$hyip_id.',
             deposit_date = now(),
             last_pay_date = now()+ interval '.$delay.' day,
             status = \'on\',
             q_pays = 0,
             amount = \''.$amount.'\',
             actual_amount = \''.$amount.'\',
             ec = '.$ec.'
             ';
                (db_query($q) OR print mysql_error());
                $deposit_id = mysql_insert_id();
                $q = ''.'insert into hm2_history set 
             user_id = '.$user_id.',
             amount = \'-'.$amount.'\',
             type = \'deposit\',
             description = \'Deposit to '.quote($type['name']).(''.'\',
             actual_amount = -'.$amount.',
             ec = '.$ec.',
             date = now(),
           deposit_id = '.$deposit_id.'
             ');
                (db_query($q) OR print mysql_error());
                if ($settings['banner_extension'] == 1) {
                    $imps = 0;
                    if (0 < $settings['imps_cost']) {
                        $imps = $amount * 1000 / $settings['imps_cost'];
                    }

                    if (0 < $imps) {
                        $q = ''.'update hm2_users set imps = imps + '.$imps.' where id = '.$user_id;
                        (db_query($q) OR print mysql_error());
                    }
                }
            }

            if ($frm['inform'] == 1) {
                $q = ''.'select * from hm2_users where id = '.$id;
                $sth = db_query($q);
                $row = mysql_fetch_array($sth);
                $info = [];
                $info['name'] = $row['username'];
                $info['amount'] = number_format($amount, 2);
                send_template_mail('bonus', $row['email'], $settings['system_email'], $info);
            }

            header(''.'Location: ?a=addbonuse&say=done&id='.$id);
            db_close($dbconn);
            exit ();
        } else {
            $id = sprintf('%d', $frm['id']);
            header(''.'Location: ?a=addbonuse&id='.$id.'&say=invalid_code');
            db_close($dbconn);
            exit ();
        }
    }

    $code = '';
    if ($frm['action'] == 'confirm') {
        $account = preg_split('/,/', $frm['conf_email']);
        $conf_email = array_pop($account);
        $frm_env['HTTP_HOST'] = preg_replace('/www\\./', '', $frm_env['HTTP_HOST']);
        $conf_email .= ''.'@'.$frm_env['HTTP_HOST'];
        $code = get_rand_md5(8);
        send_mail($conf_email, 'Bonus Confirmation Code', ''.'Code is: '.$code, ''.'From: '.$settings['system_email'].'
Reply-To: '.$settings['system_email']);
        $code = get_rand_md5(23).md5($code).get_rand_md5(32);
        $_SESSION['code'] = $code;
    }
}

if (($frm['a'] == 'addpenality' AND $frm['action'] == 'addpenality')) {
    $id = sprintf('%d', $frm['id']);
    $amount = sprintf('%f', abs($frm['amount']));
    $description = quote($frm['desc']);
    $ec = sprintf('%d', $frm['ec']);
    $q = ''.'insert into hm2_history set
	user_id = '.$id.',
	amount = -'.$amount.',
	actual_amount = -'.$amount.',
	ec = '.$ec.',
	type = \'penality\',
	date = now(),
	description = \''.$description.'\'';
    if ( ! (db_query($q))) {
        exit (mysql_error());;
    }

    if ($frm['inform'] == 1) {
        $q = ''.'select * from hm2_users where id = '.$id;
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $info = [];
        $info['name'] = $row['username'];
        $info['amount'] = number_format($amount, 2);
        send_template_mail('penalty', $row['email'], $settings['system_email'], $info);
    }

    header(''.'Location: ?a=addpenality&say=done&id='.$id);
    db_close($dbconn);
    exit ();
}

if ($frm['a'] == 'deleteaccount') {
    $id = sprintf('%d', $frm['id']);
    $q = ''.'delete from hm2_users where id = '.$id.' and id <> 1';
    db_query($q);
    header('Location: ?a=members&q='.$frm['q'].'&p='.$frm['p'].'&status='.$frm['status']);
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'editaccount' AND $frm['action'] == 'editaccount')) {
    $id = sprintf('%d', $frm['id']);
    if ((($settings['demomode'] == 1 AND $id <= 3) AND 0 < $id)) {
        header('Location: ?a=editaccount&id='.$frm['id']);
        db_close($dbconn);
        exit ();
    }

    $username = quote($frm['username']);
    $q = ''.'select * from hm2_users where id <> '.$id.' and username = \''.$username.'\'';
    $sth = db_query($q);
    ($row = mysql_fetch_array($sth) OR print mysql_error());
    if ($row) {
        header('Location: ?a=editaccount&say=userexists&id='.$frm['id']);
        db_close($dbconn);
        exit ();
    }

    if (($frm['password'] != '' AND $frm['password'] != $frm['password2'])) {
        header('Location: ?a=editaccount&say=incorrect_password&id='.$frm['id']);
        db_close($dbconn);
        exit ();
    }

    if (($frm['transaction_code'] != '' AND $frm['transaction_code'] != $frm['transaction_code2'])) {
        header('Location: ?a=editaccount&say=incorrect_transaction_code&id='.$frm['id']);
        db_close($dbconn);
        exit ();
    }

    if ($id == 0) {
        $name = quote($frm['fullname']);
        $username = quote($frm['username']);
        $password = md5(quote($frm['password']));
        $egold = quote($frm['egold']);
        $perfectmoney = quote($frm['perfectmoney']);
        $evocash = quote($frm['evocash']);
        $intgold = quote($frm['intgold']);
        $stormpay = quote($frm['stormpay']);
        $ebullion = quote($frm['ebullion']);
        $paypal = quote($frm['paypal']);
        $goldmoney = quote($frm['goldmoney']);
        $eeecurrency = quote($frm['eeecurrency']);
        $email = quote($frm['email']);
        $status = quote($frm['status']);
        $auto_withdraw = sprintf('%d', $frm['auto_withdraw']);
        $admin_auto_pay_earning = sprintf('%d', $frm['admin_auto_pay_earning']);
        $pswd = '';
        if ($settings['store_uncrypted_password'] == 1) {
            $pswd = quote($frm['password']);
        }

        $q = ''.'insert into hm2_users set
  	name = \''.$name.'\',
  	username = \''.$username.'\',
	password = \''.$password.'\',
    egold_account = \''.$egold.'\',
  	perfectmoney_account = \''.$perfectmoney.'\',
	evocash_account = \''.$evocash.'\',
	intgold_account = \''.$intgold.'\',
	stormpay_account = \''.$stormpay.'\',
	ebullion_account = \''.$ebullion.'\',
	paypal_account = \''.$paypal.'\',
	goldmoney_account = \''.$goldmoney.'\',
	eeecurrency_account = \''.$eeecurrency.'\',
  	email = \''.$email.'\',
  	status = \''.$status.'\',
    auto_withdraw = '.$auto_withdraw.',
    admin_auto_pay_earning = '.$admin_auto_pay_earning.',
    user_auto_pay_earning = '.$admin_auto_pay_earning.',
    pswd = \''.$pswd.'\',
    date_register = now()';
        (db_query($q) OR print mysql_error());
        $frm['id'] = mysql_insert_id();
    } else {
        $q = ''.'select * from hm2_users where id = '.$id;
        $sth = db_query($q);
        ($row = mysql_fetch_array($sth) OR print mysql_error());
        $name = quote($frm['fullname']);
        $address = quote($frm['address']);
        $city = quote($frm['city']);
        $state = quote($frm['state']);
        $zip = quote($frm['zip']);
        $country = quote($frm['country']);
        $edit_location = '';
        if ($settings['use_user_location']) {
            $edit_location = ''.'address = \''.$address.'\',
                        city = \''.$city.'\',
                        state = \''.$state.'\',
                        zip = \''.$zip.'\',
                        country = \''.$country.'\',
                       ';
        }

        $username = quote($frm['username']);
        $password = quote($frm['password']);
        $transaction_code = quote($frm['transaction_code']);
        $egold = quote($frm['egold']);
        $evocash = quote($frm['evocash']);
        $intgold = quote($frm['intgold']);
        $stormpay = quote($frm['stormpay']);
        $ebullion = quote($frm['ebullion']);
        $paypal = quote($frm['paypal']);
        $goldmoney = quote($frm['goldmoney']);
        $eeecurrency = quote($frm['eeecurrency']);
        $email = quote($frm['email']);
        $status = quote($frm['status']);
        $auto_withdraw = sprintf('%d', $frm['auto_withdraw']);
        $admin_auto_pay_earning = sprintf('%d', $frm['admin_auto_pay_earning']);
        $user_auto_pay_earning = $row['user_auto_pay_earning'];
        if (($row['admin_auto_pay_earning'] == 0 AND $admin_auto_pay_earning == 1)) {
            $user_auto_pay_earning = 1;
        }

        $q = ''.'update hm2_users set 
  	name = \''.$name.'\',
    '.$edit_location.'
  	username = \''.$username.'\',
  	egold_account = \''.$egold.'\',
	evocash_account = \''.$evocash.'\',
	intgold_account = \''.$intgold.'\',
	stormpay_account = \''.$stormpay.'\',
	ebullion_account = \''.$ebullion.'\',
	paypal_account = \''.$paypal.'\',
	goldmoney_account = \''.$goldmoney.'\',
	eeecurrency_account = \''.$eeecurrency.'\',
  	email = \''.$email.'\',
  	status = \''.$status.'\',
    auto_withdraw = '.$auto_withdraw.',
    admin_auto_pay_earning = '.$admin_auto_pay_earning.',
    user_auto_pay_earning = '.$user_auto_pay_earning.'
  	where id = '.$id.' and id <> 1';
        (db_query($q) OR print mysql_error());
        if ($password != '') {
            $pswd = quote($password);
            $password = md5($password);
            $q = ''.'update hm2_users set password = \''.$password.'\' where id = '.$id.' and id <> 1';
            (db_query($q) OR print mysql_error());
            if ($settings['store_uncrypted_password'] == 1) {
                $q = ''.'update hm2_users set pswd = \''.$pswd.'\' where id = '.$id.' and id <> 1';
                (db_query($q) OR print mysql_error());
            }
        }

        if ($transaction_code != '') {
            $pswd = quote($password);
            $password = md5($password);
            $q = ''.'update hm2_users set transaction_code = \''.$transaction_code.'\' where id = '.$id.' and id <> 1';
            (db_query($q) OR print mysql_error());
        }

        if ($frm['activate']) {
            $q = ''.'update hm2_users set activation_code = \'\', bf_counter = 0 where id = '.$id.' and id <> 1';
            (db_query($q) OR print mysql_error());
        }
    }

    header('Location: ?a=editaccount&id='.$frm['id'].'&say=saved');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'members' AND $frm['action'] == 'modify_status')) {
    if ($settings['demomode'] != 1) {
        $active = $frm['active'];
        while (list ($id, $status) = each($active)) {
            $qstatus = quote($status);
            $q = ''.'update hm2_users set status = \''.$qstatus.'\' where id = '.$id;
            (db_query($q) OR print mysql_error());
        }
    }

    header('Location: ?a=members');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'members' AND $frm['action'] == 'activate')) {
    $active = $frm['activate'];
    while (list ($id, $status) = each($active)) {
        $q = ''.'update hm2_users set activation_code = \'\', bf_counter = 0 where id = '.$id;
        (db_query($q) OR print mysql_error());
    }

    header('Location: ?a=members&status=blocked');
    db_close($dbconn);
    exit ();
}

if ($frm['action'] == 'add_hyip') {
    $q_days = sprintf('%d', $frm['hq_days']);
    if ($frm['hq_days_nolimit'] == 1) {
        $q_days = 0;
    }

    $min_deposit = sprintf('%0.2f', $frm['hmin_deposit']);
    $max_deposit = sprintf('%0.2f', $frm['hmax_deposit']);
    $return_profit = sprintf('%d', $frm['hreturn_profit']);
    $return_profit_percent = sprintf('%d', $frm['hreturn_profit_percent']);
    $percent = sprintf('%0.2f', $frm['hpercent']);
    $pay_to_egold_directly = sprintf('%d', $frm['earning_to_egold']);
    $use_compound = sprintf('%d', $frm['use_compound']);
    $work_week = sprintf('%d', $frm['work_week']);
    $parent = sprintf('%d', $frm['parent']);
    $desc = quote($frm_orig[plan_description]);
    $withdraw_principal = sprintf('%d', $frm['withdraw_principal']);
    $withdraw_principal_percent = sprintf('%.02f', $frm['withdraw_principal_percent']);
    $withdraw_principal_duration = sprintf('%d', $frm['withdraw_principal_duration']);
    $withdraw_principal_duration_max = sprintf('%d', $frm['withdraw_principal_duration_max']);
    $compound_min_deposit = sprintf('%.02f', $frm['compound_min_deposit']);
    $compound_max_deposit = sprintf('%.02f', $frm['compound_max_deposit']);
    $compound_percents_type = sprintf('%d', $frm['compound_percents_type']);
    $compound_min_percent = sprintf('%.02f', $frm['compound_min_percent']);
    if (($compound_min_percent < 0 OR 100 < $compound_min_percent)) {
        $compound_min_percent = 0;
    }

    $compound_max_percent = sprintf('%.02f', $frm['compound_max_percent']);
    if (($compound_max_percent < 0 OR 100 < $compound_max_percent)) {
        $compound_max_percent = 100;
    }

    $cps = preg_split('/\\s*,\\s*/', $frm['compound_percents']);
    $cps1 = [];
    foreach ($cps as $cp) {
        if ((( ! in_array($cp, $cps1) AND 0 <= $cp) AND $cp <= 100)) {
            array_push($cps1, sprintf('%d', $cp));
            continue;
        }
    }

    sort($cps1);
    $compound_percents = join(',', $cps1);
    $hold = sprintf('%d', $frm[hold]);
    $delay = sprintf('%d', $frm[delay]);
    $q = 'insert into hm2_types set
	name=\''.quote($frm['hname']).(''.'\',
	q_days = '.$q_days.',
	period = \'').quote($frm['hperiod']).'\',
	status = \''.quote($frm['hstatus']).(''.'\',
	return_profit = \''.$return_profit.'\',
	return_profit_percent = '.$return_profit_percent.',
	pay_to_egold_directly = '.$pay_to_egold_directly.',
	use_compound = '.$use_compound.',
	work_week = '.$work_week.',
	parent = '.$parent.',
	withdraw_principal = '.$withdraw_principal.',
	withdraw_principal_percent = '.$withdraw_principal_percent.',
	withdraw_principal_duration = '.$withdraw_principal_duration.',
	withdraw_principal_duration_max = '.$withdraw_principal_duration_max.',
	compound_min_deposit = '.$compound_min_deposit.',
	compound_max_deposit = '.$compound_max_deposit.',
	compound_percents_type = '.$compound_percents_type.',
	compound_min_percent = '.$compound_min_percent.',
	compound_max_percent = '.$compound_max_percent.',
	compound_percents = \''.$compound_percents.'\',
	dsc = \''.$desc.'\',
	hold = '.$hold.',
	delay = '.$delay.'
  ');
    if ( ! (db_query($q))) {
        exit (mysql_error());;
    }

    $parent = mysql_insert_id();
    $rate_amount_active = $frm['rate_amount_active'];
    for ($i = 0; $i < 300; ++$i) {
        if ($frm['rate_amount_active'][$i] == 1) {
            $name = quote($frm['rate_amount_name'][$i]);
            $min_amount = sprintf('%0.2f', $frm['rate_min_amount'][$i]);
            $max_amount = sprintf('%0.2f', $frm['rate_max_amount'][$i]);
            $percent = sprintf('%0.2f', $frm['rate_percent'][$i]);
            $q = ''.'insert into hm2_plans set 
		parent='.$parent.', 
		name=\''.$name.'\', 
		min_deposit = '.$min_amount.',
		max_deposit = '.$max_amount.', 
		percent = '.$percent;
            if ( ! (db_query($q))) {
                exit (mysql_error());;
            }

            continue;
        }
    }

    header('Location: ?a=rates');
    db_close($dbconn);
    exit ();
}

if ($frm['action'] == 'edit_hyip') {
    $id = sprintf('%d', $frm['hyip_id']);
    if (($id < 3 AND $settings['demomode'] == 1)) {
        header('Location: ?a=rates');
        db_close($dbconn);
        exit ();
    }

    $q_days = sprintf('%d', $frm['hq_days']);
    if ($frm['hq_days_nolimit'] == 1) {
        $q_days = 0;
    }

    $min_deposit = sprintf('%0.2f', $frm['hmin_deposit']);
    $max_deposit = sprintf('%0.2f', $frm['hmax_deposit']);
    $return_profit = sprintf('%d', $frm['hreturn_profit']);
    $return_profit_percent = sprintf('%d', $frm['hreturn_profit_percent']);
    $pay_to_egold_directly = sprintf('%d', $frm['earning_to_egold']);
    $percent = sprintf('%0.2f', $frm['hpercent']);
    $work_week = sprintf('%d', $frm['work_week']);
    $use_compound = sprintf('%d', $frm['use_compound']);
    $parent = sprintf('%d', $frm['parent']);
    $desc = quote($frm_orig[plan_description]);
    $withdraw_principal = sprintf('%d', $frm['withdraw_principal']);
    $withdraw_principal_percent = sprintf('%.02f', $frm['withdraw_principal_percent']);
    $withdraw_principal_duration = sprintf('%d', $frm['withdraw_principal_duration']);
    $withdraw_principal_duration_max = sprintf('%d', $frm['withdraw_principal_duration_max']);
    $compound_min_deposit = sprintf('%.02f', $frm['compound_min_deposit']);
    $compound_max_deposit = sprintf('%.02f', $frm['compound_max_deposit']);
    $compound_percents_type = sprintf('%d', $frm['compound_percents_type']);
    $compound_min_percent = sprintf('%.02f', $frm['compound_min_percent']);
    if (($compound_min_percent < 0 OR 100 < $compound_min_percent)) {
        $compound_min_percent = 0;
    }

    $compound_max_percent = sprintf('%.02f', $frm['compound_max_percent']);
    if (($compound_max_percent < 0 OR 100 < $compound_max_percent)) {
        $compound_max_percent = 100;
    }

    $cps = preg_split('/\\s*,\\s*/', $frm['compound_percents']);
    $cps1 = [];
    foreach ($cps as $cp) {
        if ((( ! in_array($cp, $cps1) AND 0 <= $cp) AND $cp <= 100)) {
            array_push($cps1, sprintf('%d', $cp));
            continue;
        }
    }

    sort($cps1);
    $compound_percents = join(',', $cps1);
    $closed = ($frm['closed'] ? 1 : 0);
    $hold = sprintf('%d', $frm[hold]);
    $delay = sprintf('%d', $frm[delay]);
    $q = 'update hm2_types set
	name=\''.quote($frm['hname']).(''.'\',
	q_days = '.$q_days.',
	period = \'').quote($frm['hperiod']).'\',
	status = \''.quote($frm['hstatus']).(''.'\',
	return_profit = \''.$return_profit.'\',
	return_profit_percent = '.$return_profit_percent.',
	pay_to_egold_directly = '.$pay_to_egold_directly.',
	use_compound = '.$use_compound.',
	work_week = '.$work_week.',
	parent = '.$parent.',
  withdraw_principal = '.$withdraw_principal.',
  withdraw_principal_percent = '.$withdraw_principal_percent.',
  withdraw_principal_duration = '.$withdraw_principal_duration.',
  withdraw_principal_duration_max = '.$withdraw_principal_duration_max.',
  compound_min_deposit = '.$compound_min_deposit.',
  compound_max_deposit = '.$compound_max_deposit.',
  compound_percents_type = '.$compound_percents_type.',
  compound_min_percent = '.$compound_min_percent.',
  compound_max_percent = '.$compound_max_percent.',
  compound_percents = \''.$compound_percents.'\',
  closed = '.$closed.',

  dsc=\''.$desc.'\',
  hold = '.$hold.',
  delay = '.$delay.'

	 where id='.$id.'
  ');
    if ( ! (db_query($q))) {
        exit (mysql_error());;
    }

    $parent = $id;
    $q = ''.'delete from hm2_plans where parent = '.$id;
    if ( ! (db_query($q))) {
        exit (mysql_error());;
    }

    $rate_amount_active = $frm['rate_amount_active'];
    for ($i = 0; $i < 300; ++$i) {
        if ($frm['rate_amount_active'][$i] == 1) {
            $name = quote($frm['rate_amount_name'][$i]);
            $min_amount = sprintf('%0.2f', $frm['rate_min_amount'][$i]);
            $max_amount = sprintf('%0.2f', $frm['rate_max_amount'][$i]);
            $percent = sprintf('%0.2f', $frm['rate_percent'][$i]);
            $q = ''.'insert into hm2_plans set 
		parent='.$parent.', 
		name=\''.$name.'\', 
		min_deposit = '.$min_amount.',
		max_deposit = '.$max_amount.', 
		percent = '.$percent;
            if ( ! (db_query($q))) {
                exit (mysql_error());;
            }

            continue;
        }
    }

    header('Location: ?a=rates');
    db_close($dbconn);
    exit ();
}

if (($frm['a'] == 'thistory' AND $frm['action2'] == 'download_csv')) {
    $frm['day_to'] = sprintf('%d', $frm['day_to']);
    $frm['month_to'] = sprintf('%d', $frm['month_to']);
    $frm['year_to'] = sprintf('%d', $frm['year_to']);
    $frm['day_from'] = sprintf('%d', $frm['day_from']);
    $frm['month_from'] = sprintf('%d', $frm['month_from']);
    $frm['year_from'] = sprintf('%d', $frm['year_from']);
    if ($frm['day_to'] == 0) {
        $frm['day_to'] = date('j', time() + $settings['time_dif'] * 60 * 60);
        $frm['month_to'] = date('n', time() + $settings['time_dif'] * 60 * 60);
        $frm['year_to'] = date('Y', time() + $settings['time_dif'] * 60 * 60);
        $frm['day_from'] = 1;
        $frm['month_from'] = $frm['month_to'];
        $frm['year_from'] = $frm['year_to'];
    }

    $datewhere = '\''.$frm['year_from'].'-'.$frm['month_from'].'-'.$frm['day_from'].'\' + interval 0 day < date + interval '.$settings['time_dif'].' hour and '.'\''.$frm['year_to'].'-'.$frm['month_to'].'-'.$frm['day_to'].'\' + interval 1 day > date + interval '.$settings['time_dif'].' hour ';
    if ($frm['ttype'] != '') {
        if ($frm['ttype'] == 'exchange') {
            $typewhere = ' and (type=\'exchange_out\' or type=\'exchange_in\')';
        } else {
            $typewhere = ' and type=\''.quote($frm['ttype']).'\' ';
        }
    }

    $u_id = sprintf('%d', $frm['u_id']);
    if (1 < $u_id) {
        $userwhere = ''.' and user_id = '.$u_id.' ';
    }

    $ecwhere = '';
    if ($frm[ec] == '') {
        $frm[ec] = -1;
    }

    $ec = sprintf('%d', $frm[ec]);
    if (-1 < $frm[ec]) {
        $ecwhere = ''.' and ec = '.$ec;
    }

    $q = 'select *, date_format(date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as d from hm2_history where '.$datewhere.' '.$userwhere.' '.$typewhere.' '.$ecwhere.' order by date desc, id desc');
    ($sth = db_query($q) OR print mysql_error());
    $trans = [];
    while ($row = mysql_fetch_array($sth)) {
        $q = 'select username from hm2_users where id = '.$row['user_id'];
        $sth1 = db_query($q);
        $row1 = mysql_fetch_array($sth1);
        if ($row1) {
            $row['username'] = $row1['username'];
        } else {
            $row['username'] = '-- deleted user --';
        }

        array_push($trans, $row);
    }

    $from = $frm['month_from'].'_'.$frm['day_from'].'_'.$frm['year_from'];
    $to = $frm['month_to'].'_'.$frm['day_to'].'_'.$frm['year_to'];
    header('Content-Disposition: attachment; filename='.$frm['ttype'].(''.'history-'.$from.'-'.$to.'.csv'));
    header('Content-type: text/coma-separated-values');
    print '"Transaction Type","User","Amount","Currency","Date","Description"
';
    for ($i = 0; $i < sizeof($trans); ++$i) {
        print '"'.$transtype[$trans[$i]['type']].'","'.$trans[$i]['username'].'","$'.number_format(abs($trans[$i]['actual_amount']),
                2).'","'.$exchange_systems[$trans[$i]['ec']]['name'].'","'.$trans[$i]['d'].'","'.$trans[$i]['description'].'"'.'
';
    }

    db_close($dbconn);
    exit ();
}

if (($frm[a] == 'add_processing' AND $frm[action] == 'add_processing')) {
    if ( ! $settings['demomode']) {
        $status = ($frm['status'] ? 1 : 0);
        $name = quote($frm['name']);
        $description = quote($frm_orig['description']);
        $use = $frm['field'];
        $fields = [];
        if ($use) {
            reset($use);
            $i = 1;
            foreach ($use as $id => $value) {
                if ($frm['use'][$id]) {
                    $fields[$i] = $value;
                    ++$i;
                    continue;
                }
            }
        }

        $qfields = serialize($fields);
        $q = 'select max(id) as max_id from hm2_processings';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $max_id = $row['max_id'];
        if ($max_id < 999) {
            $max_id = 998;
        }

        ++$max_id;
        $q = ''.'insert into hm2_processings set
             id = '.$max_id.',
             status = '.$status.',
             name = \''.$name.'\',
             description = \''.$description.'\',
             infofields = \''.quote($qfields).'\'
         ';
        (db_query($q) OR print mysql_error());
    }

    header('Location: ?a=processings');
    db_close($dbconn);
    exit ();
}

if (($frm[a] == 'edit_processing' AND $frm[action] == 'edit_processing')) {
    if ( ! $settings['demomode']) {
        $pid = intval($frm['pid']);
        $status = ($frm['status'] ? 1 : 0);
        $name = quote($frm['name']);
        $description = quote($frm_orig['description']);
        $use = $frm['field'];
        $fields = [];
        if ($use) {
            reset($use);
            $i = 1;
            foreach ($use as $id => $value) {
                if ($frm['use'][$id]) {
                    $fields[$i] = $value;
                    ++$i;
                    continue;
                }
            }
        }

        $qfields = serialize($fields);
        $q = ''.'update hm2_processings set
             status = '.$status.',
             name = \''.$name.'\',
             description = \''.$description.'\',
             infofields = \''.quote($qfields).(''.'\'
           where id = '.$pid.'
         ');
        (db_query($q) OR print mysql_error());
    }

    header('Location: ?a=processings');
    db_close($dbconn);
    exit ();
}

if ($frm[a] == 'update_processings') {
    if ( ! $settings['demomode']) {
        $q = 'update hm2_processings set status = 0';
        (db_query($q) OR print mysql_error());
        $status = $frm['status'];
        if ($status) {
            foreach ($status as $id => $v) {
                $q = ''.'update hm2_processings set status = 1 where id = '.$id;
                (db_query($q) OR print mysql_error());
            }
        }
    }

    header('Location: ?a=processings');
    db_close($dbconn);
    exit ();
}

if ($frm[a] == 'delete_processing') {
    if ( ! $settings['demomode']) {
        $pid = intval($frm['pid']);
        $q = ''.'delete from hm2_processings where id = '.$pid;
        (db_query($q) OR print mysql_error());
    }

    header('Location: ?a=processings');
    db_close($dbconn);
    exit ();
}

include 'inc/admin/html.header.inc.php';
echo '
  <tr> 
    <td valign="top">
	 <table cellspacing=0 cellpadding=1 border=0 width=100% height=100% bgcolor=#ff8d00>
	   <tr>
	     <td>
           <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
             <tr bgcolor="#FFFFFF" valign="top"> 
              <td width=300 align=center>
				   <!-- Image Table: Start -->
';
include 'inc/admin/menu.inc.php';
echo '				   <br>

              </td>
              <td bgcolor="#ff8d00" valign="top" width=1><img src=images/q.gif width=1 height=1></td>          
              <td bgcolor="#FFFFFF" valign="top" width=99%>
            <!-- Main: Start -->
            <table width="100%" height="100%" border="0" cellpadding="10" cellspacing="0" class="forTexts">
              <tr>
                <td width=100% height';
echo '=100% valign=top>
';
if ($frm['a'] == 'rates') {
    include 'inc/admin/rates.inc.php';
} else {
    if ($frm['a'] == 'editrate') {
        include 'inc/admin/edit_hyip.inc.php';
    } else {
        if ($frm['a'] == 'add_hyip') {
            include 'inc/admin/add_hyip.inc.php';
        } else {
            if ($frm['a'] == 'members') {
                include 'inc/admin/members.inc.php';
            } else {
                if ($frm['a'] == 'editaccount') {
                    include 'inc/admin/editaccount.inc.php';
                } else {
                    if ($frm['a'] == 'addmember') {
                        include 'inc/admin/addmember.inc.php';
                    } else {
                        if ($frm['a'] == 'userexists') {
                            include 'inc/admin/error_userexists.inc.php';
                        } else {
                            if ($frm['a'] == 'userfunds') {
                                include 'inc/admin/manage_user_funds.inc.php';
                            } else {
                                if ($frm['a'] == 'addbonuse') {
                                    include 'inc/admin/addbonuse.inc.php';
                                } else {
                                    if (($frm['a'] == 'mass' AND $frm['action2'] == 'masspay')) {
                                        include 'inc/admin/prepare_mass_pay.inc.php';
                                    } else {
                                        if ($frm['a'] == 'thistory') {
                                            include 'inc/admin/transactions_history.php';
                                        } else {
                                            if ($frm['a'] == 'addpenality') {
                                                include 'inc/admin/addpenality.inc.php';
                                            } else {
                                                if ($frm['a'] == 'releasedeposits') {
                                                    include 'inc/admin/releaseusersdeposits.inc.php';
                                                } else {
                                                    if ($frm['a'] == 'pay_withdraw') {
                                                        include 'inc/admin/process_withdraw.php';
                                                    } else {
                                                        if ($frm['a'] == 'settings') {
                                                            include 'inc/admin/settings.inc.php';
                                                        } else {
                                                            if ($frm['a'] == 'info_box') {
                                                                include 'inc/admin/info_box_settings.inc.php';
                                                            } else {
                                                                if ($frm['a'] == 'send_bonuce') {
                                                                    include 'inc/admin/send_bonuce.inc.php';
                                                                } else {
                                                                    if ($frm['a'] == 'send_penality') {
                                                                        include 'inc/admin/send_penality.inc.php';
                                                                    } else {
                                                                        if ($frm['a'] == 'newsletter') {
                                                                            include 'inc/admin/newsletter.inc.php';
                                                                        } else {
                                                                            if ($frm['a'] == 'edit_emails') {
                                                                                include 'inc/admin/emails.inc.php';
                                                                            } else {
                                                                                if ($frm['a'] == 'referal') {
                                                                                    include 'inc/admin/referal.inc.php';
                                                                                } else {
                                                                                    if ($frm['a'] == 'auto-pay-settings') {
                                                                                        include 'inc/admin/auto_pay_settings.inc.php';
                                                                                    } else {
                                                                                        if ($frm['a'] == 'error_pay_log') {
                                                                                            include 'inc/admin/error_pay_log.inc.php';
                                                                                        } else {
                                                                                            if ($frm['a'] == 'news') {
                                                                                                include 'inc/admin/news.inc.php';
                                                                                            } else {
                                                                                                if ($frm['a'] == 'wire_settings') {
                                                                                                    include 'inc/admin/wire_settings.inc.php';
                                                                                                } else {
                                                                                                    if ($frm['a'] == 'wires') {
                                                                                                        include 'inc/admin/wires.inc.php';
                                                                                                    } else {
                                                                                                        if ($frm['a'] == 'wiredetails') {
                                                                                                            include 'inc/admin/wiredetails.inc.php';
                                                                                                        } else {
                                                                                                            if ($frm['a'] == 'affilates') {
                                                                                                                include 'inc/admin/affilates.inc.php';
                                                                                                            } else {
                                                                                                                if ($frm['a'] == 'custompages') {
                                                                                                                    include 'inc/admin/custompage.inc.php';
                                                                                                                } else {
                                                                                                                    if ($frm['a'] == 'exchange_rates') {
                                                                                                                        include 'inc/admin/exchange_rates.inc.php';
                                                                                                                    } else {
                                                                                                                        if ($frm['a'] == 'security') {
                                                                                                                            include 'inc/admin/security.inc.php';
                                                                                                                        } else {
                                                                                                                            if ($frm['a'] == 'processings') {
                                                                                                                                include 'inc/admin/processings.inc.php';
                                                                                                                            } else {
                                                                                                                                if ($frm['a'] == 'add_processing') {
                                                                                                                                    include 'inc/admin/add_processing.inc.php';
                                                                                                                                } else {
                                                                                                                                    if ($frm['a'] == 'edit_processing') {
                                                                                                                                        include 'inc/admin/edit_processing.inc.php';
                                                                                                                                    } else {
                                                                                                                                        if ($frm['a'] == 'pending_deposits') {
                                                                                                                                            include 'inc/admin/pending_deposits.inc.php';
                                                                                                                                        } else {
                                                                                                                                            if ($frm['a'] == 'pending_deposit_details') {
                                                                                                                                                include 'inc/admin/pending_deposit_details.inc.php';
                                                                                                                                            } else {
                                                                                                                                                if ($frm['a'] == 'startup_bonus') {
                                                                                                                                                    include 'inc/admin/startup_bonus.inc.php';
                                                                                                                                                } else {
                                                                                                                                                    include 'inc/admin/main.inc.php';
                                                                                                                                                }
                                                                                                                                            }
                                                                                                                                        }
                                                                                                                                    }
                                                                                                                                }
                                                                                                                            }
                                                                                                                        }
                                                                                                                    }
                                                                                                                }
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

echo '

              </td>
              </tr>
            </table>
            <!-- Main: END -->

              </td>
             </tr>
           </table>
		  </td>
		 </tr>
	   </table>
	 </td>
  </tr>

';
include 'inc/admin/html.footer.inc.php';
db_close($dbconn);
exit ();
