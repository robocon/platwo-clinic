<?php 
$user_id = '54ed542810f0ed0d048b456a';

$I = new ApiTester($scenario);
$I->wantTo('Update User Profile Gender');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('user/location/'.$user_id, [
    'country' => '54b8dfa810f0edcf048b4567',
    'city' => '54b8e0e010f0edcf048b4575'
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);