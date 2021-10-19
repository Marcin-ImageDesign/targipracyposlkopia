<?php

class Event_Form_Admin_MediaPatrons extends Engine_Form
{
    protected $_belong_to = 'EventFormAdminMediaPatrons';

    protected $_tlabel = 'form_event-admin-sponsors_';

    protected $_event;

    protected $_bannerData;
    private $_subFormBanners = [];

    public function init()
    {
        $eventPatrons = $this->createElement('textarea', 'map_patrons', [
            'label' => $this->_tlabel . 'map_patrons',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_event->getMapPatronsRaw(),
        ]);

        $eventPatrons->setOptions(['cols' => '4', 'rows' => '20']);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');

        $this->addDisplayGroup(
            [$eventPatrons],
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'event-main-page',
            ]
        );

        $this->addPatronsGroup();

        $this->setDecorators([
            'FormElements',
            [['add' => 'OptionButton'], [
                'value' => $this->getView()->translate('form-button-option_add-banner'),
                'class' => 'mediaPatronsAddBanner',
                'block' => 'mediaPatronsAddBanner',
            ]],
            'Form',
            ['FormError', ['placement' => 'PREPEND']],
            [['showHide' => 'OptionButton'], [
                'value' => $this->getView()->translate('form-button-option_show-hide-group'),
                'class' => 'hideShowGroup',
            ]],
        ]);
    }

    public function addPatronsGroup()
    {
        // adding subform
        foreach ($this->_bannerData as $k => $v) {
            $this->_subFormBanners[$k] = new Event_Form_Admin_MediaPatrons_Banner([
                'data' => $v,
                'key' => $k,
            ]);
            $this->_subFormBanners[$k]->setElementsBelongTo('patrons[' . $k . ']');
            $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
        }
    }

    public function isValid($data)
    {
        // remove old subforms
        foreach (array_keys($this->_subFormBanners) as $k) {
            $this->removeSubForm('banner-' . $k);
        }

        // add/edit all subforms
        foreach ($data[$this->_belong_to]['patrons'] as $k => $v) {
            $this->_subFormBanners[$k] = new Event_Form_Admin_MediaPatrons_Banner([
                'data' => $v,
                'key' => $k,
            ]);

            $this->_subFormBanners[$k]->setElementsBelongTo('patrons[' . $k . ']');
            $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
        }

        return parent::isValid($data);
    }

    public function postIsValid($data)
    {
        // Upload settting
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

        // date belong to
        $data = $this->getValues($this->_belong_to);

        // save an images and add to data array
        $i = 0;

        $newData = ['patrons' => []];
        foreach ($data['patrons'] as $k => $v) {
            $newData['patrons'][$i] = $v;
            if (isset($files['image' . $k]) && '' !== $files['image' . $k]['name'] && 0 === $files['image' . $k]['error']) {
                $image = Service_Image::createImage($this->_event, [
                    'type' => Engine_Utils::_()->getFileExt($upload->getFileName('image' . $k, false)),
                    'name' => $upload->getFileName('image' . $k, false),
                    'source' => $upload->getFileName('image' . $k),
                ]);

                $image->save();
                $value = $image->getId();
                $newData['patrons'][$i]['image' . $i] = $value;
            } else {
                $value = $this->_bannerData[$k]['image' . $k];
                $newData['patrons'][$i]['image' . $i] = $value;
            }

            ++$i;
        }
        // zapis do bazy (event)
        // $this->_event->setMapPatronsRaw($data['map_patrons']);

        $this->_event->setMapPatrons($newData['patrons']);

        // save subforms
        $this->_event->save();

        // zapis do bazy (event_translation)
        $this->_event->Translations->save();

        return true;
    }

    protected function setEvent($event)
    {
        $this->_event = $event;
        $this->_bannerData = $this->_event->getMapPatrons();
    }
}
