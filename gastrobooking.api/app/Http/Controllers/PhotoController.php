<?php

namespace App\Http\Controllers;

use App\Entities\Photo;
use App\Entities\Restaurant;
use App\Entities\MenuList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;
use Mockery\CountValidator\Exception;

use App\Http\Requests;
use Webpatser\Uuid\Uuid;

class PhotoController extends Controller
{
    public function store($item_id, $item_type){

      if ($item_type=='delete') {
        # code...
        $rest = MenuList::find($item_id);

        $filepath="uploads/items/".$rest->photo;
        unlink($filepath);
        $rest->photo="";
        $rest->save();
        return ["error" => $filepath];
      }
      if ($item_type=='items') {
        # code...
        $file = Input::file('file');

          $ids = explode(',',$item_id);
          $item_restaurant = $ids[1];
          $item_menu = $ids[0];

        $rest = MenuList::find($item_menu);

        $lang = $rest->lang ? $rest->lang : "CZE";
        $item_type = $item_type . '/' . $lang . '/' . $item_restaurant;

        if ($file){

            $extension = $file->guessExtension();
            if (!file_exists('uploads/' . $item_type)) {
                mkdir('uploads/' . $item_type, 0777, true);
            }
            $destination_path = "uploads/" . $item_type . "/";
            $name = uniqid().".".$extension;

            $small = "400x300_" . $name;
            $upload_success = $file->move($destination_path, $name);

            Image::make($destination_path . $name)->fit(400,300, function($constraint){
                $constraint->upsize();
            })->save($destination_path . "400x300_" . $name);
            if (file_exists($destination_path.$name)) {
              unlink($destination_path.$name);
            }

            $dest= $lang.'/'.$item_restaurant."/400x300_" . $name;

            if ($upload_success){
                if($rest->photo!=""){
                  $filepath="uploads/items/".$rest->photo;

                  if (file_exists($filepath)) {
                    unlink($filepath);
                  }
                }
                $rest->photo=$dest;
                $rest->save();


            }else{
              return ["error" => "update failure"];
            }

        } else {
          $rest->photo='';
          $rest->save();
            return $rest;
        }
        //return Restaurant::with('photos')->find($item_id);
        return $rest;
      }else{
        $file = Input::file('file');
        $itm_type = $item_type;
        $rest = Restaurant::find($item_id);
        $lang = $rest->lang ? $rest->lang : "CZE";
        $item_type = $item_type . '/' . $lang . '/' . $rest->id;
        if ($file){
            $extension = $file->getClientOriginalExtension();
            if (!file_exists('uploads/' . $item_type)) {
                mkdir('uploads/' . $item_type, 0777, true);
            }
            $destination_path = "uploads/" . $item_type . "/";
            $name = substr($file->getClientOriginalName(), 0, 5)  . substr(sha1(time()), 0, 5) . "." . $extension;
            $small = "400x300_" . $name;
            $upload_success = $file->move($destination_path, $name);
            Image::make($destination_path . $name)->resize(400, null, function($constraint){
                $constraint->aspectRatio();
            })->save($destination_path . "400x300_" . $name);
            if ($upload_success){
                $photo = new Photo();
                $photo->item_id = $item_id;
                $photo->item_type = $itm_type;
                $photo->upload_directory = $destination_path;
                $photo->minified_image_name = $small;
                $photo->original_photo_name = $name;
                $photo->save();
            }

        } else {
            return ["error" => "File not sent"];
        }
        return Restaurant::with('photos')->find($item_id);
      }

    }

    public function storeMultiple(Request $request, $item_id, $item_type){
        $file = Input::file('files');
        for ($i = 0; $i < count($file) ; $i++){
            $extension = $file[$i]->getClientOriginalExtension();
            if (!file_exists('uploads/original/' . $item_type)) {
                mkdir('uploads/original/' . $item_type, 0777, true);
            }
            if (!file_exists('uploads/small/' . $item_type)) {
                mkdir('uploads/small/' . $item_type, 0777, true);
            }

            $original_path = "uploads/original/" . $item_type . "/";
            $small_path = "uploads/small/" . $item_type . "/";
            $orig = substr($file[$i]->getClientOriginalName(), 0, 5)  . sha1(time()) . "." . $extension;
            $small = "400x300_" . $orig;
            $upload_success = $file[$i]->move($original_path, $orig);
            Image::make($original_path . $orig)->resize(400, 300)->save($small_path . $small);
            if ($upload_success){
                $photo = new Photo();
                $photo->id = $id = Uuid::generate(4);
                $photo->item_id = $item_id;
                $photo->item_type = $item_type;
                $photo->directory_path = "uploads/";
                $photo->original_photo = $orig;
                $photo->minimised_photo = $small;
                $photo->save();
            }
        }
        return Restaurant::with('photos')->find($item_id);
    }

    public function remove($photo_id){
        $photo = Photo::find($photo_id);
        $photo->delete();
        return $photo;
    }

    public function deleteByUrl(Request $request){
        $url = explode('/', $request->url);
        $minified =  $url[count($url) -1];
        $photo = Photo::where('minified_image_name', $minified)->first();
        if ($photo){
            $photo->delete();
            if (file_exists($photo->upload_directory . $photo->minified_image_name)){
                unlink($photo->upload_directory . $photo->minified_image_name);
                return $photo;
            }
        }
    }
}
