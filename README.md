# Laravel 10 Notes

`composer create-project --prefer-dist laravel/laravel project_name "10.*"`


## Non-Latin fonts in BarryVDH PDF generation

### With BarryVDH-PDF (DomPDF) and Latin characters

https://www.positronx.io/laravel-pdf-tutorial-generate-pdf-with-dompdf-in-laravel/

1. `composer require barryvdh/laravel-dompdf`
2. In `config/app.php`:

	```php
	'providers' => [
		Barryvdh\DomPDF\ServiceProvider::class,
	],
	'aliases' => [
		'PDF' => Barryvdh\DomPDF\Facade::class,
	]
	```

3. `php artisan vendor:publish`
4. Make route, controller and view

### With non-Latin characters

https://bloglaptrinh.info/laravel-dompdf-font-issue/

1. Have the font `ttf`
2. Have this script: https://github.com/dompdf/utils/blob/master/load_font.php
3. Make directory: `storage/fonts/`
4. Run: `gzip fonts/simsun.ttf.gz && php load_font.php simsun ./fonts/simsun.ttf`

If you get `Undefined array key "storage/fonts/font-name"`, then go into `storage/fonts/installed-fonts.json` and change all the `storage\/fonts\/font-name` to `font-name`.
