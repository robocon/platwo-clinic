<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get all appointment from user');
$I->setHeader('access-token', 'f70862b6e1347aa07ec841be1f555bb6632061c3f56f27bb9232cb68b7d40209');
$I->sendGET('appoint/550d1d561831f08e07bf0bd1');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $id
]);