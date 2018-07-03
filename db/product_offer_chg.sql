ALTER TABLE `product_offer`  ADD COLUMN `accept_area_code`  varchar(6) NOT NULL COMMENT '交收地址代码' ;
ALTER TABLE `product_offer`
MODIFY COLUMN `accept_day`  varchar(20) NOT NULL COMMENT '交收时间' AFTER `accept_area`;

ALTER TABLE `products`
ADD COLUMN `produce_address`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '产地地址' ;
