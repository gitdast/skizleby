<?php
namespace Ski;

use Tracy\Debugger;
use Nette\Utils\Image;

class WebcamPresenter extends BasePresenter{
	private $recentImageDatetime;
	
	const WEBCAM_DIR = "/upload/webcam/";
	const PATH = WWW_DIR . self::WEBCAM_DIR;
	

	public function renderDefault()	{
		$this->template->files = $this->getFiles();
		
		$this->template->webcamDir = self::WEBCAM_DIR;
		$this->template->thumbsDir = self::WEBCAM_DIR .'thumbs/';
		$this->template->recentImageDatetime = $this->recentImageDatetime;
	}
	
	private function getFiles(){
		$files = array();
		$allFiles = scandir(self::PATH, SCANDIR_SORT_DESCENDING);
		
		foreach($allFiles as $file){
			if(empty($recentImage) && is_file(self::PATH . $file) && self::endswith($file, ".jpg")) {
				$recentImage = self::PATH . $file;
				$recentCtime = filemtime($recentImage);
				$this->recentImageDatetime = $recentDate = new \DateTime("@$recentCtime");
			}
			
			if(isset($recentImage)){
				$filepath = self::PATH . $file;
				if (is_file($filepath) && self::endswith($file, ".jpg")) {
					$ctime = filemtime($filepath);
					$date = new \DateTime("@$ctime");
					
					if($date->format("Y-m-d") == $recentDate->format("Y-m-d")){
						$files[] = $file;
						if(!is_file(self::PATH .'thumbs/'. $file)){
							$this->createThumbnail($file);
						}
					}
				}
			}
		}
		
		return $files;
	}
	
	public function createThumbnail($file){
		try{
			$image = Image::fromFile(self::PATH . $file);
			$image->resize(null, 90);
			$image->save(self::PATH . 'thumbs/' . $file, 80, Image::JPEG);
		}
		catch(\Exception $e){
			\Tracy\Debugger::log($e->getMessage(), "error");
		}
	}
	
	static public function getRecentImage()	{
		$allFiles = scandir(self::PATH, SCANDIR_SORT_DESCENDING);
		
		foreach($allFiles as $file) {
			if (is_file(self::PATH . $file) && self::endswith($file, ".jpg")) {
				return $file;
			}
		}
		
		return "";
	}
	
	static private function endswith($haystack, $needle){
		return substr($haystack, strlen($needle)*-1) == $needle;
	}
	
	function dateSortAsc($a, $b){
		return (@filemtime($a) - @filemtime($b));
	}
	
}