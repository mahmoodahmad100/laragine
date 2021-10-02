# Laragine
We developed Laragine to make the software development using Laravel faster and cleaner, so you can get things done faster, not only that, Laragine is not about quantity only, it's all about quantity and quality.

### Features
It's very important to know why to use Laragine, here is why:

* A module based, meaning that you can separate all related stuff together

* You can create CRUD operations including the `requests`, `resources`, `models`, `migrations`, `factories`, `views` ...etc by doing simple stuff (don't worry, it's explained in the documentation)

* Unit Tests are also created!

* One clear response for the client side (for example: API response)

* Many helper functions/traits/classes that will come in handy while you develop! (error handeling, adding more info to the logs, security helpers ...etc)

### Getting Started
To get started please check the [documentation](https://yepwoo.com/laragine/docs/index.html)

### Contributions
Please note that Laragine source code isn't in its best version yet, currently our first priority is to rewrite the whole source code again so we can scale fast.

Any contribution including pull requests, issues, suggestions or any thing that will make Laragine better is welcomed and much appreciated.

### Apart from reading the documentation you can also check the below sections to get an idea

### Introduction

To get started, require the package:

```bash
composer require yepwoo/laragine
```

Note that: Laragine currently is working on **Laravel 8** (`^8.0`).

### Install the package

After including Laragine, you have to install it by running the following command:

```bash
php artisan laragine:install
```

### Notes

* Laragine directory will be in the root directory under `Core` directory

* The system response (inclding errors response if you applied what's in `Error Handeling` section) to any request will be as in below examples (`status_code` is the http status code):

**Success Response:**

```json
{
  "is_success": true,
  "status_code": 200,
  "message": "Success.",
  "data": ...,
  "links": ...,
  "meta": ...
}
```

`links` and `meta` will be provided if the data is **paginated**.


**Error Response:**

```json
{
  "is_success": false,
  "status_code": 401,
  "message": "Error.",
  "errors": ...
}
```

### Commands

Needed commands to start using the package:

`php artisan laragine:install`

To install the package.

`php artisan laragine:module {ModuleName}`

To create new module, here is an example:

```bash
php artisan laragine:module Todo
```

`php artisan laragine:unit {UnitName} {--module=ModuleName} {--init}`

To initialize the unit with basic stuff (model, API controller and Web Controller) and after running the command you can configure the unit, here is an example:

```bash
php artisan laragine:unit Task --module=Todo --init
```

then navigate to `core/Todo/data/Task.json` and update it like in the following:

```json
{
  "attributes": {
    "name": {
      "type": "string"
    },
    "description": {
      "type": "text",
      "mod": "nullable"
    },
    "priority": {
      "type": "enum:low,medium,high",
      "mod": "nullable|default:medium"
    },
    "is_done": {
      "type": "boolean",
      "mod": "default:false"
    }
  },
  "relations": {
      this feature will be released soon...
  }
}
```

Please note the following:

`attributes`: contains the unit attributes (you can think of attributes as the columns of the table in the database).

`type`: the type of the attribute, please check all available types [here](https://laravel.com/docs/8.x/migrations#available-column-types)

`mod`: it holds the column modifiers in the database, please check all available modifiers from [here](https://laravel.com/docs/8.x/migrations#column-modifiers)

You may have noticed that the values in `type` and `mod` are designed the same way as we do in the validation rules.

`php artisan laragine:unit {UnitName} {--module=ModuleName}`

To create all the related stuff (migration, request, resource, factory, unit test ...etc) based on the previous command:

```bash
php artisan laragine:unit Task --module=Todo
```

### Error Handeling

We recommend to use Laragine to handle the errors in your application, as the package contains one clear structure to send the response back to the clinet side (mobile app, third party system, web app ...etc) so in order for you to do so, you have to do the following:


in `app\Exceptions\Handler.php` use this trait `Yepwoo\Laragine\Traits\Exceptions\Handler` after that use this method `handleExceptions()` inside the `register()` method, here is the full code snippet:

```php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Yepwoo\Laragine\Traits\Exceptions\Handler as LaragineHandler;

class Handler extends ExceptionHandler
{
    use LaragineHandler;

    ...

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->handleExceptions();
    }
}
```

Now, we need to use this helper function `client_validation_response` (it accepts 2 arguments, the first is the rules array and the second (optional) is the start error code) in the validation file `resources\lang\en\validation.php` by assigning the array to a vairable and then return the helper function, here is the full code snippet:

```php
$array = [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',

    ...

];

return client_validation_response($array);
```

You may do the same in `auth.php`

### Middlewares

Useful middlewares to help you protect the system and for better security:

`CheckApiKey`

to check if the client side includes a valid `api-key` header in any API request.

to use it, first add `API_KEY=your_api_key_here` in `.env` file, second in the **kernal** (`app\Http\Kernel.php`) add it in `$routeMiddleware` as below:

```php
protected $routeMiddleware = [
    
    ...

    'api-key' => \Core\Base\Middleware\CheckApiKey::class
];
```

then add it in `$middlewareGroups` in the api middleware that you use, so it might be `api` or `auth:api` so it will be like so:

```php
protected $middlewareGroups = [

    ...

    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Core\Base\Middleware\CheckApiKey::class
    ],
];
```

or like so:

```php
protected $middlewareGroups = [

    ...

    'auth:api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Core\Base\Middleware\CheckApiKey::class
    ],
];
```