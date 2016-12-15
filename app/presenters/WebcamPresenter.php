<?php
namespace FrontModule;

use Tracy\Debugger;
use Nette\Utils\Image;

class WebcamPresenter extends BasePresenter{
	private $latest_ctime = 0;
	private $latest_filename = '';
	private $latest_datetime;
	
	const WEBCAM_DIR = "/upload/webcam/";
	const PATH = WWW_DIR . self::WEBCAM_DIR;
	

	public function renderDefault()	{
		$lastDate = self::getRecentImage()['datetime'];
		$files = $this->getFiles();
		
		$todayFiles = $files[$this->latest_datetime->format('Y-m-d')];
		sort($todayFiles);
		$this->template->files = array_reverse($todayFiles);
		//usort($this->template->files, $this->dateSortAsc);
		
		 
		$this->template->webcamDir = self::WEBCAM_DIR;
		$this->template->thumbsDir = self::WEBCAM_DIR .'thumbs/';
		$this->template->latest_datetime = $this->latest_datetime;
	}
	
	private function getFiles(){
		$files = array();

		$d = dir(self::PATH);
		while (false !== ($entry = $d->read())) {
			$filepath = self::PATH . $entry;
			if (is_file($filepath) && self::endswith($entry, ".jpg")) {
				$ctime = filemtime($filepath);
				$date = new \DateTime("@$ctime");
				
				if(!array_key_exists($ds = $date->format("Y-m-d"), $files)){
					$files[$ds] = array();
				}
				$files[$ds][] = $entry;
				
				if($ctime > $this->latest_ctime){
					$this->latest_ctime = filemtime($filepath);
					$this->latest_filename = $entry;
					$this->latest_datetime = $date;
				}
				
				if(!is_file(self::PATH .'thumbs/'. $entry)){
					$this->createThumbnail($entry);
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
		$latest_ctime = 0;
		$latest_filename = '';    

		$d = dir(self::PATH);
		while (false !== ($entry = $d->read())) {
			$filepath = self::PATH . $entry;
			// could do also other checks than just checking whether the entry is a file
			if (is_file($filepath) && filemtime($filepath) > $latest_ctime && self::endswith($entry, ".jpg")) {
				$latest_ctime = filemtime($filepath);
				$latest_filename = $entry;
			}
		}

		return array('name' => $latest_filename, 'datetime' => $latest_ctime);
	}
	
	static private function endswith($haystack, $needle){
		return substr($haystack, strlen($needle)*-1) == $needle;
	}
	
	function dateSortAsc($a, $b){
		return (@filemtime($a) - @filemtime($b));
	}
	
}