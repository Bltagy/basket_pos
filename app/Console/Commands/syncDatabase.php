<?php

namespace App\Console\Commands;

use App\GeneralSetting;
use App\GeneralSettingRemote;
use App\Sync;
use App\SyncRemote;
use DB;
use Illuminate\Console\Command;

class syncDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $setting = GeneralSetting::first();
        if( !$setting->device_id ){
            $time = time();
            $setting->device_id = $time;
            $setting->save();
            $remoteSetting = GeneralSettingRemote::first();
            $devices = $remoteSetting->connected_devices;
            dd($devices);
            $devices[] = $time;
            $remoteSetting->connected_devices = array_unique($devices);
            $remoteSetting->save();
            die;

        }
        $localSyncs = Sync::where('synced',0)
                          ->where('origin','offline')
                          ->get();
        foreach ($localSyncs as $localSync) {
            $data = $localSync->toArray();
            unset($data['id']);
            SyncRemote::create($data);
            $localSync->synced = 1;
            $localSync->save();

        }
        $onlineSyncs =  SyncRemote::
                            where('synced',0)
//                          ->where('origin','!=',env('SYNC_SERVER'))
//                            ->where('origin','offline')
                            ->where('origin','online')
                          ->get();

        foreach ($onlineSyncs as $onlineSync) {
            $data = $onlineSync->toArray();
            unset($data['id']);
            Sync::create($data);
        }
    }
}
