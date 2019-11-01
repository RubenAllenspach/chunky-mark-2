<?php

namespace Controller;

class HomeController
{
    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function redirectHome($dc, $request)
    {
        header('Location: /text/all');

        return '';
    }

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function doDB($dc, $request)
    {
        return $dc['db']->query("-- QUERY") ? 'yay' : 'nay';
    }
}
