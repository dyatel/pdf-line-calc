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
list($src, $svg) = (function ($file) {
    $data = [];
    //
    if($file){
        if(!copy($file['tmp_name'], UPLOAD_ORIGINAL."/".$file['name']))
            throw new Exception("Unable copy file to uploads directory");

        // конвертируем в svg при помощи inkscape
        exec(sprintf('inkscape -l %s%s.svg %s%s 2>&1', UPLOAD_SVG, $file['name'], UPLOAD_ORIGINAL, $file['name']), $out, $return);

        // если скрипт вернул не 0, отображаем вывод
        if($return){
            var_dump('<pre>', $out, '</pre>');
            return $data;
        }
        // присваиваем $src
        $data[] = UPLOAD_SVG_SRC."{$file['name']}.svg";
        // присваиваем $svg
        $data[] = file_get_contents(UPLOAD_SVG."{$file['name']}.svg");
    }
    //
    return $data;
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
        function calcLine(){
            // убираем все зависимости
            document.querySelectorAll('#svg svg defs').forEach(function(el){
                el.remove();
            });

            // задаем коэффициент перевода пикселей в миллиметры
            var ratio = 0.35285019885108265,
                length = 0;

            // считаем длину всех путей
            document.querySelectorAll('#svg svg g path').forEach(function(p){
                length += p.getTotalLength();
            });

            // выводим результат
            document.querySelector('.in_px_result').innerHTML = Math.round(length);
            document.querySelector('.in_mm_result').innerHTML = Math.round(length*ratio);
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

    <?php if($src):?>
        <!--  Результат  -->
        <div>
            <strong>Длина реза: <span class="in_mm_result">0</span> mm, <span class="in_px_result">0</span> px</strong>
        </div>

        <!--  Картинка для наглядности  -->
        <img width="1000" src="<?php echo $src?>" style="border: 1px solid red" alt="">

        <!--  Скрытый элемент с данными svg  -->
        <div id="svg" style="display: none"><?php echo $svg?></div>

        <script>
            calcLine();
        </script>
    <?php endif?>
</body>

</html>