# Telnyx Plugin Changelog

## Fixed - Telnyx API v2 Compatibility

### Issues Identified
The plugin was using outdated Telnyx API v1 endpoints and authentication methods, causing message sending failures with "invalid" response errors.

### Changes Made

#### 1. API Endpoint Update (`config.php`)
- **Old**: `https://sms.telnyx.com`
- **New**: `https://api.telnyx.com/v2`

#### 2. Authentication Method (`fn.php`)
- **Old**: `x-profile-secret: [SECRET]`
- **New**: `Authorization: Bearer [API_KEY]`

#### 3. Request Body Field (`fn.php`)
- **Old**: `body` parameter for message text
- **New**: `text` parameter for message text

#### 4. Webhook URL Field (`fn.php`)
- **Old**: `delivery_status_webhook_url`
- **New**: `webhook_url`

#### 5. Response Structure (`fn.php`)
- **Old**: Expected `response->status` and `response->sms_id`
- **New**: Properly parse `response->data->id` and `response->data->to[0]->status`
- Added support for multiple status values: `queued`, `sending`, `sent`

#### 6. Error Handling (`fn.php`)
- **Old**: `response->message` for errors
- **New**: `response->errors[0]->detail` for proper error messages

#### 7. Webhook Handler (`callback.php`)
- Updated to handle Telnyx v2 webhook format with nested structure
- Added support for `message.finalized` event type for delivery receipts
- Added support for `message.received` event type for incoming messages
- Properly extract data from `data->payload` structure
- Added handling for additional status values: `queued`, `sending_failed`, `delivery_failed`, `delivery_unconfirmed`

#### 8. UI Labels (`telnyx.php`, `config.php`)
- Changed "Secret" to "API Key" for better clarity

### Testing
After these changes, the plugin should:
1. Successfully send messages via Telnyx API v2
2. Properly parse success responses with message IDs
3. Handle delivery receipts via webhooks
4. Process incoming messages correctly

### Migration Notes
If you're upgrading from the old version:
1. Update your configuration with a Telnyx API Key (not the old profile secret)
2. The API Key should be a Bearer token from your Telnyx account
3. Ensure your webhook URL is configured in your Telnyx Messaging Profile
