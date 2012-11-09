USE kloxo;

delimiter ;;

create procedure add_text_nginx_write ()
begin
    declare continue handler for 1060 begin end;
    ALTER TABLE `web` ADD `text_nginx_rewrite` LONGTEXT AFTER `text_lighty_rewrite`;
end;;

call add_text_nginx_write();;

drop procedure add_text_nginx_write;