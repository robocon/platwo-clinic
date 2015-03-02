<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show comments from event_id');
$I->sendGET('sniff/follower/54ba191510f0edb7048b456a');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);