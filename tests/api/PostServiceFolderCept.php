<?php 
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));

$I = new ApiTester($scenario);
$I->wantTo('Add Report');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('service/folder', [
    'name' => 'Lorem ipsum dolor sit amet '.time(),
    'thumb' => $image,
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);