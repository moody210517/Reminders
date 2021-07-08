<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Reminder;
use App\Exports\ReminderExport;
use App\Models\RemindMeWhen;
use Auth;
use PDF;

class RemindersController extends Controller
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

    public function getView($msg = null, $msg_type = 1){        
        
        $data = [
            'category_name' => 'reminders',
            'page_name' => 'view_reminders',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '' ,
            'msg'=>$msg,
            'msg_type' =>$msg_type
        ];

        return view('pages.reminders.lists')->with($data);
    }

    public function getArchive($msg = null, $msg_type = 1){
        $data = [
            'category_name' => 'reminders',
            'page_name' => 'archived_reminders',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '',
            'msg' => $msg,
            'msg_type' => $msg_type
        ];
        return view('pages.reminders.archive')->with($data);
    }


    public function getCreate($id = '', Request $request)
    {
        if($request->customer_id){           
            
            $data = $request->all();
            \Log::Debug($data);

            $remind_me_via_email = $request->remind_me_via_email;            
            $remind_me_via_sms = $request->remind_me_via_sms;
            $reminder_customer_email = $request->reminder_customer_email;
            $reminder_customer_sms = $request->reminder_customer_sms;
            
            if($remind_me_via_email == "on" && $remind_me_via_sms == "on"){
                $data['reminder_me_via'] = "3";
            }else if($remind_me_via_email == "on"){
                $data['reminder_me_via'] = "1";
            }else if($remind_me_via_sms == "on"){
                $data['reminder_me_via'] = "2";
            }   
            
            if($reminder_customer_email == "on" && $reminder_customer_sms == "on"){
                $data['reminder_customer_via'] = "3";
            }else if($reminder_customer_email == "on"){
                $data['reminder_customer_via'] = "1";
            }else if($reminder_customer_sms == "on"){
                $data['reminder_customer_via'] = "2";
            }

            $data['reminder_me_when'] = str_replace(',',':', $request->reminder_me_when);
            $data['created_by'] = Auth::user()->id;
            
            Reminder::create($data);
            return $this->getView("Success" , 1 );

        }else{

            $customers = Customer::all();
            $services = Service::all();
            $remind_me_when = RemindMeWhen::all();

            $index = 0;
            foreach($remind_me_when as $item){
                switch($index){
                    case 0:
                        $item->show = "checkbox-primary";
                        break;
                    case 1:
                        $item->show = "checkbox-success";
                        break;
                    case 2:
                        $item->show = "checkbox-info";
                        break;
                    case 3:
                        $item->show = "checkbox-warning";
                        break;
                    case 4:
                        $item->show = "checkbox-danger";
                        break;
                    case 5:
                        $item->show = "checkbox-secondary";
                        break;
                }
                $index++;
            }

            $reminder = null;
            if($id != null){
                $reminder = Reminder::findOrFail($id);
            }
            
            $user = Auth::user();
            
            $data = [
                'category_name' => 'reminders',
                'page_name' => 'create_reminder',
                'has_scrollspy' => 0,
                'scrollspy_offset' => '',
                'customers' => $customers,
                'services' => $services,
                'reminder' => $reminder,
                'remind_me_when' =>$remind_me_when,
                'user' =>$user
            ];

            return view('pages.reminders.create')->with($data);       

        }        
    }

    public function getUpdate($id = '', Request $request)
    {                
        $reminder  = Reminder::findOrFail($request->id);
        $data = $request->all();

        $remind_me_via_email = $request->remind_me_via_email;            
        $remind_me_via_sms = $request->remind_me_via_sms;
        $reminder_customer_email = $request->reminder_customer_email;
        $reminder_customer_sms = $request->reminder_customer_sms;
        
        if($remind_me_via_email == "on" && $remind_me_via_sms == "on"){
            $data['reminder_me_via'] = "3";
        }else if($remind_me_via_email == "on"){
            $data['reminder_me_via'] = "1";
        }else if($remind_me_via_sms == "on"){
            $data['reminder_me_via'] = "2";
        }   
        
        if($reminder_customer_email == "on" && $reminder_customer_sms == "on"){
            $data['reminder_customer_via'] = "3";
        }else if($reminder_customer_email == "on"){
            $data['reminder_customer_via'] = "1";
        }else if($reminder_customer_sms == "on"){
            $data['reminder_customer_via'] = "2";
        }
        $data['reminder_me_when'] = str_replace(',',':', $request->reminder_me_when);
        $reminder->fill($data)->save();

        return $this->getView("Success" , 1);
    }

    public function getDelete($id = '', Request $request)
    {
        if($id != null){
            Reminder::where('id', $id)->delete();
        }else{
            $id = $request->id;
            Reminder::where('id', $id)->delete();
        }        
        $page  = $request->page;
        if($page == "archive"){
            return $this->getArchive("Success" , 1);
        }else{
            return $this->getView("Success" , 1);
        }        
    }
    
    public function getToarchive($id = '', Request $request){
        if($id != null){
            Reminder::where('id', $id)->update(['status' => 2]);
        }
        return $this->getArchive("Success", 1);        
    }

    public function getRenew($id = '', Request $request){
        if($id != null){
            Reminder::where('id', $id)->update(['status' => 1]);
        }
        return $this->getView("Success", 1);        
    }  


    public function getExport($id = '' , Request $request){
                
        $from_date = ""; $to_date = "";
        $customer_id = 0; $service_id = 0;
        $start_date = ""; $expiry_date = "";

        if($request->from_date != null){
            $from_date = $request->from_date;
        }
        if($request->to_date != null){
            $to_date = $request->to_date;
        }

        if($request->start_date != null){
            $start_date = $request->start_date;
        }
        if($request->expiry_date != null){
            $expiry_date = $request->expiry_date;
        }

        if($request->customer_id != null){
            $customer_id = $request->customer_id;
        }
        if($request->service_id != null){
            $service_id = $request->service_id;
        }


        $isTrigger = "from";        
        if($request->isTrigger != null){
            $isTrigger = $request->isTrigger;
        }
                
        if (isset($_POST['csv'])) {
            # Publish-button was clicked
            return \Excel::download(new ReminderExport($isTrigger, $from_date, $to_date, $start_date , $expiry_date , $customer_id, $service_id ), 'transactions.'."csv");
        }
        elseif (isset($_POST['pdf'])) {
            $status = 1;
            $search = "";            
            $reminders = Reminder::join('customers', 'reminder.customer_id', '=', 'customers.id')
                ->join('services', 'reminder.service_id', '=', 'services.id')
                ->join('duration', 'reminder.duration', '=', 'duration.id')
                ->join('remind_me_when', 'reminder.reminder_me_when', '=', 'remind_me_when.id')
                ->where('status', $status)
                ->where(function($query) use($search){
                    $query->where('reminder.detail', 'like', '%'.$search.'%')
                    ->orWhere('customers.company_name', 'like', '%'.$search.'%')
                    ->orWhere('services.name', 'like', '%'.$search.'%')
                    ->orWhere('duration.name', 'like', '%'.$search.'%')
                    ->orWhere('reminder.expiry', 'like', '%'.$search.'%')
                    ->orWhere('remind_me_when.name', 'like', '%'.$search.'%');

                })
                ->select('reminder.id' , 'customers.company_name as customer' , 'services.name as service' , 'reminder.created_at as created_at' , 'reminder.duration' 
                , 'reminder.detail', 'reminder.start_date' , 'reminder.expiry as expiry');

            
            if($isTrigger == "from"){
                if($from_date != "" && $to_date != null){
                    $reminders = $reminders->whereBetween('reminder.created_at', [$from_date." 00:00:00", $to_date." 23:59:59" ]);
                }
            }else if($isTrigger == "start_date"){
                if($start_date != ""){
                    $reminders = $reminders->where('reminder.start_date', $start_date );
                }
            }else if($isTrigger == "expiry"){
                if($expiry_date != ""){
                    $reminders = $reminders->where('reminder.expiry', $expiry_date );
                }
            }        

            if($service_id != 0){
                $reminders = $reminders->where('reminder.service_id', $service_id);
            }
            if($customer_id != 0){
                $reminders = $reminders->where('reminder.customer_id', $customer_id);
            }

            $reminders = $reminders->get();//skip($start)->take($length)->get();
            $reminders->map(function ($data) {
                        
                //$data->customer = Customer::find($data->customer_id)->company_name;
                //$data->service = Service::find($data->service_id)->name;

                $data->created =  date('Y-m-d', strtotime($data->created_at));
                if($data->duration == 1){
                    $data->duration = "Monthly";
                }else{
                    $data->duration = "Annual";
                }            
                return $data;                            
            });
            
            //return view('pdf.index', compact('reminders'));
            // share data to view
            view()->share('reminders',$reminders);
            $pdf = PDF::loadView('pdf.index', $reminders);
            return $pdf->download('pdf_file.pdf');
            
        }



        
    }

 
    
}
