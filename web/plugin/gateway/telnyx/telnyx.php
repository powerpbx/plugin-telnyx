<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isadmin()) {
	auth_block();
}

include $core_config['apps_path']['plug'] . "/gateway/telnyx/config.php";

switch (_OP_) {
	case "manage":
		$tpl = array(
			'name' => 'telnyx',
			'vars' => array(
				'DIALOG_DISPLAY' => _dialog(),
				'Manage telnyx' => _('Manage telnyx'),
				'Gateway name' => _('Gateway name'),
				// 'User' => _('User'),
				'API Key' => _('API Key'),
				'Module sender ID' => _('Module sender ID'),
				'Module timezone' => _('Module timezone'),
				'Save' => _('Save'),
				'Notes' => _('Notes'),
				'Your callback URL is' => _('Your callback URL is'),
				'CALLBACK_URL' => _HTTP_PATH_PLUG_ . '/gateway/telnyx/callback.php',
				'HINT_FILL_PASSWORD' => _hint(_('Fill to change the API Key')),
				'HINT_MODULE_SENDER' => _hint(_('Max. 16 numeric or 11 alphanumeric char. empty to disable')),
				'HINT_TIMEZONE' => _hint(_('Eg: +0700 for Jakarta/Bangkok timezone')),
				'BUTTON_BACK' => _back('index.php?app=main&inc=core_gateway&op=gateway_list'),
				'status_active' => $status_active,
				'telnyx_param_user' => $plugin_config['telnyx']['user'],
				'telnyx_param_module_sender' => $plugin_config['telnyx']['module_sender'],
				'telnyx_param_datetime_timezone' => $plugin_config['telnyx']['datetime_timezone'] 
			) 
		);
		_p(tpl_apply($tpl));
		break;
	
	case "manage_save":
	
		$up_url = $plugin_config['telnyx']['url'];
		$up_callback_url = _HTTP_PATH_PLUG_ . '/gateway/telnyx/callback.php';
		$up_password = $_REQUEST['up_password'];
		$up_module_sender = $_REQUEST['up_module_sender'];
		$up_datetime_timezone = $_REQUEST['up_datetime_timezone'];

		if ($up_url) {
			$items = array(
				'url' => $up_url,
				'callback_url' => $up_callback_url,
				'module_sender' => $up_module_sender,
				'datetime_timezone' => $up_datetime_timezone
			);
			if ($up_password) {
				$items['password'] = $up_password;
			}
			if (registry_update(0, 'gateway', 'telnyx', $items)) {
				$_SESSION['dialog']['info'][] = _('Gateway module configurations has been saved');
			} else {
				$_SESSION['dialog']['danger'][] = _('Fail to save gateway module configurations');
			}
		} else {
			$_SESSION['dialog']['danger'][] = _('All mandatory fields must be filled');
		}
		header("Location: " . _u('index.php?app=main&inc=gateway_telnyx&op=manage'));
		exit();
		break;
}
