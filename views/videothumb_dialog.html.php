<?php defined("SYSPATH") or die("No direct script access.") ?>

<script type="text/javascript">
    var ORGANIZE_TITLE =
    <?= t("Choose videothumb")->for_js() ?>;
    
  var set_title = function(title) {
    $("#g-dialog").dialog("option", "title", ORGANIZE_TITLE.replace("__TITLE__", title));
  }    
    set_title("<?= $album->title ?>");
  
</script>

<style type="text/css">
#minute, #second, #width, #height {
    width: 7% !important;
    display: inline !important;
}
label {
    width: 4em;
    float: left;
    text-align: left;
    margin-right: 0.5em;
    display: block
}

#wide, #normal {
    float: none;
    display: inline;
}

#button-done { border: 1px solid #c5dbec; background: #dfeffc 50% 50% repeat-x; font-weight: bold; color: #2e6e9e; outline: none; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; height: 28px;}
#button-done a { color: #2e6e9e; text-decoration: none; outline: none; }

#img-holder {
    width:<?= $thumbwidth ?>px; 
    height:<?= $thumbheight ?>px;
    border: 1px solid gray;
    margin-bottom: 40px;
}

#working {
    margin-left: <?= round(($thumbwidth - 32) / 2) ?>px;
    margin-top:  <?= round(($thumbheight - 32) / 2) ?>px;
}

#info {
    background-color: lightBlue;
    margin-bottom: 10px;
    padding: 10px;
}
</style>

<img style="display:none;" src="<?= url::file("modules/videothumb/lib/working.gif") ?>" alt="<?= t("working") ?>">
<div class="g-videothumb-dialog">
    <?php if($rectangle_thumb_active == "active"){echo '<div id="info">' . t("Module \"RectangleThumbs\" active - Thumbs may be croped to defined aspect ratio.") . '</div>';} ?>
    <p><?= t("Videolength")?>&nbsp;<span style="color: darkRed"><?= $videolength;?></span>&nbsp;<?= t("(min:sec)") ?></p>
    <div id="img-holder">
    <img src="<?= $item->thumb_url() ?>" alt="">
    </div>
    <p></p>
<form action="<?= url::site("videothumb/dialog/{$item->id}") ?>" method="post" id="videothumb_form">
    <label><?= t("Time") ?></label>
    <input type="text" name="minute" id="minute" value="<?= $minute;?>"/>&nbsp;min&nbsp;&nbsp;<input type="text" name="second" id="second" value="<?= $second;?>"/>&nbsp;sec
    <p></p>
    <div style="display: <?= $ratio_error; ?>; color: darkRed;"><?= t("Aspect ratio of video could not be detected") ?></div>    
    <label><?= t("Ratio") ?></label>
    <input type="radio" name="ratio" value="wide" id="wide" onclick="document.getElementById('height').value = Math.round(document.getElementById('width').value * 9 / 16)" <?= $wide_check ?>> 16:9&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="radio" name="ratio" value="normal" id="normal" onclick="document.getElementById('height').value = Math.round(document.getElementById('width').value * 3 / 4)" <?= $normal_check ?>> 4:3
    <p></p>
    <label><?= t("Size") ?></label>    
    <input type="text" name="width" id="width" value="<?= $thumbwidth;?>"/>&nbsp;&nbsp;&nbsp;x&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="text" name="height" id="height" value="<?= $thumbheight;?>"/>
<?php
  //IPTC-Daten versteckt mit dem Formular mitschicken
  foreach($iptcTags as $key => $value) {
     $inputs .= '<input type="hidden" name="iptcdata[' . $key .']" value="' . $i->get($iptcTags[$key]). '"/>';
    }
    echo $inputs;
?>
    <input type="hidden" value="<?= $item->file_path()?>" name="file_path">
    <input type="hidden" value="<?= $item->thumb_path()?>" name="thumb_path">  
    <input type="hidden" value="1" name="sent">   
    <p></p> 
    <input type="submit" value="<?= t("Choose") ?>" id="videothumb_submit" class="submit ui-state-default ui-corner-all" onclick="show_loader();">      
</form>
    <div></div>
    <button id="button-done" onclick="location.href='';" value="<?= t("Done") ?>"><?= t("Done") ?></button>
</div>
<script>
    function show_loader(){
        document.querySelector('#img-holder').innerHTML = '<img id="working" src="<?= url::file("modules/videothumb/lib/working.gif") ?>" alt="<?= t("working") ?>">'
    }

</script>
