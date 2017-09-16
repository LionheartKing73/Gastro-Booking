<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public $table = "quiz_question";

    public $primaryKey = "ID";
}
