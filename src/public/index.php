<?php
require "../vendor/autoload.php";
use App\App;


$app = new App();

$app->use(function(){
    $this->mysqlConnection = "mysqlConnection_available_in_all_routes";
});

$app->get("/version/current", function (){
    echo "<b>Route:</b> /version/current <hr>\n";

    if(isset($this->mysqlConnection)){
        //do something
    }
});

$app->get("/version/{id}", function (string $id){
    echo " <b>Route:</b> /version/{id} <hr>\n";
    echo "<b>Argument: </b> $id <hr>\n";
});

$app->get("/version/{id}/user/{uid}", function (string $id, string $uid){
    echo " <b>Route:</b> /version/{id}/user/{uid} <hr>";
    echo "<b>Argument: </b> $id \n<hr>";
    echo "<b>Argument2: </b> $uid \n<hr>";
});

$app->run();







