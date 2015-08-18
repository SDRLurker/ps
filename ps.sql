--
-- Table structure for table `ps`
--

DROP TABLE IF EXISTS `ps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ps` (
  `id` varchar(100) NOT NULL,		-- 마이피플 아이디
  `que_num` varchar(10) DEFAULT NULL,	-- 맞출 숫자
  `start` double DEFAULT NULL,		-- 문제를 만든 시간
  `record` double DEFAULT NULL,		-- 개인 최고기록
  `grp_record` double DEFAULT NULL,	-- 그룹대화방의 최고기록
  `per_record` double DEFAULT NULL,
  `try` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ps_group`
--

DROP TABLE IF EXISTS `ps_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ps_group` (
  `id` varchar(100) NOT NULL,		-- 그룹대화방 아이디
  `que_num` varchar(10) DEFAULT NULL,	-- 맞출 숫자
  `start` double DEFAULT NULL,		-- 문제를 만든 시간
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
