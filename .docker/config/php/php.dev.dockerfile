FROM booksphp

USER $USERNAME

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug