<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\YoutuberModel;

class YoutuberController extends BaseController
{
  /**
   * Adicionar um youtuber
   */
  public function create($userName){
    try{
      $youtuber = YoutuberModel::create([
        'name' => $userName
      ]);

      $youtuber->save();

      return $youtuber;
    }catch(Exception $e){
      return response($e->getMessage(), 500);
    }
  }

}
