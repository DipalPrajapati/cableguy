CREATE TABLE `customers` (
  `customers_id` int(11) NOT NULL,
  `sr_no` text NOT NULL,
  `subscriber_name` text NOT NULL,
  `subscriber_code` text NOT NULL,
  `address` text NOT NULL,
  `phone_number` text NOT NULL,
  `setTopBoxNumber` text NOT NULL,
  `smartCardNumber` text NOT NULL,
  `blackListStatus` text NOT NULL,
  `subscriberStatus` text NOT NULL,
  `stbNumber` text NOT NULL,
  `subsid` text NOT NULL,
  `stbid` text NOT NULL,
  `balance_amt` text NOT NULL,
  `setTopBoxStatus` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='latin1_swedish_ci';
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customers_id`);
ALTER TABLE `customers`
  MODIFY `customers_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;