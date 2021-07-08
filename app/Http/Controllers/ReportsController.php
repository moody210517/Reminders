<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Reminder;

class ReportsController extends Controller
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

    public function getExpiry($id = '' , Request $request){        
        

        $customers = Customer::all();
        $services = Service::all();
        
        if($request->customer_id){
            $customer_id = $request->customer_id;
        }else{
            if($customers->first()){
                $customer_id = $customers->first()->id;
            }else{
                $customer_id = 0;
            }            
        }

        if($request->service_id){
            $service_id = $request->service_id;
        }else{
            if($services->first()){
                $service_id = $services->first()->id;
            }else{
                $service_id = 0;
            }            
        }

        if($request->from_date != null){
            $filter['from_date'] = $request->from_date;
            $filter['to_date'] = $request->to_date;
            $filter['start_date'] = $request->start_date;
            $filter['expiry_date'] = $request->expiry_date;
            $filter['customer_id'] = $request->customer_id;
            $filter['service_id'] = $request->service_id;
        }else{
            $filter['from_date'] = date('Y-m-d');
            $filter['to_date'] = date('Y-m-d');//, strtotime("+1 day"));
            $filter['start_date'] = date('Y-m-d');
            $filter['expiry_date'] = date('Y-m-d');
            $filter['customer_id'] = $customer_id;
            $filter['service_id'] = $service_id;
        }

        


        $isTrigger = "from";        
        if($request->isTrigger != null){
            $isTrigger = $request->isTrigger;
        }

        $status = 1;
        $search = '';
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
            ->select('reminder.id' , 'customers.company_name as customer' , 'services.name as service' , 'reminder.created_at' , 'reminder.duration' 
            , 'reminder.detail', 'reminder.start_date');
        
        
        $reminders = $reminders->where('reminder.customer_id', $customer_id);
        $reminders = $reminders->where('reminder.service_id', $service_id);        
        
        if($isTrigger == "from"){
            $reminders = $reminders->whereBetween('reminder.created_at', [$filter['from_date']." 00:00:00", $filter['from_date']." 23:59:59" ])->get();
            //$reminders = $reminders->where('reminder.created_at','>=', $filter['from_date'])->get();//->where('reminder.created_at' , '<=' , $filter['to_date'])->get();            
        }else{
            $reminders = $reminders->get();
        }
                    
        $reminders->map(function ($data) {                    
            $data->created =  date('Y-m-d', strtotime($data->created_at));
            if($data->duration == 1){
                $data->duration = "Monthly";
            }else{
                $data->duration = "Annual";
            }            
            return $data;        
        });

        $data = [
            'category_name' => 'reports',
            'page_name' => 'view_expiry',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '',
            'customers' => $customers, 
            'services' => $services,
            'filter' => $filter,
            'reminders' => $reminders

        ];

        return view('pages.reports.expiry')->with($data);
    }

    
    
}
