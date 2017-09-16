<?php
namespace App\Repositories;


use Illuminate\Support\Facades\DB;
use App;


class WebServiceRepository
{
    public function runSQLQuery($query) {
        $methodName = substr(strtolower($query), 0, 6);
        switch($methodName) {
            case 'select':
                return DB::select($query);
            case 'insert':
                DB::insert($query);
                return DB::select('SELECT LAST_INSERT_ID();')[0]->{'LAST_INSERT_ID()'};
            case 'update':
                return DB::update($query);
            case 'delete':
                return DB::delete($query);
            default:
                App::abort(403, 'This type of query is not allowed.');
        }
    }
}
