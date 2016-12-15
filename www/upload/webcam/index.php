<?php
/* 
 * uAlbum by Pavel Mica - Crempa (http://ualbum.crempa.net)
 * v1.43
 * simple one script online gallery (PHP CSS XHTML JS included) 
 *
 */

// SETTINGS AREA ------------------------------------------------------------------------------------------------------

// layout
define("MAX_IMAGES","10");     
define("THUMBNAIL_DIRECTORY","thumbs");                               // name of thumbnail directory
define("GALLERY_TITLE","Webkamera");                             // uAlbum title (right top corner position)
define("TITLE","");                                                   // actual gallery title
define("LOADING_TEXT","Nahrávám...");                                  // loading text (showed during image loading)
define("SUBDIRECTORY_THUMBNAIL_DESCRIPTION_FILE","images");           // number of images title
define("SUBDIRECTORY_THUMBNAIL_DESCRIPTION_DATE","date");             // date of directory title
define("SUBDIRECTORY_THUMBNAIL_DESCRIPTION_DATE_FORMAT","d.m.Y");     // date format (PHP) of directory date
define("SLIDESHOW_START_TEXT","start slideshow");
define("SLIDESHOW_STOP_TEXT","stop slideshow");
define("FULLSCREEN_START_TEXT","fullscreen mode");
define("FULLSCREEN_STOP_TEXT","normal mode");
define("NEXT_IMAGE_TEXT","nexT&gt;");
define("PREVIOUS_IMAGE_TEXT","&lt;Prev");
define("GO_BACK_TEXT","&lt;&lt;..");
define("LAST_IMAGE_TEXT","That's all");
define("ORIGINAL_SIZE_SCREEN_TEXT","Original size");
define("ORIGINAL_SIZE_ALERT_TEXT","Image has original size now");

//layout colors
define("MAIN_COLOR","rgb(155,155,155)");
define("GALLERY_TITLE_COLOR","rgb(255,255,255)");
define("TITLE_COLOR","rgb(0,0,0)"); 

// other setting
define("NUMBER_OF_COLUMNS",3);                                        // number of columns in "root directory" mode (layout is prepared for 3 columns)
define("SLIDESHOW_INTERVAL",5000);                                    // slideshow interval (ms)
define("THUMBNAIL_DIRECTORY_CHMOD",0777);                             // CHMOD used for thumbnail directory
define("RESIZE_ORIGINAL_IMAGES",false);                               // sets original images (images placed in same folder like script) resizing
                                                                      // images are resized (overwrited) to size setted below
                                                                      // use this function if size of images uploaded to the server is too big 

// gallery names replacement
// you can set galery name replacing, original directory name is showed changed
// example... directory name: "[s]koln[i]_v[y]let" ...is showed like... gallery name: "školní výlet" ...with default setting 
$replacement_original = array("_","[e]","[s]","[c]","[r]","[z]","[y]","[a]","[i]","[e]","[u]","[1u]","[o]");
$replacement_new  =     array(" ","ě","š","č","ř","ž","ý","á","í","é","ú","ů","ó");

// file sorting
define("SORT_BY_DATE",true);                                          // enables date/time sorting (default sorting is by name)
define("OLDER_FIRST",false);                                           // older files are showed first (works with names too
// error messages
define("DIRECTORY_PERMISSION_PROBLEM_TEXT",
       "<strong>uAlbum could not create thumbnail directory</strong>
       <br />Set permissons using CHMOD(777) or contact your server admin");
define("FILE_PERMISSION_PROBLEM_TEXT",
       "<strong>uAlbum could not write thumbnails</strong>
       <br />Set permissons using CHMOD(777) or contact your server admin");
define("GDLIB_PNG_PROBLEM_TEXT",
       "<strong>Warning:</strong> You have not installed GD library or PNG file type is not supported");       
define("GDLIB_GIF_PROBLEM_TEXT",
       "<strong>Warning:</strong> You have not installed GD library or GIF file type is not supported");       
define("GDLIB_JPEG_PROBLEM_TEXT",
       "<strong>Warning:</strong> You have not installed GD library or JPEG file type is not supported");

// image sizes (experimental, do not change)
define("LANDSCAPE_X",600);
define("LANDSCAPE_Y",450);
define("VERTICAL_X",337);
define("VERTICAL_Y",450);

// SCRIPT AREA ------------------------------------------------------------------------------------------------------

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// --- FUNCTIONS ---
function resizeImage($source,$destination,$x_size,$y_size)
{
  $thumbnail=imagecreatetruecolor($x_size,$y_size); //thumbnail creating
  
  $path_parts = pathinfo($source); // file recognize process
  $extension=$path_parts["extension"];
  $extension = strtolower($extension);

  switch ($extension) 
  {
    case "png":
      if (!function_exists("ImageCreateFromPNG") or !function_exists("ImagePNG")) die (GDLIB_PNG_PROBLEM_TEXT);
      $img=ImageCreateFromPNG($source);
      imageCopyResampled($thumbnail,$img,0,0,0,0,$x_size,$y_size,ImageSX($img),ImageSY($img));
      if (!@ImagePNG($thumbnail,$destination)) die(FILE_PERMISSION_PROBLEM_TEXT);
      break;
    case "gif":
      if (!function_exists("ImageCreateFromGIF") or !function_exists("ImageGIF")) die (GDLIB_GIF_PROBLEM_TEXT);
      $img=ImageCreateFromGIF($source);
      imageCopyResampled($thumbnail,$img,0,0,0,0,$x_size,$y_size,ImageSX($img),ImageSY($img));
      if (!@ImageGIF($thumbnail,$destination)) die(FILE_PERMISSION_PROBLEM_TEXT);;
      break;
    default:
      if (!function_exists("ImageCreateFromJPEG") or !function_exists("ImageJPEG")) die (GDLIB_JPEG_PROBLEM_TEXT);
      $img=ImageCreateFromJPEG($source);
      imageCopyResampled($thumbnail,$img,0,0,0,0,$x_size,$y_size,ImageSX($img),ImageSY($img));
      if (!@ImageJPEG($thumbnail,$destination)) die(FILE_PERMISSION_PROBLEM_TEXT);;    
      break;
  }
}

function dateSortDesc($a, $b)
{
  return (@filemtime($b) - @filemtime($a)); 
}

function dateSortAsc($a, $b)
{
  return (@filemtime($a) - @filemtime($b)); 
}

function readDirectory($dir)
{
  $dir = OpenDir($dir); // open script (.) directory
  $directories = Array(); // initializing directories array
  $files = Array(); // initializing files array
  $i = 0;
  while ($file = ReadDir($dir)) // loading all files in the script directory
  {

    if (!Is_Dir($file)) // testing if file(founded object) is directory
    {
      $path_parts = pathinfo($file); // file recognize process
      $extension=$path_parts["extension"];
      $extension = strtolower($extension);
      if ($extension=="jpg" or $extension=="jpeg" or $extension=="png" or $extension=="gif")
      {
        $files[] = $file; // add file into array
      }      
    }
    elseif($file!="." and $file!=".." and $file!=THUMBNAIL_DIRECTORY)
    { // object is directory and we dont want show thumbnail, . or .. directories
      $directories[] = $file; // add directory into array
    }  
  }
  CloseDir($dir); // closing directory
  $output['directories'] = $directories;
  $output['files'] = $files;
  return $output;
}

// --- SCRIPT ---

  $directory_info = readDirectory('.');
  $directories = $directory_info['directories'];
  $files = $directory_info['files'];
  
  // creating thumbnail directory  
  if (!@OpenDir(THUMBNAIL_DIRECTORY))
  { 
    if (!@mkdir(THUMBNAIL_DIRECTORY,THUMBNAIL_DIRECTORY_CHMOD)) die (DIRECTORY_PERMISSION_PROBLEM_TEXT);
    chmod(THUMBNAIL_DIRECTORY,THUMBNAIL_DIRECTORY_CHMOD);                                    
  }
  
  // sorting
  if (SORT_BY_DATE==true)
  {
    if (OLDER_FIRST==true)
    {
      usort($files, "dateSortAsc");
      usort($directories, "dateSortAsc");
    } 
    else
    { 
      usort($files, "dateSortDesc");
      usort($directories, "dateSortDesc");
    }
  }
  else
  {
    natsort($files);        
    sort($directories);
  }
  //var_dump($files);
  $files = array_slice($files,0,MAX_IMAGES);

  reset($files);
  reset($directories);

// --- FILE OPERATIONS ---

  $file_list="";      // string varibale contains filenames
  $thumbnail_list=""; // string variable contains  <a><img></a> construction of thumbnails list (left panel)
  $file_list_size=""; // string variable contains image orientation
  $i=0;
  
  //file array iterateing and generating thumbnails (if doesnt exist) and JS arrays
  foreach ($files as $file)
  {
    if (!file_exists(THUMBNAIL_DIRECTORY."/".$file)) resizeImage($file,THUMBNAIL_DIRECTORY."/".$file,90,90);
    //if ($i!=0 && $i%2==0) $thumbnail_list.="<span class=\"thumb_space\"></span>"; // generating IE space after every two thumbnails
    $thumbnail_list.="<a href=\"#\" class=\"thumb_link\" onclick=\"return setImage(".$i++.");\" onfocus=\"this.blur()\" ><img class=\"thumb_img\" src=\"".THUMBNAIL_DIRECTORY."/".$file."\" alt=\"".$file."\" title=\"".$file."\" height=\"90\" width=\"90\" /></a>\n";
    $resolution=GetImageSize($file); // image resolution detection
    
    if (($resolution[0]>$resolution[1]) and ($resolution[0]!=LANDSCAPE_X or $resolution[1]!=LANDSCAPE_Y) and RESIZE_ORIGINAL_IMAGES==true)
    {
      resizeImage($file,$file,LANDSCAPE_X,LANDSCAPE_Y);
    }
    elseif (($resolution[0]<=$resolution[1]) and ($resolution[0]!=VERTICAL_X or $resolution[1]!=VERTICAL_Y) and RESIZE_ORIGINAL_IMAGES==true)
    {
      resizeImage($file,$file,VERTICAL_X,VERTICAL_Y);
    }
    $file_list.="'".$file."',";  // generating array of filenames
  }
  //last dash removing
  $file_list=substr($file_list, 0, -1); 
  $file_list_size=substr($file_list_size, 0, -1); 
  
// --- DIRECTORY OPERATIONS ---

  $directory_list = Array();
  foreach ($directories as $directory)
  { 
    $subdirectory_info = readDirectory($directory);
    $subdirectory_files_count = count($subdirectory_info['files']); //number of image files in subdirectory
    $subdirectory_name = str_replace($replacement_original, $replacement_new, $directory);
    $subdirectory_date = date(SUBDIRECTORY_THUMBNAIL_DESCRIPTION_DATE_FORMAT,@filemtime($directory));
    
    if ($subdirectory_files_count!=0) // some image files was founded in subdirectory
    {
      // sorting files in subdirectory
      if (SORT_BY_DATE==true)
      {
        if (OLDER_FIRST==true)
        {
          usort($subdirectory_info['files'], "dateSortAsc");
        } 
        else
        { 
          usort($subdirectory_info['files'], "dateSortDesc");
        }
      }
      else
      {    
        natsort($subdirectory_info['files']);
      }
            
      $path_parts = pathinfo($subdirectory_info['files'][0]); // file recognize process
      $extension=strtolower($path_parts["extension"]);
      // generating subdirectory thumbnail
      if (!file_exists(THUMBNAIL_DIRECTORY."/".$directory.".".$extension)) resizeImage($directory."/".$subdirectory_info['files'][0],THUMBNAIL_DIRECTORY."/".$directory.".".$extension,90,90);
      $directory_list[]= "<div class=\"thumb_folder\">
                            <a href=\"$directory/\" onfocus=\"this.blur()\">
                              <img src=\"".THUMBNAIL_DIRECTORY."/".$directory.".".$extension."\" alt=\"".$directory.".".$extension."\" title=\"".$subdirectory_name."\" height=\"90\" width=\"90\" />
                            </a> 
                            <span class=\"thumb_folder_title\">
                             ".$subdirectory_name."
                            </span>
                            <span class=\"thumb_folder_description\">
                              &nbsp;".SUBDIRECTORY_THUMBNAIL_DESCRIPTION_FILE.": ".$subdirectory_files_count."<br />
                              &nbsp;".SUBDIRECTORY_THUMBNAIL_DESCRIPTION_DATE.": ".$subdirectory_date."
                            </span>
                          </div>";
    }
    else
    {
      $directory_list[]="<a href=\"".$directory."/\" class=\"folder\">
                          <span class=\"title\">".$subdirectory_name."</span>
                         </a>
                         "; 
    }
  }
  
  // actual directory name detecting and setting gallery name
  $script_name = pathinfo($_SERVER["PHP_SELF"]); 
  $pcs = explode("/", $script_name['dirname']);
  $dir_title = str_replace($replacement_original, $replacement_new, $pcs[count($pcs)-1]);
  $title = TITLE=="" ? ($dir_title =="" ? "Root directory" : $dir_title) : TITLE;

  // image absolute path detection 
  $script_name_img = pathinfo("http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']); 
  $imgPath = $script_name_img['dirname'];
  
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <meta name="description" content="uAlbum - simple one file PHP web gallery by Crempa" />
  <meta name="robots" content="index,follow" />
  <meta name="author" content="Pavel Mica - Crempa" />
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta name="generator" content="PSPad editor, www.pspad.com" />
  <meta name="googlebot" content="snippet,archive" />
  <title>Webkamera</title>
  <!-- gallery css style section-->
  <style type="text/css">
    body{font:0.84em arial, helvetica, sans-serif;margin:0;padding:0;}
    #main{height:100%;min-height:550px;min-width:900px;position:absolute;width:100%;}
    #content{height:540px;left:50%;position:absolute;top:50%;width:900px;margin:-295px 0 0 -450px;padding:0;}
    #content_matroska{border:none <?php echo MAIN_COLOR; ?>;padding:0;}
    #supplemental{height:550px;visibility:hidden;width:900px;}
    .thumbs{border-right:0px solid <?php echo MAIN_COLOR; ?>;/*float:left;height:515px;*/margin-right:10px;overflow:auto;padding-left:5px;padding-right:0;padding-top:10px;position:relative;width:610px;}
    #canvas{clear:right;height:500px;overflow:auto;margin:0;padding:20px 0 0;}
    h2{color:<?php echo TITLE_COLOR; ?>;float:left;font-size:1em;font-weight:700;width:700px;margin:0;padding:5px 0 5px 8px;}
    h1{background:<?php echo MAIN_COLOR; ?>;color:<?php echo GALLERY_TITLE_COLOR; ?>;font-size:1em;font-weight:700;text-align:right;margin:0;padding:5px 5px 5px 0;}
    #title_footer{clear:both;margin:0;padding:0;}
    #canvas a{border:0;color:<?php echo MAIN_COLOR; ?>;text-decoration:none;margin:0;padding:0;}
    #img_canvas{border:0;min-height:465px;width:1px;z-index:0;margin:0;padding:0;}
    #canvas img{border:1px solid <?php echo MAIN_COLOR; ?>;margin:0;padding:5px;}
    .thumb_img{border:1px solid <?php echo MAIN_COLOR; ?>;margin:1px;padding:2px;}
    #menu{font-weight:700;position:relative;top:-5px;color:<?php echo MAIN_COLOR; ?>; clear:both;}
    #menu_left{display:inline;}
    #signature a{color:<?php echo MAIN_COLOR; ?>;text-decoration:none;}
    #signature a:hover{color:#4f4f4f;}
    .thumb_space{display:block;line-height:2px;width:200px;}
    .thumbs .folder{border:1px solid <?php echo MAIN_COLOR; ?>;color:#000;display:block;height:2em;left:-4px;line-height:2em;position:relative;text-decoration:none;width:196px;}
    .thumbs a{border:0;padding:0;}
    .folder .title{background:#FFF;border-left:1px solid <?php echo MAIN_COLOR; ?>;display:block;font-size:85%;margin:0 0 0 5px;padding:0 0 0 5px;}
    .thumbs .folder:link,.thumbs .folder:visited,.thumbs .folder:active{margin:5px;padding:0;}
    .thumbs .folder:hover{background:<?php echo MAIN_COLOR; ?>;}
    #no_file_content{border-left:1px solid <?php echo MAIN_COLOR; ?>;height:520px;}
    h2 .path{font-size:70%;}
    #loader{display: none; background:#FFF;border:1px solid <?php echo MAIN_COLOR; ?>;left:13px;position:relative;top:-450px;width:65px;z-index:1;padding:5px; visibility: hidden;}
    #signature{color:<?php echo MAIN_COLOR; ?>;font-size:80%;text-align:center;margin:0;padding:0;}
    #canvas #slideshow a:link,#canvas #slideshow a:visited,#canvas #slideshow a:active,#canvas #menu_left a:link,#canvas #menu_left a:visited,#canvas #menu_left a:active,#canvas #menu_right a:link,#canvas #menu_right a:visited,#canvas #menu_right a:active{padding:2px;}
    #fullscreen_menu a:hover,#canvas #slideshow a:hover,#canvas #menu_left a:hover,#canvas #menu_right a:hover{color:#000;padding:2px;}
    #menu_right,#slideshow{display:inline;padding-left:153px;}
    .thumb_folder{width:196px;border:1px solid <?php echo MAIN_COLOR; ?>;left:1px;position:relative;line-height:12px;margin-bottom:4px;min-height:90px;background:#ececec;padding:2px 0;}
    .thumb_folder a{border:0;text-decoration:none;font-size:85%;color:#000;margin:0;padding:0;}
    .thumbs .thumb_folder a img{text-decoration:none;}
    .thumb_folder img{float:left;border:0;margin:0 2px;}
    .thumb_folder_description{text-align:left;font-size:85%;}
    .thumb_folder_title{text-align:center;display:block;border-bottom:1px solid <?php echo MAIN_COLOR; ?>;font-size:0.9em;margin:0 0 5px 94px;padding:2px 0;}
    #fullscreen_canvas_img{display:none;position:absolute;top:10px;left:10px;}
    #fullscreen_menu{position:absolute;display:none;color:<?php echo MAIN_COLOR; ?>;cursor:move;top:20px;left:100px;background:#ececec;border:1px solid <?php echo MAIN_COLOR; ?>;z-index:10;opacity:0.8;padding:5px;}
    #fullscreen_menu span{margin-left:50px;}
    #fullscreen_menu a{border:0;color:<?php echo MAIN_COLOR; ?>;text-decoration:none;font-weight:700;margin:0;padding:2px;}
    #fullscreen_loader{background:#ececec;border:1px solid <?php echo MAIN_COLOR; ?>;display:none;position:absolute;top:20px;left:20px;z-index:5;opacity:0.8;padding:5px;}
  </style>
  <!--[if lt IE 7]>
  <style type="text/css">
    .thumb_folder{width:198px;margin-bottom:6px;height:96px;}
    #img_canvas{border:1px solid <?php echo MAIN_COLOR; ?>;padding:5px 5px 2px; }
    #sizer{visibility: hidden; border:1px solid black; height: 490px; float:left;}
    .thumbs .thumb_link{border:1px solid <?php echo MAIN_COLOR; ?>;padding:2px;}
    .thumb_img{border:0;}
    .thumbs .folder{width:198px;}
    #canvas img{border:0;padding:0;}
    .thumbs a{padding-top:5px;}
    #canvas{height:530px;}
    .thumbs{height:527px;}
    .thumb_folder img{margin:0 2px;}   
    #fullscreen_menu, #fullscreen_loader {filter:alpha(opacity=80);}
  </style>
  <![endif]-->
</head>
  <body<?php echo $expresion = count($files)==0 ? "" : " onload=\"setImage(0);\""; ?>>
  <div id="fullscreen_loader"><?php echo LOADING_TEXT; ?></div>
  
  <div id="fullscreen_menu" onmousedown="mouseDown('fullscreen_menu',event); return false;" onmouseup="mouseUp();">
       <a  id="fullscreen_menu_left" href="#" onclick="return PrevImg();" onfocus="this.blur()"><?php echo PREVIOUS_IMAGE_TEXT; ?></a>
       <span></span>
       <a id="slideshow_link_fullscreen" href="#" onclick="return showSlideshow();" onfocus="this.blur()"><?php echo SLIDESHOW_START_TEXT; ?></a>
       ||
       <a id="fullscreen_link_fullscreen" href="#" onclick="return showFullscreen();" onfocus="this.blur()"><?php echo FULLSCREEN_STOP_TEXT; ?></a>
       ||
       <a id="fit_link_fullscreen" href="#" onclick="return originalSize();" onfocus="this.blur()"><?php echo ORIGINAL_SIZE_SCREEN_TEXT; ?></a>
       <span></span>
       <a id="fullscreen_menu_right" href="#" onclick="return NextImg();" onfocus="this.blur()"><?php echo NEXT_IMAGE_TEXT; ?></a>
  </div>
  <div>
  <img  src="" id="fullscreen_canvas_img" alt="" title="" height="450" width="600" /> <!-- fullscreen mode image -->
  </div>
  <div id="main">
  <div id="supplemental"></div>  
    <div id="content">
    <div id="content_matroska">
    <!--<h2><?php echo $title." <span class=\"path\">[".$script_name['dirname']."]</span>"; ?></h2>
    <h1><?php echo GALLERY_TITLE; ?></h1>-->
    <div id="title_footer"></div>
      <?php
      // if actual directory doesnt contain relevant files we show all directories in extended mode (full window)
      if (count($files)==0)
      {
         for ($i = 0; $i <NUMBER_OF_COLUMNS; $i++)
         {
          echo "<div class=\"thumbs\">";
            $j=$i;
            while ($directory_list[$j])
            {
            	echo $directory_list[$j];
              $j+=NUMBER_OF_COLUMNS;
            }
          echo "</div>"; 
         }
        ?>
           <div id="canvas">&nbsp;</div>
            </div><!-- content_matroska end -->
              <!-- <div id="signature">
                <a href="http://ualbum.crempa.net">uAlbum</a>1.43
            </div> -->
            </div><!-- content end -->
          </div><!-- main end -->
         </body>
        </html>      
        <?php
        exit(); // end of script (after generating extended directory index)
      }
        ?>
      <div class="thumbs">
      <!--
      <a href=".." class="folder">
        <span class="title"><?php echo GO_BACK_TEXT; ?></span>
      </a> -->
       <?php // directory index generating
          foreach ($directory_list as $dir_list)
          {
            echo $dir_list;
          }
        ?>
        <?php
        // including thumbnails
        echo $thumbnail_list;
        ?>
      </div>
      <div id="canvas"><!-- image showing part -->     
          <div id="sizer"></div>
          <div id="img_canvas">
            <a id="canvas_link" href="" onfocus="this.blur()" onclick="return!window.open(this.href);">
              <img  src="" id="canvas_img" alt="" title="" height="450" width="600" /> <!-- showed image -->
            </a>
          </div>
 
        <div id="loader">
          <?php echo LOADING_TEXT; ?>
        </div> 
	<!--
          <div id="menu">
              <div id="menu_left" >
                 <a href="#" onclick="return PrevImg();" onfocus="this.blur()"><?php echo PREVIOUS_IMAGE_TEXT; ?></a>
              </div>           
              <div id="slideshow">
                 <a id="slideshow_link" href="#" onclick="return showSlideshow();" onfocus="this.blur()"><?php echo SLIDESHOW_START_TEXT; ?></a>
                 ||
                 <a id="fullscreen_link" href="#" onclick="return showFullscreen();" onfocus="this.blur()"><?php echo FULLSCREEN_START_TEXT; ?></a>
              </div>
              <div  id="menu_right">
                 <a href="#" onclick="return NextImg();" onfocus="this.blur()"><?php echo NEXT_IMAGE_TEXT; ?></a>
              </div>
          </div> -->
        </div>
      </div><!-- content_matroska end -->
	<!--
      <div id="signature"> 
        <a href="http://ualbum.crempa.net">uAlbum</a>1.43
    </div> -->
    </div><!-- content end -->
  </div><!-- main end -->
  <script type="text/javascript">
  /* <![CDATA[ */
    var obr = document.getElementById('canvas_img');
    var fullscreenObr = document.getElementById("fullscreen_canvas_img");
    var fullscreenMenu=document.getElementById("fullscreen_menu");
    var link=document.getElementById('canvas_link');
    var Images=new Array(<?php echo $file_list;?>);
    var Counter=0;
    var numPhotos=<?php echo count($files);?>;
    var timerId;
    var slideshow=false;
    var slideshowInterval=<?php echo SLIDESHOW_INTERVAL;?>;
    var fullscreenMode=false;
    var movingObject=null;
    var index_id;
    var imgPath="<?php echo $imgPath; ?>";
    var myImg=new Image();
    
    function NextImg()
    {
        Counter++;
        if(Counter<numPhotos)
        {
            setImage(Counter);
        }
        else
        {
            if(slideshow==true)
            {
                Counter=0;
                setImage(Counter);
            }
            else
            {
                Counter=numPhotos-1;
                window.alert("<?php echo LAST_IMAGE_TEXT; ?>");
            }
        }
        return false;
    }
    
    function PrevImg()
    {
        Counter--;
        if(Counter>-1)
        {
            setImage(Counter);
        }
        else
        {
            Counter=0;
            window.alert("<?php echo LAST_IMAGE_TEXT; ?>");
        }
        return false;
    }
    
    function ImageChange()
    {
        if(fullscreenMode==false)
        {
            obr.src=myImg.src;
        }
        else
        {
            fullscreenObr.src=myImg.src;
        }
        Counter=index_id;
        link.href=Images[index_id];
        document.getElementById("loader").style.visibility="hidden";
        document.getElementById("fullscreen_loader").style.visibility="hidden";
		setImageSize();
    }
    
    function setImageSize()
    {
        if(fullscreenMode==true)
        {
            fitToScreen();
        }
        else
        {
          document_width=<?php echo LANDSCAPE_X; ?>;
          document_height=<?php echo LANDSCAPE_Y; ?>;
          img_width=myImg.width;
          img_height=myImg.height;
          width_difference=document_width-img_width;
          height_difference=document_height-img_height;
          if(width_difference<0)
          {
              if(height_difference<width_difference)
              {
                  obr.width=(document_height)*(img_width/img_height);
                  obr.height=document_height;
              }
              else
              {
                  obr.width=document_width;
                  obr.height=(document_width)*(img_height/img_width);
              }
          }//unusual aspect ratio
          else if(height_difference<0&&width_difference>=0)
          {
              obr.width=(document_height)*(img_width/img_height);
              obr.height=document_height;
          }
          else
          {
              obr.width=myImg.width;
              obr.height=myImg.height;
          }
        }
    }
    
    function setImage(index)
    {
        index_id=index;
        document.getElementById("loader").style.visibility="visible";
        document.getElementById("fullscreen_loader").style.visibility="visible";
        myUrl=imgPath+"/"+Images[index_id];
        myImg.src=myUrl;
        myImg.onload=ImageChange;
        return false;
    }
    
    function showSlideshow()
    {
        if(slideshow==false)
        {
            timerId=window.setInterval("NextImg()",slideshowInterval);
            document.getElementById("slideshow_link").firstChild.nodeValue="<?php echo SLIDESHOW_STOP_TEXT; ?>";
            document.getElementById("slideshow_link_fullscreen").firstChild.nodeValue="<?php echo SLIDESHOW_STOP_TEXT; ?>";
            document.getElementById("fullscreen_menu_left").style.visibility="hidden";
            document.getElementById("fullscreen_menu_right").style.visibility="hidden";
            document.getElementById("menu_left").style.visibility="hidden";
            document.getElementById("menu_right").style.visibility="hidden";
            slideshow=true;
        }
        else
        {
            window.clearInterval(timerId);
            slideshow=false;
            document.getElementById("slideshow_link").firstChild.nodeValue="<?php echo SLIDESHOW_START_TEXT; ?>";
            document.getElementById("slideshow_link_fullscreen").firstChild.nodeValue="<?php echo SLIDESHOW_START_TEXT; ?>";
            document.getElementById("fullscreen_menu_left").style.visibility="visible";
            document.getElementById("fullscreen_menu_right").style.visibility="visible";
            document.getElementById("menu_left").style.visibility="visible";
            document.getElementById("menu_right").style.visibility="visible";
        }
    }
    
    function showFullscreen()
    {
        if(fullscreenMode==false)
        {
            document.getElementById("main").style.display="none";
            fullscreenObr.src=myImg.src;
            fullscreenObr.height=myImg.height;
            fullscreenObr.width=myImg.width;
            fullscreenObr.style.display="block";
            fullscreenMenu.style.display="block";
            document.getElementById("fullscreen_loader").style.display="block";
            fullscreenMode=true;
            setImageSize();
        }
        else
        {
            obr.src=myImg.src;
            fullscreenObr.style.display="none";
            fullscreenMenu.style.display="none";
            document.getElementById("fullscreen_loader").style.display="none";
            document.getElementById("main").style.display="block";
            fullscreenMode=false;
            setImageSize();
        }
    }
    
    function fitToScreen()
    {
        document_width=getDocumentWidth();
        document_height=getDocumentHeight();
        img_width=myImg.width;
        img_height=myImg.height;
        width_difference=document_width-img_width;
        height_difference=document_height-img_height;
        if(width_difference<0)
        {
            if(height_difference<width_difference)
            {
                fullscreenObr.width=(document_height-20)*(img_width/img_height);
                fullscreenObr.height=document_height-20;
            }
            else
            {
                fullscreenObr.width=document_width-10;
                fullscreenObr.height=(document_width-10)*(img_height/img_width);
            }
        }
        else if(height_difference<0&&width_difference>0)
        {
            fullscreenObr.width=(document_height-20)*(img_width/img_height);
            fullscreenObr.height=document_height-20;
        }
        else
        {
            fullscreenObr.width=myImg.width;
            fullscreenObr.height=myImg.height;
        }
    }
    
    function originalSize()
    {
        if(fullscreenObr.width==myImg.width)
        {
            alert("<?php echo ORIGINAL_SIZE_ALERT_TEXT; ?>")
        }
        else
        {
            fullscreenObr.width=myImg.width;
            fullscreenObr.height=myImg.height;
        }
    }
    
    function getDocumentHeight()
    {
		if(document.documentElement.clientHeight) return document.documentElement.clientHeight; //jde v IE7, FF
		else return document.body.clientHeight; // jde v IE6, Opera 
    }
    
    function getDocumentWidth()
    {
      if(document.documentElement.clientWidth) return document.documentElement.clientWidth;
      else return document.body.clientWidth;
    }
    
    function mouseDown(id,e)
    {
        movingObject=document.getElementById(id);
        if(!document.all)
        {
            X=e.layerX;
            Y=e.layerY;
        }
        else
        {
            X=event.offsetX;
            Y=event.offsetY;
        }
    }
    
    function mouseUp()
    {
        movingObject=null;
    }
    
    function mouseMove(e)
    {
        if(movingObject)
        {
            movingObject.style.top=(e?e:event).clientY-Y+"px";
            movingObject.style.left=(e?e:event).clientX-X+"px";
            return false;
        }
    }
    
    document.onmousemove=mouseMove;
    setImage(0); 

  /* ]]> */
  </script>
  </body>
</html>
