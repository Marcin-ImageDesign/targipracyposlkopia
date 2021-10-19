<?php

class Inquiry_Service_Search
{
    /**
     * Pobiera listę elementów.
     *
     * Lista opcji:
     *
     * order (direction, sort)  - opcje sortowania
     *
     * paginator             - zwraca adapter do paginacji
     * idPk                 - tablica id (PK) wybranych elementów
     *
     * search - opcja wyszukiwania
     *      subject - (string)
     *      dateFrom - (string)
     *      dateTo - (string)
     *      channel - (string)
     *      idBaseUser - (int) id baseUsera
     *      standsArray - (array) lista stoisk dla ktorych wyciagamy zapytania
     *
     * @return Doctrine_Query|Engine_Paginator_Adapter_Doctrine
     */
    public static function getByOptions(array $options = [])
    {
//        $fn = function($key, $default = null, $source = null) use ($options) {
//            $data = $options;
//            if(null !== $source) {
//                $data = $source;
//            }
//            return array_key_exists($key, $data) ? $data[$key] : $default;
//        };

        // wczytujemy dane do wyświetlenia (jednym zapytaniem)
        $query = Doctrine_Core::getTable('Inquiry')->createQuery('i')
            ->select('i.*')
        ;

        $whereSearch = [];
        $searchParams = [];

        $search = self::getOption('search', $options);

        // baseUser (oferty danej agencji)
        $idBaseUser = self::getOption('idBaseUser', $options, null, $search);
        if ($idBaseUser) {
            $searchParams['p_id_base_user'] = $idBaseUser;
            $whereSearch[] = 'i.id_base_user = :p_id_base_user';
        }

        //zgłoszenia dla danego wystawcy
        $standsArray = self::getOption('standsArray', $options, null, $search);
        if ($standsArray) {
            // $searchParams['standsArray'] = implode($standsArray);
            $whereSearch[] = 'i.id_exhib_stand IN (' . implode(',', $standsArray) . ')';
        }

        // zakres czasu
        $dateFrom = self::getOption('dateFrom', $options, null, $search);
        $dateTo = self::getOption('dateTo', $options, null, $search);

        if ($dateFrom) {
            $searchParams['date_from'] = $dateFrom;
            $whereSearch[] = 'i.create_at >= :date_from';
        }
        if ($dateTo) {
            $searchParams['date_to'] = $dateTo;
            $whereSearch[] = 'i.create_at <= :date_to';
        }

        $channel = self::getOption('channel', $options, null, $search);
        if ($channel) {
            $searchParams['r_channel'] = $channel;
            $whereSearch[] = '(i.channel = :r_channel)';
        }

        $subject = self::getOption('subject', $options, null, $search);
        if ($subject) {
            $searchParams['r_subject'] = '%' . $subject . '%';
            $whereSearch[] = '(i.subject LIKE :r_subject)';
        }

        // Wybrane identyfikatory
        $idPk = self::getOption('idPk', $options);
        if ($idPk && !empty($idPk) && is_array($idPk)) {
            foreach ($idPk as $key => $val) {
                $idPk[$key] = (int) $val;
            }
            $stringIN = implode(',', $idPk);
            $whereSearch[] = "(i.id_inquiry IN({$stringIN}))";
        }
        if (count($whereSearch) > 0) {
            $query->andWhere(implode(' AND ', $whereSearch), $searchParams);
        }

        // sortowanie
        $orderBy = 'i.id_inquiry DESC';
        $order = self::getOption('order', $options);
        if (is_array($order) && count($order) && isset($order['sort'])) {
            $direction = isset($order['direction']) && 'DESC' === $order['direction'] ? 'DESC' : 'ASC';
            // ograniczenie do pól na sztywno
            if ($order['sort'] === 'i.create_at') {
                $orderBy = "ISNULL({$order['sort']}), {$order['sort']} {$direction}";
            }
        }
        $query->orderBy($orderBy);
        $data = self::getOption('paginator', $options) ? new Engine_Paginator_Adapter_Doctrine($query) : $query;

        return $data;
    }

    private static function getOption($key, $options = [], $default = null, $source = null)
    {
        $data = $options;
        if (null !== $source) {
            $data = $source;
        }

        return $data[$key] ?? $default;
    }
}
