<?php

use BracketSpace\Notification\Abstracts\Trigger as AbstractTrigger;

class ZoomAttendanceTrigger extends AbstractTrigger {

  public function __construct()
  {
    parent::__construct(
      'zoom_attendance/meeting_started',
      'Meeting Started'
    );

    // add_action('init', array($this, 'any_notification_trigger'));

   $this->add_action('zoom_meeting_started');

    $this->set_group('ZoomAttendance');
  }

  public function context($param_one, $param_two)
  {

    if(false === $param_two){
      return false;
    }

    $this->param_value = $param_one;
  }

  public function merge_tags()
  {
    $this->add_merge_tag(new \BracketSpace\Notification\Defaults\MergeTag\StringTag( array(
      'slug' => 'meeting_title',
      'name' => 'Meeting Title',
      'resolver' => function($trigger) {
        return $trigger->payload->object->topic;
      },
      'description' => 'This is a zoom meeting title',
      'example' => true
    )));
  }

}

class MeetingEndedTrigger extends AbstractTrigger {

  public function __construct()
  {
    parent::__construct(
      'zoom_attendance/meeting_ended',
      'Meeting Ended'
    );

    // add_action('init', array($this, 'any_notification_trigger'));

   $this->add_action('zoom_meeting_ended');

    $this->set_group('ZoomAttendance');
  }

  public function context($param_one, $param_two)
  {

    if(false === $param_two){
      return false;
    }

    $this->param_value = $param_one;
  }

  public function merge_tags()
  {
    $this->add_merge_tag(new \BracketSpace\Notification\Defaults\MergeTag\StringTag( array(
      'slug' => 'meeting_title',
      'name' => 'Meeting Title',
      'resolver' => function($trigger) {
        return $trigger->payload->object->topic;
      },
      'description' => 'This is a zoom meeting title',
      'example' => true
    )));
  }

}