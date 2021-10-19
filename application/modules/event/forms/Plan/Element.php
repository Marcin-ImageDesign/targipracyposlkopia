<?php

class Event_Form_Plan_Element extends Engine_FormAdmin
{
    /**
     * @var EventPlan Event Plan
     */
    protected $eventPlan;

    /**
     * @param EventPlan $eventPlan
     * @param array     $options
     */
    public function __construct($eventPlan, $options = null)
    {
        parent::__construct($options);
        $this->eventPlan = $eventPlan;
        $vDate = new Zend_Validate_Date();
        $vDate->setFormat('YYYY-MM-dd');
        $vTime = new Engine_Validate_Time();
        $times = [];
        $timeStarting = date('Y-m-d 00:00:00');
        $timeEnding = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('n'), date('j') + 1));
        $zStart = new Zend_Date($timeStarting, null, new Zend_Locale('pl'));
        $zEnd = new Zend_Date($timeEnding, null, new Zend_Locale('pl'));

        while ($zStart->isEarlier($zEnd)) {
            $zStart->addMinute(EventDate::TIME_INTERVALS);
            $times[$zStart->get(Zend_Date::TIME_SHORT)] = $zStart->get(Zend_Date::TIME_SHORT);
        }
        $midnight = array_pop($times);
        array_unshift($times, $midnight);

        $title = $this->createElement('text', 'title', [
            'label' => 'Title',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'filters' => ['StringTrim'],
            'value' => $eventPlan->getTitle(),
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $eventDateList = Doctrine_Query::create()
            ->from('EventDate ed')
            ->where('ed.id_event = ?', $eventPlan->id_event)
            ->orderBy('ed.date ASC')
            ->execute()
            ->toKeyValueArray('hash', 'date')
        ;

        $date = $this->createElement('select', 'date', [
            'label' => 'Date',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'required' => true,
            'class' => ' field-text field-text-small',
            'filters' => ['StringTrim'],
            'validators' => [$vDate],
            'multiOptions' => $eventDateList,
            'value' => $eventPlan->EventDate->getHash(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($eventDateList)], ],
            ],
        ]);

        $time_start = $this->createElement('select', 'time_start', [
            'label' => 'Start date',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-small',
            'filters' => ['StringTrim'],
            'validators' => [$vTime],
            'multiOptions' => $times,
            'value' => $eventPlan->getTimeStartFormat('H:i'),
        ]);

        $time_end = $this->createElement('select', 'time_end', [
            'label' => 'End date',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-small',
            'filters' => ['StringTrim'],
            'validators' => [$vTime],
            'multiOptions' => $times,
            'value' => $eventPlan->getTimeEndFormat('H:i'),
        ]);

        $lead = $this->createElement('textarea', 'lead', [
            'label' => 'Lead',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
            'value' => $eventPlan->getLead(),
        ]);

        $text = $this->createElement('tinyMce', 'text', [
            'label' => 'Text',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'editorOptions' => ['mode' => 'exact', 'elements' => 'main-text'],
            'value' => $eventPlan->getText(),
            'filters' => ['StringTrim'],
        ]);
        $decorator = $text->getDecorator('data');
        $decorator->setOption('style', 'width: 600px;');

        $options = [
            '0' => 'No',
            '1' => 'Yes',
        ];
        $listOptions = $this->translate($options);

        $is_active = $this->createElement('select', 'is_active', [
            'label' => 'Visible',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'field-switcher',
            'value' => $eventPlan->isActive(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptions)], ],
            ],
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        //----------------------------------------------
        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$title, $submit]);
        $header->addAttribs(['class' => 'form-header']);

        //----------------------------------------------
        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$date, $time_start, $time_end, $lead, $text], 'content');

        $group = $main->getDisplayGroup('content');
        $group->clearDecorators();
        $group->setLegend('Details');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $main->addAttribs(['class' => 'form-main']);

        //----------------------------------------------
        $aside = new Zend_Form_SubForm();
        $aside->setDisableLoadDefaultDecorators(false);
        $aside->setDecorators($this->subFormDecoratorsCenturion);
        $aside->addDisplayGroup([$is_active], 'aside');

        $groupAside = $aside->getDisplayGroup('aside');
        $groupAside->clearDecorators();
        $groupAside->setLegend('Options');
        $groupAside->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $aside->addAttribs(['class' => 'form-aside']);

        //----------------------------------------------
        $this->addSubForm($header, 'header');
        $this->addSubForm($main, 'main');
        $this->addSubForm($aside, 'aside');
    }

    public function isValid($data)
    {
        $ret = parent::isValid($data);
        $eventDate = EventDate::findOneByHash($this->main->date->getValue());
        $time_start = $eventDate->date . ' ' . $this->main->time_start->getValue() . ':00';
        $time_end = $eventDate->date . ' ' . $this->main->time_end->getValue() . ':00';

        $date = new Zend_Date($time_start, Zend_Date::ISO_8601);
        $date2 = new Zend_Date($time_end, Zend_Date::ISO_8601);

        if ($date->compare($date2) > 0) {
            $this->main->time_end->addError("End of event cannot be earlier than it's beginning");
            $ret = false;
        }

        if (!$date->isEarlier($date2)) {
            $this->main->time_end->addError('End of event cannot be same as beginning');
            $ret = false;
        }

        if ($this->eventPlan) {
            $eventPlanEndOld = [$eventDate->date];
            $eventPlanStartOld = [$eventDate->date];

            $event = $this->eventPlan->getId() ? $this->eventPlan->Event : Event::findOneByHash($data['hash']);

            $newEventPlanStart = $eventPlanStartOld[0] . ' ' . $this->main->time_start->getValue() . ':00';
            $newEventPlanEnd = $eventPlanEndOld[0] . ' ' . $this->main->time_end->getValue() . ':00';

            $limit = Doctrine_Query::create()
                ->from('EventPlan ep')
                ->where(
                    '( ep.time_start BETWEEN ? AND ?) OR (ep.time_start = ? AND ep.time_end = ?) OR (ep.time_end BETWEEN ? AND ? ) OR (? BETWEEN ep.time_start AND ep.time_end) OR (? BETWEEN ep.time_start AND ep.time_end)',
                    [
                        $newEventPlanStart,
                        $newEventPlanEnd,
                        $newEventPlanStart,
                        $newEventPlanEnd,
                        $newEventPlanStart,
                        $newEventPlanEnd,
                        $newEventPlanStart,
                        $newEventPlanEnd,
                    ]
                )
                ->andWhere('ep.id_event = ?', $event->getId())
                ->andWhere(' ep.time_start != ?', $newEventPlanEnd)
                ->andWhere(' ep.time_end != ? ', $newEventPlanStart)
                ->andWhere(' ep.is_active = 1')
                ->andWhere(' ep.id_base_user = ?', $event->BaseUser->getId())
            ;

            if ($this->eventPlan->getId()) {
                $limit->andWhere('ep.id_event_plan !=?', $this->eventPlan->getId());
            }

            $counter = $limit->execute();
            if ($counter->count() > 0) {
                $this->main->time_start->addError('Time interval busy');
                $this->main->time_end->addError('Time interval busy');
                $ret = false;
            }
        }

        return $ret;
    }
}
