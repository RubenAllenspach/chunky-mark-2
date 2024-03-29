<?php

$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) {
        // home
        $r->addRoute('GET', '/', 'Controller\HomeController:redirectHome');

        // util - hidden
        $r->addRoute('GET', '/u/do-db', 'Controller\UtilController:doDB');
        $r->addRoute('GET', '/u/cleanup-db', 'Controller\UtilController:cleanupDB');
        $r->addRoute('GET', '/u/cleanup-audio', 'Controller\UtilController:cleanupAudio');
        $r->addRoute('GET', '/u/process-atoms', 'Controller\UtilController:processAtoms');
        $r->addRoute('GET', '/u/rebuild-atoms', 'Controller\UtilController:rebuildAtoms');

        // study
        $r->addRoute('GET', '/study/{id:\d+}', 'Controller\StudyController:show');
        $r->addRoute('POST', '/study/action/color', 'Controller\StudyController:colorWord');
        $r->addRoute('POST', '/study/action/color-remove', 'Controller\StudyController:removeColor');
        $r->addRoute('POST', '/study/action/translation', 'Controller\StudyController:translation');

        // archive - hidden
        $r->addRoute('GET', '/archive', 'Controller\ArchiveController:showAll');
        $r->addRoute('GET', '/archive/restore/{id:\d+}', 'Controller\ArchiveController:restore');

        // text
        $r->addRoute('GET', '/text/new', 'Controller\TextController:showNew');
        $r->addRoute('POST', '/text/new', 'Controller\TextController:store');
        $r->addRoute('GET', '/text/edit/{id:\d+}', 'Controller\TextController:showEdit');
        $r->addRoute('POST', '/text/edit/{id:\d+}', 'Controller\TextController:storeEdit');
        $r->addRoute('GET', '/text/delete/{id:\d+}', 'Controller\TextController:delete');
        $r->addRoute('GET', '/text/all', 'Controller\TextController:showAll');
        $r->addRoute('POST', '/text/toggle-star', 'Controller\TextController:toggleStar');

        // language - hidden
        $r->addRoute('GET', '/language/new', 'Controller\LanguageController:showNew');
        $r->addRoute('POST', '/language/new', 'Controller\LanguageController:store');
        $r->addRoute('GET', '/language/delete/{id:\d+}', 'Controller\LanguageController:delete');
        $r->addRoute('GET', '/language/all', 'Controller\LanguageController:showAll');
    }
);
