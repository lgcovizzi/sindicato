<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Backup do banco de dados diariamente às 2:00
        $schedule->command('backup:run --only-db')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->onFailure(function () {
                     // Notificar administradores sobre falha no backup
                 });

        // Limpeza de logs antigos semanalmente
        $schedule->command('log:clear --days=30')
                 ->weekly()
                 ->sundays()
                 ->at('03:00');

        // Limpeza de sessões expiradas diariamente
        $schedule->command('session:gc')
                 ->daily()
                 ->at('04:00');

        // Processamento de notificações pendentes a cada 5 minutos
        $schedule->command('notifications:process')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        // Sincronização de dados de convênios diariamente
        $schedule->command('convenios:sync')
                 ->dailyAt('06:00')
                 ->withoutOverlapping();

        // Limpeza de arquivos temporários diariamente
        $schedule->command('temp:clean')
                 ->dailyAt('01:00');

        // Verificação de votações expiradas a cada hora
        $schedule->command('votings:check-expired')
                 ->hourly()
                 ->withoutOverlapping();

        // Geração de relatórios mensais
        $schedule->command('reports:generate monthly')
                 ->monthlyOn(1, '08:00')
                 ->withoutOverlapping();

        // Otimização do banco de dados semanalmente
        $schedule->command('db:optimize')
                 ->weekly()
                 ->sundays()
                 ->at('05:00');

        // Limpeza de cache de imagens antigas
        $schedule->command('media:clean-cache')
                 ->weekly()
                 ->saturdays()
                 ->at('02:30');

        // Verificação de integridade dos dados críticos
        $schedule->command('data:integrity-check')
                 ->dailyAt('07:00')
                 ->onFailure(function () {
                     // Alertar sobre problemas de integridade
                 });

        // Envio de lembretes de votações próximas
        $schedule->command('votings:send-reminders')
                 ->dailyAt('09:00')
                 ->withoutOverlapping();

        // Atualização de estatísticas do sistema
        $schedule->command('stats:update')
                 ->hourly()
                 ->between('08:00', '18:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}