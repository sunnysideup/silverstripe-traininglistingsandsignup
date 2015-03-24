<?php
/**
 *
 * @author nicolaas [at] sunnysideup.co.nz
 */
class TrainingMemberDecorator extends DataExtension {

	private static $belongs_many_many = array(
		'Training' => 'TrainingPage'
	);
	private static $db = array(
		'Organisation' => 'Varchar',
		'Phone' => 'Varchar'
	);



	function getTrainingFields() {
		$fields = new FieldList(
			new TextField('FirstName', 'First Name'),
			new TextField('Surname', 'Surname'),
			new TextField('Organisation'),
			new EmailField('Email', 'Email'),
			new TextField('Phone')
		);
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
			$SQL_unique = Convert::raw2sql($data[$uniqueField]);
			$existingUniqueMember = Member::get()->filter(array($uniqueField => $SQL_unique))->first();
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
