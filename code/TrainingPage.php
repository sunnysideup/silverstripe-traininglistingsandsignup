<?php

class TrainingPage extends Page {

	static $icon = "mysite/images/treeicons/TrainingPage";

	static $db = array(
		"Date" => "Date",
		"EndDate" => "Date",
		"Location" => "Varchar(255)",
		"Price" => "Currency",
		"IsOpenForBookings" => "Boolean",
		"PlacesAvailable" => "Int",
		"PeopleSignedUpElseWhere" => "Int",
		"MoreInformation" => "HTMLText",
		"Options" => "Text"
	);

	static $has_one = array(
		"DownloadFile" => "File"
	);

	static $many_many = array(
		"Attendees" => "Member"
	);

	static $many_many_extraFields = array(
		 "Attendees" => array(
				"SelectedOption" => "Varchar(255)",
				"BookingCode" => "Varchar(255)"
		 )
	);

	//parents and children in sitetree
	static $allowed_children = "none"; //can also be "none";
	static $default_parent = "TrainingHolder";
	static $can_be_root = false; //default is true

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Content.WhoWhereWhat", new DateField("Date", "Start Date"));
		$fields->addFieldToTab("Root.Content.WhoWhereWhat", new DateField("EndDate", "End Date - can be left blank for one day events"));
		$fields->addFieldToTab("Root.Content.WhoWhereWhat", new TextField("Location"));
		$fields->addFieldToTab("Root.Content.WhoWhereWhat", new CurrencyField("Price"));
		$fields->addFieldToTab("Root.Content.MoreInformation", new FileIFrameField("DownloadFile","Download File"));
		$fields->addFieldToTab("Root.Content.MoreInformation", new HTMLEditorField("MoreInformation","More Information", 12));
		$fields->addFieldToTab("Root.Content.Bookings", new CheckboxField("IsOpenForBookings", "Is Open For Bookings"));
		$fields->addFieldToTab("Root.Content.Bookings", new HeaderField("ActualPlacesAvailableHeader", "Actual Places Available: ".$this->ActualPlacesAvailable(), 3));
		$fields->addFieldToTab("Root.Content.Bookings", new LiteralField("ActualPlacesAvailableData", "Calculated as: Places Available [-] Minus People Signed up elsewhere [-] Minus People Signed up through this Website)"));
		$fields->addFieldToTab("Root.Content.Bookings", new NumericField("PlacesAvailable", "Places Available"));
		$fields->addFieldToTab("Root.Content.Bookings", new NumericField("PeopleSignedUpElseWhere","People Signed Up Else Where (thus excluding the ones signed up on this website)"));
		$fields->addFieldToTab("Root.Content.Bookings", new HeaderField("FormAdditions", "Form Additions", 3));
		$fields->addFieldToTab("Root.Content.Bookings", new TextareaField("Options", "Options available (separate by comma) - if any (e.g. venues)", 3));
		$fields->addFieldToTab("Root.Content.Bookings", new HeaderField("Current Registrations", "Current Registrations", 3));
		$fields->addFieldToTab(
			"Root.Content.Bookings",
			$this->MemberField()
		);
		return $fields;
	}

	function MemberField() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		$memberField = new ManyManyComplexTableField(
			$controller = $this,
			$name = "Attendees",
			$sourceClass = "Member",
			$fieldList = null,
			$detailFormFields = null,
			$sourceFilter = "{$bt}TrainingPageID{$bt} = ".$this->ID,
			$sourceSort = "{$bt}TrainingPage_Attendees{$bt}.{$bt}ID{$bt} DESC",
			$sourceJoin = ""
		);
		$memberField->setAddTitle("Attendees");
		$memberField->setPermissions(array("show", "edit", "export"));
		return $memberField;
	}

	function addAttendee($member, $extraFields = null) {
		$existingMembers = $this->Attendees();
		$existingMembers->add($member, $extraFields);
	}

	function DifferentEndDate() {
		if($this->Date != $this->EndDate && $this->EndDate) {
			return true;
		}
	}

	function DifferentEndMonth() {
		if($this->DifferentEndDate()) {
			if(Date("F",$this->Date) || Date("F",$this->EndDate)) {
				return true;
			}
		}
	}

	function ActualPlacesAvailable() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		return intval($this->PlacesAvailable - $this->PeopleSignedUpElseWhere - $this->Attendees("{$bt}TrainingPageID{$bt} = ".$this->ID)->count());
	}

}

class TrainingPage_Controller extends Page_Controller {

	function SignUpForm() {

		if(
			!$this->IsOpenForBookings ||
			"thankyou" == Director::URLParam("Action") ||
			$this->MemberAlreadySignedUp() ||
			$this->ActualPlacesAvailable() < 1
		) {
			return false;
		}
		$form = new TrainingSignupForm($this, "SignUpForm", "Sign-Up for ".$this->Title);
		return $form;
	}

	function thankyou () {
		$this->Title = "Thank You";
		$this->Content = "We will be in touch soon";
		return array();
	}

	function MemberAlreadySignedUp() {
		$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
		if($id = Member::currentUserID()) {
			if($this->Attendees("{$bt}MemberID{$bt} = ".$id.' AND {$bt}TrainingPageID{$bt} = '.$this->ID)->count()) {
				return true;
			}
		}
		return false;
	}


}
