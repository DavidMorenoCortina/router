# Router - Lite implementation

This is a lite implementation of a PHP router. 


## Usage

Needs a dependency container:

    $container = new Container([
        'jwtKeyName' => 'tests'
    ]);
    
Require to define a PDO dependency:

    container['pdo'] = function(){
        
        return new PDO(...);
    };
    
Instantiate a Router and register dependencies. You can overwrite this dependencies, **jwt** and **request**.

    $router = new Router($this->container);

    $router->registerDependencies();

Define some routes:

    $router->get('/', HomeController::class, 'action');

and, finally, match actual route:

    $route = $this->router->match();
    
With **$route** you can instantiate the controller, execute the action, and call to the **send** method of the response to output it.

    $response->send();
    
## Database

You have to follow instructions from the JWT library.

## Tests

**phpunit-settings.php** must be configured to be able to run tests.

## License

[MIT License](https://opensource.org/licenses/MIT)

## Authors

 - David Moreno Cortina