<?php

namespace Binaryk\LaravelRestify\Tests;

use Binaryk\LaravelRestify\LaravelRestifyServiceProvider;
use Binaryk\LaravelRestify\Restify;
use Binaryk\LaravelRestify\Tests\Fixtures\BookRepository;
use Binaryk\LaravelRestify\Tests\Fixtures\PostRepository;
use Binaryk\LaravelRestify\Tests\Fixtures\User;
use Binaryk\LaravelRestify\Tests\Fixtures\UserRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Mockery;
use Orchestra\Testbench\TestCase;

/**
 * @author Eduard Lupacescu <eduard.lupacescu@binarcode.com>
 */
abstract class IntegrationTest extends TestCase
{
    use InteractWithModels;

    /**
     * @var mixed
     */
    protected $authenticatedAs;

    /**
     * @var mixed
     */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        DB::enableQueryLog();
        Hash::driver('bcrypt')->setRounds(4);
        $this->repositoryMock();
        $this->loadMigrations();
        $this->loadRoutes();
        $this->withFactories(__DIR__.'/Factories');
        $this->injectTranslator();
        $this->loadRepositories();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelRestifyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Load the migrations for the test environment.
     *
     * @return void
     */
    protected function loadMigrations()
    {
        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--path' => realpath(__DIR__.DIRECTORY_SEPARATOR.'Migrations'),
        ]);
    }

    public function repositoryMock()
    {
    }

    public function injectTranslator()
    {
        $this->instance('translator', (new class implements Translator {
            public function get($key, array $replace = [], $locale = null)
            {
                return $key;
            }

            public function choice($key, $number, array $replace = [], $locale = null)
            {
                return $key;
            }

            /**
             * {@inheritdoc}
             */
            public function trans($key, array $replace = [], $locale = null)
            {
                return $key;
            }

            /**
             * {@inheritdoc}
             */
            public function getFromJson($key, array $replace = [], $locale = null)
            {
                return $key;
            }

            /**
             * {@inheritdoc}
             */
            public function transChoice($key, $number, array $replace = [], $locale = null)
            {
                return $key;
            }

            /**
             * {@inheritdoc}
             */
            public function getLocale()
            {
                return 'en';
            }

            /**
             * {@inheritdoc}
             */
            public function setLocale($locale)
            {
            }
        }));
    }

    public function loadRoutes()
    {
        Route::post('login', function () {
            // AuthService->login
        });
        Route::post('register', function () {
            // AuthService -> register
        });
        Route::get('email/verify/{id}/{hash}', function () {
            // AuthService -> verify
        })->name('verification.verify')->middleware([
            'signed',
            'throttle:6,1',
        ]);
        Route::post('password/email', function () {
            // AuthService -> sendResetPasswordLinkEmail
        });
        Route::post('password/reset', function () {
            // AuthPassport -> resetPassword
        })->name('password.reset');
    }

    /**
     * @return array
     */
    public function lastQuery()
    {
        $queries = DB::getQueryLog();

        return end($queries);
    }

    public function loadRepositories()
    {
        Restify::repositories([
            UserRepository::class,
            PostRepository::class,
            BookRepository::class,
        ]);
    }

    /**
     * Authenticate as an anonymous user.
     */
    protected function authenticate()
    {
        $this->actingAs($this->authenticatedAs = Mockery::mock(Authenticatable::class));

        $this->authenticatedAs->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->authenticatedAs->shouldReceive('getKey')->andReturn(1);

        return $this;
    }
}
