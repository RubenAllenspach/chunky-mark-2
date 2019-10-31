<?php

namespace Controller;

class HomeController
{
    /**
     * Callback
     */
    public function redirectHome($dc, $request)
    {
        header("Location: /text/all");

        return "";
    }
}
