<?php
namespace Ski;

class HomepagePresenter extends BasePresenter{

	public function renderDefault()	{
		$this->template->recentImage = WebcamPresenter::getRecentImage();
	}
	
}