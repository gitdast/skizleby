<?php
namespace FrontModule;

class HomepagePresenter extends BasePresenter{

	public function renderDefault()	{
		$this->template->today = new \DateTime();
		$this->template->working = $this->config['working']->value === 'true';
		$this->template->recentImage = WebcamPresenter::getRecentImage()['name'];
	}
	
}