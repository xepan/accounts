<?php

namespace xepan\accounts;


class View_BalanceSheetFormatted extends \View {
	

	function recursiveRender(){
		$template = $this->add('GiTemplate');
		$template->loadTemplate('view/report/balancesheet');

		parent::recursiveRender();
	}
}