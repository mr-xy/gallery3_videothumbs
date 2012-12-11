<?php defined("SYSPATH") or die("No direct script access.");/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2011 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
 
 include_once('class.iptcdata.php'); 
 
class videothumb_Controller extends Controller {
  
  function dialog($item_id) {
    $item = ORM::factory("item", $item_id);
    access::required("view", $item);
    access::required("edit", $item);
    //read iptc
    $i = new iptcdata($item->thumb_path());
    $v = View::factory("videothumb_dialog.html");
    $v->iptcLabels = iptcdata::iptcLabels();
    $v->iptcTags = iptcdata::iptcTags();
    $v->i = $i;
    $v->item = $item;
    //thumb format width
    $v->thumbwidth = module::get_var("gallery", "thumb_size");
    $v->defined_ratio = "";
    
    if(module::is_active("rectangle_thumbs")){
        list ($desired_width, $desired_height) = explode(":", module::get_var("rectangle_thumbs", "aspect_ratio"));
        $v->defined_ratio = $desired_width / $desired_height;
        $v->rectangle_thumb_active = "active";
    }
    $v->videolength = $this->get_duration_manual($item);
    $ratio = $this->get_ratio_manual($item);
    $v->ratio_error = "none";   

    if($_POST['sent']) {
        //keep checked value of radio
        if($_POST['ratio'] == wide) {
            $v->wide_check = "checked='checked'";
        }else{
            $v->normal_check = "checked='checked'";
        }        
        $this->extract_frame_manual($item_id, $v->defined_ratio, $v->thumbwidth);
 
        $v->second = $_POST['second'];
        $v->minute = $_POST['minute'];
        $v->thumbheight = $_POST['height'];
        $i->write($_POST['iptcdata'],$v->iptcTags,$item); 
        json::reply(array("result" => "error", "html" => (string)$v));
    }else{
        $v->thumbheight = round($v->thumbwidth * 9 / 16);   
        if($ratio == 16/9) {
            $v->wide_check = "checked='checked'";
            $v->height_check = "";            
        }elseif($ratio == 4/3){
            $v->wide_check = "";   
            $v->height_check = "checked='checked'";               
        }else{
            $v->ratio_error = "block";
        }     
        print $v;
    }
  }
  
  
  static function extract_frame_manual($item, $defined_ratio, $defined_width) {
     
    $ffmpeg = movie::find_ffmpeg();
    if (empty($ffmpeg)) {
      throw new Exception("@todo MISSING_FFMPEG");
    }
        
    $input_file = $_POST['file_path'];
    $output_file = $_POST['thumb_path'];  
   
    // Convert the movie to a JPG first
    $output_file = preg_replace("/...$/", "jpg", $output_file);
    if(empty($_POST['minute'])) {
        $_POST['minute'] = 0;
    };
    if(empty($_POST['second'])) {
        $_POST['second'] = 0;
    };
    $second = date("H:i:s", mktime(0,$_POST['minute'],$_POST['second'],0,0,0,0));
    
    
    if(module::is_active("rectangle_thumbs")){
        $size = "";
    }else{
        $size = "-s ".$_POST['width']."x".$_POST['height'];        
    }
    
        
    $cmd = escapeshellcmd($ffmpeg) . " -ss ".$second." -i " . escapeshellarg($input_file) .
      " -an -an -r 1 -vframes 1" .
      " -y -f mjpeg " . $size . " ". escapeshellarg($output_file) . " 2>&1";
    exec($cmd);    

    clearstatcache();  // use $filename parameter when PHP_version is 5.3+
    if (filesize($output_file) == 0) {
        echo "filesize=0";
      // Maybe the movie is shorter, fall back to the first frame.
      $cmd = escapeshellcmd($ffmpeg) . " -i " . escapeshellarg($input_file) .
        " -an -an -r 1 -vframes 1" .
        " -y -f mjpeg ". $size . " " . escapeshellarg($output_file) . " 2>&1";
      exec($cmd);
            
      clearstatcache();
      if (filesize($output_file) == 0) {
        throw new Exception("@todo FFMPEG_FAILED");
      }
    }
    
    if(module::is_active("rectangle_thumbs")){
      rectangle_thumbs_graphics::crop_to_aspect_ratio($output_file, $output_file);     
      $ratio = $defined_ratio;
      $new_width = $defined_width;
      $new_height = $new_width / $ratio;
      
      Image::factory($output_file)
      ->resize($new_width, $new_height)
      ->quality(module::get_var("gallery", "image_quality"))
      ->save($output_file);
      
    }
    
  }
  
  static function get_duration_manual($item) {
    $ffmpeg = movie::find_ffmpeg();
    if (empty($ffmpeg)) {
      throw new Exception("@todo MISSING_FFMPEG");
    }
    $cmd = escapeshellcmd($ffmpeg) . " -i " . escapeshellarg($item->file_path()) . " 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//";  
    $result = exec($cmd);
    if (preg_match("/(\d+):(\d+):(\d+\.\d+)/", $result)) {
        $regs = explode(":",$result);  
        //remove leading 0' of minute'        
        $regs[1] = $regs[1] * 1;
        return $regs[1].":".round($regs[2]);        
      //return 3600 * $regs[1] + 60 * $regs[2] + $regs[3];
    } else{
        $regs = floor($result/60).':'.($result % 60); 
        return $regs;
    }
  }    
  
    static function get_ratio_manual($item) {
    $ffmpeg = movie::find_ffmpeg();
    if (empty($ffmpeg)) {
      throw new Exception("@todo MISSING_FFMPEG");
    }
    $cmd = escapeshellcmd($ffmpeg) . " -i " . escapeshellarg($item->file_path()) . " 2>&1 | grep 'Video' | cut -d ' ' -f 10 | sed s/,//";  
    $result = exec($cmd);
    
    if (preg_match("/(\d+)x(\d+)/", $result)) {
        $regs = explode("x",$result);  
        $ratio = $regs[0] / $regs[1];     
    }else{
        $ratio = "";
    }
        return $ratio;  
  }   
  
}
