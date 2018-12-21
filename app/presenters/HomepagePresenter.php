<?php
namespace Ski;

class HomepagePresenter extends BasePresenter{

	public function renderDefault()	{
		$this->addScript("slick.min.js", "/slick/");
		$this->template->recentImage = WebcamPresenter::getRecentImage();
	}
	
}