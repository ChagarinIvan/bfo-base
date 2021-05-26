<?php

return [
    'club' => [
        'name' => 'Клюб',
        'persons_count' => 'Колькасць членаў',
    ],
    'persons' => [
        'title' => 'Члены федэрацыі',
    ],
    'navbar' => [
        'clubs' => 'Клюбы',
        'persons' => 'Члены федэрацыі',
        'competitions' => 'Спаборніцтвы',
        'no-ident' => 'Неідэнтыфікаваныя запісы пратаколаў',
        'flags' => 'Меткі этапаў',
        'faq' => 'Даведка',
        'api' => 'API',
    ],
    'ident' => [
        'edit' => [
            'title' => 'Звязаць выник и спартоўца',
        ]
    ],
    'common' => [
        'name' => 'Імя',
        'lastname' => 'Прозвішча',
        'birthday' => 'Дата нараджэння',
        'create' => 'Захаваць',
        'update' => 'Абнавіць',
        'new' => 'Дадаць',
        'delete' => 'Выдаліць',
        'cancel' => 'Адмена',
        'add_flags' => 'Рэдагаваць меткі',
        'edit_event' => 'Рэдагаванне этапу',
        'title' => 'Назва',
        'description' => 'Апісанне',
        'back' => 'Назад',
        'date' => 'Дата',
        'dates' => 'Даты',
        'competitors' => 'Колькасць удзельнікаў',
        'edit' => 'Рэдагаваць',
        'group' => 'Группа',
        'result' => 'Вынік',
        'place' => 'Месца',
        'points' => 'Ачкі',
        'complete_rank' => 'Выкананы разрад',
        'search' => 'Пошук',
    ],
    'competition' => 'Спаборніцтвы',
    'add_competition' => 'Дадаць спаборніцтвы',
    'protocol' => 'Пратакол',
    'protocol-hint' => 'Если не добавлять файл, то обновления участников не будет',
    'lang' => [
        'ru' => 'Рус',
        'by' => 'Бел',
    ],
    'events' => [
        'flags' => 'Меткі'
    ],
    'flags' => [
        'add_flags_title' => 'Редагаваць меткі этапа',
        'name' => 'Назва',
        'color' => 'Колер',
        'create' => [
            'title' => 'Дабаўленне новай меткі этапа',
        ],
        'edit' => [
            'title' => 'Рэдагаванне меткі этапа',
        ],
    ],
    'event' => 'Этап',
    'person' => [
        'create_button' => 'Дадаць асобу',
        'create_title' => 'Даданне асобы',
        'edit_title' => 'Рэдагаванне асобы',
    ],

    'faq' => [
        'description' => 'Апісанне працы сэрвісу',
        'create_competition' => 'Ствараем спаборніцтва',
        'add_events' => 'Дадаем дні спаборніцтва з пратаколамі (берэм з orient.by)',
        'store_event' => 'Пратакол у відзе набора строк (нумар, Прозвішча, Імя, вынік, месца..) трапляюць у базу',
        'parsing_start' => 'На пачатку кожнага часу пачынаецца працэс СУАДНОСІН усіх раней не "вызначаных" спартоўцаў з даданымі у сістему спартоўцамі (сатронка Члены Федэрацыі)',
        'relation_faq' => 'У выніку СУАДНОСІН некаторыя спартоўцы могуць быць не вызначанымі, хоць яны і ёсць у базе членаў федерацыі (напрыклад калі у пратаколе прозыішча або імя на іншай мове, або была змена прозвішча), тады есць магчымасць у пратаколе побач з прозвішчам спартоўца націснуць кнопку "add" і абраць сапращднага спартоўца. Дадзены "выпадак" будзе захаваны і будзе улічвацца пры усіх дальнейшых СУАДНОСІН',
        'relation_edit' => 'Таксама ёсць магчымасць адрэдагаваць няправільныя СУАДНОСІНЫ',
        'flags_adding' => 'Да атрыманнага дня спаборніцтв трэба прымацаваць адпаведныя меткі (меткі тыпу спаборніцтв, тыпу дыстанцыі, меткі кубкаў и г.д), што патрэбна для агрэгацыі спаборніцтв для атрымання статыстыкі па пэўнай прыкмеце',
        'api_link' => 'Дадзенныя магцыма выгрузіць па',
        'dev_plan' => 'План распрацоўкі',
        'dev_protocol' => 'Самая галоўная праца па даданню новых и старых пратаколаў и дапрацоўцы ПАРСЕРАЎ пад ніх',
        'dev_relation' => 'Праверка працы ІДЭНТЫФІКАТАРА (суаднісіны строк пратакола да рэальных людзей), и яго дапрацоўка',
        'dev_ui' => 'Дапрацоўка карыстальніцкага інтэрфейса дзеля зручнасці прагляду інфармацыі па спаборніцтвам, людзям, клубам',
        'dev_contact_us' => 'Даданне формы зваротнай сувязі',
        'dev_exports' => 'Даданне ЭКСПОРТАЎ са старонак пратаколаў у фармаце csv і xlsx',
    ],

    'no-ident-title' => 'Прыведзены неідентыфікаваныя вынікі спаборніцтваў',
    'up' => 'Уверх',
    'all' => 'Усё',

    'errors' => [
        'title' => 'Памылачка',
        'error' => 'Здарылася непрадбачанная памылка',
        'description' => 'Падробнае апісанне памылкі адпраўлена распрацоўніку',
        'next' => 'Ў бліжашы час памылка будзе папраўлена',
        'thanks' => 'Дзякуй за тое што карстаецеся нашым сервісам',
    ],
];
