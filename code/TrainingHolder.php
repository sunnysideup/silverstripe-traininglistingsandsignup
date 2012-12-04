<?php

class TrainingHolder extends Page {

	static $icon = "mysite/images/treeicons/TrainingHolder";

	//parents and children in sitetree
	static $allowed_children = array("TrainingPage"); //can also be "none";
	static $default_child = "TrainingPage";

	public function canCreate($member = null) {
		return DataObject::get_one('TrainingHolder') == null;
	}

	public function canDelete($member = null) {
		return false;
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		return $fields;
	}
}

class TrainingHolder_Controller extends Page_Controller {

	function MonthlyCourses() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$results = new DataObjectSet();

		$stage = Versioned::current_stage();
		$suffix = (!$stage || $stage == 'Stage') ? "" : "_$stage";

		$sqlResults = DB::query("
			SELECT DISTINCT MONTH({$bt}Date{$bt}) AS {$bt}Month{$bt}, YEAR({$bt}Date{$bt}) AS {$bt}Year{$bt}
			FROM {$bt}SiteTree$suffix{$bt} NATURAL JOIN {$bt}TrainingPage$suffix{$bt}
			WHERE {$bt}ParentID{$bt} = ".$this->ID." AND {$bt}Date{$bt} > CONVERT_TZ(now(),'+00:00','+13:00')
			ORDER BY {$bt}Year{$bt} DESC, {$bt}Month{$bt} ASC;"
		);

		if($sqlResults) foreach($sqlResults as $sqlResult) {
			$month = (isset($sqlResult['Month'])) ? (int) $sqlResult['Month'] : 1;
			$year = ($sqlResult['Year']) ? (int) $sqlResult['Year'] : date('Y');

			$date = DBField::create('Date', array(
				'Day' => 1,
				'Month' => $month,
				'Year' => $year
			));


			$results->push(new ArrayData(array(
				'Date' => $date,
				'Courses' => DataObject::get("TrainingPage", "{$bt}ShowInMenus{$bt} = 1 AND MONTH({$bt}TrainingPage{$bt}.Date) = $month AND YEAR({$bt}TrainingPage{$bt}.Date) = $year")
			)));
		}
		return $results;
	}

}
