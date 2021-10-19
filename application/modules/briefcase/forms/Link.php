<?php

class Briefcase_Form_Link extends Engine_Form
{
    /**
     * @var BriefcaseLink
     */
    private $briefcaseLink;

    /**
     * @param EventFile $eventFile
     * @param array     $options
     * @param mixed     $briefcaseLink
     */
    public function __construct($briefcaseLink, $options = null)
    {
        parent::__construct($options);
        $this->briefcaseLink = $briefcaseLink;

        $title = $this->createElement('text', 'title', [
            'label' => 'Title',
            'required' => true,
            'value' => $this->briefcaseLink->getTitle(),
            'filters' => ['StringTrim'],
        ]);

        $link = $this->createElement('text', 'link', [
            'label' => 'Link',
            'required' => true,
            'value' => $this->briefcaseLink->getLink(),
            'filters' => ['StringTrim'],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Zapisz',
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        // cześć głowna
        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$title, $link, $submit], 'content');

        $group = $main->getDisplayGroup('content');
        $group->clearDecorators();
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $main->addAttribs(['class' => 'form-main']);

        // dodanie subformularzu do formularza
        $this->addSubForm($main, 'main');
    }

    /**
     * @return BaseUser
     */
    public function populateModel()
    {
        $subForms = $this->getSubForms();

        foreach ($subForms as $subForm) {
            $populateMethod = 'populate' . ucfirst($subForm->getName());
            if (method_exists($this, $populateMethod)) {
                $this->{$populateMethod}();
            }
        }

        return $this->baseUser;
    }

    private function populateMain()
    {
        $this->briefcaseLink->setTitle($this->main->title->getValue());
        $this->briefcaseLink->setLink($this->main->link->getValue());
    }
}
