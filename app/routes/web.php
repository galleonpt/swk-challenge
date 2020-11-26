<?php

$router->get(
    '/',
    function () use ($router) {
        return "Hello from swk dev team";
    }
);

$router->get("/videos","VideoController@show");
$router->post("/videos","VideoController@create");
$router->put("/videos/{id}","VideoController@update");
$router->delete("/videos/{id}","VideoController@delete");