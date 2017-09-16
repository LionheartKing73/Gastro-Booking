<?php
/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;


use App\Entities\QuizSetting;
use App\Entities\Question;
use App\Entities\QuizClient;
use App\Entities\OrderDetail;
use App\Entities\QuizPrize;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;


class QuizRepository
{
    public function getQuizSetting($lang){
        $quiz = QuizSetting::where(["lang" => $lang]);
        return $quiz->first();
    }

    public function getQuestion(){
	$question = Question::all();
        $user = app('Dingo\Api\Auth\Auth')->user();
        $clientId = $user->client->ID;
        $quizClient = QuizClient::where(["ID_client" => $clientId])->get();
		
        $a = array();
        for ($i=0; $i < count($question); $i++) { 
            $repeated = false;
            for ($j=0; $j < count($quizClient); $j++) { 
                if ($question[$i]->ID == $quizClient[$j]->ID_quiz_question) {
                    $repeated = true;
                }
            }
            if ($repeated == false) {
                array_push($a,$question[$i]);
            }
        }

        if(count($a) == 0) {
            $real_question = Question::orderByRaw("RAND()")->take(1)->get()->first();
        }
        else if(count($a) == 1) {
            $question_id = 1;
            $real_question = $a[$question_id - 1];
        } 
        else {
            $question_id = rand(1, count($a));
            $real_question = $a[$question_id - 1];
        }
    	return $real_question;

    }

    public function getQuizClient($clientId){
        $quizClient = QuizClient::where(["ID_client" => $clientId])->get(); // for get quizClient info
        $quizClient1_cs = QuizClient::where(["ID_client" => $clientId, "bonus" => 0, "lang" => 'CZE'])->orderBy('created_at', 'desc')->first(); // for last answered date
        $quizClient1_en = QuizClient::where(["ID_client" => $clientId, "bonus" => 0, "lang" => 'ENG'])->orderBy('created_at', 'desc')->first(); // for last answered date
        $quizClient2_cs = QuizClient::where(["ID_client" => $clientId, "lang" => 'CZE'])->whereNotNull('percentage_step_update')->orderBy('created_at', 'desc')->first();
        $quizClient2_en = QuizClient::where(["ID_client" => $clientId, "lang" => 'ENG'])->whereNotNull('percentage_step_update')->orderBy('created_at', 'desc')->first();

        $total_questions_cs = 0;
        $right_answers_cs = 0;
        $wrong_answers_cs = 0;
        $unanswered_answers_cs = 0;
        $percentage_discount_cs = 0;
        $daily_percentage_discount_cs = 0;
        $percetage_step_cs = 0;

        $total_questions_en = 0;
        $right_answers_en = 0;
        $wrong_answers_en = 0;
        $unanswered_answers_en = 0;
        $percentage_discount_en = 0;
        $daily_percentage_discount_en = 0;
        $percetage_step_en = 0;

        if ($quizClient) {
        	foreach($quizClient as $single) {
                if ($single->lang == "CZE") {
                    $total_questions_cs ++;
                    if($single->quiz_percentage != 0) $right_answers_cs ++;
                    else if($single->answer == 'x') $unanswered_answers_cs ++;
                    else $wrong_answers_cs ++;

                    $percentage_discount_cs += $single->quiz_percentage;
                    if($single->bonus == 0)
                        $daily_percentage_discount_cs += $single->quiz_percentage;
                } else {
                    $total_questions_en ++;
                    if($single->quiz_percentage != 0) $right_answers_en ++;
                    else if($single->answer == 'x') $unanswered_answers_en ++;
                    else $wrong_answers_en ++;

                    $percentage_discount_en += $single->quiz_percentage;
                    if($single->bonus == 0)
                        $daily_percentage_discount_en += $single->quiz_percentage;
                }
        	}

        	if(!$quizClient2_cs) $percetage_step_cs = 0;
            else $percetage_step_cs = $quizClient2_cs->percentage_step_update;

            if(!$quizClient2_en) $percetage_step_en = 0;
            else $percetage_step_en = $quizClient2_en->percentage_step_update;

        	if(!$quizClient1_cs) $lastanswered_cs = Carbon::create(1970, 1, 1, 0, 0, 0);
        	else $lastanswered_cs = $quizClient1_cs->answered;

            if(!$quizClient1_en) $lastanswered_en = Carbon::create(1970, 1, 1, 0, 0, 0);
        	else $lastanswered_en = $quizClient1_en->answered;

        	return [
                'cs' => [
                    'total_questions' => $total_questions_cs,
                    'right_answers' => $right_answers_cs,
                    'wrong_answers' => $wrong_answers_cs,
                    'unanswered_answers' => $unanswered_answers_cs,
                    'percentage_discount' => $percentage_discount_cs,
                    'daily_percentage_discount' => $daily_percentage_discount_cs,
                    'lastanswered' => $lastanswered_cs,
                    'percentage_step'=> $percetage_step_cs
                ],
                'en' => [
                    'total_questions' => $total_questions_en,
                    'right_answers' => $right_answers_en,
                    'wrong_answers' => $wrong_answers_en,
                    'unanswered_answers' => $unanswered_answers_en,
                    'percentage_discount' => $percentage_discount_en,
                    'daily_percentage_discount' => $daily_percentage_discount_en,
                    'lastanswered' => $lastanswered_en,
                    'percentage_step'=> $percetage_step_en
                ]
        	];
        }
        else
        	return [
                'cs' => [
                    'total_questions' => 0,
                    'right_answers' => 0,
                    'wrong_answers' => 0,
                    'unanswered_answers' => 0,
                    'percentage_discount' => 0,
                    'daily_percentage_discount' => 0,
                    'lastanswered' => Carbon::create(1970, 1, 1, 0, 0, 0),
                    'percentage_step'=> 0
                ],
                'en' => [
                    'total_questions' => 0,
                    'right_answers' => 0,
                    'wrong_answers' => 0,
                    'unanswered_answers' => 0,
                    'percentage_discount' => 0,
                    'daily_percentage_discount' => 0,
                    'lastanswered' => Carbon::create(1970, 1, 1, 0, 0, 0),
                    'percentage_step'=> 0
                ]
        	];
    }

    public function updateLastCrossingTime(){
    	$user = app('Dingo\Api\Auth\Auth')->user();
        $clientId = $user->client->ID;
        $quizClient = QuizClient::where(["ID_client" => $clientId])->whereNotNull('percentage_step_update')->orderBy('created_at', 'desc')->first();
        if(!$quizClient->percentage_step_update)
        	$quizClient->percentage_step_update = 1;
        else $quizClient->percentage_step_update ++;
        $quizClient->save();
        return $quizClient;
    }

    public function getQuizPrize(){
       	$user = app('Dingo\Api\Auth\Auth')->user();
        $clientId = $user->client->ID;
        $quizPrize = QuizPrize::where(["ID_client" => $clientId])->get();
        $response = [
            'cs' => [],
            'en' => []
        ];
        if ($quizPrize) {
        	foreach($quizPrize as $single) {
        		if(OrderDetail::where(["ID" => $single->ID_order])->first()) {
        			$serve_at = OrderDetail::where(["ID" => $single->ID_order])->first()->serve_at;
                    if($single->lang == 'CZE') {
                        array_push($response['cs'], ["serve_at"=>$serve_at, "percentage"=>$single->percentage, "prize"=>$single->prize]);
                    } else {
                        array_push($response['en'], ["serve_at"=>$serve_at, "percentage"=>$single->percentage, "prize"=>$single->prize]);
                    }
        		}
        	}        	
        }
        return $response;
        if($quizPrize)
        	return [
	            'percentage' => $quizPrize->percentage,
	            'prize' => $quizPrize->prize
        	];
        else {
        	$quizPrize = new QuizPrize();
        	$quizPrize->ID_client = $clientId;
        	$quizPrize->ID_order = 0;
        	$quizPrize->percentage = 0;
        	$quizPrize->prize = 0;
        	$quizPrize->save();
        	return $quizPrize;
        }
    }
    public function storeQuizClient($quizResult) {
    	$user = app('Dingo\Api\Auth\Auth')->user();
        $clientId = $user->client->ID;
        
    	$quizClient = new QuizClient();
        $quizClient->ID_quiz_question = $quizResult->ID_quiz;
        $quizClient->ID_client = $clientId;
        $quizClient->bonus = $quizResult->bonus;
        $quizClient->answer = $quizResult->answer;
        $quizClient->answered = $quizResult->answered;
        $quizClient->rate_difficulty = $quizResult->rate_difficulty;
        $quizClient->rate_quality = $quizResult->rate_quality;
        $quizClient->lang = $quizResult->lang;
        if ($quizResult->isRight == true) {
        	$quizClient->quiz_percentage = $quizResult->percentage;
        }
        $quizClient->save();
        return $quizClient;
    }
    public function storeQuizPrize($Id_order, $percentage, $prize, $lang) {
        $user = app('Dingo\Api\Auth\Auth')->user();
        $clientId = $user->client->ID;
        
        $quizPrize = new QuizPrize();
        $quizPrize->ID_client = $clientId;
        $quizPrize->percentage = $percentage;
        $quizPrize->prize = $prize;
        $quizPrize->lang = $lang;       
        $quizPrize->ID_order = $Id_order;

        $quizPrize->save();
        return $quizPrize;
    }    
}
