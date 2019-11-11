<?php

namespace Controller;

class ArchiveController
{
    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function showAll($dc, $request)
    {
        // get archived texts with corresponding language title
        $texts = $dc['db']->get(
            "SELECT
                texts.*,
                languages.title AS language_title
            FROM texts
                INNER JOIN languages
                    ON languages.id=texts.fk_language
            WHERE texts.deleted=1
            ORDER BY created DESC"
        );

        return $dc['twig']->render('archive.twig', ['texts' => $texts]);
    }

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function restore($dc, $request)
    {
        $dc['db']->query(
            "UPDATE texts SET deleted=0 WHERE id=:id",
            [':id' => $request['param']['id']]
        );

        header('Location: /archive');

        return '';
    }
}
