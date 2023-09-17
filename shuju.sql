-- MySQL dump 10.13  Distrib 5.7.39, for Linux (x86_64)
--
-- Host: localhost    Database: mh
-- ------------------------------------------------------
-- Server version	5.7.39-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `sy_activity`
--

DROP TABLE IF EXISTS `sy_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(200) DEFAULT NULL COMMENT '活动名称',
  `cover` varchar(255) DEFAULT NULL COMMENT '推荐封面',
  `bg` varchar(255) DEFAULT NULL COMMENT '活动背景',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '充值金额',
  `send_money` int(8) DEFAULT '0' COMMENT '赠送书币',
  `start_time` int(11) unsigned DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) unsigned DEFAULT '0' COMMENT '结束时间',
  `is_first` tinyint(1) unsigned DEFAULT '1' COMMENT '是否是只能充值1次，1是2否',
  `status` tinyint(1) DEFAULT '1' COMMENT '1正常，2禁用',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='促销活动信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_activity`
--

LOCK TABLES `sy_activity` WRITE;
/*!40000 ALTER TABLE `sy_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_ad`
--

DROP TABLE IF EXISTS `sy_ad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_ad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `sort` int(5) NOT NULL DEFAULT '99' COMMENT '排序',
  `name` varchar(10) NOT NULL DEFAULT '' COMMENT '广告位置名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=显示2=不显示',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_ad`
--

LOCK TABLES `sy_ad` WRITE;
/*!40000 ALTER TABLE `sy_ad` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_ad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_ad_cate`
--

DROP TABLE IF EXISTS `sy_ad_cate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_ad_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(200) NOT NULL COMMENT '广告链接',
  `img` varchar(200) NOT NULL COMMENT '广告图片',
  `time` int(11) NOT NULL COMMENT '添加时间',
  `sort` int(5) NOT NULL DEFAULT '99' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=显示2=不显示',
  `cid` int(11) NOT NULL COMMENT '关联广告位置id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_ad_cate`
--

LOCK TABLES `sy_ad_cate` WRITE;
/*!40000 ALTER TABLE `sy_ad_cate` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_ad_cate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_book`
--

DROP TABLE IF EXISTS `sy_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_book` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1漫画，2小说，3听书',
  `name` varchar(200) DEFAULT NULL COMMENT '书籍名称',
  `author` varchar(50) DEFAULT NULL COMMENT '作者',
  `lead` varchar(255) DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL COMMENT '封面图片',
  `detail_img` varchar(255) DEFAULT NULL COMMENT '详情页图片',
  `summary` varchar(500) DEFAULT NULL COMMENT '小说简介',
  `sort_num` int(8) unsigned DEFAULT '0' COMMENT '排序权值',
  `area` varchar(255) DEFAULT NULL COMMENT '发布区域',
  `category` varchar(255) DEFAULT NULL COMMENT '小说分类',
  `free_type` tinyint(1) unsigned DEFAULT '2' COMMENT '1免费2收费',
  `new_type` tinyint(1) unsigned DEFAULT '1' COMMENT '1新书2非新书',
  `long_type` tinyint(1) unsigned DEFAULT '1' COMMENT '1长篇2短篇',
  `gender_type` tinyint(1) unsigned DEFAULT '1' COMMENT '1男频2女频',
  `over_type` tinyint(1) unsigned DEFAULT '1' COMMENT '1连载中2已完结',
  `is_hot` tinyint(1) DEFAULT NULL COMMENT '是否热门推荐，1是2否',
  `free_chapter` smallint(5) unsigned DEFAULT '0' COMMENT '免费章节',
  `money` int(8) unsigned DEFAULT '0' COMMENT '每章节金额',
  `hot_num` int(10) unsigned DEFAULT '0' COMMENT '人气值',
  `share_title` varchar(100) DEFAULT NULL COMMENT '分享标题',
  `share_desc` varchar(500) DEFAULT NULL COMMENT '分享简介',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1上架，2下架',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `area` (`area`) USING BTREE,
  KEY `category` (`category`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='书籍信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_book`
--

LOCK TABLES `sy_book` WRITE;
/*!40000 ALTER TABLE `sy_book` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_book_chapter`
--

DROP TABLE IF EXISTS `sy_book_chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_book_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '分集标题',
  `book_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属书籍',
  `number` int(10) unsigned DEFAULT '0' COMMENT '阅读人数',
  `src` varchar(255) DEFAULT NULL COMMENT '漫画封面&听书地址',
  `read_num` int(10) unsigned DEFAULT '0' COMMENT '阅读人数',
  `files` json DEFAULT NULL COMMENT '七牛云文件路径',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '录入时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `book_id` (`book_id`) USING BTREE,
  KEY `number` (`number`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='书籍章节表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_book_chapter`
--

LOCK TABLES `sy_book_chapter` WRITE;
/*!40000 ALTER TABLE `sy_book_chapter` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_book_chapter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_book_share`
--

DROP TABLE IF EXISTS `sy_book_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_book_share` (
  `book_id` int(11) unsigned NOT NULL COMMENT '书籍ID',
  `title` varchar(100) DEFAULT NULL COMMENT '分享标题',
  `content` varchar(500) DEFAULT NULL COMMENT '分享话术',
  PRIMARY KEY (`book_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='书籍分享话术';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_book_share`
--

LOCK TABLES `sy_book_share` WRITE;
/*!40000 ALTER TABLE `sy_book_share` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_book_share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_book_sync`
--

DROP TABLE IF EXISTS `sy_book_sync`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_book_sync` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `sync_id` int(10) DEFAULT NULL COMMENT '对方书籍主键',
  `book_id` int(10) DEFAULT NULL COMMENT '我方书籍主键',
  `ip` varchar(30) DEFAULT NULL COMMENT '主机ip',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `sync_id` (`sync_id`) USING BTREE,
  UNIQUE KEY `book_id` (`book_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='书籍同步表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_book_sync`
--

LOCK TABLES `sy_book_sync` WRITE;
/*!40000 ALTER TABLE `sy_book_sync` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_book_sync` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_channel`
--

DROP TABLE IF EXISTS `sy_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_channel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '账户余额',
  `name` varchar(100) DEFAULT NULL COMMENT '渠道名称',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1渠道2代理',
  `is_wx` tinyint(1) unsigned DEFAULT '1' COMMENT '是否有公众号1是2否',
  `parent_id` int(10) unsigned DEFAULT '0' COMMENT '代理所属渠道,为0时所属总站',
  `login_name` varchar(20) DEFAULT NULL COMMENT '登录账号',
  `password` varchar(32) DEFAULT NULL COMMENT '登录密码',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '0待审核,1正常,2禁用,3审核不通过，',
  `url` varchar(255) DEFAULT NULL COMMENT '绑定域名',
  `is_location` tinyint(1) unsigned DEFAULT '2' COMMENT '是否开启域名跳转1是2否',
  `location_url` varchar(255) DEFAULT NULL COMMENT '跳转域名',
  `appid` varchar(50) DEFAULT NULL COMMENT 'appid',
  `appsecret` varchar(64) DEFAULT NULL COMMENT '公众号secret',
  `apptoken` varchar(32) DEFAULT NULL COMMENT '公众号token',
  `qrcode` varchar(255) DEFAULT NULL COMMENT '公众号二维码',
  `deduct_min` smallint(4) unsigned DEFAULT '0' COMMENT '距扣量',
  `deduct_num` smallint(4) unsigned DEFAULT '0' COMMENT '每多少单扣一单',
  `wefare_days` int(10) unsigned DEFAULT '0' COMMENT '代理福利时长',
  `pay_type` tinyint(1) unsigned DEFAULT '1' COMMENT '1官方微信，2个人微信，3支付宝',
  `ratio` tinyint(3) unsigned DEFAULT '0' COMMENT '返额比例',
  `bank_user` varchar(50) DEFAULT NULL COMMENT '开户人',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '开户银行',
  `bank_no` varchar(20) DEFAULT NULL COMMENT '银行卡号',
  `total_charge` decimal(18,2) unsigned DEFAULT '0.00' COMMENT '总充值',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `url` (`url`) USING BTREE,
  KEY `location_url` (`location_url`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='渠道代理主表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_channel`
--

LOCK TABLES `sy_channel` WRITE;
/*!40000 ALTER TABLE `sy_channel` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_channel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_comments`
--

DROP TABLE IF EXISTS `sy_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1漫画2小说3听书4视频',
  `pid` int(10) DEFAULT NULL COMMENT '对象id',
  `pname` varchar(200) DEFAULT NULL COMMENT '对象名称',
  `content` varchar(255) DEFAULT NULL COMMENT '评论内容',
  `status` tinyint(1) unsigned DEFAULT '2' COMMENT '1显示，2隐藏,3删除',
  `create_time` int(11) unsigned NOT NULL COMMENT '评论时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='评论表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_comments`
--

LOCK TABLES `sy_comments` WRITE;
/*!40000 ALTER TABLE `sy_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_complaint`
--

DROP TABLE IF EXISTS `sy_complaint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_complaint` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '用户所属渠道',
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '用户所属代理',
  `book_id` tinyint(1) unsigned DEFAULT NULL COMMENT '1漫画，2小说，3听书，4视频',
  `book_name` varchar(200) DEFAULT NULL COMMENT '投诉对象名称',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1色情，2血腥，3暴力，4违法，5盗版，6其他',
  `remark` varchar(255) DEFAULT NULL COMMENT '其他投诉',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '投诉时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `agent_id` (`agent_id`) USING BTREE,
  KEY `book_id` (`book_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户投诉表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_complaint`
--

LOCK TABLES `sy_complaint` WRITE;
/*!40000 ALTER TABLE `sy_complaint` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_complaint` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_config`
--

DROP TABLE IF EXISTS `sy_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_config` (
  `key` varchar(20) NOT NULL COMMENT '配置项',
  `value` json DEFAULT NULL COMMENT '值',
  PRIMARY KEY (`key`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配置信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_config`
--

LOCK TABLES `sy_config` WRITE;
/*!40000 ALTER TABLE `sy_config` DISABLE KEYS */;
INSERT INTO `sy_config` VALUES ('web_block','[{\"key\": \"cartoon\", \"url\": \"/index/Book/cartoon.html\", \"name\": \"漫画\", \"is_on\": 1}, {\"key\": \"novel\", \"url\": \"/index/Book/novel.html\", \"name\": \"小说\", \"is_on\": 1}, {\"key\": \"music\", \"url\": \"/index/Book/music.html\", \"name\": \"听书\", \"is_on\": 1}, {\"key\": \"video\", \"url\": \"/index/Video/index.html\", \"name\": \"视频\", \"is_on\": 1}, {\"key\": \"shop\", \"url\": \"/index/Product/index.html\", \"name\": \"商城\", \"is_on\": 1}]'),('website','{\"url\": \"103.39.210.31\", \"name\": \"鲨鱼漫画小说系统\", \"is_sign\": \"1\", \"pay_type\": \"5\", \"contactQQ\": \"997768636\", \"contactWx\": \"997768636\", \"contactTel\": \"997768636\", \"is_location\": \"2\", \"share_money\": \"\", \"sign_config\": {\"day1\": \"10\", \"day2\": \"20\", \"day3\": \"30\", \"day4\": \"40\", \"day5\": \"50\", \"day6\": \"60\", \"day7\": \"70\"}, \"location_url\": \"\"}'),('mihuaPay','{\"merNo\": \"\", \"publicKey\": \"\", \"merAccount\": \"\", \"privateKey\": \"\"}'),('wxpay','{\"APPID\": \"\", \"MCHID\": \"\", \"APIKEY\": \"\"}'),('milabaoPay','{\"key\": \"\", \"appid\": \"\", \"gateway\": \"\"}'),('message','{\"sign\": \"鲨鱼阅读\", \"appid\": \"\", \"appkey\": \"\", \"content\": \"您的验证码为CODE，请在五分钟内使用\"}'),('charge','[{\"coin\": 100, \"is_on\": \"1\", \"money\": \"1\", \"is_hot\": \"1\", \"reward\": \"100\", \"package\": \"0\", \"is_checked\": \"1\"}, {\"coin\": 5000, \"is_on\": \"1\", \"money\": \"49\", \"is_hot\": \"0\", \"reward\": \"2000\", \"package\": \"0\", \"is_checked\": \"2\"}, {\"coin\": 10000, \"is_on\": \"1\", \"money\": \"99\", \"is_hot\": \"0\", \"reward\": \"5000\", \"package\": \"0\", \"is_checked\": \"2\"}, {\"coin\": 0, \"is_on\": \"1\", \"money\": \"30\", \"is_hot\": \"0\", \"reward\": \"0\", \"package\": \"2\", \"is_checked\": \"0\"}, {\"coin\": 0, \"is_on\": \"1\", \"money\": \"89\", \"is_hot\": \"0\", \"reward\": \"0\", \"package\": \"3\", \"is_checked\": \"0\"}, {\"coin\": 0, \"is_on\": \"1\", \"money\": \"159\", \"is_hot\": \"1\", \"reward\": \"0\", \"package\": \"5\", \"is_checked\": \"0\"}, {\"coin\": 0, \"is_on\": \"1\", \"money\": \"299\", \"is_hot\": \"0\", \"reward\": \"0\", \"package\": \"6\", \"is_checked\": \"0\"}]'),('web_footer','[{\"src\": \"/uploads/icon/20221123/25220_203808_552079.png\", \"link\": \"/index\", \"text\": \"首页\"}, {\"src\": \"/uploads/icon/20221123/25220_203821_499020.png\", \"link\": \"/index/User/myHistory.html\", \"text\": \"书架\"}, {\"src\": \"/uploads/icon/20221123/25220_203824_668368.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}, {\"src\": \"/uploads/icon/20221123/25220_203828_900074.png\", \"link\": \"/index/center/index.html\", \"text\": \"我的\"}]'),('product_category','[\"热门推荐\"]'),('weixin','{\"appid\": \"\", \"qrcode\": \"\", \"apptoken\": \"\", \"appsecret\": \"\"}'),('alioss','{\"url\": \"\", \"type\": \"1\", \"bucket\": \"\", \"accessKey\": \"\", \"secretKey\": \"\"}'),('cartoon_nav','[{\"src\": \"/uploads/icon/20221123/25220_203936_223377.png\", \"link\": \"/index/Book/category.html?type=1\", \"text\": \"分类\"}, {\"src\": \"/uploads/icon/20221123/25220_203941_935892.png\", \"link\": \"/index/Book/rank.html?type=1\", \"text\": \"排行\"}, {\"src\": \"/uploads/icon/20221123/25220_204029_576485.png\", \"link\": \"/index/Book/more.html?type=1&area=新书上架\", \"text\": \"新作\"}, {\"src\": \"/uploads/icon/20221123/25220_204033_363660.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}]'),('novel_area','[\"男频精选\", \"女频精选\"]'),('reward','[\"1\", \"2\", \"3\", \"4\", \"5\", \"6\", \"7\", \"8\", \"9\", \"10\"]'),('cartoon_area','[\"精选漫画\", \"人气漫画\", \"新品上架\"]'),('cartoon_category','{\"1\": \"霸总\", \"2\": \"仙侠 \", \"3\": \"恋爱\", \"4\": \"校园\", \"5\": \"冒险\", \"6\": \"搞笑  \", \"7\": \"生活\", \"8\": \"热血 \", \"9\": \"架空\", \"10\": \"后宫\", \"11\": \"耽美  \", \"12\": \"玄幻 \", \"13\": \"悬疑\", \"14\": \"恐怖\", \"15\": \"灵异\", \"16\": \"动作 \", \"17\": \"科幻 \", \"18\": \"战争 \", \"19\": \"古风  \", \"20\": \"穿越\", \"21\": \"竞技\", \"22\": \"百合  \", \"23\": \"励志   \", \"24\": \"同人 \", \"25\": \"真人\"}'),('cartoon_footer','[{\"src\": \"/uploads/icon/20221123/25220_203808_552079.png\", \"link\": \"/index/Book/cartoon.html\", \"text\": \"首页\"}, {\"src\": \"/uploads/icon/20221123/25220_203821_499020.png\", \"link\": \"/index/User/myHistory.html\", \"text\": \"书架\"}, {\"src\": \"/uploads/icon/20221123/25220_203824_668368.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}, {\"src\": \"/uploads/icon/20221123/25220_203828_900074.png\", \"link\": \"/index/center/index.html\", \"text\": \"我的\"}]'),('novel_nav','[{\"src\": \"/uploads/icon/20221123/25220_203936_223377.png\", \"link\": \"/index/Book/category.html?type=2\", \"text\": \"分类\"}, {\"src\": \"/uploads/icon/20221123/25220_203941_935892.png\", \"link\": \"/index/Book/rank.html?type=2\", \"text\": \"排行\"}, {\"src\": \"/uploads/icon/20221123/25220_204029_576485.png\", \"link\": \"/index/Book/more.html?type=2&area=新书上架\", \"text\": \"新作\"}, {\"src\": \"/uploads/icon/20221123/25220_204033_363660.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}]'),('music_nav','[{\"src\": \"/uploads/icon/20221123/25220_203936_223377.png\", \"link\": \"/index/Book/category.html?type=3\", \"text\": \"分类\"}, {\"src\": \"/uploads/icon/20221123/25220_203941_935892.png\", \"link\": \"/index/Book/rank.html?type=3\", \"text\": \"排行\"}, {\"src\": \"/uploads/icon/20221123/25220_204029_576485.png\", \"link\": \"/index/Book/more.html?type=3&area=新书上架\", \"text\": \"新作\"}, {\"src\": \"/uploads/icon/20221123/25220_204033_363660.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}]'),('video_nav','[{\"link\": \"/index/Video/category.html?free_type=1\", \"text\": \"免费播放\"}, {\"link\": \"/index/Video/more.html?is_hot=1\", \"text\": \"热门推荐\"}, {\"link\": \"/index/Video/category.html\", \"text\": \"影视分类\"}]'),('video_footer','[{\"src\": \"/uploads/icon/20221123/25220_203808_552079.png\", \"link\": \"/index/Video/index.html\", \"text\": \"首页\"}, {\"src\": \"/uploads/icon/20221123/25220_203821_499020.png\", \"link\": \"/index/User/myHistory.html\", \"text\": \"书架\"}, {\"src\": \"/uploads/icon/20221123/25220_203824_668368.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}, {\"src\": \"/uploads/icon/20221123/25220_203828_900074.png\", \"link\": \"/index/center/index.html\", \"text\": \"我的\"}]'),('novel_footer','[{\"src\": \"/uploads/icon/20221123/25220_203808_552079.png\", \"link\": \"/index/Video/index.html\", \"text\": \"首页\"}, {\"src\": \"/uploads/icon/20221123/25220_203821_499020.png\", \"link\": \"/index/User/myHistory.html\", \"text\": \"书架\"}, {\"src\": \"/uploads/icon/20221123/25220_203824_668368.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}, {\"src\": \"/uploads/icon/20221123/25220_203828_900074.png\", \"link\": \"/index/center/index.html\", \"text\": \"我的\"}]'),('music_area','[\"男频\", \"女频\"]'),('music_category','[\"玄幻\", \"青春\"]'),('video_area','{\"0\": \"大陆\", \"2\": \"欧美\", \"3\": \"日韩\"}'),('novel_banner','[{\"src\": \"/img/20220926/25220_141853_352672.png\", \"link\": \"\"}]'),('cartoon_banner','[{\"src\": \"/uploads/img/20220926/25220_143857_446806.png\", \"link\": \"\"}]'),('paycat','{\"uid\": \"\", \"token\": \"\", \"gateway\": \"\"}'),('epay','{\"epayid\": \"\", \"epaykey\": \"\", \"epayurl\": \"\"}'),('video_banner','[{\"src\": \"/uploads/img/20220926/25220_150003_850359.png\", \"link\": \"\"}]'),('music_footer','[{\"src\": \"/uploads/icon/20221123/25220_203808_552079.png\", \"link\": \"/index/Book/music.html\", \"text\": \"首页\"}, {\"src\": \"/uploads/icon/20221123/25220_203821_499020.png\", \"link\": \"/index/User/myHistory.html\", \"text\": \"书架\"}, {\"src\": \"/uploads/icon/20221123/25220_203824_668368.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}, {\"src\": \"/uploads/icon/20221123/25220_203828_900074.png\", \"link\": \"/index/center/index.html\", \"text\": \"我的\"}]'),('novel_category','[\"玄幻\", \"武侠\", \"军事\", \"网游\", \"科幻\", \"恐怖\", \"都市\", \"现言\", \"古言\", \"总裁\", \"宫斗\", \"校园\", \"穿越\", \"架空\"]'),('video_category','{\"0\": \"动画片\", \"1\": \"武侠片\", \"3\": \"科幻片\"}'),('music_banner','[{\"src\": \"/uploads/img/20220926/25220_144500_673834.png\", \"link\": \"\"}]'),('product_footer','[{\"src\": \"/uploads/icon/20221123/25220_203808_552079.png\", \"link\": \"/index/Product/index.html\", \"text\": \"首页\"}, {\"src\": \"/uploads/icon/20221123/25220_210113_361126.png\", \"link\": \"/index/User/myHistory.html\", \"text\": \"订单\"}, {\"src\": \"/uploads/icon/20221123/25220_203824_668368.png\", \"link\": \"/index/charge/index.html\", \"text\": \"充值\"}, {\"src\": \"/uploads/icon/20221123/25220_203828_900074.png\", \"link\": \"/index/center/index.html\", \"text\": \"我的\"}]'),('product_nav','[]'),('product_banner','[{\"src\": \"/uploads/img/20220926/25220_152310_798940.png\", \"link\": \"\"}]'),('product_area','[\"服饰\"]');
/*!40000 ALTER TABLE `sy_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_feedback`
--

DROP TABLE IF EXISTS `sy_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '用户所属渠道',
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '用户所属代理',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `phone` char(11) DEFAULT NULL COMMENT '手机号',
  `content` varchar(500) DEFAULT NULL COMMENT '反馈内容',
  `reply` varchar(500) DEFAULT NULL COMMENT '回复内容',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '反馈时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `agent_id` (`agent_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户反馈表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_feedback`
--

LOCK TABLES `sy_feedback` WRITE;
/*!40000 ALTER TABLE `sy_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_login_log`
--

DROP TABLE IF EXISTS `sy_login_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_login_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `login_name` varchar(20) DEFAULT NULL COMMENT '登录账号',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1总站,2渠道,3代理',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1登录成功，2登录异常',
  `remark` varchar(100) DEFAULT NULL COMMENT '备注',
  `login_ip` varchar(255) DEFAULT NULL COMMENT '登录ip',
  `login_time` int(11) unsigned DEFAULT '0' COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `login_name` (`login_name`) USING BTREE,
  KEY `login_time` (`login_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台登录日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_login_log`
--

LOCK TABLES `sy_login_log` WRITE;
/*!40000 ALTER TABLE `sy_login_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_login_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_manage`
--

DROP TABLE IF EXISTS `sy_manage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_manage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `role_id` int(10) unsigned DEFAULT '0' COMMENT '角色id',
  `name` varchar(20) DEFAULT NULL COMMENT '用户名称',
  `login_name` varchar(20) DEFAULT NULL COMMENT '登录账户',
  `password` char(32) DEFAULT NULL COMMENT '登录密码',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态：1启用，2禁用，3删除',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '账号创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `login_name` (`login_name`) USING BTREE,
  KEY `password` (`password`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_manage`
--

LOCK TABLES `sy_manage` WRITE;
/*!40000 ALTER TABLE `sy_manage` DISABLE KEYS */;
INSERT INTO `sy_manage` VALUES (1,0,'admin','admin','bdc9b023eae81642d3707cbcd8149f7c',1,0);
/*!40000 ALTER TABLE `sy_manage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_material`
--

DROP TABLE IF EXISTS `sy_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(200) DEFAULT NULL COMMENT '文案标题',
  `cover` varchar(255) DEFAULT NULL COMMENT '文案图片',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文案素材';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_material`
--

LOCK TABLES `sy_material` WRITE;
/*!40000 ALTER TABLE `sy_material` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_material` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_member`
--

DROP TABLE IF EXISTS `sy_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `wx_id` int(10) unsigned DEFAULT '0' COMMENT '所属微信id',
  `password` varchar(50) CHARACTER SET ucs2 DEFAULT NULL,
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '用户所属渠道',
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '用户所属代理ID',
  `money` int(10) DEFAULT '0' COMMENT '用户书币余额',
  `total_money` int(11) unsigned DEFAULT '0' COMMENT '累计获得书币',
  `openid` varchar(32) DEFAULT NULL COMMENT 'openid',
  `phone` varchar(11) DEFAULT NULL COMMENT '用户手机号',
  `nickname` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '用户昵称',
  `sex` tinyint(1) unsigned DEFAULT '0' COMMENT '0未知，1男,2女',
  `city` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '用户所在城市',
  `province` varchar(100) DEFAULT NULL COMMENT '用户所在省份',
  `country` varchar(100) DEFAULT NULL COMMENT '用户所在国家',
  `headimgurl` varchar(255) DEFAULT NULL COMMENT '用户头像地址',
  `subscribe` tinyint(1) unsigned DEFAULT '0' COMMENT '是否关注,1已关注0未关注',
  `subscribe_time` int(11) unsigned DEFAULT '0' COMMENT '用户关注时间',
  `viptime` int(11) unsigned DEFAULT '0' COMMENT 'vip到期时间',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1启用,2禁用',
  `spread_id` int(10) unsigned DEFAULT '0' COMMENT '推广ID',
  `is_charge` tinyint(1) unsigned DEFAULT '2' COMMENT '是否充值，1是2否',
  `is_auto` tinyint(1) unsigned DEFAULT '1' COMMENT '是否自动扣减书币1是2否',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户创建时间',
  `username` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `openid` (`openid`) USING BTREE,
  UNIQUE KEY `phone` (`phone`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `spread_id` (`spread_id`) USING BTREE,
  KEY `viptime` (`viptime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='粉丝用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_member`
--

LOCK TABLES `sy_member` WRITE;
/*!40000 ALTER TABLE `sy_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_member_collect`
--

DROP TABLE IF EXISTS `sy_member_collect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_member_collect` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `book_id` int(10) NOT NULL COMMENT '书籍ID',
  `uid` int(10) NOT NULL COMMENT '用户ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '收藏时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='我的收藏表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_member_collect`
--

LOCK TABLES `sy_member_collect` WRITE;
/*!40000 ALTER TABLE `sy_member_collect` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_member_collect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_member_consume`
--

DROP TABLE IF EXISTS `sy_member_consume`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_member_consume` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) unsigned DEFAULT NULL COMMENT '用户ID',
  `money` smallint(5) DEFAULT NULL COMMENT '消费书币',
  `summary` varchar(200) DEFAULT NULL COMMENT '消费描述',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '消费时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户书币消费记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_member_consume`
--

LOCK TABLES `sy_member_consume` WRITE;
/*!40000 ALTER TABLE `sy_member_consume` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_member_consume` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_member_sign`
--

DROP TABLE IF EXISTS `sy_member_sign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_member_sign` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户主键',
  `date` int(8) unsigned NOT NULL DEFAULT '0' COMMENT '签到日期',
  `money` int(10) unsigned DEFAULT '0' COMMENT '获得书币',
  `days` tinyint(2) unsigned DEFAULT '0' COMMENT '连续签到天数',
  `create_time` int(10) unsigned NOT NULL COMMENT '签到时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员签到记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_member_sign`
--

LOCK TABLES `sy_member_sign` WRITE;
/*!40000 ALTER TABLE `sy_member_sign` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_member_sign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_message`
--

DROP TABLE IF EXISTS `sy_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1渠道，2个人',
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1正常，2删除',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='代理&个人消息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_message`
--

LOCK TABLES `sy_message` WRITE;
/*!40000 ALTER TABLE `sy_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_message_read`
--

DROP TABLE IF EXISTS `sy_message_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_message_read` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '代理ID',
  `message_id` int(10) unsigned DEFAULT '0' COMMENT '消息ID',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `message_id` (`message_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公告阅读记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_message_read`
--

LOCK TABLES `sy_message_read` WRITE;
/*!40000 ALTER TABLE `sy_message_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_message_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_nodes`
--

DROP TABLE IF EXISTS `sy_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_nodes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `level` tinyint(2) unsigned DEFAULT '1' COMMENT '层级',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1总站，2渠道，3代理',
  `pid` int(10) unsigned DEFAULT NULL COMMENT '父菜单id',
  `pids` varchar(500) DEFAULT NULL COMMENT '父菜单集',
  `name` varchar(50) DEFAULT NULL COMMENT '菜单名称',
  `is_menu` tinyint(1) unsigned DEFAULT '2' COMMENT '是否菜单，1是2否',
  `url` varchar(50) DEFAULT NULL COMMENT '跳转链接',
  `icon` varchar(50) DEFAULT NULL COMMENT '菜单图标',
  `child_nodes` varchar(500) DEFAULT NULL COMMENT '附属链接',
  `all_nodes` varchar(500) DEFAULT NULL COMMENT '权限集',
  `sort_num` smallint(5) unsigned DEFAULT '0' COMMENT '排序权值',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态1正常，2删除',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `pids` (`pids`) USING BTREE,
  KEY `pid` (`pid`) USING BTREE,
  KEY `level` (`level`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=210 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统节点表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_nodes`
--

LOCK TABLES `sy_nodes` WRITE;
/*!40000 ALTER TABLE `sy_nodes` DISABLE KEYS */;
INSERT INTO `sy_nodes` VALUES (1,1,1,0,'','基本设置',1,'','layui-icon-set','','',125,1,1562142656),(2,2,1,1,',1,','渠道代理菜单设置',1,'','','','',2,1,1562142704),(3,3,1,2,',2,1,','渠道菜单配置',1,'System/channelMenu','','','system:channelmenu',3,1,1562142740),(4,3,1,2,',2,1,','代理菜单配置',1,'System/agentMenu','','','system:agentmenu',4,1,1562142769),(5,2,1,1,',1,','文件储存配置',1,'','','','',5,1,1562142867),(6,3,1,5,',5,1,','阿里云OSS配置',1,'Website/alioss','','','website:alioss',6,1,1562142901),(7,2,1,1,',1,','支付配置',1,'','','','',7,1,1562142913),(8,3,1,7,',7,1,','米花微信支付',1,'Website/mihuaPay','','','website:mihuapay',8,3,1562142948),(9,3,1,7,',7,1,','官方微信支付',1,'Website/pay','','','website:pay',202,1,1562142976),(10,2,1,1,',1,','模版配置',1,'','','','',10,1,1562142998),(11,3,1,10,',10,1,','通用底部导航配置',1,'Templet/footer','','upload:douploadicon','templet:footer,upload:douploadicon',11,1,1562143031),(12,3,1,10,',10,1,','板块管理',1,'Templet/block','','','templet:block',12,1,1562143066),(13,3,1,10,',10,1,','模版管理',1,'Templet/h5','','','templet:h5',13,1,1562143096),(14,4,1,13,',13,10,1,','更新H5模版',2,'Templet/doH5','','','templet:doh5',14,1,1562143151),(15,2,1,1,',1,','站点设置',1,'','','','',15,1,1562143204),(16,3,1,15,',15,1,','基础信息',1,'Website/info','','','website:info',209,1,1562143259),(17,3,1,15,',15,1,','打赏配置',1,'Website/reward','','','website:reward',16,1,1562143285),(18,3,1,15,',15,1,','充值配置',1,'Website/charge','','','website:charge',17,1,1562143302),(19,3,1,15,',15,1,','短信配置',1,'Website/message','','','website:message',18,1,1562143329),(20,1,1,0,'','权限配置',1,'','layui-icon-user','','',60,1,1562143395),(21,2,1,20,',20,','总站菜单',1,'Rbac/node','','','rbac:node',21,1,1562143459),(22,3,1,21,',21,20,','更新菜单',2,'','','rbac:addnode,rbac:donode','rbac:addnode,rbac:donode',22,1,1562143528),(23,3,1,21,',21,20,','处理菜单事件',2,'Rbac/doNodeEvent','','','rbac:donodeevent',23,1,1562143565),(24,1,1,0,'','微信管理',1,'','layui-icon-auz','','',67,1,1562143614),(25,2,1,24,',24,','公众号菜单设置',1,'Wechat/menu','','','wechat:menu',25,1,1562143650),(26,3,1,25,',25,24,','更新菜单',2,'','','wechat:addmenu,wechat:domenu','wechat:addmenu,wechat:domenu',26,1,1562143690),(27,3,1,25,',25,24,','删除&菜单排序',2,'Wechat/doMenuEvent','','','wechat:domenuevent',27,1,1562143773),(28,3,1,25,',25,24,','发布菜单',2,'Wechat/pushMenu','','','wechat:pushmenu',28,1,1562143816),(29,3,1,25,',25,24,','重置默认菜单',2,'Wechat/createDefaultMenu','','','wechat:createdefaultmenu',29,1,1562143844),(30,2,1,20,',20,','角色管理',1,'Rbac/role','','','rbac:role',30,1,1562143860),(31,3,1,30,',30,20,','更新角色',2,'','','rbac:addrole,rbac:dorole,option:getnodeselectlist','rbac:addrole,rbac:dorole,option:getnodeselectlist',31,1,1562143912),(32,3,1,30,',30,20,','处理角色事件',2,'Rbac/doRoleEvent','','','rbac:doroleevent',32,1,1562143964),(33,2,1,20,',20,','用户管理',1,'Rbac/user','','','rbac:user',33,1,1562144001),(34,3,1,33,',33,20,','更新用户',2,'','','rbac:adduser,rbac:douser','rbac:adduser,rbac:douser',34,1,1562144028),(35,3,1,33,',33,20,','处理用户事件',2,'Rbac/doUserEvent','','','rbac:douserevent',35,1,1562144058),(36,2,1,24,',24,','菜单推事件回复',1,'Wechat/special','','','wechat:special',36,1,1562144111),(37,3,1,36,',36,24,','更新回复内容',2,'','','wechat:addspecial,wechat:dospecial','wechat:addspecial,wechat:dospecial',37,1,1562144147),(38,3,1,36,',36,24,','处理菜单推事件',2,'Wechat/doSpecialEvent','','','wechat:dospecialevent',38,1,1562144181),(39,2,1,24,',24,','关键字回复',1,'Wechat/reply','','','wechat:reply',39,1,1562144211),(40,3,1,39,',39,24,','更新关键字',2,'','','wechat:addreply,wechat:doreply','wechat:addreply,wechat:doreply',40,1,1562144253),(41,3,1,39,',39,24,','处理关键字事件',2,'Wechat/doReplyEvent','','','wechat:doreplyevent',41,1,1562144299),(42,2,1,24,',24,','参数配置',1,'Wechat/param','','upload:crop','wechat:param,upload:crop',42,1,1562144337),(43,2,1,24,',24,','获取链接',1,'Wechat/links','','','wechat:links',43,1,1562144366),(44,1,1,0,'','读者管理',1,'','layui-icon-username','','',54,1,1562144418),(45,2,1,44,',44,','读者反馈',1,'Member/feedback','','','member:feedback',45,1,1562144463),(46,3,1,45,',45,44,','回复反馈',2,'Member/doFeedback','','','member:dofeedback',46,1,1562144478),(47,2,1,44,',44,','读者投诉',1,'Member/complaint','','','member:complaint',47,1,1562144495),(48,2,1,44,',44,','读者列表',1,'Member/index','','','member:index',48,1,1562144519),(49,3,1,48,',48,44,','书币及VIP设置',2,'Member/doMemberMoney','','','member:domembermoney',49,1,1562144571),(50,3,1,48,',48,44,','启用禁用读者',2,'Member/doMemberState','','','member:domemberstate',50,1,1562144593),(51,3,1,48,',48,44,','查看详情',2,'Member/info','','member:getrecordlist','member:info,member:getrecordlist',51,1,1562144614),(52,1,1,0,'','结算管理',1,'Withdraw/index','layui-icon-rmb','','withdraw:index',24,1,1562144660),(53,2,1,52,',52,','处理结算申请',2,'Withdraw/doWithdraw','','','withdraw:dowithdraw',53,1,1562144685),(54,1,1,0,'','订单管理',1,'','layui-icon-cart-simple','','',52,1,1562144714),(55,2,1,54,',54,','商品订单',1,'Order/product','','','order:product',55,1,1562144741),(56,3,1,55,',55,54,','商品发货',2,'Order/doSendProduct','','','order:dosendproduct',56,1,1562144760),(57,2,1,54,',54,','打赏订单',1,'Order/reward','','','order:reward',57,1,1562144783),(58,2,1,54,',54,','活动订单',1,'Order/activity','','','order:activity',58,1,1562144797),(59,2,1,54,',54,','充值订单',1,'Order/charge','','','order:charge',59,1,1562144814),(60,1,1,0,'','数据统计',1,'','layui-icon-chart','','',20,1,1562144849),(61,2,1,60,',60,','用户统计',1,'Chart/member','','','chart:member',61,1,1562144880),(62,2,1,60,',60,','订单统计',1,'Chart/order','','','chart:order',62,1,1562144896),(63,2,1,60,',60,','分成统计',1,'Chart/bonus','','','chart:bonus',63,1,1562144910),(64,2,1,60,',60,','推广统计',1,'Chart/spread','','','chart:spread',64,1,1562144969),(65,2,1,60,',60,','投诉统计',1,'Chart/complaint','','','chart:complaint',65,1,1562144984),(66,2,1,60,',60,','充值统计',1,'Chart/charge','','','chart:charge',66,1,1562145001),(67,1,1,0,'','渠道代理',1,'','layui-icon-app','','',44,1,1562145027),(68,2,1,67,',67,','代理管理',1,'Platform/agent','','platform:child,platform:dochild,platform:intobackstage','platform:agent,platform:child,platform:dochild,platform:intobackstage',68,1,1562145056),(69,3,1,68,',68,67,','更新代理',2,'','','platform:addagent,platform:doagent','platform:addagent,platform:doagent',69,1,1562145109),(70,3,1,68,',68,67,','处理代理事件',2,'Platform/doAgentEvent','','','platform:doagentevent',70,1,1562145136),(71,2,1,67,',67,','渠道管理',1,'Platform/channel','','platform:child,platform:dochild,platform:intobackstage','platform:channel,platform:child,platform:dochild,platform:intobackstage',71,1,1562145163),(72,3,1,71,',71,67,','更新渠道',2,'','','platform:addchannel,platform:dochannel,upload:crop','platform:addchannel,platform:dochannel,upload:crop',72,1,1562145217),(73,3,1,71,',71,67,','处理渠道事件',2,'Platform/doChannelEvent','','','platform:dochannelevent',73,1,1562145255),(74,1,1,0,'','内容管理',1,'','layui-icon-list','','',118,1,1562145715),(75,2,1,74,',74,','评论管理',1,'Comments/index','','','comments:index',75,1,1562145756),(76,3,1,75,',75,74,','处理评论审核',2,'Comments/doState','','','comments:dostate',76,1,1562145782),(77,2,1,74,',74,','活动管理',1,'Activity/index','','activity:copylink,message:addtask','activity:index,activity:copylink,message:addtask',77,1,1562145801),(78,3,1,77,',77,74,','更新活动',2,'','','activity:addactivity,activity:doactivity,upload:crop','activity:addactivity,activity:doactivity,upload:crop',78,1,1562145842),(79,3,1,77,',77,74,','处理活动事件',2,'Activity/doActivityEvent','','','activity:doactivityevent',79,1,1562145901),(80,2,1,74,',74,','商品管理',1,'Product/index','','product:copylink,message:addtask,product:refreshcache','product:index,product:copylink,message:addtask,product:refreshcache',80,1,1562145931),(81,3,1,80,',80,74,','处理商品事件',2,'Product/doProductEvent','','','product:doproductevent',81,1,1562145967),(82,3,1,80,',80,74,','更新商品',2,'','','product:addproduct,product:doproduct,upload:crop','product:addproduct,product:doproduct,upload:crop',82,1,1562146007),(83,3,1,80,',80,74,','发布区域配置',2,'Product/area','','','product:area',83,1,1562146049),(84,3,1,80,',80,74,','类型配置',2,'Product/category','','','product:category',84,1,1562146071),(85,3,1,80,',80,74,','轮播图片配置',2,'Product/banners','','upload:crop','product:banners,upload:crop',85,1,1562146118),(86,3,1,80,',80,74,','导航配置',2,'','','product:footer,product:nav,upload:crop','product:footer,product:nav,upload:crop',86,1,1562146184),(87,2,1,74,',74,','小说管理',1,'Novel/index','','spread:createlink,novel:guide,novel:copylink,message:addtask,novel:refreshcache,novel:setsharedata','novel:index,spread:createlink,novel:guide,novel:copylink,message:addtask,novel:refreshcache,novel:setsharedata',111,1,1562146211),(88,3,1,87,',87,74,','更新小说',2,'','','upload:crop,novel:addmorebook,novel:addbook,novel:dobook','upload:crop,novel:addmorebook,novel:addbook,novel:dobook',88,1,1562146270),(89,3,1,87,',87,74,','处理小说事件',2,'Novel/doBookEvent','','','novel:dobookevent',89,1,1562146303),(90,3,1,87,',87,74,','分集管理',2,'','','novel:chapter,novel:dodecodezip,novel:checkchapter,novel:addchapter,novel:dochapter,novel:delchapter,novel:delallchapter,novel:showInfo,upload:douploadfile','novel:chapter,novel:dodecodezip,novel:checkchapter,novel:addchapter,novel:dochapter,novel:delchapter,novel:delallchapter,novel:showinfo,upload:douploadfile',90,1,1562146339),(91,3,1,87,',87,74,','导航配置',2,'','','novel:footer,novel:nav','novel:footer,novel:nav',91,1,1562146363),(92,3,1,87,',87,74,','发布区域配置',2,'Novel/area','','','novel:area',92,1,1562146392),(93,3,1,87,',87,74,','类型配置',2,'Novel/category','','','novel:category',93,1,1562146430),(94,3,1,87,',87,74,','轮播图配置',2,'Novel/banners','','','novel:banners',94,1,1562146489),(95,2,1,74,',74,','漫画管理',1,'Cartoon/index','','spread:createlink,cartoon:guide,cartoon:copylink,message:addtask,cartoon:refreshcache,cartoon:setsharedata','cartoon:index,spread:createlink,cartoon:guide,cartoon:copylink,message:addtask,cartoon:refreshcache,cartoon:setsharedata',103,1,1562158479),(96,3,1,95,',95,74,','更新漫画',2,'','','cartoon:addbook,cartoon:dobook,upload:crop','cartoon:addbook,cartoon:dobook,upload:crop',96,1,1562158533),(97,3,1,95,',95,74,','处理漫画事件',2,'Cartoon/doBookEvent','','','cartoon:dobookevent',97,1,1562158564),(98,3,1,95,',95,74,','分集管理',2,'','','cartoon:chapter,cartoon:dodecodezip,cartoon:checkchapter,cartoon:delchapter,cartoon:delallchapter,cartoon:showinfo,upload:douploadfile','cartoon:chapter,cartoon:dodecodezip,cartoon:checkchapter,cartoon:delchapter,cartoon:delallchapter,cartoon:showinfo,upload:douploadfile',98,1,1562158642),(99,3,1,95,',95,74,','发布区域配置',2,'Cartoon/area','','','cartoon:area',99,1,1562158666),(100,3,1,95,',95,74,','类型配置',2,'Cartoon/category','','','cartoon:category',100,1,1562158694),(101,3,1,95,',95,74,','轮播图片配置',2,'Cartoon/banners','','upload:crop','cartoon:banners,upload:crop',101,1,1562158748),(102,3,1,95,',95,74,','导航配置',2,'','','cartoon:footer,cartoon:nav,upload:crop','cartoon:footer,cartoon:nav,upload:crop',102,1,1562158784),(103,2,1,74,',74,','听书管理',1,'Music/index','','music:copylink,message:addtask,music:refreshcache','music:index,music:copylink,message:addtask,music:refreshcache',95,1,1562159478),(104,3,1,103,',103,74,','更新听书',2,'','','music:addbook,music:dobook,upload:crop','music:addbook,music:dobook,upload:crop',104,1,1562159561),(105,3,1,103,',103,74,','处理听书事件',2,'Music/doBookEvent','','','music:dobookevent',105,1,1562159606),(106,3,1,103,',103,74,','分集管理',2,'Music/chapter','','music:dodecodezip,music:checkchapter,music:addchapter,music:dochapter,music:delchapter,music:delallchapter,music:showInfo,upload:douploadfile','music:chapter,music:dodecodezip,music:checkchapter,music:addchapter,music:dochapter,music:delchapter,music:delallchapter,music:showinfo,upload:douploadfile',106,1,1562159645),(107,3,1,103,',103,74,','发布区域配置',2,'Music/area','','','music:area',107,1,1562159674),(108,3,1,103,',103,74,','类型配置',2,'Music/category','','','music:category',108,1,1562159695),(109,3,1,103,',103,74,','导航配置',2,'','','music:footer,music:nav,upload:crop','music:footer,music:nav,upload:crop',109,1,1562159734),(110,3,1,103,',103,74,','轮播图片配置',2,'Music/banners','','upload:crop','music:banners,upload:crop',110,1,1562159776),(111,2,1,74,',74,','视频管理',1,'Video/index','','video:doplay,video:copylink,message:addtask,video:refreshcache','video:index,video:doplay,video:copylink,message:addtask,video:refreshcache',87,1,1562160129),(112,3,1,111,',111,74,','更新视频',2,'','','video:addvideo,video:dovideo,upload:crop,upload:douploadvideo','video:addvideo,video:dovideo,upload:crop,upload:douploadvideo',112,1,1562160186),(113,3,1,111,',111,74,','处理视频事件',2,'Video/doVideoEvent','','','video:dovideoevent',113,1,1562160236),(114,3,1,111,',111,74,','轮播图片配置',2,'Video/banners','','upload:crop','video:banners,upload:crop',114,1,1562160274),(115,3,1,111,',111,74,','发布区域配置',2,'Video/area','','','video:area',115,1,1562160311),(116,3,1,111,',111,74,','视频分类配置',2,'Video/category','','','video:category',116,1,1562160345),(117,3,1,111,',111,74,','导航配置',2,'','','video:footer,video:nav,upload:crop','video:footer,video:nav,upload:crop',117,1,1562160385),(118,1,1,0,'','推广文案',1,'','layui-icon-release','','',74,1,1562160419),(119,2,1,118,',118,','文案素材',1,'Spread/material','','','spread:material',119,1,1562160799),(120,3,1,119,',119,118,','更新素材',2,'','','spread:addmaterial,spread:domaterial,upload:docrop','spread:addmaterial,spread:domaterial,upload:docrop',120,1,1562160866),(121,3,1,119,',119,118,','删除文案素材',2,'Spread/delMaterial','','','spread:delmaterial',121,1,1562160890),(122,2,1,118,',118,','推广链接',1,'Spread/index','','spread:copylink,spread:qrcode','spread:index,spread:copylink,spread:qrcode',122,1,1562160910),(123,3,1,122,',122,118,','编辑链接',2,'Spread/doLink','','','spread:dolink',123,1,1562160952),(124,3,1,122,',122,118,','删除链接',2,'Spread/delLink','','','spread:dellink',124,1,1562160969),(125,1,1,0,'','系统消息',1,'','layui-icon-chat','','',204,1,1562160999),(126,2,1,125,',125,','客服消息',1,'Message/task','','','message:task',126,1,1562161025),(127,3,1,126,',126,125,','更新客服消息',2,'','','message:addtask,message:dotask,message:getusercount,message:testsend,upload:crop','message:addtask,message:dotask,message:getusercount,message:testsend,upload:crop',127,1,1562161103),(128,3,1,126,',126,125,','删除客服消息',2,'Message/delTask','','','message:deltask',128,1,1562161124),(129,2,1,125,',125,','通知公告',1,'Message/index','','','message:index',129,1,1562161210),(130,3,1,129,',129,125,','更新公告',2,'','','message:addmessage,message:domessage','message:addmessage,message:domessage',130,1,1562161226),(131,3,1,129,',129,125,','删除公告',2,'Message/delMessage','','','message:delmessage',131,1,1562161243),(132,2,1,125,',125,','智能推送',1,'Push/index','','push:doevent,push:dopush,upload:crop','push:index,push:doevent,push:dopush,upload:crop',132,1,1562161268),(133,1,2,0,'','公众号管理',2,'','layui-icon-auz',NULL,NULL,133,1,1562161343),(134,2,2,133,',133,','公众号菜单设置',2,'Wechat/menu','',NULL,NULL,134,1,1562161366),(135,2,2,133,',133,','菜单推事件回复',2,'Wechat/special','',NULL,NULL,135,1,1562161391),(136,2,2,133,',133,','关键字回复',2,'Wechat/reply','',NULL,NULL,136,1,1562161412),(137,2,2,133,',133,','参数配置',2,'Wechat/param','',NULL,NULL,137,1,1562161436),(138,2,2,133,',133,','获取链接',2,'Wechat/links','',NULL,NULL,138,1,1562161454),(139,1,2,0,'','订单管理',2,'','layui-icon-cart-simple',NULL,NULL,139,1,1562161526),(140,2,2,139,',139,','充值订单',2,'Order/charge','',NULL,NULL,140,1,1562161552),(141,2,2,139,',139,','活动订单',2,'Order/activity','',NULL,NULL,141,1,1562161567),(142,2,2,139,',139,','打赏订单',2,'Order/reward','',NULL,NULL,142,1,1562161579),(143,1,2,0,'','数据统计',2,'','layui-icon-chart',NULL,NULL,143,1,1562161624),(144,2,2,143,',143,','订单统计',2,'Chart/order','',NULL,NULL,144,1,1562161652),(145,2,2,143,',143,','用户统计',2,'Chart/member','',NULL,NULL,145,1,1562161666),(146,2,2,143,',143,','分成统计',2,'Chart/bonus','',NULL,NULL,146,1,1562161685),(147,2,2,143,',143,','投诉统计',2,'Chart/complaint','',NULL,NULL,147,1,1562161706),(148,2,2,143,',143,','推广统计',2,'Chart/spread','',NULL,NULL,148,1,1562161724),(149,1,2,0,'','读者管理',2,'','layui-icon-username',NULL,NULL,149,1,1562161745),(150,2,2,149,',149,','读者投诉',2,'Member/complaint','',NULL,NULL,150,1,1562161771),(151,2,2,149,',149,','读者反馈',2,'Member/feedback','',NULL,NULL,151,1,1562161790),(152,2,2,149,',149,','读者列表',2,'Member/index','',NULL,NULL,152,1,1562161804),(153,1,2,0,'','结算管理',2,'','layui-icon-rmb',NULL,NULL,153,1,1562161830),(154,2,2,153,',153,','代理结算',2,'Withdraw/index','',NULL,NULL,154,1,1562161871),(155,2,2,153,',153,','我的结算',2,'Withdraw/mine','',NULL,NULL,155,1,1562161884),(156,1,2,0,'','内容管理',2,'','layui-icon-list',NULL,NULL,156,1,1562161907),(157,2,2,156,',156,','小说列表',2,'Novel/index','',NULL,NULL,157,1,1562161926),(158,2,2,156,',156,','漫画列表',2,'Cartoon/index','',NULL,NULL,158,1,1562161944),(159,2,2,156,',156,','听书列表',2,'Music/index','',NULL,NULL,159,1,1562161977),(160,2,2,156,',156,','视频列表',2,'Video/index','',NULL,NULL,160,1,1562161991),(161,2,2,156,',156,','活动列表',2,'Activity/index','',NULL,NULL,161,1,1562162006),(162,1,2,0,'','推广链接',2,'Spread/index','layui-icon-link',NULL,NULL,162,1,1562162026),(163,1,2,0,'','消息管理',2,'','layui-icon-chat',NULL,NULL,163,1,1562162073),(164,2,2,163,',163,','客服消息',2,'Message/task','',NULL,NULL,165,1,1562162093),(165,2,2,163,',163,','通知公告',2,'Message/index','',NULL,NULL,164,1,1562162109),(166,2,2,163,',163,','智能推送',2,'Push/index','',NULL,NULL,166,1,1562162122),(167,1,3,0,'','数据统计',2,'','layui-icon-chart',NULL,NULL,167,1,1562162170),(168,2,3,167,',167,','推广统计',2,'Chart/spread','',NULL,NULL,168,1,1562162190),(169,2,3,167,',167,','分成统计',2,'Chart/bonus','',NULL,NULL,169,1,1562162206),(170,2,3,167,',167,','订单统计',2,'Chart/order','',NULL,NULL,170,1,1562162219),(171,2,3,167,',167,','用户统计',2,'Chart/member','',NULL,NULL,171,1,1562162231),(172,1,3,0,'','订单管理',2,'','layui-icon-cart-simple',NULL,NULL,172,1,1562162254),(173,2,3,172,',172,','充值订单',2,'Order/charge','',NULL,NULL,173,1,1562162277),(174,2,3,172,',172,','活动订单',2,'Order/activity','',NULL,NULL,174,1,1562162290),(175,2,3,172,',172,','打赏订单',2,'Order/reward','',NULL,NULL,175,1,1562162302),(176,1,3,0,'','我的结算',2,'Withdraw/index','layui-icon-rmb',NULL,NULL,176,1,1562162342),(177,1,3,0,'','内容管理',2,'','layui-icon-list',NULL,NULL,177,1,1562162368),(178,2,3,177,',177,','小说管理',2,'Novel/index','',NULL,NULL,178,1,1562162388),(179,2,3,177,',177,','漫画管理',2,'Cartoon/index','',NULL,NULL,179,1,1562162414),(180,2,3,177,',177,','活动管理',2,'Activity/index','',NULL,NULL,180,1,1562162431),(181,1,3,0,'','推广链接',2,'Spread/index','layui-icon-link',NULL,NULL,181,1,1562162460),(182,1,3,0,'','读者列表',2,'Member/index','layui-icon-username',NULL,NULL,182,1,1562162500),(183,1,3,0,'','通知公告',2,'Message/index','layui-icon-chat',NULL,NULL,183,1,1562162529),(184,4,1,4,',4,2,1,','更新代理菜单',2,'','','system:addagentmenu,system:doagentmenu','system:addagentmenu,system:doagentmenu',184,1,1562207972),(185,4,1,4,',4,2,1,','处理代理菜单事件',2,'System/doAgentMenuEvent','','','system:doagentmenuevent',185,1,1562208089),(186,4,1,3,',3,2,1,','更新渠道菜单',2,'','','system:addchannelmenu,system:dochannelmenu','system:addchannelmenu,system:dochannelmenu',186,1,1562208151),(187,4,1,3,',3,2,1,','处理渠道菜单事件',2,'System/doChannelMenuEvent','','','system:dochannelmenuevent',187,1,1562208188),(188,1,2,0,'','代理管理',2,'Agent/index','layui-icon-app',NULL,NULL,188,1,1566357234),(189,3,1,7,',7,1,','咪拉宝支付',1,'Website/milabaoPay','','','website:milabaopay',189,3,1568474911),(190,2,1,74,',74,','广告管理',1,'Advertisement/index','','','advertisement:index',190,1,1568862747),(191,3,1,190,',190,74,','修改广告状态',2,'Advertisement/dostatus','','','advertisement:dostatus',191,1,1568865146),(192,3,1,190,',190,74,','广告分类列表',2,'advertisement/details','','','advertisement:details',192,1,1568882951),(193,3,1,190,',190,74,','编辑广告位',2,'advertisement/adedit','','','advertisement:adedit',193,1,1568883609),(194,3,1,190,',190,74,','添加广告',2,'advertiserment/addCate','','','advertiserment:addcate',194,1,1568887328),(195,3,1,190,',190,74,','编辑广告',2,'advertisement/doedit','','','advertisement:doedit',195,1,1568943402),(196,3,1,190,',190,74,','更改广告状态',2,'advertisement/dostatu','','','advertisement:dostatu',196,1,1568947675),(197,3,1,190,',190,74,','添加广告位置',2,'advertisement/adadd','','','advertisement:adadd',197,1,1568949641),(198,3,1,190,',190,74,','新增广告',2,'advertisement/doadd','','','advertisement:doadd',198,1,1568950515),(199,3,1,190,',190,74,','删除所有广告',2,'advertisement/delall','','','advertisement:delall',199,1,1568954822),(200,2,3,177,',177,','视频管理',2,' Video/index','',NULL,NULL,200,3,1584969512),(201,3,1,7,',7,1,','支付猫支付',1,'Website/payCat','','','website:paycat',9,3,1585755959),(202,3,1,7,',7,1,','彩虹易支付',1,'Website/payCat','','','website:paycat',201,1,1585758404),(203,2,1,1,',1,','采集设置',1,'','','','',203,3,1586148781),(204,1,1,0,'','采集设置',1,'','layui-icon-upload','','',1,3,1586150610),(205,2,1,204,',204,','小说采集设置',1,'website/xsplus','','','website:xsplus',205,3,1586150867),(206,2,1,204,',204,','漫画采集设置',1,'website/mhplus','','','website:mhplus',206,3,1586150912),(207,2,1,204,',204,','听书采集设置',1,'website/tsplus','','','website:tsplus',207,3,1586150987),(208,2,1,204,',204,','视频采集设置',1,'website/ysplus','','','website:ysplus',208,3,1586151164),(209,3,1,15,',15,1,','启用管理',1,'Templet/block','','','templet:block',19,3,1664171147);
/*!40000 ALTER TABLE `sy_nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_order`
--

DROP TABLE IF EXISTS `sy_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` tinyint(1) unsigned DEFAULT NULL COMMENT '订单类型：1充值，2活动充值，3打赏',
  `order_no` varchar(100) NOT NULL COMMENT '订单号',
  `wx_id` int(10) unsigned DEFAULT '0' COMMENT '所属微信渠道ID',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '渠道ID',
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '代理ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `money` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `send_money` int(10) unsigned DEFAULT '0' COMMENT '赠送书币',
  `package` tinyint(2) unsigned DEFAULT '0' COMMENT '套餐类型',
  `pay_time` int(11) unsigned DEFAULT '0' COMMENT '支付时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '支付状态1未支付，2已支付',
  `is_count` tinyint(1) unsigned DEFAULT '1' COMMENT '是否分成；1是2否',
  `is_count_temp` tinyint(1) unsigned DEFAULT '2' COMMENT '临时变量，是否扣量，1是2否，支付成功后根据此变量更改分成状态',
  `pay_type` tinyint(1) unsigned DEFAULT '0' COMMENT '支付方式,1官方微信支付',
  `pay_url` longtext COMMENT '米花金服支付url',
  `spread_id` int(10) unsigned DEFAULT '0' COMMENT '推广id',
  `relation_type` tinyint(1) unsigned DEFAULT '0' COMMENT '0直接充值，1漫画，2小说，3听书，4视频',
  `relation_id` int(10) unsigned DEFAULT '0' COMMENT '书籍id，视频id，活动id',
  `relation_name` varchar(200) DEFAULT NULL COMMENT '关联书籍&视频&活动&推广名称',
  `create_date` int(8) unsigned DEFAULT '0' COMMENT '创建日期',
  `create_time` int(11) NOT NULL COMMENT '订单创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_no` (`order_no`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `is_count` (`is_count`) USING BTREE,
  KEY `spread_id` (`spread_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='充值记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_order`
--

LOCK TABLES `sy_order` WRITE;
/*!40000 ALTER TABLE `sy_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_order_count`
--

DROP TABLE IF EXISTS `sy_order_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_order_count` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '渠道id',
  `agent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '代理id',
  `order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `agent_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '代理分成金额',
  `channel_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '渠道分成金额',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '1已结算，2待支付结算',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:普通充值；2:活动充值，3打赏',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='分成结算表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_order_count`
--

LOCK TABLES `sy_order_count` WRITE;
/*!40000 ALTER TABLE `sy_order_count` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_order_count` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_play_history`
--

DROP TABLE IF EXISTS `sy_play_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_play_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `video_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '视频ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '播放用户',
  `create_time` int(11) NOT NULL COMMENT '播放时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `video_id` (`video_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='播放历史表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_play_history`
--

LOCK TABLES `sy_play_history` WRITE;
/*!40000 ALTER TABLE `sy_play_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_play_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_product`
--

DROP TABLE IF EXISTS `sy_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(200) DEFAULT NULL COMMENT '商品名称',
  `cover` varchar(255) DEFAULT NULL COMMENT '商品封面',
  `money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '商品金额',
  `summary` varchar(255) DEFAULT NULL COMMENT '商品简介',
  `stock` int(10) unsigned DEFAULT '0' COMMENT '商品库存',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1正常，2下架',
  `sort_num` int(8) unsigned DEFAULT '0' COMMENT '排序权值',
  `area` varchar(255) DEFAULT NULL COMMENT '发布区域',
  `is_hot` tinyint(1) unsigned DEFAULT '1' COMMENT '是否热门1是2否',
  `hot_num` int(10) unsigned DEFAULT '0' COMMENT '人气值',
  `buy_num` int(10) unsigned DEFAULT '0' COMMENT '销量',
  `category` varchar(255) DEFAULT NULL COMMENT '类型',
  `share_title` varchar(200) DEFAULT NULL COMMENT '分享标题',
  `share_desc` varchar(255) DEFAULT NULL COMMENT '分享简介',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='促销活动信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_product`
--

LOCK TABLES `sy_product` WRITE;
/*!40000 ALTER TABLE `sy_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_push_first_charge`
--

DROP TABLE IF EXISTS `sy_push_first_charge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_push_first_charge` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `create_time` int(11) unsigned NOT NULL COMMENT '推送时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='首冲消息推送记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_push_first_charge`
--

LOCK TABLES `sy_push_first_charge` WRITE;
/*!40000 ALTER TABLE `sy_push_first_charge` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_push_first_charge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_push_likes`
--

DROP TABLE IF EXISTS `sy_push_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_push_likes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `rid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '阅读最大id',
  `uid` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rid` (`rid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='猜你喜欢推送记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_push_likes`
--

LOCK TABLES `sy_push_likes` WRITE;
/*!40000 ALTER TABLE `sy_push_likes` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_push_likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_push_notpay`
--

DROP TABLE IF EXISTS `sy_push_notpay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_push_notpay` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `order_id` int(11) unsigned DEFAULT '0' COMMENT '订单最大id',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='未支付推送记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_push_notpay`
--

LOCK TABLES `sy_push_notpay` WRITE;
/*!40000 ALTER TABLE `sy_push_notpay` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_push_notpay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_push_read`
--

DROP TABLE IF EXISTS `sy_push_read`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_push_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `rid` int(11) unsigned DEFAULT '0' COMMENT '阅读最大id',
  `uid` int(10) unsigned DEFAULT '0' COMMENT '用户id',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `rid` (`rid`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='继续阅读记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_push_read`
--

LOCK TABLES `sy_push_read` WRITE;
/*!40000 ALTER TABLE `sy_push_read` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_push_read` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_read_history`
--

DROP TABLE IF EXISTS `sy_read_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_read_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `book_id` int(10) unsigned DEFAULT '0' COMMENT '书籍ID',
  `type` tinyint(1) unsigned DEFAULT '0' COMMENT '书籍类型',
  `number` int(10) unsigned DEFAULT '0' COMMENT '章节数',
  `channel_id` int(10) unsigned DEFAULT NULL COMMENT '渠道ID',
  `is_end` tinyint(1) unsigned DEFAULT '2' COMMENT '是否最后阅读',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '阅读时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `book_id` (`book_id`) USING BTREE,
  KEY `number` (`number`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `is_end` (`is_end`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `create_time` (`create_time`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='阅读历史表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_read_history`
--

LOCK TABLES `sy_read_history` WRITE;
/*!40000 ALTER TABLE `sy_read_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_read_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_role`
--

DROP TABLE IF EXISTS `sy_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(30) DEFAULT NULL COMMENT '角色名称',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态：1启用2禁用',
  `content` json DEFAULT NULL COMMENT '角色内容',
  `summary` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `create_time` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统角色表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_role`
--

LOCK TABLES `sy_role` WRITE;
/*!40000 ALTER TABLE `sy_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_sale_order`
--

DROP TABLE IF EXISTS `sy_sale_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_sale_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '渠道id',
  `order_no` varchar(100) NOT NULL COMMENT '订单号',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `username` varchar(100) DEFAULT NULL COMMENT '收货人',
  `phone` int(11) unsigned DEFAULT '0' COMMENT '手机号',
  `address` varchar(255) DEFAULT NULL COMMENT '收货地址',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '订单状态1：待支付，2已支付，3已发货，4已完成',
  `pay_time` int(10) unsigned DEFAULT NULL COMMENT '支付时间',
  `pid` int(10) unsigned DEFAULT '0' COMMENT '商品id',
  `count` smallint(5) unsigned DEFAULT '1' COMMENT '商品数量',
  `pname` varchar(200) DEFAULT NULL COMMENT '商品名称',
  `date` int(8) unsigned DEFAULT '0' COMMENT '创建日期',
  `remark` varchar(255) DEFAULT NULL COMMENT '订单备注',
  `pay_type` tinyint(1) unsigned DEFAULT '1' COMMENT '支付渠道',
  `create_time` int(11) NOT NULL COMMENT '订单创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_no` (`order_no`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `product_id` (`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商品订单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_sale_order`
--

LOCK TABLES `sy_sale_order` WRITE;
/*!40000 ALTER TABLE `sy_sale_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_sale_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_search_record`
--

DROP TABLE IF EXISTS `sy_search_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_search_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `keyword` varchar(20) DEFAULT NULL COMMENT '搜索关键字',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `keyword` (`keyword`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='搜索记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_search_record`
--

LOCK TABLES `sy_search_record` WRITE;
/*!40000 ALTER TABLE `sy_search_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_search_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_share`
--

DROP TABLE IF EXISTS `sy_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_share` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `money` int(8) DEFAULT NULL COMMENT '赠送书币数量',
  `create_date` int(8) DEFAULT NULL COMMENT '分享日期',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `create_date` (`create_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='分享奖励领取表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_share`
--

LOCK TABLES `sy_share` WRITE;
/*!40000 ALTER TABLE `sy_share` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_spread`
--

DROP TABLE IF EXISTS `sy_spread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_spread` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '代理id',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1漫画，2小说',
  `book_id` int(10) unsigned DEFAULT '0' COMMENT '推广书籍ID',
  `chapter_id` int(10) DEFAULT NULL COMMENT '推广章节ID',
  `chapter_number` smallint(5) DEFAULT '0' COMMENT '推广章节章数',
  `name` varchar(255) DEFAULT NULL COMMENT '推广名称',
  `url` varchar(255) DEFAULT NULL COMMENT '推广链接',
  `short_url` varchar(255) DEFAULT NULL COMMENT '短链接',
  `is_sub` tinyint(1) unsigned DEFAULT '1' COMMENT '是否强制关注',
  `number` smallint(5) unsigned DEFAULT '0' COMMENT '强制关注章节',
  `visitor_num` int(11) unsigned DEFAULT '0' COMMENT '新增人数',
  `member_num` int(11) unsigned DEFAULT '0' COMMENT '关注人数',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态，1正常',
  `update_time` int(11) DEFAULT '0' COMMENT '最后更新时间',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `book_id` (`book_id`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='小说推广链接';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_spread`
--

LOCK TABLES `sy_spread` WRITE;
/*!40000 ALTER TABLE `sy_spread` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_spread` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_task`
--

DROP TABLE IF EXISTS `sy_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) DEFAULT NULL COMMENT '任务名称',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '渠道ID，0为总站',
  `material` json DEFAULT NULL COMMENT '客服消息',
  `condition` json DEFAULT NULL COMMENT '选择的条件',
  `where` json DEFAULT NULL COMMENT '构造好的查询条件',
  `send_time` int(10) unsigned DEFAULT '0' COMMENT '发送时间',
  `status` tinyint(1) unsigned DEFAULT '2' COMMENT '1已发送，2未发送',
  `is_all` tinyint(1) unsigned DEFAULT '1' COMMENT '是否所有用户1是2否',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `is_all` (`is_all`) USING BTREE,
  KEY `send_time` (`send_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='客服消息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_task`
--

LOCK TABLES `sy_task` WRITE;
/*!40000 ALTER TABLE `sy_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_task_member`
--

DROP TABLE IF EXISTS `sy_task_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_task_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `create_date` date DEFAULT NULL COMMENT '记录日期',
  `channel_id` int(11) unsigned DEFAULT '0' COMMENT '渠道ID',
  `add_num` int(10) unsigned DEFAULT '0' COMMENT '新增人数',
  `sub_num` int(10) unsigned DEFAULT '0' COMMENT '关注人数',
  `sex1` int(10) unsigned DEFAULT '0' COMMENT '男性人数',
  `sex2` int(10) unsigned DEFAULT '0' COMMENT '女性人数',
  `sex0` int(10) unsigned DEFAULT '0' COMMENT '未知性别人数',
  `charge_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '充值金额',
  `charge_nums` int(10) unsigned DEFAULT '0' COMMENT '充值数',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `create_date` (`create_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员统计缓存表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_task_member`
--

LOCK TABLES `sy_task_member` WRITE;
/*!40000 ALTER TABLE `sy_task_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_task_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_task_message`
--

DROP TABLE IF EXISTS `sy_task_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_task_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '渠道id',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1首冲，2继续阅读，3未支付，4猜你喜欢',
  `material` json DEFAULT NULL COMMENT '推送内容',
  `status` tinyint(1) unsigned DEFAULT '2' COMMENT '1开启2关闭',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='智能推送配置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_task_message`
--

LOCK TABLES `sy_task_message` WRITE;
/*!40000 ALTER TABLE `sy_task_message` DISABLE KEYS */;
INSERT INTO `sy_task_message` VALUES (1,0,1,'[{\"url\": \"链接\", \"title\": \"标题\", \"picurl\": \"图片\", \"description\": \"简介\"}]',2),(2,0,2,NULL,2),(3,0,3,NULL,2),(4,0,4,NULL,2);
/*!40000 ALTER TABLE `sy_task_message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_task_order`
--

DROP TABLE IF EXISTS `sy_task_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_task_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `create_date` date DEFAULT NULL COMMENT '记录日期',
  `channel_id` int(11) unsigned DEFAULT '0' COMMENT '渠道ID',
  `agent_id` int(10) unsigned DEFAULT '0' COMMENT '代理ID',
  `n_pay` int(10) unsigned DEFAULT '0' COMMENT '普通支付笔数',
  `n_notpay` int(10) unsigned DEFAULT '0' COMMENT '普通未支付笔数',
  `n_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '普通支付金额',
  `n_user` int(10) DEFAULT NULL COMMENT '普通支付人数',
  `n_rate` tinyint(3) unsigned DEFAULT '0' COMMENT '普通充值比例',
  `p_pay` int(10) unsigned DEFAULT '0' COMMENT 'vip支付笔数',
  `p_notpay` int(10) unsigned DEFAULT '0' COMMENT 'vip未支付笔数',
  `p_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT 'vip充值金额',
  `p_user` int(10) unsigned DEFAULT '0' COMMENT 'vip充值人数',
  `p_rate` tinyint(3) unsigned DEFAULT '0' COMMENT 'vip充值比例',
  `total_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '总计支付金额',
  `type1_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '普通充值',
  `type2_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '活动充值',
  `type3_money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '打赏金额',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `create_date` (`create_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='订单统计缓存表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_task_order`
--

LOCK TABLES `sy_task_order` WRITE;
/*!40000 ALTER TABLE `sy_task_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_task_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_video`
--

DROP TABLE IF EXISTS `sy_video`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_video` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(200) DEFAULT NULL COMMENT '视频名称',
  `cover` varchar(255) DEFAULT NULL COMMENT '封面图片',
  `detail_img` varchar(255) DEFAULT NULL COMMENT '详情图片',
  `summary` varchar(500) DEFAULT NULL COMMENT '简介',
  `file_key` varchar(150) DEFAULT NULL COMMENT '七牛云key',
  `url` varchar(255) DEFAULT NULL COMMENT '视频外链',
  `sort_num` int(8) unsigned DEFAULT '0' COMMENT '排序权值',
  `area` varchar(255) DEFAULT NULL COMMENT '发布区域',
  `category` varchar(500) DEFAULT NULL COMMENT '分类',
  `free_type` tinyint(1) unsigned DEFAULT '2' COMMENT '1免费2收费',
  `money` int(8) unsigned DEFAULT '0' COMMENT '书币金额',
  `hot_num` int(10) unsigned DEFAULT '0' COMMENT '人气值',
  `is_hot` tinyint(1) unsigned DEFAULT '1' COMMENT '是否推荐1是2否',
  `read_num` int(10) unsigned DEFAULT '0' COMMENT '播放次数',
  `share_title` varchar(100) DEFAULT NULL COMMENT '分享标题',
  `share_desc` varchar(500) DEFAULT NULL COMMENT '分享简介',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1上架，2下架',
  `create_time` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `zan` tinyint(5) DEFAULT '0',
  `cai` tinyint(5) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `area` (`area`) USING BTREE,
  KEY `category` (`category`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='书籍信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_video`
--

LOCK TABLES `sy_video` WRITE;
/*!40000 ALTER TABLE `sy_video` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_video` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_video_collection`
--

DROP TABLE IF EXISTS `sy_video_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_video_collection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `video_id` int(10) NOT NULL COMMENT '书籍ID',
  `uid` int(10) NOT NULL COMMENT '用户ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '收藏时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='我的收藏表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_video_collection`
--

LOCK TABLES `sy_video_collection` WRITE;
/*!40000 ALTER TABLE `sy_video_collection` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_video_collection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_view_record`
--

DROP TABLE IF EXISTS `sy_view_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_view_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) NOT NULL COMMENT '用户id',
  `create_time` int(11) unsigned NOT NULL COMMENT '首次访问时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='首次访问记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_view_record`
--

LOCK TABLES `sy_view_record` WRITE;
/*!40000 ALTER TABLE `sy_view_record` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_view_record` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_withdraw`
--

DROP TABLE IF EXISTS `sy_withdraw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_withdraw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请渠道&代理id',
  `to_channel_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0代表总站，接受申请方id',
  `money` decimal(18,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '申请提现金额',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0待审核1已通过，2未通过',
  `bank_user` varchar(50) DEFAULT NULL COMMENT '开户人',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '开户银行',
  `bank_no` varchar(20) DEFAULT NULL COMMENT '银行卡号',
  `remark` varchar(255) DEFAULT NULL COMMENT '不通过原因',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提交时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `to_channel_id` (`to_channel_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='提现结算表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_withdraw`
--

LOCK TABLES `sy_withdraw` WRITE;
/*!40000 ALTER TABLE `sy_withdraw` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_withdraw` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_wx_menu`
--

DROP TABLE IF EXISTS `sy_wx_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_wx_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '渠道id,0为总站',
  `pid` int(10) unsigned DEFAULT NULL COMMENT '父id',
  `name` varchar(20) DEFAULT NULL COMMENT '菜单名称',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1网页，2小程序,3菜单推',
  `content` json DEFAULT NULL COMMENT '菜单内容信息',
  `sort_num` int(10) unsigned DEFAULT '0' COMMENT '排序值',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='微信菜单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_wx_menu`
--

LOCK TABLES `sy_wx_menu` WRITE;
/*!40000 ALTER TABLE `sy_wx_menu` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_wx_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_wx_reply`
--

DROP TABLE IF EXISTS `sy_wx_reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_wx_reply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '渠道ID,0为总站',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1文本，2图文',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1开启，2关闭',
  `keyword` varchar(255) DEFAULT NULL COMMENT '关键字',
  `content` varchar(500) DEFAULT NULL COMMENT '回复内容',
  `material` json DEFAULT NULL COMMENT '回复图文消息',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='微信自动回复表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_wx_reply`
--

LOCK TABLES `sy_wx_reply` WRITE;
/*!40000 ALTER TABLE `sy_wx_reply` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_wx_reply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sy_wx_special`
--

DROP TABLE IF EXISTS `sy_wx_special`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sy_wx_special` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(10) unsigned DEFAULT '0' COMMENT '渠道ID',
  `keyword` varchar(20) DEFAULT NULL COMMENT '关键字',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '1文本，2图文',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1启用，2禁用',
  `content` varchar(500) DEFAULT NULL COMMENT '文本内容',
  `material` json DEFAULT NULL COMMENT '图文素材',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `key` (`keyword`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='微信特殊事件回复';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sy_wx_special`
--

LOCK TABLES `sy_wx_special` WRITE;
/*!40000 ALTER TABLE `sy_wx_special` DISABLE KEYS */;
/*!40000 ALTER TABLE `sy_wx_special` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'mh'
--

--
-- Dumping routines for database 'mh'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-11-23 21:02:38
