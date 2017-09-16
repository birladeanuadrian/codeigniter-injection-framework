# codeigniter-injection-framework [Installation](#installation-guide) [Usage](#usage-guide)
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
declare *STATUS_SUCCESS* and *STATUS_UNSUCCESSFUL* in **config/constants.php** and 
make sure the *url* helper is loaded (either load it in MY_Controller or put it in 
**config/autoload.php**). That's it.

## Usage Guide
The framework uses PHPDoc comments to validate methods and classes and inject models
and configs. Next, we'll take a look at the 3 main files: [**Validator.php**,](README.md:22) 
[**Injector.php**](README.md:34) and [**ExitHandler.php**](README.md:36)

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
    
 - **view**
    - does nothing except to mark that a method or a class is loading a view
    ```php
    /**
     * @view()
     */
    public function index(){..}
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
 
 ### ExitHandler
