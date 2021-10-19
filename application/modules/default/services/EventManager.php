<?php

class Service_EventManager
{
    /**
     * Rozgłasza o wystąpieniu zdarzenia globalnego.
     *
     * Parametry $options:
     *  id      - zazwyczaj identyfikator (PK) obiektu, którego dotyczy event
     *  object  - instancja danego obiektu, którego dotyczy event
     *  context - obiekt lub nazwa klasy kontekstu
     *
     * Jest to ekstremalnie proste rozwiązanie.
     * Umożliwia globalne rozgłaszanie i centralne sterowanie komunikatami.
     *
     * W przyszłości -> Zend_EventManager.
     *
     * @param string $event   Nazwa zdarzenia
     * @param array  $options Dodatkowe informacje
     */
    public static function trigger($event, $options = [])
    {
        $id = isset($options['id']) ? $options['id'] : 0;
        $object = isset($options['object']) ? $options['object'] : 0;
        $context = isset($options['context']) ? $options['context'] : null;

        if ('imageDelete' === $event) {
            // usunięcie keszu statycznego dla danego zdjęcia (miniaturki)
            Service_Cache::getCache('page')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['image_' . $object->id_image]);
        }

        if ('imageAdd' === $event || 'imageDelete' === $event || 'imageUpdate' === $event) {
            // usunięcie listy zdjęć dla danego objektu
//            Service_Cache::getCache('managed')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('images', 'object_id_'.$object->object_id, 'object_class_'.$object->class_name));
        }
    }
}
