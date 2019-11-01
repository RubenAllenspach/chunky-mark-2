<?php

namespace Controller;

class TextController
{
    private $audio_path = __DIR__ . '/../../public/audio/';

    /**
     * Generates a random string
     *
     * @param int $length
     *
     * @return string
     */
    private function randomString($length=8): string
    {
        return substr(str_shuffle(MD5(microtime())), 0, $length);
    }

    /**
     * Moves uploaded audio file to correct folder
     *
     * @param string $filename
     * @param string $new_filename
     *
     * @return string
     */
    private function storeAudio($filename, $new_filename): string
    {
        if (file_exists($this->audio_path . $new_filename)) {
            $filename_pathinfo = pathinfo($new_filename);

            $name = $filename_pathinfo['filename'];
            $extension = $filename_pathinfo['extension'];

            $new_filename = $name . $this->randomString() . '.' . $extension;
        }

        move_uploaded_file($filename, $this->audio_path . $new_filename);

        return $new_filename;
    }

    /**
     * Callback
     */
    public function showNew($dc, $request)
    {
        $languages = $dc['db']->get("SELECT * FROM languages WHERE deleted=0");

        return $dc['twig']->render(
            'new-text.twig',
            ['languages' => $languages]
        );
    }

    /**
     * Callback
     */
    public function showAll($dc, $request)
    {
        // get texts with corresponding language title
        $texts = $dc['db']->get(
            "SELECT
                texts.*,
                languages.title AS language_title
            FROM texts
                INNER JOIN languages
                    ON languages.id=texts.fk_language
            WHERE texts.deleted=0"
        );

        return $dc['twig']->render('all-texts.twig', ['texts' => $texts]);
    }

    /**
     * Callback
     */
    public function delete($dc, $request)
    {
        $dc['db']->query(
            "UPDATE texts SET deleted=1 WHERE id=:id",
            [':id' => $request['param']['id']]
        );

        header('Location: /text/all');

        return '';
    }

    private function storeAtoms($db, $text_id, $text, $language_id)
    {
        $character_range = $db->var(
            "SELECT character_range FROM languages WHERE id=:id",
            [':id' => $language_id]
        );

        // "cut" before and after a word
        $r_split_atom = '/(
            (?<=[^' . $character_range . '])
            (?=[' . $character_range . '])
            |
            (?<=[' . $character_range . '])
            (?=[^' . $character_range . '])
        )/xu';

        $atoms = preg_split($r_split_atom, trim($text));

        // regex to match a word in this language
        $r_word = '/^[' . $character_range . ']+$/xu';

        $i = 1;
        foreach ($atoms as $atom) {
            $db->query(
                'INSERT INTO text_atoms (
                    chars,
                    fk_text,
                    is_word,
                    `order`
                ) VALUES (
                    :chars,
                    :fk_text,
                    :is_word,
                    :order
                )',
                [
                    ':chars'   => $atom,
                    ':fk_text' => $text_id,
                    ':is_word' => (
                        preg_match(
                            // regex for word match
                            $r_word,
                            $atom
                        ) === 1 ? 1 : 0
                    ),
                    ':order'   => $i
                ]
            );

            $i++;
        }
    }

    /**
     * Callback
     */
    public function processAtoms($dc, $request)
    {
        $texts = $dc['db']->get("SELECT * FROM texts WHERE deleted=0");
        $atoms = $dc['db']->get("SELECT DISTINCT fk_text FROM text_atoms");

        $atom_text_ids = array_map(function ($a) { return $a['fk_text']; }, $atoms);

        $processed = [];

        foreach ($texts as $text) {
            if (!in_array($text['id'], $atom_text_ids)) {
                $processed[] = $text['id'];

                $this->storeAtoms($dc['db'], $text['id'], $text['text'], $text['fk_language']);
            }
        }

        header('Content-Type: application/json');

        return json_encode($processed);
    }

    /**
     * Callback
     */
    public function rebuildAtoms($dc, $request)
    {
        // delete all atoms
        $dc['db']->query("DELETE FROM text_atoms");

        $texts = $dc['db']->get("SELECT * FROM texts WHERE deleted=0");

        $processed = [];

        foreach ($texts as $text) {
            $processed[] = $text['id'];

            $this->storeAtoms($dc['db'], $text['id'], $text['text'], $text['fk_language']);
        }

        header('Content-Type: application/json');

        return json_encode($processed);
    }

    /**
     * Callback
     */
    public function store($dc, $request)
    {
        header('Content-Type: application/json');

        $mimes = [
            'audio/mp3',
            'audio/mpeg',
            'audio/vnd.wav',
            'audio/ogg'
        ];

        if (
            isset($request['form']['title']) && strlen($request['form']['title']) > 0 &&
            isset($request['form']['text']) && strlen($request['form']['text']) > 0 &&
            isset($request['form']['language']) && intval($request['form']['language']) > 0 &&
            isset($request['file']['audio']['type']) && in_array($request['file']['audio']['type'], $mimes)
        ) {
            $audio_name = $this->storeAudio($request['file']['audio']['tmp_name'], $request['file']['audio']['name']);

            $dc['db']->query(
                "INSERT INTO texts (
                    `title`,
                    `text`,
                    `fk_language`,
                    `audio`
                ) VALUES (
                    :title,
                    :text,
                    :fk_language,
                    :audio
                )",
                [
                    ':title'        => trim($request['form']['title']),
                    ':text'         => trim($request['form']['text']),
                    ':fk_language'  => intval($request['form']['language']),
                    ':audio'        => $audio_name
                ]
            );

            $last_id = $dc['db']->var("SELECT seq FROM sqlite_sequence WHERE name=\"texts\"");

            $this->storeAtoms($dc['db'], (int) $last_id, $request['form']['text'], $request['form']['language']);

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
