<?php

namespace Controller;

class UtilController
{
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

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function processAtoms($dc, $request)
    {
        $texts = $dc['db']->get("SELECT * FROM texts WHERE deleted=0");
        $atoms = $dc['db']->get("SELECT DISTINCT fk_text FROM text_atoms");

        $atom_text_ids = array_map(
            function ($a) {
                return $a['fk_text'];
            },
            $atoms
        );

        $processed = [];

        foreach ($texts as $text) {
            if (!in_array($text['id'], $atom_text_ids)) {
                $processed[] = $text['id'];

                (new TextController())->storeAtoms($dc['db'], $text['id'], $text['text'], $text['fk_language']);
            }
        }

        header('Content-Type: application/json');

        return json_encode($processed);
    }

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function rebuildAtoms($dc, $request)
    {
        // delete all atoms and linked data
        $dc['db']->query("DELETE FROM text_atoms");
        // reset auto increment
        $dc['db']->query("DELETE FROM sqlite_sequence WHERE name='text_atoms'");

        $dc['db']->query("DELETE FROM text_atom_color");
        $dc['db']->query("DELETE FROM sqlite_sequence WHERE name='text_atom_color'");

        $dc['db']->query("DELETE FROM text_atom_translation");
        $dc['db']->query("DELETE FROM sqlite_sequence WHERE name='text_atom_translation'");

        $texts = $dc['db']->get("SELECT * FROM texts WHERE deleted=0");

        $processed = [];

        foreach ($texts as $text) {
            $processed[] = $text['id'];

            (new TextController())->storeAtoms($dc['db'], $text['id'], $text['text'], $text['fk_language']);
        }

        header('Content-Type: application/json');

        return json_encode($processed);
    }

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function cleanupDB($dc, $request)
    {
        $texts = $dc['db']->get("SELECT * FROM texts WHERE deleted=1");

        $processed = [];

        foreach ($texts as $text) {
            // delete all highlights of this text
            $dc['db']->query(
                "DELETE FROM text_atom_color
                WHERE fk_text_atom IN (
                    SELECT id
                    FROM text_atoms
                    WHERE fk_text=:fk_text
                )",
                [':fk_text' => $text['id']]
            );

            // delete all translations of this text
            $dc['db']->query(
                "DELETE FROM text_atom_translation
                WHERE fk_text_atom IN (
                    SELECT id
                    FROM text_atoms
                    WHERE fk_text=:fk_text
                )",
                [':fk_text' => $text['id']]
            );

            // delete atoms of this text
            $dc['db']->query(
                "DELETE FROM text_atoms WHERE fk_text=:fk_text",
                [':fk_text' => $text['id']]
            );

            $processed[] = $text['id'];
        }

        // eventually delete all texts that are flagged deleted
        $dc['db']->query("DELETE FROM texts WHERE deleted=1");

        header('Content-Type: application/json');

        return json_encode($processed);
    }
}
