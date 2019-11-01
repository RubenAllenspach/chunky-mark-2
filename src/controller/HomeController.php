<?php

namespace Controller;

class HomeController
{
    /**
     * Callback
     */
    public function redirectHome($dc, $request)
    {
        header('Location: /text/all');

        return '';
    }

    /**
     * Callback
     */
    public function doDB($dc, $request)
    {
        return $dc['db']->query("-- QUERY") ? 'yay' : 'nay';
    }
}
