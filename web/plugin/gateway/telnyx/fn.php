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

// hook_sendsms
// called by main sms sender
// return true for success delivery
// $smsc : smsc
// $sms_sender : sender mobile number
// $sms_footer : sender sms footer or sms sender ID
// $sms_to : destination sms number
// $sms_msg : sms message tobe delivered
// $gpid : group phonebook id (optional)
// $uid : sender User ID
// $smslog_id : sms ID
function telnyx_hook_sendsms($smsc, $sms_sender, $sms_footer, $sms_to, $sms_msg, $uid = '', $gpid = 0, $smslog_id = 0, $sms_type = 'text', $unicode = 0) {
	global $plugin_config;
	
	_log("enter smsc:" . $smsc . " smslog_id:" . $smslog_id . " uid:" . $uid . " to:" . $sms_to, 3, "telnyx_hook_sendsms");
	
	// override plugin gateway configuration by smsc configuration
	$plugin_config = gateway_apply_smsc_config($smsc, $plugin_config);
	
	$sms_sender = stripslashes($sms_sender);
	if ($plugin_config['telnyx']['module_sender']) {
		$sms_sender = $plugin_config['telnyx']['module_sender'];
	}
	
	$sms_footer = stripslashes($sms_footer);
	$sms_msg = stripslashes($sms_msg);
	$ok = false;
	
	if ($sms_footer) {
		$sms_msg = $sms_msg . $sms_footer;
	}
	
	// no sender config yet
	if ($sms_to && $sms_to && $sms_msg) {
		
		$c_sms_type = ( $sms_type == "flash" ? 2 : 0 );
		
		$unicode_query_string = '';
		if ($unicode) {
			if (function_exists('mb_convert_encoding')) {
				// $sms_msg = mb_convert_encoding($sms_msg, "UCS-2BE", "auto");
				$sms_msg = mb_convert_encoding($sms_msg, "UCS-2", "auto");
				// $sms_msg = mb_convert_encoding($sms_msg, "UTF-8", "auto");
			}
			
			$c_sms_type = ( $sms_type == "flash" ? 3 : 1 );
		}
		
		// https://developers.telnyx.com/docs/messaging
		$url = $plugin_config['telnyx']['url'] . '/messages';
		$data = array(
			'to' => $sms_to,
			'from' => $sms_sender,
			'body' => $sms_msg 
		);
		if ($plugin_config['telnyx']['callback_url']) {
			$data['delivery_status_webhook_url'] = $plugin_config['telnyx']['callback_url'];
		}

		$data_string = json_encode($data);

		if (function_exists('curl_init')) {
			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    			'Content-Type: application/json',
    			'x-profile-secret: ' . $plugin_config['telnyx']['password']
			));
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$returns = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			_log("sendsms url:[" . $url . "] callback:[" . $data['delivery_status_webhook_url'], "] smsc:[" . $smsc . "]", 3, "telnyx_hook_sendsms");

			$response = json_decode($returns);
		
			if ($response->status) {
				$c_status = $response->status;
				$c_message_id = $response->sms_id;
				$c_error_text = $c_status;
			}

			if ($http_code != 200) {
				$c_error_code = $http_code;
			} 
			
			// a single non-zero respond will be considered as a SENT response
			if ($c_message_id) {
				_log("sent smslog_id:" . $smslog_id . " message_id:" . $c_message_id . " smsc:" . $smsc, 2, "telnyx_hook_sendsms");
				$db_query = "
					INSERT INTO " . _DB_PREF_ . "_gatewayTelnyx_log (local_smslog_id, remote_smslog_id)
					VALUES ('$smslog_id', '$c_message_id')";
				$id = @dba_insert_id($db_query);
				if ($id && ($c_status == 'sending')) {
					$ok = true;
					$p_status = 1;
				} else {
					$p_status = 2;
				}
				dlr($smslog_id, $uid, $p_status);
			} else if ($c_error_code) {
				_log("failed smslog_id:" . $smslog_id . " message_id:" . $c_message_id . " error_code:" . $c_error_code . " smsc:" . $smsc, 2, "telnyx_hook_sendsms");
			} else {
				$resp = json_encode($response);
				_log("invalid smslog_id:" . $smslog_id . " resp:[" . $resp . "] smsc:" . $smsc, 2, "telnyx_hook_sendsms");
			}
		} else {
			_log("fail to sendsms due to missing PHP curl functions", 3, "telnyx_hook_sendsms");
		}
	}

	if (!$ok) {
		$p_status = 2;
		dlr($smslog_id, $uid, $p_status);
	}

	_log("sendsms end", 3, "telnyx_hook_sendsms");
	
	return $ok;
}
