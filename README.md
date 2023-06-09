# Laravel 10 Notes

`composer create-project --prefer-dist laravel/laravel project_name "10.*"`

## Notable changes from Laravel 8 to 10

- In version 9.19, Vite became the default bundler: https://www.youtube.com/playlist?list=PLDc9bt_00KcJaUEt9_WLioydKazVJwaXZ , https://www.youtube.com/watch?v=KCrXgy8qtjM , https://www.youtube.com/watch?v=epMbfE37014

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

- https://github.com/barryvdh/laravel-dompdf/issues/79
- https://github.com/barryvdh/laravel-dompdf/issues/290
- https://stackoverflow.com/questions/45714545/dompdf-package-other-languages-support-in-laravel
- https://blog.kongnir.com/2017/11/28/laravel-dompdf-custom-font-to-support-simplified-chinese/
- https://bloglaptrinh.info/laravel-dompdf-font-issue/

1. Have the font `ttf`
2. Have this script: https://github.com/dompdf/utils/blob/master/load_font.php
3. Make directory: `storage/fonts/`
4. Run: `gzip fonts/simsun.ttf.gz && php load_font.php simsun ./fonts/simsun.ttf` (May also need to run: `php artisan cache:clear && php artisan route:cache && php artisan route:clear`)

If you get `Undefined array key "storage/fonts/font-name"`, then go into `storage/fonts/installed-fonts.json` and change all the `storage\/fonts\/font-name` to `font-name`.

## Storage to AWS S3

- https://github.com/thephpleague/flysystem-aws-s3-v3
	- https://github.com/thephpleague/flysystem
		- https://flysystem.thephpleague.com/docs/guides/laravel-usage/
			- https://laravel.com/docs/10.x/filesystem#s3-driver-configuration

- https://github.com/aws/aws-sdk-php-laravel
- https://iwconnect.com/working-with-amazonaws-s3-creds-in-laravel/
- https://readouble.com/laravel/8.x/en/filesystem.html
- https://www.clever-cloud.com/doc/deploy/addon/cellar/

1. `composer require league/flysystem-aws-s3-v3`

2. `.env`
```
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_ENDPOINT=https://cellar-c2.services.clever-cloud.com
```

3. `config/filesystems.php`
```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
    ],
],
```

4. Usage
```php
use Illuminate\Support\Facades\Storage;

$fileName = 'path/to/file/filename.txt';
$content = 'bla bla';

if (Storage::disk('s3')->exists($fileName))
{
  Storage::disk('s3')->append($fileName, $content);
  Storage::disk('s3')->setVisibility($fileName, 'public');
}
else
{
  Storage::disk('s3')->put($fileName, $heading . $content, 'public');
}
```

## Clevercloud

- https://www.clever-cloud.com/doc/administrate
    - https://www.clever-cloud.com/doc/deploy/application/php/tutorials/tutorial-laravel/
- https://www.youtube.com/watch?v=ZWEbZhFk4bs

`clevercloud/php.json`

```
 {
   "deploy": {
     "webroot": "/public"
   }
 }
```

### Cron

- https://laravel.com/docs/10.x/scheduling

`app/Console/Kernel.php`

```php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\TransactionChecker;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        TransactionChecker::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('transaction:check')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

`app/Console/Commands/TransactionChecker.php`

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TransactionCompletenessService;

class TransactionChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the completeness of transactions';

    private $transactionCompletenessService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TransactionCompletenessService $transactionCompletenessService)
    {
        $this->transactionCompletenessService = $transactionCompletenessService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $result   = '';
        $finished = false;

        while (!$finished)
        {
            $response = $this->transactionCompletenessService->check();

            $result  .= $response['message'];
            $finished = $response['finished'];
        }

        $this->info("RESULTS: \n" . $result);

        return 0;
    }
}
```

Now you can invoke from terminal: `php artisan transaction:check`

In server:

1. SSH in
2. `crontab -e`
3. `* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`

In Clevercloud server: 

- https://www.clever-cloud.com/doc/administrate/cron
- https://www.clever-cloud.com/doc/deploy/application/php/tutorials/tutorial-laravel/#optional-configure-task-scheduling

`clevercloud/cron.json`

```
[
  "0 1 * * * $ROOT/clevercloud/cron.sh"
]
```

`clevercloud/cron.sh`

```
#!/bin/bash -l
set -euo pipefail

pushd "$APP_HOME"
/path/to/php /path/to/artisan transaction:check >> /dev/null 2>&1
```

To find the paths to the php executable: https://github.com/Ruslan-Aliyev/Laravel8_Newest_Notes#get-servers-info-from-within-laravel

## Pipeline and filters

https://dev.to/abrardev99/pipeline-pattern-in-laravel-278p

## Rate Limit

- https://www.youtube.com/watch?v=vrLcCxWlxOk
- https://laravel.com/docs/10.x/rate-limiting
- https://stackoverflow.com/questions/43058219/disable-rate-limiter-in-laravel
- https://www.cloudways.com/blog/laravel-and-api-rate-limiting/
- https://www.youtube.com/watch?v=TQSDi3e0TxU

![rate_limit](https://github.com/Ruslan-Aliyev/Laravel10_Newest_Notes/assets/6761422/282bba79-da39-4dbf-aacf-6f01c9b0c80e)
