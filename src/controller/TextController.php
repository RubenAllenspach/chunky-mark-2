<?php

namespace Controller;

class TextController
{
    private $audio_path = __DIR__ . '/../../public/audio/';

    const ALLOWED_MIMES = [
        'audio/mp3',
        'audio/mpeg',
        'audio/vnd.wav',
        'audio/m4a',
        'audio/ogg'
    ];

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
        // create audio folder if it does not exist
        if (!file_exists($this->audio_path)) {
            mkdir($this->audio_path, 0777, true);
        }

        $filename_pathinfo = pathinfo($new_filename);

        $name = $filename_pathinfo['filename'];
        $extension = $filename_pathinfo['extension'];

        $slug = \slugify($name);

        if (file_exists($this->audio_path . $slug . '.' . $extension)) {
            $new_filename = $slug . $this->randomString() . '.' . $extension;
        } else {
            $new_filename = $slug . '.' . $extension;
        }

        move_uploaded_file($filename, $this->audio_path . $new_filename);

        return $new_filename;
    }

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
        $languages = $dc['db']->get("SELECT * FROM languages WHERE deleted=0");

        return $dc['twig']->render(
            'new-text.twig',
            ['languages' => $languages]
        );
    }

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function showEdit($dc, $request)
    {
        $languages = $dc['db']->get("SELECT * FROM languages WHERE deleted=0");
        $text = $dc['db']->getOne(
            "SELECT * FROM texts WHERE id=:id",
            [':id' => $request['param']['id']]
        );

        return $dc['twig']->render(
            'edit-text.twig',
            [
                'languages' => $languages,
                'text' => $text
            ]
        );
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
        // get texts with corresponding language title
        $texts = $dc['db']->get(
            "SELECT
                texts.*,
                languages.title AS language_title
            FROM texts
                INNER JOIN languages
                    ON languages.id=texts.fk_language
            WHERE texts.deleted=0
            ORDER BY created DESC"
        );

        return $dc['twig']->render('all-texts.twig', ['texts' => $texts]);
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
            "UPDATE texts SET deleted=1 WHERE id=:id",
            [':id' => $request['param']['id']]
        );

        header('Location: /text/all');

        return '';
    }

    private function storeAtoms($db, $text_id, $text, $language_id)
    {
        // delete preexisting atoms of that text
        $db->query(
            "DELETE FROM text_atoms WHERE fk_text=:fk_text",
            [':fk_text' => $text_id]
        );

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

    private function deleteTextAnnotations($db, $text_id): bool
    {
        $result = [];

        // delete highlights
        $result[] = $db->query(
            "DELETE FROM text_atom_color
            WHERE fk_text_atom IN (
                SELECT id
                FROM text_atoms
                WHERE fk_text=:fk_text
            )",
            [':fk_text' => $text_id]
        );

        // delete translations
        $result[] = $db->query(
            "DELETE FROM text_atom_translation
            WHERE fk_text_atom IN (
                SELECT id
                FROM text_atoms
                WHERE fk_text=:fk_text
            )",
            [':fk_text' => $text_id]
        );

        return !in_array(false, $result);
    }

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function storeEdit($dc, $request)
    {
        header('Content-Type: application/json');

        if (
            isset($request['form']['title']) && strlen($request['form']['title']) > 0 &&
            isset($request['form']['language']) && intval($request['form']['language']) > 0
        ) {
            $text_id = intval($request['param']['id']);

            // store title and language id for sure
            $dc['db']->query(
                "UPDATE texts
                SET
                    `title`=:title,
                    `fk_language`=:fk_language
                WHERE id=:id",
                [
                    ':title'       => trim($request['form']['title']),
                    ':fk_language' => intval($request['form']['language']),
                    ':id'          => $text_id
                ]
            );

            // if audio is available change that too
            if (
                isset($request['file']['audio']['type']) &&
                in_array($request['file']['audio']['type'], self::ALLOWED_MIMES)
            ) {
                $audio_name = $this->storeAudio(
                    $request['file']['audio']['tmp_name'],
                    $request['file']['audio']['name']
                );

                $dc['db']->query(
                    "UPDATE texts SET audio=:audio WHERE id=:id",
                    [
                        ':audio' => $audio_name,
                        ':id'    => $text_id
                    ]
                );
            }

            // get old text for comparison
            $old_text = $dc['db']->var(
                "SELECT `text` FROM texts WHERE id=:id",
                [':id' => $text_id]
            );

            // replace funky newlines with normal ones
            $new_text = str_replace(
                "\r",
                "\n",
                str_replace(
                    "\r\n",
                    "\n",
                    $request['form']['text']
                )
            );

            // process and store new text
            if (
                isset($request['form']['text']) &&
                strlen($request['form']['text']) > 0 &&
                $old_text !== $new_text
            ) {
                $dc['db']->query(
                    "UPDATE texts SET `text`=:text WHERE id=:id",
                    [
                        ':text' => $new_text,
                        ':id'   => $text_id
                    ]
                );

                $this->deleteTextAnnotations($dc['db'], $text_id);

                $this->storeAtoms(
                    $dc['db'],
                    (int) $text_id,
                    $new_text,
                    $request['form']['language']
                );
            }

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
            isset($request['form']['text']) && strlen($request['form']['text']) > 0 &&
            isset($request['form']['language']) && intval($request['form']['language']) > 0 &&
            isset($request['file']['audio']['type']) && in_array($request['file']['audio']['type'], self::ALLOWED_MIMES)
        ) {
            $audio_name = $this->storeAudio($request['file']['audio']['tmp_name'], $request['file']['audio']['name']);

            // replace funky newlines with normal ones
            $text = str_replace(
                "\r",
                "\n",
                str_replace(
                    "\r\n",
                    "\n",
                    $request['form']['text']
                )
            );

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
                    ':text'         => trim($text),
                    ':fk_language'  => intval($request['form']['language']),
                    ':audio'        => $audio_name
                ]
            );

            $last_id = $dc['db']->var("SELECT seq FROM sqlite_sequence WHERE name=\"texts\"");

            $this->storeAtoms($dc['db'], (int) $last_id, $text, $request['form']['language']);

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
