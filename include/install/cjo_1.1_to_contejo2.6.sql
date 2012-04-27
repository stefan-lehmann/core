## CONTEJO Database Dump Version 2.6 [.0]
## Prefix cjo_

ALTER TABLE cjo_article_type ADD createuser VARCHAR( 255 ) NOT NULL ;
ALTER TABLE cjo_article_type ADD updateuser VARCHAR( 255 ) NOT NULL ;
ALTER TABLE cjo_article_type ADD createdate int(11) NOT NULL DEFAULT '0';
ALTER TABLE cjo_article_type ADD updatedate int(11) NOT NULL DEFAULT '0';

ALTER TABLE cjo_article MODIFY COLUMN clang INT AFTER re_id;
ALTER TABLE cjo_article MODIFY COLUMN createdate INT AFTER updateuser;
ALTER TABLE cjo_article MODIFY COLUMN updatedate INT AFTER createdate;
ALTER TABLE cjo_article MODIFY COLUMN template_id INT AFTER name;
ALTER TABLE cjo_article CHANGE autor author VARCHAR( 255 ) NOT NULL;

ALTER TABLE cjo_article_slice MODIFY COLUMN article_id INT AFTER re_article_slice_id;
ALTER TABLE cjo_article_slice MODIFY COLUMN modultyp_id INT AFTER article_id;
ALTER TABLE cjo_article_slice MODIFY COLUMN ctype INT AFTER article_id;
ALTER TABLE cjo_article_slice MODIFY COLUMN clang INT AFTER ctype;
ALTER TABLE cjo_article_slice MODIFY COLUMN createdate INT AFTER updateuser;
ALTER TABLE cjo_article_slice MODIFY COLUMN updatedate INT AFTER createdate;

UPDATE cjo_article_slice SET value8='prior' WHERE  value8 LIKE '%prior, catprior%';

ALTER TABLE cjo_file_category DROP hide ;
ALTER TABLE cjo_file_category ADD comment VARCHAR( 255 ) NOT NULL AFTER name ;
ALTER TABLE cjo_file_category MODIFY COLUMN createdate INT AFTER updateuser;
ALTER TABLE cjo_file_category MODIFY COLUMN updatedate INT AFTER createdate;

ALTER TABLE cjo_user MODIFY COLUMN createuser INT AFTER session_id;
ALTER TABLE cjo_user MODIFY COLUMN updateuser INT AFTER createuser;
ALTER TABLE cjo_user MODIFY COLUMN createdate INT AFTER updateuser;
ALTER TABLE cjo_user MODIFY COLUMN updatedate INT AFTER createdate;

ALTER TABLE cjo_user CHANGE createuser createuser VARCHAR( 50 ) NULL ;
ALTER TABLE cjo_user CHANGE updateuser updateuser VARCHAR( 50 ) NULL ;
ALTER TABLE cjo_user CHANGE createdate createdate int(11) NOT NULL DEFAULT '0';
ALTER TABLE cjo_user CHANGE updatedate updatedate int(11) NOT NULL DEFAULT '0';

ALTER TABLE cjo_template CHANGE date ctypes VARCHAR( 255 ) NOT NULL DEFAULT '0';
UPDATE cjo_template set ctypes = '|0|' WHERE active = 1;


ALTER TABLE cjo_article DROP catname, DROP cattype, DROP catprior, DROP fe_user, DROP fe_group, DROP fe_ext;

ALTER TABLE cjo_article CHANGE alias redirect VARCHAR( 255 ) NOT NULL;
ALTER TABLE cjo_article ADD locked tinyint(1) NOT NULL DEFAULT 0 AFTER status; 
ALTER TABLE cjo_article CHANGE locked admin_only tinyint( 1 ) NOT NULL  

UPDATE `cjo_opf_lang`  SET replacename = CONCAT ('[translate: ', TRIM(BOTH '###' FROM replacename), ']') 