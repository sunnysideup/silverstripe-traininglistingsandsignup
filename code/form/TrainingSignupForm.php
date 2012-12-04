<?php

class TrainingSignupForm extends Form {

	function __construct($controller, $name, $title = "Training") {
		if($member = Member::currentUser()) {
		}
		else {
			$member = new Member();
		}
		$fields = new FieldSet(
			new HeaderField($title)
		);
		$extraFields = $member->getTrainingFields();
		foreach($extraFields as $field) {
			if("Password" == $field->title() && $member->ID) {

			}
			elseif("Password" == $field->title() ) {
				$fields->push(new ConfirmedPasswordField("Password"));
			}
			else {
				$fields->push($field);
			}
		}
		$actions = new FieldSet(
				new FormAction("doSave", "Sign Up Now")
		);
		$requiredFields = new RequiredFields(
			"FirstName",
			"Surname",
			"Email",
			"Password"
		);
		if($controller->Options) {
			$array = array();
			$explodedOptions = explode(",", $controller->Options);
			foreach($explodedOptions as $option) {
				$option = trim(Convert::raw2att($option));
				$array[$option] = $option;
			}
			if(count($array) ) {
				$fields->push(new DropdownField("SelectedOption", "Select Option", $array));
			}
		}
		$fields->push(new TextField("BookingCode", "Booking Code (if any)"));
		parent::__construct($controller, $name, $fields, $actions, $requiredFields);
		$this->loadNonBlankDataFrom($member);
		return $this;
	}

	function doSave($data, $form) {
		if(isset($data['Password']) && is_array($data['Password'])) {
			$data['Password'] = $data['Password']['_Password'];
		}

		// We need to ensure that the unique field is never overwritten
		$uniqueField = Member::get_unique_identifier_field();
		if(isset($data[$uniqueField])) {
			$SQL_unique = Convert::raw2xml($data[$uniqueField]);
			$existingUniqueMember = DataObject::get_one('Member', "$uniqueField = '{$SQL_unique}'");
			if($existingUniqueMember && $existingUniqueMember->exists()) {
				if(Member::currentUserID() != $existingUniqueMember->ID) {
					die("current member does not match enrolled member");
					return false;
				}
			}
		}
		$member = Member::currentUser();
		if(!$member) {
			$member = new Member();
		}

		$member->update($data);
		$member->write();
		$arrayExtraFields = array();
		if(isset($data["SelectedOption"])) {
			$arrayExtraFields["SelectedOption"] = $data["SelectedOption"];
		}
		if(isset($data["BookingCode"])) {
			$arrayExtraFields["BookingCode"] = $data["BookingCode"];
		}
		$this->controller->addAttendee($member, $arrayExtraFields);
		Director::redirect($this->controller->Link()."thankyou");
		return;

	}

/*

I don't think saveInto can deal with saving many_many relations
currently, unless your Post class has a customised method called
saveCategories($data) in which you simply add the code like this:

$this->Categories->add($item, $extraFields),
where $item should be the category's ID (you can get it from $data),
depending on how your Form is stuctured, your $extraFields should be
either from $data, or assigned from backend by you before you call
saveInto in doPostForm or before you call $this->Categories->add
saveCetegories.

The name of saving many_many relation (here refer to saveCategories)
must bond with your field name, ie, if your field is called:
new DropdownField('GoneWithWind',"Category", $catOptions), the save
method must name as saveGoneWithWind().

if PrimeCategory need to be also submitted from from, your your can
get CategoryID and PrimeCategory in an array from $data, try this:
new DropdownField('Categories[CetegoryID]',"Category", $catOptions),
new CheckboxField('Categories[PrimeCategory]', "Prime Category?"),
The $data[Categories] submitted from from is a array.

In addition, dropdown field is not supposed to simulate many_many
relation, CheckboxSetField is aim to submit multiple IDs from a form
(maybe you have you special consideration here?). If this is the case,
$this->Categories->add need to change to $this->Categories-

*/


}




