<?php

/**
 * GamificationUserHistoryPoints.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class GamificationUserHistoryPoints extends Table_GamificationUserHistoryPoints
{
    public static function usersHistoryPoints($id_event)
    {
        // połączenie
        $conn = Doctrine_Manager::connection();

        // kasowanie całego rankingu dotyczącego aktualnego wydarzenia
        $conn->prepare('DELETE FROM gamification_user_history_points WHERE  id_event=' . $id_event)->execute();

        // kopiowanie obecnego pusktów i rankingu
        $query = '
        INSERT INTO gamification_user_history_points
        SELECT id_user, id_event, points, @rownum := @rownum + 1 AS rank
        FROM gamification_user_points gup, (SELECT @rownum := 0) r
        WHERE gup.id_event = ' . $id_event . '
        ORDER BY gup.points DESC;
        ';

        return $conn->prepare($query)->execute();
    }

    public function getRank()
    {
        return $this->rank;
    }
}
