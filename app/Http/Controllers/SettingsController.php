<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Mail;
use Config;
use App\Mail\ReminderMail;
use DB;
use Swift_Mailer;
use Swift_SmtpTransport;
use Illuminate\Contracts\Mail\Mailer;
use App\Providers\MailConfigServiceProvider;
use App;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('dashboard');
    }

    public function getView($msg = null, $msg_type = 1)
    {
        
        $email_setting  = Setting::where('type', 'email')->get();
        $sms_setting  = Setting::where('type', 'sms')->get();        
        
        $data = [
            'category_name' => 'settings',
            'page_name' => 'setting',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '',
            'email_setting' => $email_setting,
            'sms_setting' => $sms_setting,
            'msg'=>$msg,
            'msg_type' => $msg_type
        ];
        // $pageName = 'analytics';
        return view('pages.settings.index')->with($data);

    }


    public function getUpdate($id = '', Request $request)
    {
                        
        $email_id = $request->email_id;
        $email_params = $request->email_params;
        $type = $request->type;

        if($email_params != null && $email_id != null){            
            $index = 0;
            foreach($email_id as $id){
                Setting::where('id', $id)->update(['value'=>$email_params[$index]]);
                $index++;
            }            
        }        
       

        try{
                            
            if($type == "email"){

                (new MailConfigServiceProvider(app()))->register();
                //(new MailConfigServiceProvider(app()))->register();
                //$app = App::getInstance();
                //$app->register('App\Providers\MailConfigServiceProvider');
                //sleep(10);
                Mail::to('carl517@outlook.com')->send(new ReminderMail);
                Mail::to('carl517@outlook.com')->send(new ReminderMail);

                // $data['name'] =  "Data";        
                // $data['date'] = date('Y-m-d H:i:s');        
                // $recipient = "carl517@outlook.com";
        
                // $validate = Mail::send('email.email_test', ["data"=>$data], function ($message) use ($recipient) {
                //     //$message->from('apikey', 'New User');
                //     $message->from('refund@platinumhomecareuk.co.uk', $name = null);
                //     $message->subject('Chargeback');
                //     $message->to($recipient);            
                // }); 
                            
            }else{
                $client = new \CMText\TextClient('c3f67785-845b-4400-aa96-83d09d103851');
                \Log::Debug(json_encode($client));
                $sms  = Setting::where('type','sms')->get();
                $phones = [];
                foreach($sms as $item){
                    if($item->name == "Phone Number"){
                        $phones[] = $item->value;
                    }
                }

                \Log::Debug($phones);
                $result = $client->SendMessage('This is sms Test', 'CM.com', $phones , 'Your_Reference');
            }
        }catch(\Exception $e){
            \Log::Debug($e);
            return $this->getView("Failed " , 0 );
        }      
        return $this->getView("Success" , 1 );
    }


    
}
