<?php
/**
 *
 * @author nicolaas [at] sunnysideup.co.nz
 */
class TrainingMemberDecorator extends DataObjectDecorator {

	function extraStatics() {
		return array(
			'belongs_many_many' => array(
				'Training' => 'TrainingPage'
			),
			'db' => array(
				'Organisation' => 'Varchar',
				'Phone' => 'Varchar'
			)
		);
	}



	function getTrainingFields() {
		$fields = new FieldSet(
			new TextField('FirstName', 'First Name'),
			new TextField('Surname', 'Surname'),
			new TextField('Organisation'),
			new EmailField('Email', 'Email'),
			new TextField('Phone')
		);

		$this->owner->extend('augmentTrainingFields', $fields);

		return $fields;
	}

	function getTrainingRequiredFields() {
		$fields = array(
			'FirstName',
			'Surname',
			'Organisation',
			'Email',
			'Phone'
		);

		$this->owner->extend('augmentTrainingRequiredFields', $fields);

		return $fields;
	}

	public static function createOrMerge($data) {
		// Because we are using a ConfirmedPasswordField, the password will
		// be an array of two fields
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
					return false;
				}
			}
		}

		if(!$member = Member::currentUser()) {
			$member = new Member();
		}

		$member->update($data);

		return $member;
	}


}
