<?php

namespace App\Transformers;

use App\Entities\Quiz;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class QuizTransformer extends TransformerAbstract
{
    public function transform(Quiz $quiz)
    {
        return [
            'quiz_delay_hrs' => $quiz->quiz_delay_hrs,
            'quiz_order_percent' => $quiz->quiz_order_percent,
            'quiz_min_order' => $quiz->quiz_min_order,
            'quiz_bonus_order' => $quiz->quiz_bonus_order,
            'quiz_bonus_expire' => $quiz->quiz_bonus_expire,
            'quiz_answer_sec' => $quiz->quiz_answer_sec,
            'quiz_prize' => $quiz->quiz_prize
        ];
    }
}