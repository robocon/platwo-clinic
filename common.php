<?php

if(defined(SITE_PRODUCTION)){
    function dump($content){
        echo "<pre>".print_r($content)."</pre>";
    }
}