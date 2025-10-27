<?php
defined('_SECURE_') or die('Forbidden');

$data = registry_search(0, 'gateway', 'telnyx');
$plugin_config['telnyx'] = $data['gateway']['telnyx'];
$plugin_config['telnyx']['name'] = 'telnyx';
$plugin_config['telnyx']['url'] = 'https://api.telnyx.com/v2';

// smsc configuration
$plugin_config['telnyx']['_smsc_config_'] = array(
	'password' => _('API Key'),
	'module_sender' => _('Module sender ID'),
	'datetime_timezone' => _('Module timezone') 
);
