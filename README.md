### Разработка:

1| Добавить репозиторий:

    git init
    git remote add origin git@github.com:stenfredd/BooksBackend.git
    git pull origin master

2| Скопировать .env.example и назвать .env

2.1| Заменить в нем __VALUE__ на соответсвующие полям значения

3| Запустить докер:

    sudo docker build --tag booksphp -f ./.docker/config/php/php.dockerfile .
    sudo docker-compose -f docker-compose.yml -f docker-compose.dev.yml build
    docker-compose -f docker-compose.yml -f docker-compose.dev.yml up

>Пояснение: имеются отдельные файлы docker-compose и Dockerfile для prod и dev. 
>В prod отсутсвует xdebug и все остальное, ненужное для работы в продакшне. 
>Также в docker-compose.prod.yml контейнеры запускаются с restart: always
>
>Сперва собирается основной общий php.dockerfile, после чего 
>docker-compose для dev и prod используют php.dev.dockerfile и php.prod.dockerfile соответственно, 
>которые, в свою очередь, используют общий php.dockerfile

4| Установить пакеты композера:

    composer install

5| Выполнив "docker ps" можно узнать имя контейнера php. Допустим, это будет "booksapp_php_1". После нужно подключиться к контейнеру, чтобы выполнить миграции и загрузить фикстуры:

    sudo docker exec -it "booksapp_php_1" /bin/bash

    php bin/console doctrine:migrations:migrate
    php bin/console doctrine:fixtures:load --group=app_start

6| Для работы тестов требуется создать тестовую базу данных:

    sudo docker exec -it "booksapp_php_1" /bin/bash
    
    php bin/console --env=test doctrine:database:create
    APP_ENV=test php bin/console doctrine:migrations:migrate
    APP_ENV=test php bin/console doctrine:fixtures:load --group=test

>Проект готов к работе. API доступен по http://127.0.0.1:8080. 
>
>Документация для запросов: http://localhost:8080/api/doc

	
### Боевой:

    Потребует только git, docker, docker-compose.

1| Если хотим, чтобы работал деплой через Github actions:
1.1| На своем пк содаем ключ:

    ssh-keygen -f "booksapp"
    ENTER
    ENTER
    (Passphrase оставляем пустой)

1.2| Отправляем публичную часть ключа на сервер (IP заменить на необходимый):

    ssh-copy-id root@00.000.000.000

1.3| Сохраняем в гитхаб ssh ключ:

    cat ~/.ssh/booksapp.pub
 - Копируем то, что получилось
 - Заходим в настройки репозитория => Secrets => New repository secret => Name - Любой, Value - то что скопировали

1.4| Создаем ключа НА СЕРВЕРЕ:

    ssh-keygen
    ENTER
    ENTER
    ENTER
    (Все оставляем по умолчанию)

1.5| Добавляем публичную часть ключа на гитхаб:

    cat ~/.ssh/id_rsa.pub
- Скопировать что получилось и добавить в настройки гитхаба https://github.com/settings/keys -> "New SSH key" (title - Любой, Key - то что скопировали)

>Пояснение: Мы создали ключ на своем пк, добавили его паблик на сервер, а приват на гитхаб, чтобы github actions мог подключаться к серверу. 
>Далее мы создали ключ на сервере и добавили паблик на гитхаб, чтобы уже сервер мог подключаться к гитхабу и получать обновления из репозитория.

2| Создаем рабочую директорию (предполагается, что проект будет крутиться именно в ней):

    mkdir -p /var/www/backend
    2.1| cd /var/www/backend

1| Добавить репозиторий:

    git init
    git remote add origin git@github.com:stenfredd/BooksBackend.git
    git pull origin master

2| Скопировать .env.example и назвать .env

2.1| Заменить в нем __VALUE__ на соответсвующие полям значения

3| Выполнить:

    sh deploy.sh
>Это выполнит все необходимое для старта сервера.

4| Подключиться к контейнеру php (в docker-compose.prod.yml его имя указано явно):

    sudo docker exec -it "back_php" /bin/bash
    
    php bin/console doctrine:migrations:migrate
    php bin/console doctrine:fixtures:load --group=app_start

>Backend готов к работе


## ПРОЧЕЕ


    SITE_NAME= Имя сайта (будет показываться клиенту, в т.ч. в письмах)
    EMAIL_SEND_FROM - основной email для отправки уведомлений
    ADMIN_EMAIL_FOR_NOTIFICATIONS - админский email для отправки уведомлений
    
    AUTH_TOKEN_LIFETIME= Срок годности токенов авторизации (в секундах)
    MAX_LOGIN_FAIL_COUNT= Количество неудачных попыток авторизации, до того, как пользователь будет заблокирован (в секундах)
    MAX_LOGIN_FAIL_PERIOD= Период, в который будут учитываться неудачные попытки авторизации (в секундах)
    LOGIN_FAIL_BLOCKING_TIME= Период блокировки пользователя (в секундах)
    ACTIVATION_TOKEN_LIFETIME= Срок годности токенов активации пользователя  (в секундах)
    PASSWORD_RESET_TOKEN_LIFETIME= Срок годности токенов сброса пароля (в секундах)
    ACTIVATION_LINK_SUCCESS_REDIRECT_TO= Урл для редиректа после активации пользователя
    ACTIVATION_LINK_FAIL_REDIRECT_TO= Урл для редиректа после НЕудачной активации пользователя
    RESET_PASSWORD_LINK_SUCCESS_REDIRECT_TO= Урл для редиректа после удачного сброса пароля
    RESET_PASSWORD_LINK_FAIL_REDIRECT_TO= Урл для редиректа после НЕудачного сброса пароля
