<?php 
$user_id = '54ed542810f0ed0d048b456a';

$I = new ApiTester($scenario);
$I->wantTo('Update User Profile Website');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('user/profile/'.$user_id.'/website', [
    'website' => 'http://www.google.com'
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);