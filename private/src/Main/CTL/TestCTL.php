<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 26/11/2557
 * Time: 15:22 น.
 */

namespace Main\CTL;

use Main\DB,
    Main\Helper\APNHelper,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Facebook\FacebookSession,
    Facebook\FacebookRequest,
    Facebook\GraphUser,
    Main\Helper\FacebookHelper,
    Main\Service\UserService;

/**
 * @Restful
 * @uri /test
 */
class TestCTL extends BaseCTL {
    
        
    public function getCollection(){
        $db = DB::getDB();
        return $db->cities;
    }
    
    public function countryCollection(){
        $db = DB::getDB();
        return $db->countries;
    }
    
    public function getTagCollection(){
        $db = DB::getDB();
        return $db->tag;
    }
    
    /**
     * @GET
     * @uri /facebook/[a:facebook_id]
     */
    public function facebook() {
        
        FacebookSession::setDefaultApplication(FacebookHelper::$app_id, FacebookHelper::$app_secret);
        $session = new FacebookSession($this->reqInfo->urlParam('facebook_id'));
//        var_dump($session);
//        exit;
        $me = (new FacebookRequest(
            $session, 'GET', '/me'
        ))->execute()->getGraphObject(GraphUser::className());
//        var_dump($me);
        exit;
        
        
        $request = new FacebookRequest(
        $session,
            'GET',
            '/'.$me->getId().'/friendlists'
        );
        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        var_dump($graphObject->getProperty('data'));

        exit;
    }
    
    /**
     * @GET
     * @uri /login
     */
    public function login() {
        ?>
<script>
  // This is called with the results from from FB.getLoginStatus().
  function statusChangeCallback(response) {
    console.log('statusChangeCallback');
    console.log(response);
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
    if (response.status === 'connected') {
//        console.log(response.authResponse.accessToken);
        document.getElementById('token_contain').innerHTML = response.authResponse.accessToken;
        
        
      // Logged into your app and Facebook.
      testAPI();
    } else if (response.status === 'not_authorized') {
      // The person is logged into Facebook, but not your app.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into this app.';
    } else {
      // The person is not logged into Facebook, so we're not sure if
      // they are logged into this app or not.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into Facebook.';
    }
  }

  // This function is called when someone finishes with the Login
  // Button.  See the onlogin handler attached to it in the sample
  // code below.
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }

  window.fbAsyncInit = function() {
  FB.init({
    appId      : '903039293070396',
    cookie     : true,  // enable cookies to allow the server to access 
                        // the session
    xfbml      : true,  // parse social plugins on this page
    version    : 'v2.1' // use version 2.1
  });

  // Now that we've initialized the JavaScript SDK, we call 
  // FB.getLoginStatus().  This function gets the state of the
  // person visiting this page and can return one of three states to
  // the callback you provide.  They can be:
  //
  // 1. Logged into your app ('connected')
  // 2. Logged into Facebook, but not your app ('not_authorized')
  // 3. Not logged into Facebook and can't tell if they are logged into
  //    your app or not.
  //
  // These three cases are handled in the callback function.

  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });

  };

  // Load the SDK asynchronously
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));

  // Here we run a very simple test of the Graph API after login is
  // successful.  See statusChangeCallback() for when this call is made.
  function testAPI() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
      console.log('Successful login for: ' + response.name);
      document.getElementById('status').innerHTML =
        'Thanks for logging in, ' + response.name + '!';
    });
  }
</script>
<!--
  Below we include the Login Button social plugin. This button uses
  the JavaScript SDK to present a graphical Login button that triggers
  the FB.login() function when clicked.
-->

<fb:login-button scope="public_profile,email,friends,friendlists" onlogin="checkLoginState();">
</fb:login-button>

<div id="status">
</div>

<div id="token_contain"></div>
<?php

exit;
    }
    
    /**
     * @GET
     * @uri /a
     */

    public function a(){
        $coll = DB::getDB()->branches;
        $items = $coll->find();
        $res = [];
        foreach($items as $key=> $item){
            $set = [
                'location'=> [
                    'lat'=> sprintf("%.6f", (float)$item['location']['lat']),
                    'lng'=> sprintf("%.6f", (float)$item['location']['lng'])
                ]
            ];
            $coll->update(['_id'=> $item['_id']], ['$set'=> ArrayHelper::ArrayGetPath($set)]);
            $res[] = ArrayHelper::ArrayGetPath($set);
        }
        return $res;
    }

    /**
     * @GET
     * @uri /b
     */

    public function b(){
        $collBranches = DB::getDB()->branches;
        $collTels = DB::getDB()->branches_telephones;
        $items = $collBranches->find();
        $res = [];
        foreach($items as $key=> $item){
            $set = [
                'tel_length'=> $collTels->count(['branch_id'=> $item['_id']])
            ];
            $collBranches->update(['_id'=> $item['_id']], ['$set'=> ArrayHelper::ArrayGetPath($set)]);
            $res[] = ArrayHelper::ArrayGetPath($set);
        }
        return $res;
    }
    
    /**
     * @GET
     * @uri /import_category
     */
    public function importCategory(){
        
        try {
            $params = [
                'access_token' => $_SERVER['HTTP_P2AUTH_TOKEN'],
            ];
            $user = UserService::getInstance()->me($params, $this->getCtx());
            if ($user !== null) {
                $str = file_get_contents('./private/json/category.json');
                $items = json_decode($str, true);
                foreach($items['category'] as $category){
                    $this->getTagCollection()->insert(array(
                        'en' => $category['en'],
                        'th' => $category['th']
                    ));
                }
            }
            
            return ['status'=>'200'];
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    
    /**
     * @GET
     * @uri /country
     */
    public function importCountry() {
        try {
            $params = [
                'access_token' => $_SERVER['HTTP_P2AUTH_TOKEN'],
            ];
            
            $user = UserService::getInstance()->me($params, $this->getCtx());
            if ($user !== null) {
                $str = file_get_contents('./private/json/states.json');
                $items = json_decode($str, true);;
                foreach($items['Countries'] as $country){
                    
                    $data = ['name' => $country['n']];
                    $this->countryCollection()->insert($data);
                    
                    foreach ($country['s'] as $state) {
                        $sta = [
                            'country_id' => $data['_id']->{'$id'},
                            'name' => $state['n'],
                        ];
                        $this->getCollection()->insert($sta);
                    }
                }
            }
            return ['status'=>'200'];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}