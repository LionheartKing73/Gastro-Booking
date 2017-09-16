<?php

namespace App\Transformers;

use App\Entities\QuizSetting;
use League\Fractal\TransformerAbstract;


class QuizSettingTransformer extends TransformerAbstract
{
    public function transform(QuizSetting $quizSetting)
    {
        return [
            'quiz_delay_hrs' => $quizSetting->quiz_delay_hrs,
            'currency_short' => $quizSetting->currency_short,
            'quiz_order_percent' => $quizSetting->quiz_order_percent,
            'quiz_min_order' => $quizSetting->quiz_min_order,
            'quiz_bonus_order' => $quizSetting->quiz_bonus_order,
            'quiz_bonus_expire' => $quizSetting->quiz_bonus_expire,
            'quiz_answer_sec' => $quizSetting->quiz_answer_sec,
            'quiz_prize' => $quizSetting->quiz_prize
        ];
    }
}