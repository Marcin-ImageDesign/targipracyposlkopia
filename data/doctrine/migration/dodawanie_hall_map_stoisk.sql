-- HALA DOMYŚLNA ŚREDNIA	- taka jak na edukacyjnych
-- UWAGA podać odpowiednią nazwę, id_event, i uri!!
INSERT INTO `event_hall_map` (`hash`, `name`, `id_image`, `height`, `width`, `id_event`, `uri`)
VALUES (MD5( UNIX_TIMESTAMP( ) ), 'ICT default', 2066, 1259, 1821, 37, 'hall_l1' );
SET @last_id_in_event_hall_map = LAST_INSERT_ID();

-- stoiska przepisujemy dla id_event_hall_map = 2 (targi edukacyjne)
-- w przypadku wyboru innej hali uwzględnić to zarówno w sql poniżej jak i powyżej
INSERT INTO `event_stand_number` (`id_event_hall_map`, `id_stand_level`, `hash`, 
									`is_active`, `number`, `name`, `logo_pos_x`, `logo_pos_y`)
(SELECT @last_id_in_event_hall_map, `id_stand_level`, MD5(rand(now())), `is_active`, `number`, `name`, 
	`logo_pos_x`, `logo_pos_y` FROM `event_stand_number` WHERE `id_event_hall_map` = 2)