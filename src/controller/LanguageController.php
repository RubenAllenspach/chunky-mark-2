<?php

namespace Controller;

class LanguageController
{
    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function showNew($dc, $request)
    {
        return $dc['twig']->render('new-language.twig', []);
    }

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
        $languages = $dc['db']->get("SELECT * FROM languages WHERE deleted=0");

        return $dc['twig']->render('all-languages.twig', ['languages' => $languages]);
    }

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function delete($dc, $request)
    {
        $dc['db']->query(
            "UPDATE languages SET deleted=1 WHERE id=:id",
            [':id' => $request['param']['id']]
        );

        header('Location: /language/all');

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
    public function store($dc, $request)
    {
        header('Content-Type: application/json');

        if (
            isset($request['form']['title']) && strlen($request['form']['title']) > 0 &&
            isset($request['form']['character_range']) && strlen($request['form']['character_range']) > 0
        ) {
            $dc['db']->query(
                "INSERT INTO languages (
                    `title`,
                    `character_range`
                ) VALUES (
                    :title,
                    :character_range
                )",
                [
                    ':title'           => $request['form']['title'],
                    ':character_range' => $request['form']['character_range']
                ]
            );

            return json_encode(
                [
                    'success' => 1
                ]
            );
        } else {
            return json_encode(
                [
                    'success' => 0,
                    'msg' => 'Füllen sie alle Felder korrekt aus'
                ]
            );
        }
    }
}
