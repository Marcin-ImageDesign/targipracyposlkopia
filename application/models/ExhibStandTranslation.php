<?php

/**
 * ExhibStandTranslation.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ExhibStandTranslation extends Table_ExhibStandTranslation
{
    public function getStandId()
    {
        return $this->id_exhib_stand;
    }

    public function getStandName()
    {
        return $this->name;
    }

    public function getEventName()
    {
        $id_exhib_stand = $this->getStandId();

        $query = Doctrine_Query::create()
            ->select('es.id_event')
            ->from('ExhibStand es')
            ->where('es.id_exhib_stand = ?', $id_exhib_stand)
            ->execute()
            ;

        $event = $query->getData();
        $event_id = $event[0]->id_event;

        $query = Doctrine_Query::create()
            ->select('ev.replace_text')
            ->from('Event ev')
            ->where('ev.id_event = ?', $event_id)
            ->execute()
            ;

        $query = $query->getData();

        return $query[0]->getReplaceText();
    }

    public function preSave($event)
    {
        if (empty($this->id_language)) {
            $this->id_language = Engine_I18n::getLangId();
        }
    }
}