<?php

class User_Form_User_Edit extends User_Form_User
{
    /**
     * @param User  $user
     * @param array $options
     */
    public function init()
    {
        parent::init();

        $fields = [];
        $fileDescriptionSize = $this->getView()->translate('dot_max_file_size') . ' ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 2) . ' MB';

        $fields['img'] = $this->createElement('FileImage', 'img', [
            'label' => $this->_tlabel . 'image',
            'validators' => [
                ['Extension', false, ALLOWED_IMAGE_EXTENSIONS],
                ['Count', false, 1],
                ['Size', false, MAX_FILE_SIZE],
            ],
            'description' => ALLOWED_IMAGE_EXTENSIONS . $fileDescriptionSize,
        ]);

        $fields['img']->getDecorator('row')->setOption('class', 'form-item img_content');

        if ($this->user->hasImage()) {
            $fileImageDecorator = $fields['img']->addDecorator('FileImage');
            $fileImageDecorator->setOptions([
                'image' => Service_Image::getUrl($this->user->getIdImage(), 40, 40, 'a'),
            ]);
        }

        $this->addDisplayGroup(
            $fields,
            'image',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_user_avatar',
            ]
        );
    }
}
