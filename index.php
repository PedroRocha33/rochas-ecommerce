<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

define("ROOT_PATH", __DIR__);

require __DIR__ . "/vendor/autoload.php";
require_once "source/Core/Config.php";
require_once "source/Core/Helpers.php";

use CoffeeCode\Router\Router;

ob_start();

/*
|--------------------------------------------------------------------------
| ROTER
|--------------------------------------------------------------------------
*/
$route = new Router("http://localhost/rochas", ":");
$route->namespace("Source\\Web");

/*
|--------------------------------------------------------------------------
| SITE (PÚBLICO)
|--------------------------------------------------------------------------
*/
$route->get("/", "ProductController:index");
$route->get("/checkout", "Site:checkout");
$route->get("/login", "Site:login");
$route->post("/login", "Site:login");

/*
|--------------------------------------------------------------------------
| APP (PAINEL)
|--------------------------------------------------------------------------
*/
$route->group("/app");
$route->get("/", "App:home");
$route->get("/profile", "App:profile");
$route->get("/edit-profile", "App:editprofile");
$route->post("/edit-profile", "App:editprofile");
$route->group(null);

/*
|--------------------------------------------------------------------------
| ERROS
|--------------------------------------------------------------------------
*/
$route->get("/ops/{errcode}", "Site:error");

/*
|--------------------------------------------------------------------------
| DISPATCH
|--------------------------------------------------------------------------
*/
$route->dispatch();

if ($route->error()) {
    $route->redirect("/ops/{$route->error()}");
}

ob_end_flush();
