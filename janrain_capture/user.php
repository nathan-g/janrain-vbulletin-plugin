<?php

function capture_user_authenticate() {
    global $vbulletin;

    $redirect_uri = $vbulletin->options['bburl'] . "/";
    $auth_code = $_GET["code"];

    new_access_token($auth_code, $redirect_uri);
    $profile = load_user_entity();
    if (isset($profile)) {
        if ($vbulletin->userinfo = $vbulletin->db->query_first("
            SELECT userid, usergroupid, membergroupids, username
                FROM " . TABLE_PREFIX . "user
                WHERE userid IN (
                    SELECT userid FROM " . TABLE_PREFIX . "userfield WHERE {$vbulletin->options['janrain_capture_uuid']} = '{$profile['result']['uuid']}'
                )
            ")) {
            capture_user_sync($profile);
            capture_user_login();
        } elseif ($vbulletin->userinfo = $vbulletin->db->query_first("
            SELECT userid, usergroupid, membergroupids, username
                FROM " . TABLE_PREFIX . "user
                WHERE email = '{$profile['result']['email']}'
            ")) {
            capture_user_sync($profile, true);

            capture_user_login();
        } else {
            capture_create_user($profile);
        }
    } else {
        die("Could not load user profile. Please try again");
    }
}

function capture_user_login() {
    global $vbulletin;
    require_once(DIR . '/includes/functions_login.php');
    exec_unstrike_user($vbulletin->userinfo['username']);
    process_new_login('', false, '');
    update_capture_session(array(
        'access_token' => $vbulletin->capture_session['capture_access_token'],
        'refresh_token' => $vbulletin->capture_session['capture_refresh_token'],
        'expires_in' => $vbulletin->capture_session['capture_expires_in'],
        'transaction_state' => array(
            'capture' => array(
                'password_recover' => $vbulletin->capture_session['capture_password_recover']
            )
        )
    ));
}

function capture_create_user($profile) {
    global $vbulletin;

    // init user datamanager class
    $userdata = & datamanager_init('User', $vbulletin, ERRTYPE_ARRAY);

    // set email
    $userdata->set('email', $profile['result']['email']);

    $userdata->set('username', $profile['result']['displayName']);

    $userdata->set('password', md5($profile['result']['uuid'] . date('U')));

    if ($profile['result']['birthday'])
        $userdata->set('birthday', $profile['result']['birthday']);

    // ... additional data setting ...
    $userfield = array($vbulletin->options['janrain_capture_uuid'] => $profile['result']['uuid']);

    if($profile['result']['name']['familyName'] && $vbulletin->options['janrain_capture_lname'])
        $userfield[$vbulletin->options['janrain_capture_lname']] = $profile['result']['name']['familyName'];

    if($profile['result']['name']['givenName'] && $vbulletin->options['janrain_capture_fname'])
        $userfield[$vbulletin->options['janrain_capture_fname']] = $profile['result']['name']['givenName'];

    $customfields = $userdata->set_userfields($userfield, true, 'admin');
    $userdata->pre_save();

    // check for errors
    if (!empty($userdata->errors)) {
        foreach ($userdata->errors AS $index => $error) {
            echo $error;
        }
        exit;
    } else {
        // save the data
        $vbulletin->userinfo['userid']
                = $userid
                = $userdata->save();

        require_once(DIR . '/includes/functions_login.php');
        $vbulletin->session->created = false;
        process_new_login('', false, '');
        update_capture_session(array(
            'access_token' => $vbulletin->capture_session['capture_access_token'],
            'refresh_token' => $vbulletin->capture_session['capture_refresh_token'],
            'expires_in' => $vbulletin->capture_session['capture_expires_in'],
            'transaction_state' => array(
                'capture' => array(
                    'password_recover' => $vbulletin->capture_session['capture_password_recover']
                )
            )
        ));
    }
}

function capture_user_sync($profile=false, $setId=false, $redirect=false) {
    global $vbulletin;

    if ($profile===false)
        $profile = load_user_entity();

    if ($profile) {
        $userdata =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
        $userdata->set_existing($vbulletin->userinfo);
        $userdata->set('username', $profile['result']['displayName']);

        if ($profile['result']['birthday'])
            $userdata->set('birthday', $profile['result']['birthday']);

        if ($setId) {
            $userfield = array($vbulletin->options['janrain_capture_uuid'] => $profile['result']['uuid']);
            $customfields = $userdata->set_userfields($userfield, true, 'admin');
        }

        $userdata->save();
        if ($redirect)
            exec_header_redirect(capture_current_page());
    }
}

function capture_current_page() {
    $url = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    $url .= $_SERVER["SERVER_NAME"];
    $url .= ($_SERVER["SERVER_PORT"] != "80") ? ":".$_SERVER["SERVER_PORT"] : '';
    $url .= preg_replace("/[\?\&]capture_sync\=1/i", "", $_SERVER['REQUEST_URI']);
    return $url;
}

?>