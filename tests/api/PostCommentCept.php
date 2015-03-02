<?php 
$I = new ApiTester($scenario);
$I->wantTo('Save a comment');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$user_id = '54ba29c210f0edb8048b457a';
$event_id = '54ba191510f0edb7048b456a';

for ($i = 0; $i < 10; $i++) {
    $detail = 'test comment '.time();
    $I->sendPOST('comment/'.$event_id, [
        'detail' => $detail,
        'user_id' => $user_id
        ]);
    $I->seeResponseCodeIs(200);
    $I->seeResponseIsJson();

    $I->seeResponseContainsJson([
        'detail' => $detail,
        'user_id' => $user_id,
        'event_id' => $event_id,
    ]);
}
