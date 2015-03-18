<?php 

// Get test image and convert into base64
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));

$I = new ApiTester($scenario);
$I->wantTo('Test add new service in folder');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('service/item', [
    'name' => 'Lorem ipsum dolor '.time(),
    'detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit '.time(),
    'price' => '120',
    'parent_id' => '550949b610f0ed7e048b4568',
    'pictures' => [$image,$image],
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);