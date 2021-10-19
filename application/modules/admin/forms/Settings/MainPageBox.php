<?php

class Admin_Form_Settings_MainPageBox extends Engine_Form
{
    protected $_subFormBoxSponsors = [];

    protected $_belong_to = 'AdminFormSettingsMainPageBox';

    protected $_tlabel = 'form_admin_form_settings_main_page_box';

    protected $_homePageVersion;

    protected $_boxSponsorsData;

    public function __construct(HomePage $_homepPage)
    {
        $this->_homePageVersion = $_homepPage->Version;
        $this->_boxSponsorsData = $this->_homePageVersion->getPageData();
        parent::__construct();
    }

    public function init()
    {
        $this->setEnctype('multipart/form-data');

        $boxesJson = $this->createElement('textarea', 'data', [
            'label' => $this->_tlabel . 'data',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_homePageVersion->getDataRaw(),
        ]);

        $boxesJson->setOptions(['cols' => '4', 'rows' => '20']);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup(
            [$boxesJson],
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'event-main-page',
            ]
        );
        $this->addDisplayGroup([$submit], 'buttons');

        foreach ($this->_boxSponsorsData['sponsors'] as $k => $v) {
            $this->_subFormBoxSponsors[$k] = $this->createElement('HomeBoxSponsors', 'homeBoxSponsors' . $k, [
                'value' => $v,
                'key' => $k,
            ]);
            $this->addDisplayGroup(
                [$this->_subFormBoxSponsors[$k]],
                'HomeBoxSponsors' . $k,
                [
                    'class' => 'group-wrapper group-main',
                    'legend' => 'Sponsors Box ' . $k,
                ]
            );
        }
    }

    public function postIsValid($data)
    {
        // add & save image
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

        // add sponsors to _boxSponsorsData -> array
        foreach ($data['AdminFormSettingsMainPageBoxSponsor'] as $key => $sponsor) {
            if (isset($files['image' . $key]) && '' !== $files['image' . $key]['name'] && 0 === $files['image' . $key]['error']) {
                $image = Service_Image::createImage($this->_homePageVersion, [
                    'type' => Engine_Utils::_()->getFileExt($upload->getFileName('image' . $key, false)),
                    'name' => $_FILES['image' . $key]['name'],
                    'source' => $_FILES['image' . $key]['tmp_name'],
                ]);
                $image->save();

                $sponsor['image' . $key] = $image->getId();
            } else {
                $value = $this->_boxSponsorsData['sponsors'][$key]['image' . $key];
                $sponsor['image' . $key] = $value;
            }
            $data['AdminFormSettingsMainPageBoxSponsor']['sponsors'][$key] = $sponsor;
        }

        // save to database
        $this->_homePageVersion->setPageData($data['AdminFormSettingsMainPageBoxSponsor']['sponsors']);
        $this->_homePageVersion->save();

        return true;
    }
}
