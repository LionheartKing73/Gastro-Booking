<?php

namespace App\Transformers;

use App\Entities\Question;
use League\Fractal\TransformerAbstract;


class QuestionTransformer extends TransformerAbstract
{
    public function transform(Question $question)
    {
        return [
            'ID' => $question->ID,
            'q_group' => $question->q_group,
            'q_photo' => $question->q_photo,
            'CZE_question' => $question->CZE_question,
            'CZE_a' => $question->CZE_a,
            'CZE_b' => $question->CZE_b,
            'CZE_c' => $question->CZE_c,
            'CZE_d' => $question->CZE_d,
            'q_right' => $question->q_right,
            'q_note' => $question->q_note,
            'percentage' => $question->percentage,
            'active' => $question->active,
            'ENG_question' => $question->ENG_question,
            'ENG_a' => $question->ENG_a,
            'ENG_b' => $question->ENG_b,
            'ENG_c' => $question->ENG_c,
            'ENG_d' => $question->ENG_d
        ];
    }
}