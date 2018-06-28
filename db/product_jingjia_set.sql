/*

Date: 2018-06-26 17:36:05
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `product_jingjia_set`
-- 竞价阶段表，需求更改后不用了
-- ----------------------------
DROP TABLE IF EXISTS `product_jingjia_set`;
CREATE TABLE `product_jingjia_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jingjia_id` int(11) NOT NULL COMMENT '竞价id',
  `start_time` datetime DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `price_l` decimal(12,2) NOT NULL COMMENT '起拍价',
  `price_step` decimal(12,2) NOT NULL COMMENT '递增价',
  `pass` varchar(10) NOT NULL DEFAULT '' COMMENT '邀请验证码，为空不验证',
  `invite_way` tinyint(2) NOT NULL DEFAULT 0 COMMENT '邀请方式，0：不邀请，全员参加，1：卖家指定，2：后台指定',
  `invitees` varchar(255) NOT NULL DEFAULT '' COMMENT '被邀请的企业id,逗号相隔，为空表示all',
  `always_next` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否跳到下一个竞价区间，为0表示当前有人出价则不会跳到下一个',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;




ALTER TABLE `product_offer` ADD COLUMN `jingjia_set_id`  int(11) NOT NULL DEFAULT 0 COMMENT '当前所处竞价阶段' AFTER `price_vip`;