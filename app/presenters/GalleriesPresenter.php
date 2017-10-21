<?php
namespace FrontModule;

class GalleriesPresenter extends BasePresenter{
	
	/** @var \App\Model\Galleries */
	private $galleries;
	
	const GALL_DIR = "upload/galleries/";
	
	public function __construct(\App\Model\Galleries $galleries){
		parent::__construct();
		
		$this->galleries = $galleries;
	}

	public function renderDefault()	{
		$supported_files = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
		$galleries = $this->galleries->getList();
		
		foreach($galleries as $gallery){
			if(empty($gallery->folder))
				continue;
			
			$files = glob(self::GALL_DIR . $gallery->folder . "/*.*");
			
			foreach($files as $file){
				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if($ext && in_array($ext, $supported_files)){
					$gallery->image = $file;
					break;
				}
			}			
		}
		
		$this->template->galleries = $galleries;
		\Tracy\Debugger::barDump($galleries, "list");
	}
	
	public function renderDetail($id){
		$this->addScript("slick.min.js", "/slick/");
		
		$supported_files = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
		$gallery = $this->galleries->getItem($id);
		
		$files = glob(self::GALL_DIR . $gallery->folder . "/*.*");
		$images = array_filter(
			$files,
			function($file) use($supported_files){
				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				return $ext && in_array($ext, $supported_files);
			}
		);

		$count = count($images);
		if($count != $gallery->count){
			\Tracy\Debugger::log("Wrong count for gallery of ID $gallery->id. Count = $count.", "warning");
		}

		$gallery->count = count($images);
		$gallery->images = $images;
		
		$this->template->gallery = $gallery;
	}
	
}