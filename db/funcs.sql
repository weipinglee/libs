
-- ----------------------------
-- Procedure structure for `createDepositOrder`
-- ----------------------------
DROP PROCEDURE IF EXISTS `createDepositOrder`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `createDepositOrder`(IN `offerId` int,IN `buyer_id` int,IN `buyNum` decimal,IN `pay_times` int,IN `payDeposit` decimal,IN `payWay` tinyint)
BEGIN
	#Routine body goes here...
  DECLARE modeId INT(2);
       DECLARE orderNo VARCHAR(20);
       DECLARE totalAmt DECIMAL(15,2) ;
       DECLARE contractStatus INT(2);
       DECLARE random INT(2);
       DECLARE orderTime VARCHAR(20);
       DECLARE orderPrice DECIMAL(12,2);
       DECLARE offerUserId INT(11);
       SELECT price,mode,user_id INTO orderPrice,modeId,offerUserId FROM product_offer   WHERE id=offerId;
       SET totalAmt = orderPrice * buyNum;
         SET contractStatus=1;/*等待卖家支付保证金*/
       SET random =  FLOOR(0 + (RAND() * 99));
      SET orderNo = CONCAT(FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y%m%d%H%i%s') ,  random );
      SET orderTime = FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y-%m-%d %H:%i:%s') ;
       INSERT INTO order_sell 
       (
           offer_id,
           offer_user_id,
           mode,
           order_no,
           num,
           amount,/*总金额*/
           user_id,
           pay_deposit,/*支付订金金额*/
           buyer_deposit_payment,
           contract_status,
           invoice,
           create_time,
           price_unit
           )  
           VALUES  
           (
               offerId,
              offerUserId,
              modeId,/*生成*/
               orderNo,/*生成*/
               buyNum,/*参数*/
               totalAmt,/*生成*/
               buyer_id,/*参数*/
               payDeposit,/*参数*/
               payWay,/*参数*/
               contractStatus,/*生成*/
               1,/*默认值1*/
               orderTime,
               orderPrice
               );
END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for `createFreeOrder`
-- ----------------------------
DROP PROCEDURE IF EXISTS `createFreeOrder`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `createFreeOrder`(IN `offerId` int,IN `buyer_id` int,IN `buyNum` decimal,IN `pay_times` int,IN `payDeposit` decimal,IN `payWay` tinyint)
BEGIN
	#Routine body goes here...
  DECLARE modeId INT(2);
       DECLARE orderNo VARCHAR(20);
       DECLARE totalAmt DECIMAL(15,2) ;
       DECLARE contractStatus INT(2);
       DECLARE random INT(2);
       DECLARE orderTime VARCHAR(20);
       DECLARE orderPrice DECIMAL(12,2);
       DECLARE offerUserId INT(11);
       SELECT price,mode,user_id INTO orderPrice,modeId,offerUserId FROM product_offer   WHERE id=offerId;
       SET totalAmt = orderPrice * buyNum;
         SET contractStatus=9;/*合同等待卖家确认收款*/
       SET random =  FLOOR(0 + (RAND() * 99));
      SET orderNo = CONCAT(FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y%m%d%H%i%s') ,  random );
      SET orderTime = FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y-%m-%d %H:%i:%s') ;
       INSERT INTO order_sell 
       (
           offer_id,
           offer_user_id,
           mode,
           order_no,
           num,
           amount,/*总金额*/
           user_id,
           pay_deposit,/*支付订金金额*/
           buyer_deposit_payment,
           contract_status,
           invoice,
           create_time,
           price_unit
           )  
           VALUES  
           (
               offerId,
              offerUserId,
              modeId,/*生成*/
               orderNo,/*生成*/
               buyNum,/*参数*/
               totalAmt,/*生成*/
               buyer_id,/*参数*/
               payDeposit,/*参数*/
               payWay,/*参数*/
               contractStatus,/*生成*/
               1,/*默认值1*/
               orderTime,
               orderPrice
               );

END
;;
DELIMITER ;

-- ----------------------------
-- Procedure structure for `createStoreOrder`
-- ----------------------------
DROP PROCEDURE IF EXISTS `createStoreOrder`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `createStoreOrder`(IN `offerId` INT(11) UNSIGNED, IN `buyer_id` INT(11) UNSIGNED, IN `buyNum` DECIMAL(15,2) UNSIGNED, IN `pay_times` TINYINT(2) UNSIGNED, IN `payDeposit` DECIMAL(15,2) UNSIGNED, IN `payWay` TINYINT(2))
    NO SQL
    DETERMINISTIC
BEGIN
       DECLARE modeId INT(2);
       DECLARE orderNo VARCHAR(20);
       DECLARE totalAmt DECIMAL(15,2) ;
       DECLARE contractStatus INT(2);
       DECLARE random INT(2);
       DECLARE orderTime VARCHAR(20);
        DECLARE orderPrice DECIMAL(12,2);
       DECLARE offerUserId INT(11);
       SELECT price,mode,uer_id INTO orderPrice,modeId ,offerUserId FROM product_offer   WHERE id=offerId;
       SET totalAmt = orderPrice * buyNum;
       IF pay_times=1 THEN
         SET contractStatus=4;/*合同生效*/
       ELSE
         SET contractStatus=3;/*等待支付尾款*/
       END IF;
       SET random =  FLOOR(0 + (RAND() * 99));
      SET orderNo = CONCAT(FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y%m%d%H%i%s') ,  random );
      SET orderTime = FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y-%m-%d %H:%i:%s') ;
       INSERT INTO order_sell 
       (
           offer_id,
           offer_user_id,
           mode,
           order_no,
           num,
           amount,/*总金额*/
           user_id,
           pay_deposit,/*支付订金金额*/
           buyer_deposit_payment,
           contract_status,
           invoice,
           create_time,
           price_unit
           )  
           VALUES  
           (
               offerId,
              offerUserId,
              modeId,/*生成*/
               orderNo,/*生成*/
               buyNum,/*参数*/
               totalAmt,/*生成*/
               buyer_id,/*参数*/
               payDeposit,/*参数*/
               payWay,/*参数*/
               contractStatus,/*生成*/
               1,/*默认值1*/
               orderTime,
               orderPrice
               );
               

END
;;
DELIMITER ;


-- ----------------------------
-- Procedure structure for `xinJingjiaHandle`
-- 非由其他报盘转的竞价报盘在阶段时间结束时执行的过程
-- 不分阶段了，只有一个阶段
-- ----------------------------
DROP PROCEDURE IF EXISTS `xinJingjiaHandle`;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `xinJingjiaHandle`(IN `offer_id` int,OUT `return_status` int)
BEGIN
	#非由其他报盘转的竞价报盘在结束时间执行的过程
  DECLARE baojia_user INT(11) default 0;#当前报价的用户
  DECLARE mode_id   INT(11) ;#报盘模式
  DECLARE offer_num DECIMAL(15,2);#报盘数量
  DECLARE max_price DECIMAL(15,2);#报价最高价

  DECLARE offer_status INT(2) default 1;#报盘状态

  #获取状态为正常且id为offer_id的竞价报盘的竞价阶段id
  select `mode`,max_num INTO mode_id ,offer_num FROM product_offer where id = offer_id AND sub_mode=1 AND `status`=1;
  start TRANSACTION;

   #生成订单要重新获取最新的报价数据
   SELECT user_id ,price INTO baojia_user,max_price FROM product_jingjia WHERE `offer_id`=offer_id ORDER BY price desc LIMIT 1;

   IF baojia_user>0 THEN  #生成订单

        SET offer_status=6;
        UPDATE product_offer SET `status`=offer_status WHERE id=offer_id;
        IF mode_id=4 THEN
         CALL  createStoreOrder(offer_id,baojia_user, offer_num,1,max_price*offer_num,1);
        ELSEIF mode_id=2 THEN
          CALL  createDepositOrder(offer_id,baojia_user, offer_num,1,max_price*offer_num,1);
        ELSEIF mode_id=1 THEN
          CALL createFreeOrder(offer_id,baojia_user, offer_num,1,max_price*offer_num,1);
        END IF;
        set return_status=6;
   ELSE  #报盘改成已过期
       SET offer_status=5;
       UPDATE product_offer SET `status`=offer_status WHERE id=offer_id;
       set return_status=5;
   END IF;


  COMMIT;


END
;;
DELIMITER ;


