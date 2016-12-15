<?php

// Another way, using substr
function endswith($Haystack, $Needle){
    return substr($Haystack, strlen($Needle)*-1) == $Needle;
}

$path = "."; 

$latest_ctime = 0;
$latest_filename = '';    

$d = dir($path);
while (false !== ($entry = $d->read())) {
  $filepath = "{$path}/{$entry}";
  // could do also other checks than just checking whether the entry is a file
  if (is_file($filepath) && filectime($filepath) > $latest_ctime && endswith($entry,".jpg")) {
      $latest_ctime = filectime($filepath);
      $latest_filename = $entry;
  }
}


header( "Content-type: image/png" );
readfile($latest_filename);
exit();


?>
