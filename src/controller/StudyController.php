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
            "SELECT * FROM text_atoms WHERE fk_text=:fk_text ORDER BY `order`",
            [':fk_text' => $text_id]
        );

        $html = '';

        foreach ($atoms as $atom) {
            if ((int) $atom['is_word'] === 1) {
                $html .= '<span class="word">' . $atom['chars'] . '</span>';
            } else {
                $html .= '<span class="nonword">' . $atom['chars'] . '</span>';
            }
        }

        $paragraphs = preg_split('/(\r\n|\r|\n)+/', $html);
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
     */
    public function showDEPRECATED($dc, $request)
    {
        $row = $dc['db']->getOne(
            "SELECT * FROM texts WHERE id=:id",
            [':id' => $request['param']['id']]
        );

        $paragraphs = preg_split('/(\r\n|\r|\n)+/', $row['text']);
        $paragraphs_html = array_map(
            function ($paragraph) {
                return '<p>' . $paragraph . '</p>';
            },
            $paragraphs
        );

        return $dc['twig']->render(
            'study.twig',
            [
                'title' => $row['title'],
                'audio' => $row['audio'],
                'paragraphs' => implode("\n", $paragraphs_html)
            ]
        );
    }
}
