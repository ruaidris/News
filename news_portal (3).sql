-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 10:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `news_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `location` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `title`, `image`, `link`, `start_date`, `end_date`, `status`, `location`) VALUES
(1, 'إطلاق تدريب Code2Careers في غزة سكاي جيكس 🚀', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTN4NjiPkaImNO6Oe_7OirMf8j8Hs38sZ_ihw&s', 'https://www.facebook.com/GazaSkyGeeks/', '2025-05-28', '2025-06-07', 'active', 'category'),
(2, 'Apricot International exists because of Gaza Sky Geeks ', 'https://media.licdn.com/dms/image/v2/D5622AQH0d72YGeEPgg/feedshare-shrink_800/B56Zb.f6XJHoAg-/0/1748026502052?e=2147483647&v=beta&t=aOoiUU4AS97ViDp0GTQFv02AxmVkrAw9NVLMvdSb1Gs', 'https://ps.linkedin.com/company/gaza-sky-geeks', '2025-05-29', '2025-06-01', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `description`) VALUES
(1, 'سياسة', 'أخبار السياسة والحكومة'),
(2, 'اقتصاد', 'أخبار الاقتصاد والأعمال'),
(3, 'صحة', 'أخبار الصحة والطب'),
(4, 'رياضة', 'أخبار الرياضة والمباريات'),
(5, 'ترفيه', 'أخبار الفن والترفيه');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `dateposted` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `news_id`, `user_id`, `content`, `dateposted`) VALUES
(1, 91, 62, 'تعليق والحافظ الله\r\nيارب يزبط', '2025-05-29 08:53:25'),
(2, 91, 62, 'واوووووو هيوووو زابطططط', '2025-05-29 08:55:50'),
(3, 91, 62, 'اهدي نجاحي للجميع', '2025-05-29 08:55:58'),
(4, 91, 62, 'وبهيك تم انهاء اخر تعديل', '2025-05-29 08:56:11'),
(5, 93, 63, 'تعليق لضمان النجاح', '2025-05-29 09:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `dateposted` datetime NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `keywords` varchar(255) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `summary`, `body`, `image`, `dateposted`, `category_id`, `author_id`, `status`, `keywords`, `views`) VALUES
(62, 'ارتفاع النمو الاقتصادي إلى رقم قياسي جديد', '', 'أعلنت التقارير الرسمية تسجيل ارتفاع غير مسبوق في معدل النمو الاقتصادي للربع الأخير، مدفوعاً بزيادة الصادرات والاستثمار المحلي.', 'https://picsum.photos/800/600?random=1', '2025-05-10 09:12:34', 2, 1, 'approved', 'اقتصاد,نمو,محلي', 544),
(63, 'فوز الفريق المحلي بالبطولة', '', 'حسم الفريق المحلي المباراة النهائية بفارق نقطة واحدة وسط تشجيع حاشد من الجماهير.', 'https://picsum.photos/800/600?random=2', '2025-05-22 16:45:00', 4, 1, 'approved', 'رياضة,بطولة,فريق', 238),
(64, 'طرح نموذج هاتف ذكي جديد هذا الأسبوع', '', 'أطلقت الشركة المصنعة أحدث هاتف ذكي يتميز بشاشة عالية الدقة وعمر بطارية مطوّل.', 'https://picsum.photos/800/600?random=3', '2025-05-15 11:20:00', 3, 1, 'approved', 'تكنولوجيا,هاتف,جديد', 657),
(66, 'شركة ناشئة تحصل على تمويل بقيمة عشرة ملايين دولار', '', 'نجحت الشركة الناشئة في جولة تمويل A برئاسة مستثمرين محليين وأجانب.', 'https://picsum.photos/800/600?random=5', '2025-05-05 14:30:00', 2, 1, 'approved', 'اقتصاد,تمويل,شركات', 420),
(67, 'نهائي مثير يشهد عودة لا تصدق', '', 'شهدت المباراة النهائية عودة فريق كان متأخراً بهدفين وفاز في الدقائق الأخيرة.', 'https://picsum.photos/800/600?random=6', '2025-05-18 19:00:00', 4, 1, 'approved', 'رياضة,نهائي,إنجاز', 389),
(68, 'تقدم في أبحاث الذكاء الاصطناعي للتعرف على الصور', '', 'طور الباحثون خوارزمية جديدة قادرة على تمييز العناصر بدقة عالية ضمن الصور الفوتوغرافية.', 'https://picsum.photos/800/600?random=7', '2025-05-12 10:05:00', 3, 1, 'approved', 'ذكاء_اصطناعي,بحث,خوارزمية', 777),
(69, 'مهرجان موسيقي يجذب آلاف الزوار', '', 'احتضنت المدينة مهرجاناً موسيقياً حاشداً شارك فيه فنانون عالميون ومحليون.', 'https://picsum.photos/800/600?random=8', '2025-05-20 18:30:00', 5, 1, 'approved', 'موسيقى,مهرجان,فن', 512),
(70, 'الإعلان عن نتائج الانتخابات المحلية اليوم', '', 'أعلنت اللجنة الانتخابية النتائج النهائية للانتخابات البلدية وسط مشاركة عالية.', 'https://picsum.photos/800/600?random=9', '2025-05-07 12:00:00', 1, 1, 'approved', 'انتخابات,محلي,نتائج', 295),
(71, 'بدء تطبيق اللوائح المالية الجديدة', '', 'بدأت المؤسسات بتطبيق قواعد التقارير المالية وفق التعديلات التي أصدرتها السلطة المختصة.', 'https://picsum.photos/800/600?random=10', '2025-04-28 09:45:00', 2, 1, 'approved', 'مالية,لوائح,تقارير', 610),
(72, 'اختراق في تكنولوجيا الطاقة المتجددة', '', 'قدم العلماء تصميمًا أكثر كفاءة للألواح الشمسية يزيد من إنتاجية الطاقة بنسبة 20%.', 'https://picsum.photos/800/600?random=11', '2025-05-03 13:15:00', 3, 1, 'approved', 'طاقة_متجددة,بحث,بيئة', 847),
(73, 'مسيرة احتفالية بفوز البطولة', '', 'خرجت الجماهير في مسيرة للاحتفال بفوز فريق المدينة في البطولة الإقليمية.', 'https://picsum.photos/800/600?random=12', '2025-05-23 11:25:00', 4, 1, 'approved', 'رياضة,احتفال,مسيرة', 432),
(74, 'مؤتمر تقني يفتح آفاقاً جديدة', '', 'شارك في المؤتمر كبار رجال الأعمال والخبراء لمناقشة أحدث تقنيات المستقبل.', 'https://picsum.photos/800/600?random=13', '2025-05-09 10:50:00', 3, 1, 'approved', 'تقنية,مستقبل,مؤتمر', 994),
(75, 'عرض فيلم جديد يخطف الأنظار عالمياً', '', 'حقق الفيلم إيرادات قياسية في عطلة نهاية الأسبوع الأول من صدوره.', 'https://picsum.photos/800/600?random=14', '2025-05-16 20:10:00', 5, 1, 'approved', 'فيلم,سينما,عالمي', 317),
(76, 'مؤشرات اقتصادية تظهر نتائج متباينة', '', 'أظهرت البيانات انخفاض معدل التضخم مع ارتفاع طفيف في معدلات البطالة.', 'https://picsum.photos/800/600?random=15', '2025-05-01 08:30:00', 2, 1, 'approved', 'اقتصاد,تضخم,بطالة', 225),
(77, 'اختيارات المحررين لأفضل المقالات هذا الأسبوع', '', 'قدم محررونا مجموعة من المقالات البارزة في مختلف المجالات.', 'https://picsum.photos/800/600?random=16', '2025-05-24 09:00:00', 5, 1, 'approved', 'مقالات,تحرير,أسبوع', 187),
(78, 'بدء أعمال تجديد الحديقة العامة الشهر المقبل', '', 'أعلنت البلدية عن خطة لتطوير الحديقة العامة وتحديث المرافق والإنارة.', 'https://picsum.photos/800/600?random=17', '2025-05-11 07:45:00', 1, 1, 'approved', 'بلدية,تطوير,حدائق', 99),
(79, 'قمة قادة الأعمال تبحث استراتيجيات السوق', '', 'اجتمع عدد من كبار المسؤولين التنفيذيين لمناقشة خطط النمو والتوسع.', 'https://picsum.photos/800/600?random=18', '2025-05-14 14:00:00', 2, 1, 'approved', 'أعمال,قمة,استراتيجيات', 765),
(80, 'تأجيل المباراة النهائية بسبب الأمطار', '', 'أعلنت اللجنة المنظمة تأجيل المباراة المقررة بعد هطول أمطار غزيرة.', 'https://picsum.photos/800/600?random=19', '2025-05-19 15:30:00', 4, 1, 'approved', 'رياضة,تأجيل,طقس', 55),
(81, 'معرض علمي يعرض مشاريع طلابية مبتكرة', '', 'استضافت الجامعة معرضاً علمياً شارك فيه طلاب هندسة وأحياء بتجارب مميزة.', 'https://picsum.photos/800/600?random=20', '2025-05-06 10:20:00', 3, 1, 'approved', 'علم,طلاب,ابتكار', 659),
(82, 'مسرح المدينة يستضيف عرضاً درامياً جديداً', '', 'نال العرض إشادة واسعة من النقاد والجمهور على حد سواء.', 'https://picsum.photos/800/600?random=21', '2025-05-08 19:15:00', 5, 1, 'approved', 'مسرح,فن,عرض', 312),
(84, 'تحليل السوق يتوقع نمواً في الربع القادم', '', 'يتوقع خبراء الاقتصاد ارتفاع الإنفاق الاستهلاكي بنحو 5% خلال الأشهر المقبلة.', 'https://picsum.photos/800/600?random=23', '2025-05-13 12:30:00', 2, 1, 'approved', 'سوق,تحليل,نمو', 890),
(85, 'تحديث برمجي جديد يعزز الأمان السيبراني', '', 'أصدرت الشركة تحديثاً لمعالجة ثغرات أمنية في نظام التشغيل.', 'https://picsum.photos/800/600?random=24', '2025-05-17 16:00:00', 3, 1, 'approved', 'تقنية,أمان,برمجي', 734),
(86, 'حملة خيرية لدعم الأسر المحتاجة', '', 'دشنت مؤسسة محلية حملة لجمع التبرعات وتوفير المواد الغذائية.', 'https://picsum.photos/800/600?random=25', '2025-05-21 13:55:00', 5, 1, 'approved', 'خيري,مجتمع,تبرعات', 274),
(87, 'قمة الأعمال تركز على الاستدامة البيئية', '', 'ناقش المشاركون سبل تعزيز الممارسات الصديقة للبيئة في الشركات الكبرى.', 'https://picsum.photos/800/600?random=26', '2025-04-29 09:10:00', 2, 1, 'approved', 'أعمال,استدامة,بيئة', 607),
(88, 'مشروع مروري جديد يخفف الاختناقات المرورية', '', 'ستتم إضافة حارات جديدة وتحسين الإشارات لتسهيل حركة المرور.', 'https://picsum.photos/800/600?random=27', '2025-05-04 08:20:00', 1, 1, 'approved', 'مرور,مشروع,حركة', 143),
(89, 'إطلاق تطبيق هاتف مبتكر لتخطيط الرحلات', '', 'يوفر التطبيق ميزات جديدة مثل تحديد نقاط الاهتمام والمسارات الأكثر أماناً.', 'https://picsum.photos/800/600?random=28', '2025-05-25 17:45:00', 3, 1, 'approved', 'تطبيق,رحلات,هواتف', 994),
(90, 'افتتاح معرض فني للأعمال التشكيلية', '', 'عرض الفنانون لوحات ومنحوتات تعكس تجاربهم الفنية وأفكارهم.', 'https://picsum.photos/800/600?random=29', '2025-05-26 18:00:00', 5, 1, 'approved', 'فن,معرض,تشكيل', 345),
(91, 'خبراء ماليون يحذرون من تقلبات السوق', '', 'حذر خبراء ماليون من توقعات ضعف الاستقرار في الأسواق المالية ودعوا إلى تنويع الاستثمارات.', 'https://picsum.photos/800/600?random=30', '2025-05-27 09:30:00', 2, 1, 'approved', 'مالية,سوق,تقلبات', 484),
(92, 'المرصد السوري: أكثر من 20 غارة إسرائيلية على مواقع عسكرية في أنحاء البلاد هي \"الأعنف منذ بداية العام\"', 'قالت وكالة الأنباء السورية الرسمية (سانا)، مساء الجمعة، إن الجيش الإسرائيلي شن غارات جديدة استهدفت محيط ريف العاصمة دمشق، ومناطق بالقرب من حماة. وذكرت وسائل إعلام محلية هجمات أخرى طالت اللاذقية على الساحل الشمالي الغربي لسوريا ومنطقة إدلب.', 'وأفاد المرصد السوري لحقوق الإنسان بوقوع أكثر من 20 غارة إسرائيلية استهدفت مستودعات ومراكز عسكرية في جميع أنحاء سوريا مساء الجمعة.\r\n\r\nوأضاف أن الغارات \"الأعنف منذ بداية العام\" شملت جبل قاسيون وبرزة وحرستا بريف دمشق، وطالت تجمع دبابات في إزرع، والكتيبة الصاروخية في موثبين في درعا، وكتيبة الدفاع الجوي في جبل الشعرة في اللاذقية، كما تضمنت الأهداف ثكنات تدريب للفصائل في حماة وتلال الحمر الشمالية في القنيطرة.\r\n\r\nوبحسب المرصد السوري، فقد شنت إسرائيل منذ مطلع عام 2025، أكثر من 50 غارة استهدفت الأراضي السورية، منها 44 غارة جوية و 8 غارات برية، ما أدى إلى مقتل 33 شخصاً، وإصابة وتدمير نحو 79 هدفاً ما بين مستودعات للأسلحة والذخائر ومقرات ومراكز وآليات.', 'uploads/1748490073_6837d759dc44c.webp', '2025-05-28 00:00:00', 1, 1, 'approved', 'السوري , الإسرائيلي', 17),
(93, 'من هم دروز سوريا؟ وما الذي ينتظرهم في المشهد السوري الجديد؟', 'توسّعت خلال الأيام رقعة الاشتباكات التي شهدتها مناطق ذات أغلبية سكانية من الطائفة الدرزية في سوريا، لتصل إلى محافظة السويداء، وذلك بالتزامن مع انتشارٍ مسلّحٍ وتقارير عن سقوط مزيدٍ من الضحايا.', 'وأصدر مشايخ ووجهاء الطائفة الدرزية في سوريا بياناً على خلفية الأحداث، أكّدوا فيه رفضهم لتقسيم سوريا أو الانفصال عنها، مشدّدين على أن أبناء الطائفة \"جزء لا يتجزّأ من الوطن السوري\".\r\n\r\nوشهدت بعض المناطق ذات الكثافة السكانية الدرزية في سوريا اشتباكاتٍ متقطعةً بين مجموعات مسلّحة في حيّ جرمانا ومنطقة صحنايا، جنوب دمشق تحديداً، قالت وزارة الداخلية السورية إنها وقعت على \"خلفية تحريض وخطاب كراهية على مواقع التواصل الاجتماعي\".\r\n\r\nوتوصّل الطرفان – ممثلو الإدارة الجديدة في سوريا وممثلون عن الطائفة الدرزية – إلى اتفاق تهدئة في جرمانا وصحنايا، لم يصمد بسبب استمرار الاشتباكات.', 'https://ichef.bbci.co.uk/ace/ws/776/cpsprodpb/257c/live/7a679e60-fffb-11ef-9df0-a31b0cce0bcc.jpg.webp', '2025-05-28 00:00:00', 1, 1, 'approved', 'سوريا , الدروز', 36);

-- --------------------------------------------------------

--
-- Table structure for table `news_likes`
--

CREATE TABLE `news_likes` (
  `news_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('author','editor','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Rua', 'ruaidris000@gmail.com', '$2y$10$f76WwO4keI5vdqkCLJbIQOFNeSVPTM3mKKY3M1ZHniFyTD/OB2YHK', 'author'),
(2, 'Bob', 'bob@example.com', '$2y$10$LqEQuEHSap//J7dQDLSPKuDtqffy7LLc1bTiUg25v1LOsBis4xGHi', 'editor'),
(3, 'Lee', 'lee@example.com', '$2y$10$kbbdjSG.nU35fbM8VoSjmuVVK.6zPFfKaDTsXozAdhT.4AdMgLDeC', 'admin'),
(4, 'rita', 'rita@gmail.com', '$2y$10$ANsN5LQK13k3UDqxWi8WxugPYBmB8utBuX9XvQjyumtiY.q2pPws6', 'author'),
(62, 'rami', 'rami@gmail.com', '$2y$10$.ULcGyAdgvCFfq84vJ4vaOkpMiZECRBPqTg7U7oEz/91x.IYIfR.m', ''),
(63, 'sam', 'sam@gmail.com', '$2y$10$pgy9sI9QHR5sLE5LhE8Upu3/3tjIlVd9jAn8EXSS1tmaUArbTcgKu', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `news_likes`
--
ALTER TABLE `news_likes`
  ADD PRIMARY KEY (`news_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `news_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_likes`
--
ALTER TABLE `news_likes`
  ADD CONSTRAINT `news_likes_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `news_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
