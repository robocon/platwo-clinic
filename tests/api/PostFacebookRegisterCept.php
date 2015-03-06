<?php 
$I = new ApiTester($scenario);
$I->wantTo('Facebook login');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

//$I->setHeader('access-token', '15f0890c37b57bc837c31fbcadde3150f192509fe199c3012b78c94113706492');

$I->sendPOST('oauth/facebook', [
    'facebook_token' => 'CAALxLT6qTbkBAGZBZCfU7XudLeaAWgBpw4lU5J4ZCwuojXtREEJEQqxzdg5x78CsmTlDEL1Q1LRQzchEj5tCsotPuJEGBJ9q9mPfpApZBofgNw1ZB3RYpqqYpERnd6CBZBiwccrc7YQVyQTP5ZAhrbEsxqGOcNnuXVjFlZCSIuJl8hYSEGrdp2Te7AZC0GbSkMnHpHZAMQKAqj91eOWg2h8EcFETlTmT018McZD',
    
// Example ios
    'ios_device_token' => [
        'type' => 'product',
        'key' => 'd44dbda61f127cceefa8a09784a352025d0071aa59fd9c3ec5f1e10d8a6ccca1'
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