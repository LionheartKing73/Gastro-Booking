<?php

namespace App\Transformers;

use App\Entities\Diet;
use League\Fractal\TransformerAbstract;

class DietTransformer extends TransformerAbstract
{
    public function transform(Diet $diet)
    {
        return [
            'id' => $diet->ID,
            'name' => $diet->name,
            'lang' => $diet->lang,
            'cust_order' => $diet->cust_order
        ];
    }
}