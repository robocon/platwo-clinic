<?php 
$I = new ApiTester($scenario);
$I->wantTo('Facebook login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

//$I->setHeader('access-token', '15f0890c37b57bc837c31fbcadde3150f192509fe199c3012b78c94113706492');

$I->sendPOST('oauth/facebook', [
    'facebook_token' => 'CAAM1TzhIHDwBALsyB6BYElmesii2xwzGd1L3VAzmmjak20giFvaMGN9ul9AY9Xgr9IpHFmzOFoGPZCdf5CZBZAIdEmUROsIGEZAq1ZCkM2wgzYNwRZAuBIxONX66Bky7s2dK4SG833Kf4OtIiZBFQHTFwOx8ZA2EIjqY6ZA4fYp2g9vmL7SWSJ2F5O9Y9wO53SyVZAUZBcqyU0QnGl4AW6cRZCeHBuFUbcf9gdAZD',
    
// Example ios
    'ios_device_token' => [
        'type' => 'product',
        'key' => '56b3cf33d566d42e22457698f3d935ddbdd3fc26bc50330e7813f7d935795c4e'
    ]
    // Example Android token
    // "APA91bHh0sDFO9wDD--My4WFltYdx4murTcdwhFaZS928ZIY6x8WXDuO2cVmY5pjaockSYUekpdL8tZxUD-KUGI6QzRXLrS7Jyf7Irknh6ooUgemJ-WdTzmtXu9hG3zl4OSJwM003DRspRrSoY5Fcq8e51b2sheSAUDvNGAvWF8iYtl6ns9Ze9Q", 
        
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$user_id = $I->grabDataFromJsonResponse('user_id');
$access_token = $I->grabDataFromJsonResponse('access_token');
$I->seeResponseContainsJson([
    'user_id' => $user_id,
    'access_token' => $access_token
]);