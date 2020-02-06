<?php
namespace App;
use App\RouteParser\ARouteParser;
use App\RouteParser\RouteParser;
use App\Container\IContainer;
use App\Container\Container;

class App{

    private array $router;
    private array $activatedRouter;
    private array $middleWareStack;
    private ARouteParser $routeParser;
    private IContainer $container;

    public function __construct(IContainer $container = null, ARouteParser $routeParser = null){

        $this->router = array($_SERVER["REQUEST_METHOD"] => []);
        $this->activatedRouter = array($_SERVER["REQUEST_METHOD"] => []);
        $this->middleWareStack = [];

        if(is_null($routeParser)){
            $this->routeParser = new RouteParser();
        }else{
            $this->routeParser = $routeParser;
        }

        if(is_null($container)){
            $this->container = new Container();
        }else{
            $this->container = $container;
        }

    }

    public function getContainer()
    {
        return $this->container;
    }

    function use(callable $callable){
        $callable = $callable->bindTo($this->container);
        $this->addToMiddlewareStack($callable);
    }

    function get(string $pattern, callable $callable){
        $this->map(["GET"],$pattern, $callable);
    }

    function post(string $pattern, callable $callable){
        $this->map(["POST"],$pattern, $callable);
    }

    function map(array $methods, $pattern, callable $callable){

        if(!$callable instanceof \Closure){
            throw new \Exception("Callable is not a function.");
        }

        $callable = $callable->bindTo($this->container);
        $pattern = $this->routeParser->addSlash($pattern);
        foreach ($methods as $method){
            if(isset($this->router[$method])){
                if(strpos($pattern, "{") !== false){
                    $this->activatedRouter[$method][$this->routeParser->createRegex($pattern)] = $callable;
                }else{
                    $this->router[$method][$pattern] = $callable;
                }
            }
        }
    }

    function addToMiddlewareStack(callable $callable){
        $callable->bindTo($this->container);
        $this->middleWareStack[] = $callable;
    }

    function executeMiddleWareStack(){
        foreach ($this->middleWareStack as $middleWare){
            $middleWare();
        }
    }

    function findRouteMatch(){
        $pattern = $this->routeParser->addSlash($_SERVER['SCRIPT_URL']);
        $callbacks = $this->router[$_SERVER["REQUEST_METHOD"]];

        //searching explicit routes
        if(isset($callbacks[$pattern])){

            $callbacks[$pattern]();
            throw new \Exception("Route found.",9000 );
        }
    }

    function findActivatedRouteMatch(){

        $pattern = $this->routeParser->addSlash($_SERVER['SCRIPT_URL']);
        $routeCallbackPairs = $this->activatedRouter[$_SERVER["REQUEST_METHOD"]];

        foreach ($routeCallbackPairs as $regex => $callback){

            $hitObject = $this->routeParser->matchRegex($pattern, $regex);
            if($hitObject["hit"] == true){
//                print_r($hitObject);
                call_user_func($callback, ...$hitObject["matches"]);
                throw new \Exception("Route found.",9000 );

            }

        }
    }

    function run(){

        try{
            $this->executeMiddleWareStack();
            $this->findRouteMatch();
            $this->findActivatedRouteMatch();

        }catch(\Exception $e){

            if($e->getCode() !== 9000){
                echo $e->getMessage();
                throw $e;
            }
            return;
        }

        echo "<b>Route not found.<b>";
        http_response_code(404);

    }
}