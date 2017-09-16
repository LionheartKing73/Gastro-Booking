<?php

namespace App\Transformers;

use App\Entities\QuizClient;
use League\Fractal\TransformerAbstract;


class QuizClientTransformer extends TransformerAbstract
{
    public function transform(QuizClient $quizClient)
    {
        return [
            'q_group' => $quizClient->ID_quiz_question,
            'answered' => $quizClient->answered,
            'quiz_percentage' => $quizClient->quiz_percentage
        ];
    }
}