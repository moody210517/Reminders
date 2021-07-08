<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;

use App\Models\Customer;
use App\Models\Service;
use App\Models\Reminder;
use App\Models\User;
use Auth;
use DB;


class PaginationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
        

    public function getUsers(Request $request){                           

        \Log::Debug('start - '.json_encode($request));
        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        
        $search_data = $request->get('search');
        $search = '';
        if($search_data){
            $search = $search_data['value'];
        }

        $order_data = $request->get('order');
        $index = -1;
        $dir = '';
        if( isset($order_data[0]) ){
            $index = $order_data[0]['column'];
            $dir = $order_data[0]['dir'];
        }

        // order[0][column]: 3
        // order[0][dir]: asc

        \Log::Debug('start - '.$start);
        \Log::Debug('length - '.$length);

        $users = User::where('role' , 2)
            ->where(function($query) use($search) {            
                $query->where('name', 'like', '%'.$search.'%')->orWhere('username', 'like', '%'.$search.'%')
                ->orWhere('job_title', 'like', '%'.$search.'%')->orWhere('number', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')->orWhere('surname', 'like', '%'.$search.'%');
            });                                    
        $users = $users->skip($start)->take($length)->get();

        
        $count = $users->count();
        $data = array(
            'draw' => $draw,
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $users,
        );

        echo json_encode($data);
    }


    public function getCustomers(Request $request){                           


        \Log::Debug('start - '.json_encode($request));

        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        
        $search_data = $request->get('search');
        $search = '';
        if($search_data){
            $search = $search_data['value'];
        }

        $order_data = $request->get('order');
        $index = -1;
        $dir = '';
        if( isset($order_data[0]) ){
            $index = $order_data[0]['column'];
            $dir = $order_data[0]['dir'];
        }

        // order[0][column]: 3
        // order[0][dir]: asc

        \Log::Debug('start - '.$start);
        \Log::Debug('length - '.$length);

        $users = Customer::where('company_name', 'like', '%'.$search.'%')->orWhere('physical_address', 'like', '%'.$search.'%');
        $count = $users->count();
        // switch($index){
        //     case 0:
        //         $users = $users->orderBy('name', $dir)->skip($start)->take($length)->get();

        // }
        $users = $users->skip($start)->take($length)->get();        
        
        $data = array(
            'draw' => $draw,
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $users,
        );

        echo json_encode($data);
    }


    public function getServices(Request $request){                           


        \Log::Debug('start - '.json_encode($request));

        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        
        $search_data = $request->get('search');
        $search = '';
        if($search_data){
            $search = $search_data['value'];
        }

        $order_data = $request->get('order');
        $index = -1;
        $dir = '';
        if( isset($order_data[0]) ){
            $index = $order_data[0]['column'];
            $dir = $order_data[0]['dir'];
        }

        // order[0][column]: 3
        // order[0][dir]: asc

        \Log::Debug('start - '.$start);
        \Log::Debug('length - '.$length);

        $services = Service::where('name', 'like', '%'.$search.'%');
        $count = $services->count();
        $services = $services->skip($start)->take($length)->get();

        $i = 1;
        $services->map(function($data) use(&$i){
            $data['number'] = $i ;
            $i = $i + 1;
            return $data;
        });
        
        
        $data = array(
            'draw' => $draw,
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $services,
        );

        echo json_encode($data);
    }


    
    public function getReminders(Request $request){                           
        
        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        
        $search_data = $request->get('search');
        $search = '';
        if($search_data){
            $search = $search_data['value'];
        }

        $order_data = $request->get('order');
        $index = -1;
        $dir = 'DESC';
        if( isset($order_data[0]) ){
            $index = $order_data[0]['column'];
            $dir = $order_data[0]['dir'];
        }

        // order[0][column]: 3
        // order[0][dir]: asc

        \Log::Debug('start - '.$start);
        \Log::Debug('length - '.$length);
        $status = 1;
        if($request->status != null){
            $status = $request->status;
        }
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

        \Log::Debug('from date '.$from_date);
        \Log::Debug('end date '.$to_date);
        \Log::Debug('customer '.$customer_id);
        \Log::Debug('service '.$service_id);     

        $reminders = Reminder::join('customers', 'reminder.customer_id', '=', 'customers.id')
            ->join('services', 'reminder.service_id', '=', 'services.id')
            ->join('duration', 'reminder.duration', '=', 'duration.id')
            ->join('remind_me_when', 'reminder.reminder_me_when', '=', 'remind_me_when.id')
            ->join('status', 'reminder.status', '=', 'status.id')
            ->where('status', $status)
            ->where(function($query) use($search){
                $query->where('reminder.detail', 'like', '%'.$search.'%')
                ->orWhere('customers.company_name', 'like', '%'.$search.'%')
                ->orWhere('services.name', 'like', '%'.$search.'%')
                ->orWhere('duration.name', 'like', '%'.$search.'%')
                ->orWhere('reminder.expiry', 'like', '%'.$search.'%')
                ->orWhere('remind_me_when.name', 'like', '%'.$search.'%');

            })
            ->select('reminder.id' , 'customers.company_name as customer' , 'services.name as service' , 'reminder.created_at' , 'reminder.duration' 
            , 'reminder.detail', 'reminder.start_date' , 'reminder.reminder_me_via' , 'remind_me_when.name as reminder_me_when', 'reminder.expiry', 'status.name as status');

        $count = $reminders->count();
        
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

        $reminders = $reminders->skip($start)->take($length)->orderBy('reminder.id' , $dir)->get();
        $reminders->map(function ($data) {
                    
            //$data->customer = Customer::find($data->customer_id)->company_name;
            //$data->service = Service::find($data->service_id)->name;
            //$data->created =  date('Y-m-d', strtotime($data->created_at));

            if($data->reminder_me_via != null){
                if($data->reminder_me_via == 1){
                    $data->reminder_me_via = "Email";
                }else if($data->reminder_me_via == 2){
                    $data->reminder_me_via = "SMS";
                }else if($data->reminder_me_via == 3){
                    $data->reminder_me_via = "Email/SMS";
                }            
            }else{
                $data->reminder_me_via = "";
            }
            

            if($data->duration == 1){
                $data->duration = "Monthly";
            }else{
                $data->duration = "Annual";
            }           

            return $data;
        
        });

       
        $data = array(
            'draw' => $draw,
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $reminders,
        );

        echo json_encode($data);
    }



    



}
