<?php namespace models\summit;
/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use App\Events\SummitAttendeeProfileCompleted;
use App\Mail\InviteAttendeeTicketEditionMail;
use App\Mail\RevocationTicketEmail;
use App\Mail\SummitAttendeeTicketEmail;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use models\exceptions\ValidationException;
use models\main\Company;
use models\main\Member;
use models\main\SummitMemberSchedule;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitAttendeeRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(
 *          name="summit",
 *          inversedBy="attendees"
 *     )
 * })
 * @ORM\Table(name="SummitAttendee")
 * Class SummitAttendee
 * @package models\summit
 */
class SummitAttendee extends SilverstripeBaseModel
{

    const StatusIncomplete = 'Incomplete';
    const StatusComplete   = 'Complete';
    /**
     * @ORM\Column(name="FirstName", type="string")
     * @var string
     */
    private $first_name;

    /**
     * @ORM\Column(name="Surname", type="string")
     * @var string
     */
    private $surname;

    /**
     * @ORM\Column(name="Email", type="string")
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(name="SharedContactInfo", type="boolean")
     * @var bool
     */
    private $share_contact_info;

    /**
     * @ORM\Column(name="DisclaimerAcceptedDate", type="datetime")
     * @var \DateTime
     */
    private $disclaimer_accepted_date;

    /**
     * @ORM\Column(name="SummitHallCheckedInDate", type="datetime")
     * @var \DateTime
     */
    private $summit_hall_checked_in_date;

    /**
     * @ORM\Column(name="LastReminderEmailSentDate", type="datetime")
     * @var \DateTime
     */
    private $last_reminder_email_sent_date;

    /**
     * @ORM\Column(name="SummitHallCheckedIn", type="boolean")
     * @var \DateTime
     */
    private $summit_hall_checked_in;

    /**
     * @ORM\Column(name="ExternalId", type="string")
     * @var string
     */
    private $external_id;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID", nullable=true)
     * @var Member
     */
    private $member;

    /**
     * @ORM\OneToMany(targetEntity="SummitOrderExtraQuestionAnswer", mappedBy="attendee", cascade={"persist","remove"}, orphanRemoval=true)
     * @var SummitOrderExtraQuestionAnswer[]
     */
    private $extra_question_answers;

    /**
     * @ORM\Column(name="Company", type="string")
     * @var string
     */
    private $company_name;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Company")
     * @ORM\JoinColumn(name="CompanyID", referencedColumnName="ID", nullable=true)
     * @var Company
     */
    private $company;

    /**
     * @ORM\Column(name="Status", type="string")
     * @var string
     */
    private $status;

    /**
     * @return \DateTime
     */
    public function getSummitHallCheckedInDate(){
        return $this->summit_hall_checked_in_date;
    }

    /**
     * @return bool
     */
    public function getSummitHallCheckedIn(){
        return (bool)$this->summit_hall_checked_in;
    }

    /**
     * @param bool $summit_hall_checked_in
     */
    public function setSummitHallCheckedIn($summit_hall_checked_in){
        $this->summit_hall_checked_in = $summit_hall_checked_in;
    }

    /**
     * @param \DateTime $summit_hall_checked_in_date
     */
    public function setSummitHallCheckedInDate(\DateTime $summit_hall_checked_in_date){
        $this->summit_hall_checked_in_date = $summit_hall_checked_in_date;
    }

    /**
     * @return boolean
     */
    public function getSharedContactInfo()
    {
        return $this->share_contact_info;
    }

    /**
     * @param boolean $share_contact_info
     */
    public function setShareContactInfo($share_contact_info)
    {
        $this->share_contact_info = $share_contact_info;
    }

    /**
     * @return int
     */
    public function getMemberId(){
        try {
            return is_null($this->member) ? 0 : $this->member->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasMember(){
        return $this->getMemberId() > 0;
    }

    /**
     * @ORM\OneToMany(targetEntity="SummitAttendeeTicket", mappedBy="owner", cascade={"persist", "remove"})
     * @var SummitAttendeeTicket[]
     */
    private $tickets;

    /**
     * @return SummitAttendeeTicket[]
     */
    public function getTickets(){
        return $this->tickets;
    }

    /**
     * @param SummitAttendeeTicket $ticket
     */
    public function addTicket(SummitAttendeeTicket $ticket){
        if($this->tickets->contains($ticket)) return;
        $this->tickets->add($ticket);
        $ticket->setOwner($this);
    }

    /**
     * @return Member
     */
    public function getMember():?Member{
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember(Member $member){
        $this->member = $member;
    }

    use SummitOwned;

    public function __construct()
    {
        parent::__construct();
        $this->share_contact_info       = false;
        $this->summit_hall_checked_in   = false;
        $this->tickets                  = new ArrayCollection();
        $this->extra_question_answers   = new ArrayCollection();
        $this->disclaimer_accepted_date = null;
        $this->status                   = self::StatusIncomplete;
    }

    /**
     * @return SummitEventFeedback[]
     */
    public function getEmittedFeedback(){

        return $this->member->getFeedback()->matching
        (
            Criteria::create()->orderBy(["id" => Criteria::ASC])
        );
    }

    /**
     * @param SummitEvent $event
     * @throws ValidationException
     * @deprecated use Member::add2Schedule instead
     */
    public function add2Schedule(SummitEvent $event)
    {
        $this->member->add2Schedule($event);
    }

    /**
     * @param SummitEvent $event
     * @throws ValidationException
     * @deprecated use Member::removeFromSchedule instead
     */
    public function removeFromSchedule(SummitEvent $event)
    {
       $this->member->removeFromSchedule($event);
    }

    /**
     * @param SummitEvent $event
     * @return bool
     * @deprecated use Member::isOnSchedule instead
     */
    public function isOnSchedule(SummitEvent $event)
    {
        return $this->member->isOnSchedule($event);
    }

    /**
     * @param SummitEvent $event
     * @return null| SummitMemberSchedule
     * @deprecated use Member::getScheduleByEvent instead
     */
    public function getScheduleByEvent(SummitEvent $event){
        return $this->member->getScheduleByEvent($event);
    }

    /**
     * @return SummitMemberSchedule[]
     * @deprecated use Member::getScheduleBySummit instead
     */
    public function getSchedule(){
        return $this->member->getScheduleBySummit($this->summit);
    }

    /**
     * @return int[]
     * @deprecated use Member::getScheduledEventsIds instead
     */
    public function getScheduledEventsIds(){
        return $this->member->getScheduledEventsIds($this->summit);
    }

    /**
     * @param int $event_id
     * @return null|RSVP
     * @deprecated use Member::getRsvpByEvent instead
     */
    public function getRsvpByEvent($event_id){
       return $this->member->getRsvpByEvent($event_id);
    }

    /**
     * @param int $ticket_id
     * @return SummitAttendeeTicket
     */
    public function getTicketById($ticket_id){
        $ticket = $this->tickets->matching(
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq("id", $ticket_id))
        )->first();
        return $ticket ? $ticket : null;
    }

    /**
     * @param SummitAttendeeTicket $ticket
     * @return $this
     */
    public function removeTicket(SummitAttendeeTicket $ticket){
        $this->tickets->removeElement($ticket);
        $ticket->clearOwner();
        return $this;
    }

    /**
     * @param SummitAttendeeTicket $ticket
     */
    public function sendRevocationTicketEmail(SummitAttendeeTicket $ticket){
        if(!$ticket->hasOwner()) return;

        if($ticket->getOwner()->getId() != $this->getId()) return;

        Mail::queue(new RevocationTicketEmail($this, $ticket));
    }

    /**
     * @param SummitAttendeeTicket $ticket
     */
    public function sendInvitationEmail(SummitAttendeeTicket $ticket){
        Log::debug(sprintf("SummitAttendee::sendInvitationEmail attendee %s", $this->getEmail()));
        if($ticket->getOwnerEmail() != $this->getEmail()) return;
        $this->updateStatus();
        if($this->isComplete()) {
            Log::debug(sprintf("SummitAttendee::sendInvitationEmail attendee %s is complete", $this->getEmail()));
            Mail::queue(new SummitAttendeeTicketEmail($ticket));
            return;
        }
        Log::debug(sprintf("SummitAttendee::sendInvitationEmail attendee %s is not complete", $this->getEmail()));
        Mail::queue(new InviteAttendeeTicketEditionMail($ticket));
    }

    /**
     * @return bool
     */
    public function hasTickets(){
        return $this->tickets->count() > 0;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        if($this->hasMember()){
            return $this->member->getFirstName();
        }
        return $this->first_name;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    /**
     * @return string
     */
    public function getSurname(): ?string
    {
        if($this->hasMember()){
            return $this->member->getLastName();
        }
        return $this->surname;
    }

    /**
     * @param string $surname
     */
    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        if($this->hasMember()){
            return $this->member->getEmail();
        }
        return $this->email;
    }

    public function getFullName():?string{
        if($this->hasMember()){
            $fullname  = $this->member->getFullName();
            if(!empty($fullname))
                return $fullname;
        }
        $fullname = $this->first_name;
        if(!empty($this->surname)){
            if(!empty($fullname)) $fullname .= ' ';
            $fullname .= $this->surname;
        }
        return $fullname;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = strtolower(trim($email));
    }

    /**
     * @return \DateTime
     */
    public function getDisclaimerAcceptedDate(): ?\DateTime
    {
        return $this->disclaimer_accepted_date;
    }

    /**
     * @return bool
     */
    public function hasDisclaimerAccepted():bool{
        return !is_null($this->disclaimer_accepted_date);
    }

    /**
     * @param \DateTime $disclaimer_accepted_date
     */
    public function setDisclaimerAcceptedDate(\DateTime $disclaimer_accepted_date): void
    {
        $this->disclaimer_accepted_date = $disclaimer_accepted_date;
    }

    /**
     * @return SummitOrderExtraQuestionAnswer[]
     */
    public function getExtraQuestionAnswers()
    {
        return $this->extra_question_answers;
    }


    public function clearExtraQuestionAnswers()
    {
        return $this->extra_question_answers->clear();
    }

    /**
     * @param SummitOrderExtraQuestionAnswer $answer
     */
    public function addExtraQuestionAnswer(SummitOrderExtraQuestionAnswer $answer){
        if($this->extra_question_answers->contains($answer)) return;
        $this->extra_question_answers->add($answer);
        $answer->setAttendee($this);
    }

    /**
     * @param SummitOrderExtraQuestionAnswer $answer
     */
    public function removeExtraQuestionAnswer(SummitOrderExtraQuestionAnswer $answer){
        if(!$this->extra_question_answers->contains($answer)) return;
        $this->extra_question_answers->removeElement($answer);
        $answer->clearAttendee();
    }

    /**
     * @return string
     */
    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    /**
     * @param string $company_name
     */
    public function setCompanyName(string $company_name): void
    {
        $this->company_name = $company_name;
    }

    /**
     * @return Company
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     */
    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }

    /**
     * @return bool
     */
    public function needToFillDetails():bool {
        return $this->getStatus() == self::StatusIncomplete;
    }

    /**
     * @return bool
     */
    public function isComplete():bool{
        return $this->getStatus() == self::StatusComplete;
    }

    /**
     * @return string
     */
    public function getStatus():?string{
        return $this->status;
    }

    public function updateStatus():string {

        Log::debug(sprintf("SummitAttendee::updateStatus original status %s", $this->status));
        $is_disclaimer_mandatory = $this->summit->isRegistrationDisclaimerMandatory();

        // mandatory fields
        if($is_disclaimer_mandatory && !$this->hasDisclaimerAccepted()){
            $this->status = self::StatusIncomplete;
            Log::debug(sprintf("SummitAttendee::updateStatus StatusIncomplete for attendee %s (disclaimer mandatory)", $this->id));
            return $this->status;
        }

        if(empty($this->getFirstName())){
            $this->status = self::StatusIncomplete;
            Log::debug(sprintf("SummitAttendee::updateStatus StatusIncomplete for attendee %s (first name empty)", $this->id));
            return $this->status;
        }

        if(empty($this->getSurname())){
            $this->status = self::StatusIncomplete;
            Log::debug(sprintf("SummitAttendee::updateStatus StatusIncomplete for attendee %s (last name empty)", $this->id));
            return $this->status;
        }

        if(empty($this->getEmail())){
            $this->status = self::StatusIncomplete;
            Log::debug(sprintf("SummitAttendee::updateStatus StatusIncomplete for attendee %s (email empty)", $this->id));
            return $this->status;
        }

        // check mandatory questions

        // get mandatory question ids
        $extra_questions_mandatory_questions     = $this->summit->getMandatoryOrderExtraQuestionsByUsage(SummitOrderExtraQuestionTypeConstants::TicketQuestionUsage);
        $extra_questions_mandatory_questions_ids = [];

        foreach($extra_questions_mandatory_questions as $extra_mandatory_question){
            $extra_questions_mandatory_questions_ids[] = $extra_mandatory_question->getId();
        }

        // now check the answers
        foreach($this->extra_question_answers as $extra_question_answer){
            if(!$extra_question_answer->hasQuestion()) continue;
            $question_type = $extra_question_answer->getQuestion();
            if(in_array($question_type->getId(), $extra_questions_mandatory_questions_ids)) {
                // is mandatory now check if we have value set
                if(!$extra_question_answer->hasValue()) {
                    $this->status = self::StatusIncomplete;
                    Log::debug(sprintf("SummitAttendee::updateStatus StatusIncomplete for attendee %s ( mandatory extra question missing value )", $this->id));
                    return $this->status;
                }
                // delete from ids due its already answeres
                if (($key = array_search($question_type->getId(), $extra_questions_mandatory_questions_ids)) !== false) {
                    unset($extra_questions_mandatory_questions_ids[$key]);
                }
            }
        }

        // if we have mandatory questions without answer ...
        if(count($extra_questions_mandatory_questions_ids) > 0 ){
            $this->status = self::StatusIncomplete;
            Log::debug(sprintf("SummitAttendee::updateStatus StatusIncomplete for attendee %s ( mandatory extra questions )", $this->id));
            return $this->status;
        }

        $this->status = self::StatusComplete;

        return $this->status;
    }

    /**
     * @return \DateTime
     */
    public function getLastReminderEmailSentDate(): ?\DateTime
    {
        $last_action_date = $this->last_reminder_email_sent_date;

        if (is_null($last_action_date)) {
            $last_action_date = $this->getCreatedUTC();
        }

        return $last_action_date;
    }

    /**
     * @param \DateTime $last_reminder_email_sent_date
     */
    public function setLastReminderEmailSentDate(\DateTime $last_reminder_email_sent_date): void
    {
        $this->last_reminder_email_sent_date = $last_reminder_email_sent_date;
    }

    public function getExternalId(): ?string
    {
        return $this->external_id;
    }

    /**
     * @param string $external_id
     */
    public function setExternalId(string $external_id): void
    {
        $this->external_id = $external_id;
    }

}