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
	//if ($sms_sender && $sms_to && $sms_msg) {
	if ($sms_to && $sms_msg) {
		
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
		$url = $plugin_config['telnyx']['url'] . "?";
		$url .= "&password=" . $plugin_config['telnyx']['password'];
		$url .= "&from=" . urlencode($sms_sender);
		$url .= "&to=" . urlencode($sms_to);
		$url .= "&text=" . urlencode($sms_msg);
		$url .= "&tipe=" . $c_sms_type;
		$url = trim($url);
		
		_log("send url:[" . $url . "]", 3, "telnyx_hook_sendsms");
		
		// send it
		$response = file_get_contents($url);
		
		/*
		 * OK:1234567891011
		 * ERROR:1001
		 */
		
		if ($response) {
			$c_response = explode(':', $response);
			
			if (strtolower($c_response[0] == 'ok')) {
				$c_message_id = $c_response[1];
			}
		
			if (strtolower($c_response[0] == 'error')) {
				$c_error_code = $c_response[1];
			} else if ((int) $c_response[0]) {
				$c_error_code = (int) $c_response[0];
			}
		}
		
		// a single non-zero respond will be considered as a SENT response
		if ($c_message_id) {
			_log("sent smslog_id:" . $smslog_id . " message_id:" . $c_message_id . " smsc:" . $smsc, 2, "telnyx_hook_sendsms");
			$db_query = "
				INSERT INTO " . _DB_PREF_ . "_gatewayTelnyx_log (local_smslog_id, remote_smslog_id)
				VALUES ('$smslog_id', '$c_message_id')";
			$id = @dba_insert_id($db_query);
			if ($id) {
				$ok = true;
				$p_status = 1;
				dlr($smslog_id, $uid, $p_status);
			}
		} else if ($c_error_code) {
			_log("failed smslog_id:" . $smslog_id . " message_id:" . $c_message_id . " error_code:" . $c_error_code . " smsc:" . $smsc, 2, "telnyx_hook_sendsms");
		} else {
			$resp = $response;
			_log("invalid smslog_id:" . $smslog_id . " resp:[" . $resp . "] smsc:" . $smsc, 2, "telnyx_hook_sendsms");
		}
	}
	if (!$ok) {
		$p_status = 2;
		dlr($smslog_id, $uid, $p_status);
	}
	
	return $ok;
}
