<?php

namespace Controller;

class StudyController
{
    public function show($dc, $request)
    {
        $db = $dc['db'];

        $stmt = $db->prepare('SELECT * FROM texts WHERE id=:id');
        $stmt->execute([':id' => $request['param']['id']]);
        $row = $stmt->fetch();

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
