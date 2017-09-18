# codeigniter-injection-framework  
 
 - [Introduction](#introduction)
 - [Installation](#installation-guide)
 - [Usage](#usage-guide)  
 
 ## Introduction
This is a CodeIgniter framework written entirely in PHP using reflection for 
validation and dependency injection. It is meant to work with, but does not depend on, a
code completion plugin for PHPStorm. You can find more details on
code completion plugins on github. The framework uses CodeIgniter post_build hooks
to inspect the requested controller and method using PHP reflection and then
validate them and inject configurations and models in class variables. 

## Installation guide
All source files are in their respective folders, so you must copy them to your project.
In addition to this, you must also modify the **config/config.php** file. Set the 
*subclass_prefix* to *'MY_'* and set *enable_hooks* to true. After you've done this, 
make sure the *url* helper is loaded (either load it in MY_Controller or put it in 
**config/autoload.php**). If you plan on using the user permission supplied by this 
framework, then after user login you must save in session user data "userInfo" some 
information about the user in the following format:
```php
    array(
        'Id' => 0, //optional
        'Username' => 'john',
        'Flags' => 3
    );
```

## Usage Guide
The framework uses PHPDoc comments to validate methods and classes and inject models
and configs. Next, we'll take a look at the 3 main files: [**Validator.php**,](#validator) 
[**Injector.php**](#injector) and [**ExitHandler.php**](#exit-handler)

### Validator
Is used to validate classes and methods alike. It has 5 methods that can be used:
 - **ajaxRequest**
    - validates an ajax request
    ```php
    /**
     * @ajaxRequest()
     */
    public function method_in_a_controller(){...}
    ```
 - **permission**
    - requires storing user data in session in the format described above.
    - uses flags defined in *constants.php* to determine user rights and permission.
    For example, there can be 2 flags: `USER_READ = 1` and `USER_WRITE = 2`.
    ```php
    /**
     * @permission(USER_READ)
     */
    public function read_data(){...}
     
    /**
     * @permission(USER_WRITE)
     */
    class Writer extends MY_Controller{...}
    ```
    
 - **session**
    - loads the session library for a class or method
    - using the **session** annotation means you can't use session in the constructor.
    ```php
    /**
     * @session()
     */
    class Controller extends MY_Controller{..}
    ```
    
 - **auth**
    - specifies that a controller or method requires authentication
    - requires the session library
    - checks whether in session there exists an array under the name of 'userInfo'
    ```php
    /**
     * @session()
     * @auth()
     */
    class Controller extends MY_Controller{..}
    ```
 
 ### Injector  
 Is used to inject models and configurations in controllers. It has 2 main functions
 that can only be applied to variables:
 - **model**
    - Injects a model in a class variable visible to the controller (does not work for
    private variables declared in *MY_Controller* since they are not visible from the 
    child controller)
    - is equivalent to `$this->load->model(ModelName, "o_model_name")`
    ```php
       class Login extends MY_Controller{
           /**
            * @var ModelUser
            * @model(ModelUser)
            */
           private $o_user_model;
       }
    ```
    - a special feature for when the model is known only at runtime is the 
    *$construct* parameter. Yo use it, initialize the variable in the `__construct()`
    method with a string representing the name of the model. Once the constructor has 
    finished executing, the hook will initialize the variable with the given model.
    In the example below, the model `ModelUser` will be injected in `$o_model`.
    ```php
       class Login extends MY_Controller{
           
           /**
            * @model($construct)
            */
           private $o_model;
        
           public function __construct()
           {
               parent::__construct();
               $this->o_model = 'ModelUser';
           }
        
           public function index()
           {
               $a_users = $this->o_model->list_users();
               ....
           }
       }
    ```
    - if the hook fails to load the model, the exit handler is called
    
 - **config**
    - This function loads a configuration item into a variable. It is equivalent to
    `$this->variable = $this->config->item("some_config_item")`
    - it also accepts an optional secondary variable stating the expected type of the 
    config item. If loading the config failed or if the types do not match, 
    then the exit handler is called.
    ```php
       /**
        * @config(item, array)
        */
       private $a_var;
    ```
    - the config item can also be specified at run time if the *$construct* parameter 
    is used
    ```php
       /**
        * @config($construct, integer)
        */
       private $a_var;
    ```
 
 ### Exit Handler
 Contains methods that are called when validations or injections fail. The main functions
 that are called are `set_ajax_request()` (that should not be tampered with unless you 
 know what you're doing), `redirect_login()` and `exit_ci()`. The last two are application
 dependant and should be modified by developers according to their needs. This class
 should not suffer any modifications and should remain unchanged when updating the
 framework.