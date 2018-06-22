
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
