<?php
namespace App\Container;

class Container implements IContainer{

    function __get($property){
        throw new \Exception("Accessed undefined property $property");
    }
}