<?php


namespace xepan\accounts;

class Form_Field_ReportRichText extends \Form_Field_Text{
	public $options=array();

	function init(){
		parent::init();
		$this->addClass('tinymce');
	}

	function render(){

		$this->js(true)
				->_load('tinymce.min')
				->_load('jquery.tinymce.min')
				->_load('xepan-reportrichtext-admin');
		$this->js(true)->univ()->richtext($this,$this->options);
		parent::render();
	}
}