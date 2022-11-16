-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	8.0.27


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema testbase
--

CREATE DATABASE IF NOT EXISTS testbase;
USE testbase;

--
-- Definition of table `answer`
--

DROP TABLE IF EXISTS `answer`;
CREATE TABLE `answer` (
  `answerid` int unsigned NOT NULL AUTO_INCREMENT,
  `questionid` int unsigned NOT NULL COMMENT 'Вопрос, к которому относится ответ',
  `content` varchar(45) NOT NULL COMMENT 'Содержание ответа',
  `correct` tinyint(1) NOT NULL COMMENT 'Ответ является верным или нет',
  PRIMARY KEY (`answerid`),
  KEY `FK_answer_1` (`questionid`),
  CONSTRAINT `FK_answer_1` FOREIGN KEY (`questionid`) REFERENCES `question` (`questionid`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Ответы на вопросы тестов';

--
-- Dumping data for table `answer`
--

/*!40000 ALTER TABLE `answer` DISABLE KEYS */;
INSERT INTO `answer` (`answerid`,`questionid`,`content`,`correct`) VALUES 
 (1,1,'Верный ответ',1),
 (2,1,'Неверный ответ',0),
 (3,1,'Еще один неверный ответ',0),
 (4,1,'И этот ответ тоже неверный',0),
 (5,3,'Верный текстовый ответ',1),
 (6,6,'Верный ответ',1),
 (7,6,'Еще один верный ответ',1),
 (8,6,'Неверный ответ',0),
 (9,6,'Неверный ответ еще один',0),
 (10,6,'Неверный ответ номер три',0),
 (11,6,'Неверный ответ номер четыре',0);
/*!40000 ALTER TABLE `answer` ENABLE KEYS */;


--
-- Definition of table `apikey`
--

DROP TABLE IF EXISTS `apikey`;
CREATE TABLE `apikey` (
  `apikey` varchar(45) NOT NULL,
  `descript` varchar(45) NOT NULL COMMENT 'Держатель ключа и прочая информация',
  PRIMARY KEY (`apikey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Ключ доступа для пользователей API';

--
-- Dumping data for table `apikey`
--

/*!40000 ALTER TABLE `apikey` DISABLE KEYS */;
INSERT INTO `apikey` (`apikey`,`descript`) VALUES 
 ('APIKEY','Base application');
/*!40000 ALTER TABLE `apikey` ENABLE KEYS */;


--
-- Definition of table `course`
--

DROP TABLE IF EXISTS `course`;
CREATE TABLE `course` (
  `courseid` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL COMMENT 'Название курса',
  PRIMARY KEY (`courseid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Группа тестов, открывающихся последовательно';

--
-- Dumping data for table `course`
--

/*!40000 ALTER TABLE `course` DISABLE KEYS */;
INSERT INTO `course` (`courseid`,`name`) VALUES 
 (2,'Второй курс');
/*!40000 ALTER TABLE `course` ENABLE KEYS */;


--
-- Definition of table `group`
--

DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `groupid` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`groupid`),
  UNIQUE KEY `Index_2` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Классификатор групп студентов';

--
-- Dumping data for table `group`
--

/*!40000 ALTER TABLE `group` DISABLE KEYS */;
INSERT INTO `group` (`groupid`,`name`) VALUES 
 (1,'ИКБО-18-20'),
 (2,'ИКБО-19-20'),
 (3,'ИКБО-20-20'),
 (4,'ИКБО-21-20');
/*!40000 ALTER TABLE `group` ENABLE KEYS */;


--
-- Definition of table `question`
--

DROP TABLE IF EXISTS `question`;
CREATE TABLE `question` (
  `questionid` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Наименование вопроса',
  `testid` int unsigned NOT NULL COMMENT 'Тест, к которому прикреплен вопрос',
  `content` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Содержание вопроса',
  `type` varchar(45) NOT NULL COMMENT 'Тип вопроса',
  PRIMARY KEY (`questionid`) USING BTREE,
  KEY `FK_question_1` (`testid`),
  KEY `FK_question_2` (`type`),
  CONSTRAINT `FK_question_1` FOREIGN KEY (`testid`) REFERENCES `test` (`testid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_question_2` FOREIGN KEY (`type`) REFERENCES `questiontype` (`type`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Вопросы тестов';

--
-- Dumping data for table `question`
--

/*!40000 ALTER TABLE `question` DISABLE KEYS */;
INSERT INTO `question` (`questionid`,`name`,`testid`,`content`,`type`) VALUES 
 (1,'Первый вопрос',2,'Содержание вопроса','radio'),
 (3,'Третий вопрос',2,'Содержание третьего вопроса','text'),
 (6,'Вопрос с несколькими вариантами ответа',2,'Содержание вопроса','checkbox');
/*!40000 ALTER TABLE `question` ENABLE KEYS */;


--
-- Definition of table `questiontype`
--

DROP TABLE IF EXISTS `questiontype`;
CREATE TABLE `questiontype` (
  `type` varchar(45) NOT NULL COMMENT 'Тип вопроса',
  `limit` int unsigned NOT NULL COMMENT 'Лимит вариантов ответа на вопрос',
  `score` int unsigned NOT NULL COMMENT 'Количество баллов, которое получает студент при правильном ответе на вопрос',
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Описание',
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Классификатор типов вопросов';

--
-- Dumping data for table `questiontype`
--

/*!40000 ALTER TABLE `questiontype` DISABLE KEYS */;
INSERT INTO `questiontype` (`type`,`limit`,`score`,`description`) VALUES 
 ('checkbox',6,2,'Пользователю предоставляется 6 вариантов ответа на вопрос, каждый из которых может быть верным. Число верных ответов: от 1 до 8.'),
 ('radio',4,1,'Пользователю предоставляется 4 варианта ответа на вопрос, из которых только 1 может быть верным.'),
 ('text',0,3,'Пользователь должен сам ввести ответ на вопрос.');
/*!40000 ALTER TABLE `questiontype` ENABLE KEYS */;


--
-- Definition of table `result`
--

DROP TABLE IF EXISTS `result`;
CREATE TABLE `result` (
  `testid` int unsigned NOT NULL,
  `userid` int unsigned NOT NULL,
  `seed` int unsigned NOT NULL COMMENT 'Сид сгенерированного теста',
  `result` int unsigned NOT NULL,
  KEY `FK_result_1` (`testid`),
  KEY `FK_result_2` (`userid`),
  CONSTRAINT `FK_result_1` FOREIGN KEY (`testid`) REFERENCES `test` (`testid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_result_2` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Результаты выполнения тестов';

--
-- Dumping data for table `result`
--

/*!40000 ALTER TABLE `result` DISABLE KEYS */;
/*!40000 ALTER TABLE `result` ENABLE KEYS */;


--
-- Definition of table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `role` varchar(45) NOT NULL,
  `descript` varchar(45) NOT NULL COMMENT 'Описание роли',
  PRIMARY KEY (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Классификатор ролей пользователей';

--
-- Dumping data for table `role`
--

/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` (`role`,`descript`) VALUES 
 ('admin','Администратор'),
 ('student','Ученик'),
 ('teacher','Преподаватель');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;


--
-- Definition of table `test`
--

DROP TABLE IF EXISTS `test`;
CREATE TABLE `test` (
  `testid` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `courseid` int unsigned NOT NULL COMMENT 'Курс, к которому привязан тест',
  `number` int unsigned NOT NULL COMMENT 'Номер теста в курсе',
  `questioncnt` int unsigned NOT NULL COMMENT 'Количество вопросов в тесте',
  PRIMARY KEY (`testid`),
  KEY `FK_test_1` (`courseid`),
  CONSTRAINT `FK_test_1` FOREIGN KEY (`courseid`) REFERENCES `course` (`courseid`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Тесты';

--
-- Dumping data for table `test`
--

/*!40000 ALTER TABLE `test` DISABLE KEYS */;
INSERT INTO `test` (`testid`,`name`,`courseid`,`number`,`questioncnt`) VALUES 
 (2,'Второй тест',2,1,1);
/*!40000 ALTER TABLE `test` ENABLE KEYS */;


--
-- Definition of table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `userid` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `role` varchar(45) NOT NULL,
  `groupid` int unsigned DEFAULT NULL,
  PRIMARY KEY (`userid`),
  KEY `FK_user_1` (`role`),
  KEY `FK_user_2` (`groupid`),
  CONSTRAINT `FK_user_1` FOREIGN KEY (`role`) REFERENCES `role` (`role`),
  CONSTRAINT `FK_user_2` FOREIGN KEY (`groupid`) REFERENCES `group` (`groupid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Пользователь';

--
-- Dumping data for table `user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`userid`,`name`,`role`,`groupid`) VALUES 
 (6,'Петров Петр Петрович','student',1),
 (7,'Николаев Павел Максимович','student',1);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
