<?php

class TrainingPage extends Page
{
    private static $icon = "mysite/images/treeicons/TrainingPage";

    private static $db = array(
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

    private static $has_one = array(
        "DownloadFile" => "File"
    );

    private static $many_many = array(
        "Attendees" => "Member"
    );

    private static $many_many_extraFields = array(
         "Attendees" => array(
                "SelectedOption" => "Varchar(255)",
                "BookingCode" => "Varchar(255)"
         )
    );

    //parents and children in sitetree
    private static $allowed_children = "none"; //can also be "none";
    private static $default_parent = "TrainingHolder";
    private static $can_be_root = false; //default is true

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab("Root.WhoWhereWhat", new DateField("Date", "Start Date"));
        $fields->addFieldToTab("Root.WhoWhereWhat", new DateField("EndDate", "End Date - can be left blank for one day events"));
        $fields->addFieldToTab("Root.WhoWhereWhat", new TextField("Location"));
        $fields->addFieldToTab("Root.WhoWhereWhat", new CurrencyField("Price"));
        $fields->addFieldToTab("Root.MoreInformation", new UploadField("DownloadFile", "Download File"));
        $fields->addFieldToTab("Root.MoreInformation", new HtmlEditorField("MoreInformation", "More Information"));
        $fields->addFieldToTab("Root.Bookings", new CheckboxField("IsOpenForBookings", "Is Open For Bookings"));
        $fields->addFieldToTab("Root.Bookings", new HeaderField("ActualPlacesAvailableHeader", "Actual Places Available: ".$this->ActualPlacesAvailable(), 3));
        $fields->addFieldToTab("Root.Bookings", new LiteralField("ActualPlacesAvailableData", "Calculated as: Places Available [-] Minus People Signed up elsewhere [-] Minus People Signed up through this Website)"));
        $fields->addFieldToTab("Root.Bookings", new NumericField("PlacesAvailable", "Places Available"));
        $fields->addFieldToTab("Root.Bookings", new NumericField("PeopleSignedUpElseWhere", "People Signed Up Else Where (thus excluding the ones signed up on this website)"));
        $fields->addFieldToTab("Root.Bookings", new HeaderField("FormAdditions", "Form Additions", 3));
        $fields->addFieldToTab("Root.Bookings", new TextareaField("Options", "Options available (separate by comma) - if any (e.g. venues)"));
        $fields->addFieldToTab("Root.Bookings", new HeaderField("Current Registrations", "Current Registrations", 3));
        $fields->addFieldToTab(
            "Root.Bookings",
            $this->MemberField()
        );
        return $fields;
    }

    public function MemberField()
    {
        $memberField = new GridField(
            $name = "Attendees",
            $sourceClass = "Attendees",
            $this->Attendees(),
            GridFieldConfig_RelationEditor::create()
        );
        return $memberField;
    }

    public function addAttendee($member, $extraFields = null)
    {
        $existingMembers = $this->Attendees();
        $existingMembers->add($member, $extraFields);
    }

    public function DifferentEndDate()
    {
        if ($this->Date != $this->EndDate && $this->EndDate) {
            return true;
        }
    }

    public function DifferentEndMonth()
    {
        if ($this->DifferentEndDate()) {
            if (Date("F", $this->Date) || Date("F", $this->EndDate)) {
                return true;
            }
        }
    }

    public function ActualPlacesAvailable()
    {
        return intval($this->PlacesAvailable - $this->PeopleSignedUpElseWhere - $this->Attendees("\"TrainingPageID\" = ".$this->ID)->count());
    }
}

class TrainingPage_Controller extends Page_Controller
{
    private static $allowed_actions = array(
        "thankyou",
        "SignUpForm"
    );

    public function SignUpForm()
    {
        if (
            !$this->IsOpenForBookings ||
            "thankyou" == $this->getRequest()->param("Action") ||
            $this->MemberAlreadySignedUp() ||
            $this->ActualPlacesAvailable() < 1
        ) {
            return false;
        }
        $form = new TrainingSignupForm($this, "SignUpForm", "Sign-Up for ".$this->Title);
        return $form;
    }

    public function thankyou()
    {
        $this->Title = "Thank You";
        $this->Content = "We will be in touch soon";
        return array();
    }

    public function MemberAlreadySignedUp()
    {
        if ($id = Member::currentUserID()) {
            if ($this->Attendees("\"MemberID\" = ".$id.' AND \"TrainingPageID\" = '.$this->ID)->count()) {
                return true;
            }
        }
        return false;
    }
}
