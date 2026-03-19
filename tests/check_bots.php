<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Bot count: ".DB::table('tg_bots')->count().PHP_EOL;
$bots = DB::table('tg_bots')->select(['id','name','token','status','type'])->get();
foreach ($bots as $b) {
    echo $b->id.' | '.$b->name.' | '.substr($b->token, 0, 10).'... | '.$b->status.' | '.$b->type.PHP_EOL;
}
