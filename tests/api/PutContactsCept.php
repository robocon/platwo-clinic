<?php
$I = new ApiTester($scenario);
$I->wantTo('Update Contacts');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
$data = [
    'phone' => '0811111111',
    'website' => 'http://pla2api.com',
    'email' => 'twotwoapi@gmail.com',
    'facebook' => ['name' => 'CARTMAN','id' => '54f95eb010f0ed41048b456b'],
    'line' => ['id' => 'Kenny','code' => 'oEReiRe']
];
$I->sendPUT('contact', $data);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
//$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson($data);
