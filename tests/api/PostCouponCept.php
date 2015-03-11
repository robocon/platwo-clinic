<?php 

// Get test image and convert into base64
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));

$I = new ApiTester($scenario);
$I->wantTo('Test add new coupon');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
$I->sendPOST('coupon', [
    'name' => 'Lorem ipsum dolor '.time(),
    'detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit '.time(),
    'thumb' => $image,
    'condition' => '120',
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);