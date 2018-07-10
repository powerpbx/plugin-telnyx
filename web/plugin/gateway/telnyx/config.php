<?php
defined('_SECURE_') or die('Forbidden');

$data = registry_search(0, 'gateway', 'telnyx');
$plugin_config['telnyx'] = $data['gateway']['telnyx'];
$plugin_config['telnyx']['name'] = 'telnyx';
$plugin_config['telnyx']['url'] = 'https://sms.telnyx.com';
//$plugin_config['telnyx']['default_url'] = 'https://www.telnyx.com/sms/bulksms-api/bulksms-api';
//$plugin_config['telnyx']['url'] = ( $plugin_config['telnyx']['url'] ? $plugin_config['telnyx']['url'] : $plugin_config['telnyx']['default_url'] );

// smsc configuration
$plugin_config['telnyx']['_smsc_config_'] = array(
	/* 'url' => _('Easy Send SMS URL'), */
	'password' => _('Secret'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone') 
);
