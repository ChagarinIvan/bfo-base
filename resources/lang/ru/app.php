<?php

return [
    'club' => [
        'name' => 'Клуб',
        'persons_count' => 'Количество членов',
    ],
    'navbar' => [
        'clubs' => 'Клубы',
        'cups' => 'Кубок',
        'persons' => 'Члены федерации',
        'competitions' => 'Соревнования',
        'no-ident' => 'Неидентифицированные записи протоколов',
        'flags' => 'Метки этапов',
        'faq' => 'Справка',
        'api' => 'API',
    ],
    'ident' => [
        'edit' => [
            'title' => 'Привязать результат к спортсмену',
        ]
    ],
    'common' => [
        'year' => 'Год',
        'name' => 'Имя',
        'fio' => 'Фамилия, Имя',
        'lastname' => 'Фамилия',
        'birthday' => 'Дата рождения',
        'create' => 'Создать',
        'new' => 'Добавить',
        'delete' => 'Удалить',
        'update' => 'Обновить',
        'cancel' => 'Отмена',
        'search' => 'Поиск',
        'add_flags' => 'Редактировать метки',
        'title' => 'Название',
        'description' => 'Описание',
        'edit' => 'Редактировать',
        'back' => 'Назад',
        'date' => 'Дата',
        'dates' => 'Даты',
        'competitors' => 'Число участников',
        'group' => 'Группа',
        'groups' => 'Группы',
        'result' => 'Результат',
        'place' => 'Место',
        'points' => 'Очки',
        'complete_rank' => 'Выполненный разряд',
        'event_number' => 'Количество этапов',
        'save' => 'Сохранить',
        'complete' => 'Выполнил',
        'time' => 'Время',
        'rank' => 'Разряд',
        'type' => 'Тип',
    ],
    'protocol' => 'Протокол',
    'competition' => [
        'title' => 'Соревнование',
        'add' => 'Добавление соревнивания',
        'description' => 'Описание',
        'name' => 'Название соревнования',
        'from_date' => 'Дата начала',
        'to_date' => 'Дата окончания',
        'add_competition' => 'Добавить соревнование',
        'add_event' => 'Добавить этап',
    ],
    'protocol-hint' => 'Если не добавлять файл, то обнаўления участников не будет',
    'event' => [
        'flags' => 'Метки',
        'title' => 'Этап',
        'add' => 'Добавлене этапа',
        'edit' => 'Редактирование этапа',
    ],
    'lang' => [
        'ru' => 'Рус',
        'by' => 'Бел',
    ],
    'flags' => [
        'add_flags_title' => 'Редактировать метки этапа',
        'name' => 'Название',
        'color' => 'Цвет',
        'create' => [
            'title' => 'Добавление новой метки этапа',
        ],
        'edit' => [
            'title' => 'Редактирование метки этапа',
        ],
    ],
    'person' => [
        'create_button' => 'Добавить человека',
        'create_title' => 'Добавление человека',
        'edit_title' => 'Редактирование человека',
    ],

    'faq' => [
        'description' => 'Описание работы',
        'create_competition' => 'Создаем соревноване',
        'add_events' => 'Добавляем дни соренований с протоколами (берем с orient.by)',
        'store_event' => 'Протокол в виде набора строк (номер, Фамилия, Имя, результат, место..) сохраняется в базу',
        'parsing_start' => 'В начале каждого часа запускается процесс СООТНЕСЕНИЯ всех ранее не "определенных" спортсменов с добавленными спортсменами (страница Члены Федерации)',
        'relation_faq' => 'В результате СООТНЕСЕНИЯ некоторые спортсмены могут не определится, хотя в базе спартсменов они есть (например если в протоколе фамилия на другом языке, или произошла смена фамилии), тогда есть возможность в протоколе рядом с фамилией спортсмена нажать кнопку "add" и выбрать действительного спортсмена. Данный "кейс" будет также сохранен и будет учитыватся при всех последующих СООТНЕСЕНИЯХ',
        'relation_edit' => 'Так же есть возможность отредактировать неправильное СООТНЕСЕНИЕ',
        'flags_adding' => 'К полученному дню соревнований, нужно прикрепить соответствующие метки (метки типа соревнований, типа дистанции, метки кубков и т.д), что необходимо для аггрегации соревнований для получения статистики по определенному признаку',
        'api_link' => 'Данные можно выгрузить по',
        'dev_plan' => 'План разработки',
        'dev_protocol' => 'Самая главная работа по добавлению новых и старых протоколов и доработке ПАРСЕРОВ под них',
        'dev_relation' => 'Проверка работы ИДЕНТИФИКАТОРА (привязка сторок протоколов к реальным людям), и его доработка',
        'dev_ui' => 'Доработка пользовательского интерфейса для удобства просмотра инфы по сревнованиям, людям, клубам',
        'dev_contact_us' => 'Добавление формы обратной связи',
        'dev_exports' => 'Добавление ЭКСПОРТОВ из страниц протоколов в форматах csv и xlsx',
    ],

    'no-ident-title' => 'Представлены неидентифированные результаты',
    'up' => 'Вверх',
    'all' => 'Всё',
    'cups' => 'Кубки Федерации',
    'cup' => [
        'add' => 'Добавление кубка',
        'edit' => 'Редактирование кубка',
        'name' => 'Название кубка',
        'events' => 'Этапы',
        'table' => 'Таблица',
        'events_count' => 'Количество зачетных стартов',
    ],
    'errors' => [
        'title' => 'Ошибочка',
        'error' => 'Произошла непредвиденная ошибка',
        'description' => 'Подробное описание ошибки отправленно разработчику',
        'next' => 'В ближайшее время ошибка будет исправленно',
        'thanks' => 'Спасибо, что используете Наш сервис',
    ],
];
