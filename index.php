<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

session_start();

$flash = $_SESSION['lead_flash'] ?? null;
$old = $_SESSION['lead_old'] ?? [];
$errors = $_SESSION['lead_errors'] ?? [];
unset($_SESSION['lead_flash'], $_SESSION['lead_old'], $_SESSION['lead_errors']);

if (isset($_GET['lead']) && $_GET['lead'] === 'success' && !$flash) {
    $flash = [
        'type' => 'success',
        'message' => 'Заявка отправлена. Мы свяжемся с вами по телефону.',
    ];
}

$services = [
    [
        'title' => 'Компьютерная диагностика',
        'text' => 'Считываем ошибки, проверяем датчики и помогаем понять причину неисправности до ремонта.',
        'type' => 'Диагностика',
        'status' => 'Запись',
        'what' => 'Подключаем диагностическое оборудование, считываем ошибки, смотрим показания датчиков и связываем их с реальными симптомами автомобиля.',
        'when' => 'Нужна, если горит ошибка на панели, машина потеряла тягу, плохо заводится, вырос расход или есть ощущение, что проблема есть, но непонятно где.',
        'why' => 'В «Угур» диагностика не заканчивается кодом ошибки: мастер объясняет, что проверять дальше и какие работы действительно имеют смысл.',
    ],
    [
        'title' => 'Двигатель и ГРМ',
        'text' => 'Ремонт двигателя, замена цепей и ремней ГРМ, работы с ГБЦ, турбиной и навесным оборудованием.',
        'type' => 'Мотор',
        'status' => 'Согласование',
        'what' => 'Работы с двигателем, цепями и ремнями ГРМ, ГБЦ, турбиной, навесным оборудованием и узлами, от которых зависит тяга и надежность мотора.',
        'when' => 'Обращайтесь при шуме под капотом, пропусках зажигания, течах, перегреве, падении мощности, дыме из выхлопа или плановой замене ГРМ.',
        'why' => 'Перед ремонтом согласуем объем и цену: в отзывах клиенты отдельно отмечают, что стоимость не менялась внезапно после работы.',
    ],
    [
        'title' => 'АКПП, КПП и трансмиссия',
        'text' => 'Диагностика и ремонт коробок, DSG, мостов и узлов трансмиссии.',
        'type' => 'Трансмиссия',
        'status' => 'Диагностика',
        'what' => 'Проверяем коробку, сцепление, DSG, приводы, мосты и другие узлы, которые передают момент от двигателя к колесам.',
        'when' => 'Нужна при рывках, задержке переключений, гуле на скорости, пробуксовке, вибрациях или ошибках по коробке.',
        'why' => 'Начинаем с диагностики и дорожных симптомов, чтобы не менять дорогие узлы без подтверждения причины.',
    ],
    [
        'title' => 'Ходовая и тормоза',
        'text' => 'Ремонт подвески, амортизаторов, тормозной системы и сопутствующих узлов.',
        'type' => 'Безопасность',
        'status' => 'Запись',
        'what' => 'Осматриваем подвеску, амортизаторы, рычаги, тормозные диски, колодки, суппорты и элементы, влияющие на управляемость.',
        'when' => 'Приезжайте при стуках, скрипах, уводе автомобиля, биении руля, длинном тормозном пути или вибрации при торможении.',
        'why' => 'Есть подъемники и рабочая зона для дефектовки: мастер показывает, что изношено, и согласует, что менять сейчас, а что можно наблюдать.',
    ],
    [
        'title' => 'Автоэлектрика',
        'text' => 'Поиск неисправностей, ремонт стартеров, генераторов и установка дополнительного оборудования.',
        'type' => 'Электрика',
        'status' => 'Осмотр',
        'what' => 'Ищем неисправности в цепях, датчиках, стартере, генераторе, зарядке, запуске и дополнительном оборудовании.',
        'when' => 'Подходит, если машина не заводится, садится аккумулятор, моргают ошибки, пропадает свет, не работает часть электроники.',
        'why' => 'Сначала локализуем участок проблемы, чтобы ремонт был точечным, а не превращался в замену всего подряд.',
    ],
    [
        'title' => 'Масло, фильтры и сервис',
        'text' => 'Базовое обслуживание, замена масла, расходников и плановые работы без лишних задержек.',
        'type' => 'ТО',
        'status' => 'Быстро',
        'what' => 'Плановое обслуживание: масло, фильтры, расходники, базовая проверка состояния автомобиля и подготовка к дальнейшей эксплуатации.',
        'when' => 'Нужно по регламенту, после покупки автомобиля, перед дальней поездкой или если давно не было нормального осмотра.',
        'why' => 'Можно совместить ТО с короткой проверкой слабых мест, чтобы заранее заметить будущие расходы.',
    ],
    [
        'title' => 'Топливная система и катализаторы',
        'text' => 'Ремонт топливной системы, работы с катализаторами и выхлопом.',
        'type' => 'Системы',
        'status' => 'Осмотр',
        'what' => 'Проверяем элементы подачи топлива, выхлопа, катализаторы и связанные симптомы по тяге, запаху, ошибкам и расходу.',
        'when' => 'Актуально при провалах при разгоне, запахе топлива или выхлопа, ошибках по смеси, повышенном расходе и потере мощности.',
        'why' => 'Сопоставляем ошибки с поведением машины, чтобы не лечить только последствия и не упустить основную причину.',
    ],
    [
        'title' => 'Шиномонтаж и выездные услуги',
        'text' => 'Шиномонтаж, правка и прокатка дисков, выездная диагностика и сервис по договоренности.',
        'type' => 'Колеса',
        'status' => 'По звонку',
        'what' => 'Шиномонтаж, работы с дисками, сезонная замена, а также выездная диагностика или сервис по предварительному согласованию.',
        'when' => 'Нужно при сезонной смене резины, вибрации после удара, повреждении диска или ситуации, когда автомобиль сложно доставить в сервис.',
        'why' => 'Условия выезда и время лучше уточнить по телефону: мастер сразу скажет, что можно решить на месте, а что требует сервиса.',
    ],
];

$heroRows = [
    ['car' => 'Peugeot 206', 'request' => 'ГРМ и двигатель', 'signal' => 'шум под капотом', 'next' => 'расчет по телефону', 'state' => 'Запись'],
    ['car' => 'BMW 5 series', 'request' => 'Диагностика', 'signal' => 'потеря тяги', 'next' => 'проверка ошибок', 'state' => 'Осмотр'],
    ['car' => 'Lada Vesta', 'request' => 'Ходовая', 'signal' => 'стук спереди', 'next' => 'подъемник', 'state' => 'Запись'],
    ['car' => 'Hyundai Solaris', 'request' => 'Тормоза', 'signal' => 'биение руля', 'next' => 'дефектовка', 'state' => 'Согласование'],
    ['car' => 'Volkswagen Tiguan', 'request' => 'DSG / КПП', 'signal' => 'рывки коробки', 'next' => 'диагностика', 'state' => 'Осмотр'],
];

$trustItems = [
    ['value' => SITE_RATING_VALUE, 'label' => 'рейтинг на Яндекс Картах'],
    ['value' => (string) SITE_RATING_COUNT, 'label' => 'оценки клиентов'],
    ['value' => (string) SITE_REVIEW_COUNT, 'label' => 'отзыва'],
    ['value' => '09:00-20:00', 'label' => 'ежедневно'],
];

$benefits = [
    ['value' => '31', 'label' => 'обслуживание', 'text' => 'Клиенты чаще всего отмечают внимательное отношение и понятное общение.'],
    ['value' => '18', 'label' => 'время ожидания', 'text' => 'Записываем заранее и предупреждаем, если работа требует больше времени.'],
    ['value' => '14', 'label' => 'ремонт', 'text' => 'Работы согласуются до начала, без внезапной смены цены в конце.'],
];

$steps = [
    ['title' => 'Начните с описания', 'text' => 'Марка авто, симптом и удобное время. Этого достаточно, чтобы мастер понял направление диагностики.'],
    ['title' => 'Получите расчет', 'text' => 'По телефону сориентируем по работам, деталям и ближайшему окну записи.'],
    ['title' => 'Согласуйте ремонт', 'text' => 'После осмотра объясним причину, объем работ и стоимость до начала ремонта.'],
];

$reviews = [
    ['name' => 'Леон', 'date' => '12 января 2026', 'text' => 'Попросил поменять цепь ГРМ, все сделали за день. Цена после замены не изменилась, машина стала ехать резвее.'],
    ['name' => 'elgun i.', 'date' => '7 января 2026', 'text' => 'Провели диагностику в кратчайшие сроки. Работы выполнены качественно и в оговоренные сроки, цена оказалась адекватной.'],
    ['name' => 'Г.К.', 'date' => '15 ноября 2025', 'text' => 'Ребята приехали поздно вечером после работы, все сделали быстро и качественно. В общении приятные, порядочные люди.'],
];

$brands = ['Lada', 'УАЗ', 'ВАЗ', 'Toyota', 'Nissan', 'Kia', 'Hyundai', 'Renault', 'Volkswagen', 'Audi', 'BMW', 'Mercedes-Benz', 'Mazda', 'Ford', 'Chevrolet', 'Opel', 'Mitsubishi', 'Subaru', 'Honda', 'Chery', 'Geely', 'Haval'];

$diagnosticCases = [
    ['symptom' => 'Горит ошибка', 'check' => 'Сканер, датчики, питание', 'result' => 'Понимаем, что проверять первым'],
    ['symptom' => 'Стук в ходовой', 'check' => 'Подъемник, люфты, тормоза', 'result' => 'Показываем изношенный узел'],
    ['symptom' => 'Рывки коробки', 'check' => 'Ошибки, масло, тест-проезд', 'result' => 'Отделяем механику от электроники'],
    ['symptom' => 'Замена ГРМ', 'check' => 'Регламент, состояние, доступ', 'result' => 'Согласуем объем до начала работ'],
];

$faqs = [
    ['q' => 'Можно ли узнать цену до приезда?', 'a' => 'Да. По телефону можно получить предварительный расчет. Точную стоимость мастер назовет после диагностики и согласования объема работ.'],
    ['q' => 'Нужно ли записываться заранее?', 'a' => 'Лучше записаться заранее: так проще подобрать время и подготовить подъемник или диагностическое оборудование.'],
    ['q' => 'Работаете с иномарками и отечественными авто?', 'a' => 'Да. В карточке сервиса указаны российские, европейские, корейские, японские, китайские и другие марки.'],
    ['q' => 'Можно оплатить картой?', 'a' => 'Да, в карточке бизнеса указана оплата картой.'],
    ['q' => 'Есть выездная диагностика?', 'a' => 'Да, выездная диагностика и выездной сервис указаны среди услуг. Условия лучше уточнить по телефону.'],
    ['q' => 'Где находится сервис?', 'a' => 'Струнино, ул. Фрунзе, 15А. В блоке контактов есть кнопка построения маршрута в Яндекс Картах.'],
];

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'AutoRepair',
    'name' => SITE_NAME,
    'description' => 'Автосервис Угур в Струнино: диагностика, ремонт двигателя, ГРМ, АКПП, КПП, ходовой, тормозов, автоэлектрики и шиномонтаж.',
    'url' => site_url(),
    'telephone' => SITE_PHONE,
    'image' => site_url('assets/images/ugur-workshop.jpg'),
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'улица Фрунзе, 15А',
        'addressLocality' => 'Струнино',
        'addressRegion' => 'Владимирская область',
        'addressCountry' => 'RU',
    ],
    'geo' => [
        '@type' => 'GeoCoordinates',
        'latitude' => SITE_LATITUDE,
        'longitude' => SITE_LONGITUDE,
    ],
    'openingHoursSpecification' => [[
        '@type' => 'OpeningHoursSpecification',
        'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        'opens' => '09:00',
        'closes' => '20:00',
    ]],
    'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => SITE_RATING_VALUE,
        'ratingCount' => SITE_RATING_COUNT,
        'reviewCount' => SITE_REVIEW_COUNT,
    ],
    'sameAs' => [SITE_MAP_URL],
];

$pageTitle = 'Угур — автосервис в Струнино | Диагностика и ремонт авто';
$pageDescription = 'Автосервис Угур в Струнино, ул. Фрунзе, 15А. Ремонт двигателя, ГРМ, ходовой, тормозов, АКПП, автоэлектрики и шиномонтаж. Рейтинг 5.0 на Яндекс Картах.';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape_html($pageTitle) ?></title>
    <meta name="description" content="<?= escape_html($pageDescription) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= escape_html(site_url()) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="<?= escape_html($pageTitle) ?>">
    <meta property="og:description" content="<?= escape_html($pageDescription) ?>">
    <meta property="og:url" content="<?= escape_html(site_url()) ?>">
    <meta property="og:image" content="<?= escape_html(site_url('assets/images/ugur-workshop.jpg')) ?>">
    <meta name="theme-color" content="#05070d">
    <link rel="icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="./assets/images/ugur-workshop.jpg">
    <link rel="stylesheet" href="./assets/css/styles.css">
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?></script>
</head>
<body>
    <a class="skip-link" href="#main">Перейти к содержанию</a>

    <header class="site-header">
        <div class="site-shell header-inner">
            <a class="brand" href="./" aria-label="Угур, главная">
                <span class="brand-dot" aria-hidden="true"></span>
                <strong><?= SITE_NAME ?></strong>
            </a>
            <nav class="main-nav" aria-label="Основная навигация">
                <a href="#services">Услуги</a>
                <a href="#workflow">Процесс</a>
                <a href="#proof">Отзывы</a>
                <a href="#contacts">Контакты</a>
            </nav>
            <div class="header-actions">
                <a href="tel:<?= SITE_PHONE_TEL ?>">Звонок</a>
                <a class="button button-light button-small" href="#lead-form">Расчет</a>
            </div>
        </div>
    </header>

    <main id="main">
        <section class="hero-section">
            <div class="site-shell hero-shell">
                <div class="hero-copy">
                    <p class="eyebrow">Автосервис в Струнино</p>
                    <h1>Угур — автосервис, который сначала разбирается в причине</h1>
                    <div class="hero-actions">
                        <a class="button button-light" href="tel:<?= SITE_PHONE_TEL ?>">Позвонить <?= SITE_PHONE ?></a>
                        <a class="button button-ghost" href="#lead-form">Получить расчет</a>
                    </div>
                </div>
                <div class="hero-note">
                    <p>Диагностика, ремонт двигателя, ГРМ, ходовой, тормозов, АКПП и автоэлектрики. Мастер уточнит симптомы, сориентирует по работам и запишет на удобное время.</p>
                    <dl>
                        <div>
                            <dt>Адрес</dt>
                            <dd><?= SITE_SHORT_ADDRESS ?></dd>
                        </div>
                        <div>
                            <dt>График</dt>
                            <dd><?= SITE_WORK_HOURS ?></dd>
                        </div>
                    </dl>
                </div>

                <div class="diagnostic-board" aria-label="Диагностическая ведомость">
                    <div class="board-topline">
                        <div>
                            <span class="board-kicker">Live diagnostic board</span>
                            <strong>Ведомость обращений</strong>
                        </div>
                        <a href="<?= SITE_MAP_URL ?>" target="_blank" rel="noopener">Яндекс Карты</a>
                    </div>
                    <div class="board-tabs" aria-hidden="true">
                        <span class="is-active">Диагностика</span>
                        <span>Двигатель</span>
                        <span>Ходовая</span>
                        <span>Электрика</span>
                        <span>Шиномонтаж</span>
                    </div>
                    <div class="work-table" role="table" aria-label="Примеры обращений в автосервис">
                        <div class="work-row work-head" role="row">
                            <span role="columnheader">Авто</span>
                            <span role="columnheader">Запрос</span>
                            <span role="columnheader">Симптом</span>
                            <span role="columnheader">Следующий шаг</span>
                            <span role="columnheader">Статус</span>
                        </div>
                        <?php foreach ($heroRows as $row): ?>
                            <div class="work-row" role="row">
                                <span role="cell"><?= escape_html($row['car']) ?></span>
                                <span role="cell"><?= escape_html($row['request']) ?></span>
                                <span role="cell"><?= escape_html($row['signal']) ?></span>
                                <span role="cell"><?= escape_html($row['next']) ?></span>
                                <span role="cell"><mark><?= escape_html($row['state']) ?></mark></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-gridline services-section" id="services">
            <div class="site-shell">
                <div class="services-intro">
                    <p class="eyebrow">Услуги</p>
                    <h2>Выберите ситуацию, с которой приехать в «Угур»</h2>
                    <p>Второй блок сразу отвечает на главное: что входит в услугу, когда она нужна и почему с этим стоит обратиться именно к нам.</p>
                </div>

                <div class="service-detail-grid" aria-label="Подробные услуги автосервиса">
                    <?php foreach ($services as $service): ?>
                        <article class="service-detail-card">
                            <div class="service-card-head">
                                <span><?= escape_html($service['type']) ?></span>
                                <mark><?= escape_html($service['status']) ?></mark>
                            </div>
                            <h3><?= escape_html($service['title']) ?></h3>
                            <p class="service-summary"><?= escape_html($service['text']) ?></p>
                            <div class="service-answer-list">
                                <div>
                                    <strong>Что за услуга</strong>
                                    <p><?= escape_html($service['what']) ?></p>
                                </div>
                                <div>
                                    <strong>Для кого и когда</strong>
                                    <p><?= escape_html($service['when']) ?></p>
                                </div>
                                <div>
                                    <strong>Почему у нас</strong>
                                    <p><?= escape_html($service['why']) ?></p>
                                </div>
                            </div>
                            <a class="service-link" href="#lead-form">Обсудить услугу</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="trust-bar" aria-label="Данные из карточки бизнеса">
            <div class="site-shell trust-grid">
                <?php foreach ($trustItems as $item): ?>
                    <div>
                        <strong><?= escape_html($item['value']) ?></strong>
                        <span><?= escape_html($item['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section section-gridline">
            <div class="site-shell editorial-pair">
                <div>
                    <p class="eyebrow">Проблема</p>
                    <h2>Не меняем детали наугад. Сначала ищем, что именно сломалось.</h2>
                    <ul class="quiet-list">
                        <li>Стук в подвеске или биение при торможении.</li>
                        <li>Рывки коробки, потеря тяги, ошибки на панели.</li>
                        <li>Шум под капотом, течи, проблемы с запуском.</li>
                    </ul>
                </div>
                <div class="interface-card split-card">
                    <div class="mini-form">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="mini-report">
                        <strong>Результат диагностики</strong>
                        <p>Причина, список работ и предварительный расчет до начала ремонта.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section workflow-section" id="workflow">
            <div class="site-shell">
                <div class="section-lead-copy narrow">
                    <p class="eyebrow">Как идет работа</p>
                    <h2>Три шага от симптома к согласованному ремонту</h2>
                </div>
                <div class="workflow-grid">
                    <?php foreach ($steps as $index => $step): ?>
                        <article class="workflow-card">
                            <div class="step-line">
                                <span class="<?= $index === 0 ? 'is-active' : '' ?>">Шаг <?= $index + 1 ?></span>
                            </div>
                            <h3><?= escape_html($step['title']) ?></h3>
                            <p><?= escape_html($step['text']) ?></p>
                            <div class="workflow-preview" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section section-gridline">
            <div class="site-shell photo-module">
                <div class="photo-panel blue-panel">
                    <img src="./assets/images/ugur-workshop.jpg" alt="Рабочая зона автосервиса Угур с подъемниками и автомобилями" loading="lazy" width="800" height="600">
                </div>
                <div class="section-lead-copy">
                    <p class="eyebrow">Реальная мастерская</p>
                    <h2>Фото не играют фон. Они доказывают, что сервис живой.</h2>
                    <p>Подъемники, диагностическое оборудование и машины в работе используются как подтверждение, а не как декоративная заставка.</p>
                    <div class="small-photo-row">
                        <img src="./assets/images/ugur-diagnostic.jpg" alt="Диагностическое оборудование для разных марок автомобилей" loading="lazy" width="640" height="590">
                        <img src="./assets/images/ugur-engine.jpg" alt="Ремонт двигателя в автосервисе" loading="lazy" width="640" height="820">
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-gridline" id="proof">
            <div class="site-shell proof-layout">
                <div>
                    <p class="eyebrow">Данные отзывов</p>
                    <h2>Клиенты чаще всего отмечают обслуживание, ожидание и сам ремонт</h2>
                </div>
                <div class="metrics-list">
                    <?php foreach ($benefits as $benefit): ?>
                        <article>
                            <strong><?= escape_html($benefit['value']) ?></strong>
                            <span><?= escape_html($benefit['label']) ?></span>
                            <p><?= escape_html($benefit['text']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="site-shell reviews-row">
                <?php foreach ($reviews as $review): ?>
                    <figure>
                        <blockquote>«<?= escape_html($review['text']) ?>»</blockquote>
                        <figcaption><?= escape_html($review['name']) ?> · <?= escape_html($review['date']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section section-gridline">
            <div class="site-shell brand-layout">
                <div>
                    <p class="eyebrow">Марки</p>
                    <h2>Работаем с отечественными и импортными автомобилями</h2>
                </div>
                <div class="brand-matrix" aria-label="Марки автомобилей">
                    <?php foreach ($brands as $brand): ?>
                        <span><?= escape_html($brand) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section section-gridline">
            <div class="site-shell accuracy-layout">
                <div>
                    <p class="eyebrow">Точность как база</p>
                    <h2>Сначала диагностика. Потом решение.</h2>
                    <ul class="quiet-list">
                        <li>Не обещаем скидки и бесплатные работы без подтверждения.</li>
                        <li>Стоимость ремонта согласуется до начала работ.</li>
                        <li>Запись и предварительный расчет доступны по телефону.</li>
                    </ul>
                </div>
                <div class="diagnostic-panel" aria-label="Как симптомы превращаются в план диагностики">
                    <div class="diagnostic-panel-head">
                        <span>Что беспокоит автомобиль?</span>
                        <strong>От симптома к понятному плану</strong>
                    </div>
                    <div class="diagnostic-table">
                        <div class="diagnostic-row diagnostic-row-head">
                            <span>Симптом</span>
                            <span>Что проверяем</span>
                            <span>Что получаете</span>
                        </div>
                        <?php foreach ($diagnosticCases as $case): ?>
                            <div class="diagnostic-row">
                                <span><?= escape_html($case['symptom']) ?></span>
                                <span><?= escape_html($case['check']) ?></span>
                                <span><?= escape_html($case['result']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a class="diagnostic-panel-link" href="#lead-form">Описать свой симптом</a>
                </div>
            </div>
        </section>

        <section class="section section-gridline">
            <div class="site-shell faq-layout">
                <div>
                    <p class="eyebrow">FAQs</p>
                    <h2>Частые вопросы</h2>
                    <p>Если вопрос срочный, лучше позвонить напрямую: <?= SITE_PHONE ?>.</p>
                </div>
                <div class="faq-list">
                    <?php foreach ($faqs as $faq): ?>
                        <details>
                            <summary><?= escape_html($faq['q']) ?></summary>
                            <p><?= escape_html($faq['a']) ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dark-cta" id="contacts">
            <div class="site-shell dark-cta-inner">
                <div>
                    <p class="eyebrow">Запись без лишнего шума</p>
                    <h2>Получите предварительный расчет по телефону</h2>
                    <a class="button button-light" href="tel:<?= SITE_PHONE_TEL ?>">Позвонить <?= SITE_PHONE ?></a>
                </div>
                <dl>
                    <div>
                        <dt>Адрес</dt>
                        <dd><?= SITE_ADDRESS ?></dd>
                    </div>
                    <div>
                        <dt>График</dt>
                        <dd><?= SITE_WORK_HOURS ?></dd>
                    </div>
                    <div>
                        <dt>Маршрут</dt>
                        <dd><a href="<?= SITE_MAP_URL ?>" target="_blank" rel="noopener">Открыть Яндекс Карты</a></dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="section number-cta">
            <div class="site-shell">
                <p>Рейтинг сервиса на Яндекс Картах</p>
                <strong><?= SITE_RATING_VALUE ?></strong>
                <a class="button button-dark" href="#lead-form">Оставить заявку</a>
            </div>
        </section>

        <section class="section section-lead" id="lead-form">
            <div class="site-shell lead-layout">
                <div class="lead-copy">
                    <p class="eyebrow">Форма заявки</p>
                    <h2>Опишите, что случилось с машиной</h2>
                    <p>Оставьте телефон, выберите услугу и добавьте пару деталей. Мастер свяжется с вами и подскажет предварительный расчет или время записи.</p>
                    <div class="map-card" aria-label="Карта расположения">
                        <iframe title="Угур на Яндекс Картах" src="https://yandex.ru/map-widget/v1/?ll=38.588130%2C56.371456&mode=search&oid=19226243313&ol=biz&z=16" loading="lazy"></iframe>
                    </div>
                </div>
                <form class="lead-form interface-card" action="./lead.php" method="post" novalidate data-lead-form>
                    <?php if ($flash): ?>
                        <div class="form-alert form-alert-<?= escape_html($flash['type']) ?>" role="status">
                            <?= escape_html($flash['message']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-alert form-alert-js" hidden role="status"></div>

                    <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                    <input type="hidden" name="source" value="landing">
                    <label class="honeypot">
                        Не заполняйте это поле
                        <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
                    </label>

                    <div class="field">
                        <label for="name">Ваше имя <span>(необязательно)</span></label>
                        <input id="name" name="name" type="text" autocomplete="name" value="<?= escape_html($old['name'] ?? '') ?>">
                        <?php if (!empty($errors['name'])): ?><p class="field-error"><?= escape_html($errors['name']) ?></p><?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="phone">Телефон</label>
                        <input id="phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+7 900 000-00-00" value="<?= escape_html($old['phone'] ?? '') ?>" aria-describedby="phone-help">
                        <p id="phone-help" class="field-help">Нужен только для связи по заявке.</p>
                        <?php if (!empty($errors['phone'])): ?><p class="field-error"><?= escape_html($errors['phone']) ?></p><?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="service">Что нужно сделать?</label>
                        <select id="service" name="service">
                            <option value="">Выберите услугу</option>
                            <?php foreach ($services as $service): ?>
                                <?php $selected = (($old['service'] ?? '') === $service['title']) ? 'selected' : ''; ?>
                                <option value="<?= escape_html($service['title']) ?>" <?= $selected ?>><?= escape_html($service['title']) ?></option>
                            <?php endforeach; ?>
                            <option value="Другое" <?= (($old['service'] ?? '') === 'Другое') ? 'selected' : '' ?>>Другое</option>
                        </select>
                        <?php if (!empty($errors['service'])): ?><p class="field-error"><?= escape_html($errors['service']) ?></p><?php endif; ?>
                    </div>

                    <div class="field">
                        <label for="message">Что происходит с автомобилем? <span>(необязательно)</span></label>
                        <textarea id="message" name="message" rows="4" placeholder="Например: стук в подвеске, горит ошибка, нужно заменить ГРМ"><?= escape_html($old['message'] ?? '') ?></textarea>
                        <?php if (!empty($errors['message'])): ?><p class="field-error"><?= escape_html($errors['message']) ?></p><?php endif; ?>
                    </div>

                    <label class="checkbox">
                        <input type="checkbox" name="consent" value="1" <?= !empty($old['consent']) ? 'checked' : '' ?>>
                        <span>Согласен на обработку данных для связи по заявке</span>
                    </label>
                    <?php if (!empty($errors['consent'])): ?><p class="field-error checkbox-error"><?= escape_html($errors['consent']) ?></p><?php endif; ?>

                    <button class="button button-dark form-submit" type="submit">Получить расчет</button>
                    <p class="form-note">Можно быстрее: <a href="tel:<?= SITE_PHONE_TEL ?>">позвонить <?= SITE_PHONE ?></a>.</p>
                </form>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="site-shell footer-grid">
            <div>
                <a class="brand footer-brand" href="./" aria-label="Угур, главная">
                    <span class="brand-dot" aria-hidden="true"></span>
                    <strong><?= SITE_NAME ?></strong>
                </a>
                <h2>Ремонт без догадок</h2>
            </div>
            <div class="footer-note">
                <p>Данные о рейтинге, графике и услугах сверены с Яндекс Картами: <?= SITE_VERIFIED_DATE ?>.</p>
                <a class="button button-light button-small" href="#lead-form">Получить расчет</a>
            </div>
            <nav aria-label="Нижняя навигация">
                <a href="#services">Услуги</a>
                <a href="#workflow">Процесс</a>
                <a href="#proof">Отзывы</a>
                <a href="#contacts">Контакты</a>
            </nav>
            <address>
                <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a><br>
                <?= SITE_SHORT_ADDRESS ?><br>
                <?= SITE_WORK_HOURS ?>
            </address>
        </div>
    </footer>

    <div class="mobile-cta" aria-label="Быстрые действия">
        <a href="tel:<?= SITE_PHONE_TEL ?>">Позвонить</a>
        <a href="#lead-form">Записаться</a>
    </div>

    <script src="./assets/js/main.js" defer></script>
</body>
</html>
