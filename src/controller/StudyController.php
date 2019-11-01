<?php

namespace Controller;

class StudyController
{
    /**
     * Builds text from text-atoms
     *
     * @param Lib\SQLiteManager\SQLiteManager $db
     * @param int                             $text_id
     *
     * @return string
     */
    private function buildText($db, $text_id)
    {
        $atoms = $db->get(
            "SELECT
                text_atoms.*,
                text_atom_color.color AS text_atom_color_color
            FROM text_atoms
                LEFT JOIN text_atom_color
                    ON text_atom_color.fk_text_atom=text_atoms.id
            WHERE text_atoms.fk_text=:fk_text
            ORDER BY text_atoms.`order`",
            [':fk_text' => $text_id]
        );

        $html = '';

        foreach ($atoms as $atom) {
            if ((int) $atom['is_word'] === 1) {
                $color = '';

                if ((int) $atom['text_atom_color_color'] > 0) {
                    $color = 'color-' . $atom['text_atom_color_color'];
                }

                $html .= '<span class="word ' . $color . '" data-atomid="' . $atom['id'] . '">' . $atom['chars'] . '</span>';
            } else {
                if (strpos($atom['chars'], "\n") !== false) {
                    /*
                     * When a newline character is in between other nonword symbols (". \n\n 5."),
                     * I can't simply trim it and append a newline.
                     * So I replace the newline characters with "</span>\n<span class=\"nonword\">"
                     * to make sure the HTML is correct. \r and \r\n are replaced with \n when storing
                     * the text, so i don't need to worry about that.
                     */
                    $nl_safe_replace = preg_replace(
                        "/\n+/",
                        "</span>\n<span class=\"nonword\">",
                        $atom['chars']
                    );

                    $html .= '<span class="nonword">' . $nl_safe_replace . '</span>';
                } else {
                    $html .= '<span class="nonword">' . $atom['chars'] . '</span>';
                }
            }
        }

        $paragraphs = preg_split("/\n+/", trim($html));
        $paragraphs_html = array_map(
            function ($paragraph) {
                return '<p>' . $paragraph . '</p>';
            },
            $paragraphs
        );

        return implode("\n", $paragraphs_html);
    }

    /**
     * Callback
     *
     * @param array $dc
     * @param array $request
     *
     * @return string
     */
    public function show($dc, $request)
    {
        $text = $dc['db']->getOne(
            "SELECT * FROM texts WHERE id=:id",
            [':id' => $request['param']['id']]
        );

        $paragraphs_html = $this->buildText($dc['db'], $request['param']['id']);

        return $dc['twig']->render(
            'study.twig',
            [
                'title' => $text['title'],
                'audio' => $text['audio'],
                'paragraphs' => $paragraphs_html
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
    public function colorWord($dc, $request)
    {
        $result = false;

        if (
            isset($request['form']['id']) && intval($request['form']['id']) > 0 &&
            isset($request['form']['color']) && intval($request['form']['color']) > 0
        ) {
            $dc['db']->query(
                "DELETE FROM text_atom_color WHERE fk_text_atom=:fk_text_atom",
                [':fk_text_atom' => intval($request['form']['id'])]
            );

            $result = $dc['db']->query(
                "INSERT INTO text_atom_color (
                    fk_text_atom,
                    color
                ) VALUES (
                    :fk_text_atom,
                    :color
                )",
                [
                    ':fk_text_atom' => intval($request['form']['id']),
                    ':color'        => intval($request['form']['color'])
                ]
            );
        }

        header('Content-Type: application/json');

        return json_encode(
            [
                'success' => $result === true ? 1 : 0
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
    public function removeColor($dc, $request)
    {
        $result = $dc['db']->query(
            "DELETE FROM text_atom_color WHERE fk_text_atom=:fk_text_atom",
            [':fk_text_atom' => intval($request['form']['id'])]
        );

        header('Content-Type: application/json');

        return json_encode(
            [
                'success' => $result === true ? 1 : 0
            ]
        );
    }
}
