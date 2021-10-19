<?php

class Event_Form_Admin_StandBanner extends Engine_Form
{
    /**
     * @var ExhibStand
     */
    protected $_model;

    protected $_belong_to = 'StandBanner';

    protected $_tlabel = 'label_form_event-admin-stand-banner_';

    private $_bannerData = [];
    private $_standBannerData = [];

    public function init()
    {
        $fields = [];

        $id_stand_level = $this->_model->id_stand_level;
        $suffix = '';
        switch ($id_stand_level) {
            case ExhibStand::STAND_LEVEL_STANDARD:
                $level = 'std';

                break;
            case ExhibStand::STAND_LEVEL_REGIONAL:
                $level = 'reg';

                break;
            case ExhibStand::STAND_LEVEL_MAIN:
                $level = 'main';

                break;
        }

        $i = 1;
        foreach (array_keys($this->_bannerData) as $k) {
            $suffix = $level . '_' . $k;

            $Bname = ('top' === $k || 'desk' === $k || 'tv' === $k) ? $k : $i++;

            $fields[$k] = $this->createElement('FileImage', $k, [
                //'label' => $this->_tlabel.$suffix, //t�umaczenie
                //'label' => 'Banner - '.$k,    //nazwy
                'label' => 'Banner - ' . $Bname,
                'validators' => [
                    ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                    ['Count', false, 1],
                    ['Size', false, MAX_FILE_SIZE],
                ],
            ]);
            if (isset($this->_standBannerData[$k])) {
                $imageDecorator = $fields[$k]->getDecorator('FileImage');
                $imageDecorator->setOptions([
                    'id_image' => $this->_standBannerData[$k]['id_image'],
                    'crop' => true,
                    'delete' => [
                        'params' => [
                            'stand_hash' => $this->_model->getHash(),
                            'banner_key' => $k,
                        ],
                        'route' => 'event_admin-stand-banner_delete',
                    ],
                ]);
            }
        }

        $this->addDisplayGroup($fields, 'main', [
            'legend' => $this->_tlabel . 'group_main',
        ]);

        $buttons = [];
        $buttons['submit'] = $this->createElement('submit', 'submit', [
            'label' => 'Save',
        ]);

        $this->addDisplayGroup($buttons, 'buttons');
    }

    /**
     * @param $model ExhibStand
     */
    protected function setModel($model)
    {
        $this->_model = $model;
        $this->_bannerData = $this->_model->ExhibStandViewImage->getDataBanner();
        $this->_standBannerData = $this->_model->getDataBanner();
    }

    protected function postIsValid($data)
    {
        $_data = $this->getValues($this->_belong_to);
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

        foreach (array_keys($this->_bannerData) as $k) {
            if (isset($files[$k]) && 0 === $files[$k]['error']) {
                $image = Service_Image::createImage($this->_model, [
                    'type' => Engine_Utils::_()->getFileExt($upload->getFileName($k, false)),
                    'name' => $upload->getFileName($k, false),
                    'source' => $upload->getFileName($k),
                ]);
                $image->save();
                $value = ['id_image' => $image->getId()];
                $this->_model->setDataBanner($k, $value);
            }
        }

        return true;
    }
}
