<?php

class Admin_Form_Menu extends Engine_FormAdmin
{
    public function __construct($menu, $options = [])
    {
        parent::__construct($options);

        $title = $this->createElement('text', 'title', [
            'label' => 'Title',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
            'filters' => ['StringTrim'],
        ]);

        $text = $this->createElement('tinyMce', 'text', [
            'label' => 'Text',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
        ]);
        $decorator = $text->getDecorator('data');
        $decorator->setOption('style', 'width: 600px;');

        $menuType = $this->createElement('radio', 'id_menu_type', [
            'label' => 'Site type',
            'required' => true,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'onclick' => 'linkType()',
        ]);

        $menuType->setMultiOptions(
            [
                Menu::MENU_TYPE_TEXT => 'Text', Menu::MENU_TYPE_LINK => 'Link', ]
        );

        $link = $this->createElement('text', 'link', [
            'label' => 'Link',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'field-text',
        ]);

        $menuType->getDecorator('row')->setOption('class', 'form-item menu_type_content');
        $link->getDecorator('row')->setOption('class', 'form-item link_content');
        $text->getDecorator('row')->setOption('class', 'form-item text_content');

        $metatag_title = $this->createElement('text', 'metatag_title', [
            'label' => 'Title',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
        ]);

        $metatag_key = $this->createElement('text', 'metatag_key', [
            'label' => 'Keywords',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
        ]);

        $metatag_desc = $this->createElement('text', 'metatag_desc', [
            'label' => 'Description',
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => ' field-text field-text-big',
        ]);
        $slug = $this->createElement('text', 'slug', [
            'class' => 'field-text field-text-small',
            'readonly' => 'readonly',
            'label' => 'Slug',
            'value' => $menu->getUriFull(),
        ]);
        $metatag_title->getDecorator('row')->setOption('class', 'form-item metatag_title_content');
        $metatag_key->getDecorator('row')->setOption('class', 'form-item metatag_key_content');
        $metatag_desc->getDecorator('row')->setOption('class', 'form-item metatag_desc_content');

        $is_metatag = $this->createElement('checkbox', 'is_metatag', [
            'label' => 'Is active',
            'decorators' => $this->elementDecoratorsCenturion,
            'uncheckedValue' => '0',
            'class' => 'seoActive',
            'onclick' => 'seoActive()',
        ]);

        $is_header = $this->createElement('checkbox', 'is_header', [
            'label' => 'Show in the top menu',
            'decorators' => $this->elementDecoratorsCenturion,
            'uncheckedValue' => '0',
        ]);

        $is_footer = $this->createElement('checkbox', 'is_footer', [
            'label' => 'Show in the bottom menu',
            'decorators' => $this->elementDecoratorsCenturion,
            'uncheckedValue' => '0',
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$title, $submit]);
        $header->addAttribs(['class' => 'form-header']);
        $this->addSubForm($header, 'header');

        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$is_header, $is_footer, $menuType, $slug, $link, $text], 'content');

        $group = $main->getDisplayGroup('content');
        $group->clearDecorators();
        $group->setLegend('Content');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $main->addAttribs(['class' => 'form-main']);

        $this->addSubForm($main, 'main');

        $seo = new Zend_Form_SubForm();
        $seo->setDisableLoadDefaultDecorators(false);
        $seo->setDecorators($this->subFormDecoratorsCenturion);
        $seo->addDisplayGroup([$is_metatag, $metatag_title, $metatag_key, $metatag_desc], 'seo');
        $group = $seo->getDisplayGroup('seo');
        $group->clearDecorators();
        $group->setLegend('SEO');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $seo->addAttribs(['class' => 'form-main']);

        $this->addSubForm($seo, 'seo');
    }
}
