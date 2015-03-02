<?php 

// Get test image and convert into base64
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));
$user_id = '54ba29c210f0edb8048b457a';
$event_id = '54cb466710f0ed24048b4567';

$I = new ApiTester($scenario);
$I->wantTo('Add picture into event');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('gallery/'.$event_id, [
    'event_id' => $event_id,
    'picture' => $image,
    'user_id' => $user_id,
    'detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit '.time()
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'user_id' => '54ba29c210f0edb8048b457a',
    'event_id' => $event_id,
]);
