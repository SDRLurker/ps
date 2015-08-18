--
-- Table structure for table `ps`
--

DROP TABLE IF EXISTS `ps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ps` (
  `id` varchar(100) NOT NULL,		-- �������� ���̵�
  `que_num` varchar(10) DEFAULT NULL,	-- ���� ����
  `start` double DEFAULT NULL,		-- ������ ���� �ð�
  `record` double DEFAULT NULL,		-- ���� �ְ���
  `grp_record` double DEFAULT NULL,	-- �׷��ȭ���� �ְ���
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
  `id` varchar(100) NOT NULL,		-- �׷��ȭ�� ���̵�
  `que_num` varchar(10) DEFAULT NULL,	-- ���� ����
  `start` double DEFAULT NULL,		-- ������ ���� �ð�
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
