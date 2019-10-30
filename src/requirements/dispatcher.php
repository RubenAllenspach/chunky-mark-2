<?php

$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/study/{id:\d+}', 'Controller\StudyController:show');

        $r->addRoute('GET', '/text/new', 'Controller\TextController:showNew');
        $r->addRoute('POST', '/text/new', 'Controller\TextController:store');

        $r->addRoute('GET', '/text/all', 'Controller\TextController:showAll');
    }
);
