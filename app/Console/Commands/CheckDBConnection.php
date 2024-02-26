<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDBConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка соединения с базой данных';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::connection()->getPdo();
            $this->info('Соединение с базой данных успешно установлено.');
        } catch (\Exception $e) {
            $this->error('Не удалось соединиться с базой данных. Ошибка: ' . $e->getMessage());
        }
    }
}
