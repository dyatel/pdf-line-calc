<?php
ini_set('display_errors', 1);
error_reporting(E_ERROR);

// Пути к каталогам загрузки. Должны быть доступны для чтения и записи веб-серверу
const UPLOAD_ORIGINAL = __DIR__."/uploads/";
const UPLOAD_SVG_SRC = "/uploads/svg/";
const UPLOAD_SVG = __DIR__.UPLOAD_SVG_SRC;

// получаем файл, если пришел
$file = $_FILES['upload'];

// определяем шаблонные переменные
$pages = (function ($file) {
    $pages = [];
    //
    if($file){
        // копируем файл в uploads
        if(!copy($file['tmp_name'], UPLOAD_ORIGINAL.$file['name']))
            throw new Exception("Unable copy file to uploads directory");

        // разбиваем на страницы
        $img = new Imagick();
        $img->pingImage(UPLOAD_ORIGINAL.$file['name']);

        for($p = 1; $p <= $img->getNumberImages(); $p++){
            // конвертируем в svg при помощи inkscape
            exec(
                    sprintf(
                            'inkscape -l --export-filename="%1$s%3$s_%4$d.svg" --pdf-page=%4$d "%2$s%3$s" 2>&1',
                            UPLOAD_SVG, UPLOAD_ORIGINAL, $file['name'], $p
                    ),
                    $out,
                    $return
            );
            // если скрипт вернул не 0, отображаем вывод
            if($return){
                var_dump('<pre>', $out, '</pre>');
                return $pages;
            }
            // добавляем страницу
            $pages[] = [
                'src' => UPLOAD_SVG_SRC."{$file['name']}_{$p}.svg",
                'svg' => file_get_contents(UPLOAD_SVG."{$file['name']}_{$p}.svg")
            ];
        }
    }
    //
    return $pages;
})($file);
//
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>CalcLine</title>
    <style> div{ margin: 10px 0; } </style>
    <script>
        // функция расчета длины путей в svg
        function calcLine($page){
            // убираем все зависимости
            document.querySelectorAll('.svg_'+$page+' svg defs').forEach(function(el){
                el.remove();
            });

            // задаем коэффициент перевода пикселей в миллиметры
            var ratio = 0.35285019885108265,
                length = 0;

            // считаем длину всех путей
            document.querySelectorAll('.svg_'+$page+' svg g path').forEach(function(p){
                if(p.style.fill === 'none')
                    length += p.getTotalLength();
            });

            // выводим результат
            document.querySelector('.in_px_result_'+$page).innerHTML = Math.round(length);
            document.querySelector('.in_mm_result_'+$page).innerHTML = Math.round(length*ratio);
        }
    </script>
</head>
<body>
    <div>
        <strong>Выберите файл (.pdf, .ai, .eps):</strong>
    </div>

    <form method="post" enctype="multipart/form-data" action="/">
        <input type="file" name="upload" accept="application/pdf,application/postscript">
        <input type="submit" value="Загрузить">
    </form>

    <?php if(count($pages)):?>
        <?php foreach($pages as $page_num => $page):?>
            <br><br><hr>
            <!--  Результат  -->
            <div>
                <strong><?=$page_num+1?>. Длина реза: <span class="in_mm_result_<?=$page_num?>">0</span> mm, <span class="in_px_result_<?=$page_num?>">0</span> px</strong>
            </div>
            <!--  Картинка для наглядности  -->
            <img width="1000" src="<?=$page['src']?>" style="border: 1px solid red" alt="">
            <!--  Скрытый элемент с данными svg  -->
            <div class="svg_<?=$page_num?>" style="display: none"><?=$page['svg']?></div>
            <!---->
            <script>
                calcLine(<?=$page_num?>);
            </script>
        <?php endforeach?>
    <?php endif?>
</body>
</html>