# minicore
A simple MVC PHP framework. It uses the PHP PDO for database interactions via the Database and Record classes. The template engine the PHP itself with helper functions. The translations stored in ini files. It has form handling via the Form, Input and Validator classes.

**Warning! Do NOT use in production!**
This framework is in WIP (Work In Progress) state.

## Hello application
1) Create the following directory structure for "cache" and "logs" the application has to have write rights:
```
~/app
    /cache
    /logs
    /templates     
~/minicore
```
2) Create the index.dev.php: 
```php
<?php

require_once 'core/Framework.php';

Framework::dispatch(    
    [ // your app class name
      'MyApp', 
      
      // the config environment
      'dev',
      
      // the config file
      'config.ini.php' 
    ],
    
    [ // the directories where the framework will search for the classes recursively
        'minicore/core',
        'app'
    ] 
);
```
3) Copy the *~/minicore/config.ini.example.php* to *~/config.ini.php* and set the configuration for your environment. The [all] section contains the default values, you can override those in the [dev] section for the dev environment.
4) Copy the *~/minicore/core/templates/layout.phtml* to your *~/app/templates/layout.phtml*.
5) Create *MyApp.php* in the *~/app* directory:
```php
<?php

class MyApp extends App {

    public function __construct($env='dev', $configPath='config.ini.php') {
        parent::__construct($env, $configPath);
        Framework::instance()->add([
            'helloController' => 'HelloController',
        ]);
    }

    public function init() {
        parent::init();
        $this->router->add([
            ['hello/?', 'helloController', 'index']
        ]);
        $this->view->addFolder(':app', 'app/templates');
    }

}
```
6) Create *HelloController.php* in the *~/app* directory:
```php
<?php

class HelloController extends Controller {

    public function index($name) {
        $this->render(':app/hello', ['name' => $name]);
    }

}
```
7) Create the *hello.phtml* template in the *~/app/templates* directory:
```php
<?php use_layout(':app/layout') ?>
<?php start_block('content') ?>
Hello <?= esc($name) ?>!
<?php end_block() ?>
```
You should have the following structure at this point:
```
~/app
    /cache (empty)
    /logs (empty)   
    /templates       
        layout.phtml
        hello.phtml
    MyApp.php
    HelloController.php
~/minicore (the minicore files)
~/config.ini.php
~/index.dev.php
```
Now you can run your Hello application:
```
http://localhost/your_folder/index.dev.php?route=hello/World
```
## Available modules

- [Captcha](https://github.com/goph-R/minicore-captcha) - An example module.
- [DB Route Aliases](https://github.com/goph-R/minicore-db-route-aliases) - Routing aliases stored in database.
- [Users](https://github.com/goph-R/minicore-users) - Users module for: registration with activation email, forgot password, login, logout, settings with new email address activation.
- [CKEditor](https://github.com/goph-R/minicore-ckeditor) - [CKEditor](https://ckeditor.com) input for the forms.
- [Bulma](https://github.com/goph-R/minicore-bulma) - The [Bulma](https://bulma.io) CSS framework.
 

