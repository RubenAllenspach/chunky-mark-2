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
        return $dc['twig']->render('new-text.twig', []);
    }

    /**
     * Callback
     */
    public function showAll($dc, $request)
    {
        $texts = $dc['db']->get("SELECT * FROM texts WHERE deleted=0");

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
            isset($request['file']['audio']['type']) && in_array($request['file']['audio']['type'], $mimes)
        ) {
            $audio_name = $this->storeAudio($request['file']['audio']['tmp_name'], $request['file']['audio']['name']);

            $dc['db']->query(
                "INSERT INTO texts (
                    `title`,
                    `text`,
                    `audio`
                ) VALUES (
                    :title,
                    :text,
                    :audio
                )",
                [
                    ':title' => $request['form']['title'],
                    ':text'  => $request['form']['text'],
                    ':audio' => $audio_name
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
