<?php

use Binaryk\LaravelRestify\Http\Middleware\AuthorizeRestify;
use Binaryk\LaravelRestify\Http\Middleware\DispatchRestifyStartingEvent;

return [
    'auth' => [
        /*
        |--------------------------------------------------------------------------
        | Table containing authenticatable resource
        |--------------------------------------------------------------------------
        |
        | This configuration contain the name of the table used for the authentication.
        |
        */

        'table' => 'users',

        /*
        |--------------------------------------------------------------------------
        |
        |--------------------------------------------------------------------------
        |
        | Next you may configure the package you're using for the personal tokens generation,
        | this will be used for the verification of the authenticatable model and provide the
        | authorizable functionality
        |
        | Supported: "passport", "airlock"
        */

        'provider' => 'airlock',
    ],

    /*
    |--------------------------------------------------------------------------
    | Restify Base Route
    |--------------------------------------------------------------------------
    |
    | This configuration is used as a prefix path where Restify will be accessible from.
    | Feel free to change this path to anything you like.
    |
    */

    'base' => '/restify-api',

    /*
    |--------------------------------------------------------------------------
    | Restify Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Restify route, giving you the
    | chance to add your own middleware to this stack or override any of
    | the existing middleware. Or, you can just stick with this stack.
    |
    */

    'middleware' => [
        'api',
        DispatchRestifyStartingEvent::class,
        AuthorizeRestify::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Restify Exception Handler
    |--------------------------------------------------------------------------
    |
    | The exception handler will be attached to any Restify "CRUD" route.
    | It will not be attached to custom routes defined in yours Restify Repositories,
    | if you want to have it for all of the routes, can extend RestifyHandler
    | from your application App\Exceptions\Handler.
    |
    | These will override the main application exception handler, set to null, it will not override it.
    | Having RestifyHandler as a global exception handler is a good recommendation since it
    | will return the exceptions in an API pretty format.
    |
    */
    'exception_handler' => \Binaryk\LaravelRestify\Exceptions\RestifyHandler::class,
];
