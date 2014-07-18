#!/bin/bash
# 
# Quick and dirty dump of users from db.
#
# Usage: ./dump_users2csv.sh > users.csv
#
####################################################################

mysql -upingwin -pgranite -hdbs3.dx30.net gotyouin -e \
'SELECT mail as email, f.field_first_name_value as First, l.field_last_name_value as Last, from_unixtime( created ) as Created, r.name as Role
FROM users u, field_data_field_first_name f, field_data_field_last_name l, users_roles rs, role r
WHERE u.uid = f.entity_id AND u.uid = l.entity_id AND u.status =1 AND rs.uid = u.uid AND r.rid = rs.rid
ORDER by l.field_last_name_value, f.field_first_name_value'
