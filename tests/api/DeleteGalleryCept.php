<?php
$I = new ApiTester($scenario);
$I->wantTo('Delete picture from picture_id');
$I->sendDELETE('gallery/picture/54cb466810f0ed23048b4567');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContains('{"success":true}');