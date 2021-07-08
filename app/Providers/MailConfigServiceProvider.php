<?php

namespace App\Providers;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (\Schema::hasTable('settings')) {

            //$configs = Setting::where('type','email')->get();
            $configs = DB::table('settings')->get();        
            foreach($configs as $item){
                switch($item->name){
                    case "SMPT Servcer":                    
                        $host = $item->value; 
                        break;
                    case "SMPT Port" :
                        $port = $item->value; 

                        break;
                    case "SMPT Username" :
                        $username = $item->value; 

                        break;
                    case "SMPT Password" :
                        $password = $item->value; 

                        break;
                    case "Encryption":
                        $encryption = $item->value; 

                        break;
                }
            }

            $config = array(
                'driver'     => 'smtp',
                'host'       => $host,
                'port'       => $port,
                'from'       => array('address' => 'refund@platinumhomecareuk.co.uk', 'name' => 'Tester'),
                'encryption' => $encryption,
                'username'   => $username,
                'password'   => $password,
                'sendmail'   => '/usr/sbin/sendmail -bs',
                'pretend'    => false,
            );

            \Log::Debug($config);
            \Log::Debug("called");
        
            Config::set('mail', $config);            

            // $mail = DB::table('mails')->first();
            // if ($mail) //checking if table is not empty
            // {
            //     $config = array(
            //         'driver'     => $mail->driver,
            //         'host'       => $mail->host,
            //         'port'       => $mail->port,
            //         'from'       => array('address' => $mail->from_address, 'name' => $mail->from_name),
            //         'encryption' => $mail->encryption,
            //         'username'   => $mail->username,
            //         'password'   => $mail->password,
            //         'sendmail'   => '/usr/sbin/sendmail -bs',
            //         'pretend'    => false,
            //     );
            //     Config::set('mail', $config);
            // }


        }
    }
}