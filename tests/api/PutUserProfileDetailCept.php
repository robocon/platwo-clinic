<?php 
$user_id = '54ba29c210f0edb8048b457a';

$I = new ApiTester($scenario);
$I->wantTo('Update User Profile About Me');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('user/profile/'.$user_id.'/detail', [
    'detail' => 'test to change about me'
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$success = $I->grabDataFromJsonResponse('success');
$I->seeResponseContainsJson([
    'success' => $success
]);