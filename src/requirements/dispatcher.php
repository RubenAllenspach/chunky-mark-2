<?php

$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/', 'Controller\HomeController:redirectHome');

        $r->addRoute('GET', '/db', 'Controller\HomeController:doDB');

        $r->addRoute('GET', '/study/{id:\d+}', 'Controller\StudyController:show');

        $r->addRoute('GET', '/text/new', 'Controller\TextController:showNew');
        $r->addRoute('POST', '/text/new', 'Controller\TextController:store');
        $r->addRoute('GET', '/text/delete/{id:\d+}', 'Controller\TextController:delete');
        $r->addRoute('GET', '/text/all', 'Controller\TextController:showAll');

        $r->addRoute('GET', '/language/new', 'Controller\LanguageController:showNew');
        $r->addRoute('POST', '/language/new', 'Controller\LanguageController:store');
        $r->addRoute('GET', '/language/delete/{id:\d+}', 'Controller\LanguageController:delete');
        $r->addRoute('GET', '/language/all', 'Controller\LanguageController:showAll');
    }
);
