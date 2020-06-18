# pdf-line-calc
### Зависимости:
На сервере должны быть установлены пакеты:

* `php-imagick`;

* `inkscape` версии `>= 1.0`;

### API:

`POST` `/` принимает параметры:

`GET` `json` - ответ будет в json-формате - массив $pages той же структуры, 
что и передавалась в шаблон: 
`[{"src":"","svg":""},{"src":"","svg":""},...]`

`POST` `user` - строка произвольного формата для идентификации пользователя, 
которая будет записываться в лог. Может содержать ip, имя или id пользователя, или всё вместе.