<?php

$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) {
        // home
        $r->addRoute('GET', '/', 'Controller\HomeController:redirectHome');

        // util
        $r->addRoute('GET', '/u/db', 'Controller\HomeController:doDB');
        $r->addRoute('GET', '/u/process-atoms', 'Controller\TextController:processAtoms');
        $r->addRoute('GET', '/u/rebuild-atoms', 'Controller\TextController:rebuildAtoms');

        // study
        $r->addRoute('GET', '/study/{id:\d+}', 'Controller\StudyController:show');
        $r->addRoute('POST', '/study/action/color', 'Controller\StudyController:colorWord');
        $r->addRoute('POST', '/study/action/color-remove', 'Controller\StudyController:removeColor');
        $r->addRoute('POST', '/study/action/translation', 'Controller\StudyController:translation');

        // text
        $r->addRoute('GET', '/text/new', 'Controller\TextController:showNew');
        $r->addRoute('POST', '/text/new', 'Controller\TextController:store');
        $r->addRoute('GET', '/text/delete/{id:\d+}', 'Controller\TextController:delete');
        $r->addRoute('GET', '/text/all', 'Controller\TextController:showAll');

        // language
        $r->addRoute('GET', '/language/new', 'Controller\LanguageController:showNew');
        $r->addRoute('POST', '/language/new', 'Controller\LanguageController:store');
        $r->addRoute('GET', '/language/delete/{id:\d+}', 'Controller\LanguageController:delete');
        $r->addRoute('GET', '/language/all', 'Controller\LanguageController:showAll');
    }
);
