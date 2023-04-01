<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-16">
        <style>

            .asian {
                font-family: "simsun";
                word-wrap: break-word;
                word-break: break-all;
            }
        </style>
    </head>
    <body>
        <div class="latin">
            Abcdefg. {{ $dummy_key }}
        </div>
        <div class="asian">
            啊啊啊啊啊啊。
        </div>
    </body>
</html>