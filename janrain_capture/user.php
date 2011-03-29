<?php

function capture_user_authenticate() {
	global $vbulletin;

	$redirect_uri = $vbulletin->options['bburl'] . "/";
	$auth_code = $_GET["code"];

	new_access_token($auth_code, $redirect_uri);
	$profile = load_user_entity();
	if ($vbulletin->userinfo = $vbulletin->db->query_first("
		SELECT userid, usergroupid, membergroupids, username
			FROM " . TABLE_PREFIX . "user
			WHERE userid IN (
				SELECT userid FROM " . TABLE_PREFIX . "userfield WHERE {$vbulletin->options['janrain_capture_uuid']} = '{$profile['result']['uuid']}'
			)
		")) {
		capture_user_login();
	} else {
		capture_create_user($profile);
	}
}

function capture_user_login() {
	global $vbulletin;
	require_once(DIR . '/includes/functions_login.php');
	exec_unstrike_user($vbulletin->userinfo['username']);
	process_new_login('', false, '');
	//do_login_redirect();
}

function capture_create_user($profile) {
	global $vbulletin;

	// init user datamanager class
	$userdata = & datamanager_init('User', $vbulletin, ERRTYPE_ARRAY);

	// set email
	$userdata->set('email', $profile['result']['email']);

	$userdata->set('username', $profile['result']['email']);

	$userdata->set('password', md5($profile['result']['uuid'] . date('U')));

	if ($profile['result']['birthday'])
		$userdata->set('birthday', $profile['result']['birthday']);

	// ... additional data setting ...
	$userfield = array($vbulletin->options['janrain_capture_uuid'] => $profile['result']['uuid']);
	/**
	 * Enable when we're sure these are the column names
	 *
	  if($profile['result']['name']['familyName'])
	  $userfield['last_name'] = $profile['result']['name']['familyName'];

	  if($profile['result']['name']['givenName'])
	  $userfield['first_name'] = $profile['result']['name']['givenName'];
	 */
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
	}
}

?>