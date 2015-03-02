<?php 
$user_id = '54ba29c210f0edb8048b457a';
$event_id = '54cb466710f0ed24048b4567';

$I = new ApiTester($scenario);
$I->wantTo('Add Report');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('report', [
    'detail' => 'Testing message detail '.time(),
    'type' => 'event',
    'user_id' => $user_id,
    'reference_id' => $event_id
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);