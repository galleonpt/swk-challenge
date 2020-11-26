<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Http\Controllers\YoutuberController as Youtuber;
use App\Models\VideoModel;

class VideoController extends BaseController
{
  private $yt;

  public function __construct(Youtuber $youtuber){
    $this->yt = $youtuber;
  }

  /**
   * Obter o Url de um canal do youtube
   */
  public function getChannelUrl($url){
    $response = file_get_contents($url);
    $regex = '/"externalId":"([^"]+)"/m';

    preg_match_all($regex, $response, $matches, PREG_SET_ORDER, 0);

    $baseUrl='https://www.youtube.com/feeds/videos.xml?channel_id=';

    return $baseUrl.$matches[0][1];
  }

  /**
   * Obter os dados dos videos
   */
  public function getVideoInfo($requestUrl){

    $url=$this->getChannelUrl($requestUrl);
    $XmlToString = file_get_contents($url);

    $regex = '/<[a-z]+:[a-z]+>|<\/[a-z]+:[a-z]+>/m';
    preg_match_all($regex, $XmlToString, $matches, PREG_SET_ORDER, 0);

    foreach($matches as $match)
    {  
      $XmlToString=str_replace($match[0], str_replace(":","",$match[0]), $XmlToString);
    }

    $finalXml = simplexml_load_string($XmlToString);

    $youtuber = $finalXml->author->name;
    
    $entries=array();

    foreach($finalXml->entry as $entry){
      array_push($entries, array("title"=>$entry->title, "url"=>$entry->link['href'], "description"=>$entry->mediagroup->mediadescription));
    }
    return array("youtuber"=>$youtuber, "entries"=>$entries);
  }

  /**
   * Criar videos
   */
  public function create(Request $request){    
    try{
      if(!isset($request->all()['url'])){
        return response("Invalide URL", 404);
      }
      
      $url=$request->all()['url'];
  
      $videoData=$this->getVideoInfo($url);
      
      $youtuberData=$this->yt->create($videoData['youtuber']);
  
      foreach($videoData['entries'] as $element){
  
        $youtuber = VideoModel::create([
          'title' => $element['title'],
          'url'=>$element['url'],
          'description'=>$element['description'],
          'id_youtuber'=>$youtuberData->id
        ]);
      }
      return response("Videos imported successfully!", 201);
    }catch(Exception $e){
      return response($e->getMessage(),500);
    };
  }
  
  /**
   * Listar todos os videos
   */
  public function show(){
    try{
      $videos= VideoModel::all();

      return response($videos, 200);
    }catch(Exception $e){
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Apagar um video
   */
  public function delete($id){
    try{
      $video= VideoModel::where('id',$id)->delete();

      return response("Video successfully deleted!", 200);
    }catch(Exception $e){
      return response($e->getMessage(), 500);
    }
  }

  /**
   * Update dos dados de um video
   */
  public function update($id, Request $request){
    $video= VideoModel::where('id',$id);

    if(!isset($video)){
      return response("Video not found!", 404);
    }

    if(!isset($request->all()['title']) && !isset($request->all()['description'])){
      return response("Invalid data!", 404);
    }

    if(isset($request->all()['title'])){
      $video->update(['title'=>$request->all()['title']]);
    }

    if(isset($request->all()['description'])){
      $video->update(['description'=>$request->all()['description']]);
    }

    return response("Video data updated!", 200);
  }
}
