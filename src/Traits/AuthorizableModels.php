<?php

namespace Binaryk\LaravelRestify\Traits;

use Binaryk\LaravelRestify\Repositories\Repository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Could be used as a trait in a model class and in a repository class.
 *
 * @property Model resource
 * @author Eduard Lupacescu <eduard.lupacescu@binarcode.com>
 */
trait AuthorizableModels
{
    /**
     * @return static
     */
    public static function newModel()
    {
        return new static;
    }

    /**
     * Determine if the given resource is authorizable.
     *
     * @return bool
     */
    public static function authorizable()
    {
        return ! is_null(Gate::getPolicyFor(static::newModel()));
    }

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     * @throws AuthorizationException
     */
    public function authorizeToShowAny(Request $request)
    {
        if (! static::authorizable()) {
            return;
        }

        if (method_exists(Gate::getPolicyFor(static::newModel()), 'showAny')) {
            $this->authorizeTo($request, 'showAny');
        }
    }

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public static function authorizedToShowAny(Request $request)
    {
        if (! static::authorizable()) {
            return true;
        }

        return method_exists(Gate::getPolicyFor(static::newModel()), 'showAny')
            ? Gate::check('showAny', get_class(static::newModel()))
            : true;
    }

    /**
     * Determine if the resource should be available for the given request (.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     * @throws AuthorizationException
     */
    public function authorizeToShowEvery(Request $request)
    {
        if (! static::authorizable()) {
            return;
        }

        if (method_exists(Gate::getPolicyFor(static::newModel()), 'showEvery')) {
            $this->authorizeTo($request, 'showEvery');
        }
    }

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public static function authorizedToShowEvery(Request $request)
    {
        if (! static::authorizable()) {
            return true;
        }

        return method_exists(Gate::getPolicyFor(static::newModel()), 'showEvery')
            ? Gate::check('showEvery', get_class(static::newModel()))
            : true;
    }

    /**
     * Determine if the current user can view the given resource or throw.
     *
     * @param Request $request
     * @throws AuthorizationException
     */
    public function authorizeToShow(Request $request)
    {
        $this->authorizeTo($request, 'show');
    }

    /**
     * Determine if the current user can view the given resource.
     *
     * @param Request $request
     * @return bool
     */
    public function authorizedToShow(Request $request)
    {
        return $this->authorizedTo($request, 'show');
    }

    /**
     * Determine if the current user can store new repositories or throw an exception.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public static function authorizeToStore(Request $request)
    {
        if (! static::authorizedToStore($request)) {
            throw new AuthorizationException('Unauthorized to store.');
        }
    }

    /**
     * Determine if the current user can store new repositories.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public static function authorizedToStore(Request $request)
    {
        if (static::authorizable()) {
            return Gate::check('store', static::$model);
        }

        return true;
    }

    /**
     * Determine if the current user can update the given resource or throw an exception.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToUpdate(Request $request)
    {
        $this->authorizeTo($request, 'update');
    }

    /**
     * Determine if the current user can update the given resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return $this->authorizedTo($request, 'update');
    }

    /**
     * Determine if the current user can delete the given resource or throw an exception.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToDelete(Request $request)
    {
        $this->authorizeTo($request, 'delete');
    }

    /**
     * Determine if the current user can delete the given resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return $this->authorizedTo($request, 'delete');
    }

    /**
     * Determine if the current user has a given ability.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $ability
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeTo(Request $request, $ability)
    {
        if ($this->authorizedTo($request, $ability) === false) {
            throw new AuthorizationException();
        }
    }

    /**
     * Determine if the current user can view the given resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $ability
     * @return bool
     */
    public function authorizedTo(Request $request, $ability)
    {
        return static::authorizable() ? Gate::check($ability, $this->determineModel()) : true;
    }

    /**
     * Since this trait could be used by a repository or by a model, we have to
     * detect the model from either class.
     *
     * @return AuthorizableModels|Model|mixed|null
     * @throws ModelNotFoundException
     */
    public function determineModel()
    {
        $model = $this->isRepositoryContext() === false ? $this : ($this->resource ?? null);

        if (is_null($model)) {
            throw new ModelNotFoundException(__('Model is not declared in :class', ['class' => self::class]));
        }

        return $model;
    }

    /**
     * Determine if the trait is used by repository or model.
     *
     * @return bool
     */
    public static function isRepositoryContext()
    {
        return new static instanceof Repository;
    }
}
