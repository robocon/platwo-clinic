<?php 

// Get test image and convert into base64
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));

$I = new ApiTester($scenario);
$I->wantTo('Test add new promotion');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('promotion', [
    'name' => '[Promotion] Lorem ipsum dolor '.time(),
    'detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit '.time(),
    'thumb' => $image,
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);