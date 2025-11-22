<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CheckScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek status scheduler Laravel dan daftar scheduled tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('🔍 CEK STATUS SCHEDULER LARAVEL');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // 1. Tampilkan daftar scheduled tasks
        $this->info('📋 Daftar Scheduled Tasks:');
        $this->newLine();

        Artisan::call('schedule:list');
        $this->line(Artisan::output());

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // 2. Cek apakah cron job sudah di-setup
        $this->info('🔧 Cara Setup Cron Job:');
        $this->newLine();
        $this->line('1. Buka terminal/command prompt');
        $this->line('2. Jalankan: crontab -e (Linux/Mac)');
        $this->line('3. Tambahkan baris berikut:');
        $this->newLine();
        $this->comment('   * * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1');
        $this->newLine();
        $this->line('4. Simpan dan keluar');
        $this->newLine();

        $this->info('💡 Untuk Windows (Laragon):');
        $this->line('   - Gunakan Task Scheduler Windows');
        $this->line('   - Atau gunakan Laragon Task Scheduler');
        $this->newLine();

        // 3. Test scheduler
        $this->info('🧪 Test Scheduler:');
        $this->newLine();
        $this->line('Jalankan command berikut untuk test:');
        $this->comment('   php artisan schedule:test');
        $this->newLine();
        $this->line('Atau test command laporan langsung:');
        $this->comment('   php artisan report:daily-whatsapp');
        $this->newLine();

        // 4. Cek log
        $this->info('📝 Cek Log:');
        $this->newLine();
        $this->line('Log scheduler ada di:');
        $this->comment('   storage/logs/laravel.log');
        $this->newLine();

        return 0;
    }
}

