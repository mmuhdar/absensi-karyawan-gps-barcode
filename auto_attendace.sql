DELETE FROM attendances
WHERE user_id = '01jgmzqw2dfqbda8f46dgehssh'
  AND date BETWEEN '2025-07-01' AND '2025-07-31';

DELETE FROM attendances
WHERE user_id = '01jgmzqw2dfqbda8f46dgehssh'
  AND date > '2025-08-31';

SELECT *
FROM attendances
WHERE user_id = '01jcsw880ez6dyjqncy2x5gwnm'
  AND date BETWEEN '2025-07-01' AND '2025-07-31';

DELIMITER $$

CREATE PROCEDURE generate_attendance(
    IN p_user_id VARCHAR(255),
    IN p_year INT,
    IN p_month INT
)
BEGIN
    DECLARE v_date DATE;
    DECLARE v_last_date DATE;
    DECLARE v_day_of_week INT;

    SET v_date = STR_TO_DATE(CONCAT(p_year, '-', LPAD(p_month,2,'0'), '-01'), '%Y-%m-%d');
    SET v_last_date = LAST_DAY(v_date);

    WHILE v_date <= v_last_date DO
        SET v_day_of_week = DAYOFWEEK(v_date); -- Minggu = 1, Sabtu = 7

        IF v_day_of_week IN (1,7) THEN
            -- Weekend = holiday
            INSERT INTO attendances
            (user_id, barcode_id, date, time_in, time_out, shift_id, latitude, longitude, status, created_at, updated_at)
            VALUES
            (p_user_id, NULL, v_date, NULL, NULL, NULL, NULL, NULL, 'holiday', NOW(), NOW());
        ELSE
            -- Weekday = present
            INSERT INTO attendances
            (user_id, barcode_id, date, time_in, time_out, shift_id, latitude, longitude, status, created_at, updated_at)
            VALUES
            (p_user_id, 1, v_date, '08:00', '16:03', 2, 1.0359694066817, 120.82274763347, 'present', NOW(), NOW());
        END IF;

        SET v_date = DATE_ADD(v_date, INTERVAL 1 DAY);
    END WHILE;
END$$

DELIMITER ;

CALL generate_attendance('01jcsw880ez6dyjqncy2x5gwnm', 2025, 7);

SHOW PROCEDURE STATUS WHERE Db = 'u171981792_absensi';
