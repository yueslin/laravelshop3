<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    //用户
    $router->get('users', 'UsersController@index');
    //商品列表
    $router->resource('products', 'ProductsController');
    //    $router->get('products', 'ProductsController@index');
    //    $router->get('products/create', 'ProductsController@create');
    //    $router->post('products', 'ProductsController@store');
    //    $router->get('products/{id}/edit', 'ProductsController@edit');
    //    $router->put('products/{id}', 'ProductsController@update');
    //商品SKU
    $router->resource('skus', 'ProductSkusController');

    //商品列表API
    $router->get('api/productlist', 'ProductSkusController@getProduct')->name('admin.api.productlist');
    //商品属性API
    $router->get('api/attributes/{id}', 'ProductSkusController@getAttributes')->name('admin.api.attributes');
});
