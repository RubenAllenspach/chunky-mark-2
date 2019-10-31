<?php

namespace Controller;

class StudyController
{
    public function show($dc, $request)
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
