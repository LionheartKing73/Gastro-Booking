<?php

namespace App\Transformers;

use App\Entities\Photo;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class PhotoTransformer extends TransformerAbstract
{

    public function transform(Photo $photo)
    {
        return [
            'id' => $photo->id,
            'item_type' => $photo->item_type,
            'file_path' => $photo->upload_directory . $photo->minified_image_name


        ];
    }


}