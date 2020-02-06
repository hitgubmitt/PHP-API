<?php
namespace App\RouteParser;

class RouteParser implements ARouteParser{


    public function addSlash($pattern):string{
        if(substr($pattern, -1) !== "/"){
            $pattern = $pattern."/";
        }
        return $pattern;
    }

    public function createRegex($apiUrl):string{
        $nonEscapedApiUrl = preg_replace('/{.*?}/', "([^/]*?)", $apiUrl);
        $apiUrl = "/".str_replace("/","\/", $nonEscapedApiUrl)."$/";
        return $apiUrl;
    }

    function matchRegex($pattern, $regexString):array{
        $matches = [];
//        echo $regexString."<br>\n";
//        echo $pattern."<br>\n";
        $hit = preg_match($regexString, $pattern, $matches);
        if($hit > 0){
            array_shift($matches);
            return array("hit" => true, "matches" => $matches);
        }
        return array("hit" => false, "matches" => $matches);

    }
}